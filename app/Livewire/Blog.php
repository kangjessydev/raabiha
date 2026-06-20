<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Blog - Raabiha')]
class Blog extends Component
{
    use WithPagination;

    #[Url]
    public $category = 'all';

    public function setCategory($slug)
    {
        $this->category = $slug;
        $this->resetPage();
    }

    public function render()
    {
        $categories = PostCategory::all();

        $query = Post::where('is_published', true)
            ->with(['category', 'author', 'tags'])
            ->orderBy('published_at', 'desc');

        if ($this->category !== 'all') {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        $posts = $query->paginate(12);
        
        $featuredPost = null;
        if ($posts->currentPage() == 1 && $this->category === 'all' && $posts->count() > 0) {
            $featuredPost = $posts->first();
        }

        return view('livewire.blog', [
            'posts' => $posts,
            'categories' => $categories,
            'featuredPost' => $featuredPost,
        ]);
    }
}
