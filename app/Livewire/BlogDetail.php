<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class BlogDetail extends Component
{
    public $slug;
    public $post;
    
    // Comment fields
    public $name = '';
    public $email = '';
    public $commentContent = '';
    public $replyTo = null;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->with(['author', 'category', 'tags'])
            ->firstOrFail();
            
        if (auth()->check()) {
            $this->name = auth()->user()->name;
            $this->email = auth()->user()->email;
        }
    }
    
    public function setReply($commentId)
    {
        $this->replyTo = $commentId;
    }
    
    public function cancelReply()
    {
        $this->replyTo = null;
    }
    
    public function postComment()
    {
        $this->validate([
            'commentContent' => 'required|string|max:1000',
            'name' => auth()->check() ? 'nullable' : 'required|string|max:255',
            'email' => auth()->check() ? 'nullable' : 'required|email|max:255',
        ]);

        \App\Models\PostComment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id() ?? null,
            'parent_id' => $this->replyTo,
            'guest_name' => auth()->check() ? auth()->user()->name : $this->name,
            'guest_email' => auth()->check() ? auth()->user()->email : $this->email,
            'content' => $this->commentContent,
            'is_approved' => false, // Require moderation by default
        ]);

        $this->reset(['commentContent', 'replyTo']);
        
        session()->flash('message', 'Komentar Anda telah dikirim dan menunggu persetujuan moderator.');
    }

    public function render()
    {
        $relatedPosts = Post::where('is_published', true)
            ->where('id', '!=', $this->post->id)
            ->where(function ($query) {
                if ($this->post->post_category_id) {
                    $query->where('post_category_id', $this->post->post_category_id);
                }
            })
            ->limit(3)
            ->get();
            
        $comments = \App\Models\PostComment::where('post_id', $this->post->id)
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) {
                $query->where('is_approved', true)->with('user');
            }, 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $title = $this->post->meta_title ?? $this->post->title;
        $description = $this->post->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($this->post->content), 160);

        return view('livewire.blog-detail', [
            'relatedPosts' => $relatedPosts,
            'comments' => $comments,
        ])->layout('components.layouts.app', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
