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
    public $channel = 'email'; // Default email, can be whatsapp

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string',
            'message' => 'required|string',
            'channel' => 'required|in:email,whatsapp',
        ]);

        if (empty($this->email) && empty($this->phone)) {
            session()->flash('error', 'Silakan isi salah satu: Email atau No. WhatsApp Anda.');
            return;
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
                'email' => $this->email,
                'phone' => $this->phone,
                'subject' => $this->subject,
                'message' => $this->message,
                'channel' => $this->channel,
                'status' => 'new',
            ]);
        }

        if ($this->channel === 'whatsapp') {
            $phoneSetting = SiteSetting::where('key', 'contact_phone')->first()->value ?? '6281234567890';
            $phoneClean = preg_replace('/[^0-9]/', '', $phoneSetting);
            if (str_starts_with($phoneClean, '0')) {
                $phoneClean = '62' . substr($phoneClean, 1);
            }
            
            $waMessage = "Halo Raabiha, nama saya {$this->name}.\n\nSubjek: {$this->subject}\n\n{$this->message}";
            $waUrl = "https://wa.me/{$phoneClean}?text=" . urlencode($waMessage);
            
            return redirect()->away($waUrl);
        }

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
        session()->flash('success', 'Pesan Anda telah berhasil dikirim! Kami akan segera menghubungi Anda melalui Email.');
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
        ])->layout('components.layouts.app');
    }
}
