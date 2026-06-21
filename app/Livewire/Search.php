<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Post;

class Search extends Component
{
    public $q = '';

    protected $queryString = [
        'q' => ['except' => ''],
    ];

    public function render()
    {
        $products = collect();
        $posts = collect();

        if (trim($this->q) !== '') {
            $products = Product::with(['category', 'variants'])
                ->where('is_active', true)
                ->where('is_hidden', false)
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->q . '%')
                          ->orWhere('description', 'like', '%' . $this->q . '%');
                })
                ->limit(12)
                ->get();

            $posts = Post::with(['category'])
                ->where('is_published', true)
                ->where(function($query) {
                    $query->where('title', 'like', '%' . $this->q . '%')
                          ->orWhere('content', 'like', '%' . $this->q . '%');
                })
                ->limit(12)
                ->get();
        }

        return view('livewire.search', [
            'products' => $products,
            'posts' => $posts,
        ])->layout('components.layouts.app', [
            'title' => 'Pencarian: ' . ($this->q ?: 'Semua'),
            'robots' => 'noindex, nofollow'
        ]);
    }
}
