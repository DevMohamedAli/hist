<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: [] });

type Faq = {
    id: number;
    question: string;
    answer: string;
    is_published: boolean;
    sort_order: number;
};

defineProps<{
    faqs: {
        data: Array<Faq>;
    };
}>();

const editingId = ref<number | null>(null);

const form = useForm({
    question: '',
    answer: '',
    is_published: true,
    sort_order: 0,
});

const resetForm = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const editFaq = (faq: Faq) => {
    editingId.value = faq.id;
    form.question = faq.question;
    form.answer = faq.answer;
    form.is_published = faq.is_published;
    form.sort_order = faq.sort_order;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/admin/website/faqs/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: resetForm,
        });

        return;
    }

    form.post('/admin/website/faqs', {
        preserveScroll: true,
        onSuccess: resetForm,
    });
};
</script>

<template>
    <Head title="إدارة الأسئلة الشائعة" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <div class="mx-auto max-w-6xl space-y-6">
                <section class="rounded-xl bg-white p-6 shadow">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h1 class="text-2xl font-extrabold text-blue-950">
                            الأسئلة الشائعة
                        </h1>
                        <button
                            v-if="editingId"
                            type="button"
                            class="rounded-md border px-4 py-2 text-sm font-bold"
                            @click="resetForm"
                        >
                            إلغاء التعديل
                        </button>
                    </div>
                    <form class="mt-5 grid gap-4" @submit.prevent="submit">
                        <input
                            v-model="form.question"
                            required
                            placeholder="السؤال"
                            class="rounded-md border px-3 py-2"
                        />
                        <textarea
                            v-model="form.answer"
                            required
                            placeholder="الإجابة"
                            class="min-h-32 rounded-md border px-3 py-2"
                        />
                        <div class="grid gap-4 md:grid-cols-2">
                            <input
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                placeholder="ترتيب العرض"
                                class="rounded-md border px-3 py-2"
                            />
                            <label class="flex items-center gap-2 rounded-md border px-3 py-2">
                                <input v-model="form.is_published" type="checkbox" />
                                منشور
                            </label>
                        </div>
                        <button
                            class="rounded-md bg-blue-900 px-5 py-2.5 font-bold text-white"
                            :disabled="form.processing"
                        >
                            {{ editingId ? 'حفظ التعديل' : 'حفظ السؤال' }}
                        </button>
                    </form>
                </section>

                <section class="rounded-xl bg-white p-6 shadow">
                    <table class="w-full text-start text-sm">
                        <thead>
                            <tr>
                                <th class="py-2">السؤال</th>
                                <th class="py-2">منشور</th>
                                <th class="py-2">الترتيب</th>
                                <th class="py-2">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="faq in faqs.data" :key="faq.id" class="border-t">
                                <td class="py-2">{{ faq.question }}</td>
                                <td class="py-2">{{ faq.is_published ? 'نعم' : 'لا' }}</td>
                                <td class="py-2">{{ faq.sort_order }}</td>
                                <td class="py-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                        @click="editFaq(faq)"
                                    >
                                        تعديل
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
