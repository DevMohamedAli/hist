<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({ layout: [] });

defineProps<{
    submissions: {
        data: Array<{
            id: number;
            name: string;
            email?: string;
            phone?: string;
            subject?: string;
            message: string;
            status: string;
            created_at: string;
        }>;
    };
}>();
</script>

<template>
    <Head title="رسائل التواصل" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <div class="mx-auto max-w-6xl space-y-6">
                <section class="rounded-xl bg-white p-6 shadow">
                    <h1 class="text-2xl font-extrabold text-blue-950">
                        رسائل التواصل
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        جميع الرسائل المرسلة من نموذج التواصل في الموقع.
                    </p>
                </section>

                <section class="grid gap-4">
                    <article
                        v-for="submission in submissions.data"
                        :key="submission.id"
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="font-extrabold text-blue-950">
                                    {{ submission.subject || 'بدون موضوع' }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ submission.name }} —
                                    {{ submission.email || submission.phone || 'لا توجد بيانات اتصال' }}
                                </p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                {{ submission.status }}
                            </span>
                        </div>
                        <p class="mt-4 leading-7 text-slate-700">
                            {{ submission.message }}
                        </p>
                    </article>
                    <p
                        v-if="!submissions.data.length"
                        class="rounded-xl bg-white p-6 text-center text-slate-500 shadow-sm"
                    >
                        لا توجد رسائل تواصل حالياً.
                    </p>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
