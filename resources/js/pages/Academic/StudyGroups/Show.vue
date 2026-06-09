<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronRight,
    Building2,
    CalendarRange,
    Layers,
    Users,
    BookOpen,
} from 'lucide-vue-next';

// ── Types ──────────────────────────────────────────────
interface Course {
    id: number;
    code: string;
    name: string;
    units: number;
}

interface Enrollment {
    id: number;
    course: Course;
}

interface Student {
    id: number;
    full_name: string;
    registration_number: string;
    enrollments?: Enrollment[]; // العلاقة الجديدة التي ستجلب المقررات
}

interface StudyGroup {
    id: number;
    group_name: string;
    semester_level: number;
    capacity: number;
    actual_students_count?: number; // العداد الحقيقي للطلاب
    specialization?: {
        id: number;
        name: string;
        department?: { name: string } | null;
    } | null;
    academic_semester?: {
        id: number;
        code: string;
    } | null;
    students?: Student[] | null;
}

defineProps<{
    studyGroup: StudyGroup;
}>();

// دالة مساعدة لحساب إجمالي وحدات الطالب داخل هذه المجموعة
const calculateTotalUnits = (enrollments?: Enrollment[]) => {
    if (!enrollments || enrollments.length === 0) {
        return 0;
    }

    return enrollments.reduce(
        (sum, enrollment) => sum + (enrollment.course?.units || 0),
        0,
    );
};
</script>

