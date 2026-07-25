<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PosLogin extends Component
{
    public string $loginInput = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'loginInput' => 'required|string',
        'password'   => 'required|string|min:4',
    ];

    protected array $messages = [
        'loginInput.required' => 'Silakan isi Username atau Email Kasir.',
        'password.required'   => 'Silakan isi password Anda.',
        'password.min'        => 'Password minimal harus 4 karakter.',
    ];

    public function login()
    {
        $this->validate();

        $loginInput = trim($this->loginInput);
        $email = $loginInput;

        if (!str_contains($loginInput, '@')) {
            $matchedUser = \App\Models\User::where('email', 'like', $loginInput . '@%')->first();
            if ($matchedUser) {
                $email = $matchedUser->email;
            } else {
                $email = $loginInput . '@raabiha.com';
            }
        }

        if (Auth::attempt(['email' => $email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            $allowedRoles = ['kasir', 'super_admin', 'owner', 'manager', 'finance'];

            if (!$user->hasAnyRole($allowedRoles) && !in_array($user->role, $allowedRoles)) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                $this->addError('loginInput', 'Akun Anda tidak memiliki hak akses ke Terminal POS Kasir.');
                return;
            }

            return redirect()->intended(route('pos.index'));
        }

        $this->addError('loginInput', 'Kredensial atau password kasir yang Anda masukkan salah.');
    }

    public function render()
    {
        return view('livewire.auth.pos-login')
            ->layout('components.layouts.pos-auth', [
                'title' => 'Login Kasir POS - Raabiha',
            ]);
    }
}
