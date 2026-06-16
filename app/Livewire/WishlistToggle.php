<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class WishlistToggle extends Component
{
    public $product_id;
    public $isInWishlist = false;
    public $isDetail = false;

    public function mount($product_id, $isDetail = false)
    {
        $this->product_id = $product_id;
        $this->isDetail = $isDetail;
        $this->checkWishlistStatus();
    }

    public function checkWishlistStatus()
    {
        if (Auth::check()) {
            $this->isInWishlist = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $this->product_id)
                ->exists();
        }
    }

    public function toggleWishlist()
    {
        if (!Auth::check()) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Silakan login terlebih dahulu untuk menambahkan ke Wishlist.')
                ->warning()
                ->send();
            return redirect()->route('login');
        }

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $this->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $this->isInWishlist = false;
            Notification::make()
                ->title('Wishlist Diperbarui')
                ->body('Produk telah dihapus dari Wishlist Anda.')
                ->success()
                ->send();
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $this->product_id,
            ]);
            $this->isInWishlist = true;
            Notification::make()
                ->title('Wishlist Diperbarui')
                ->body('Produk berhasil ditambahkan ke Wishlist Anda!')
                ->success()
                ->send();
        }

        // Dispatch an event to update a wishlist counter in the navbar
        $this->dispatch('wishlist-updated');
    }

    public function render()
    {
        return view('livewire.wishlist-toggle');
    }
}
