<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StoreMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailView;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $view = 'emails.layout', array $data = [])
    {
        $this->subject = $subject;
        $this->emailView = $view;
        $this->viewData = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Get settings
        $siteName = \App\Models\SiteSetting::where('key', 'site_name')->value('value') ?? 'Raabiha Olshop';
        $siteEmail = \App\Models\SiteSetting::where('key', 'store_email')->value('value') ?? 'support@raabiha.com';

        $this->viewData['siteName'] = $siteName;
        $this->viewData['siteEmail'] = $siteEmail;
        $this->viewData['title'] = $this->subject;

        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if (!empty($fromAddress)) {
            $this->from($fromAddress, $fromName ?? '');
        }

        return $this->view($this->emailView)
                    ->with($this->viewData);
    }
}
