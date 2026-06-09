<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarClock,
    GraduationCap,
    Layers3,
    PenLine,
    PlusCircle,
    UserPlus,
    UsersRound,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({
    layout: [],
});

const statistics = [
    {
        title: 'إجمالي الطلاب المقيدين',
        value: '1,250',
        icon: UsersRound,
        accent: 'text-blue-800',
    },
    {
        title: 'المقررات المفتوحة',
        value: '42',
        icon: BookOpen,
        accent: 'text-orange-500',
    },
    {
        title: 'أعضاء هيئة التدريس',
        value: '38',
        icon: GraduationCap,
        accent: 'text-blue-800',
    },
    {
        title: 'الفصل الدراسي الحالي',
        value: 'ربيع 2026',
        icon: CalendarClock,
        accent: 'text-orange-500',
    },
];

const quickActions = [
    {
        title: 'تسجيل طالب جديد',
        icon: UserPlus,
    },
    {
        title: 'رصد الدرجات',
        icon: PenLine,
    },
    {
        title: 'إعداد جداول الامتحانات',
        icon: CalendarClock,
    },
    {
        title: 'فتح شعبة جديدة',
        icon: Layers3,
    },
];
</script>

<template>
    <Head title="لوحة التحكم | المعهد العالي" />

    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
            <div class="mx-auto max-w-7xl space-y-8">
                <section
                    class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md"
                >
                    <p class="text-sm font-semibold text-orange-500">
                        لوحة التحكم
                    </p>
                    <h1
                        class="mt-2 text-2xl leading-9 font-bold text-blue-800 lg:text-3xl"
                    >
                        مرحباً بك في منظومة الإدارة الأكاديمية للمعهد العالي
                        للعلوم والتقنية - العجيلات
                    </h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">
                        نظرة عامة سريعة على أهم مؤشرات النظام والإجراءات اليومية
                        الأكثر استخداماً.
                    </p>
                </section>

                <section>
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-gray-900">
                            نظرة عامة على الإحصائيات
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <article
                            v-for="item in statistics"
                            :key="item.title"
                            class="rounded-lg bg-white p-5 shadow-md"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-sm font-semibold text-gray-500"
                                    >
                                        {{ item.title }}
                                    </p>
                                    <p
                                        class="mt-3 text-3xl font-bold"
                                        :class="item.accent"
                                    >
                                        {{ item.value }}
                                    </p>
                                </div>
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50"
                                    :class="item.accent"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-6 w-6"
                                    />
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6 shadow-md">
                    <div
                        class="mb-5 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                إجراءات سريعة
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                وصول مختصر للمهام المتكررة داخل المنظومة.
                            </p>
                        </div>
                        <PlusCircle class="h-7 w-7 text-orange-500" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <Link
                            v-for="action in quickActions"
                            :key="action.title"
                            href="#"
                            class="group rounded-lg border border-gray-200 bg-gray-50 p-5 text-start transition hover:border-orange-500 hover:bg-orange-50 hover:shadow-md"
                        >
                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-blue-800 text-white transition group-hover:bg-orange-500"
                            >
                                <component :is="action.icon" class="h-5 w-5" />
                            </div>
                            <p class="font-bold text-blue-800">
                                {{ action.title }}
                            </p>
                        </Link>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
