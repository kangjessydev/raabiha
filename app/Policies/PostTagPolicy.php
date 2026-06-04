<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PostTag;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostTagPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PostTag');
    }

    public function view(AuthUser $authUser, PostTag $postTag): bool
    {
        return $authUser->can('View:PostTag');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PostTag');
    }

    public function update(AuthUser $authUser, PostTag $postTag): bool
    {
        return $authUser->can('Update:PostTag');
    }

    public function delete(AuthUser $authUser, PostTag $postTag): bool
    {
        return $authUser->can('Delete:PostTag');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PostTag');
    }

    public function restore(AuthUser $authUser, PostTag $postTag): bool
    {
        return $authUser->can('Restore:PostTag');
    }

    public function forceDelete(AuthUser $authUser, PostTag $postTag): bool
    {
        return $authUser->can('ForceDelete:PostTag');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PostTag');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PostTag');
    }

    public function replicate(AuthUser $authUser, PostTag $postTag): bool
    {
        return $authUser->can('Replicate:PostTag');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PostTag');
    }

}