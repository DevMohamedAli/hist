<?php

namespace Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class ActivityLogView extends Model
{
    protected $fillable = ['user_id', 'name', 'filters'];
    protected $casts = ['filters' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
