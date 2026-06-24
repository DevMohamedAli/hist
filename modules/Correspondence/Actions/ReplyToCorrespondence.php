<?php

namespace Modules\Correspondence\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class ReplyToCorrespondence
{
    public function execute(Correspondence $correspondence, User $sender, string $body): Correspondence
    {
        return DB::transaction(function () use ($correspondence, $sender, $body): Correspondence {
            $correspondence->replies()->create([
                'sender_user_id' => $sender->id,
                'body' => $body,
            ]);

            $correspondence->forceFill(['status' => CorrespondenceStatus::Responded])->save();
            $correspondence->recipients()->where('user_id', $sender->id)->update(['responded_at' => now()]);

            $correspondence->statusHistories()->create([
                'actor_user_id' => $sender->id,
                'to_status' => CorrespondenceStatus::Responded->value,
            ]);

            return $correspondence->fresh(['replies']);
        });
    }
}
