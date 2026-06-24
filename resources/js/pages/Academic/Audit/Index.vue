<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    ClipboardCheck,
    Database,
    RefreshCw,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type AuditRow = Record<string, unknown>;

interface AuditReport {
    generated_at: string;
    seed_smoke: Record<string, number | boolean>;
    checks: Record<string, AuditRow[]>;
    has_issues: boolean;
}

const props = defineProps<{
    report: AuditReport;
}>();

const openChecks = ref<string[]>([]);

const metricLabels: Record<string, string> = {
    departments: 'الأقسام',
    specializations: 'التخصصات',
    semesters: 'الفصول',
    courses: 'المقررات',
    students: 'الطلاب',
    enrollments: 'التنزيلات',
    graduation_records: 'سجلات التخرج',
};

const readinessLabels: Record<string, string> = {
    has_enrollment_fixture: 'وجود تنزيل مقرر',
    has_grade_fixture: 'وجود درجات مرصودة',
    has_curriculum_fixture: 'وجود خطة دراسية',
    has_class_fixture: 'وجود شعب ومحاضرين',
    full_lifecycle_possible: 'مسار كامل قابل للتجربة',
};

const checkLabels: Record<string, string> = {
    specializations_with_no_courses: 'تخصصات بدون مقررات',
    courses_not_attached_to_specialization: 'مقررات غير مرتبطة بتخصص',
    study_groups_without_course_classes: 'مجموعات بدون شعب تدريس',
    course_classes_without_instructor_or_study_group:
        'شعب بدون محاضر أو مجموعة',
    students_with_invalid_status_for_enrollments:
        'طلاب بحالة غير مناسبة ولديهم تنزيلات',
    pending_grades_in_old_semesters: 'درجات معلقة في فصول قديمة',
    graduated_students_without_graduation_records: 'طلاب متخرجون بدون سجل تخرج',
};

const numericMetrics = computed(() =>
    Object.entries(metricLabels).map(([key, label]) => ({
        key,
        label,
        value: Number(props.report.seed_smoke[key] ?? 0),
    })),
);

const readiness = computed(() =>
    Object.entries(readinessLabels).map(([key, label]) => ({
        key,
        label,
        ready: Boolean(props.report.seed_smoke[key]),
    })),
);

const checks = computed(() =>
    Object.entries(checkLabels).map(([key, label]) => ({
        key,
        label,
        count: props.report.checks[key]?.length ?? 0,
        rows: props.report.checks[key] ?? [],
    })),
);

const totalIssues = computed(() =>
    checks.value.reduce((total, check) => total + check.count, 0),
);

const generatedAt = computed(() =>
    new Intl.DateTimeFormat('ar-LY-u-nu-latn', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(props.report.generated_at)),
);

const toggleCheck = (key: string) => {
    openChecks.value = openChecks.value.includes(key)
        ? openChecks.value.filter((item) => item !== key)
        : [...openChecks.value, key];
};

const refresh = () => {
    router.reload({ only: ['report'] });
};

const formatValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'غير محدد';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
};
</script>

