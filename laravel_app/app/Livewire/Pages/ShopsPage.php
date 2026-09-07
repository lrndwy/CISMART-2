<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

class ShopsPage extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortBy = 'rating';

    #[Url]
    public string $location = 'all';

    #[Url]
    public string $category = 'all';

    #[Url]
    public string $minRating = '';

    public string $viewMode = 'grid';
    public bool $showFilters = false;

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

    public function updatedLocation()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedMinRating()
    {
        $this->resetPage();
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

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->location = 'all';
        $this->category = 'all';
        $this->minRating = '';
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function getFilteredShopsProperty()
    {
        $query = Shop::query()
            ->where('status', 'approved')
            ->withCount(['products' => function ($q) {
                $q->where('status', 'published');
            }]);

        // Search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . strtolower($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(address) LIKE ?', [$searchTerm]);
            });
        }

        // Location filter
        if ($this->location && $this->location !== 'all') {
            $query->where('address', 'like', '%' . $this->location . '%');
        }

        // Category filter (based on description - you can enhance this)
        if ($this->category && $this->category !== 'all') {
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($this->category) . '%']);
        }

        // Rating filter
        if (!empty($this->minRating)) {
            $minRating = (float)$this->minRating;
            $query->where('rating', '>=', $minRating);
        }

        // Sort shops
        switch ($this->sortBy) {
            case 'products':
                $query->orderBy('products_count', 'desc');
                break;
            case 'reviews':
                $query->orderBy('total_reviews', 'desc');
                break;
            case 'response':
                $query->orderBy('response_rate', 'desc');
                break;
            case 'rating':
            default:
                $query->orderBy('rating', 'desc');
                break;
        }

        return $query->paginate(12);
    }

    public function render()
    {
        $locations = Shop::select('address')
            ->distinct()
            ->whereNotNull('address')
            ->where('status', 'approved')
            ->orderBy('address')
            ->pluck('address');

        $categories = [
            ['id' => 'all', 'name' => 'Semua Kategori'],
            ['id' => 'batik', 'name' => 'Batik & Tekstil'],
            ['id' => 'kerajinan', 'name' => 'Kerajinan Tangan'],
            ['id' => 'makanan', 'name' => 'Makanan & Minuman'],
            ['id' => 'souvenir', 'name' => 'Souvenir'],
            ['id' => 'fashion', 'name' => 'Fashion'],
        ];

        $sortOptions = [
            ['value' => 'rating', 'label' => 'Rating Tertinggi'],
            ['value' => 'products', 'label' => 'Produk Terbanyak'],
            ['value' => 'reviews', 'label' => 'Ulasan Terbanyak'],
            ['value' => 'response', 'label' => 'Respon Tercepat'],
            ['value' => 'newest', 'label' => 'Terbaru'],
        ];

        return view('livewire.pages.shops-page', [
            'filteredShops' => $this->filteredShops,
            'locations' => $locations,
            'categories' => $categories,
            'sortOptions' => $sortOptions,
        ]);
    }
}
