<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;

#[Lazy]
class Shop extends Component
{
    use WithPagination;

    public $search = '';
    public $sort = 'default';
    public $selectedCategories = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'default'],
        'selectedCategories' => ['except' => []],
    ];

    public function render()
    {
        $query = Product::with('category')->where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->selectedCategories)) {
            $query->whereIn('category_id', $this->selectedCategories);
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
