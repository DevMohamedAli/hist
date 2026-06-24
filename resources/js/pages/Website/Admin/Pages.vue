<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Bold,
    Eraser,
    Heading2,
    ImagePlus,
    Italic,
    Link2,
    List,
    ListOrdered,
    Pilcrow,
    UploadCloud,
    X,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { computed, nextTick, onMounted, ref } from 'vue';

defineOptions({ layout: [] });

type WebsitePage = {
    id: number;
    title: string;
    summary?: string;
    content?: string;
    status: string;
    featured_image_path?: string;
    featured_image_url?: string;
    seo_title?: string;
    seo_description?: string;
    published_at?: string;
};

const props = defineProps<{
    page: WebsitePage | null;
    canPublish: boolean;
}>();

const defaultImage = '/assets/img/website-news-hero.svg';
const defaultContent =
    '<p>اكتب هنا نبذة واضحة عن المعهد، رسالته، تخصصاته، والخدمات التي يقدمها للطلاب والزوار.</p>';

const editingId = ref<number | null>(props.page?.id ?? null);
const editorRef = ref<HTMLElement | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
const savedImagePreview = ref<string | null>(
    props.page?.featured_image_url || props.page?.featured_image_path || defaultImage,
);
const imagePreview = ref<string | null>(savedImagePreview.value);

const normalizeDateTime = (value?: string) => {
    if (!value) {
        return '';
    }

    return value.slice(0, 16);
};

const form = useForm({
    _method: '',
    title: props.page?.title || 'عن المعهد',
    summary: props.page?.summary || '',
    content: props.page?.content || defaultContent,
    status: props.page?.status || 'draft',
    featured_image: null as File | null,
    seo_title: props.page?.seo_title || 'عن المعهد',
    seo_description: props.page?.seo_description || '',
    published_at: normalizeDateTime(props.page?.published_at),
});

const cannotSubmitPublished = computed(() => !props.canPublish && form.status === 'published');

const setEditorContent = async (content: string) => {
    await nextTick();

    if (editorRef.value && editorRef.value.innerHTML !== content) {
        editorRef.value.innerHTML = content;
    }
};

const syncEditorContent = () => {
    form.content = editorRef.value?.innerHTML || '';
};

const runEditorCommand = (command: string, value?: string) => {
    editorRef.value?.focus();
    document.execCommand(command, false, value);
    syncEditorContent();
};

const insertLink = () => {
    const url = window.prompt('أدخل رابط الصفحة');

    if (!url) {
        return;
    }

    runEditorCommand('createLink', url);
};

const resetImageInput = () => {
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.featured_image = file;
    imagePreview.value = file ? URL.createObjectURL(file) : savedImagePreview.value;
};

const clearSelectedImage = () => {
    form.featured_image = null;
    imagePreview.value = savedImagePreview.value;
    resetImageInput();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.content = props.page?.content || defaultContent;
    savedImagePreview.value = props.page?.featured_image_url || props.page?.featured_image_path || defaultImage;
    imagePreview.value = savedImagePreview.value;
    resetImageInput();
    void setEditorContent(form.content);
};

const submit = () => {
    syncEditorContent();

    if (cannotSubmitPublished.value) {
        form.setError('status', 'لا تملك صلاحية النشر. اختر مسودة أو اطلب صلاحية النشر.');

        return;
    }

    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.featured_image = null;
            resetImageInput();
        },
    };

    if (editingId.value) {
        form._method = 'put';
        form.post(`/admin/website/pages/${editingId.value}`, options);

        return;
    }

    form._method = '';
    form.post('/admin/website/pages', {
        ...options,
        onSuccess: () => {
            form.featured_image = null;
            resetImageInput();
        },
    });
};

onMounted(() => {
    void setEditorContent(form.content);
});
</script>

