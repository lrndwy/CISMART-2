<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Services\DeliveryCostCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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

    /**
     * Item terpilih (snapshot dari session cart). Harga selalu disinkronkan
     * ulang dari database — nilai di session hanya untuk tampilan.
     */
    public array $checkoutItems = [];

    /** Nomor order yang baru dibuat (satu order per toko). */
    public array $createdOrderNumbers = [];

    protected ?Shop $cachedCheckoutShop = null;

    protected bool $checkoutShopLoaded = false;

    public function mount()
    {
        if (! Auth::check()) {
            return redirect()->route('login', ['redirect' => '/checkout']);
        }

        $checkoutItemIds = session()->get('checkout_items', []);
        $cart = session()->get('cart', []);

        if (empty($checkoutItemIds)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong');
        }

        $this->checkoutItems = array_filter($cart, function ($key) use ($checkoutItemIds) {
            return in_array($key, $checkoutItemIds);
        }, ARRAY_FILTER_USE_KEY);

        if (empty($this->checkoutItems)) {
            return redirect()->route('cart.index')->with('error', 'Tidak ada produk yang dipilih');
        }

        $this->syncCheckoutItemsWithDatabase();

        if (empty($this->checkoutItems)) {
            return redirect()->route('cart.index')->with('error', 'Produk yang dipilih sudah tidak tersedia');
        }

        $this->name = Auth::user()->name ?? '';
        $this->phone = Auth::user()->phone ?? '';
        $this->address = Auth::user()->address ?? '';

        $this->initializeOptions();
        $this->shippingMethod = $this->canDeliver() ? 'delivery' : 'pickup';
    }

    /**
     * Sinkronkan nama/harga/gambar di keranjang dengan data produk terbaru.
     * Produk yang sudah tidak dijual (unpublished, toko tidak approved,
     * dihapus) dibuang dari pilihan checkout.
     */
    public function syncCheckoutItemsWithDatabase(): void
    {
        $products = $this->availableProducts();

        $this->checkoutItems = collect($this->checkoutItems)
            ->filter(fn ($item, $id) => $products->has((int) $id))
            ->map(function ($item, $id) use ($products) {
                $product = $products->get((int) $id);
                $item['product_id'] = $product->id;
                $item['name'] = $product->name;
                $item['price'] = (float) $product->price;
                $item['image'] = $product->first_image;
                $item['shop_id'] = $product->shop_id;
                $item['shop_name'] = $product->shop?->name ?? $item['shop_name'] ?? '';

                return $item;
            })
            ->all();
    }

    /**
     * Produk yang masih boleh dibeli (published & toko approved).
     */
    protected function availableProducts()
    {
        return Product::query()
            ->with('shop')
            ->published()
            ->whereIn('id', array_keys($this->checkoutItems))
            ->get()
            ->keyBy('id');
    }

    /**
     * Item terotorisasi: produk dari DB + kuantitas dari session cart.
     *
     * @return array<int, array{product: Product, quantity: int, price: float}>
     */
    protected function resolvedItems(): array
    {
        $cart = session()->get('cart', []);
        $products = $this->availableProducts();
        $resolved = [];

        foreach (array_keys($this->checkoutItems) as $id) {
            $product = $products->get((int) $id);

            if (! $product) {
                continue;
            }

            $quantity = max(1, (int) ($cart[$id]['quantity'] ?? $this->checkoutItems[$id]['quantity'] ?? 1));

            $resolved[(int) $id] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => (float) $product->price,
            ];
        }

        return $resolved;
    }

    /**
     * Kelompokkan item per toko; satu order dibuat untuk tiap toko.
     *
     * @return array<int, array{shop: Shop, items: array<int, array{product: Product, quantity: int, price: float}>}>
     */
    protected function shopGroups(): array
    {
        $groups = [];

        foreach ($this->resolvedItems() as $id => $row) {
            $shopId = (int) $row['product']->shop_id;

            if (! $row['product']->shop) {
                continue;
            }

            $groups[$shopId] ??= ['shop' => $row['product']->shop, 'items' => []];
            $groups[$shopId]['items'][$id] = $row;
        }

        return $groups;
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
            $withoutDelivery = $this->shopsWithoutDelivery();

            if ($withoutDelivery !== []) {
                $this->addError('shippingMethod', 'Toko berikut belum mengatur pengiriman: '.implode(', ', $withoutDelivery).'. Pilih ambil di toko atau checkout terpisah.');

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
        if (! in_array($this->paymentMethod, ['qris', 'cod'], true)) {
            $this->dispatch('notify', [
                'message' => 'Mohon pilih metode pembayaran',
                'type' => 'error',
            ]);

            return;
        }

        if ($this->paymentMethod === 'qris') {
            $withoutQris = $this->shopsWithoutQris();

            if ($withoutQris !== []) {
                $this->dispatch('notify', [
                    'message' => 'Toko berikut belum mengatur QRIS: '.implode(', ', $withoutQris).'. Pilih bayar di tempat atau checkout terpisah per toko.',
                    'type' => 'error',
                ]);

                return;
            }
        }

        $this->step = 'review';
    }

    public function submitOrder()
    {
        if ($this->loading) {
            return;
        }

        $throttleKey = 'checkout:'.Auth::id();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $this->dispatch('notify', [
                'message' => 'Terlalu banyak percobaan checkout. Tunggu sebentar lalu coba lagi.',
                'type' => 'error',
            ]);

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $this->loading = true;

        // Metode pengiriman harus salah satu yang dikenal (anti tamper state).
        if (! in_array($this->shippingMethod, ['pickup', 'delivery'], true)) {
            $this->loading = false;
            $this->dispatch('notify', [
                'message' => 'Metode pengiriman tidak dikenal. Muat ulang halaman checkout.',
                'type' => 'error',
            ]);

            return;
        }

        try {
            $groups = $this->shopGroups();

            if (empty($groups)) {
                $this->dispatch('notify', [
                    'message' => 'Produk yang dipilih sudah tidak tersedia.',
                    'type' => 'error',
                ]);

                return;
            }

            // Metode pembayaran harus valid untuk SEMUA toko di checkout ini.
            if ($this->paymentMethod === 'qris') {
                $withoutQris = $this->shopsWithoutQris();

                if ($withoutQris !== []) {
                    $this->dispatch('notify', [
                        'message' => 'Toko berikut belum mengatur QRIS: '.implode(', ', $withoutQris).'. Pilih bayar di tempat atau checkout terpisah per toko.',
                        'type' => 'error',
                    ]);

                    return;
                }
            }

            // Harga berubah sejak masuk keranjang? Jangan pernah menagih harga lama.
            $changed = $this->priceChanges();

            if ($changed !== []) {
                $this->syncCheckoutItemsWithDatabase();
                $this->dispatch('notify', [
                    'message' => 'Harga produk berubah: '.implode(', ', $changed).'. Keranjang sudah diperbarui, silakan periksa kembali.',
                    'type' => 'error',
                ]);

                return;
            }

            $orders = DB::transaction(function () use ($groups) {
                $created = [];

                foreach ($groups as $shopId => $group) {
                    $shop = $group['shop'];
                    $subtotal = 0.0;

                    foreach ($group['items'] as $id => $row) {
                        $product = Product::query()
                            ->withTrashed()
                            ->lockForUpdate()
                            ->find($id);

                        if (! $product || $product->trashed() || $product->status !== 'published' || $product->stock < $row['quantity']) {
                            throw new \RuntimeException("Stok produk \"{$row['product']->name}\" tidak mencukupi. Sisa {$product?->stock}.");
                        }

                        $subtotal += (float) $product->price * $row['quantity'];
                    }

                    $shippingMethod = $this->shippingMethod === 'delivery' && $shop->hasDelivery()
                        ? 'delivery'
                        : 'pickup';

                    $quote = $shippingMethod === 'delivery' ? $this->quoteFor($shop) : null;
                    $shippingCost = (int) ($quote['cost'] ?? 0);

                    $paymentMethod = $this->paymentMethod === 'qris' && $shop->hasQris() ? 'qris' : 'cod';

                    $order = Order::create([
                        'user_id' => Auth::id(),
                        'shop_id' => $shop->id,
                        'shipping_name' => $this->name,
                        'shipping_phone' => $this->phone,
                        'shipping_address' => $this->address,
                        'shipping_city' => $this->city,
                        'shipping_postal_code' => $this->postalCode,
                        'shipping_notes' => $this->notes,
                        'delivery_latitude' => $shippingMethod === 'delivery' ? $this->deliveryLatitude : null,
                        'delivery_longitude' => $shippingMethod === 'delivery' ? $this->deliveryLongitude : null,
                        'delivery_distance_meters' => $quote['distance_meters'] ?? null,
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentMethod === 'qris' ? 'unpaid' : 'not_required',
                        'shipping_method' => $shippingMethod,
                        'subtotal' => $subtotal,
                        'shipping_cost' => $shippingCost,
                        'total' => $subtotal + $shippingCost,
                        'status' => Order::STATUS_PENDING,
                        'shop_whatsapp' => $shop->phone,
                    ]);

                    foreach ($group['items'] as $id => $row) {
                        $product = Product::query()->lockForUpdate()->find($id);

                        if (! $product || $product->stock < $row['quantity']) {
                            throw new \RuntimeException("Stok produk \"{$row['product']->name}\" tidak mencukupi.");
                        }

                        $product->decrement('stock', $row['quantity']);

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'shop_id' => $shop->id,
                            'product_name' => $product->name,
                            'shop_name' => $shop->name,
                            'product_image' => $product->first_image,
                            'price' => $product->price,
                            'quantity' => $row['quantity'],
                            'subtotal' => (float) $product->price * $row['quantity'],
                        ]);
                    }

                    if ($paymentMethod === 'qris') {
                        $order->update([
                            'qris_type' => $shop->qris_mode,
                            'qris_payload' => $shop->qrisPayloadFor($order->total, $order->order_number),
                        ]);
                    }

                    $created[] = $order;
                }

                return $created;
            });

            // Baru hapus dari keranjang setelah transaksi DB sukses.
            $cart = session()->get('cart', []);
            foreach (array_keys($this->checkoutItems) as $productId) {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
            session()->forget('checkout_items');

            $this->createdOrderNumbers = collect($orders)->pluck('order_number')->all();
            $this->dispatch('cart-updated');

            // Satu order & QRIS → langsung ke halaman pembayaran.
            if (count($orders) === 1 && $orders[0]->isQris()) {
                return redirect()->route('orders.pay', $orders[0]);
            }

            $this->step = 'success';
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->dispatch('notify', [
                'message' => 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.',
                'type' => 'error',
            ]);
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Nama produk yang harganya berubah dibanding snapshot keranjang.
     *
     * @return list<string>
     */
    protected function priceChanges(): array
    {
        $cart = session()->get('cart', []);
        $changed = [];

        foreach ($this->resolvedItems() as $id => $row) {
            $snapshot = (float) ($cart[$id]['price'] ?? $row['price']);

            if (abs($snapshot - $row['price']) > 0.001) {
                $changed[] = $row['product']->name;
            }
        }

        return $changed;
    }

    public function getSubtotal(): float
    {
        $total = 0.0;

        foreach ($this->resolvedItems() as $row) {
            $total += $row['price'] * $row['quantity'];
        }

        return $total;
    }

    protected function quoteFor(Shop $shop): ?array
    {
        if ($this->deliveryLatitude === null || $this->deliveryLatitude === ''
            || $this->deliveryLongitude === null || $this->deliveryLongitude === '') {
            return null;
        }

        return DeliveryCostCalculator::quote($shop, (float) $this->deliveryLatitude, (float) $this->deliveryLongitude);
    }

    /**
     * Total ongkir = jumlah ongkir tiap toko (order dipecah per toko).
     */
    public function getShippingCost(): float
    {
        if ($this->shippingMethod !== 'delivery') {
            return 0;
        }

        $total = 0;

        foreach ($this->shopGroups() as $group) {
            if (! $group['shop']->hasDelivery()) {
                continue;
            }

            $total += (int) ($this->quoteFor($group['shop'])['cost'] ?? 0);
        }

        return (float) $total;
    }

    /**
     * Quote untuk tampilan (hanya bila belanja dari satu toko).
     */
    public function getDeliveryQuote(): ?array
    {
        if ($this->shippingMethod !== 'delivery') {
            return null;
        }

        $groups = $this->shopGroups();

        if (count($groups) !== 1) {
            return null;
        }

        return $this->quoteFor(reset($groups)['shop']);
    }

    /**
     * Toko pada checkout ini yang belum mengaktifkan pengiriman.
     *
     * @return list<string>
     */
    protected function shopsWithoutDelivery(): array
    {
        return collect($this->shopGroups())
            ->reject(fn ($group) => $group['shop']->hasDelivery())
            ->map(fn ($group) => $group['shop']->name)
            ->values()
            ->all();
    }

    /**
     * Toko pada checkout ini yang belum mengatur QRIS.
     *
     * @return list<string>
     */
    protected function shopsWithoutQris(): array
    {
        return collect($this->shopGroups())
            ->reject(fn ($group) => $group['shop']->hasQris())
            ->map(fn ($group) => $group['shop']->name)
            ->values()
            ->all();
    }

    /**
     * Pengiriman hanya ditawarkan bila semua toko bisa mengirim.
     */
    public function canDeliver(): bool
    {
        $groups = $this->shopGroups();

        return $groups !== []
            && collect($groups)->every(fn ($group) => $group['shop']->hasDelivery());
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
            $groups = $this->shopGroups();
            $descriptions = collect($groups)->map(function ($group) {
                $shop = $group['shop'];

                return 'Rp '.number_format((int) $shop->delivery_rate, 0, ',', '.').
                    ' per '.number_format((int) $shop->delivery_unit_meters, 0, ',', '.').' m ('.$shop->name.')';
            })->implode('; ');

            $options[] = [
                'id' => 'delivery',
                'name' => 'Kirim ke lokasi',
                'desc' => $descriptions,
                'icon' => 'truck',
            ];
        }

        return $options;
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() + $this->getShippingCost();
    }

    /**
     * Toko tunggal pada checkout ini (null bila multi-toko).
     */
    public function checkoutShop(): ?Shop
    {
        if ($this->checkoutShopLoaded) {
            return $this->cachedCheckoutShop;
        }

        $groups = $this->shopGroups();
        $this->cachedCheckoutShop = count($groups) === 1 ? reset($groups)['shop'] : null;
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
        $quote = $this->getDeliveryQuote();

        return view('livewire.pages.checkout-page', [
            'subtotal' => $this->getSubtotal(),
            'shippingCost' => $this->getShippingCost(),
            'total' => $this->getTotal(),
            'checkoutShop' => $this->checkoutShop(),
            'shopCount' => count($this->shopGroups()),
            'deliveryAvailable' => $this->canDeliver(),
            'deliveryQuote' => $quote,
            'deliveryDistanceLabel' => $quote
                ? DeliveryCostCalculator::formatDistance($quote['distance_meters'])
                : null,
        ]);
    }
}