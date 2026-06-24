<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpenCheck,
    CheckCircle2,
    ClipboardList,
    GraduationCap,
    Lock,
    Search,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({ layout: AuthenticatedLayout });

interface Course {
    code?: string | null;
    name?: string | null;
    units?: number | null;
}

interface Semester {
    code?: string | null;
}

interface Instructor {
    name?: string | null;
}

interface StudyGroup {
    group_name?: string | null;
    specialization?: { name?: string | null } | null;
}

interface GradeClass {
    id: number;
    group_name?: string | null;
    course?: Course | null;
    semester?: Semester | null;
    instructor?: Instructor | null;
    study_group?: StudyGroup | null;
    student_count?: number;
    graded_count?: number;
    pending_count?: number;
}

const props = defineProps<{
    classes: GradeClass[];
    currentSemester?: {
        code?: string | null;
        season?: string | null;
        year?: number | null;
    } | null;
    gradeEntryAvailability?: {
        is_open: boolean;
        can_override: boolean;
        message: string;
    };
}>();

const search = ref('');

const filteredClasses = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.classes;
    }

    return props.classes.filter((item) => {
        return [
            item.course?.name,
            item.course?.code,
            item.group_name,
            item.study_group?.group_name,
            item.study_group?.specialization?.name,
            item.semester?.code,
            item.instructor?.name,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term));
    });
});

const totalStudents = computed(() =>
    props.classes.reduce(
        (total, item) => total + Number(item.student_count ?? 0),
        0,
    ),
);

const totalGraded = computed(() =>
    props.classes.reduce(
        (total, item) => total + Number(item.graded_count ?? 0),
        0,
    ),
);

const totalPending = computed(() =>
    props.classes.reduce(
        (total, item) => total + Number(item.pending_count ?? 0),
        0,
    ),
);

const completionPercent = (item: GradeClass) => {
    const total = Number(item.student_count ?? 0);

    if (total === 0) {
        return 0;
    }

    return Math.round((Number(item.graded_count ?? 0) / total) * 100);
};

const gradeWindowAccessible = computed(
    () =>
        !!props.gradeEntryAvailability?.is_open ||
        !!props.gradeEntryAvailability?.can_override,
);
</script>

