<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronRight, Save } from 'lucide-vue-next';

interface Specialization {
    id: number;
    name: string;
}

interface AcademicSemester {
    id: number;
    code: string;
}

defineProps<{
    specializations: Specialization[];
    semesters: AcademicSemester[];
}>();

const form = useForm({
    specialization_id: '',
    academic_semester_id: '',
    semester_level: 1,
    group_name: '',
    capacity: 50,
});

const submit = () => {
    form.post('/academic/study-groups', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
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
                    تحديد المجموعة حسب التخصص والفصل الدراسي والمستوى.
                </p>

                <form
                    class="mt-6 space-y-6 text-gray-900"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <!-- Specialization -->
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
                                    v-for="spec in specializations"
                                    :key="spec.id"
                                    :value="spec.id"
                                >
                                    {{ spec.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.specialization_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.specialization_id }}
                            </p>
                        </div>

                        <!-- Academic Semester -->
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >الفصل الدراسي *</label
                            >
                            <select
                                v-model="form.academic_semester_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            >
                                <option value="" disabled>اختر الفصل</option>
                                <option
                                    v-for="sem in semesters"
                                    :key="sem.id"
                                    :value="sem.id"
                                >
                                    {{ sem.code }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.academic_semester_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.academic_semester_id }}
                            </p>
                        </div>

                        <!-- Semester Level -->
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

                        <!-- Group Name -->
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

                        <!-- Capacity -->
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700"
                                >السعة الاستيعابية</label
                            >
                            <input
                                v-model.number="form.capacity"
                                type="number"
                                min="1"
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
