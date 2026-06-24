<?php

namespace Modules\Website\Http\Requests;

class UpdateBannerRequest extends StoreBannerRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('website.banners.update') ?? false;
    }
}
