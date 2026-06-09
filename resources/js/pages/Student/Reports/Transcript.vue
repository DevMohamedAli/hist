<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, Printer } from 'lucide-vue-next';
import { formatDisplayDateTime } from '@/lib/date';

interface Student {
    registration_number: string;
    full_name: string;
    status?: string;
    current_specialization?: { name: string; department?: { name: string } };
}

interface Enrollment {
    id: number;
    semester_work_grade?: number | string | null;
    final_exam_grade?: number | string | null;
    total_grade?: number | string | null;
    grade_evaluation?: string | null;
    class?: { course?: { code?: string; name?: string; units?: number } };
}

defineProps<{
    student: Student;
    enrollments: Enrollment[];
    summary?: Record<string, number | string | null> | null;
    printed_at: string;
}>();

const printPage = () => window.print();
</script>

<template>
    <Head :title="`كشف درجات | ${student.full_name}`" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8 print:bg-white print:p-0"
        dir="rtl"
    >
        <section
            class="mx-auto max-w-6xl rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md print:border-0 print:shadow-none"
        >
            <div
                class="flex flex-wrap items-start justify-between gap-4 border-b pb-6"
            >
                <div class="flex items-start gap-3">
                    <GraduationCap class="h-10 w-10 text-orange-500" />
                    <div>
                        <p class="font-bold text-orange-500">
                            المعهد العالي للعلوم والتقنية - العجيلات
                        </p>
                        <h1 class="text-2xl font-extrabold text-blue-800">
                            كشف الدرجات الأكاديمي
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            تاريخ الطباعة: {{ formatDisplayDateTime(printed_at) }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600 print:hidden"
                    @click="printPage"
                >
                    <Printer class="h-5 w-5" />
                    طباعة
                </button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-sm text-gray-500">رقم القيد</p>
                    <p class="font-extrabold text-blue-800">
                        {{ student.registration_number }}
                    </p>
                </div>
                <div class="rounded-xl bg-blue-50 p-4 md:col-span-2">
                    <p class="text-sm text-gray-500">اسم الطالب</p>
                    <p class="font-extrabold text-blue-800">
                        {{ student.full_name }}
                    </p>
                </div>
                <div class="rounded-xl bg-orange-50 p-4">
                    <p class="text-sm text-gray-500">التخصص</p>
                    <p class="font-extrabold text-blue-800">
                        {{ student.current_specialization?.name ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="mt-8 overflow-hidden rounded-xl border">
                <table class="w-full min-w-190 text-sm">
                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-start">كود المقرر</th>
                            <th class="px-4 py-3 text-start">اسم المقرر</th>
                            <th class="px-4 py-3 text-center">الوحدات</th>
                            <th class="px-4 py-3 text-center">الأعمال</th>
                            <th class="px-4 py-3 text-center">النهائي</th>
                            <th class="px-4 py-3 text-center">المجموع</th>
                            <th class="px-4 py-3 text-center">التقدير</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="enrollment in enrollments"
                            :key="enrollment.id"
                            class="hover:bg-orange-50/40"
                        >
                            <td class="px-4 py-3 font-mono">
                                {{ enrollment.class?.course?.code ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">
                                <BookOpen
                                    class="me-2 inline h-4 w-4 text-orange-500"
                                />{{ enrollment.class?.course?.name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ enrollment.class?.course?.units ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ enrollment.semester_work_grade ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ enrollment.final_exam_grade ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold">
                                {{ enrollment.total_grade ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ enrollment.grade_evaluation ?? '-' }}
                            </td>
                        </tr>
                        <tr v-if="enrollments.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-gray-500"
                            >
                                لا توجد مقررات في الكشف.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="summary" class="mt-6 grid gap-4 md:grid-cols-3">
                <div
                    v-for="(value, key) in summary"
                    :key="key"
                    class="rounded-xl bg-gray-50 p-4"
                >
                    <p class="text-sm text-gray-500">{{ key }}</p>
                    <p class="font-extrabold text-blue-800">
                        {{ value ?? '-' }}
                    </p>
                </div>
            </div>
        </section>
    </main>
</template>
