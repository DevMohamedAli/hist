<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    GraduationCap,
    Hash,
    MoveLeft,
    PauseCircle,
    Target,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

// ---------- Types ----------
interface Department {
    name: string;
}

interface Specialization {
    id: number;
    name: string;
    department: Department;
}

interface Semester {
    id: number;
    code: string;
}

interface Student {
    id: number;
    registration_number: string;
    full_name: string;
    status: string;
    current_specialization: Specialization;
}

interface Enrollment {
    id: number;
    semester_code: string;
    course_code: string;
    course_name: string;
    units: number;
    semester_work_grade: number | string;
    final_exam_grade: number | string;
    total_grade: number | string;
    grade_evaluation: string | null;
}

interface Summary {
    id: number;
    semester_code: string;
    semester_gpa: number | string;
    cumulative_gpa: number | string;
    total_registered_units: number;
    result: string;
    evaluation: string;
    carried_courses_count: number;
}

const props = withDefaults(
    defineProps<{
        student: Student;
        enrollments: Enrollment[];
        summaries: Summary[];
        semesters?: Semester[];
        specializations?: Specialization[];
        enrollmentAvailability?: {
            is_open: boolean;
            semester: {
                id: number;
                code: string;
                registration_start: string | null;
                registration_end: string | null;
            } | null;
            message: string;
        };
        transferEligibility?: {
            can_transfer: boolean;
            has_transferred_before: boolean;
            message: string;
        };
    }>(),
    {
        semesters: () => [],
        specializations: () => [],
        enrollmentAvailability: () => ({
            is_open: false,
            semester: null,
            message: 'تسجيل وتنزيل المقررات غير متاح حاليا.',
        }),
        transferEligibility: () => ({
            can_transfer: false,
            has_transferred_before: false,
            message: 'انتقال التخصص غير متاح حاليا.',
        }),
    },
);

// ---------- Modals state ----------
const showSuspendModal = ref(false);
const showTransferModal = ref(false);

// ---------- Suspension form ----------
const today = new Date().toISOString().slice(0, 10);
const suspendForm = useForm({
    semester_id: '',
    suspension_reason: '',
    approval_date: today,
});

const submitSuspension = () => {
    suspendForm.post(`/students/${props.student.id}/suspend`, {
        preserveScroll: true,
        onSuccess: () => {
            showSuspendModal.value = false;
            suspendForm.reset('semester_id', 'suspension_reason');
            suspendForm.approval_date = today;
        },
    });
};

// ---------- Transfer form ----------
const transferForm = useForm({
    to_specialization_id: '',
    transfer_date: today,
    approval_reference: '',
    transfer: '',
});

const submitTransfer = () => {
    if (!props.transferEligibility.can_transfer) {
        return;
    }

    transferForm.post(`/students/${props.student.id}/transfer`, {
        preserveScroll: true,
        onSuccess: () => {
            showTransferModal.value = false;
            transferForm.reset('to_specialization_id', 'approval_reference');
            transferForm.transfer_date = today;
        },
    });
};

// ---------- Helpers ----------
const getStatusInfo = (status: string) => {
    const map = {
        Active: {
            label: 'مقيد نشط',
            classes: 'bg-emerald-100 text-emerald-900 border-emerald-300',
        },
        Suspended: {
            label: 'موقوف القيد',
            classes: 'bg-amber-100 text-amber-900 border-amber-300',
        },
        Graduated: {
            label: 'متخرج',
            classes: 'bg-blue-100 text-blue-900 border-blue-300',
        },
        Withdrawn: {
            label: 'منسحب',
            classes: 'bg-red-100 text-red-900 border-red-300',
        },
        Dismissed: {
            label: 'مفصول',
            classes: 'bg-red-100 text-red-900 border-red-300',
        },
        Transferred_Out: {
            label: 'منقول خارجياً',
            classes: 'bg-sky-100 text-sky-900 border-sky-300',
        },
    };

    return (
        map[status as keyof typeof map] || {
            label: status,
            classes: 'bg-gray-100 text-gray-900 border-gray-300',
        }
    );
};

