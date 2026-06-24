<?php

namespace Modules\Correspondence\Enums;

enum CorrespondenceType: string
{
    case OfficialLetter = 'official_letter';
    case InternalMemo = 'internal_memo';
    case AdministrativeRequest = 'administrative_request';
    case AcademicRequest = 'academic_request';
    case Directive = 'directive';
    case Circular = 'circular';
    case Notice = 'notice';
    case Response = 'response';
    case Complaint = 'complaint';
    case StudentRequest = 'student_request';
}
