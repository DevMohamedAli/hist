<?php

namespace Modules\Correspondence\Enums;

enum CorrespondencePriority: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Urgent = 'urgent';
    case Immediate = 'immediate';
}
