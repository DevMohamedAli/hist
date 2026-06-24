<?php

namespace Modules\Correspondence\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class DispatchCorrespondence
{
    public function execute(Correspondence $correspondence, User $actor): Correspondence
    {
        abort_unless(in_array($correspondence->status->value, [
            CorrespondenceStatus::Submitted->value,
            CorrespondenceStatus::Approved->value,
        ], true), 422);

        return DB::transaction(function () use ($correspondence, $actor): Correspondence {
            $from = $correspondence->status->value;

            $correspondence->forceFill([
                'status' => CorrespondenceStatus::Dispatched,
                'sent_at' => now(),
            ])->save();

            $correspondence->recipients()->update([
                'delivery_status' => 'delivered',
                'received_at' => now(),
            ]);

            $correspondence->statusHistories()->create([
                'actor_user_id' => $actor->id,
                'from_status' => $from,
                'to_status' => CorrespondenceStatus::Dispatched->value,
            ]);

            return $correspondence->fresh(['recipients']);
        });
    }
}
