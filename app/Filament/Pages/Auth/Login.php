<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username / Email')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $loginInput = $data['email'];

        if (! str_contains($loginInput, '@')) {
            $loginInput = $loginInput . '@raabiha.com';
        }

        return [
            'email' => $loginInput,
            'password' => $data['password'],
        ];
    }
}
