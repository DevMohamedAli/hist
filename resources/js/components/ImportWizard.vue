<script setup lang="ts">
import {
    AlertCircle,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileSpreadsheet,
    Link2,
    Loader2,
    PlayCircle,
    UploadCloud,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface SchemaField {
    name: string;
    label: string;
}

interface WorkbookSummary {
    sheets: number;
    students: number;
    courses: number;
    grade_cells: number;
    skipped_cells: number;
}

interface WorkbookSheet {
    name: string;
    metadata: {
        season: string;
        academic_year: string | null;
        department: string;
        specialization: string;
        semester_level: number;
    };
    courses: { name: string; units: number }[];
    students: {
        name: string;
        registration_number: string;
        grades: unknown[];
    }[];
    grade_cells: number;
    skipped_cells: number;
}

interface WorkbookPreview {
    sheets: WorkbookSheet[];
    warnings: Record<string, unknown>[];
    summary: WorkbookSummary;
}

const props = withDefaults(defineProps<{ importType?: string }>(), {
    importType: 'students',
});

const steps = [
    { id: 1, label: 'رفع الملف', icon: UploadCloud },
    { id: 2, label: 'المعاينة', icon: Eye },
    { id: 3, label: 'اختيار الصفوف', icon: Link2 },
    { id: 4, label: 'التحقق', icon: CheckCircle2 },
    { id: 5, label: 'التنفيذ', icon: PlayCircle },
];

const importTypes = [
    { value: 'students', label: 'الطلاب' },
    { value: 'courses', label: 'المقررات الدراسية' },
    { value: 'grades', label: 'النتائج الفصلية' },
    { value: 'grade_workbook', label: 'كشف الدرجات متعدد الأوراق' },
];

const currentStep = ref(1);
const selectedImportType = ref(props.importType);
const jobId = ref<number | null>(null);
const fileName = ref('');
const columns = ref<string[]>([]);
const previewData = ref<Record<string, unknown>[]>([]);
const schema = ref<SchemaField[]>([]);
const mapping = ref<Record<string, string>>({});
const selectedRows = ref<boolean[]>([]);
const validationErrors = ref<Record<number, string[]>>({});
const workbook = ref<WorkbookPreview | null>(null);
const progress = ref(0);
const isImporting = ref(false);
const isUploading = ref(false);
const isLoadingPreview = ref(false);
const isValidating = ref(false);
const isDragging = ref(false);
const selectAll = ref(true);
const statusMessage = ref('');
const errorMessage = ref('');
const fileInput = ref<HTMLInputElement | null>(null);

const csrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

const requestJson = async <T,>(
    url: string,
    options: RequestInit = {},
): Promise<T> => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...(options.headers ?? {}),
        },
        ...options,
    });

    if (!response.ok) {
        const body = (await response.json().catch(() => null)) as {
            message?: string;
        } | null;
        throw new Error(
            body?.message ?? `Request failed with status ${response.status}`,
        );
    }

    return (await response.json()) as T;
};

const isWorkbookImport = computed(
    () => selectedImportType.value === 'grade_workbook',
);
const currentStepMeta = computed(() => steps[currentStep.value - 1]);
const previewRows = computed(() => previewData.value.slice(0, 8));
const mappedCount = computed(
    () => Object.values(mapping.value).filter(Boolean).length,
);
const errorCount = computed(() => Object.keys(validationErrors.value).length);
const selectedCount = computed(() => selectedRows.value.filter(Boolean).length);
const workbookWarningCount = computed(
    () => workbook.value?.warnings.length ?? 0,
);
const workbookStudents = computed(() => workbook.value?.summary.students ?? 0);
const workbookGradeCells = computed(
    () => workbook.value?.summary.grade_cells ?? 0,
);

const canMoveToNext = computed(() => {
    if (currentStep.value === 1) {
        return !!jobId.value && !isUploading.value;
    }

    if (isWorkbookImport.value) {
        return currentStep.value !== 4 || !isValidating.value;
    }

    if (currentStep.value === 2) {
        return columns.value.length > 0 && mappedCount.value > 0;
    }

    if (currentStep.value === 3) {
        return selectedCount.value > 0;
    }

    return true;
});

const selectedImportTypeLabel = computed(
    () =>
        importTypes.find((type) => type.value === selectedImportType.value)
            ?.label ?? selectedImportType.value,
);

const openFilePicker = () => fileInput.value?.click();
const clearMessages = () => {
    statusMessage.value = '';
    errorMessage.value = '';
};

