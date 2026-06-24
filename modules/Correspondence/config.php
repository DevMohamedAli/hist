<?php

return [
    'reference_format' => env('CORRESPONDENCE_REFERENCE_FORMAT', 'COR-{YEAR}-{SEQUENCE}'),
    'attachments_disk' => env('CORRESPONDENCE_ATTACHMENTS_DISK', 'local'),
];
