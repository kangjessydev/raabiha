<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class Shop extends Component
{
    public $amount = 12;

    public function loadMore()
    {
        $this->amount += 12;
    }

    #[Url]
    public $search = '';

    #[Url]
    public $sort = 'default';

    #[Url]
    public $selectedCategories = [];

    #[Url]
    public $selectedAttributes = [];

    #[Url]
    public $maxPrice = 4000000;

    public function mount()
    {
        // Initialize attribute keys as empty arrays to ensure Livewire binds multiple checkboxes correctly
        $attributes = \App\Models\Attribute::all();
        foreach ($attributes as $attr) {
            if (!isset($this->selectedAttributes[$attr->id])) {
                $this->selectedAttributes[$attr->id] = [];
            } else if (!is_array($this->selectedAttributes[$attr->id])) {
                $this->selectedAttributes[$attr->id] = [$this->selectedAttributes[$attr->id]];
            }
        }
    }

    public function updatedSort()
    {
        // Pagination logic removed for loadMore
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedCategories = [];
        $this->maxPrice = 4000000;
        
        $attributes = \App\Models\Attribute::all();
        foreach ($attributes as $attr) {
            $this->selectedAttributes[$attr->id] = [];
        }
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

        if (!empty($this->selectedAttributes)) {
            foreach ($this->selectedAttributes as $attributeId => $optionIds) {
                if (empty($optionIds)) continue;
                if (!is_array($optionIds)) {
                    $optionIds = [$optionIds];
                }

                // Ensure the product has at least one variant with the selected option
                $query->whereHas('variants.attributeOptions', function($qo) use ($attributeId, $optionIds) {
                    $qo->where('attribute_options.attribute_id', $attributeId)
                       ->whereIn('attribute_options.id', $optionIds);
                });
            }
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
            'products' => $query->paginate($this->amount),
            'categories' => Category::where('is_active', true)->get(),
            'filterAttributes' => \App\Models\Attribute::with(['options' => function($q) {
                $q->whereHas('productVariants.product', function($q2) {
                    $q2->where('is_active', true);
                });
            }])->get()
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
