<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, GraduationCap, UserCheck } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const portals = [
    {
        title: 'طالب',
        description: 'عرض الملف الأكاديمي والنتائج والمقررات المسجلة.',
        href: '/student/login',
        icon: GraduationCap,
    },
    {
        title: 'عضو هيئة تدريس',
        description: 'متابعة الشعب المسندة ورصد درجات الطلاب.',
        href: '/teacher/login',
        icon: UserCheck,
    },
    {
        title: 'موظف',
        description: 'إدارة الطلاب والمقررات والفصول الدراسية.',
        href: '/employee/login',
        icon: Building2,
    },
];
</script>

<template>
    <Head title="مرحباً - المعهد العالي للعلوم والتقنية" />

    <main class="min-h-screen bg-gray-50 px-4 py-8 font-['Cairo']" dir="rtl">
        <section
            class="mx-auto max-w-5xl overflow-hidden rounded-xl bg-white shadow-md"
        >
            <div class="p-6 text-center md:p-10">
                <div class="mb-5 flex justify-center">
                    <AppLogoIcon class="h-28 w-auto md:h-36" />
                </div>

                <h1 class="text-2xl font-extrabold text-blue-800 md:text-3xl">
                    المعهد العالي للعلوم والتقنية - العجيلات
                </h1>
                <div class="mx-auto my-5 h-1 w-32 rounded-full bg-orange-500" />
                <p class="mx-auto max-w-2xl text-gray-600">
                    يرجى اختيار بوابة الدخول المناسبة للوصول إلى منظومة الإدارة
                    الأكاديمية.
                </p>

                <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <Link
                        v-for="portal in portals"
                        :key="portal.href"
                        :href="portal.href"
                        class="group rounded-xl border border-gray-100 bg-white p-6 text-start shadow-sm transition hover:border-orange-200 hover:bg-orange-50/60 hover:shadow-md"
                    >
                        <component
                            :is="portal.icon"
                            class="h-10 w-10 text-orange-500"
                        />
                        <h2 class="mt-4 text-xl font-extrabold text-blue-800">
                            {{ portal.title }}
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ portal.description }}
                        </p>
                    </Link>
                </div>
            </div>
        </section>
    </main>
</template>
