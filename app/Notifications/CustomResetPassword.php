<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use App\Mail\StoreMail;

class CustomResetPassword extends BaseResetPassword
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $greeting = 'Halo, ' . $notifiable->name . '!';
        $messageBody = 'Anda menerima email ini karena kami menerima permintaan atur ulang (reset) password untuk akun Anda. Tautan reset password ini akan kedaluwarsa dalam 60 menit. Jika Anda tidak meminta perubahan ini, silakan abaikan email ini.';

        return (new StoreMail('Atur Ulang Password Akun Anda', 'emails.layout', [
            'greeting' => $greeting,
            'messageBody' => $messageBody,
            'actionUrl' => $resetUrl,
            'actionText' => 'Reset Password'
        ]))->to($notifiable->email);
    }
}
