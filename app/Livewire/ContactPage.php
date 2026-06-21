<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Inquiry;
use App\Models\SiteSetting;

class ContactPage extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $subject = '';
    public $message = '';
    public $channel = 'email'; // Default 'email', can be 'whatsapp'

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => $this->channel === 'email' ? 'required|email|max:255' : 'nullable|email|max:255',
            'phone' => $this->channel === 'whatsapp' ? 'required|string|max:20' : 'nullable|string|max:20',
            'subject' => 'required|string',
            'message' => 'required|string',
        ], [
            'email.required' => 'Silakan isi alamat email Anda.',
            'phone.required' => 'Silakan isi nomor WhatsApp Anda.',
        ]);

        // Clean up the non-selected contact channel
        if ($this->channel === 'email') {
            $this->phone = '';
        } else {
            $this->email = '';
        }

        // Check SiteSettings if we should save to DB based on subject
        $settings = SiteSetting::where('key', 'contact_subjects')->first();
        $saveToDb = true;

        if ($settings && $settings->value) {
            $subjects = json_decode($settings->value, true);
            if (is_array($subjects)) {
                foreach ($subjects as $s) {
                    if ($s['subject'] === $this->subject) {
                        $saveToDb = isset($s['save_to_db']) ? (bool) $s['save_to_db'] : true;
                        break;
                    }
                }
            }
        }

        if ($saveToDb) {
            Inquiry::create([
                'name' => $this->name,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'subject' => $this->subject,
                'message' => $this->message,
                'channel' => $this->channel,
                'status' => 'new',
            ]);
        }

        $phoneSetting = SiteSetting::where('key', 'contact_phone')->first()->value ?? '6281234567890';
        $phoneClean = preg_replace('/[^0-9]/', '', $phoneSetting);
        if (str_starts_with($phoneClean, '0')) {
            $phoneClean = '62' . substr($phoneClean, 1);
        }
        
        $waMessage = "Halo Raabiha, nama saya {$this->name}.\n\nSubjek: {$this->subject}\n\n{$this->message}";
        if ($this->email) {
            $waMessage .= "\n\nEmail saya: {$this->email}";
        }
        if ($this->phone) {
            $waMessage .= "\n\nWA saya: {$this->phone}";
        }
        
        $waUrl = "https://wa.me/{$phoneClean}?text=" . urlencode($waMessage);
        
        $this->reset(['name', 'email', 'phone', 'subject', 'message', 'channel']);
        
        return redirect()->away($waUrl);
    }

    public function render()
    {
        $settings = SiteSetting::where('key', 'contact_subjects')->first();
        $subjects = [];
        
        if ($settings && $settings->value) {
            $decoded = json_decode($settings->value, true);
            if (is_array($decoded)) {
                $subjects = array_column($decoded, 'subject');
            }
        }

        if (empty($subjects)) {
            $subjects = ['GENERAL INQUIRY', 'WHOLESALE', 'PRESS & MEDIA'];
        }

        return view('livewire.contact-page', [
            'subjects' => $subjects
        ])->layout('components.layouts.app', [
            'title' => 'Lokasi & Kontak',
            'description' => 'Hubungi Raabiha untuk pertanyaan umum, kemitraan grosir, layanan pelanggan, atau kunjungi lokasi showroom kami.'
        ]);
    }
}
