<?php

namespace Modules\Correspondence\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class SubmitCorrespondence
{
    public function execute(Correspondence $correspondence, User $actor): Correspondence
    {
        abort_unless(in_array($correspondence->status->value, [
            CorrespondenceStatus::Draft->value,
            CorrespondenceStatus::ReturnedForChanges->value,
        ], true), 422);

        return DB::transaction(function () use ($correspondence, $actor): Correspondence {
            $from = $correspondence->status->value;
            $to = $correspondence->requires_approval
                ? CorrespondenceStatus::PendingApproval
                : CorrespondenceStatus::Submitted;

            if (! $correspondence->reference_number) {
                $correspondence->reference_number = app(GenerateCorrespondenceReference::class)->execute();
            }

            $correspondence->status = $to;
            $correspondence->save();

            $correspondence->statusHistories()->create([
                'actor_user_id' => $actor->id,
                'from_status' => $from,
                'to_status' => $to->value,
            ]);

            return $correspondence->fresh(['recipients']);
        });
    }
}
