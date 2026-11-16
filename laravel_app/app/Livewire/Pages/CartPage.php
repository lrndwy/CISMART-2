<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use Livewire\Component;

class CartPage extends Component
{
    public array $selectedItems = [];

    public bool $selectAll = false;

    public function mount()
    {
        // Initialize with all items selected
        $cart = $this->getCart();
        $this->selectedItems = array_keys($cart);
        $this->selectAll = ! empty($cart);
    }

    public function getCart()
    {
        return session()->get('cart', []);
    }

    public function getTotalItems()
    {
        $cart = $this->getCart();

        return array_sum(array_column($cart, 'quantity'));
    }

    public function updateQuantity($productId, $quantity)
    {
        $cart = $this->getCart();
        $quantity = (int) $quantity;

        if (! isset($cart[$productId])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$productId]));
            $this->dispatch('cart-updated');

            return;
        }

        $product = Product::query()->with('shop')->published()->find($productId);

        if (! $product || $product->stock <= 0) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$productId]));
            $this->dispatch('cart-updated');
            $this->dispatch('notify', [
                'message' => 'Produk sudah tidak tersedia dan dihapus dari keranjang.',
                'type' => 'error',
            ]);

            return;
        }

        // Kuantitas tidak boleh melebihi stok; data harga disinkronkan.
        $quantity = min($quantity, max(1, (int) $product->stock));
        $cart[$productId]['quantity'] = $quantity;
        $cart[$productId]['name'] = $product->name;
        $cart[$productId]['price'] = (float) $product->price;
        $cart[$productId]['image'] = $product->first_image;
        $cart[$productId]['shop_name'] = $product->shop?->name ?? ($cart[$productId]['shop_name'] ?? '');

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');

        $this->dispatch('notify', [
            'message' => 'Jumlah produk diperbarui',
            'type' => 'success',
        ]);
    }

    public function removeFromCart($productId)
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);

            // Remove from selected items
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$productId]));

            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'message' => 'Produk dihapus dari keranjang',
                'type' => 'success',
            ]);
        }
    }

    public function toggleSelectItem($productId)
    {
        if (in_array($productId, $this->selectedItems)) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$productId]));
        } else {
            $this->selectedItems[] = $productId;
        }

        // Update selectAll status
        $cart = $this->getCart();
        $this->selectAll = count($this->selectedItems) === count($cart);
    }

    public function toggleSelectAll()
    {
        $cart = $this->getCart();

        if ($this->selectAll) {
            $this->selectedItems = [];
            $this->selectAll = false;
        } else {
            $this->selectedItems = array_keys($cart);
            $this->selectAll = true;
        }
    }

    public function getSelectedTotal()
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($this->selectedItems as $productId) {
            if (isset($cart[$productId])) {
                $item = $cart[$productId];
                $total += $item['price'] * $item['quantity'];
            }
        }

        return $total;
    }

    public function getSelectedCount()
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($this->selectedItems as $productId) {
            if (isset($cart[$productId])) {
                $count += $cart[$productId]['quantity'];
            }
        }

        return $count;
    }

    public function proceedToCheckout()
    {
        // Buang produk yang sudah tidak dijual sebelum checkout.
        $cart = $this->getCart();
        $available = Product::query()->published()
            ->whereIn('id', array_keys($cart))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $removed = array_diff(array_map('intval', array_keys($cart)), $available);

        if ($removed !== []) {
            foreach ($removed as $id) {
                unset($cart[$id]);
            }

            session()->put('cart', $cart);
            $this->selectedItems = array_values(array_intersect($this->selectedItems, $available));
            $this->dispatch('cart-updated');
            $this->dispatch('notify', [
                'message' => 'Beberapa produk sudah tidak tersedia dan dihapus dari keranjang.',
                'type' => 'error',
            ]);
        }

        if (empty($this->selectedItems)) {
            $this->dispatch('notify', [
                'message' => 'Pilih produk yang ingin dibeli',
                'type' => 'error',
            ]);

            return;
        }

        session()->put('checkout_items', $this->selectedItems);

        if (! auth()->check()) {
            return redirect()->route('login', ['redirect' => '/checkout']);
        }

        return redirect()->route('checkout.index');
    }

    public function render()
    {
        $cart = $this->getCart();

        return view('livewire.pages.cart-page', [
            'cart' => $cart,
            'totalItems' => $this->getTotalItems(),
            'selectedTotal' => $this->getSelectedTotal(),
            'selectedCount' => $this->getSelectedCount(),
        ]);
    }
}
