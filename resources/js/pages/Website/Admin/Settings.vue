<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ImagePlus, UploadCloud, X } from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: [] });

const props = defineProps<{
    settings: Record<string, string | null>;
}>();

const heroImageInput = ref<HTMLInputElement | null>(null);
const heroImagePreview = ref<string | null>(
    props.settings.hero_image_url || null,
);

const form = useForm({
    _method: 'put',
    site_name: props.settings.site_name || '',
    site_description: props.settings.site_description || '',
    contact_email: props.settings.contact_email || '',
    contact_phone: props.settings.contact_phone || '',
    contact_address: props.settings.contact_address || '',
    hero_title: props.settings.hero_title || '',
    hero_subtitle: props.settings.hero_subtitle || '',
    hero_image: null as File | null,
});

const onHeroImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.hero_image = file;
    heroImagePreview.value = file
        ? URL.createObjectURL(file)
        : props.settings.hero_image_url || null;
};

const clearSelectedHeroImage = () => {
    form.hero_image = null;
    heroImagePreview.value = props.settings.hero_image_url || null;

    if (heroImageInput.value) {
        heroImageInput.value.value = '';
    }
};

const submit = () => {
    form.post('/admin/website/settings', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.hero_image = null;

            if (heroImageInput.value) {
                heroImageInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <Head title="إعدادات الموقع" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <div class="mx-auto max-w-5xl">
                <section class="overflow-hidden rounded-2xl bg-white shadow">
                    <div class="bg-blue-950 px-6 py-7 text-white">
                        <p class="text-sm font-bold text-orange-300">
                            إعدادات واجهة الموقع
                        </p>
                        <h1 class="mt-2 text-3xl font-extrabold">
                            إعدادات الموقع
                        </h1>
                        <p class="mt-2 max-w-3xl leading-7 text-blue-100">
                            حدّث بيانات التواصل ومحتوى الهيرو الافتراضي، وارفع
                            صورة الهيرو مباشرة من جهازك.
                        </p>
                    </div>

                    <form
                        class="grid gap-6 p-6 lg:grid-cols-[1fr_360px]"
                        @submit.prevent="submit"
                    >
                        <section class="grid gap-4">
                            <input
                                v-model="form.site_name"
                                placeholder="اسم الموقع"
                                class="rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <textarea
                                v-model="form.site_description"
                                placeholder="وصف الموقع"
                                class="min-h-24 rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <div class="grid gap-4 md:grid-cols-2">
                                <input
                                    v-model="form.contact_email"
                                    type="email"
                                    placeholder="بريد التواصل"
                                    class="rounded-xl border border-slate-300 px-4 py-3"
                                />
                                <input
                                    v-model="form.contact_phone"
                                    placeholder="هاتف التواصل"
                                    class="rounded-xl border border-slate-300 px-4 py-3"
                                />
                            </div>
                            <textarea
                                v-model="form.contact_address"
                                placeholder="العنوان"
                                class="rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <input
                                v-model="form.hero_title"
                                placeholder="عنوان الهيرو الافتراضي"
                                class="rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <textarea
                                v-model="form.hero_subtitle"
                                placeholder="وصف الهيرو الافتراضي"
                                class="min-h-28 rounded-xl border border-slate-300 px-4 py-3"
                            />
                            <button
                                class="rounded-xl bg-blue-900 px-5 py-3 font-extrabold text-white transition hover:bg-orange-500"
                                :disabled="form.processing"
                            >
                                حفظ الإعدادات
                            </button>
                        </section>

                        <aside
                            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5"
                        >
                            <input
                                ref="heroImageInput"
                                type="file"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="hidden"
                                @change="onHeroImageChange"
                            />
                            <div
                                class="flex aspect-video items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm"
                            >
                                <img
                                    v-if="heroImagePreview"
                                    :src="heroImagePreview"
                                    alt="معاينة صورة الهيرو"
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
                                @click="heroImageInput?.click()"
                            >
                                <UploadCloud class="h-5 w-5" />
                                اختيار صورة الهيرو
                            </button>
                            <button
                                v-if="form.hero_image"
                                type="button"
                                class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700"
                                @click="clearSelectedHeroImage"
                            >
                                <X class="h-4 w-4" />
                                إزالة الصورة المختارة
                            </button>
                            <p
                                class="mt-3 text-center text-xs leading-6 text-slate-500"
                            >
                                تظهر هذه الصورة في الهيرو عند عدم وجود صورة خبر
                                منشور. الحد الأقصى 4MB.
                            </p>
                            <p
                                v-if="form.errors.hero_image"
                                class="mt-2 text-center text-sm font-bold text-red-600"
                            >
                                {{ form.errors.hero_image }}
                            </p>
                        </aside>
                    </form>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
