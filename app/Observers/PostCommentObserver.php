<?php

namespace App\Observers;

use App\Models\PostComment;
use App\Models\User;
use Filament\Notifications\Notification;

class PostCommentObserver
{
    public function creating(PostComment $comment): void
    {
        $badWords = [
            'anjing', 'babi', 'bangsat', 'keparat', 'bajingan', 'kontol', 'memek', 'ngentot', 'jancok', 'asu',
            'perek', 'lonte', 'silit', 'tetek', 'toket', 'jembut', 'peler', 'pepek', 'tempik', 'titit', 'pler',
            'goblok', 'tolol', 'bego', 'gila', 'idiot', 'sinting', 'sarap', 'brengsek', 'tai', 'pantek',
            'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'cunt', 'dick', 'pussy', 'whore', 'slut', 'faggot',
            'motherfucker', 'cock', 'wank', 'crap'
        ];

        $isSpam = false;
        $hasBadWords = false;

        // Deteksi spam jika terdapat link atau tag tautan HTML
        if (preg_match('/(https?:\/\/|www\.|<a\s+href)/i', $comment->content)) {
            $isSpam = true;
        }

        // Deteksi & Sensor kata-kata buruk
        $cleanedContent = $comment->content;
        foreach ($badWords as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $cleanedContent)) {
                $hasBadWords = true;
                $asterisks = str_repeat('*', strlen($word));
                $cleanedContent = preg_replace($pattern, $asterisks, $cleanedContent);
            }
        }

        $comment->content = $cleanedContent;

        // Jika terdeteksi spam atau mengandung kata kotor, simpan sebagai tidak disetujui (memerlukan moderasi admin)
        // Jika bersih, otomatis setujui (langsung tampil di website)
        if ($isSpam || $hasBadWords) {
            $comment->is_approved = false;
        } else {
            $comment->is_approved = true;
        }
    }

    public function created(PostComment $comment): void
    {
        $post        = $comment->post;
        $authorName  = $comment->user?->name ?? $comment->guest_name ?? 'Anonim';
        $isReply     = filled($comment->parent_id);
        $excerpt     = \Str::limit($comment->content, 80);
        $postTitle   = $post?->title ?? 'artikel';

        $admins = User::role('super_admin')->get();
        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        foreach ($admins as $admin) {
            // Tentukan judul notifikasi apakah auto-approved atau butuh moderasi
            $title = $isReply ? '💬 Balasan Komentar Baru' : '💬 Komentar Baru di Blog';
            if (!$comment->is_approved) {
                $title .= ' (Butuh Moderasi)';
            } else {
                $title .= ' (Otomatis Tayang)';
            }

            Notification::make()
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->iconColor($comment->is_approved ? 'success' : 'warning')
                ->title($title)
                ->body("**{$authorName}** berkomentar di \"{$postTitle}\": \"{$excerpt}\"")
                ->sendToDatabase($admin);
        }
    }
}
