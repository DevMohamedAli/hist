<?php

namespace Modules\Correspondence\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyCorrespondenceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
        ];
    }
}
