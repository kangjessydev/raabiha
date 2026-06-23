<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    public string $loginInput = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'loginInput' => 'required|string',
        'password' => 'required|string|min:6',
    ];

    protected array $messages = [
        'loginInput.required' => 'Silakan isi Username atau Email Anda.',
        'password.required' => 'Silakan isi password Anda.',
        'password.min' => 'Password minimal harus 6 karakter.',
    ];

    public function login()
    {
        $this->validate();

        $loginInput = trim($this->loginInput);
        $email = $loginInput;

        // Jika input tidak mengandung '@', cari user yang email-nya berawalan 'username@'
        if (! str_contains($loginInput, '@')) {
            $matchedUser = \App\Models\User::where('email', 'like', $loginInput . '@%')->first();
            if ($matchedUser) {
                $email = $matchedUser->email;
            } else {
                $email = $loginInput . '@raabiha.com'; // fallback default
            }
        }

        if (Auth::attempt(['email' => $email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();

            // Check if the user is an admin/staff role
            $adminRoles = ['super_admin', 'owner', 'kasir', 'finance', 'marketing', 'gudang', 'cs'];
            if ($user->hasAnyRole($adminRoles)) {
                return redirect()->intended(url('/admin'));
            }

            return redirect()->intended(route('account'));
        }

        $this->addError('loginInput', 'Kredensial yang Anda masukkan salah.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.app', [
                'title' => 'Masuk',
                'robots' => 'noindex, nofollow'
            ]);
    }
}
