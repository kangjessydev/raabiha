<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SalesPage;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesPagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SalesPage');
    }

    public function view(AuthUser $authUser, SalesPage $salesPage): bool
    {
        return $authUser->can('View:SalesPage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SalesPage');
    }

    public function update(AuthUser $authUser, SalesPage $salesPage): bool
    {
        return $authUser->can('Update:SalesPage');
    }

    public function delete(AuthUser $authUser, SalesPage $salesPage): bool
    {
        return $authUser->can('Delete:SalesPage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SalesPage');
    }

    public function restore(AuthUser $authUser, SalesPage $salesPage): bool
    {
        return $authUser->can('Restore:SalesPage');
    }

    public function forceDelete(AuthUser $authUser, SalesPage $salesPage): bool
    {
        return $authUser->can('ForceDelete:SalesPage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SalesPage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SalesPage');
    }

    public function replicate(AuthUser $authUser, SalesPage $salesPage): bool
    {
        return $authUser->can('Replicate:SalesPage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SalesPage');
    }

}