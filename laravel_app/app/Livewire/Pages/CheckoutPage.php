<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Services\DeliveryCostCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

    /** Alamat belum pernah disunting manual oleh pembeli. */
    public bool $addressIsUntouched = true;

    /** Alamat yang terpasang sekarang berasal dari titik peta. */
    public bool $addressFromMap = false;

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
                'icon' => 'qrcode',
                'desc' => 'Scan QR toko, lalu unggah bukti pembayaran',
            ],
            [
                'id' => 'cod',
                'name' => 'Bayar di Tempat',
                'icon' => 'cash',
                'desc' => 'Bayar saat ambil di toko atau saat pesanan diantar',
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

        // Alamat ikut terisi otomatis selama pembeli belum menyuntingnya sendiri.
        if ($this->addressIsUntouched || $this->addressFromMap) {
            $this->fillAddressFromMap(silent: true);
        }
    }

    public function updatedAddress(): void
    {
        // Pembeli menulis sendiri: berhenti menimpa alamatnya dari titik peta.
        $this->addressIsUntouched = false;
        $this->addressFromMap = false;
    }

    /**
     * Isi alamat lengkap dari titik peta yang sedang dipilih.
     */
    public function fillAddressFromMap(bool $silent = false): void
    {
        if (blank($this->deliveryLatitude) || blank($this->deliveryLongitude)) {
            return;
        }

        $latitude = (float) $this->deliveryLatitude;
        $longitude = (float) $this->deliveryLongitude;

        $address = $this->reverseGeocode($latitude, $longitude);

        if (blank($address)) {
            if (! $silent) {
                $this->dispatch('notify', [
                    'message' => 'Alamat tidak bisa dibaca otomatis. Silakan isi manual.',
                    'type' => 'info',
                ]);
            }

            return;
        }

        $this->address = $address;
        $this->addressFromMap = true;
        $this->addressIsUntouched = false;
    }

    /**
     * Terjemahkan koordinat menjadi alamat lewat Nominatim (OpenStreetMap).
     * Kegagalan layanan tidak boleh memblokir checkout — pembeli tetap bisa
     * menulis alamat manual.
     */
    protected function reverseGeocode(float $latitude, float $longitude): string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'CISMART/1.0 (+https://cismart.id)'])
                ->timeout(6)
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

            $address = $response->successful() ? $response->json('address') : null;
        } catch (\Throwable) {
            $address = null;
        }

        if (! is_array($address)) {
            return '';
        }

        return implode(', ', $this->composeAddress($address));
    }

    /**
     * Susun alamat dari paling spesifik ke paling umum, tanpa bagian berulang.
     * Data Nominatim untuk Indonesia sering mengisi "city" dengan level di
     * bawah kota ("Kelurahan X Kecamatan Y" / "Kelurahan X, Cilacap") — kalau
     * begitu pakai county sebagai kabupaten/kota.
     *
     * @param  array<string, mixed>  $address
     * @return list<string>
     */
    protected function composeAddress(array $address): array
    {
        $city = $address['municipality'] ?? $address['city'] ?? $address['town'] ?? null;

        if (is_string($city) && preg_match('/kelurahan|kecamatan|desa/i', $city)) {
            $city = null;
        }

        $candidates = [
            trim(($address['road'] ?? '').' '.($address['house_number'] ?? '')),
            $address['neighbourhood'] ?? null,
            $address['village'] ?? null,
            $address['suburb'] ?? null,
            $address['city_district'] ?? $address['district'] ?? null,
            $city,
            $address['county'] ?? null,
            $address['postcode'] ?? null,
        ];

        $parts = [];
        $used = [];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $candidate = trim($candidate);
            $tokens = $this->significantTokens($candidate);

            // Lewati bagian yang tidak menambah informasi baru, mis. kelurahan
            // yang sudah terkandung di nama RW.
            if ($tokens ===[] || array_diff($tokens, $used) === []) {
                continue;
            }

            $parts[] = $candidate;
            $used = array_merge($used, $tokens);
        }

        return $parts;
    }

    /**
     * Kata kunci alamat yang tidak membedakan satu tempat dengan tempat lain.
     *
     * @return list<string>
     */
    protected function significantTokens(string $value): array
    {
        $tokens = preg_split('/[^a-z0-9]+/', mb_strtolower($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_diff($tokens, [
            'rw', 'rt', 'kelurahan', 'kecamatan', 'desa', 'kel', 'kec', 'jalan', 'jl', 'gang', 'gg',
        ]));
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
                'shop_whatsapp' => $shop?->phone,
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

            $this->dispatch('cart-updated');

            if ($isQris) {
                return redirect()->route('orders.pay', $order);
            }

            $this->step = 'success';
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
                'icon' => 'store',
            ],
        ];

        if ($this->canDeliver()) {
            $shop = $this->checkoutShop();
            $options[] = [
                'id' => 'delivery',
                'name' => 'Kirim ke lokasi',
                'desc' => 'Ongkir Rp '.number_format((int) $shop->delivery_rate, 0, ',', '.').
                    ' per '.number_format((int) $shop->delivery_unit_meters, 0, ',', '.').' meter',
                'icon' => 'truck',
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