const gradeToColor = (evaluation: string | null) => {
    if (!evaluation) {
        return '';
    }

    const map: Record<string, string> = {
        ممتاز: 'bg-emerald-600 text-white',
        'جيد جداً': 'bg-blue-600 text-white',
        جيد: 'bg-sky-500 text-white',
        مقبول: 'bg-amber-500 text-white',
        ضعيف: 'bg-red-500 text-white',
        'ضعيف جداً': 'bg-red-700 text-white',
        معادلة: 'bg-purple-600 text-white',
    };

    return map[evaluation] || 'bg-gray-200 text-gray-800';
};
</script>

<template>
    <Head :title="`ملف الطالب | ${student.full_name}`" />

    <main
        class="font-cairo min-h-screen bg-[#f4f5f9] p-4 text-slate-900 sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-7xl space-y-8">
            <!-- الترويسة وأزرار الإجراءات (GOV.UK Style) -->
            <section
                class="flex flex-col gap-6 border-b-4 border-slate-900 pb-6 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <span
                        class="mb-3 inline-block border border-blue-300 bg-blue-100 px-3 py-1 text-sm font-bold text-blue-900"
                    >
                        الملف الأكاديمي الشامل
                    </span>
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                    >
                        {{ student.full_name }}
                    </h1>
                    <p class="mt-2 font-mono text-lg font-bold text-slate-700">
                        رقم القيد: {{ student.registration_number }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        @click="showSuspendModal = true"
                        class="inline-flex items-center gap-2 border-b-4 border-slate-900 bg-slate-700 px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-slate-800 active:translate-y-1 active:border-b-0"
                    >
                        <PauseCircle class="h-4 w-4" />
                        إيقاف القيد
                    </button>
                    <button
                        v-if="transferEligibility.can_transfer"
                        type="button"
                        @click="showTransferModal = true"
                        class="inline-flex items-center gap-2 border-b-4 border-slate-900 bg-slate-700 px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-slate-800 active:translate-y-1 active:border-b-0"
                    >
                        <MoveLeft class="h-4 w-4" />
                        انتقال التخصص
                    </button>
                    <div
                        v-else
                        class="max-w-xs border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700"
                    >
                        {{ transferEligibility.message }}
                    </div>
                    <Link
                        v-if="enrollmentAvailability.is_open"
                        :href="`/students/${student.id}/enroll`"
                        class="inline-flex items-center gap-2 border-b-4 border-orange-800 bg-orange-600 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-orange-700 active:translate-y-1 active:border-b-0"
                    >
                        <BookOpen class="h-4 w-4" />
                        تسجيل وتنزيل مقررات
                    </Link>
                    <div
                        v-else
                        class="max-w-xs border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-900"
                    >
                        {{ enrollmentAvailability.message }}
                    </div>
                </div>
            </section>

            <!-- شبكة بيانات الطالب (Density Layout) -->
            <section
                class="overflow-hidden border-2 border-slate-300 bg-white p-0 shadow-sm"
            >
                <div
                    class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 sm:divide-x-reverse lg:grid-cols-4"
                >
                    <div
                        class="flex items-center gap-4 p-5 transition-colors hover:bg-slate-50"
                    >
                        <div class="rounded bg-blue-50 p-3 text-blue-700">
                            <Hash class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                رقم القيد الوطني
                            </p>
                            <p
                                class="font-mono text-lg font-bold text-slate-900"
                            >
                                {{ student.registration_number }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-4 p-5 transition-colors hover:bg-slate-50"
                    >
                        <div class="rounded bg-blue-50 p-3 text-blue-700">
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                الاسم بالكامل
                            </p>
                            <p class="text-lg font-bold text-slate-900">
                                {{ student.full_name }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-4 p-5 transition-colors hover:bg-slate-50"
                    >
                        <div class="rounded bg-blue-50 p-3 text-blue-700">
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                القسم والشعبة
                            </p>
                            <p class="font-bold text-slate-900">
                                {{ student.current_specialization.name }}
                            </p>
                            <p class="text-xs font-semibold text-slate-600">
                                {{
                                    student.current_specialization.department
                                        .name
                                }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-4 p-5 transition-colors hover:bg-slate-50"
                    >
                        <div class="rounded bg-blue-50 p-3 text-blue-700">
                            <Target class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="mb-1 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                الحالة الأكاديمية
                            </p>
                            <span
                                class="inline-block border px-3 py-1 text-xs font-bold"
                                :class="getStatusInfo(student.status).classes"
                            >
                                {{ getStatusInfo(student.status).label }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- الخلاصات الأكاديمية (محاكاة دقيقة لنهاية سطر الإكسل) -->
            <section>
                <h2
                    class="mb-4 flex items-center gap-2 text-2xl font-bold text-slate-900"
                >
                    <Award class="h-6 w-6 text-orange-600" />
                    الخلاصات التراكمية ونتائج الفصول
                </h2>

                <div
                    class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="summary in summaries"
                        :key="summary.id"
                        class="relative border-2 border-t-8 border-slate-300 border-t-blue-800 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 flex items-start justify-between border-b border-slate-200 pb-3"
                        >
                            <h3
                                class="font-mono text-xl font-bold text-slate-900"
                            >
                                {{ summary.semester_code }}
                            </h3>
                            <span
                                class="border px-3 py-1 text-sm font-bold"
                                :class="gradeToColor(summary.evaluation)"
                            >
                                {{ summary.evaluation }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between border border-slate-100 bg-slate-50 p-2"
                            >
                                <span class="text-sm font-bold text-slate-600"
                                    >المتوسط الفصلي:</span
                                >
                                <span class="font-mono text-lg font-bold"
                                    >{{ summary.semester_gpa }}%</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border border-slate-100 bg-slate-50 p-2"
                            >
                                <span class="text-sm font-bold text-slate-600"
                                    >المعدل التراكمي:</span
                                >
                                <span class="font-mono text-lg font-bold"
                                    >{{ summary.cumulative_gpa }}%</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border border-slate-100 bg-slate-50 p-2"
                            >
                                <span class="text-sm font-bold text-slate-600"
                                    >مجموع الوحدات:</span
                                >
                                <span class="font-mono text-lg font-bold">{{
                                    summary.total_registered_units
                                }}</span>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex items-center gap-2 border-2 p-3"
                            :class="
                                summary.carried_courses_count > 0
                                    ? 'border-amber-200 bg-amber-50 text-amber-900'
                                    : 'border-emerald-200 bg-emerald-50 text-emerald-900'
                            "
                        >
                            <AlertTriangle
                                v-if="summary.carried_courses_count > 0"
                                class="h-5 w-5 flex-shrink-0"
                            />
                            <CheckCircle2
                                v-else
                                class="h-5 w-5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-base font-bold">
                                    النتيجة: {{ summary.result }}
                                </p>
                                <p
                                    v-if="summary.carried_courses_count > 0"
                                    class="mt-0.5 text-xs font-bold"
                                >
                                    الطالب يحمل ({{
                                        summary.carried_courses_count
                                    }}) مواد معه.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="summaries.length === 0"
                    class="border-2 border-dashed border-slate-300 bg-slate-100 p-10 text-center"
                >
                    <Clock class="mx-auto mb-3 h-12 w-12 text-slate-400" />
                    <p class="text-lg font-bold text-slate-600">
                        لا توجد سجلات لنتائج فصول سابقة معتمدة.
                    </p>
                </div>
            </section>

            <!-- جدول المقررات والدرجات (تصميم يحاكي ملف الإكسل المرفق تماماً) -->
            <section>
                <h2
                    class="mb-4 flex items-center gap-2 text-2xl font-bold text-slate-900"
                >
                    <BookOpen class="h-6 w-6 text-orange-600" />
                    كشف الدرجات والمقررات التفصيلي
                </h2>

                <div
                    class="overflow-x-auto border-2 border-slate-400 bg-white shadow-sm"
                >
                    <table class="w-full border-collapse text-start">
                        <!-- ترويسة الجدول بالأزرق الداكن كما في الكشف -->
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th
                                    class="w-24 border border-slate-700 p-3 text-center font-bold"
                                >
                                    الفصل
                                </th>
                                <th
                                    class="border border-slate-700 p-3 text-start font-bold"
                                >
                                    اسم المادة
                                </th>
                                <th
                                    class="w-24 border border-slate-700 p-3 text-center font-bold"
                                >
                                    كود المادة
                                </th>
                                <th
                                    class="w-20 border border-slate-700 p-3 text-center font-bold"
                                >
                                    الوحدات
                                </th>
                                <th
                                    class="w-28 border border-slate-700 bg-[#047857] p-3 text-center font-bold"
                                >
                                    أعمال الفصل
                                </th>
                                <th
                                    class="w-28 border border-slate-700 bg-[#c2410c] p-3 text-center font-bold"
                                >
                                    النهائي
                                </th>
                                <th
                                    class="w-28 border border-slate-700 bg-[#ca8a04] p-3 text-center font-bold"
                                >
                                    المجموع
                                </th>
                                <th
                                    class="w-28 border border-slate-700 p-3 text-center font-bold"
                                >
                                    التقدير
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900">
                            <tr
                                v-for="course in enrollments"
                                :key="course.id"
                                class="transition-colors hover:bg-slate-50"
                            >
                                <td
                                    class="border border-slate-300 bg-slate-50 p-3 text-center font-mono text-sm font-bold"
                                >
                                    {{ course.semester_code }}
                                </td>
                                <td
                                    class="border border-slate-300 p-3 font-bold"
                                >
                                    {{ course.course_name }}
                                </td>
                                <td
                                    class="border border-slate-300 p-3 text-center font-mono text-xs text-slate-500"
                                >
                                    {{ course.course_code }}
                                </td>
                                <td
                                    class="border border-slate-300 p-3 text-center font-bold text-slate-700"
                                >
                                    {{ course.units }}
                                </td>

                                <!-- محاكاة ألوان خلايا الإكسل للدرجات -->
                                <td
                                    class="border border-slate-300 bg-[#ecfdf5] p-3 text-center font-mono text-lg font-bold text-[#065f46]"
                                >
                                    {{ course.semester_work_grade }}
                                </td>
                                <td
                                    class="border border-slate-300 bg-[#fff7ed] p-3 text-center font-mono text-lg font-bold text-[#9a3412]"
                                >
                                    {{ course.final_exam_grade }}
                                </td>
                                <td
                                    class="border border-slate-300 bg-[#fefce8] p-3 text-center font-mono text-xl font-extrabold text-[#854d0e]"
                                >
                                    {{ course.total_grade }}
                                </td>

                                <td
                                    class="border border-slate-300 p-3 text-center"
                                >
                                    <span
                                        v-if="course.grade_evaluation"
                                        class="inline-block border px-3 py-1 text-xs font-bold"
                                        :class="
                                            gradeToColor(
                                                course.grade_evaluation,
                                            )
                                        "
                                    >
                                        {{ course.grade_evaluation }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-xs font-bold text-slate-400"
                                        >قيد الدراسة</span
                                    >
                                </td>
                            </tr>
                            <tr v-if="enrollments.length === 0">
                                <td
                                    colspan="8"
                                    class="bg-slate-50 p-10 text-center text-lg font-bold text-slate-500"
                                >
                                    لا توجد مقررات مسجلة في ملف الطالب بعد.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Modals (النوافذ المنبثقة الصارمة بأسلوب GOV.UK) -->
            <!-- نافذة إيقاف القيد -->
            <div
                v-if="showSuspendModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg border-t-8 border-slate-800 bg-white shadow-2xl"
                >
                    <div
                        class="flex items-start justify-between border-b border-slate-200 p-6"
                    >
                        <h2 class="text-2xl font-bold text-slate-900">
                            إيقاف قيد الطالب
                        </h2>
                        <button
                            @click="showSuspendModal = false"
                            class="text-slate-400 transition-colors hover:text-red-600"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>
                    <form
                        class="space-y-5 p-6"
                        @submit.prevent="submitSuspension"
                    >
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-slate-800"
                                >الفصل الدراسي المراد إيقافه
                                <span class="text-red-600">*</span></label
                            >
                            <select
                                v-model="suspendForm.semester_id"
                                required
                                class="rounded-none border-2 border-slate-400 bg-white p-3 font-semibold outline-none focus:border-blue-700"
                            >
                                <option value="" disabled>
                                    اختر الفصل الدراسي
                                </option>
                                <option
                                    v-for="sem in semesters"
                                    :key="sem.id"
                                    :value="sem.id"
                                >
                                    {{ sem.code }}
                                </option>
                            </select>
                            <p
                                v-if="suspendForm.errors.semester_id"
                                class="text-sm font-bold text-red-600"
                            >
                                {{ suspendForm.errors.semester_id }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-slate-800"
                                >تاريخ الموافقة الإدارية
                                <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model="suspendForm.approval_date"
                                type="date"
                                required
                                class="rounded-none border-2 border-slate-400 bg-white p-3 font-mono outline-none focus:border-blue-700"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-slate-800"
                                >سبب الإيقاف (للحفظ بالسجل)
                                <span class="text-red-600">*</span></label
                            >
                            <textarea
                                v-model="suspendForm.suspension_reason"
                                rows="3"
                                required
                                placeholder="أدخل العذر القاهر لإيقاف القيد..."
                                class="resize-none rounded-none border-2 border-slate-400 bg-white p-3 outline-none focus:border-blue-700"
                            ></textarea>
                        </div>
                        <div
                            class="mt-8 flex flex-col gap-3 sm:flex-row-reverse"
                        >
                            <button
                                type="submit"
                                :disabled="suspendForm.processing"
                                class="flex-1 border-b-4 border-black bg-slate-800 px-4 py-3 text-lg font-bold text-white transition-all hover:bg-slate-900 active:translate-y-1 active:border-b-0"
                            >
                                {{
                                    suspendForm.processing
                                        ? 'جار الحفظ...'
                                        : 'تأكيد إيقاف القيد'
                                }}
                            </button>
                            <button
                                type="button"
                                @click="showSuspendModal = false"
                                class="flex-1 border-b-4 border-slate-400 bg-slate-200 px-4 py-3 text-lg font-bold text-slate-900 transition-all hover:bg-slate-300 active:translate-y-1 active:border-b-0"
                            >
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- نافذة انتقال التخصص -->
            <div
                v-if="showTransferModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg border-t-8 border-blue-800 bg-white shadow-2xl"
                >
                    <div
                        class="flex items-start justify-between border-b border-slate-200 p-6"
                    >
                        <h2 class="text-2xl font-bold text-slate-900">
                            انتقال التخصص (تغيير مسار)
                        </h2>
                        <button
                            @click="showTransferModal = false"
                            class="text-slate-400 transition-colors hover:text-red-600"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>
                    <form
                        class="space-y-5 p-6"
                        @submit.prevent="submitTransfer"
                    >
                        <div
                            v-if="transferForm.errors.transfer"
                            class="border-l-4 border-red-600 bg-red-50 p-4 font-bold text-red-800"
                        >
                            {{ transferForm.errors.transfer }}
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-slate-800"
                                >التخصص الجديد المستهدف
                                <span class="text-red-600">*</span></label
                            >
                            <select
                                v-model="transferForm.to_specialization_id"
                                required
                                class="rounded-none border-2 border-slate-400 bg-white p-3 font-semibold outline-none focus:border-blue-700"
                            >
                                <option value="" disabled>
                                    اختر التخصص الجديد
                                </option>
                                <option
                                    v-for="spec in specializations"
                                    :key="spec.id"
                                    :value="spec.id"
                                    :disabled="
                                        spec.id ===
                                        student.current_specialization.id
                                    "
                                >
                                    {{ spec.department.name }} - {{ spec.name }}
                                </option>
                            </select>
                            <p
                                v-if="transferForm.errors.to_specialization_id"
                                class="text-sm font-bold text-red-600"
                            >
                                {{ transferForm.errors.to_specialization_id }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-slate-800"
                                >تاريخ قرار الانتقال
                                <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model="transferForm.transfer_date"
                                type="date"
                                required
                                class="rounded-none border-2 border-slate-400 bg-white p-3 font-mono outline-none focus:border-blue-700"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-slate-800"
                                >رقم مرجع الموافقة (قرار اللجنة)
                                <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model="transferForm.approval_reference"
                                type="text"
                                required
                                placeholder="مثال: قرار إداري رقم 15 لسنة..."
                                class="rounded-none border-2 border-slate-400 bg-white p-3 font-bold outline-none focus:border-blue-700"
                            />
                        </div>
                        <div
                            class="mt-8 flex flex-col gap-3 sm:flex-row-reverse"
                        >
                            <button
                                type="submit"
                                :disabled="transferForm.processing"
                                class="flex-1 border-b-4 border-blue-900 bg-blue-700 px-4 py-3 text-lg font-bold text-white transition-all hover:bg-blue-800 active:translate-y-1 active:border-b-0"
                            >
                                {{
                                    transferForm.processing
                                        ? 'جار التنفيذ...'
                                        : 'تأكيد ونقل الطالب'
                                }}
                            </button>
                            <button
                                type="button"
                                @click="showTransferModal = false"
                                class="flex-1 border-b-4 border-slate-400 bg-slate-200 px-4 py-3 text-lg font-bold text-slate-900 transition-all hover:bg-slate-300 active:translate-y-1 active:border-b-0"
                            >
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>
