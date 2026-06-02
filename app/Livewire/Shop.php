<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;

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
        ])->layout('components.layouts.app');
    }
}
