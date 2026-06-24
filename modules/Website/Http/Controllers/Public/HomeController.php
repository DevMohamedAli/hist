<?php

namespace Modules\Website\Http\Controllers\Public;

use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academic\Actions\GetPublicAcademicCalendar;
use Modules\Academic\Actions\ListPublicDepartments;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Staff\Actions\ListPublicInstructors;
use Modules\Staff\Models\Instructor;
use Modules\Student\Actions\GetNewStudentRegistrationInformation;
use Modules\Student\Actions\GetPublicRegistrationStatus;
use Modules\Student\Models\Student;
use Modules\Website\Models\Banner;
use Modules\Website\Models\Faq;
use Modules\Website\Models\Post;
use Modules\Website\Models\WebsiteSetting;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
{
    public function __invoke(
        ListPublicDepartments $departments,
        ListPublicInstructors $instructors,
        GetPublicAcademicCalendar $calendar,
        GetPublicRegistrationStatus $registrationStatus,
        GetNewStudentRegistrationInformation $registrationInformation,
    ): Response {
        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $heroImagePath = $settings->get('hero_image_path');
        $employeeRole = Role::query()
            ->where('name', 'employee')
            ->where('guard_name', 'web')
            ->first();

        return Inertia::render('Website/Public/Home', [
            'settings' => [
                ...$settings->all(),
                'hero_image_url' => $this->publicUrl($heroImagePath),
            ],
            'statistics' => [
                'year' => now()->year,
                'students' => Student::query()->where('status', 'Active')->count(),
                'graduates' => Student::query()->where('status', 'Graduated')->count(),
                'instructors' => Instructor::query()->where('status', 'Active')->count(),
                'employees' => $employeeRole ? $employeeRole->users()->count() : 0,
            ],
            'banners' => Banner::active()->orderBy('sort_order')->limit(5)->get(),
            'news' => Post::publiclyVisible()->where('type', 'news')->latest('published_at')->limit(8)->get(),
            'announcements' => Post::publiclyVisible()->where('type', 'announcement')->latest('published_at')->limit(5)->get(),
            'events' => Post::publiclyVisible()->where('type', 'event')->orderBy('starts_at')->limit(5)->get(),
            'departments' => $departments->execute(),
            'instructors' => $instructors->execute()->take(6)->values(),
            'academicCalendar' => $calendar->execute(),
            'registration' => [
                ...$registrationStatus->execute(),
                ...$registrationInformation->execute(),
            ],
            'faqs' => Faq::query()->where('is_published', true)->orderBy('sort_order')->limit(8)->get(),
            'portalLoginUrl' => route('portal.home'),
        ]);
    }

    private function publicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
