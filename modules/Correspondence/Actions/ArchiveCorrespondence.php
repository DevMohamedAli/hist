<?php

namespace Modules\Correspondence\Actions;

use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class ArchiveCorrespondence
{
    public function execute(Correspondence $correspondence, User $actor): Correspondence
    {
        $from = $correspondence->status->value;

        $correspondence->forceFill([
            'status' => CorrespondenceStatus::Archived,
            'archived_at' => now(),
        ])->save();

        $correspondence->statusHistories()->create([
            'actor_user_id' => $actor->id,
            'from_status' => $from,
            'to_status' => CorrespondenceStatus::Archived->value,
        ]);

        return $correspondence->fresh();
    }
}
