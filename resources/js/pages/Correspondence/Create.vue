<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({ layout: [] });

defineProps<{ categories: Array<{ id: number; name: string }> }>();

const form = useForm({
    category_id: '',
    type: 'official_letter',
    subject: '',
    body: '',
    priority: 'normal',
    classification: 'internal',
    recipients: [] as Array<{ user_id: number; recipient_type: string }>,
});

const submit = () => form.post('/correspondence');
</script>

<template>
    <Head title="Create Correspondence" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <form
                class="mx-auto grid max-w-3xl gap-4 rounded-lg bg-white p-6 shadow"
                @submit.prevent="submit"
            >
                <h1 class="text-2xl font-bold">مراسلة جديدة</h1>
                <select
                    v-model="form.category_id"
                    class="rounded-md border px-3 py-2"
                >
                    <option value="">بدون تصنيف</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
                <input
                    v-model="form.subject"
                    required
                    placeholder="الموضوع"
                    class="rounded-md border px-3 py-2"
                />
                <textarea
                    v-model="form.body"
                    required
                    placeholder="نص المراسلة"
                    class="min-h-48 rounded-md border px-3 py-2"
                />
                <div class="grid gap-4 md:grid-cols-3">
                    <select
                        v-model="form.type"
                        class="rounded-md border px-3 py-2"
                    >
                        <option value="official_letter">official_letter</option>
                        <option value="internal_memo">internal_memo</option>
                        <option value="student_request">student_request</option>
                    </select>
                    <select
                        v-model="form.priority"
                        class="rounded-md border px-3 py-2"
                    >
                        <option value="normal">normal</option>
                        <option value="important">important</option>
                        <option value="urgent">urgent</option>
                    </select>
                    <select
                        v-model="form.classification"
                        class="rounded-md border px-3 py-2"
                    >
                        <option value="internal">internal</option>
                        <option value="restricted">restricted</option>
                        <option value="confidential">confidential</option>
                    </select>
                </div>
                <p class="text-sm text-slate-600">
                    Recipients can be added through the workflow extension
                    points; this foundation keeps server-side checks in place.
                </p>
                <button
                    class="rounded-md bg-blue-900 px-5 py-2.5 font-bold text-white"
                    :disabled="form.processing"
                >
                    حفظ كمسودة
                </button>
            </form>
        </main>
    </AuthenticatedLayout>
</template>
