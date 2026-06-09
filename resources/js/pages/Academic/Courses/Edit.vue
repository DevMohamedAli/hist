<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronRight,
    Save,
    PlusCircle,
    Trash2,
    AlertCircle,
    XCircle,
    BookOpen,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// ── Types ──────────────────────────────────────────────
interface Specialization {
    id: number;
    name: string;
}

interface CoursePivot {
    id: number;
    semester_level: number;
}

interface Course {
    id: number;
    code: string;
    name: string;
    units: number;
    has_practical: boolean;
    prerequisite_ids: number[]; // from backend (many-to-many)
    prerequisites?: { id: number; code: string; name: string }[];
    description: string | null;
    specializations: (Specialization & { pivot: CoursePivot })[];
}

interface Option {
    id: number;
    name: string;
    code?: string;
}

const props = defineProps<{
    course: Course;
    specializations: Specialization[];
    prerequisites: Option[];
}>();

// Build initial specializations from course (if any)
const initialSpecs = props.course.specializations.map((s) => ({
    id: String(s.id),
    semester_level: s.pivot?.semester_level ?? 1,
}));

// Form uses prerequisite_ids array (many-to-many)
const form = useForm({
    code: props.course.code,
    name: props.course.name,
    units: props.course.units,
    has_practical: Boolean(props.course.has_practical),
    prerequisite_ids: props.course.prerequisite_ids ?? [],
    description: props.course.description ?? '',
    specializations: initialSpecs.length
        ? initialSpecs
        : [{ id: '', semester_level: 1 }],
});

const showErrors = ref(false);

const addSpecialization = () => {
    form.specializations.push({ id: '', semester_level: 1 });
};

const removeSpecialization = (index: number) => {
    if (form.specializations.length > 1) {
        form.specializations.splice(index, 1);
    }
};

const isSpecializationsValid = computed(() => {
    return form.specializations.some((spec) => spec.id !== '');
});

const submit = () => {
    showErrors.value = true;

    if (!isSpecializationsValid.value) {
        return;
    }

    form.patch(`/academic/courses/${props.course.id}`, {
        preserveScroll: true,
        onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    });
};
</script>

