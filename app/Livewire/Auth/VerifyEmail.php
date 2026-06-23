<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class VerifyEmail extends Component
{
    public bool $verificationSent = false;

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }
    }

    public function resend()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        Auth::user()->sendEmailVerificationNotification();

        $this->verificationSent = true;

        session()->flash('status', 'verification-link-sent');
    }

    public function render()
    {
        return view('livewire.auth.verify-email')
            ->layout('components.layouts.app', [
                'title' => 'Verifikasi Email',
                'robots' => 'noindex, nofollow'
            ]);
    }
}
