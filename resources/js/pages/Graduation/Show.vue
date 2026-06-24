<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    Award,
    BookOpen,
    CheckCircle2,
    FileText,
    GraduationCap,
    Printer,
    ShieldCheck,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface CourseRow {
    id: number;
    code: string;
    name: string;
    units: number;
    semester_level: number;
    total_mark?: number;
    grade_evaluation?: string | null;
}

interface EnrollmentRow {
    id: number;
    semester: string;
    course_code: string;
    course_name: string;
    units: number;
    semester_work: number;
    final_exam: number;
    total_mark: number;
    grade_evaluation?: string | null;
    status: string;
}

const props = defineProps<{
    student: {
        id: number;
        registration_number: string;
        full_name: string;
        status: string;
        current_specialization?: { name: string; department?: { name: string } | null } | null;
    };
    eligibility: {
        eligible: boolean;
        cgpa: number;
        total_units: number;
        required_courses: CourseRow[];
        passed_courses: CourseRow[];
        missing_courses: CourseRow[];
        failed_courses: CourseRow[];
        reasons: string[];
    };
    graduationRecord?: {
        id: number;
        certificate_number: string;
        graduation_date: string;
    } | null;
    enrollments: EnrollmentRow[];
}>();

const completionPercent = computed(() => {
    const required = props.eligibility.required_courses.length;

    if (required === 0) {
        return 0;
    }

    return Math.min(100, Math.round((props.eligibility.passed_courses.length / required) * 100));
});

const blockedCount = computed(() => props.eligibility.missing_courses.length + props.eligibility.failed_courses.length);

const approve = () => {
    if (!confirm('اعتماد تخرج الطالب وإصدار رقم شهادة رسمي؟')) {
        return;
    }

    router.post(`/graduations/${props.student.id}/approve`);
};
</script>

