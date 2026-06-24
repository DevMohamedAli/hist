<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import CorrespondenceStatusBadge from '@/components/correspondence/CorrespondenceStatusBadge.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({ layout: [] });

const props = defineProps<{
    correspondence: {
        id: number;
        reference_number?: string;
        subject: string;
        body: string;
        status: string;
        priority: string;
        classification: string;
        sender?: { name: string };
        recipients: Array<{
            id: number;
            recipient_type: string;
            user?: { name: string };
        }>;
        attachments: Array<{
            id: number;
            original_filename: string;
            file_size: number;
        }>;
        replies: Array<{ id: number; body: string; sender?: { name: string } }>;
        status_histories?: Array<{
            id: number;
            to_status: string;
            created_at: string;
        }>;
    };
}>();

const replyForm = useForm({ body: '' });
const attachmentForm = useForm({
    file: null as File | null,
    description: '',
});
const postAction = (action: string) =>
    router.post(`/correspondence/${props.correspondence.id}/${action}`);
const reply = () =>
    replyForm.post(`/correspondence/${props.correspondence.id}/reply`, {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
const uploadAttachment = () =>
    attachmentForm.post(
        `/correspondence/${props.correspondence.id}/attachments`,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => attachmentForm.reset(),
        },
    );
</script>

<template>
    <Head :title="correspondence.subject" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <article
                class="mx-auto max-w-5xl space-y-6 rounded-lg bg-white p-6 shadow"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">
                            {{ correspondence.reference_number || 'Draft' }}
                        </p>
                        <h1 class="text-2xl font-bold">
                            {{ correspondence.subject }}
                        </h1>
                    </div>
                    <CorrespondenceStatusBadge
                        :status="correspondence.status"
                    />
                </div>
                <div class="grid gap-3 text-sm md:grid-cols-3">
                    <p>المرسل: {{ correspondence.sender?.name || '-' }}</p>
                    <p>الأولوية: {{ correspondence.priority }}</p>
                    <p>التصنيف: {{ correspondence.classification }}</p>
                </div>
                <div
                    class="rounded-lg border bg-slate-50 p-4 whitespace-pre-wrap"
                >
                    {{ correspondence.body }}
                </div>
                <section>
                    <h2 class="font-bold">Attachments</h2>
                    <div
                        v-for="attachment in correspondence.attachments"
                        :key="attachment.id"
                        class="mt-3 flex items-center justify-between rounded-lg border p-3"
                    >
                        <span>{{ attachment.original_filename }}</span>
                        <a
                            :href="`/correspondence/attachments/${attachment.id}`"
                            class="font-semibold text-blue-900"
                        >
                            Download
                        </a>
                    </div>
                    <form
                        class="mt-4 grid gap-3"
                        @submit.prevent="uploadAttachment"
                    >
                        <input
                            type="file"
                            class="rounded-md border px-3 py-2"
                            @input="
                                attachmentForm.file =
                                    ($event.target as HTMLInputElement)
                                        .files?.[0] ?? null
                            "
                        />
                        <input
                            v-model="attachmentForm.description"
                            placeholder="Description"
                            class="rounded-md border px-3 py-2"
                        />
                        <button
                            class="rounded-md bg-slate-900 px-4 py-2 font-bold text-white"
                            :disabled="attachmentForm.processing"
                        >
                            Upload attachment
                        </button>
                    </form>
                </section>
                <div class="flex flex-wrap gap-2">
                    <button
                        class="rounded-md border px-4 py-2"
                        @click="postAction('submit')"
                    >
                        إرسال للمراجعة
                    </button>
                    <button
                        class="rounded-md border px-4 py-2"
                        @click="postAction('approve')"
                    >
                        اعتماد
                    </button>
                    <button
                        class="rounded-md border px-4 py-2"
                        @click="postAction('dispatch')"
                    >
                        توجيه
                    </button>
                    <button
                        class="rounded-md border px-4 py-2"
                        @click="postAction('complete')"
                    >
                        إكمال
                    </button>
                    <button
                        class="rounded-md border px-4 py-2"
                        @click="postAction('archive')"
                    >
                        أرشفة
                    </button>
                </div>
                <section>
                    <h2 class="font-bold">الردود</h2>
                    <div
                        v-for="item in correspondence.replies"
                        :key="item.id"
                        class="mt-3 rounded-lg border p-3"
                    >
                        <p class="text-sm font-semibold">
                            {{ item.sender?.name || '-' }}
                        </p>
                        <p class="mt-2 whitespace-pre-wrap">{{ item.body }}</p>
                    </div>
                    <form class="mt-4 grid gap-3" @submit.prevent="reply">
                        <textarea
                            v-model="replyForm.body"
                            required
                            class="min-h-28 rounded-md border px-3 py-2"
                        />
                        <button
                            class="rounded-md bg-blue-900 px-4 py-2 font-bold text-white"
                        >
                            إضافة رد
                        </button>
                    </form>
                </section>
            </article>
        </main>
    </AuthenticatedLayout>
</template>
