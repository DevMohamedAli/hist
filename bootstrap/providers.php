<?php

use Modules\Academic\Providers\AcademicServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Import\Providers\ImportServiceProvider;
use Modules\Platform\Providers\PlatformServiceProvider;
use Modules\Qualification\Providers\QualificationServiceProvider;
use Modules\Staff\Providers\StaffServiceProvider;
use Modules\Student\Providers\StudentServiceProvider;
use Modules\User\Providers\UserServiceProvider;

return [
    PlatformServiceProvider::class,
    ImportServiceProvider::class,
    UserServiceProvider::class,
    AuthServiceProvider::class,
    QualificationServiceProvider::class,
    StaffServiceProvider::class,
    AcademicServiceProvider::class,
    StudentServiceProvider::class,
];
