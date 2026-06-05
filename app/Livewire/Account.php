<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Account extends Component
{
    public $activeTab = 'dasbor'; // dasbor, pesanan, alamat, voucher, akun
    
    // Order Search
    public $searchPesanan = '';

    // Addresses
    public $showAddressForm = false;
    public $editingAddressId = null;
    public $addressForm = [
        'title' => '',
        'recipient_name' => '',
        'phone' => '',
        'full_address' => '',
        'province' => '',
        'city' => '',
        'district' => '',
        'postal_code' => '',
        'is_primary' => false,
    ];    // Profile fields
    public $name;
    public $email;
    
    // Password fields
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->to('/login');
        }
        
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'pesanan') {
            $this->searchPesanan = '';
        }
    }

    public function updatedSearchPesanan()
    {
        // Trigger re-render for search
    }

    // Address Methods
    public function toggleAddressForm()
    {
        $this->showAddressForm = !$this->showAddressForm;
        if (!$this->showAddressForm) {
            $this->resetAddressForm();
        }
    }

    public function resetAddressForm()
    {
        $this->editingAddressId = null;
        $this->addressForm = [
            'title' => '',
            'recipient_name' => '',
            'phone' => '',
            'full_address' => '',
            'province' => '',
            'city' => '',
            'district' => '',
            'postal_code' => '',
            'is_primary' => false,
        ];
    }

    public function editAddress($id)
    {
        $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($id);
        $this->editingAddressId = $address->id;
        $this->addressForm = $address->toArray();
        $this->showAddressForm = true;
    }

    public function deleteAddress($id)
    {
        \App\Models\UserAddress::where('user_id', Auth::id())->where('id', $id)->delete();
        session()->flash('address_success', 'Alamat berhasil dihapus.');
    }

    public function setPrimaryAddress($id)
    {
        \App\Models\UserAddress::where('user_id', Auth::id())->update(['is_primary' => false]);
        \App\Models\UserAddress::where('user_id', Auth::id())->where('id', $id)->update(['is_primary' => true]);
        session()->flash('address_success', 'Alamat utama diperbarui.');
    }

    public function saveAddress()
    {
        $this->validate([
            'addressForm.recipient_name' => 'required|string|max:255',
            'addressForm.phone' => 'required|string|max:20',
            'addressForm.full_address' => 'required|string',
            'addressForm.province' => 'required|string',
            'addressForm.city' => 'required|string',
        ]);

        if ($this->addressForm['is_primary']) {
            \App\Models\UserAddress::where('user_id', Auth::id())->update(['is_primary' => false]);
        }

        if ($this->editingAddressId) {
            $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($this->editingAddressId);
            $address->update($this->addressForm);
        } else {
            // If first address, make it primary automatically
            $count = \App\Models\UserAddress::where('user_id', Auth::id())->count();
            if ($count === 0) {
                $this->addressForm['is_primary'] = true;
            }
            
            Auth::user()->addresses()->create($this->addressForm);
        }

        $this->toggleAddressForm();
        session()->flash('address_success', 'Alamat berhasil disimpan.');
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        session()->flash('profile_success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = \Illuminate\Support\Facades\Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_success', 'Kata sandi berhasil diubah.');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/login');
    }

    public function getOrdersProperty()
    {
        $query = Order::with(['items.product', 'items.variant.attributeOptions.attribute'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if (!empty($this->searchPesanan)) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->searchPesanan . '%')
                  ->orWhereHas('items.product', function($q2) {
                      $q2->where('name', 'like', '%' . $this->searchPesanan . '%');
                  });
            });
        }

        return $query->get();
    }

    public function getDashboardStatsProperty()
    {
        $orders = Order::where('user_id', Auth::id())->get();
        return [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->whereIn('status', ['paid', 'packed', 'sent', 'completed'])->sum('grand_total'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
        ];
    }

    public function getAddressesProperty()
    {
        return \App\Models\UserAddress::where('user_id', Auth::id())
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getVouchersProperty()
    {
        // Simple logic to get global valid vouchers for now
        return \App\Models\Coupon::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>', now());
            })
            ->where(function($q) {
                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.account')->layout('components.layouts.app', [
            'title' => 'Akun Saya'
        ]);
    }
}
