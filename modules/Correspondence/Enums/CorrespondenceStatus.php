<?php

namespace Modules\Correspondence\Enums;

enum CorrespondenceStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case PendingApproval = 'pending_approval';
    case ReturnedForChanges = 'returned_for_changes';
    case Approved = 'approved';
    case Dispatched = 'dispatched';
    case Received = 'received';
    case Responded = 'responded';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Archived = 'archived';
}
