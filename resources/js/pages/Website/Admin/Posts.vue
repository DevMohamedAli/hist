<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    CalendarClock,
    CheckCircle2,
    FileText,
    ImagePlus,
    Megaphone,
    Newspaper,
    Pencil,
    Save,
    Send,
    UploadCloud,
    X,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';

defineOptions({ layout: [] });

type WebsitePost = {
    id: number;
    title: string;
    slug: string;
    summary?: string;
    content?: string;
    type: string;
    status: string;
    featured_image_path?: string;
    featured_image_url?: string;
    seo_title?: string;
    seo_description?: string;
    published_at?: string;
    starts_at?: string;
    ends_at?: string;
};

type FlashProps = {
    flash?: {
        success?: string | null;
        error?: string | null;
        message?: string | null;
    };
};

const props = defineProps<{
    posts: {
        data: Array<WebsitePost>;
    };
    canPublish: boolean;
}>();

const page = usePage<FlashProps>();

const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
const maxImageSize = 4 * 1024 * 1024;
const fallbackImage = '/assets/img/website-news-hero.svg';

const editingId = ref<number | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
const savedImagePreview = ref<string | null>(null);
const imagePreview = ref<string | null>(null);
const imageError = ref<string | null>(null);
const selectedImageName = ref<string | null>(null);
const isDraggingImage = ref(false);

const form = useForm({
    _method: '',
    title: '',
    slug: '',
    summary: '',
    content: '',
    type: 'news',
    status: 'draft',
    seo_title: '',
    seo_description: '',
    featured_image: null as File | null,
    published_at: '',
    starts_at: '',
    ends_at: '',
});

const typeLabels: Record<string, string> = {
    news: 'خبر',
    announcement: 'إعلان',
    event: 'فعالية',
};

const statusLabels: Record<string, string> = {
    draft: 'مسودة',
    under_review: 'قيد المراجعة',
    scheduled: 'مجدول',
    published: 'منشور',
    archived: 'مؤرشف',
};

const statusClasses: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    under_review: 'bg-amber-100 text-amber-800',
    scheduled: 'bg-sky-100 text-sky-800',
    published: 'bg-emerald-100 text-emerald-800',
    archived: 'bg-zinc-200 text-zinc-700',
};

const postCounts = computed(() => ({
    news: props.posts.data.filter((post) => post.type === 'news').length,
    announcement: props.posts.data.filter(
        (post) => post.type === 'announcement',
    ).length,
    event: props.posts.data.filter((post) => post.type === 'event').length,
    published: props.posts.data.filter((post) => post.status === 'published')
        .length,
}));

const formTitle = computed(() =>
    editingId.value ? 'تعديل الخبر أو الإعلان' : 'إضافة خبر أو إعلان جديد',
);
const isEvent = computed(() => form.type === 'event');
const cannotPublish = computed(
    () => !props.canPublish && form.status === 'published',
);
const hasServerError = computed(
    () => Object.keys(form.errors).length > 0 || Boolean(imageError.value),
);

const normalizeDateTime = (value?: string) => (value ? value.slice(0, 16) : '');

const formatFileSize = (bytes: number) =>
    `${(bytes / 1024 / 1024).toFixed(2)} MB`;

const buildSlug = () => {
    const base = form.title
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    form.slug =
        base ||
        `${form.type}-${new Date().toISOString().slice(0, 10)}-${Date.now().toString().slice(-4)}`;
    form.clearErrors('slug');
};

