<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pageview;
use App\Models\Product;
use App\Models\Post;
use App\Models\StaticPage;
use App\Models\SalesPage;
use Illuminate\Support\Facades\Log;

class TrackPageviews
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya melacak permintaan sukses GET, bukan AJAX/Livewire/API/Admin
        if ($request->isMethod('GET') 
            && $response->getStatusCode() === 200
            && !$request->ajax() 
            && !$request->wantsJson()) {
            
            $path = trim($request->getPathInfo(), '/');
            
            // Hiraukan halaman admin, api, livewire, dan file statis
            if (str_starts_with($path, 'admin') 
                || str_starts_with($path, 'livewire') 
                || str_starts_with($path, 'api') 
                || str_starts_with($path, 'filament')
                || str_starts_with($path, 'webhook')) {
                return $response;
            }

            try {
                $pageType = 'other';
                $modelId = null;
                $title = null;
                
                $segments = explode('/', $path);
                
                $pageNames = [
                    'about' => 'Tentang Kami',
                    'contact' => 'Lokasi & Kontak',
                    'shop' => 'Katalog Produk',
                    'blog' => 'Blog',
                    'gallery' => 'Galeri',
                    'cart' => 'Keranjang',
                    'checkout' => 'Checkout',
                    'account' => 'Dasbor Akun',
                    'login' => 'Login',
                    'register' => 'Register',
                    'forgot-password' => 'Lupa Password',
                    'reseller-register' => 'Daftar Reseller',
                    'reseller-dashboard' => 'Dasbor Reseller',
                    'promo' => 'Promo',
                    'search' => 'Pencarian',
                ];

                if ($path === '') {
                    $pageType = 'home';
                    $title = 'Beranda';
                } elseif (count($segments) >= 2 && $segments[0] === 'product') {
                    $slug = $segments[1];
                    $product = Product::where('slug', $slug)->first();
                    if ($product) {
                        $pageType = 'product';
                        $modelId = $product->id;
                        $title = $product->name;
                    }
                } elseif (count($segments) >= 2 && $segments[0] === 'blog') {
                    $slug = $segments[1];
                    $post = Post::where('slug', $slug)->first();
                    if ($post) {
                        $pageType = 'post';
                        $modelId = $post->id;
                        $title = $post->title;
                    }
                } else {
                    // Cek di array pre-defined
                    if (isset($pageNames[$path])) {
                        $pageType = 'other';
                        $title = $pageNames[$path];
                    } else {
                        // Cek StaticPage
                        $staticPage = StaticPage::where('slug', $path)->where('is_active', true)->first();
                        if ($staticPage) {
                            $pageType = 'static_page';
                            $modelId = $staticPage->id;
                            $title = $staticPage->title;
                        } else {
                            // Cek SalesPage
                            $salesPage = SalesPage::where('slug', $path)->where('is_active', true)->first();
                            if ($salesPage) {
                                $pageType = 'sales_page';
                                $modelId = $salesPage->id;
                                $title = $salesPage->title;
                            } else {
                                $pageType = 'other';
                                $title = ucwords(str_replace('-', ' ', $path));
                            }
                        }
                    }
                }

                // Simpan kunjungan
                Pageview::create([
                    'session_id' => session()->getId(),
                    'ip_address' => $request->ip(),
                    'url' => '/' . $path,
                    'page_type' => $pageType,
                    'model_id' => $modelId,
                    'title' => $title ?? ('/' . $path),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                ]);

            } catch (\Exception $e) {
                // Jangan menggagalkan request jika pencatatan analitik bermasalah
                Log::error("Error tracking pageview: " . $e->getMessage());
            }
        }

        return $response;
    }
}
