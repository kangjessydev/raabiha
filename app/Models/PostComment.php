<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'guest_name', 'guest_email', 'content', 'is_approved',
        'is_edited_by_admin', 'admin_edit_reason'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }}
