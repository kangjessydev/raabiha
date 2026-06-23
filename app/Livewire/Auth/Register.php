<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public bool $agree_terms = false;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
        'agree_terms' => 'accepted',
    ];

    protected array $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal harus 8 karakter.',
        'agree_terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan untuk mendaftar.',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Fire registered event to trigger verification email
        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.app', [
                'title' => 'Daftar Akun',
                'robots' => 'noindex, nofollow'
            ]);
    }
}
