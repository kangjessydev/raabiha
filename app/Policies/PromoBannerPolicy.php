<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PromoBanner;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromoBannerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PromoBanner');
    }

    public function view(AuthUser $authUser, PromoBanner $promoBanner): bool
    {
        return $authUser->can('View:PromoBanner');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PromoBanner');
    }

    public function update(AuthUser $authUser, PromoBanner $promoBanner): bool
    {
        return $authUser->can('Update:PromoBanner');
    }

    public function delete(AuthUser $authUser, PromoBanner $promoBanner): bool
    {
        return $authUser->can('Delete:PromoBanner');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PromoBanner');
    }

    public function restore(AuthUser $authUser, PromoBanner $promoBanner): bool
    {
        return $authUser->can('Restore:PromoBanner');
    }

    public function forceDelete(AuthUser $authUser, PromoBanner $promoBanner): bool
    {
        return $authUser->can('ForceDelete:PromoBanner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PromoBanner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PromoBanner');
    }

    public function replicate(AuthUser $authUser, PromoBanner $promoBanner): bool
    {
        return $authUser->can('Replicate:PromoBanner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PromoBanner');
    }

}