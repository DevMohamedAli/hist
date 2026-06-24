<?php

namespace Modules\Correspondence\Policies;

use Modules\Correspondence\Enums\CorrespondenceClassification;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class CorrespondencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('correspondence.view-own') || $user->can('correspondence.admin');
    }

    public function view(User $user, Correspondence $correspondence): bool
    {
        if (! $this->canViewClassification($user, $correspondence)) {
            return false;
        }

        return $user->can('correspondence.admin')
            || (int) $correspondence->sender_user_id === (int) $user->id
            || (int) $correspondence->approved_by === (int) $user->id
            || $correspondence->recipients()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('correspondence.create');
    }

    public function submit(User $user, Correspondence $correspondence): bool
    {
        return (int) $correspondence->sender_user_id === (int) $user->id
            && in_array($correspondence->status, [CorrespondenceStatus::Draft, CorrespondenceStatus::ReturnedForChanges], true);
    }

    public function dispatch(User $user, Correspondence $correspondence): bool
    {
        return $user->can('correspondence.send')
            && in_array($correspondence->status, [CorrespondenceStatus::Submitted, CorrespondenceStatus::Approved], true);
    }

    public function approve(User $user, Correspondence $correspondence): bool
    {
        return $user->can('correspondence.approve')
            && $correspondence->status === CorrespondenceStatus::PendingApproval;
    }

    public function reply(User $user, Correspondence $correspondence): bool
    {
        return $user->can('correspondence.reply') && $this->view($user, $correspondence);
    }

    public function complete(User $user, Correspondence $correspondence): bool
    {
        return $user->can('correspondence.complete') && $this->view($user, $correspondence);
    }

    public function archive(User $user, Correspondence $correspondence): bool
    {
        return $user->can('correspondence.archive') && $this->view($user, $correspondence);
    }

    private function canViewClassification(User $user, Correspondence $correspondence): bool
    {
        return match ($correspondence->classification) {
            CorrespondenceClassification::HighlyConfidential => $user->can('correspondence.view-highly-confidential'),
            CorrespondenceClassification::Confidential => $user->can('correspondence.view-confidential')
                || $user->can('correspondence.view-highly-confidential'),
            default => true,
        };
    }
}
