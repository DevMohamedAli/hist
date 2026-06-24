<?php

namespace Modules\Correspondence\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCorrespondenceAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
