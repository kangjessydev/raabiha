<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Cashflow;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashflowPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Cashflow');
    }

    public function view(AuthUser $authUser, Cashflow $cashflow): bool
    {
        return $authUser->can('View:Cashflow');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Cashflow');
    }

    public function update(AuthUser $authUser, Cashflow $cashflow): bool
    {
        return $authUser->can('Update:Cashflow');
    }

    public function delete(AuthUser $authUser, Cashflow $cashflow): bool
    {
        return $authUser->can('Delete:Cashflow');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Cashflow');
    }

    public function restore(AuthUser $authUser, Cashflow $cashflow): bool
    {
        return $authUser->can('Restore:Cashflow');
    }

    public function forceDelete(AuthUser $authUser, Cashflow $cashflow): bool
    {
        return $authUser->can('ForceDelete:Cashflow');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Cashflow');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Cashflow');
    }

    public function replicate(AuthUser $authUser, Cashflow $cashflow): bool
    {
        return $authUser->can('Replicate:Cashflow');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Cashflow');
    }

}