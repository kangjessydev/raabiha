<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use App\Mail\StoreMail;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $greeting = 'Halo, ' . $notifiable->name . '!';
        $messageBody = 'Terima kasih telah melakukan pendaftaran di Raabiha. Silakan lakukan verifikasi alamat email Anda dengan mengeklik tombol di bawah ini agar dapat mengakses penuh dasbor akun Anda.';

        return (new StoreMail('Verifikasi Alamat Email Anda', 'emails.layout', [
            'greeting' => $greeting,
            'messageBody' => $messageBody,
            'actionUrl' => $verificationUrl,
            'actionText' => 'Verifikasi Email'
        ]))->to($notifiable->email);
    }
}