const resetImageInput = () => {
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const revokeLocalPreview = () => {
    if (imagePreview.value?.startsWith('blob:')) {
        URL.revokeObjectURL(imagePreview.value);
    }
};

const resetForm = () => {
    revokeLocalPreview();
    editingId.value = null;
    savedImagePreview.value = null;
    imagePreview.value = null;
    imageError.value = null;
    selectedImageName.value = null;
    form.reset();
    form.clearErrors();
    resetImageInput();
};

const editPost = (post: WebsitePost) => {
    revokeLocalPreview();
    editingId.value = post.id;
    savedImagePreview.value =
        post.featured_image_url || post.featured_image_path || null;
    imagePreview.value = savedImagePreview.value;
    imageError.value = null;
    selectedImageName.value = null;
    form._method = '';
    form.title = post.title;
    form.slug = post.slug;
    form.summary = post.summary || '';
    form.content = post.content || '';
    form.type = post.type;
    form.status = post.status;
    form.seo_title = post.seo_title || '';
    form.seo_description = post.seo_description || '';
    form.featured_image = null;
    form.published_at = normalizeDateTime(post.published_at);
    form.starts_at = normalizeDateTime(post.starts_at);
    form.ends_at = normalizeDateTime(post.ends_at);
    form.clearErrors();
    resetImageInput();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const validateImage = (file: File | null) => {
    imageError.value = null;

    if (!file) {
        return false;
    }

    if (!allowedImageTypes.includes(file.type)) {
        imageError.value = 'صيغة الصورة غير مدعومة. استخدم JPG أو PNG أو WEBP.';
        return false;
    }

    if (file.size > maxImageSize) {
        imageError.value = `حجم الصورة ${formatFileSize(file.size)} ويتجاوز الحد الأقصى 4MB.`;
        return false;
    }

    return true;
};

const setImageFile = (file: File | null) => {
    revokeLocalPreview();

    if (!file) {
        form.featured_image = null;
        imagePreview.value = savedImagePreview.value;
        selectedImageName.value = null;
        return;
    }

    if (!validateImage(file)) {
        form.featured_image = null;
        imagePreview.value = savedImagePreview.value;
        selectedImageName.value = null;
        resetImageInput();
        return;
    }

    form.clearErrors('featured_image');
    form.featured_image = file;
    selectedImageName.value = `${file.name} — ${formatFileSize(file.size)}`;
    imagePreview.value = URL.createObjectURL(file);
};

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    setImageFile(target.files?.[0] ?? null);
};

const onImageDrop = (event: DragEvent) => {
    isDraggingImage.value = false;
    setImageFile(event.dataTransfer?.files?.[0] ?? null);
};

const clearSelectedImage = () => {
    setImageFile(null);
    imageError.value = null;
    resetImageInput();
};

const validateBeforeSubmit = () => {
    form.clearErrors();
    imageError.value = null;

    if (!form.title.trim()) {
        form.setError('title', 'العنوان مطلوب.');
    }

    if (!form.slug.trim()) {
        form.setError(
            'slug',
            'الرابط المختصر مطلوب. استخدم زر إنشاء رابط تلقائي.',
        );
    }

    if (cannotPublish.value) {
        form.setError(
            'status',
            'لا تملك صلاحية النشر. احفظ كمسودة أو اطلب صلاحية النشر.',
        );
    }

    if (form.featured_image && !validateImage(form.featured_image)) {
        form.setError(
            'featured_image',
            imageError.value || 'الصورة غير صالحة.',
        );
    }

    if (form.starts_at && form.ends_at && form.ends_at < form.starts_at) {
        form.setError('ends_at', 'نهاية الفعالية يجب أن تكون بعد البداية.');
    }

    return !hasServerError.value;
};

const submit = (targetStatus?: 'draft' | 'published') => {
    if (targetStatus) {
        form.status = targetStatus;
    }

    if (!validateBeforeSubmit()) {
        return;
    }

    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: resetForm,
    };

    if (editingId.value) {
        form._method = 'put';
        form.post(`/admin/website/posts/${editingId.value}`, options);
        return;
    }

    form._method = '';
    form.post('/admin/website/posts', options);
};
</script>

