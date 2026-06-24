<?php

namespace Modules\Correspondence\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class ApproveCorrespondence
{
    public function execute(Correspondence $correspondence, User $approver, ?Request $request = null, ?string $notes = null): Correspondence
    {
        abort_unless($correspondence->status === CorrespondenceStatus::PendingApproval, 422);

        return DB::transaction(function () use ($correspondence, $approver, $request, $notes): Correspondence {
            $hash = hash('sha256', $correspondence->subject."\n".$correspondence->body);

            $correspondence->forceFill([
                'status' => CorrespondenceStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ])->save();

            $correspondence->statusHistories()->create([
                'actor_user_id' => $approver->id,
                'from_status' => CorrespondenceStatus::PendingApproval->value,
                'to_status' => CorrespondenceStatus::Approved->value,
                'notes' => $notes,
            ]);

            $correspondence->approvals()->create([
                'approver_user_id' => $approver->id,
                'decision' => 'approved',
                'notes' => $notes,
                'content_hash' => $hash,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 1000) : null,
                'decided_at' => now(),
            ]);

            return $correspondence->fresh();
        });
    }
}
