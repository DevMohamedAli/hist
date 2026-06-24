<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, Layers3, Save } from 'lucide-vue-next';
import { computed } from 'vue';

interface CourseClass {
    id: number;
    group_name: string;
    course?: { name: string; code?: string };
    semester?: { code: string };
    study_group?: {
        group_name: string;
        semester_level?: number;
        specialization?: { name: string };
    } | null;
}

interface StudentGrade {
    enrollment_id: number;
    student_name: string;
    registration_number: string;
    semester_work_grade: number | null;
    final_exam_grade: number | null;
    total_grade: number | null;
    evaluation: string | null;
}

interface PageProps {
    flash?: { success?: string };
    [key: string]: unknown;
}

const props = defineProps<{
    courseClass: CourseClass;
    students: StudentGrade[];
}>();

const page = usePage<PageProps>();

const form = useForm({
    grades: Object.fromEntries(
        props.students.map((student) => [
            student.enrollment_id,
            {
                semester_work_grade: Number(student.semester_work_grade ?? 0),
                final_exam_grade: Number(student.final_exam_grade ?? 0),
            },
        ]),
    ) as Record<
        number,
        { semester_work_grade: number; final_exam_grade: number }
    >,
});

const successMessage = computed(() => page.props.flash?.success);

const totalFor = (enrollmentId: number) => {
    const grade = form.grades[enrollmentId];

    return Math.min(
        100,
        Number(grade?.semester_work_grade || 0) +
            Number(grade?.final_exam_grade || 0),
    );
};

const fieldError = (
    enrollmentId: number,
    field: 'semester_work_grade' | 'final_exam_grade',
) => {
    return form.errors[
        `grades.${enrollmentId}.${field}` as keyof typeof form.errors
    ];
};

const regulationEvaluationFor = (total: number) => {
    if (total >= 85) {
        return 'ممتاز';
    }

    if (total >= 75) {
        return 'جيد جداً';
    }

    if (total >= 65) {
        return 'جيد';
    }

    if (total >= 50) {
        return 'مقبول';
    }

    if (total >= 35) {
        return 'ضعيف';
    }

    return 'ضعيف جداً';
};

