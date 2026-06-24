<?php

namespace Modules\Website\Http\Controllers\Public;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Models\Page;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::publiclyVisible()->where('slug', $slug)->first();

        if (! $page && $slug !== 'about') {
            abort(404);
        }

        return Inertia::render('Website/Public/Page', [
            'page' => $page ?? [
                'title' => 'عن المعهد',
                'summary' => 'تعرّف على المعهد العالي وخدماته الأكاديمية والإدارية.',
                'content' => '<p>يوفر المعهد بيئة تعليمية حديثة تجمع بين الدراسة التطبيقية والخدمات الإلكترونية، مع متابعة مستمرة للأخبار والإعلانات والتسجيل عبر الموقع الرسمي.</p><p>يمكن لمسؤول الموقع تحديث هذه الصفحة من لوحة التحكم عبر صفحة إدارة "عن المعهد".</p>',
                'featured_image_path' => '/assets/img/website-news-hero.svg',
                'featured_image_url' => '/assets/img/website-news-hero.svg',
                'seo_title' => 'عن المعهد',
                'seo_description' => 'نبذة عن المعهد العالي وخدماته.',
            ],
        ]);
    }
}