<template>
    <Head title="سلامة البيانات الأكاديمية" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="border-b border-slate-200 pb-5">
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p class="text-sm font-bold text-orange-600">
                            مراقبة النظام الأكاديمي
                        </p>
                        <h1 class="mt-1 text-2xl font-black text-blue-950">
                            سلامة البيانات الأكاديمية
                        </h1>
                        <p
                            class="mt-2 max-w-3xl text-sm leading-7 text-slate-600"
                        >
                            مراجعة مباشرة للبيانات الأساسية التي تؤثر على
                            التسجيل، رصد الدرجات، الترفيع، والتخرج.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-900 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800"
                        @click="refresh"
                    >
                        <RefreshCw class="h-4 w-4" />
                        تحديث التقرير
                    </button>
                </div>
            </section>

            <section
                class="grid gap-4 rounded-md border p-5"
                :class="
                    report.has_issues
                        ? 'border-amber-200 bg-amber-50'
                        : 'border-emerald-200 bg-emerald-50'
                "
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-start gap-3">
                        <component
                            :is="
                                report.has_issues ? AlertTriangle : ShieldCheck
                            "
                            class="mt-1 h-7 w-7 shrink-0"
                            :class="
                                report.has_issues
                                    ? 'text-amber-600'
                                    : 'text-emerald-600'
                            "
                        />
                        <div>
                            <h2
                                class="text-lg font-black"
                                :class="
                                    report.has_issues
                                        ? 'text-amber-950'
                                        : 'text-emerald-950'
                                "
                            >
                                {{
                                    report.has_issues
                                        ? 'توجد ملاحظات تحتاج مراجعة'
                                        : 'لا توجد مشاكل مانعة'
                                }}
                            </h2>
                            <p
                                class="mt-1 text-sm"
                                :class="
                                    report.has_issues
                                        ? 'text-amber-800'
                                        : 'text-emerald-800'
                                "
                            >
                                آخر تحديث: {{ generatedAt }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-md bg-white/70 px-4 py-3 text-center shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500">
                            إجمالي الملاحظات
                        </p>
                        <p class="mt-1 text-2xl font-black text-slate-950">
                            {{ totalIssues }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <article
                    v-for="metric in numericMetrics"
                    :key="metric.key"
                    class="rounded-md border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500">
                                {{ metric.label }}
                            </p>
                            <p class="mt-2 text-2xl font-black text-blue-950">
                                {{ metric.value }}
                            </p>
                        </div>
                        <Database class="h-6 w-6 text-blue-700" />
                    </div>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-[0.8fr_1.2fr]">
                <div
                    class="rounded-md border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <ClipboardCheck class="h-5 w-5 text-blue-800" />
                        <h2 class="text-base font-black text-slate-950">
                            جاهزية بيانات التجربة
                        </h2>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="item in readiness"
                            :key="item.key"
                            class="flex items-center justify-between gap-3 rounded-md border border-slate-100 px-3 py-2"
                        >
                            <span class="text-sm font-bold text-slate-700">{{
                                item.label
                            }}</span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-black"
                                :class="
                                    item.ready
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-rose-100 text-rose-700'
                                "
                            >
                                <CheckCircle2
                                    v-if="item.ready"
                                    class="h-3.5 w-3.5"
                                />
                                {{ item.ready ? 'جاهز' : 'غير جاهز' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-md border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="text-base font-black text-slate-950">
                            فحوصات سلامة البيانات
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            اضغط على أي فحص يحتوي ملاحظات لعرض التفاصيل.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <article
                            v-for="check in checks"
                            :key="check.key"
                            class="p-4"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 text-start"
                                @click="toggleCheck(check.key)"
                            >
                                <span class="font-bold text-slate-800">{{
                                    check.label
                                }}</span>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-black"
                                    :class="
                                        check.count > 0
                                            ? 'bg-amber-100 text-amber-700'
                                            : 'bg-emerald-100 text-emerald-700'
                                    "
                                >
                                    {{ check.count }}
                                </span>
                            </button>

                            <div
                                v-if="openChecks.includes(check.key)"
                                class="mt-3 overflow-hidden rounded-md border border-slate-100"
                            >
                                <div
                                    v-if="check.rows.length === 0"
                                    class="bg-slate-50 px-4 py-3 text-sm font-bold text-slate-500"
                                >
                                    لا توجد سجلات مخالفة في هذا الفحص.
                                </div>

                                <div
                                    v-else
                                    class="max-h-80 overflow-auto bg-slate-950 text-left text-xs text-slate-100"
                                    dir="ltr"
                                >
                                    <div
                                        v-for="(row, index) in check.rows"
                                        :key="index"
                                        class="border-b border-white/10 p-3"
                                    >
                                        <dl class="grid gap-2 sm:grid-cols-2">
                                            <div
                                                v-for="(value, field) in row"
                                                :key="field"
                                                class="min-w-0"
                                            >
                                                <dt
                                                    class="font-bold text-orange-200"
                                                >
                                                    {{ field }}
                                                </dt>
                                                <dd
                                                    class="mt-1 break-words whitespace-pre-wrap"
                                                >
                                                    {{ formatValue(value) }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