const resetImportState = () => {
    jobId.value = null;
    fileName.value = '';
    columns.value = [];
    previewData.value = [];
    schema.value = [];
    mapping.value = {};
    selectedRows.value = [];
    validationErrors.value = {};
    workbook.value = null;
    progress.value = 0;
    selectAll.value = true;
};

const handleFileSelect = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (file) {
        await uploadFile(file);
    }

    input.value = '';
};

const handleDrop = async (event: DragEvent) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0];

    if (file) {
        await uploadFile(file);
    }
};

const applyPreviewResponse = (response: {
    columns?: string[];
    data?: Record<string, unknown>[];
    schema?: Record<string, { label?: string }>;
    workbook?: WorkbookPreview;
}) => {
    if (response.workbook) {
        workbook.value = response.workbook;
        statusMessage.value = `تم تحليل ${response.workbook.summary.sheets} أوراق و ${response.workbook.summary.grade_cells} خلية درجات.`;
        return;
    }

    previewData.value = response.data ?? [];
    columns.value = response.columns ?? columns.value;
    schema.value = Object.entries(response.schema ?? {}).map(
        ([name, field]) => ({
            name,
            label: field.label ?? name,
        }),
    );
    mapping.value = {};

    for (const col of columns.value) {
        const normalizedCol = col.trim().toLowerCase();
        const match = schema.value.find(
            (field) =>
                field.name.toLowerCase() === normalizedCol ||
                field.label.trim().toLowerCase() === normalizedCol,
        );

        mapping.value[col] = match?.name ?? '';
    }

    selectedRows.value = new Array(previewData.value.length).fill(true);
    statusMessage.value = `تم تحميل المعاينة: ${previewData.value.length} صف قابل للمراجعة.`;
};

const fetchPreview = async () => {
    if (!jobId.value) return;

    isLoadingPreview.value = true;

    try {
        applyPreviewResponse(
            await requestJson(`/api/import/preview/${jobId.value}`),
        );
    } finally {
        isLoadingPreview.value = false;
    }
};

const uploadFile = async (file: File) => {
    resetImportState();
    clearMessages();
    isUploading.value = true;
    fileName.value = file.name;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', selectedImportType.value);

    try {
        const response = await fetch('/api/import/upload', {
            body: formData,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            method: 'POST',
        });

        if (!response.ok) {
            const body = (await response.json().catch(() => null)) as {
                message?: string;
            } | null;
            throw new Error(body?.message ?? 'تعذر رفع الملف.');
        }

        const data = (await response.json()) as {
            job_id: number;
            columns?: string[];
            workbook?: WorkbookPreview;
        };
        jobId.value = data.job_id;
        columns.value = data.columns ?? [];

        if (data.workbook) {
            applyPreviewResponse(data);
        } else {
            await fetchPreview();
        }

        currentStep.value = 2;
    } catch (error) {
        errorMessage.value =
            error instanceof Error ? error.message : 'حدث خطأ أثناء رفع الملف.';
    } finally {
        isUploading.value = false;
    }
};

const validateData = async (): Promise<boolean> => {
    if (!jobId.value) return false;

    clearMessages();
    isValidating.value = true;

    try {
        const response = await requestJson<{
            valid?: boolean;
            errors?: Record<number, string[]>;
            warnings?: Record<string, unknown>[];
            summary?: WorkbookSummary;
        }>(`/api/import/validate/${jobId.value}`, {
            body: JSON.stringify({ mapping: mapping.value }),
            method: 'POST',
        });

        validationErrors.value = response.errors ?? {};

        if (response.valid === false) {
            errorMessage.value =
                'لم يتم العثور على بيانات قابلة للاستيراد في هذا الملف.';
            return false;
        }

        if (isWorkbookImport.value && workbook.value) {
            workbook.value.warnings =
                response.warnings ?? workbook.value.warnings;
            workbook.value.summary = response.summary ?? workbook.value.summary;
            statusMessage.value =
                workbookWarningCount.value > 0
                    ? `اكتمل التحقق مع ${workbookWarningCount.value} تحذيرات.`
                    : 'تم التحقق من كشف الدرجات بدون أخطاء مانعة.';
            return true;
        }

        statusMessage.value =
            errorCount.value > 0
                ? `اكتمل التحقق مع وجود ${errorCount.value} صف يحتاج مراجعة.`
                : 'تم التحقق من البيانات بدون أخطاء.';
        return true;
    } catch (error) {
        errorMessage.value =
            error instanceof Error
                ? error.message
                : 'خطأ في التحقق من البيانات.';
        return false;
    } finally {
        isValidating.value = false;
    }
};

