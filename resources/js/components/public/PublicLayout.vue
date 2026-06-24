<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-['Cairo'] text-slate-950" dir="rtl">
        <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/" class="flex items-center gap-3 text-xl font-extrabold text-blue-950">
                    <img
                        src="/histlogopng.png"
                        alt="شعار المعهد"
                        class="h-12 w-12 rounded-full object-contain"
                    />
                    <span>المعهد العالي</span>
                </Link>
                <nav class="flex flex-wrap items-center gap-4 text-sm font-bold text-slate-700">
                    <Link href="/" class="transition hover:text-orange-500">الرئيسية</Link>
                    <Link href="/pages/about" class="transition hover:text-orange-500">عن المعهد</Link>
                    <a href="/#admission" class="transition hover:text-orange-500">القبول والتسجيل</a>
                    <Link href="/faqs" class="transition hover:text-orange-500">الأسئلة الشائعة</Link>
                    <a href="/#contact" class="transition hover:text-orange-500">تواصل معنا</a>
                    <Link
                        href="/portal"
                        class="rounded-full bg-blue-950 px-4 py-2 text-white transition hover:bg-orange-500"
                    >
                        دخول البوابة
                    </Link>
                </nav>
            </div>
        </header>

        <slot />

        <footer class="mt-12 border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                <p>الموقع الرسمي للمعهد العالي للأخبار والخدمات الأكاديمية والإدارية.</p>
                <p>© {{ new Date().getFullYear() }} جميع الحقوق محفوظة.</p>
            </div>
        </footer>
    </div>
</template>
