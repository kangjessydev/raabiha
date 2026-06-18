<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefundRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefundRequest');
    }

    public function view(AuthUser $authUser, RefundRequest $refundRequest): bool
    {
        return $authUser->can('View:RefundRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefundRequest');
    }

    public function update(AuthUser $authUser, RefundRequest $refundRequest): bool
    {
        return $authUser->can('Update:RefundRequest');
    }

    public function delete(AuthUser $authUser, RefundRequest $refundRequest): bool
    {
        return $authUser->can('Delete:RefundRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefundRequest');
    }

    public function restore(AuthUser $authUser, RefundRequest $refundRequest): bool
    {
        return $authUser->can('Restore:RefundRequest');
    }

    public function forceDelete(AuthUser $authUser, RefundRequest $refundRequest): bool
    {
        return $authUser->can('ForceDelete:RefundRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefundRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefundRequest');
    }

    public function replicate(AuthUser $authUser, RefundRequest $refundRequest): bool
    {
        return $authUser->can('Replicate:RefundRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefundRequest');
    }

}