<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronRight, Save } from 'lucide-vue-next';

const props = defineProps<{
    department: {
        id: number;
        name: string;
        code?: string | null;
        description?: string | null;
    };
}>();
const form = useForm({
    name: props.department.name,
    code: props.department.code ?? '',
    description: props.department.description ?? '',
});
const submit = () => form.patch(`/academic/departments/${props.department.id}`);
</script>

<template>
    <Head title="تعديل قسم علمي" />
    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <section
            class="mx-auto max-w-3xl rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
        >
            <Link
                href="/academic/departments"
                class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-500"
                ><ChevronRight class="h-4 w-4" /> العودة للأقسام</Link
            >
            <h1 class="text-2xl font-extrabold text-blue-800">
                تعديل قسم علمي
            </h1>
            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <input
                    v-model="form.name"
                    required
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                />
                <input
                    v-model="form.code"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                />
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 px-6 py-3 font-bold text-white hover:bg-orange-600 disabled:opacity-60"
                >
                    <Save class="h-5 w-5" />حفظ التعديلات
                </button>
            </form>
        </section>
    </main>
</template>
