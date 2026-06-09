<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, LineChart, UserRound } from 'lucide-vue-next';
import MinistryNewsFeed, { type MinistryNewsItem } from '@/components/MinistryNewsFeed.vue';

interface Student {
    id: number;
    full_name: string;
    registration_number: string;
    current_specialization?: {
        name: string;
        department?: { name: string };
    };
}

interface Summary {
    id: number;
    semester_gpa: number | string;
    cumulative_gpa: number | string;
    carried_courses_count: number;
    semester?: { code: string };
}

interface Enrollment {
    id: number;
    total_grade: number | string;
    grade_evaluation?: string | null;
    class?: {
        course?: { code: string; name: string; units: number };
        semester?: { code: string };
    };
}

defineProps<{
    student: Student;
    summaries: Summary[];
    enrollments: Enrollment[];
    ministryNews?: MinistryNewsItem[];
}>();
</script>

<template>
    <Head title="لوحة الطالب" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <GraduationCap class="h-12 w-12 text-orange-500" />
                        <div>
                            <p class="text-sm font-bold text-orange-500">
                                بوابة الطالب
                            </p>
                            <h1 class="text-2xl font-extrabold text-blue-800">
                                {{ student.full_name }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600">
                                رقم القيد: {{ student.registration_number }} -
                                {{
                                    student.current_specialization?.name ??
                                    'غير محدد'
                                }}
                            </p>
                        </div>
                    </div>
                    <Link
                        href="/student/profile"
                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600"
                    >
                        <UserRound class="h-5 w-5" />
                        الملف الأكاديمي
                    </Link>
                </div>
            </section>

            <MinistryNewsFeed :items="ministryNews" />

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <LineChart class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">آخر معدل تراكمي</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ summaries[0]?.cumulative_gpa ?? '-' }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <BookOpen class="mb-3 h-6 w-6 text-orange-500" />
                    <p class="text-sm text-gray-500">المقررات المسجلة</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ enrollments.length }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <GraduationCap class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">مواد محمولة</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ summaries[0]?.carried_courses_count ?? 0 }}
                    </p>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b p-5">
                    <h2 class="text-xl font-bold text-blue-800">
                        آخر المقررات والدرجات
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="bg-blue-800 text-white">
                            <tr>
                                <th class="px-5 py-3 text-start">الفصل</th>
                                <th class="px-5 py-3 text-start">المقرر</th>
                                <th class="px-5 py-3 text-center">الوحدات</th>
                                <th class="px-5 py-3 text-center">الدرجة</th>
                                <th class="px-5 py-3 text-center">التقدير</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="enrollment in enrollments"
                                :key="enrollment.id"
                                class="hover:bg-orange-50/50"
                            >
                                <td class="px-5 py-3">
                                    {{
                                        enrollment.class?.semester?.code ?? '-'
                                    }}
                                </td>
                                <td class="px-5 py-3 font-bold text-gray-900">
                                    {{ enrollment.class?.course?.code }} -
                                    {{ enrollment.class?.course?.name }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{ enrollment.class?.course?.units ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-center font-bold">
                                    {{ enrollment.total_grade }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{ enrollment.grade_evaluation ?? '-' }}
                                </td>
                            </tr>
                            <tr v-if="enrollments.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-10 text-center text-gray-500"
                                >
                                    لا توجد مقررات مسجلة حالياً.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</template>
