<?php

namespace Modules\Correspondence\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Correspondence\Enums\CorrespondenceClassification;
use Modules\Correspondence\Enums\CorrespondencePriority;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Enums\CorrespondenceType;
use Modules\Correspondence\Models\Correspondence;
use Modules\Correspondence\Models\CorrespondenceCategory;
use Modules\User\Models\User;

class CreateCorrespondence
{
    public function execute(User $sender, array $data): Correspondence
    {
        return DB::transaction(function () use ($sender, $data): Correspondence {
            $category = isset($data['category_id'])
                ? CorrespondenceCategory::query()->find($data['category_id'])
                : null;

            $correspondence = Correspondence::create([
                'sender_user_id' => $sender->id,
                'category_id' => $category?->id,
                'type' => $data['type'] ?? CorrespondenceType::OfficialLetter->value,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'priority' => $data['priority'] ?? CorrespondencePriority::Normal->value,
                'classification' => $data['classification'] ?? CorrespondenceClassification::Internal->value,
                'status' => CorrespondenceStatus::Draft->value,
                'requires_approval' => (bool) ($data['requires_approval'] ?? $category?->requires_approval ?? false),
            ]);

            $correspondence->statusHistories()->create([
                'actor_user_id' => $sender->id,
                'to_status' => CorrespondenceStatus::Draft->value,
                'notes' => 'Draft created.',
            ]);

            return $correspondence;
        });
    }
}
