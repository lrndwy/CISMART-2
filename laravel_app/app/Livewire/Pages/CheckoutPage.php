<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Services\DeliveryCostCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CheckoutPage extends Component
{
    public string $step = 'address';

    public bool $loading = false;

    // Shipping Address
    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $city = 'Cilacap';

    public string $postalCode = '';

    public string $notes = '';

    public ?string $deliveryLatitude = null;

    public ?string $deliveryLongitude = null;

    // Payment & Shipping
    public string $paymentMethod = '';

    public string $shippingMethod = 'pickup';

    public array $shippingOptions = [];

    public array $paymentOptions = [];

    public array $checkoutItems = [];

    protected ?Shop $cachedCheckoutShop = null;

    protected bool $checkoutShopLoaded = false;

    public function mount()
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login', ['redirect' => '/checkout']);
        }

        // Get checkout items from session
        $checkoutItemIds = session()->get('checkout_items', []);
        $cart = session()->get('cart', []);

        if (empty($checkoutItemIds)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong');
        }

        // Filter cart items based on checkout selection
        $this->checkoutItems = array_filter($cart, function ($key) use ($checkoutItemIds) {
            return in_array($key, $checkoutItemIds);
        }, ARRAY_FILTER_USE_KEY);

        if (empty($this->checkoutItems)) {
            return redirect()->route('cart.index')->with('error', 'Tidak ada produk yang dipilih');
        }

        // Initialize with user data
        $this->name = Auth::user()->name ?? '';
        $this->phone = Auth::user()->phone ?? '';
        $this->address = Auth::user()->address ?? '';

        $this->initializeOptions();
        $this->shippingMethod = $this->canDeliver() ? 'delivery' : 'pickup';
    }

    protected function initializeOptions()
    {
        $this->shippingOptions = $this->availableShippingOptions();

        $this->paymentOptions = [
            [
                'id' => 'qris',
                'name' => 'QRIS',
                'icon' => '🔳',
                'desc' => 'Scan QR toko, lalu unggah bukti pembayaran',
            ],
            [
                'id' => 'whatsapp-order',
                'name' => 'Pesan via WhatsApp',
                'icon' => '💬',
                'desc' => 'Konfirmasi pesanan langsung ke penjual',
            ],
            [
                'id' => 'bank-transfer',
                'name' => 'Transfer Bank',
                'icon' => '🏦',
                'desc' => 'Koordinasi pembayaran via WhatsApp',
            ],
            [
                'id' => 'cod',
                'name' => 'Bayar di Tempat',
                'icon' => '💵',
                'desc' => 'Khusus area Cilacap',
            ],
        ];
    }

    public function submitAddress()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'shippingMethod' => 'required|in:pickup,delivery',
        ], [
            'name.required' => 'Nama penerima harus diisi',
            'phone.required' => 'Nomor telepon harus diisi',
            'address.required' => 'Alamat lengkap harus diisi',
            'city.required' => 'Kota harus diisi',
            'shippingMethod.required' => 'Pilih metode pengiriman',
        ]);

        if ($this->shippingMethod === 'delivery') {
            if (! $this->canDeliver()) {
                $this->addError('shippingMethod', 'Toko belum mengatur pengiriman atau belanja dari lebih dari satu toko.');

                return;
            }

            $this->validate([
                'deliveryLatitude' => 'required|numeric|between:-90,90',
                'deliveryLongitude' => 'required|numeric|between:-180,180',
            ], [
                'deliveryLatitude.required' => 'Tandai lokasi pengiriman di peta',
                'deliveryLongitude.required' => 'Tandai lokasi pengiriman di peta',
            ]);
        }

        $this->step = 'payment';
    }

    public function setDeliveryLocation(float|string $latitude, float|string $longitude): void
    {
        $this->deliveryLatitude = number_format((float) $latitude, 8, '.', '');
        $this->deliveryLongitude = number_format((float) $longitude, 8, '.', '');
    }

    public function submitPayment()
    {
        if (empty($this->paymentMethod)) {
            $this->dispatch('notify', [
                'message' => 'Mohon pilih metode pembayaran',
                'type' => 'error',
            ]);

            return;
        }

        if ($this->paymentMethod === 'qris') {
            $shop = $this->checkoutShop();

            if (! $shop) {
                $this->dispatch('notify', [
                    'message' => 'QRIS hanya untuk belanja dari satu toko. Checkout per toko, atau pilih metode lain.',
                    'type' => 'error',
                ]);

                return;
            }

            if (! $shop->hasQris()) {
                $this->dispatch('notify', [
                    'message' => 'Toko ini belum mengatur QRIS. Pilih metode lain atau hubungi penjual.',
                    'type' => 'error',
                ]);

                return;
            }
        }

        $this->step = 'review';
    }

    public function submitOrder()
    {
        $this->loading = true;

        try {
            DB::beginTransaction();

            // Create order
            $shop = $this->checkoutShop();
            $isQris = $this->paymentMethod === 'qris';
            $quote = $this->shippingMethod === 'delivery' ? $this->getDeliveryQuote() : null;

            $order = Order::create([
                'user_id' => Auth::id(),
                'shipping_name' => $this->name,
                'shipping_phone' => $this->phone,
                'shipping_address' => $this->address,
                'shipping_city' => $this->city,
                'shipping_postal_code' => $this->postalCode,
                'shipping_notes' => $this->notes,
                'delivery_latitude' => $this->shippingMethod === 'delivery' ? $this->deliveryLatitude : null,
                'delivery_longitude' => $this->shippingMethod === 'delivery' ? $this->deliveryLongitude : null,
                'delivery_distance_meters' => $quote['distance_meters'] ?? null,
                'payment_method' => $this->paymentMethod,
                'payment_status' => $isQris ? 'unpaid' : 'not_required',
                'shipping_method' => $this->shippingMethod ?: 'none',
                'subtotal' => $this->getSubtotal(),
                'shipping_cost' => $this->getShippingCost(),
                'total' => $this->getTotal(),
                'status' => 'pending',
                'shop_whatsapp' => $shop?->phone ?: '6288225292279',
                'whatsapp_sent_at' => $isQris ? null : now(),
            ]);

            if ($isQris && $shop?->hasQris()) {
                $order->update([
                    'qris_type' => $shop->qris_mode,
                    'qris_payload' => $shop->qrisPayloadFor($order->total, $order->order_number),
                ]);
            }

            // Create order items
            foreach ($this->checkoutItems as $productId => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'shop_id' => $item['shop_id'] ?? $shop?->id,
                    'product_name' => $item['name'],
                    'shop_name' => $item['shop_name'],
                    'product_image' => $item['image'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            $cart = session()->get('cart', []);
            foreach (array_keys($this->checkoutItems) as $productId) {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
            session()->forget('checkout_items');

            if ($isQris) {
                $this->dispatch('cart-updated');

                return redirect()->route('orders.pay', $order);
            }

            $message = $this->generateWhatsAppMessage($order);
            $whatsappUrl = 'https://wa.me/6288225292279?text='.urlencode($message);
            $this->dispatch('open-whatsapp', ['url' => $whatsappUrl]);

            $this->step = 'success';
            $this->dispatch('cart-updated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                'type' => 'error',
            ]);
        } finally {
            $this->loading = false;
        }
    }

    protected function generateWhatsAppMessage($order = null)
    {
        $itemsList = '';
        foreach ($this->checkoutItems as $item) {
            $itemsList .= "• {$item['name']}\n";
            $itemsList .= "  Qty: {$item['quantity']} x Rp ".number_format($item['price'], 0, ',', '.')."\n";
            $itemsList .= '  Subtotal: Rp '.number_format($item['price'] * $item['quantity'], 0, ',', '.')."\n";
            $itemsList .= "  Toko: {$item['shop_name']}\n\n";
        }

        $shippingInfo = '';
        $shippingCost = $this->getShippingCost();
        $selectedShipping = collect($this->shippingOptions)->firstWhere('id', $this->shippingMethod);
        $shippingName = $selectedShipping['name'] ?? $this->shippingMethod;

        if ($this->shippingMethod === 'delivery') {
            $quote = $this->getDeliveryQuote();
            $distanceLabel = $quote
                ? DeliveryCostCalculator::formatDistance($quote['distance_meters'])
                : '-';
            $mapsUrl = $this->deliveryLatitude && $this->deliveryLongitude
                ? "https://www.google.com/maps?q={$this->deliveryLatitude},{$this->deliveryLongitude}"
                : null;
            $shippingInfo = "🚚 *Pengiriman:*\n".
                "{$shippingName} - Rp ".number_format($shippingCost, 0, ',', '.')."\n".
                "Jarak: {$distanceLabel}\n".
                ($mapsUrl ? "Lokasi: {$mapsUrl}\n\n" : "\n");
        } elseif ($this->shippingMethod === 'pickup') {
            $shippingInfo = "🚚 *Pengiriman:*\n{$shippingName}\n\n";
        }

        $paymentName = collect($this->paymentOptions)->firstWhere('id', $this->paymentMethod)['name'] ?? '';

        $orderNumber = $order ? $order->order_number : 'Menunggu konfirmasi';

        $message = "🛒 *PESANAN BARU DARI CISMART*\n\n".
            "📋 *Nomor Pesanan:* {$orderNumber}\n\n".
            "👤 *Data Pembeli:*\n".
            "Nama: {$this->name}\n".
            "Telepon: {$this->phone}\n".
            'Email: '.(Auth::user()->email ?? '-')."\n\n".
            "📍 *Alamat Pengiriman:*\n".
            "{$this->address}\n".
            "{$this->city} {$this->postalCode}\n".
            ($this->notes ? "Catatan: {$this->notes}\n" : '')."\n".
            "🛍️ *Detail Pesanan:*\n".
            $itemsList.
            $shippingInfo.
            "💰 *Ringkasan Pembayaran:*\n".
            'Subtotal: Rp '.number_format($this->getSubtotal(), 0, ',', '.')."\n";

        if ($shippingCost > 0) {
            $message .= 'Ongkir: Rp '.number_format($shippingCost, 0, ',', '.')."\n";
        }

        $message .= '*TOTAL: Rp '.number_format($this->getTotal(), 0, ',', '.')."*\n\n".
            "💳 *Metode Pembayaran:*\n".
            "{$paymentName}\n\n".
            "---\n".
            'Mohon konfirmasi pesanan ini dan informasi pembayaran selanjutnya. Terima kasih! 🙏';

        return $message;
    }

    public function getSubtotal()
    {
        $total = 0;
        foreach ($this->checkoutItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function getShippingCost()
    {
        if ($this->shippingMethod !== 'delivery') {
            return 0;
        }

        return $this->getDeliveryQuote()['cost'] ?? 0;
    }

    /**
     * @return array{distance_meters: int, units: int, cost: int, unit_meters: int, rate: int}|null
     */
    public function getDeliveryQuote(): ?array
    {
        if ($this->shippingMethod !== 'delivery' || ! $this->canDeliver()) {
            return null;
        }

        if ($this->deliveryLatitude === null || $this->deliveryLatitude === ''
            || $this->deliveryLongitude === null || $this->deliveryLongitude === '') {
            return null;
        }

        return DeliveryCostCalculator::quote(
            $this->checkoutShop(),
            (float) $this->deliveryLatitude,
            (float) $this->deliveryLongitude,
        );
    }

    public function canDeliver(): bool
    {
        return (bool) $this->checkoutShop()?->hasDelivery();
    }

    /**
     * @return list<array{id: string, name: string, desc: string, icon: string}>
     */
    protected function availableShippingOptions(): array
    {
        $options = [
            [
                'id' => 'pickup',
                'name' => 'Ambil di toko',
                'desc' => 'Tanpa ongkir, ambil pesanan langsung di toko',
                'icon' => '🏪',
            ],
        ];

        if ($this->canDeliver()) {
            $shop = $this->checkoutShop();
            $options[] = [
                'id' => 'delivery',
                'name' => 'Kirim ke lokasi',
                'desc' => 'Ongkir Rp '.number_format((int) $shop->delivery_rate, 0, ',', '.').
                    ' per '.number_format((int) $shop->delivery_unit_meters, 0, ',', '.').' meter',
                'icon' => '🛵',
            ];
        }

        return $options;
    }

    public function getTotal()
    {
        return $this->getSubtotal() + $this->getShippingCost();
    }

    public function checkoutShop(): ?Shop
    {
        if ($this->checkoutShopLoaded) {
            return $this->cachedCheckoutShop;
        }

        $productIds = collect($this->checkoutItems)
            ->map(fn ($item, $key) => $item['product_id'] ?? $key)
            ->filter()
            ->values();

        $shopIds = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('shop_id')
            ->unique()
            ->filter();

        $this->cachedCheckoutShop = $shopIds->count() === 1
            ? Shop::query()->find($shopIds->first())
            : null;
        $this->checkoutShopLoaded = true;

        return $this->cachedCheckoutShop;
    }

    public function goBack()
    {
        if ($this->step === 'address') {
            return redirect()->route('cart.index');
        } elseif ($this->step === 'payment') {
            $this->step = 'address';
        } elseif ($this->step === 'review') {
            $this->step = 'payment';
        }
    }

    public function openWhatsAppAgain()
    {
        $message = $this->generateWhatsAppMessage();
        $whatsappUrl = 'https://wa.me/6288225292279?text='.urlencode($message);
        $this->dispatch('open-whatsapp', ['url' => $whatsappUrl]);
    }

    public function render()
    {
        $shop = $this->checkoutShop();
        $quote = $this->getDeliveryQuote();

        return view('livewire.pages.checkout-page', [
            'subtotal' => $this->getSubtotal(),
            'shippingCost' => $this->getShippingCost(),
            'total' => $this->getTotal(),
            'checkoutShop' => $shop,
            'deliveryAvailable' => $this->canDeliver(),
            'deliveryQuote' => $quote,
            'deliveryDistanceLabel' => $quote
                ? DeliveryCostCalculator::formatDistance($quote['distance_meters'])
                : null,
        ]);
    }
}
