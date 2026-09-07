<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsPage extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortBy = 'newest';

    #[Url]
    public string $category = 'all';

    #[Url]
    public string $minPrice = '';

    #[Url]
    public string $maxPrice = '';

    #[Url]
    public string $location = 'all';

    #[Url]
    public string $minRating = '';

    public string $viewMode = 'grid';

    public bool $mobileFilterOpen = false;

    public function mount()
    {
        $this->search = request()->query('search', '');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function updatedMinPrice()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function updatedMaxPrice()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function updatedLocation()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function updatedMinRating()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }

    public function updateViewMode($mode)
    {
        if (in_array($mode, ['grid', 'list'])) {
            $this->viewMode = $mode;
        }
    }

    public function toggleMobileFilter()
    {
        $this->mobileFilterOpen = ! $this->mobileFilterOpen;
    }

    public function closeMobileFilter()
    {
        $this->mobileFilterOpen = false;
    }

    public function clearFilters()
    {
        $this->category = 'all';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->location = 'all';
        $this->minRating = '';
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function setPriceRange($min, $max)
    {
        $this->minPrice = $min;
        $this->maxPrice = $max;
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function getFilteredProductsProperty()
    {
        $query = Product::query()
            ->where('status', 'published')
            ->with('shop', 'category');

        // Category filter
        if ($this->category && $this->category !== 'all') {
            $query->where('category_id', $this->category);
        }

        // Price range filter
        if (! empty($this->minPrice)) {
            $query->where('price', '>=', (int) $this->minPrice);
        }

        if (! empty($this->maxPrice)) {
            $query->where('price', '<=', (int) $this->maxPrice);
        }

        // Location filter
        if ($this->location && $this->location !== 'all') {
            $query->whereHas('shop', function ($q) {
                $q->where('address', 'like', '%'.$this->location.'%');
            });
        }

        // Rating filter
        if (! empty($this->minRating)) {
            $minRating = (float) $this->minRating;
            $query->where('rating', '>=', $minRating);
        }

        // Search filter
        if (! empty($this->search)) {
            $searchTerm = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm])
                    ->orWhereHas('category', function ($sq) use ($searchTerm) {
                        $sq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                    })
                    ->orWhereHas('shop', function ($sq) use ($searchTerm) {
                        $sq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                    });
            });
        }

        // Sort products
        switch ($this->sortBy) {
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('sold', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->paginate(12);
    }

    #[On('add-to-cart')]
    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (! $product || ! $product->isInStock()) {
            $this->dispatch('notify', [
                'message' => 'Produk tidak tersedia!',
                'type' => 'error',
            ]);

            return;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->first_image,
                'quantity' => 1,
                'shop_name' => $product->shop->name,
                'shop_id' => $product->shop_id,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', [
            'message' => 'Produk ditambahkan ke keranjang!',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        $categories = Category::withCount([
            'products' => function ($q) {
                $q->where('status', 'published');
            },
        ])->get();
        $locations = Shop::select('address')
            ->distinct()
            ->whereNotNull('address')
            ->where('status', 'approved')
            ->orderBy('address')
            ->pluck('address');

        return view('livewire.pages.products-page', [
            'filteredProducts' => $this->filteredProducts,
            'categories' => $categories,
            'locations' => $locations,
            'sortOptions' => [
                ['value' => 'newest', 'label' => 'Terbaru'],
                ['value' => 'popular', 'label' => 'Terpopuler'],
                ['value' => 'rating', 'label' => 'Rating Tertinggi'],
                ['value' => 'price-low', 'label' => 'Harga Terendah'],
                ['value' => 'price-high', 'label' => 'Harga Tertinggi'],
            ],
        ]);
    }
}