<template>
    <Head :title="studyGroup.group_name" />

    <main
        class="font-cairo min-h-screen bg-[#f4f5f9] p-4 sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-6xl space-y-8">
            <!-- زر العودة -->
            <Link
                href="/academic/study-groups"
                class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition-colors hover:text-blue-800"
            >
                <ChevronRight class="h-4 w-4" />
                العودة إلى المجموعات التدريسية
            </Link>

            <!-- الترويسة وبطاقة المعلومات الرئيسية -->
            <section
                class="rounded-xl border border-t-8 border-blue-900 border-slate-200 bg-white p-6 shadow-sm"
            >
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center"
                >
                    <div>
                        <span
                            class="mb-2 inline-block border border-blue-200 bg-blue-100 px-3 py-1 text-xs font-bold text-blue-900"
                        >
                            بطاقة المجموعة الدراسية
                        </span>
                        <h1
                            class="text-3xl font-extrabold tracking-tight text-slate-900"
                        >
                            {{ studyGroup.group_name }}
                        </h1>
                    </div>
                    <Link
                        :href="`/academic/study-groups/${studyGroup.id}/edit`"
                        class="border-b-4 border-orange-700 bg-orange-500 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-orange-600 active:translate-y-1 active:border-b-0"
                    >
                        تعديل بيانات المجموعة
                    </Link>
                </div>

                <!-- إحصائيات سريعة (Density Layout) -->
                <div
                    class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        class="border border-slate-200 bg-slate-50 p-4 transition-colors hover:border-blue-300"
                    >
                        <Building2 class="mb-2 h-6 w-6 text-blue-800" />
                        <p
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                        >
                            التخصص
                        </p>
                        <p
                            class="mt-1 truncate text-lg font-bold text-slate-900"
                        >
                            {{ studyGroup.specialization?.name ?? '—' }}
                        </p>
                        <p
                            v-if="studyGroup.specialization?.department"
                            class="truncate text-xs font-semibold text-slate-600"
                        >
                            {{ studyGroup.specialization.department.name }}
                        </p>
                    </div>
                    <div
                        class="border border-slate-200 bg-slate-50 p-4 transition-colors hover:border-blue-300"
                    >
                        <CalendarRange class="mb-2 h-6 w-6 text-blue-800" />
                        <p
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                        >
                            الفصل الدراسي
                        </p>
                        <p
                            class="mt-1 font-mono text-lg font-bold text-slate-900"
                        >
                            {{ studyGroup.academic_semester?.code ?? '—' }}
                        </p>
                    </div>
                    <div
                        class="border border-slate-200 bg-slate-50 p-4 transition-colors hover:border-blue-300"
                    >
                        <Layers class="mb-2 h-6 w-6 text-blue-800" />
                        <p
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                        >
                            مستوى الدفعة
                        </p>
                        <p class="mt-1 text-lg font-bold text-slate-900">
                            السمستر {{ studyGroup.semester_level }}
                        </p>
                    </div>
                    <div
                        class="border border-slate-200 bg-slate-50 p-4 transition-colors hover:border-blue-300"
                    >
                        <Users class="mb-2 h-6 w-6 text-blue-800" />
                        <p
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                        >
                            الطلاب / السعة
                        </p>
                        <p
                            class="mt-1 text-lg font-bold"
                            :class="
                                (studyGroup.actual_students_count ?? 0) >
                                studyGroup.capacity
                                    ? 'text-red-600'
                                    : 'text-slate-900'
                            "
                        >
                            {{ studyGroup.actual_students_count ?? 0 }} /
                            {{ studyGroup.capacity }} طالب
                        </p>
                    </div>
                </div>
            </section>

            <!-- كشف الطلاب والمقررات المسجلة -->
            <section
                v-if="studyGroup.students"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 p-6"
                >
                    <h2
                        class="flex items-center gap-2 text-2xl font-bold text-slate-900"
                    >
                        <Users class="h-6 w-6 text-orange-600" />
                        الطلاب المسجلين ومقرراتهم
                    </h2>
                    <span
                        class="rounded border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-bold text-blue-800"
                    >
                        العدد: {{ studyGroup.students.length }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-start">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th
                                    class="w-16 border border-slate-700 p-4 text-start font-bold"
                                >
                                    #
                                </th>
                                <th
                                    class="w-32 border border-slate-700 p-4 text-start font-bold"
                                >
                                    رقم القيد
                                </th>
                                <th
                                    class="w-64 border border-slate-700 p-4 text-start font-bold"
                                >
                                    الاسم الكامل
                                </th>
                                <th
                                    class="border border-slate-700 p-4 text-start font-bold"
                                >
                                    المقررات المُنزّلة للطالب في هذه المجموعة
                                </th>
                                <th
                                    class="w-24 border border-slate-700 p-4 text-center font-bold"
                                >
                                    الوحدات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900">
                            <tr
                                v-for="(student, index) in studyGroup.students"
                                :key="student.id"
                                class="border-b border-slate-200 transition-colors hover:bg-slate-50"
                            >
                                <td
                                    class="border-l border-slate-200 p-4 font-bold text-slate-500"
                                >
                                    {{ index + 1 }}
                                </td>

                                <td
                                    class="border-l border-slate-200 p-4 font-mono font-bold text-blue-800"
                                >
                                    {{ student.registration_number }}
                                </td>

                                <td
                                    class="border-l border-slate-200 p-4 font-bold text-slate-800"
                                >
                                    {{ student.full_name }}
                                </td>

                                <!-- خلية المقررات (تعرض المواد كشارات Badges) -->
                                <td class="border-l border-slate-200 p-4">
                                    <div
                                        v-if="
                                            student.enrollments &&
                                            student.enrollments.length > 0
                                        "
                                        class="flex flex-wrap gap-2"
                                    >
                                        <span
                                            v-for="enrollment in student.enrollments"
                                            :key="enrollment.id"
                                            class="inline-flex cursor-default items-center gap-1.5 rounded border border-slate-300 bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 transition-colors hover:border-slate-400 hover:bg-slate-200"
                                        >
                                            <BookOpen
                                                class="h-3.5 w-3.5 text-blue-600"
                                            />
                                            {{ enrollment.course?.name }}
                                            <span
                                                class="font-mono text-[10px] text-slate-500"
                                                >({{
                                                    enrollment.course?.code
                                                }})</span
                                            >
                                        </span>
                                    </div>
                                    <div
                                        v-else
                                        class="text-xs font-bold text-red-500"
                                    >
                                        لم يقم بتنزيل أي مواد في هذه المجموعة
                                        بعد.
                                    </div>
                                </td>

                                <!-- خلية إجمالي الوحدات -->
                                <td
                                    class="p-4 text-center font-mono text-lg font-extrabold"
                                    :class="
                                        calculateTotalUnits(
                                            student.enrollments,
                                        ) > 24
                                            ? 'bg-red-50 text-red-600'
                                            : 'text-slate-700'
                                    "
                                >
                                    {{
                                        calculateTotalUnits(student.enrollments)
                                    }}
                                </td>
                            </tr>

                            <!-- رسالة عدم وجود طلاب -->
                            <tr v-if="studyGroup.students.length === 0">
                                <td
                                    colspan="5"
                                    class="bg-slate-50 p-12 text-center text-slate-500"
                                >
                                    <Users
                                        class="mx-auto mb-3 h-12 w-12 text-slate-400"
                                    />
                                    <p class="text-lg font-bold">
                                        لا يوجد طلاب مسجلين في هذه المجموعة حتى
                                        الآن.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</template>
