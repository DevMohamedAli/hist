<?php

namespace Modules\Website\Enums;

enum WebsiteContentStatus: string
{
    case Draft = 'draft';
    case UnderReview = 'under_review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
}