<template>
    <Head :title="`ملف التخرج | ${student.full_name}`" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <Link href="/graduations" class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 hover:text-sky-800">
                <ArrowRight class="h-4 w-4" />
                العودة إلى إدارة الخريجين
            </Link>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-900 px-6 py-5 text-white">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-300">
                                <GraduationCap class="h-4 w-4" />
                                <span>ملف التخرج</span>
                                <span class="font-mono" dir="ltr">{{ student.registration_number }}</span>
                            </div>
                            <h1 class="mt-2 break-words text-2xl font-black tracking-normal text-white sm:text-3xl">
                                {{ student.full_name }}
                            </h1>
                            <p class="mt-2 text-sm text-slate-300">
                                {{ student.current_specialization?.department?.name ?? 'بدون قسم' }}
                                <span class="mx-2 text-slate-500">/</span>
                                {{ student.current_specialization?.name ?? 'بدون تخصص' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                v-if="graduationRecord"
                                :href="`/graduation-records/${graduationRecord.id}/certificate`"
                                class="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-sm hover:bg-slate-100"
                            >
                                <Printer class="h-4 w-4" />
                                الشهادة
                            </a>
                            <a
                                v-if="graduationRecord"
                                :href="`/graduation-records/${graduationRecord.id}/study-report`"
                                class="inline-flex items-center gap-2 rounded-md bg-sky-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-sky-700"
                            >
                                <FileText class="h-4 w-4" />
                                التقرير التفصيلي
                            </a>
                            <button
                                v-if="!graduationRecord && eligibility.eligible"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700"
                                @click="approve"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                اعتماد التخرج
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="graduationRecord" class="grid gap-3 border-b border-emerald-100 bg-emerald-50 px-6 py-4 text-sm md:grid-cols-3">
                    <div>
                        <span class="text-emerald-700">رقم الشهادة</span>
                        <p class="mt-1 font-mono font-bold text-emerald-950" dir="ltr">{{ graduationRecord.certificate_number }}</p>
                    </div>
                    <div>
                        <span class="text-emerald-700">تاريخ التخرج</span>
                        <p class="mt-1 font-bold text-emerald-950">{{ graduationRecord.graduation_date }}</p>
                    </div>
                    <div>
                        <span class="text-emerald-700">حالة الطالب</span>
                        <p class="mt-1 font-bold text-emerald-950">{{ student.status }}</p>
                    </div>
                </div>

                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-md border border-slate-200 p-4">
                        <p class="flex items-center gap-2 text-sm font-bold text-slate-500">
                            <ShieldCheck class="h-4 w-4" />
                            نتيجة الأهلية
                        </p>
                        <p class="mt-3 flex items-center gap-2 text-lg font-black" :class="eligibility.eligible ? 'text-emerald-700' : 'text-amber-700'">
                            <CheckCircle2 v-if="eligibility.eligible" class="h-5 w-5" />
                            <XCircle v-else class="h-5 w-5" />
                            {{ eligibility.eligible ? 'مؤهل للتخرج' : 'غير مكتمل' }}
                        </p>
                    </div>
                    <div class="rounded-md border border-slate-200 p-4">
                        <p class="flex items-center gap-2 text-sm font-bold text-slate-500">
                            <Award class="h-4 w-4" />
                            المعدل التراكمي
                        </p>
                        <p class="mt-3 text-lg font-black text-slate-900" dir="ltr">{{ eligibility.cgpa.toFixed(2) }}%</p>
                    </div>
                    <div class="rounded-md border border-slate-200 p-4">
                        <p class="flex items-center gap-2 text-sm font-bold text-slate-500">
                            <BookOpen class="h-4 w-4" />
                            الوحدات المنجزة
                        </p>
                        <p class="mt-3 text-lg font-black text-slate-900">{{ eligibility.total_units }}</p>
                    </div>
                    <div class="rounded-md border border-slate-200 p-4">
                        <p class="text-sm font-bold text-slate-500">اكتمال الخطة</p>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full rounded-full bg-sky-600" :style="{ width: `${completionPercent}%` }" />
                            </div>
                            <span class="font-mono text-sm font-black text-slate-900" dir="ltr">{{ completionPercent }}%</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ eligibility.passed_courses.length }} من {{ eligibility.required_courses.length }} مقرر
                        </p>
                    </div>
                </div>
            </section>

            <section v-if="eligibility.reasons.length" class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-900">
                <div class="flex items-start gap-3">
                    <XCircle class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-black">أسباب عدم الأهلية</p>
                        <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                            <li v-for="reason in eligibility.reasons" :key="reason">{{ reason }}</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black text-slate-900">المقررات الناقصة</h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ eligibility.missing_courses.length }}</span>
                    </div>
                    <div class="mt-4 max-h-[360px] overflow-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead class="sticky top-0 bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-3 py-2 text-start">الكود</th>
                                    <th class="px-3 py-2 text-start">المقرر</th>
                                    <th class="px-3 py-2 text-center">الوحدات</th>
                                    <th class="px-3 py-2 text-center">المستوى</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="course in eligibility.missing_courses" :key="course.id">
                                    <td class="px-3 py-2 font-mono text-xs" dir="ltr">{{ course.code }}</td>
                                    <td class="px-3 py-2 font-bold text-slate-900">{{ course.name }}</td>
                                    <td class="px-3 py-2 text-center">{{ course.units }}</td>
                                    <td class="px-3 py-2 text-center">{{ course.semester_level }}</td>
                                </tr>
                                <tr v-if="eligibility.missing_courses.length === 0">
                                    <td colspan="4" class="px-3 py-8 text-center text-slate-500">لا توجد مقررات ناقصة.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black text-slate-900">المقررات الراسبة</h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ eligibility.failed_courses.length }}</span>
                    </div>
                    <div class="mt-4 max-h-[360px] overflow-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead class="sticky top-0 bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-3 py-2 text-start">الكود</th>
                                    <th class="px-3 py-2 text-start">المقرر</th>
                                    <th class="px-3 py-2 text-center">المجموع</th>
                                    <th class="px-3 py-2 text-center">التقدير</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="course in eligibility.failed_courses" :key="course.id">
                                    <td class="px-3 py-2 font-mono text-xs" dir="ltr">{{ course.code }}</td>
                                    <td class="px-3 py-2 font-bold text-slate-900">{{ course.name }}</td>
                                    <td class="px-3 py-2 text-center">{{ course.total_mark ?? '-' }}</td>
                                    <td class="px-3 py-2 text-center">{{ course.grade_evaluation ?? '-' }}</td>
                                </tr>
                                <tr v-if="eligibility.failed_courses.length === 0">
                                    <td colspan="4" class="px-3 py-8 text-center text-slate-500">لا توجد مقررات راسبة.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">السجل الدراسي</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ enrollments.length }} مقرر مسجل، و {{ blockedCount }} مقرر يحتاج متابعة قبل الاعتماد.
                        </p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr>
                                <th class="px-3 py-3 text-start">الفصل</th>
                                <th class="px-3 py-3 text-start">المقرر</th>
                                <th class="px-3 py-3 text-center">الوحدات</th>
                                <th class="px-3 py-3 text-center">الأعمال</th>
                                <th class="px-3 py-3 text-center">النهائي</th>
                                <th class="px-3 py-3 text-center">المجموع</th>
                                <th class="px-3 py-3 text-center">التقدير</th>
                                <th class="px-3 py-3 text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="enrollment in enrollments" :key="enrollment.id" class="hover:bg-slate-50">
                                <td class="px-3 py-3 font-mono text-xs" dir="ltr">{{ enrollment.semester }}</td>
                                <td class="px-3 py-3">
                                    <p class="font-bold text-slate-900">{{ enrollment.course_name }}</p>
                                    <p class="font-mono text-xs text-slate-500" dir="ltr">{{ enrollment.course_code }}</p>
                                </td>
                                <td class="px-3 py-3 text-center">{{ enrollment.units }}</td>
                                <td class="px-3 py-3 text-center">{{ enrollment.semester_work }}</td>
                                <td class="px-3 py-3 text-center">{{ enrollment.final_exam }}</td>
                                <td class="px-3 py-3 text-center font-black">{{ enrollment.total_mark }}</td>
                                <td class="px-3 py-3 text-center">{{ enrollment.grade_evaluation ?? '-' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                        {{ enrollment.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="enrollments.length === 0">
                                <td colspan="8" class="px-3 py-10 text-center text-slate-500">لا توجد مقررات مسجلة لهذا الطالب.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</template>