const startImport = async () => {
    if (!jobId.value) return;

    clearMessages();
    isImporting.value = true;
    progress.value = 0;

    try {
        await requestJson(`/api/import/execute/${jobId.value}`, {
            body: JSON.stringify({
                mapping: mapping.value,
                selected_rows: selectedRows.value,
            }),
            method: 'POST',
        });

        const poll = window.setInterval(async () => {
            try {
                const res = await requestJson<{
                    progress?: number;
                    status?: string;
                    errors?: {
                        summary?: WorkbookSummary;
                        warnings?: Record<string, unknown>[];
                    };
                }>(`/api/import/progress/${jobId.value}`);
                progress.value = res.progress ?? 0;

                if (res.status === 'failed') {
                    window.clearInterval(poll);
                    isImporting.value = false;
                    errorMessage.value =
                        'تعذر تنفيذ الاستيراد. راجع سجل الأخطاء.';
                }

                if (progress.value === 100 || res.status === 'completed') {
                    window.clearInterval(poll);
                    isImporting.value = false;
                    statusMessage.value = 'تم استيراد البيانات بنجاح.';
                }
            } catch {
                window.clearInterval(poll);
                isImporting.value = false;
                errorMessage.value = 'تعذر متابعة حالة الاستيراد.';
            }
        }, 1000);
    } catch (error) {
        errorMessage.value =
            error instanceof Error ? error.message : 'حدث خطأ أثناء الاستيراد.';
        isImporting.value = false;
    }
};

const cancelImport = async () => {
    if (!jobId.value) return;

    try {
        await requestJson(`/api/import/cancel/${jobId.value}`, {
            method: 'POST',
        });
        isImporting.value = false;
        statusMessage.value = 'تم إلغاء العملية.';
    } catch {
        errorMessage.value = 'تعذر إلغاء العملية.';
    }
};

const prevStep = () => {
    if (currentStep.value > 1 && !isImporting.value) {
        currentStep.value--;
    }
};

const nextStep = async () => {
    if (!canMoveToNext.value) return;

    if (
        currentStep.value === 3 ||
        (isWorkbookImport.value && currentStep.value === 2)
    ) {
        const validated = await validateData();

        if (!validated) {
            return;
        }

        if (isWorkbookImport.value) {
            currentStep.value = 4;
            return;
        }
    }

    if (currentStep.value < steps.length) {
        currentStep.value++;
    }
};

const toggleAllRows = () => {
    selectedRows.value = selectedRows.value.map(() => selectAll.value);
};
</script>

