<?php

namespace Modules\Correspondence\Enums;

enum CorrespondenceClassification: string
{
    case Internal = 'internal';
    case Restricted = 'restricted';
    case Confidential = 'confidential';
    case HighlyConfidential = 'highly_confidential';
}
