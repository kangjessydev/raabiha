<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Post;
use App\Models\StaticPage;
use App\Models\SalesPage;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Static routes
        $staticRoutes = [
            '/',
            '/about',
            '/contact',
            '/shop',
            '/blog',
            '/gallery',
            '/promo',
        ];

        foreach ($staticRoutes as $route) {
            $urls[] = [
                'loc' => url($route),
                'lastmod' => now()->startOfDay()->toIso8601String(),
                'changefreq' => 'daily',
                'priority' => ($route === '/') ? '1.0' : '0.8',
            ];
        }

        // Active and visible products
        $products = Product::where('is_active', true)
            ->where('is_hidden', false)
            ->get();

        foreach ($products as $product) {
            $urls[] = [
                'loc' => url('/product/' . $product->slug),
                'lastmod' => $product->updated_at->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        // Published blog posts
        $posts = Post::where('is_published', true)->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => url('/blog/' . $post->slug),
                'lastmod' => ($post->updated_at ?? $post->published_at ?? now())->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // Active static pages (catch-all)
        $staticPages = StaticPage::where('is_active', true)->get();
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => url('/' . $page->slug),
                'lastmod' => $page->updated_at->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        // Active sales pages (catch-all)
        $salesPages = SalesPage::where('is_active', true)->get();
        foreach ($salesPages as $page) {
            $urls[] = [
                'loc' => url('/' . $page->slug),
                'lastmod' => ($page->updated_at ?? $page->published_at ?? now())->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $content = view('sitemap', compact('urls'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