<template>
    <Head title="إدارة صفحة عن المعهد" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-slate-50 p-6" dir="rtl">
            <div class="mx-auto max-w-7xl space-y-6">
                <section class="overflow-hidden rounded-3xl bg-blue-950 text-white shadow-xl">
                    <div class="grid gap-6 p-7 lg:grid-cols-[1fr_340px] lg:items-center">
                        <div>
                            <p class="text-sm font-extrabold text-orange-300">إدارة واجهة الموقع</p>
                            <h1 class="mt-2 text-3xl font-black md:text-4xl">
                                صفحة عن المعهد
                            </h1>
                            <p class="mt-3 max-w-3xl leading-8 text-blue-100">
                                هذه الصفحة مخصصة فقط لتحديث محتوى "عن المعهد" الذي يظهر للزوار في رابط
                                <span dir="ltr" class="font-bold text-white">/pages/about</span>.
                            </p>
                        </div>

                        <div class="rounded-3xl bg-white/10 p-5 ring-1 ring-white/15">
                            <p class="text-sm font-bold text-blue-100">حالة الصفحة</p>
                            <p class="mt-2 text-2xl font-black">
                                {{
                                    form.status === 'published'
                                        ? 'منشورة'
                                        : form.status === 'draft'
                                          ? 'مسودة'
                                          : 'قيد الإعداد'
                                }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-blue-100">
                                عند النشر ستظهر التعديلات مباشرة للزوار.
                            </p>
                        </div>
                    </div>
                </section>

                <form class="grid gap-6 lg:grid-cols-[1fr_380px]" @submit.prevent="submit">
                    <section class="space-y-6">
                        <div class="rounded-3xl bg-white p-6 shadow">
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="grid gap-2">
                                    <span class="text-sm font-extrabold text-slate-700">عنوان الصفحة</span>
                                    <input
                                        v-model="form.title"
                                        required
                                        class="rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    />
                                    <span v-if="form.errors.title" class="text-xs font-bold text-red-600">
                                        {{ form.errors.title }}
                                    </span>
                                </label>

                                <label class="grid gap-2">
                                    <span class="text-sm font-extrabold text-slate-700">حالة النشر</span>
                                    <select
                                        v-model="form.status"
                                        class="rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="draft">مسودة</option>
                                        <option value="under_review">قيد المراجعة</option>
                                        <option value="scheduled">مجدول</option>
                                        <option value="published" :disabled="!canPublish">
                                            منشور
                                        </option>
                                        <option value="archived">مؤرشف</option>
                                    </select>
                                    <span v-if="form.errors.status" class="text-xs font-bold text-red-600">
                                        {{ form.errors.status }}
                                    </span>
                                    <span v-else-if="!canPublish" class="text-xs font-bold text-orange-600">
                                        لا تظهر لك صلاحية النشر حالياً؛ يمكنك الحفظ كمسودة.
                                    </span>
                                </label>
                            </div>

                            <label class="mt-4 grid gap-2">
                                <span class="text-sm font-extrabold text-slate-700">الملخص</span>
                                <textarea
                                    v-model="form.summary"
                                    rows="3"
                                    placeholder="ملخص قصير يظهر أعلى صفحة عن المعهد."
                                    class="rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                />
                                <span v-if="form.errors.summary" class="text-xs font-bold text-red-600">
                                    {{ form.errors.summary }}
                                </span>
                            </label>
                        </div>

                        <div class="overflow-hidden rounded-3xl bg-white shadow">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-2xl font-black text-blue-950">محرر المحتوى</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    استخدم الأدوات لتنسيق النص، ثم شاهد النتيجة كما ستظهر للزوار.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 border-b border-slate-100 bg-slate-50 p-3">
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="عنوان"
                                    @click="runEditorCommand('formatBlock', 'h2')"
                                >
                                    <Heading2 class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="فقرة"
                                    @click="runEditorCommand('formatBlock', 'p')"
                                >
                                    <Pilcrow class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="عريض"
                                    @click="runEditorCommand('bold')"
                                >
                                    <Bold class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="مائل"
                                    @click="runEditorCommand('italic')"
                                >
                                    <Italic class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="قائمة نقطية"
                                    @click="runEditorCommand('insertUnorderedList')"
                                >
                                    <List class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="قائمة مرقمة"
                                    @click="runEditorCommand('insertOrderedList')"
                                >
                                    <ListOrdered class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="رابط"
                                    @click="insertLink"
                                >
                                    <Link2 class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-orange-50 hover:text-orange-600"
                                    title="إزالة التنسيق"
                                    @click="runEditorCommand('removeFormat')"
                                >
                                    <Eraser class="h-5 w-5" />
                                </button>
                            </div>

                            <div
                                ref="editorRef"
                                contenteditable="true"
                                class="prose prose-slate min-h-80 max-w-none p-6 leading-8 outline-none focus:bg-orange-50/30"
                                @input="syncEditorContent"
                            />
                            <p v-if="form.errors.content" class="px-6 pb-5 text-sm font-bold text-red-600">
                                {{ form.errors.content }}
                            </p>
                        </div>
                    </section>

                    <aside class="space-y-6">
                        <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-5 shadow">
                            <input
                                ref="imageInput"
                                type="file"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="hidden"
                                @change="onImageChange"
                            />
                            <div class="flex aspect-[16/10] items-center justify-center overflow-hidden rounded-3xl bg-slate-100">
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="معاينة صورة صفحة عن المعهد"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="text-center text-slate-400">
                                    <ImagePlus class="mx-auto h-14 w-14" />
                                    <p class="mt-3 text-sm font-extrabold">لم يتم اختيار صورة</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-500 px-4 py-3 font-black text-white transition hover:bg-orange-600"
                                @click="imageInput?.click()"
                            >
                                <UploadCloud class="h-5 w-5" />
                                رفع صورة الصفحة
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
                            <p class="mt-3 text-center text-xs leading-6 text-slate-500">
                                الصيغ المدعومة: JPG, PNG, WEBP — الحد الأقصى 4MB.
                            </p>
                            <p v-if="form.errors.featured_image" class="mt-2 text-center text-sm font-bold text-red-600">
                                {{ form.errors.featured_image }}
                            </p>
                        </section>

                        <section class="rounded-3xl bg-white p-5 shadow">
                            <h2 class="text-xl font-black text-blue-950">إعدادات الظهور</h2>
                            <label class="mt-4 grid gap-2">
                                <span class="text-sm font-extrabold text-slate-700">تاريخ النشر</span>
                                <input
                                    v-model="form.published_at"
                                    type="datetime-local"
                                    class="rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                />
                            </label>
                            <label class="mt-4 grid gap-2">
                                <span class="text-sm font-extrabold text-slate-700">عنوان SEO</span>
                                <input
                                    v-model="form.seo_title"
                                    class="rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                />
                            </label>
                            <label class="mt-4 grid gap-2">
                                <span class="text-sm font-extrabold text-slate-700">وصف SEO</span>
                                <textarea
                                    v-model="form.seo_description"
                                    rows="3"
                                    class="rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                />
                            </label>
                        </section>

                        <div class="sticky top-6 space-y-3 rounded-3xl bg-blue-950 p-5 text-white shadow-xl">
                            <button
                                class="w-full rounded-2xl bg-orange-500 px-6 py-3 font-black transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="form.processing || cannotSubmitPublished"
                            >
                                حفظ صفحة عن المعهد
                            </button>
                            <button
                                type="button"
                                class="w-full rounded-2xl border border-white/20 px-6 py-3 font-bold text-blue-100 transition hover:bg-white/10"
                                @click="resetForm"
                            >
                                إعادة ضبط النموذج
                            </button>
                            <p class="text-center text-xs leading-6 text-blue-100">
                                الرابط ثابت ولا يتغير:
                                <span dir="ltr" class="font-bold text-white">/pages/about</span>
                            </p>
                        </div>
                    </aside>
                </form>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
