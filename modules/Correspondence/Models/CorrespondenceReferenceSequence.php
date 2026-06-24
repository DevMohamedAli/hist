<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenceReferenceSequence extends Model
{
    protected $fillable = ['year', 'last_number'];
}
