<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BookOpen,
    ChevronRight,
    ClipboardCheck,
    LockKeyhole,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Course {
    id: number;
    code: string;
    name: string;
    units: number;
    is_eligible: boolean;
    prerequisite_message: string | null;
    missing_prerequisites: Array<{
        id: number;
        code: string;
        name: string;
    }>;
}

interface StudyGroup {
    id: number;
    group_name: string;
    semester_level: number;
    semester: { code: string };
    courses: Course[];
}

interface CarriedCourse {
    id: number;
    course_id: number;
    name: string;
    code: string;
    units: number;
}

const props = defineProps<{
    student: {
        id: number;
        full_name: string;
        registration_number: string;
        current_specialization: { name: string };
        current_study_group?: {
            id: number;
            group_name: string;
            semester_level: number;
            semester_code?: string | null;
        } | null;
        status: string;
        cgpa: number;
        has_warning: boolean;
    };
    studyGroups: StudyGroup[];
    carriedEnrollments: CarriedCourse[];
    currentSemester: {
        code?: string | null;
    } | null;
    assignedStudyGroupId?: number | null;
    workflowMessage?: string;
}>();

const form = useForm({
    study_group_id: String(props.assignedStudyGroupId ?? ''),
    selected_course_ids: [] as number[],
    selected_carried_ids: [] as number[],
});

const selectedGroup = ref<StudyGroup | null>(null);
const assignedGroup = computed(
    () =>
        props.studyGroups.find(
            (group) => group.id === props.assignedStudyGroupId,
        ) ?? null,
);
const isGroupLocked = computed(() => !!props.assignedStudyGroupId);

watch(
    () => form.study_group_id,
    (newId) => {
        const group = props.studyGroups.find((g) => g.id === Number(newId));
        selectedGroup.value = group || null;

        if (group) {
            form.selected_course_ids = group.courses
                .filter((course) => course.is_eligible)
                .map((course) => course.id);
        } else {
            form.selected_course_ids = [];
        }
    },
    { immediate: true },
);

const limits = computed(() => {
    const min = 12;
    let max = 18;

    if (props.student.has_warning) {
        max = 12;
    } else if (props.student.cgpa >= 75.0) {
        max = 21;
    }

    if (form.selected_carried_ids.length > 0) {
        max = 24;
    }

    return { min, max };
});

const totalUnitsComputed = computed(() => {
    let sum = 0;

    if (selectedGroup.value) {
        selectedGroup.value.courses.forEach((course) => {
            if (form.selected_course_ids.includes(course.id)) {
                sum += course.units;
            }
        });
    }

    props.carriedEnrollments.forEach((course) => {
        if (form.selected_carried_ids.includes(course.course_id)) {
            sum += course.units;
        }
    });

    return sum;
});

const isSelectionValid = computed(() => {
    const units = totalUnitsComputed.value;

    if (units === 0) {
        return false;
    }

    const isGraduating =
        selectedGroup.value && selectedGroup.value.semester_level === 6;

    if (!isGraduating && units < limits.value.min) {
        return false;
    }

    return units <= limits.value.max;
});

const submit = () => {
    form.post(`/students/${props.student.id}/enroll`, {
        preserveScroll: true,
    });
};

const prerequisiteMessage = (course: Course) => {
    return (
        course.prerequisite_message ??
        'لا يمكن تسجيل هذا المقرر حتى النجاح في المتطلب السابق.'
    );
};
</script>

