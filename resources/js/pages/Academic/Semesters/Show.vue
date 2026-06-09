<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarRange, ChevronRight } from 'lucide-vue-next';
import { formatDisplayDate } from '@/lib/date';

defineProps<{
    semester: Record<string, string | number | null> & {
        id: number;
        code: string;
    };
}>();

const dateFields = new Set(['start_date', 'end_date', 'registration_start', 'final_exams_start'])

const formatSemesterValue = (key: string, value: string | number | null | undefined) => {
    if (dateFields.has(key)) {
        return formatDisplayDate(value, '-')
    }

    return value || '-'
}
</script>

<template>
    <Head :title="semester.code" />
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
                ><ChevronRight class="h-4 w-4" /> العودة للفصول</Link
            >
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-orange-500">فصل دراسي</p>
                    <h1 class="text-2xl font-extrabold text-blue-800">
                        {{ semester.code }}
                    </h1>
                </div>
                <Link
                    :href="`/academic/semesters/${semester.id}/edit`"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600"
                    >تعديل</Link
                >
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="key in [
                        'season',
                        'year',
                        'start_date',
                        'end_date',
                        'registration_start',
                        'final_exams_start',
                    ]"
                    :key="key"
                    class="rounded-xl bg-blue-50 p-5"
                >
                    <CalendarRange class="mb-2 h-5 w-5 text-blue-800" />
                    <p class="text-sm text-gray-500">{{ key }}</p>
                    <p class="font-bold text-blue-800">
                        {{ formatSemesterValue(key, semester[key]) }}
                    </p>
                </div>
            </div>
        </section>
    </main>
</template>
