<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ImagePlus, UploadCloud, X } from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';

defineOptions({ layout: [] });

type Banner = {
    id: number;
    title: string;
    subtitle?: string;
    image_path?: string;
    image_url?: string;
    link_url?: string;
    link_label?: string;
    is_active: boolean;
    sort_order: number;
    starts_at?: string;
    ends_at?: string;
};

defineProps<{
    banners: {
        data: Array<Banner>;
    };
}>();

const editingId = ref<number | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
const savedImagePreview = ref<string | null>(null);
const imagePreview = ref<string | null>(null);

const form = useForm({
    _method: '',
    title: '',
    subtitle: '',
    image: null as File | null,
    link_url: '',
    link_label: '',
    is_active: true,
    sort_order: 0,
    starts_at: '',
    ends_at: '',
});

const formTitle = computed(() =>
    editingId.value ? 'تعديل شريحة السلايدر' : 'إضافة شريحة جديدة',
);

const resetForm = () => {
    editingId.value = null;
    savedImagePreview.value = null;
    imagePreview.value = null;
    form.reset();
    form.clearErrors();

    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const editBanner = (banner: Banner) => {
    editingId.value = banner.id;
    savedImagePreview.value = banner.image_url || null;
    imagePreview.value = savedImagePreview.value;
    form.title = banner.title;
    form.subtitle = banner.subtitle || '';
    form.image = null;
    form.link_url = banner.link_url || '';
    form.link_label = banner.link_label || '';
    form.is_active = banner.is_active;
    form.sort_order = banner.sort_order;
    form.starts_at = banner.starts_at || '';
    form.ends_at = banner.ends_at || '';

    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.image = file;
    imagePreview.value = file
        ? URL.createObjectURL(file)
        : savedImagePreview.value;
};

const clearSelectedImage = () => {
    form.image = null;
    imagePreview.value = savedImagePreview.value;

    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const submit = () => {
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: resetForm,
    };

    if (editingId.value) {
        form._method = 'put';
        form.post(`/admin/website/banners/${editingId.value}`, options);

        return;
    }

    form._method = '';
    form.post('/admin/website/banners', options);
};
</script>

<template>
    <Head title="إدارة السلايدر والبنرات" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <div class="mx-auto max-w-6xl space-y-6">
                <section class="overflow-hidden rounded-2xl bg-white shadow">
                    <div class="bg-blue-950 px-6 py-7 text-white">
                        <p class="text-sm font-bold text-orange-300">
                            إدارة واجهة الموقع
                        </p>
                        <h1 class="mt-2 text-3xl font-extrabold">
                            السلايدر والبنرات
                        </h1>
                        <p class="mt-2 max-w-3xl leading-7 text-blue-100">
                            ارفع صورة الشريحة مباشرة من جهازك، وأضف العنوان
                            والرابط وترتيب الظهور في الصفحة الرئيسية.
                        </p>
                    </div>

                    <form
                        class="grid gap-6 p-6 lg:grid-cols-[1fr_360px]"
                        @submit.prevent="submit"
                    >
                        <section class="grid gap-4">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <h2
                                    class="text-xl font-extrabold text-blue-950"
                                >
                                    {{ formTitle }}
                                </h2>
                                <button
                                    v-if="editingId"
                                    type="button"
                                    class="rounded-full border px-4 py-2 text-sm font-bold"
                                    @click="resetForm"
                                >
                                    إلغاء التعديل
                                </button>
                            </div>

                            <input
                                v-model="form.title"
                                required
                                placeholder="عنوان الشريحة"
                                class="rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <textarea
                                v-model="form.subtitle"
                                placeholder="وصف مختصر يظهر فوق الصورة"
                                class="min-h-28 rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <div class="grid gap-4 md:grid-cols-3">
                                <input
                                    v-model="form.link_url"
                                    placeholder="رابط الزر"
                                    class="rounded-xl border border-slate-300 px-4 py-3"
                                />
                                <input
                                    v-model="form.link_label"
                                    placeholder="نص الزر"
                                    class="rounded-xl border border-slate-300 px-4 py-3"
                                />
                                <input
                                    v-model="form.sort_order"
                                    type="number"
                                    min="0"
                                    placeholder="ترتيب العرض"
                                    class="rounded-xl border border-slate-300 px-4 py-3"
                                />
                            </div>
                            <label
                                class="flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-3"
                            >
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                />
                                نشط ويظهر في الموقع
                            </label>
                            <button
                                class="rounded-xl bg-blue-900 px-5 py-3 font-extrabold text-white transition hover:bg-orange-500"
                                :disabled="form.processing"
                            >
                                {{ editingId ? 'حفظ التعديل' : 'حفظ الشريحة' }}
                            </button>
                        </section>

                        <aside
                            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5"
                        >
                            <input
                                ref="imageInput"
                                type="file"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="hidden"
                                @change="onImageChange"
                            />
                            <div
                                class="flex aspect-video items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm"
                            >
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="معاينة صورة الشريحة"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="text-center text-slate-400">
                                    <ImagePlus class="mx-auto h-12 w-12" />
                                    <p class="mt-2 text-sm font-bold">
                                        لم يتم اختيار صورة
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-3 font-extrabold text-white transition hover:bg-orange-600"
                                @click="imageInput?.click()"
                            >
                                <UploadCloud class="h-5 w-5" />
                                اختيار صورة
                            </button>
                            <button
                                v-if="form.image"
                                type="button"
                                class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700"
                                @click="clearSelectedImage"
                            >
                                <X class="h-4 w-4" />
                                إزالة الصورة المختارة
                            </button>
                            <p
                                class="mt-3 text-center text-xs leading-6 text-slate-500"
                            >
                                الصيغ المدعومة: JPG, PNG, WEBP — الحد الأقصى
                                4MB.
                            </p>
                            <p
                                v-if="form.errors.image"
                                class="mt-2 text-center text-sm font-bold text-red-600"
                            >
                                {{ form.errors.image }}
                            </p>
                        </aside>
                    </form>
                </section>

                <section class="rounded-2xl bg-white p-6 shadow">
                    <div class="grid gap-4 md:grid-cols-2">
                        <article
                            v-for="banner in banners.data"
                            :key="banner.id"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                        >
                            <img
                                v-if="banner.image_url"
                                :src="banner.image_url"
                                :alt="banner.title"
                                class="h-44 w-full object-cover"
                            />
                            <div class="p-5">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <h3
                                            class="font-extrabold text-blue-950"
                                        >
                                            {{ banner.title }}
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{
                                                banner.is_active
                                                    ? 'نشط'
                                                    : 'غير نشط'
                                            }}
                                            — ترتيب {{ banner.sort_order }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700"
                                        @click="editBanner(banner)"
                                    >
                                        تعديل
                                    </button>
                                </div>
                                <p
                                    v-if="banner.subtitle"
                                    class="mt-3 text-sm leading-6 text-slate-600"
                                >
                                    {{ banner.subtitle }}
                                </p>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
