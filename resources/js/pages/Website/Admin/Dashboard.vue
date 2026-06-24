<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({ layout: [] });

defineProps<{
    stats: {
        pages: number;
        posts: number;
        banners: number;
        faqs: number;
        contactSubmissions: number;
    };
}>();

const cards = [
    {
        title: 'عن المعهد',
        description: 'تحديث صفحة التعريف بالمعهد، النص المنسق، صورة الصفحة، وبيانات SEO.',
        href: '/admin/website/pages',
        stat: 'pages',
    },
    {
        title: 'الأخبار والإعلانات',
        description: 'إضافة الأخبار، الإعلانات، والفعاليات وتحديث صورها وشريط ظهورها في الصفحة الرئيسية.',
        href: '/admin/website/posts',
        stat: 'posts',
    },
    {
        title: 'السلايدر والبنرات',
        description: 'إدارة شرائح الصفحة الرئيسية وروابطها وصورها وترتيب ظهورها.',
        href: '/admin/website/banners',
        stat: 'banners',
    },
    {
        title: 'الأسئلة الشائعة',
        description: 'إضافة وتعديل الأسئلة الشائعة المنشورة للزوار.',
        href: '/admin/website/faqs',
        stat: 'faqs',
    },
    {
        title: 'إعدادات الموقع',
        description: 'تحديث اسم الموقع، وصفه، بيانات التواصل، وصورة الهيرو الافتراضية.',
        href: '/admin/website/settings',
        stat: null,
    },
    {
        title: 'رسائل التواصل',
        description: 'استعراض الرسائل الواردة من نموذج التواصل في الموقع.',
        href: '/admin/website/contact-submissions',
        stat: 'contactSubmissions',
    },
] as const;
</script>

<template>
    <Head title="إدارة الموقع" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-slate-50 p-6" dir="rtl">
            <div class="mx-auto max-w-6xl space-y-6">
                <section class="rounded-3xl bg-blue-950 p-8 text-white shadow">
                    <p class="text-sm font-bold text-orange-300">لوحة إدارة الموقع</p>
                    <h1 class="mt-2 text-3xl font-extrabold">
                        إدارة محتوى واجهة الموقع
                    </h1>
                    <p class="mt-3 max-w-3xl leading-8 text-blue-100">
                        من هنا يستطيع مسؤول الموقع إدارة صفحة عن المعهد، الأخبار، السلايدر، الأسئلة الشائعة، الإعدادات، ورسائل التواصل.
                    </p>
                </section>

                <section class="grid gap-4 md:grid-cols-3">
                    <Link
                        v-for="card in cards"
                        :key="card.href"
                        :href="card.href"
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-xl font-extrabold text-blue-950">
                                {{ card.title }}
                            </h2>
                            <span
                                v-if="card.stat"
                                class="rounded-full bg-orange-50 px-3 py-1 text-sm font-bold text-orange-700"
                            >
                                {{ stats[card.stat] }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            {{ card.description }}
                        </p>
                    </Link>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
