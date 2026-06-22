<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderRequest');
    }

    public function view(AuthUser $authUser, OrderRequest $orderRequest): bool
    {
        return $authUser->can('View:OrderRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderRequest');
    }

    public function update(AuthUser $authUser, OrderRequest $orderRequest): bool
    {
        return $authUser->can('Update:OrderRequest');
    }

    public function delete(AuthUser $authUser, OrderRequest $orderRequest): bool
    {
        return $authUser->can('Delete:OrderRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OrderRequest');
    }

    public function restore(AuthUser $authUser, OrderRequest $orderRequest): bool
    {
        return $authUser->can('Restore:OrderRequest');
    }

    public function forceDelete(AuthUser $authUser, OrderRequest $orderRequest): bool
    {
        return $authUser->can('ForceDelete:OrderRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderRequest');
    }

    public function replicate(AuthUser $authUser, OrderRequest $orderRequest): bool
    {
        return $authUser->can('Replicate:OrderRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderRequest');
    }

}