<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PostComment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostCommentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PostComment');
    }

    public function view(AuthUser $authUser, PostComment $postComment): bool
    {
        return $authUser->can('View:PostComment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PostComment');
    }

    public function update(AuthUser $authUser, PostComment $postComment): bool
    {
        return $authUser->can('Update:PostComment');
    }

    public function delete(AuthUser $authUser, PostComment $postComment): bool
    {
        return $authUser->can('Delete:PostComment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PostComment');
    }

    public function restore(AuthUser $authUser, PostComment $postComment): bool
    {
        return $authUser->can('Restore:PostComment');
    }

    public function forceDelete(AuthUser $authUser, PostComment $postComment): bool
    {
        return $authUser->can('ForceDelete:PostComment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PostComment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PostComment');
    }

    public function replicate(AuthUser $authUser, PostComment $postComment): bool
    {
        return $authUser->can('Replicate:PostComment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PostComment');
    }

}