<template>
    <Head title="إدارة الأخبار والإعلانات" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-slate-50 p-6" dir="rtl">
            <div class="mx-auto max-w-7xl space-y-6">
                <section
                    class="overflow-hidden rounded-3xl bg-blue-950 text-white shadow-xl"
                >
                    <div
                        class="grid gap-6 p-7 lg:grid-cols-[1fr_420px] lg:items-center"
                    >
                        <div>
                            <p class="text-sm font-extrabold text-orange-300">
                                إدارة واجهة الموقع
                            </p>
                            <h1 class="mt-2 text-3xl font-black md:text-4xl">
                                الأخبار والإعلانات
                            </h1>
                            <p class="mt-3 max-w-3xl leading-8 text-blue-100">
                                واجهة واحدة لإضافة الأخبار والإعلانات
                                والفعاليات، مع رفع صورة آمن ومعاينة واضحة قبل
                                الحفظ.
                            </p>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <article
                                class="rounded-2xl bg-white/10 p-4 text-center ring-1 ring-white/15"
                            >
                                <Newspaper
                                    class="mx-auto h-7 w-7 text-orange-300"
                                />
                                <p class="mt-2 text-2xl font-black">
                                    {{ postCounts.news }}
                                </p>
                                <p class="text-xs font-bold text-blue-100">
                                    أخبار
                                </p>
                            </article>
                            <article
                                class="rounded-2xl bg-white/10 p-4 text-center ring-1 ring-white/15"
                            >
                                <Megaphone
                                    class="mx-auto h-7 w-7 text-orange-300"
                                />
                                <p class="mt-2 text-2xl font-black">
                                    {{ postCounts.announcement }}
                                </p>
                                <p class="text-xs font-bold text-blue-100">
                                    إعلانات
                                </p>
                            </article>
                            <article
                                class="rounded-2xl bg-white/10 p-4 text-center ring-1 ring-white/15"
                            >
                                <CalendarClock
                                    class="mx-auto h-7 w-7 text-orange-300"
                                />
                                <p class="mt-2 text-2xl font-black">
                                    {{ postCounts.event }}
                                </p>
                                <p class="text-xs font-bold text-blue-100">
                                    فعاليات
                                </p>
                            </article>
                            <article
                                class="rounded-2xl bg-white/10 p-4 text-center ring-1 ring-white/15"
                            >
                                <CheckCircle2
                                    class="mx-auto h-7 w-7 text-orange-300"
                                />
                                <p class="mt-2 text-2xl font-black">
                                    {{ postCounts.published }}
                                </p>
                                <p class="text-xs font-bold text-blue-100">
                                    منشور
                                </p>
                            </article>
                        </div>
                    </div>
                </section>

                <section
                    v-if="page.props.flash?.success || page.props.flash?.error"
                    class="rounded-2xl border p-4 shadow-sm"
                    :class="
                        page.props.flash?.error
                            ? 'border-red-200 bg-red-50 text-red-800'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    "
                >
                    <div class="flex items-center gap-2 font-extrabold">
                        <AlertCircle
                            v-if="page.props.flash?.error"
                            class="h-5 w-5"
                        />
                        <CheckCircle2 v-else class="h-5 w-5" />
                        {{
                            page.props.flash?.error || page.props.flash?.success
                        }}
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl bg-white shadow">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5"
                    >
                        <div>
                            <h2 class="text-2xl font-black text-blue-950">
                                {{ formTitle }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                الصورة اختيارية، لكن عند رفعها يجب أن تكون JPG
                                أو PNG أو WEBP وبحجم لا يتجاوز 4MB.
                            </p>
                        </div>
                        <button
                            v-if="editingId"
                            type="button"
                            class="rounded-full border border-slate-300 px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-slate-100"
                            @click="resetForm"
                        >
                            إلغاء التعديل
                        </button>
                    </div>

                    <form
                        class="grid gap-6 p-6 lg:grid-cols-[1fr_390px]"
                        @submit.prevent="submit()"
                    >
                        <section class="grid gap-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >العنوان</span
                                    >
                                    <input
                                        v-model="form.title"
                                        required
                                        placeholder="مثال: بدء التسجيل للفصل الجديد"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                    <span
                                        v-if="form.errors.title"
                                        class="text-xs font-bold text-red-600"
                                        >{{ form.errors.title }}</span
                                    >
                                </label>

                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >الرابط المختصر</span
                                    >
                                    <div class="flex gap-2">
                                        <input
                                            v-model="form.slug"
                                            required
                                            dir="ltr"
                                            placeholder="news-2026-0001"
                                            class="min-w-0 flex-1 rounded-2xl border border-slate-300 px-4 py-3 text-left transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        />
                                        <button
                                            type="button"
                                            class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-orange-100 hover:text-orange-700"
                                            @click="buildSlug"
                                        >
                                            تلقائي
                                        </button>
                                    </div>
                                    <span
                                        v-if="form.errors.slug"
                                        class="text-xs font-bold text-red-600"
                                        >{{ form.errors.slug }}</span
                                    >
                                </label>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >نوع المحتوى</span
                                    >
                                    <select
                                        v-model="form.type"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="news">خبر</option>
                                        <option value="announcement">
                                            إعلان
                                        </option>
                                        <option value="event">فعالية</option>
                                    </select>
                                </label>
                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >حالة النشر</span
                                    >
                                    <select
                                        v-model="form.status"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="draft">مسودة</option>
                                        <option value="under_review">
                                            قيد المراجعة
                                        </option>
                                        <option value="scheduled">مجدول</option>
                                        <option
                                            value="published"
                                            :disabled="!canPublish"
                                        >
                                            منشور
                                        </option>
                                        <option value="archived">مؤرشف</option>
                                    </select>
                                    <span
                                        v-if="form.errors.status"
                                        class="text-xs font-bold text-red-600"
                                        >{{ form.errors.status }}</span
                                    >
                                    <span
                                        v-else-if="!canPublish"
                                        class="text-xs font-bold text-orange-600"
                                        >لا تملك صلاحية النشر حالياً؛ يمكنك
                                        الحفظ كمسودة.</span
                                    >
                                </label>
                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >تاريخ النشر</span
                                    >
                                    <input
                                        v-model="form.published_at"
                                        type="datetime-local"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                </label>
                            </div>

                            <label class="grid gap-2">
                                <span
                                    class="text-sm font-extrabold text-slate-700"
                                    >الملخص</span
                                >
                                <textarea
                                    v-model="form.summary"
                                    rows="3"
                                    placeholder="ملخص قصير يظهر في شريط الأخبار والبطاقات."
                                    class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                />
                                <span
                                    v-if="form.errors.summary"
                                    class="text-xs font-bold text-red-600"
                                    >{{ form.errors.summary }}</span
                                >
                            </label>

                            <label class="grid gap-2">
                                <span
                                    class="text-sm font-extrabold text-slate-700"
                                    >المحتوى الكامل</span
                                >
                                <textarea
                                    v-model="form.content"
                                    rows="8"
                                    placeholder="اكتب تفاصيل الخبر أو الإعلان هنا."
                                    class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                />
                            </label>

                            <div
                                v-if="isEvent"
                                class="grid gap-4 md:grid-cols-2"
                            >
                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >بداية الفعالية</span
                                    >
                                    <input
                                        v-model="form.starts_at"
                                        type="datetime-local"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                </label>
                                <label class="grid gap-2">
                                    <span
                                        class="text-sm font-extrabold text-slate-700"
                                        >نهاية الفعالية</span
                                    >
                                    <input
                                        v-model="form.ends_at"
                                        type="datetime-local"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                    <span
                                        v-if="form.errors.ends_at"
                                        class="text-xs font-bold text-red-600"
                                        >{{ form.errors.ends_at }}</span
                                    >
                                </label>
                            </div>

                            <details
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <summary
                                    class="cursor-pointer font-black text-blue-950"
                                >
                                    إعدادات SEO اختيارية
                                </summary>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <input
                                        v-model="form.seo_title"
                                        placeholder="عنوان SEO"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                    <input
                                        v-model="form.seo_description"
                                        placeholder="وصف SEO"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 transition outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                </div>
                            </details>

                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3 font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="form.processing"
                                    @click="submit('draft')"
                                >
                                    <Save class="h-5 w-5" />
                                    حفظ كمسودة
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-900 px-6 py-3 font-black text-white shadow-lg shadow-blue-900/20 transition hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="form.processing || !canPublish"
                                    @click="submit('published')"
                                >
                                    <Send class="h-5 w-5" />
                                    {{
                                        editingId
                                            ? 'حفظ ونشر التعديل'
                                            : 'حفظ ونشر'
                                    }}
                                </button>
                            </div>
                        </section>

                        <aside class="space-y-5">
                            <section
                                class="rounded-3xl border border-dashed bg-slate-50 p-5 transition"
                                :class="
                                    isDraggingImage
                                        ? 'border-orange-400 bg-orange-50'
                                        : 'border-slate-300'
                                "
                                @dragover.prevent="isDraggingImage = true"
                                @dragleave.prevent="isDraggingImage = false"
                                @drop.prevent="onImageDrop"
                            >
                                <input
                                    ref="imageInput"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg,image/webp"
                                    class="hidden"
                                    @change="onImageChange"
                                />
                                <div
                                    class="overflow-hidden rounded-3xl bg-white shadow-sm"
                                >
                                    <div
                                        class="flex aspect-[16/10] items-center justify-center bg-slate-100"
                                    >
                                        <img
                                            v-if="imagePreview"
                                            :src="imagePreview"
                                            alt="معاينة صورة الخبر"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="px-6 text-center text-slate-400"
                                        >
                                            <ImagePlus
                                                class="mx-auto h-14 w-14"
                                            />
                                            <p
                                                class="mt-3 text-sm font-extrabold"
                                            >
                                                اسحب الصورة هنا أو اخترها من
                                                جهازك
                                            </p>
                                            <p class="mt-1 text-xs">
                                                تظهر الصورة في الهيرو وشريط
                                                الأخبار.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-500 px-4 py-3 font-black text-white transition hover:bg-orange-600"
                                            @click="imageInput?.click()"
                                        >
                                            <UploadCloud class="h-5 w-5" />
                                            رفع صورة الخبر
                                        </button>
                                        <button
                                            v-if="form.featured_image"
                                            type="button"
                                            class="mt-2 flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-extrabold text-slate-700"
                                            @click="clearSelectedImage"
                                        >
                                            <X class="h-4 w-4" />
                                            إزالة الصورة المختارة
                                        </button>
                                        <p
                                            v-if="selectedImageName"
                                            class="mt-3 text-center text-xs font-bold text-slate-600"
                                        >
                                            {{ selectedImageName }}
                                        </p>
                                        <p
                                            class="mt-3 text-center text-xs leading-6 text-slate-500"
                                        >
                                            JPG, PNG, WEBP — الحد الأقصى 4MB.
                                        </p>
                                        <p
                                            v-if="
                                                imageError ||
                                                form.errors.featured_image
                                            "
                                            class="mt-2 rounded-xl bg-red-50 p-3 text-center text-sm font-bold text-red-700"
                                        >
                                            {{
                                                imageError ||
                                                form.errors.featured_image
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section
                                class="rounded-3xl bg-blue-950 p-5 text-white shadow"
                            >
                                <FileText class="h-6 w-6 text-orange-300" />
                                <h3 class="mt-2 font-black">
                                    لماذا قد يفشل رفع الصورة؟
                                </h3>
                                <ul
                                    class="mt-2 list-disc space-y-1 pr-5 text-sm leading-6 text-blue-100"
                                >
                                    <li>الصورة أكبر من 4MB.</li>
                                    <li>الصيغة ليست JPG أو PNG أو WEBP.</li>
                                    <li>محاولة النشر بدون صلاحية نشر.</li>
                                </ul>
                            </section>
                        </aside>
                    </form>
                </section>

                <section class="rounded-3xl bg-white p-6 shadow">
                    <div
                        class="mb-5 flex flex-wrap items-center justify-between gap-3"
                    >
                        <div>
                            <h2 class="text-2xl font-black text-blue-950">
                                المحتوى الحالي
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                اضغط تعديل لتحديث النص أو استبدال الصورة.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="post in posts.data"
                            :key="post.id"
                            class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                        >
                            <div
                                class="relative h-48 overflow-hidden bg-slate-100"
                            >
                                <img
                                    :src="
                                        post.featured_image_url ||
                                        post.featured_image_path ||
                                        fallbackImage
                                    "
                                    :alt="post.title"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div
                                    class="absolute inset-x-4 top-4 flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="rounded-full bg-blue-950 px-3 py-1 text-xs font-black text-white"
                                        >{{
                                            typeLabels[post.type] || post.type
                                        }}</span
                                    >
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-black"
                                        :class="
                                            statusClasses[post.status] ||
                                            'bg-white text-blue-950'
                                        "
                                        >{{
                                            statusLabels[post.status] ||
                                            post.status
                                        }}</span
                                    >
                                </div>
                            </div>
                            <div class="p-5">
                                <h3
                                    class="line-clamp-2 text-lg font-black text-blue-950"
                                >
                                    {{ post.title }}
                                </h3>
                                <p
                                    class="mt-2 line-clamp-3 min-h-16 text-sm leading-7 text-slate-500"
                                >
                                    {{
                                        post.summary ||
                                        'لا يوجد ملخص لهذا المحتوى بعد.'
                                    }}
                                </p>
                                <div
                                    class="mt-4 flex items-center justify-between gap-3"
                                >
                                    <code
                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500"
                                        >{{ post.slug }}</code
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-black text-white transition hover:bg-orange-600"
                                        @click="editPost(post)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        تعديل
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-if="posts.data.length === 0"
                        class="rounded-3xl border border-dashed border-slate-300 p-10 text-center text-slate-500"
                    >
                        لا توجد أخبار أو إعلانات بعد. أضف أول محتوى من النموذج
                        بالأعلى.
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