<template>
    <Head title="تعديل مقرر دراسي" />

    <main
        class="min-h-screen bg-linear-to-br from-gray-50 to-gray-100 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Back link -->
            <Link
                href="/academic/courses"
                class="inline-flex items-center gap-2 text-sm font-bold text-blue-800 transition hover:text-orange-500"
            >
                <ChevronRight class="h-4 w-4" />
                العودة إلى المقررات
            </Link>

            <!-- Edit form card -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
            >
                <!-- Header with gradient -->
                <div class="bg-linear-to-l from-blue-800 to-blue-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <BookOpen class="h-7 w-7 text-orange-300" />
                        <div>
                            <h1 class="text-2xl font-extrabold text-white">
                                تعديل المقرر الدراسي
                            </h1>
                            <p class="mt-1 text-sm text-blue-200">
                                قم بتعديل بيانات المقرر وارتباطاته
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-6 md:p-8">
                    <!-- Global error alert -->
                    <div
                        v-if="Object.keys(form.errors).length > 0 && showErrors"
                        class="mb-6 rounded-xl border-r-4 border-red-500 bg-red-50 p-4"
                    >
                        <div class="flex items-center gap-2">
                            <AlertCircle class="h-5 w-5 text-red-600" />
                            <span class="font-bold text-red-800"
                                >يرجى تصحيح الأخطاء التالية:</span
                            >
                        </div>
                        <ul class="mt-2 list-disc pr-6 text-sm text-red-700">
                            <li v-for="(error, key) in form.errors" :key="key">
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <!-- Basic information -->
                    <fieldset
                        class="mb-8 rounded-xl border border-gray-200 bg-gray-50/40 p-5"
                    >
                        <legend
                            class="mx-3 px-2 text-base font-bold text-blue-800"
                        >
                            المعلومات الأساسية
                        </legend>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    >الكود
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.code"
                                    required
                                    type="text"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                    :class="{
                                        'border-red-500':
                                            form.errors.code && showErrors,
                                    }"
                                />
                                <p
                                    v-if="form.errors.code && showErrors"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    >اسم المقرر
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.name"
                                    required
                                    type="text"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                    :class="{
                                        'border-red-500':
                                            form.errors.name && showErrors,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name && showErrors"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    >الوحدات
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model.number="form.units"
                                    required
                                    type="number"
                                    min="1"
                                    max="4"
                                    step="1"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                    :class="{
                                        'border-red-500':
                                            form.errors.units && showErrors,
                                    }"
                                />
                                <p
                                    v-if="form.errors.units && showErrors"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.units }}
                                </p>
                            </div>
                            <div class="flex items-center">
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-xl bg-blue-50 p-3 font-semibold text-blue-800 transition hover:bg-blue-100"
                                >
                                    <input
                                        v-model="form.has_practical"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span>له جانب عملي (معمل)</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >الوصف</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="وصف مختصر للمقرر ومحتواه..."
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            ></textarea>
                        </div>
                    </fieldset>

                    <!-- Prerequisites (many-to-many) -->
                    <fieldset
                        class="mb-8 rounded-xl border border-gray-200 bg-gray-50/40 p-5"
                    >
                        <legend
                            class="mx-3 px-2 text-base font-bold text-blue-800"
                        >
                            المتطلبات السابقة
                        </legend>
                        <div class="space-y-3">
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >المقررات التي يجب اجتيازها أولاً</label
                            >
                            <select
                                v-model="form.prerequisite_ids"
                                multiple
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                size="5"
                            >
                                <option
                                    v-for="prereq in prerequisites"
                                    :key="prereq.id"
                                    :value="prereq.id"
                                >
                                    {{ prereq.code }} – {{ prereq.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500">
                                يمكن اختيار أكثر من مقرر (اضغط مع الاستمرار على
                                Ctrl أو ⌘)
                            </p>
                            <div
                                v-if="form.prerequisite_ids.length"
                                class="mt-2 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="id in form.prerequisite_ids"
                                    :key="id"
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800"
                                >
                                    {{
                                        prerequisites.find((p) => p.id === id)
                                            ?.code
                                    }}
                                    <button
                                        type="button"
                                        @click="
                                            form.prerequisite_ids =
                                                form.prerequisite_ids.filter(
                                                    (i) => i !== id,
                                                )
                                        "
                                        class="mr-1 text-amber-600 hover:text-amber-900"
                                    >
                                        ×
                                    </button>
                                </span>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Specializations (dynamic) -->
                    <fieldset
                        class="mb-8 rounded-xl border border-gray-200 bg-gray-50/40 p-5"
                    >
                        <legend
                            class="mx-3 px-2 text-base font-bold text-blue-800"
                        >
                            التخصصات المرتبطة ورقم الفصل
                            <span class="mr-2 text-sm font-normal text-red-500"
                                >*</span
                            >
                        </legend>
                        <div class="space-y-4">
                            <div
                                v-for="(spec, index) in form.specializations"
                                :key="index"
                                class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition sm:flex-row sm:items-end"
                            >
                                <div class="flex-1">
                                    <label
                                        class="block text-sm font-semibold text-gray-700"
                                        >التخصص</label
                                    >
                                    <select
                                        v-model="spec.id"
                                        required
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        :class="{
                                            'border-red-500':
                                                !spec.id && showErrors,
                                        }"
                                    >
                                        <option value="" disabled>
                                            اختر تخصصًا
                                        </option>
                                        <option
                                            v-for="opt in specializations"
                                            :key="opt.id"
                                            :value="String(opt.id)"
                                        >
                                            {{ opt.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-36">
                                    <label
                                        class="block text-sm font-semibold text-gray-700"
                                        >الفصل</label
                                    >
                                    <input
                                        v-model.number="spec.semester_level"
                                        type="number"
                                        min="1"
                                        max="12"
                                        required
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-center shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removeSpecialization(index)"
                                    :disabled="
                                        form.specializations.length === 1
                                    "
                                    class="mb-0.5 flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100 disabled:opacity-30"
                                    title="حذف التخصص"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="addSpecialization"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50/50 py-2.5 font-semibold text-blue-700 transition hover:bg-blue-100"
                            >
                                <PlusCircle class="h-4 w-4" />
                                إضافة تخصص آخر
                            </button>
                            <div
                                v-if="showErrors && !isSpecializationsValid"
                                class="mt-2 text-sm text-red-600"
                            >
                                يجب إضافة تخصص واحد على الأقل.
                            </div>
                        </div>
                    </fieldset>

                    <!-- Submit & reset -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="
                                () => {
                                    form.reset();
                                    showErrors = false;
                                }
                            "
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            <XCircle class="h-5 w-5" />
                            إعادة تعيين
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-8 py-2.5 font-bold text-white shadow-md transition hover:bg-orange-600 disabled:opacity-70"
                        >
                            <Save class="h-5 w-5" />
                            {{
                                form.processing
                                    ? 'جاري الحفظ...'
                                    : 'حفظ التعديلات'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>
