<?php

namespace Modules\Website\Http\Requests;

class UpdateFaqRequest extends StoreFaqRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('website.faqs.update') ?? false;
    }
}