<template>
    <section
        class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
        dir="rtl"
    >
        <div class="border-b border-gray-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="min-w-0">
                    <p class="text-xs font-bold text-orange-600">
                        معالج الاستيراد
                    </p>
                    <h2
                        class="mt-1 text-xl font-extrabold text-blue-900 sm:text-2xl"
                    >
                        {{ currentStepMeta.label }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ selectedImportTypeLabel }}
                        <span v-if="fileName"> · {{ fileName }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-5 gap-2">
                    <div
                        v-for="step in steps"
                        :key="step.id"
                        class="flex min-w-0 flex-col items-center gap-1 rounded-md border px-2 py-2 text-center"
                        :class="
                            currentStep >= step.id
                                ? 'border-blue-200 bg-blue-50 text-blue-800'
                                : 'border-gray-200 bg-white text-gray-400'
                        "
                    >
                        <component :is="step.icon" class="h-4 w-4 shrink-0" />
                        <span
                            class="hidden text-[11px] leading-4 font-bold sm:block"
                            >{{ step.label }}</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5 p-5 sm:p-6">
            <div
                v-if="statusMessage"
                class="flex items-center gap-2 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"
            >
                <CheckCircle2 class="h-5 w-5 shrink-0" />
                <span>{{ statusMessage }}</span>
            </div>

            <div
                v-if="errorMessage"
                class="flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800"
            >
                <XCircle class="h-5 w-5 shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <div
                v-if="currentStep === 1"
                class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <button
                    type="button"
                    class="group flex min-h-72 flex-col items-center justify-center rounded-lg border-2 border-dashed p-8 text-center transition"
                    :class="
                        isDragging
                            ? 'border-blue-600 bg-blue-50'
                            : 'border-blue-200 bg-white hover:border-blue-500 hover:bg-blue-50/60'
                    "
                    @click="openFilePicker"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        accept=".xlsx,.xls,.csv"
                        @change="handleFileSelect"
                    />
                    <span
                        class="rounded-full bg-blue-50 p-4 text-blue-700 ring-1 ring-blue-100 transition group-hover:bg-white"
                    >
                        <Loader2
                            v-if="isUploading"
                            class="h-10 w-10 animate-spin"
                        />
                        <UploadCloud v-else class="h-10 w-10" />
                    </span>
                    <span class="mt-5 text-lg font-extrabold text-gray-900">
                        {{
                            isUploading
                                ? 'جاري رفع الملف...'
                                : 'اسحب الملف هنا أو انقر للاختيار'
                        }}
                    </span>
                    <span class="mt-2 text-sm text-gray-500"
                        >CSV أو XLS أو XLSX</span
                    >
                </button>

                <aside
                    class="rounded-lg border border-gray-200 bg-slate-50 p-4"
                >
                    <label class="block text-sm font-bold text-gray-800"
                        >نوع البيانات</label
                    >
                    <select
                        v-model="selectedImportType"
                        class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                    >
                        <option
                            v-for="type in importTypes"
                            :key="type.value"
                            :value="type.value"
                        >
                            {{ type.label }}
                        </option>
                    </select>

                    <div class="mt-5 flex gap-2 text-sm text-gray-600">
                        <FileSpreadsheet
                            class="mt-0.5 h-5 w-5 shrink-0 text-blue-700"
                        />
                        <p>
                            اختر كشف الدرجات متعدد الأوراق لملفات النتائج التي
                            تحتوي على عدة فصول داخل نفس المصنف.
                        </p>
                    </div>
                </aside>
            </div>

            <div
                v-if="currentStep === 2 && isWorkbookImport && workbook"
                class="space-y-5"
            >
                <div class="grid gap-4 sm:grid-cols-5">
                    <div
                        class="rounded-md border border-blue-100 bg-blue-50 p-4"
                    >
                        <p class="text-xs font-bold text-blue-700">الأوراق</p>
                        <p class="mt-1 text-2xl font-extrabold text-blue-900">
                            {{ workbook.summary.sheets }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-green-100 bg-green-50 p-4"
                    >
                        <p class="text-xs font-bold text-green-700">الطلاب</p>
                        <p class="mt-1 text-2xl font-extrabold text-green-900">
                            {{ workbookStudents }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-gray-200 bg-gray-50 p-4"
                    >
                        <p class="text-xs font-bold text-gray-600">المقررات</p>
                        <p class="mt-1 text-2xl font-extrabold text-gray-900">
                            {{ workbook.summary.courses }}
                        </p>
                    </div>
                    <div class="rounded-md border border-blue-100 bg-white p-4">
                        <p class="text-xs font-bold text-blue-700">
                            خلايا الدرجات
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-blue-900">
                            {{ workbookGradeCells }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-amber-200 bg-amber-50 p-4"
                    >
                        <p class="text-xs font-bold text-amber-700">تحذيرات</p>
                        <p class="mt-1 text-2xl font-extrabold text-amber-900">
                            {{ workbookWarningCount }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 lg:grid-cols-2">
                    <div
                        v-for="sheet in workbook.sheets"
                        :key="sheet.name"
                        class="rounded-md border border-gray-200 bg-white p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3
                                    class="text-base font-extrabold text-gray-900"
                                >
                                    {{ sheet.name }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ sheet.metadata.department }} ·
                                    {{ sheet.metadata.specialization }} · الفصل
                                    {{ sheet.metadata.semester_level }}
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700"
                            >
                                {{ sheet.grade_cells }} درجة
                            </span>
                        </div>
                        <div
                            class="mt-4 grid grid-cols-3 gap-2 text-center text-sm"
                        >
                            <div class="rounded bg-gray-50 p-2">
                                <p class="font-bold text-gray-900">
                                    {{ sheet.students.length }}
                                </p>
                                <p class="text-xs text-gray-500">طلاب</p>
                            </div>
                            <div class="rounded bg-gray-50 p-2">
                                <p class="font-bold text-gray-900">
                                    {{ sheet.courses.length }}
                                </p>
                                <p class="text-xs text-gray-500">مقررات</p>
                            </div>
                            <div class="rounded bg-gray-50 p-2">
                                <p class="font-bold text-gray-900">
                                    {{ sheet.skipped_cells }}
                                </p>
                                <p class="text-xs text-gray-500">متروك</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="currentStep === 2 && !isWorkbookImport"
                class="space-y-5"
            >
                <div class="grid gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-md border border-blue-100 bg-blue-50 p-4"
                    >
                        <p class="text-xs font-bold text-blue-700">
                            الأعمدة المقروءة
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-blue-900">
                            {{ columns.length }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-green-100 bg-green-50 p-4"
                    >
                        <p class="text-xs font-bold text-green-700">
                            حقول مربوطة
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-green-900">
                            {{ mappedCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-gray-200 bg-gray-50 p-4"
                    >
                        <p class="text-xs font-bold text-gray-600">
                            صفوف المعاينة
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-gray-900">
                            {{ previewData.length }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="isLoadingPreview"
                    class="flex items-center justify-center gap-2 rounded-lg border p-10 text-blue-700"
                >
                    <Loader2 class="h-5 w-5 animate-spin" />
                    <span class="font-bold">جاري تجهيز المعاينة...</span>
                </div>

                <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="col in columns"
                        :key="col"
                        class="rounded-md border border-gray-200 bg-white p-4 shadow-sm"
                    >
                        <p
                            class="truncate text-sm font-extrabold text-gray-900"
                        >
                            {{ col }}
                        </p>
                        <p class="mt-1 truncate text-xs text-gray-500">
                            مثال: {{ previewData[0]?.[col] ?? '-' }}
                        </p>
                        <select
                            v-model="mapping[col]"
                            class="mt-4 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                        >
                            <option value="">تجاهل هذا العمود</option>
                            <option
                                v-for="field in schema"
                                :key="field.name"
                                :value="field.name"
                            >
                                {{ field.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div
                v-if="currentStep === 3 && !isWorkbookImport"
                class="space-y-4"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h3 class="text-lg font-extrabold text-blue-900">
                            معاينة الصفوف
                        </h3>
                        <p class="text-sm text-gray-500">
                            اختر الصفوف التي تريد تنفيذ الاستيراد عليها.
                        </p>
                    </div>
                    <label
                        class="inline-flex items-center gap-2 text-sm font-bold text-blue-800"
                    >
                        <input
                            v-model="selectAll"
                            type="checkbox"
                            class="rounded border-gray-300"
                            @change="toggleAllRows"
                        />
                        تحديد الكل
                    </label>
                </div>

                <div
                    class="max-h-[460px] overflow-auto rounded-lg border border-gray-200"
                >
                    <table class="min-w-full text-right text-sm">
                        <thead class="sticky top-0 bg-gray-100 text-gray-700">
                            <tr>
                                <th class="w-12 border-b px-3 py-3"></th>
                                <th
                                    v-for="col in columns"
                                    :key="col"
                                    class="border-b px-3 py-3 whitespace-nowrap"
                                >
                                    {{ col }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr
                                v-for="(row, rowIndex) in previewRows"
                                :key="rowIndex"
                                class="hover:bg-blue-50/40"
                            >
                                <td class="px-3 py-3">
                                    <input
                                        v-model="selectedRows[rowIndex]"
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                    />
                                </td>
                                <td
                                    v-for="col in columns"
                                    :key="col"
                                    class="max-w-56 truncate px-3 py-3 text-gray-700"
                                >
                                    {{ row[col] ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentStep === 4" class="space-y-5">
                <div
                    v-if="isWorkbookImport && workbook"
                    class="grid gap-4 sm:grid-cols-3"
                >
                    <div
                        class="rounded-md border border-green-200 bg-green-50 p-5 text-center"
                    >
                        <p class="text-sm font-bold text-green-700">
                            درجات جاهزة
                        </p>
                        <p class="mt-1 text-3xl font-extrabold text-green-900">
                            {{ workbook.summary.grade_cells }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-amber-200 bg-amber-50 p-5 text-center"
                    >
                        <p class="text-sm font-bold text-amber-700">تحذيرات</p>
                        <p class="mt-1 text-3xl font-extrabold text-amber-900">
                            {{ workbookWarningCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-blue-200 bg-blue-50 p-5 text-center"
                    >
                        <p class="text-sm font-bold text-blue-700">طلاب</p>
                        <p class="mt-1 text-3xl font-extrabold text-blue-900">
                            {{ workbook.summary.students }}
                        </p>
                    </div>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-md border border-green-200 bg-green-50 p-5 text-center"
                    >
                        <p class="text-sm font-bold text-green-700">
                            صفوف صالحة
                        </p>
                        <p class="mt-1 text-3xl font-extrabold text-green-900">
                            {{ previewData.length - errorCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-red-200 bg-red-50 p-5 text-center"
                    >
                        <p class="text-sm font-bold text-red-700">أخطاء</p>
                        <p class="mt-1 text-3xl font-extrabold text-red-900">
                            {{ errorCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-blue-200 bg-blue-50 p-5 text-center"
                    >
                        <p class="text-sm font-bold text-blue-700">
                            محدد للاستيراد
                        </p>
                        <p class="mt-1 text-3xl font-extrabold text-blue-900">
                            {{ selectedCount }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="isWorkbookImport && workbookWarningCount > 0"
                    class="max-h-72 overflow-y-auto rounded-lg border border-amber-100 bg-amber-50 p-4"
                >
                    <div
                        v-for="(warning, index) in workbook?.warnings"
                        :key="index"
                        class="mb-2 rounded-md bg-white p-3 text-sm shadow-sm last:mb-0"
                    >
                        <p class="font-extrabold text-gray-900">
                            {{ warning.sheet ?? '-' }} ·
                            {{ warning.course ?? '-' }}
                        </p>
                        <p class="mt-1 text-amber-700">{{ warning.message }}</p>
                    </div>
                </div>

                <div
                    v-if="!isWorkbookImport && errorCount > 0"
                    class="max-h-72 overflow-y-auto rounded-lg border border-red-100 bg-red-50 p-4"
                >
                    <div
                        v-for="(errors, index) in validationErrors"
                        :key="index"
                        class="mb-2 rounded-md bg-white p-3 text-sm shadow-sm last:mb-0"
                    >
                        <p class="font-extrabold text-gray-900">
                            الصف {{ Number(index) + 1 }}
                        </p>
                        <p class="mt-1 text-red-700">{{ errors.join('، ') }}</p>
                    </div>
                </div>
            </div>

            <div v-if="currentStep === 5" class="space-y-6">
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-6">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-bold text-blue-700">
                                جاهز للتنفيذ
                            </p>
                            <h3
                                class="mt-1 text-2xl font-extrabold text-blue-950"
                            >
                                استيراد
                                {{
                                    isWorkbookImport
                                        ? workbookGradeCells
                                        : selectedCount
                                }}
                                سجل
                            </h3>
                        </div>
                        <button
                            v-if="!isImporting"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-800"
                            @click="startImport"
                        >
                            <PlayCircle class="h-5 w-5" />
                            بدء الاستيراد
                        </button>
                    </div>

                    <div v-if="isImporting" class="mt-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-3 flex-1 overflow-hidden rounded-full bg-white"
                            >
                                <div
                                    class="h-full rounded-full bg-blue-700 transition-all duration-300"
                                    :style="{ width: `${progress}%` }"
                                />
                            </div>
                            <span
                                class="w-12 text-left text-sm font-extrabold text-blue-900"
                                >{{ progress }}%</span
                            >
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50"
                            @click="cancelImport"
                        >
                            <XCircle class="h-4 w-4" />
                            إلغاء العملية
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="sticky bottom-0 flex items-center justify-between border-t border-gray-200 bg-white/95 px-5 py-4 backdrop-blur sm:px-6"
        >
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-5 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="currentStep === 1 || isImporting"
                @click="prevStep"
            >
                <ChevronRight class="h-4 w-4" />
                السابق
            </button>

            <div
                class="hidden items-center gap-2 text-xs font-semibold text-gray-500 sm:flex"
            >
                <AlertCircle class="h-4 w-4" />
                <span>راجع المعاينة والتحقق قبل التنفيذ.</span>
            </div>

            <button
                v-if="currentStep < 5"
                type="button"
                :disabled="!canMoveToNext"
                class="inline-flex items-center gap-2 rounded-md bg-blue-700 px-6 py-2 text-sm font-bold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:bg-gray-300"
                @click="nextStep"
            >
                التالي
                <ChevronLeft class="h-4 w-4" />
            </button>
            <button
                v-else
                type="button"
                class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-5 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50"
                @click="
                    currentStep = 1;
                    resetImportState();
                    clearMessages();
                "
            >
                عملية جديدة
            </button>
        </div>
    </section>
</template>
