<?php

namespace Modules\Correspondence\Actions;

use Modules\Correspondence\Enums\CorrespondenceRecipientType;
use Modules\Correspondence\Models\Correspondence;
use Modules\Correspondence\Models\CorrespondenceRecipient;

class AddCorrespondenceRecipient
{
    public function execute(Correspondence $correspondence, int $userId, string $type = 'to'): CorrespondenceRecipient
    {
        return $correspondence->recipients()->firstOrCreate([
            'user_id' => $userId,
            'recipient_type' => CorrespondenceRecipientType::tryFrom($type)?->value ?? CorrespondenceRecipientType::To->value,
        ]);
    }
}
