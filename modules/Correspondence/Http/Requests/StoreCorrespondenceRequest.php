<?php

namespace Modules\Correspondence\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Correspondence\Enums\CorrespondenceClassification;
use Modules\Correspondence\Enums\CorrespondencePriority;
use Modules\Correspondence\Enums\CorrespondenceRecipientType;
use Modules\Correspondence\Enums\CorrespondenceType;
use Modules\Correspondence\Models\CorrespondenceCategory;

class StoreCorrespondenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('correspondence.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:correspondence_categories,id'],
            'type' => ['required', Rule::enum(CorrespondenceType::class)],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['required', Rule::enum(CorrespondencePriority::class)],
            'classification' => ['required', Rule::enum(CorrespondenceClassification::class)],
            'recipients' => ['array'],
            'recipients.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'recipients.*.recipient_type' => ['required', Rule::enum(CorrespondenceRecipientType::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->user()?->hasRole('student')) {
                return;
            }

            $category = CorrespondenceCategory::query()->find($this->input('category_id'));

            if (! $category?->is_student_available) {
                $validator->errors()->add('category_id', 'Students may only use approved correspondence categories.');
            }

            if ($this->input('classification') !== CorrespondenceClassification::Internal->value) {
                $validator->errors()->add('classification', 'Students cannot choose restricted classifications.');
            }
        });
    }
}
