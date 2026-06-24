<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'website_faqs';

    protected $fillable = [
        'question',
        'answer',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
