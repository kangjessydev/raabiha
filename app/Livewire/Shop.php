<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;

#[Lazy]
class Shop extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public $search = '';

    #[Url(keep: true)]
    public $sort = 'default';

    #[Url(keep: true)]
    public $selectedCategories = [];

    #[Url(keep: true)]
    public $selectedSizes = [];

    #[Url(keep: true)]
    public $selectedColors = [];

    #[Url(keep: true)]
    public $maxPrice = 4000000;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatedSelectedSizes()
    {
        $this->resetPage();
    }

    public function updatedSelectedColors()
    {
        $this->resetPage();
    }

    public function updatedMaxPrice()
    {
        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::with(['category', 'variants.attributeOptions'])->where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->selectedCategories)) {
            $query->whereIn('category_id', $this->selectedCategories);
        }

        if ($this->maxPrice) {
            $query->where(function($q) {
                $q->where('price', '<=', $this->maxPrice)
                  ->orWhereHas('variants', function($qv) {
                      $qv->where('is_price_override', true)
                         ->where('price', '<=', $this->maxPrice);
                  });
            });
        }

        if (!empty($this->selectedSizes)) {
            $query->where(function($q) {
                $q->whereHas('variants.attributeOptions', function($qo) {
                    $qo->where(function($sub) {
                        foreach ($this->selectedSizes as $size) {
                            $sub->orWhere('slug', 'like', '%' . $size . '%')
                               ->orWhere('value', 'like', '%' . $size . '%');
                        }
                    });
                })
                ->orWhereHas('variants', function($qo) {
                    $qo->where(function($sub) {
                        foreach ($this->selectedSizes as $size) {
                            $parts = explode('/', $size);
                            foreach ($parts as $part) {
                                $sub->orWhere('name', 'like', '%' . $part . '%')
                                   ->orWhere('sku', 'like', '%' . $part . '%');
                            }
                        }
                    });
                });
            });
        }

        if (!empty($this->selectedColors)) {
            $query->where(function($q) {
                $q->whereHas('variants.attributeOptions', function($qo) {
                    $qo->where(function($sub) {
                        foreach ($this->selectedColors as $color) {
                            $sub->orWhere('slug', 'like', '%' . $color . '%')
                               ->orWhere('value', 'like', '%' . $color . '%');
                        }
                    });
                })
                ->orWhereHas('variants', function($qo) {
                    $qo->where(function($sub) {
                        foreach ($this->selectedColors as $color) {
                            $sub->orWhere('name', 'like', '%' . $color . '%')
                               ->orWhere('sku', 'like', '%' . $color . '%');
                        }
                    });
                });
            });
        }

        switch ($this->sort) {
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return view('livewire.shop', [
            'products' => $query->paginate(12),
            'categories' => Category::where('is_active', true)->get()
        ])->layout('components.layouts.app', [
            'title' => 'Katalog Produk'
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12 md:py-16">
            <div class="animate-pulse flex flex-col gap-8">
                <div class="h-16 bg-[#e5e2de] w-1/3 mb-4"></div>
                <div class="h-4 bg-[#e5e2de] w-1/2 mb-12"></div>
                <div class="flex gap-12">
                    <div class="hidden lg:block w-64 h-[500px] bg-[#e5e2de]"></div>
                    <div class="flex-1 grid grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="aspect-[4/5] bg-[#e5e2de]"></div>
                        <div class="aspect-[4/5] bg-[#e5e2de]"></div>
                        <div class="aspect-[4/5] bg-[#e5e2de]"></div>
                        <div class="aspect-[4/5] bg-[#e5e2de]"></div>
                        <div class="aspect-[4/5] bg-[#e5e2de]"></div>
                        <div class="aspect-[4/5] bg-[#e5e2de]"></div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
