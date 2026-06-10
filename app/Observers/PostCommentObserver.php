<?php

namespace App\Observers;

use App\Models\PostComment;
use App\Models\User;
use Filament\Notifications\Notification;

class PostCommentObserver
{
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
            Notification::make()
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->iconColor('warning')
                ->title($isReply ? '💬 Balasan Komentar Baru' : '💬 Komentar Baru di Blog')
                ->body("**{$authorName}** berkomentar di \"{$postTitle}\": \"{$excerpt}\"")
                ->sendToDatabase($admin);
        }
    }
}
