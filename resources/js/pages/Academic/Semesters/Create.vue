<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronRight, Save } from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface CreationOption {
    season: 'Spring' | 'Fall';
    season_label: string;
    year: number;
    code: string;
}

const props = defineProps<{
    creationOptions: {
        availableCombinations: CreationOption[];
        defaultCombination: CreationOption | null;
    };
}>();

const defaultCombination = props.creationOptions.defaultCombination;

const form = useForm({
    code: defaultCombination?.code ?? '',
    season: (defaultCombination?.season ?? 'Fall') as 'Spring' | 'Fall',
    year: defaultCombination?.year ?? new Date().getFullYear(),
    start_date: '',
    end_date: '',
    registration_start: '',
    registration_end: '',
    final_exams_start: '',
});

const addDays = (dateStr: string, days: number): string => {
    if (!dateStr) {
        return '';
    }

    const date = new Date(dateStr);
    date.setDate(date.getDate() + days);

    return date.toISOString().slice(0, 10);
};

const selectedCombinationKey = computed({
    get: () => `${form.season}-${form.year}`,
    set: (value: string) => {
        const selected = props.creationOptions.availableCombinations.find(
            (item) => `${item.season}-${item.year}` === value,
        );

        if (!selected) {
            return;
        }

        form.season = selected.season;
        form.year = selected.year;
        form.code = selected.code;
    },
});

watch(
    () => [form.season, form.year] as const,
    ([season, year]) => {
        form.code = `${season}-${year}`;
    },
    { immediate: true },
);

watch(
    () => form.start_date,
    (newStartDate) => {
        if (!newStartDate) {
            form.registration_start = '';
            form.registration_end = '';
            form.final_exams_start = '';
            form.end_date = '';
            return;
        }

        form.registration_start = newStartDate;
        form.registration_end = addDays(newStartDate, 13);
        form.final_exams_start = addDays(newStartDate, 126);
        form.end_date = addDays(newStartDate, 139);
    },
);

const submit = () => form.post('/academic/semesters');
</script>

<template>
    <Head title="إضافة فصل دراسي" />
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
                إضافة فصل دراسي
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                تظهر هنا فقط الفصول الرسمية غير الموجودة فعلاً في قاعدة
                البيانات.
            </p>

            <div
                v-if="props.creationOptions.availableCombinations.length === 0"
                class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-900"
            >
                لا توجد حالياً تركيبات جديدة متاحة للربيع أو الخريف ضمن نطاق
                السنوات المقترح.
            </div>

            <form
                v-else
                class="mt-6 grid gap-5 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div class="md:col-span-2">
                    <label
                        for="combination"
                        class="block text-sm font-semibold text-gray-700"
                    >
                        الفصل الرسمي المتاح
                    </label>
                    <select
                        id="combination"
                        v-model="selectedCombinationKey"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2"
                        required
                    >
                        <option
                            v-for="option in props.creationOptions
                                .availableCombinations"
                            :key="`${option.season}-${option.year}`"
                            :value="`${option.season}-${option.year}`"
                        >
                            {{ option.season_label }} - {{ option.year }}
                        </option>
                    </select>
                </div>
                <input
                    v-model="form.code"
                    required
                    type="text"
                    readonly
                    class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600"
                />
                <input
                    v-model.number="form.year"
                    required
                    type="number"
                    readonly
                    class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600"
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
                    readonly
                    class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600"
                />
                <input
                    v-model="form.registration_start"
                    type="date"
                    readonly
                    class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600"
                />
                <input
                    v-model="form.registration_end"
                    type="date"
                    readonly
                    class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600"
                />
                <input
                    v-model="form.final_exams_start"
                    readonly
                    type="date"
                    class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600 md:col-span-2"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-6 py-3 font-bold text-white hover:bg-orange-600 md:col-span-2"
                >
                    <Save class="h-5 w-5" />
                    حفظ الفصل
                </button>
            </form>
        </section>
    </main>
</template>