const submit = () => {
    form.put(`/teacher/classes/${props.courseClass.id}/grades`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Ø±ØµØ¯ Ø¯Ø±Ø¬Ø§Øª Ø§Ù„Ø´Ø¹Ø¨Ø©" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <Link
                            href="/teacher/dashboard"
                            class="mb-3 inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-500"
                        >
                            <ArrowRight class="h-4 w-4" />
                            Ø§Ù„Ø¹ÙˆØ¯Ø© Ù„Ù„ÙˆØ­Ø© Ø§Ù„ØªØ¯Ø±ÙŠØ³
                        </Link>
                        <p class="text-sm font-bold text-orange-500">
                            ÙƒØ´Ù Ø±ØµØ¯ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª
                        </p>
                        <h1 class="mt-1 text-2xl font-extrabold text-blue-800">
                            {{
                                courseClass.course?.name ??
                                'Ù…Ù‚Ø±Ø± ØºÙŠØ± Ù…Ø­Ø¯Ø¯'
                            }}
                        </h1>
                        <div
                            class="mt-3 grid gap-3 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <div class="rounded-lg bg-blue-50 px-3 py-2">
                                <span
                                    class="block text-xs font-bold text-gray-500"
                                    >Ø±Ù…Ø² Ø§Ù„Ù…Ù‚Ø±Ø±</span
                                >
                                <span class="font-bold text-blue-800">{{
                                    courseClass.course?.code ?? '-'
                                }}</span>
                            </div>
                            <div class="rounded-lg bg-blue-50 px-3 py-2">
                                <span
                                    class="block text-xs font-bold text-gray-500"
                                    >Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹Ø© Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠØ©</span
                                >
                                <span class="font-bold text-blue-800">{{
                                    courseClass.study_group?.group_name ??
                                    courseClass.group_name
                                }}</span>
                            </div>
                            <div class="rounded-lg bg-blue-50 px-3 py-2">
                                <span
                                    class="block text-xs font-bold text-gray-500"
                                    >Ø§Ù„ØªØ®ØµØµ</span
                                >
                                <span class="font-bold text-blue-800">{{
                                    courseClass.study_group?.specialization
                                        ?.name ?? '-'
                                }}</span>
                            </div>
                            <div class="rounded-lg bg-blue-50 px-3 py-2">
                                <span
                                    class="block text-xs font-bold text-gray-500"
                                    >Ø§Ù„ÙØµÙ„ Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠ</span
                                >
                                <span class="font-bold text-blue-800">{{
                                    courseClass.semester?.code ?? '-'
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-orange-50 px-4 py-3 text-center">
                        <Layers3 class="mx-auto mb-2 h-5 w-5 text-orange-500" />
                        <p class="text-xs font-bold text-gray-500">
                            Ø¹Ø¯Ø¯ Ø§Ù„Ø·Ù„Ø§Ø¨
                        </p>
                        <p class="text-2xl font-extrabold text-blue-800">
                            {{ students.length }}
                        </p>
                    </div>
                </div>
            </section>

            <div
                v-if="successMessage"
                class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700"
            >
                <CheckCircle2 class="h-5 w-5" />
                {{ successMessage }}
            </div>

            <form
                class="overflow-hidden rounded-xl bg-white shadow-sm"
                @submit.prevent="submit"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-sm">
                        <thead class="bg-blue-800 text-white">
                            <tr>
                                <th class="px-5 py-4 text-start">
                                    Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨
                                </th>
                                <th class="px-5 py-4 text-start">
                                    Ø±Ù‚Ù… Ø§Ù„Ù‚ÙŠØ¯
                                </th>
                                <th class="px-5 py-4 text-center">
                                    Ø£Ø¹Ù…Ø§Ù„ Ø§Ù„ÙØµÙ„
                                </th>
                                <th class="px-5 py-4 text-center">
                                    Ø§Ù„Ø§Ù…ØªØ­Ø§Ù† Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ
                                </th>
                                <th class="px-5 py-4 text-center">
                                    Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹
                                </th>
                                <th class="px-5 py-4 text-center">
                                    Ø§Ù„ØªÙ‚Ø¯ÙŠØ±
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="student in students"
                                :key="student.enrollment_id"
                                class="hover:bg-orange-50/40"
                            >
                                <td class="px-5 py-4 font-bold text-gray-900">
                                    {{ student.student_name }}
                                </td>
                                <td class="px-5 py-4 font-mono text-blue-800">
                                    {{ student.registration_number }}
                                </td>
                                <td class="px-5 py-4">
                                    <input
                                        v-model.number="
                                            form.grades[student.enrollment_id]
                                                .semester_work_grade
                                        "
                                        type="number"
                                        min="0"
                                        max="40"
                                        step="1"
                                        class="mx-auto block w-28 rounded-lg border border-gray-300 px-3 py-2 text-center font-bold focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none"
                                    />
                                    <p
                                        v-if="
                                            fieldError(
                                                student.enrollment_id,
                                                'semester_work_grade',
                                            )
                                        "
                                        class="mt-2 text-center text-xs font-bold text-red-600"
                                    >
                                        {{
                                            fieldError(
                                                student.enrollment_id,
                                                'semester_work_grade',
                                            )
                                        }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <input
                                        v-model.number="
                                            form.grades[student.enrollment_id]
                                                .final_exam_grade
                                        "
                                        type="number"
                                        min="0"
                                        max="60"
                                        step="1"
                                        class="mx-auto block w-28 rounded-lg border border-gray-300 px-3 py-2 text-center font-bold focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none"
                                    />
                                    <p
                                        v-if="
                                            fieldError(
                                                student.enrollment_id,
                                                'final_exam_grade',
                                            )
                                        "
                                        class="mt-2 text-center text-xs font-bold text-red-600"
                                    >
                                        {{
                                            fieldError(
                                                student.enrollment_id,
                                                'final_exam_grade',
                                            )
                                        }}
                                    </p>
                                </td>
                                <td
                                    class="px-5 py-4 text-center text-lg font-extrabold text-blue-800"
                                >
                                    {{ totalFor(student.enrollment_id) }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800"
                                    >
                                        {{
                                            regulationEvaluationFor(
                                                totalFor(student.enrollment_id),
                                            )
                                        }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="students.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center text-gray-500"
                                >
                                    Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø·Ù„Ø§Ø¨ Ù…Ø³Ø¬Ù„ÙˆÙ† ÙÙŠ
                                    Ù‡Ø°Ù‡ Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹Ø© Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠØ©
                                    Ù„Ù‡Ø°Ø§ Ø§Ù„Ù…Ù‚Ø±Ø± Ø­Ø§Ù„ÙŠØ§.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t bg-gray-50 p-5"
                >
                    <p class="text-sm text-gray-600">
                        Ø³ÙŠØªÙ… Ø­ÙØ¸ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª Ù„Ù„Ø·Ù„Ø§Ø¨
                        Ø§Ù„Ù…Ø³Ø¬Ù„ÙŠÙ† ÙÙŠ Ù‡Ø°Ù‡ Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹Ø©
                        Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠØ© ÙÙ‚Ø·.
                    </p>
                    <button
                        type="submit"
                        :disabled="form.processing || students.length === 0"
                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-orange-600 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Save class="h-5 w-5" />
                        {{
                            form.processing
                                ? 'Ø¬Ø§Ø± Ø§Ù„Ø­ÙØ¸...'
                                : 'Ø­ÙØ¸ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>
