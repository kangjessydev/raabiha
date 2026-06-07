<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ResellerRegister extends Component
{
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'reseller',
            'reseller_status' => 'pending',
        ]);

        Auth::login($user);

        return redirect()->to('/reseller-welcome');
    }

    public function render()
    {
        return view('livewire.reseller-register')->layout('components.layouts.app', [
            'title' => 'Daftar Reseller'
        ]);
    }
}
