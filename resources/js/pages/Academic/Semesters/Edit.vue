<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronRight, Save } from 'lucide-vue-next';

const props = defineProps<{
    semester: Record<string, string | number | null> & { id: number };
}>();

const form = useForm({
    code: String(props.semester.code ?? ''),
    season: String(props.semester.season ?? 'Fall'),
    year: Number(props.semester.year ?? new Date().getFullYear()),
    start_date: String(props.semester.start_date ?? ''),
    end_date: String(props.semester.end_date ?? ''),
    registration_start: String(props.semester.registration_start ?? ''),
    final_exams_start: String(props.semester.final_exams_start ?? ''),
});

const submit = () => form.patch(`/academic/semesters/${props.semester.id}`);
</script>

<template>
    <Head title="تعديل فصل دراسي" />
    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <section
            class="mx-auto max-w-4xl rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
        >
            <Link
                href="/academic/semesters"
                class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-500"
            >
                <ChevronRight class="h-4 w-4" />
                العودة للفصول
            </Link>
            <h1 class="text-2xl font-extrabold text-blue-800">
                تعديل فصل دراسي
            </h1>
            <form
                class="mt-6 grid gap-5 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <input
                    v-model="form.code"
                    required
                    type="text"
                    class="rounded-lg border border-gray-300 px-3 py-2"
                />
                <select
                    v-model="form.season"
                    required
                    class="rounded-lg border border-gray-300 px-3 py-2"
                >
                    <option value="Fall">الخريف</option>
                    <option value="Spring">الربيع</option>
                </select>
                <input
                    v-model.number="form.year"
                    required
                    type="number"
                    class="rounded-lg border border-gray-300 px-3 py-2"
                />
                <input
                    v-model="form.start_date"
                    required
                    type="date"
                    class="rounded-lg border border-gray-300 px-3 py-2"
                />
                <input
                    v-model="form.end_date"
                    type="date"
                    class="rounded-lg border border-gray-300 px-3 py-2"
                />
                <input
                    v-model="form.registration_start"
                    type="date"
                    class="rounded-lg border border-gray-300 px-3 py-2"
                />
                <input
                    v-model="form.final_exams_start"
                    type="date"
                    class="rounded-lg border border-gray-300 px-3 py-2 md:col-span-2"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-6 py-3 font-bold text-white hover:bg-orange-600 md:col-span-2"
                >
                    <Save class="h-5 w-5" />
                    حفظ التعديلات
                </button>
            </form>
        </section>
    </main>
</template>
