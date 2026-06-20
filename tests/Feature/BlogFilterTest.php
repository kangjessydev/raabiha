<?php

namespace Tests\Feature;

use App\Livewire\Blog;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_posts_by_category()
    {
        // 1. Create a user
        $user = User::factory()->create();

        // 2. Create categories
        $categoryA = PostCategory::create([
            'name' => 'Category A',
            'slug' => 'category-a',
        ]);
        $categoryB = PostCategory::create([
            'name' => 'Category B',
            'slug' => 'category-b',
        ]);

        // 3. Create posts
        $postA = Post::create([
            'user_id' => $user->id,
            'post_category_id' => $categoryA->id,
            'title' => 'Post A Title',
            'slug' => 'post-a-title',
            'content' => 'Content for post A',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $postB = Post::create([
            'user_id' => $user->id,
            'post_category_id' => $categoryB->id,
            'title' => 'Post B Title',
            'slug' => 'post-b-title',
            'content' => 'Content for post B',
            'is_published' => true,
            'published_at' => now(),
        ]);

        // 4. Test Livewire filter
        Livewire::test(Blog::class)
            ->assertSee('Post A Title')
            ->assertSee('Post B Title')
            ->call('setCategory', 'category-a')
            ->assertSet('category', 'category-a')
            ->assertSee('Post A Title')
            ->assertDontSee('Post B Title');
    }
}
