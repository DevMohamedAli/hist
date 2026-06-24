<?php

namespace Modules\Academic\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Academic\Services\AcademicAuditService;
use Modules\Shared\Http\Controllers\Controller;

class AcademicAuditController extends Controller
{
    public function __invoke(AcademicAuditService $audit): Response
    {
        return Inertia::render('Academic/Audit/Index', [
            'report' => $audit->report(),
        ]);
    }
}
