<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Salespage;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalespagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Salespage');
    }

    public function view(AuthUser $authUser, Salespage $salespage): bool
    {
        return $authUser->can('View:Salespage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Salespage');
    }

    public function update(AuthUser $authUser, Salespage $salespage): bool
    {
        return $authUser->can('Update:Salespage');
    }

    public function delete(AuthUser $authUser, Salespage $salespage): bool
    {
        return $authUser->can('Delete:Salespage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Salespage');
    }

    public function restore(AuthUser $authUser, Salespage $salespage): bool
    {
        return $authUser->can('Restore:Salespage');
    }

    public function forceDelete(AuthUser $authUser, Salespage $salespage): bool
    {
        return $authUser->can('ForceDelete:Salespage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Salespage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Salespage');
    }

    public function replicate(AuthUser $authUser, Salespage $salespage): bool
    {
        return $authUser->can('Replicate:Salespage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Salespage');
    }

}