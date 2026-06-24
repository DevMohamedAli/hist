<?php

namespace Modules\Correspondence\Enums;

enum CorrespondenceRecipientType: string
{
    case To = 'to';
    case Cc = 'cc';
    case Bcc = 'bcc';
    case Observer = 'observer';
    case ActionRequired = 'action_required';
}
