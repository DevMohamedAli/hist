<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarRange,
    GraduationCap,
    Layers3,
    ListChecks,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MinistryNewsFeed from '@/components/MinistryNewsFeed.vue';
import RegistrationClosedDialog from '@/components/RegistrationClosedDialog.vue';
import type { MinistryNewsItem } from '@/components/MinistryNewsFeed.vue';

interface Stats {
    students: number;
    active_students: number;
    courses: number;
    semesters: number;
    pending_transfers: number;
    teachers: number;
}

interface RegistrationStatus {
    is_open: boolean;
    message?: string;
    semester?: {
        code?: string | null;
        registration_start?: string | null;
        registration_end?: string | null;
    } | null;
}

const props = defineProps<{
    stats: Stats;
    registration?: RegistrationStatus;
    ministryNews?: MinistryNewsItem[];
}>();

const registrationDialogOpen = ref(false);

const quickLinks = computed(() => [
    {
        title: 'تسجيل طالب جديد',
        href: '/students/create',
        icon: UserPlus,
        requiresRegistration: true,
    },
    { title: 'إدارة المقررات', href: '/academic/courses', icon: BookOpen },
    {
        title: 'الفصول الدراسية',
        href: '/academic/semesters',
        icon: CalendarRange,
    },
    { title: 'سجل الطلاب', href: '/students', icon: Users },
    {
        title: 'سجل النشاطات',
        href: '/employee/activity-logs',
        icon: ListChecks,
    },
]);
</script>

<template>
    <Head title="لوحة الموظفين" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <p class="text-sm font-bold text-orange-500">بوابة الموظفين</p>
                <h1 class="mt-1 text-2xl font-extrabold text-blue-800">
                    لوحة إدارة النظام الأكاديمي
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    ملخص سريع وروابط مباشرة لأعمال الشؤون الأكاديمية.
                </p>
            </section>

            <MinistryNewsFeed :items="ministryNews" />

            <section
                v-if="registration"
                class="rounded-xl border p-4 shadow-sm"
                :class="
                    registration.is_open
                        ? 'border-emerald-200 bg-emerald-50'
                        : 'border-amber-200 bg-amber-50'
                "
            >
                <p class="font-bold text-slate-900">
                    {{ registration.message }}
                </p>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <Users class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">إجمالي الطلاب</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ stats.students }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <GraduationCap class="mb-3 h-6 w-6 text-orange-500" />
                    <p class="text-sm text-gray-500">الطلاب النشطون</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ stats.active_students }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <BookOpen class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">المقررات</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ stats.courses }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <CalendarRange class="mb-3 h-6 w-6 text-orange-500" />
                    <p class="text-sm text-gray-500">الفصول</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ stats.semesters }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <Layers3 class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">طلبات الانتقال المسجلة</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ stats.pending_transfers }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <GraduationCap class="mb-3 h-6 w-6 text-orange-500" />
                    <p class="text-sm text-gray-500">أعضاء هيئة التدريس</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ stats.teachers }}
                    </p>
                </div>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold text-blue-800">
                    إجراءات سريعة
                </h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <template v-for="item in quickLinks" :key="item.href">
                        <button
                            v-if="
                                item.requiresRegistration &&
                                !registration?.is_open
                            "
                            type="button"
                            class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-right font-bold text-amber-900 transition hover:bg-amber-100"
                            @click="registrationDialogOpen = true"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 text-amber-700"
                            />
                            {{ item.title }}
                        </button>
                        <Link
                            v-else
                            :href="item.href"
                            class="flex items-center gap-3 rounded-xl border p-4 font-bold text-blue-800 transition hover:border-orange-200 hover:bg-orange-50"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 text-orange-500"
                            />
                            {{ item.title }}
                        </Link>
                    </template>
                </div>
            </section>
        </div>

        <RegistrationClosedDialog
            v-model:open="registrationDialogOpen"
            :registration="registration"
        />
    </main>
</template>