<template>
    <Head title="رصد الدرجات" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-t-4 border-blue-800 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="rounded-md bg-blue-50 p-3 text-blue-800">
                                <ClipboardList class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-orange-600">
                                    وحدة الرصد
                                </p>
                                <h1 class="mt-1 text-2xl font-extrabold text-blue-900">
                                    رصد الدرجات
                                </h1>
                                <p class="mt-2 max-w-3xl text-sm leading-7 text-gray-600">
                                    تم ضبط الرصد ليتبع دورة الفصل: يفتح في فترة الامتحانات النهائية، ثم يغلق بعدها إلا بإذن من مدير النظام.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3">
                                <p class="text-xs font-bold text-blue-700">الشعب</p>
                                <p class="mt-1 text-xl font-extrabold text-blue-950">
                                    {{ classes.length }}
                                </p>
                            </div>
                            <div class="rounded-md border border-green-100 bg-green-50 px-4 py-3">
                                <p class="text-xs font-bold text-green-700">مرصود</p>
                                <p class="mt-1 text-xl font-extrabold text-green-950">
                                    {{ totalGraded }}
                                </p>
                            </div>
                            <div class="rounded-md border border-orange-100 bg-orange-50 px-4 py-3">
                                <p class="text-xs font-bold text-orange-700">قيد الرصد</p>
                                <p class="mt-1 text-xl font-extrabold text-orange-950">
                                    {{ totalPending }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="rounded-lg border p-4 shadow-sm"
                :class="
                    props.gradeEntryAvailability?.is_open
                        ? 'border-green-200 bg-green-50'
                        : 'border-amber-200 bg-amber-50'
                "
            >
                <div class="flex items-start gap-3">
                    <Lock class="mt-0.5 h-5 w-5 text-gray-700" />
                    <div>
                        <p class="font-bold text-gray-900">
                            {{ props.gradeEntryAvailability?.message }}
                        </p>
                        <p
                            v-if="props.gradeEntryAvailability?.can_override"
                            class="mt-1 text-sm text-gray-600"
                        >
                            لديك صلاحية تجاوز الإغلاق كمدير نظام عند الضرورة.
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <Users class="h-6 w-6 text-blue-800" />
                        <div>
                            <p class="text-xs font-bold text-gray-500">إجمالي الطلاب</p>
                            <p class="text-2xl font-extrabold text-gray-950">{{ totalStudents }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <CheckCircle2 class="h-6 w-6 text-green-700" />
                        <div>
                            <p class="text-xs font-bold text-gray-500">درجات محفوظة</p>
                            <p class="text-2xl font-extrabold text-gray-950">{{ totalGraded }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <BookOpenCheck class="h-6 w-6 text-orange-600" />
                        <div>
                            <p class="text-xs font-bold text-gray-500">بانتظار الإدخال</p>
                            <p class="text-2xl font-extrabold text-gray-950">{{ totalPending }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-950">
                            الشعب المتاحة
                        </h2>
                        <p class="text-sm text-gray-500">
                            النتائج: {{ filteredClasses.length }} شعبة
                            <span
                                v-if="props.currentSemester?.code"
                                class="mr-2 text-gray-400"
                            >
                                | الفصل الحالي: {{ props.currentSemester.code }}
                            </span>
                        </p>
                    </div>
                    <div class="relative w-full sm:max-w-sm">
                        <Search class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="بحث بالمقرر أو الشعبة أو الفصل..."
                            class="w-full rounded-md border border-gray-300 bg-white py-2 pr-9 pl-3 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="item in filteredClasses"
                        :key="item.id"
                        class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <GraduationCap class="h-5 w-5 shrink-0 text-orange-500" />
                                    <h3 class="truncate text-base font-extrabold text-blue-950">
                                        {{ item.course?.name ?? 'مقرر غير محدد' }}
                                    </h3>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ item.course?.code ?? '-' }} ·
                                    {{ item.semester?.code ?? '-' }}
                                </p>
                            </div>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">
                                {{ completionPercent(item) }}%
                            </span>
                        </div>

                        <div class="mt-4 grid gap-2 text-sm text-gray-600">
                            <div class="flex justify-between gap-3">
                                <span>الشعبة</span>
                                <strong class="text-gray-900">
                                    {{ item.study_group?.group_name ?? item.group_name ?? '-' }}
                                </strong>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span>التخصص</span>
                                <strong class="text-gray-900">
                                    {{ item.study_group?.specialization?.name ?? '-' }}
                                </strong>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span>المحاضر</span>
                                <strong class="text-gray-900">
                                    {{ item.instructor?.name ?? '-' }}
                                </strong>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span>الوحدات / الطلاب</span>
                                <strong class="text-gray-900">
                                    {{ item.course?.units ?? 0 }} /
                                    {{ item.student_count ?? 0 }}
                                </strong>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-blue-700"
                                    :style="{ width: `${completionPercent(item)}%` }"
                                />
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ item.graded_count ?? 0 }} محفوظ ·
                                {{ item.pending_count ?? 0 }} لم يرصد بعد
                            </p>
                        </div>

                        <Link
                            :href="`/grades/classes/${item.id}`"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-bold text-white"
                            :class="
                                gradeWindowAccessible
                                    ? 'bg-orange-500 hover:bg-orange-600'
                                    : 'bg-gray-400 hover:bg-gray-500'
                            "
                        >
                            {{
                                gradeWindowAccessible
                                    ? 'فتح كشف الرصد'
                                    : 'الرصد مغلق حالياً'
                            }}
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </article>

                    <div
                        v-if="filteredClasses.length === 0"
                        class="col-span-full rounded-lg border border-dashed border-gray-300 p-10 text-center text-gray-500"
                    >
                        لا توجد شعب مطابقة للبحث الحالي.
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