<template>
    <Head title="تنزيل المقررات وتنسيب الطالب" />

    <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-4xl space-y-6">
            <Link
                :href="`/students/${student.id}`"
                class="inline-flex items-center gap-2 text-sm font-bold text-blue-800 transition hover:text-orange-500"
            >
                <ChevronRight class="h-4 w-4" />
                العودة إلى ملف الطالب الأكاديمي
            </Link>

            <section
                class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <h1 class="text-2xl font-extrabold text-blue-800">
                    تنزيل المقررات وتجديد القيد للفصل الدراسي
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    تم تحويل الصفحة إلى مسار عمل أوضح: ربط بالمجموعة أولاً، ثم
                    تنزيل المواد الخاصة بها.
                </p>
            </section>

            <section class="rounded-lg border border-blue-100 bg-blue-50 p-5">
                <div class="flex items-start gap-3">
                    <Sparkles class="mt-0.5 h-5 w-5 text-blue-700" />
                    <div>
                        <p class="font-bold text-blue-950">
                            {{ workflowMessage }}
                        </p>
                        <p
                            v-if="assignedGroup"
                            class="mt-1 text-sm text-blue-900"
                        >
                            المجموعة المعتمدة: {{ assignedGroup.group_name }} -
                            الفصل {{ assignedGroup.semester.code }} - المستوى
                            {{ assignedGroup.semester_level }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-lg bg-white p-6 shadow-md">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 p-4">
                        <p class="text-xs font-medium text-gray-500">
                            اسم الطالب
                        </p>
                        <p class="mt-1 font-bold text-blue-800">
                            {{ student.full_name }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-4">
                        <p class="text-xs font-medium text-gray-500">
                            المعدل التراكمي الحالي
                        </p>
                        <p class="mt-1 font-mono font-bold text-blue-800">
                            {{ student.cgpa }}%
                        </p>
                    </div>
                    <div
                        class="rounded-lg p-4"
                        :class="
                            student.has_warning ? 'bg-red-50' : 'bg-green-50'
                        "
                    >
                        <p class="text-xs font-medium text-gray-500">
                            الإنذارات الأكاديمية
                        </p>
                        <p
                            class="mt-1 font-bold"
                            :class="
                                student.has_warning
                                    ? 'text-red-700'
                                    : 'text-green-700'
                            "
                        >
                            {{
                                student.has_warning
                                    ? 'يوجد إنذار نشط (محدود بـ 12 وحدة)'
                                    : 'لا يوجد إنذارات'
                            }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-4">
                        <p class="text-xs font-medium text-gray-500">
                            التخصص الحالي
                        </p>
                        <p class="mt-1 font-bold text-blue-800">
                            {{ student.current_specialization?.name }}
                        </p>
                    </div>
                </div>
            </section>

            <form @submit.prevent="submit" class="space-y-6">
                <section class="rounded-lg bg-white p-6 shadow-md">
                    <h2
                        class="mb-4 flex items-center gap-2 text-xl font-bold text-blue-800"
                    >
                        1. المجموعة الدراسية المستهدفة
                    </h2>
                    <div class="max-w-md">
                        <label
                            for="group"
                            class="block text-sm font-semibold text-gray-700"
                        >
                            الشعبة / الدفعة *
                        </label>
                        <div class="relative mt-1">
                            <select
                                id="group"
                                v-model="form.study_group_id"
                                required
                                :disabled="isGroupLocked"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-start text-gray-900 shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 disabled:cursor-not-allowed disabled:bg-gray-100"
                            >
                                <option value="" disabled>
                                    اختر الدفعة لتنزيل موادها...
                                </option>
                                <option
                                    v-for="group in studyGroups"
                                    :key="group.id"
                                    :value="group.id"
                                >
                                    {{ group.semester.code }} -
                                    {{ group.group_name }} (السمستر
                                    {{ group.semester_level }})
                                </option>
                            </select>
                            <LockKeyhole
                                v-if="isGroupLocked"
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                            />
                        </div>
                        <p
                            v-if="isGroupLocked"
                            class="mt-2 text-xs font-bold text-gray-500"
                        >
                            تم قفل اختيار المجموعة لأن الطالب أُسند إليها
                            تلقائياً عند التفعيل.
                        </p>
                    </div>
                </section>

                <section
                    v-if="selectedGroup"
                    class="space-y-6 rounded-lg bg-white p-6 shadow-md"
                >
                    <h2
                        class="mb-4 flex items-center gap-2 text-xl font-bold text-blue-800"
                    >
                        2. تحديد المقررات وتنزيل المواد
                    </h2>

                    <div>
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-blue-800"
                        >
                            <Users class="h-5 w-5" />
                            مقررات المجموعة {{ selectedGroup.group_name }} -
                            المستوى {{ selectedGroup.semester_level }}
                        </h3>
                        <div
                            class="mt-3 divide-y divide-gray-100 border-t border-b"
                        >
                            <div
                                v-for="course in selectedGroup.courses"
                                :key="course.id"
                                class="flex items-start justify-between gap-4 py-3"
                                :class="!course.is_eligible ? 'opacity-70' : ''"
                            >
                                <label
                                    class="flex items-start gap-3 text-sm font-semibold"
                                    :class="
                                        course.is_eligible
                                            ? 'cursor-pointer'
                                            : 'cursor-not-allowed'
                                    "
                                >
                                    <input
                                        v-model="form.selected_course_ids"
                                        type="checkbox"
                                        :value="course.id"
                                        :disabled="!course.is_eligible"
                                        :title="
                                            !course.is_eligible
                                                ? prerequisiteMessage(course)
                                                : undefined
                                        "
                                        class="h-4 w-4 rounded border-gray-300 text-blue-800 focus:ring-blue-800"
                                    />
                                    <span class="space-y-1">
                                        <span>
                                            {{ course.name }}
                                            <span
                                                class="font-mono text-xs text-gray-500"
                                            >
                                                ({{ course.code }})
                                            </span>
                                        </span>
                                        <span
                                            v-if="!course.is_eligible"
                                            class="block rounded-md bg-red-50 px-2 py-1 text-xs font-bold text-red-700"
                                        >
                                            {{ prerequisiteMessage(course) }}
                                        </span>
                                    </span>
                                </label>
                                <span
                                    class="rounded bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700"
                                >
                                    {{ course.units }} وحدات
                                </span>
                            </div>
                        </div>
                        <p
                            v-if="form.errors.selected_course_ids"
                            class="mt-3 rounded-lg bg-red-50 p-3 text-sm font-bold text-red-700"
                        >
                            {{ form.errors.selected_course_ids }}
                        </p>
                    </div>

                    <div v-if="carriedEnrollments.length > 0">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-orange-600"
                        >
                            <AlertTriangle class="h-5 w-5" />
                            المقررات المحمولة المتاحة للإعادة
                        </h3>
                        <div
                            class="mt-3 divide-y divide-gray-100 border-t border-b bg-orange-50/30"
                        >
                            <div
                                v-for="carried in carriedEnrollments"
                                :key="carried.id"
                                class="flex items-center justify-between px-3 py-3"
                            >
                                <label
                                    class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-orange-900"
                                >
                                    <input
                                        v-model="form.selected_carried_ids"
                                        type="checkbox"
                                        :value="carried.course_id"
                                        class="h-4 w-4 rounded border-orange-400 text-orange-600 focus:ring-orange-500"
                                    />
                                    <span>
                                        {{ carried.name }}
                                        <span
                                            class="font-mono text-xs text-orange-600"
                                        >
                                            ({{ carried.code }})
                                        </span>
                                    </span>
                                </label>
                                <span
                                    class="rounded bg-orange-100 px-2 py-1 text-xs font-bold text-orange-800"
                                >
                                    {{ carried.units }} وحدات (محمّلة)
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border-2 p-5"
                        :class="
                            isSelectionValid
                                ? 'border-blue-600 bg-blue-50/40'
                                : 'border-red-600 bg-red-50'
                        "
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <span
                                    class="block text-lg font-bold text-gray-900"
                                >
                                    إجمالي الوحدات المحددة للتنزيل:
                                </span>
                                <span
                                    class="mt-1 block text-xs font-semibold text-gray-500"
                                >
                                    الحد المسموح به: من {{ limits.min }} إلى
                                    {{ limits.max }} وحدة
                                </span>
                            </div>
                            <span
                                class="text-3xl font-extrabold"
                                :class="
                                    isSelectionValid
                                        ? 'text-blue-800'
                                        : 'text-red-700'
                                "
                            >
                                {{ totalUnitsComputed }} / {{ limits.max }} وحدة
                            </span>
                        </div>

                        <p
                            v-if="totalUnitsComputed > limits.max"
                            class="mt-3 flex items-center gap-1.5 text-sm font-bold text-red-700"
                        >
                            <AlertTriangle class="h-4 w-4" />
                            مجموع الوحدات يتجاوز الحد الأقصى المسموح به.
                        </p>
                        <p
                            v-if="
                                totalUnitsComputed < limits.min &&
                                selectedGroup.semester_level !== 6
                            "
                            class="mt-3 flex items-center gap-1.5 text-sm font-bold text-red-700"
                        >
                            <AlertTriangle class="h-4 w-4" />
                            لا يجوز التسجيل في أقل من الحد الأدنى إلا لطلاب فصل
                            التخرج.
                        </p>
                    </div>
                </section>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="
                            form.processing ||
                            !isSelectionValid ||
                            !form.study_group_id
                        "
                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-8 py-3 text-lg font-bold text-white shadow-md transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <ClipboardCheck class="h-5 w-5" />
                        <span>
                            {{
                                form.processing
                                    ? 'جاري الحفظ...'
                                    : 'تثبيت وتنزيل المقررات المحددة'
                            }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>
