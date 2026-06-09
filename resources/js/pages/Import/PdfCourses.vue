<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, FileText, LoaderCircle, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';

interface CoursePreviewRow {
    code: string;
    name: string;
    units: number | null;
    semester: number | null;
    weekly_hours: string | null;
    raw: string;
}

interface ImportSummary {
    created: number;
    updated: number;
    skipped: number;
    errors: string[];
    rows: CoursePreviewRow[];
}

const selectedFile = ref<File | null>(null);
const previewRows = ref<CoursePreviewRow[]>([]);
const summary = ref<ImportSummary | null>(null);
const errorMessage = ref('');
const successMessage = ref('');
const isPreviewing = ref(false);
const isImporting = ref(false);
const progress = ref(0);

const canSubmit = computed(() => selectedFile.value && !isPreviewing.value && !isImporting.value);

const csrfToken = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

const resetMessages = () => {
    errorMessage.value = '';
    successMessage.value = '';
    summary.value = null;
};

const handleFileChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    resetMessages();
    previewRows.value = [];

    if (file && file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        selectedFile.value = null;
        errorMessage.value = 'يرجى اختيار ملف PDF فقط.';
        return;
    }

    selectedFile.value = file;
};

const sendPdf = async (preview: boolean) => {
    if (!selectedFile.value) {
        errorMessage.value = 'اختر ملف PDF أولاً.';
        return;
    }

    resetMessages();
    progress.value = 20;
    preview ? isPreviewing.value = true : isImporting.value = true;

    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('preview', preview ? '1' : '0');

    try {
        const response = await fetch('/api/import/courses/pdf', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            body: formData,
        });

        progress.value = 80;
        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.error || payload.message || 'تعذر تنفيذ العملية.');
        }

        if (preview) {
            previewRows.value = payload.preview || [];
            successMessage.value = `تم استخراج ${previewRows.value.length} صفاً للمعاينة.`;
        } else {
            summary.value = payload.summary;
            successMessage.value = payload.message || 'تم استيراد المقررات بنجاح.';
            previewRows.value = payload.summary?.rows || previewRows.value;
        }

        progress.value = 100;
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'حدث خطأ غير متوقع.';
        progress.value = 0;
    } finally {
        isPreviewing.value = false;
        isImporting.value = false;
    }
};
</script>

<template>
    <Head title="استيراد مقررات PDF" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="rounded-xl bg-blue-900 p-6 text-white shadow-lg">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-orange-300">استيراد البيانات</p>
                        <h1 class="mt-1 text-2xl font-bold">استيراد مقررات HISD من PDF</h1>
                        <p class="mt-2 text-sm text-blue-100">
                            ارفع ملف PDF يحتوي على جدول المقررات وسيتم استخراج الرمز والاسم والوحدات وحفظها في جدول المقررات.
                        </p>
                    </div>
                    <div class="rounded-lg bg-white/10 p-4">
                        <FileText class="h-10 w-10 text-orange-300" />
                    </div>
                </div>
            </section>

            <section class="rounded-xl border bg-white p-6 shadow-sm">
                <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">ملف PDF</label>
                        <input
                            type="file"
                            accept="application/pdf,.pdf"
                            class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2"
                            @change="handleFileChange"
                        />
                        <p class="text-xs text-slate-500">
                            يدعم النصوص العربية والإنجليزية قدر الإمكان. ملفات PDF المصورة تحتاج OCR قبل الاستيراد.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="outline" :disabled="!canSubmit" @click="sendPdf(true)">
                            <LoaderCircle v-if="isPreviewing" class="h-4 w-4 animate-spin" />
                            <FileText v-else class="h-4 w-4" />
                            معاينة
                        </Button>
                        <Button type="button" class="bg-orange-500 text-white hover:bg-orange-600" :disabled="!canSubmit" @click="sendPdf(false)">
                            <LoaderCircle v-if="isImporting" class="h-4 w-4 animate-spin" />
                            <Upload v-else class="h-4 w-4" />
                            استيراد
                        </Button>
                    </div>
                </div>

                <div v-if="isPreviewing || isImporting || progress > 0" class="mt-5">
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full bg-blue-700 transition-all" :style="{ width: `${progress}%` }"></div>
                    </div>
                </div>

                <div v-if="successMessage" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">
                    <div class="flex items-center gap-2">
                        <CheckCircle2 class="h-5 w-5" />
                        {{ successMessage }}
                    </div>
                </div>

                <div v-if="errorMessage" class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">
                    <div class="flex items-center gap-2">
                        <AlertCircle class="h-5 w-5" />
                        {{ errorMessage }}
                    </div>
                </div>
            </section>

            <section v-if="summary" class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">تمت الإضافة</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ summary.created }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">تم التحديث</p>
                    <p class="mt-2 text-3xl font-bold text-blue-700">{{ summary.updated }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">تم التجاوز</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">{{ summary.skipped }}</p>
                </div>
            </section>

            <section v-if="previewRows.length" class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <div class="border-b p-4">
                    <h2 class="font-bold text-slate-950">معاينة البيانات المستخرجة</h2>
                    <p class="text-sm text-slate-500">تأكد من البيانات قبل الاستيراد، خصوصاً ملفات PDF ذات الأعمدة المتعددة.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-right text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3">رمز المقرر</th>
                                <th class="px-4 py-3">اسم المقرر</th>
                                <th class="px-4 py-3">الوحدات</th>
                                <th class="px-4 py-3">الفصل</th>
                                <th class="px-4 py-3">الساعات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(row, index) in previewRows" :key="`${row.code}-${index}`">
                                <td class="px-4 py-3 font-mono font-bold">{{ row.code }}</td>
                                <td class="px-4 py-3">{{ row.name }}</td>
                                <td class="px-4 py-3">{{ row.units || '—' }}</td>
                                <td class="px-4 py-3">{{ row.semester || '—' }}</td>
                                <td class="px-4 py-3">{{ row.weekly_hours || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-if="summary?.errors?.length" class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                <h2 class="font-bold text-orange-800">ملاحظات الاستيراد</h2>
                <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-orange-700">
                    <li v-for="(error, index) in summary.errors" :key="index">{{ error }}</li>
                </ul>
            </section>
        </div>
    </main>
</template>
