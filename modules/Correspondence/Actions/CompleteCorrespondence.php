<?php

namespace Modules\Correspondence\Actions;

use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class CompleteCorrespondence
{
    public function execute(Correspondence $correspondence, User $actor): Correspondence
    {
        $from = $correspondence->status->value;

        $correspondence->forceFill([
            'status' => CorrespondenceStatus::Completed,
            'closed_at' => now(),
        ])->save();

        $correspondence->statusHistories()->create([
            'actor_user_id' => $actor->id,
            'from_status' => $from,
            'to_status' => CorrespondenceStatus::Completed->value,
        ]);

        return $correspondence->fresh();
    }
}
