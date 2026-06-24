<?php

namespace Modules\Correspondence\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Correspondence\Enums\CorrespondenceClassification;
use Modules\Correspondence\Enums\CorrespondencePriority;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Enums\CorrespondenceType;
use Modules\Correspondence\Models\Correspondence;
use Modules\User\Models\User;

class CorrespondenceFactory extends Factory
{
    protected $model = Correspondence::class;

    public function definition(): array
    {
        return [
            'sender_user_id' => User::factory(),
            'type' => CorrespondenceType::OfficialLetter,
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'priority' => CorrespondencePriority::Normal,
            'classification' => CorrespondenceClassification::Internal,
            'status' => CorrespondenceStatus::Draft,
            'requires_approval' => false,
        ];
    }
}
