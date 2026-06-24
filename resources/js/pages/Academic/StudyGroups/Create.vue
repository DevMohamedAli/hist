<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronRight, Save } from 'lucide-vue-next';

interface Department {
    id: number;
    name: string;
}

interface Specialization {
    id: number;
    name: string;
    department?: Department | null;
}

interface AcademicSemester {
    id: number;
    code: string;
}

interface CreationAvailability {
    is_open: boolean;
    message: string;
    days_remaining?: number | null;
    semester?: {
        id: number;
        code: string;
        registration_start: string | null;
        registration_end: string | null;
    } | null;
}

const props = defineProps<{
    specializations: Specialization[];
    semesters: AcademicSemester[];
    creationAvailability: CreationAvailability;
}>();

const activeSemesterId = props.creationAvailability.semester?.id
    ? String(props.creationAvailability.semester.id)
    : '';

const form = useForm({
    specialization_id: '',
    academic_semester_id: activeSemesterId,
    semester_level: 1,
    group_name: '',
    capacity: 50,
});

const submit = () => {
    form.post('/academic/study-groups', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="إضافة مجموعة تدريسية" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <Link
                href="/academic/study-groups"
                class="inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-500"
            >
                <ChevronRight class="h-4 w-4" /> العودة إلى المجموعات
            </Link>

            <section
                class="rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <h1 class="text-2xl font-extrabold text-blue-800">
                    إضافة مجموعة تدريسية جديدة
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    إنشاء مجموعة جديدة متاح فقط للفصل النشط الذي ما يزال ضمن
                    نافذة التسجيل وباقٍ عليه 3 أيام على الأقل قبل الإغلاق.
                </p>

                <div
                    class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900"
                >
                    <p class="font-bold">
                        {{ props.creationAvailability.message }}
                    </p>
                    <p v-if="props.creationAvailability.semester" class="mt-1">
                        الفصل المحدد:
                        {{ props.creationAvailability.semester.code }} | التسجيل
                        من
                        {{
                            props.creationAvailability.semester
                                .registration_start ?? '-'
                        }}
                        إلى
                        {{
                            props.creationAvailability.semester
                                .registration_end ?? '-'
                        }}
                    </p>
                </div>

                <form
                    class="mt-6 space-y-6 text-gray-900"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >التخصص *</label
                            >
                            <select
                                v-model="form.specialization_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            >
                                <option value="" disabled>اختر التخصص</option>
                                <option
                                    v-for="spec in props.specializations"
                                    :key="spec.id"
                                    :value="spec.id"
                                >
                                    {{ spec.name }}
                                    <template v-if="spec.department?.name">
                                        ({{ spec.department.name }})
                                    </template>
                                </option>
                            </select>
                            <p
                                v-if="form.errors.specialization_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.specialization_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >الفصل الدراسي *</label
                            >
                            <select
                                v-model="form.academic_semester_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-start text-gray-600 shadow-sm focus:outline-none"
                            >
                                <option
                                    v-for="sem in props.semesters"
                                    :key="sem.id"
                                    :value="String(sem.id)"
                                >
                                    {{ sem.code }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                تم تقييد الاختيار على الفصل النشط المسموح به
                                فقط.
                            </p>
                            <p
                                v-if="form.errors.academic_semester_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.academic_semester_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >المستوى (الدفعة) *</label
                            >
                            <input
                                v-model.number="form.semester_level"
                                type="number"
                                min="1"
                                max="12"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            />
                            <p
                                v-if="form.errors.semester_level"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.semester_level }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >اسم المجموعة (مثال: أ، ب) *</label
                            >
                            <input
                                v-model="form.group_name"
                                type="text"
                                maxlength="50"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            />
                            <p
                                v-if="form.errors.group_name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.group_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >السعة الاستيعابية</label
                            >
                            <input
                                v-model.number="form.capacity"
                                type="number"
                                min="1"
                                max="200"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            />
                            <p
                                v-if="form.errors.capacity"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.capacity }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 px-6 py-3 font-bold text-white transition hover:bg-orange-600 disabled:opacity-60"
                    >
                        <Save class="h-5 w-5" />
                        {{ form.processing ? 'جاري الحفظ...' : 'حفظ المجموعة' }}
                    </button>
                </form>
            </section>
        </div>
    </main>
</template>
