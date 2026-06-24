<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Camera,
    CheckCircle2,
    Mail,
    Save,
    ShieldCheck,
    Sparkles,
    Upload,
    UserRound,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'إعدادات الملف الشخصي',
                href: edit(),
            },
        ],
    },
});

interface AuthUser {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    email_verified_at?: string | null;
    roles?: Array<{ name: string }>;
}

const page = usePage();
const user = computed(() => page.props.auth.user as AuthUser);
const avatarPreview = ref<string | null>(user.value.avatar ?? null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    name: user.value.name,
    email: user.value.email,
    avatar: null as File | null,
});

const initials = computed(() => {
    return (form.name || user.value.name)
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('');
});

const primaryRole = computed(() => {
    const role = user.value.roles?.[0]?.name;

    const labels: Record<string, string> = {
        super_admin: 'مدير النظام',
        employee: 'موظف شؤون الطلبة',
        teacher: 'عضو هيئة التدريس',
        student: 'طالب',
    };

    return role ? (labels[role] ?? role) : 'حساب مستخدم';
});

const verificationLabel = computed(() =>
    user.value.email_verified_at ? 'موثق' : 'بانتظار التوثيق',
);

const onAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.avatar = file;

    if (!file) {
        avatarPreview.value = user.value.avatar ?? null;

        return;
    }

    avatarPreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post('/settings/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.avatar = null;

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <Head title="إعدادات الملف الشخصي" />

    <main class="space-y-6" dir="rtl">
        <section
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-100 bg-blue-950 px-6 py-7 text-white md:px-8"
            >
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="max-w-3xl">
                        <p
                            class="flex items-center gap-2 text-sm font-black text-orange-300"
                        >
                            <Sparkles class="h-4 w-4" />
                            مساحة حسابك
                        </p>
                        <h1
                            class="mt-3 text-3xl font-black tracking-normal md:text-4xl"
                        >
                            إعدادات الملف الشخصي
                        </h1>
                        <p
                            class="mt-3 text-sm leading-7 text-blue-100 md:text-base"
                        >
                            حدّث الاسم، البريد، والصورة الشخصية. هذه البيانات
                            تظهر في أعلى النظام، سجل النشاط، ولوحات التحكم.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:min-w-80">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-4"
                        >
                            <p class="text-xs font-bold text-blue-100">
                                نوع الحساب
                            </p>
                            <p class="mt-2 flex items-center gap-2 font-black">
                                <ShieldCheck class="h-4 w-4 text-orange-300" />
                                {{ primaryRole }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-4"
                        >
                            <p class="text-xs font-bold text-blue-100">
                                حالة البريد
                            </p>
                            <p class="mt-2 flex items-center gap-2 font-black">
                                <BadgeCheck class="h-4 w-4 text-emerald-300" />
                                {{ verificationLabel }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form
                class="grid lg:grid-cols-[minmax(0,1fr)_360px]"
                @submit.prevent="submit"
            >
                <section class="space-y-6 p-6 md:p-8">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <p
                                class="flex items-center gap-2 text-xs font-black text-slate-500"
                            >
                                <UserRound class="h-4 w-4 text-blue-700" />
                                الاسم الحالي
                            </p>
                            <p
                                class="mt-2 truncate text-xl font-black text-slate-950"
                            >
                                {{ form.name || 'بدون اسم' }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <p
                                class="flex items-center gap-2 text-xs font-black text-slate-500"
                            >
                                <Mail class="h-4 w-4 text-blue-700" />
                                البريد الحالي
                            </p>
                            <p
                                class="mt-2 truncate text-lg font-black text-slate-950"
                            >
                                {{ form.email }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid gap-5 rounded-2xl border border-slate-200 p-5"
                    >
                        <div class="space-y-2">
                            <Label
                                for="name"
                                class="text-sm font-black text-slate-800"
                            >
                                الاسم الكامل
                            </Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                name="name"
                                required
                                autocomplete="name"
                                placeholder="مثال: مدير النظام العام"
                                class="h-12 rounded-xl border-slate-300 bg-white text-right text-base"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="email"
                                class="text-sm font-black text-slate-800"
                            >
                                البريد الإلكتروني
                            </Label>
                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    name="email"
                                    required
                                    autocomplete="username"
                                    placeholder="name@example.com"
                                    class="h-12 rounded-xl border-slate-300 bg-white pl-12 text-right text-base"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div
                        v-if="
                            page.props.mustVerifyEmail &&
                            !user.email_verified_at
                        "
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                    >
                        <p class="font-black">
                            البريد الإلكتروني غير موثق بعد.
                        </p>
                        <Link
                            :href="send()"
                            as="button"
                            class="mt-2 font-black text-amber-950 underline underline-offset-4"
                        >
                            إرسال رابط التوثيق مرة أخرى
                        </Link>
                        <div
                            v-if="
                                page.props.status === 'verification-link-sent'
                            "
                            class="mt-3 flex items-center gap-2 font-bold text-emerald-700"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            تم إرسال رابط توثيق جديد إلى بريدك الإلكتروني.
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center"
                    >
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            data-test="update-profile-button"
                            class="h-12 gap-2 rounded-xl bg-orange-500 px-8 text-base font-black text-white shadow-lg shadow-orange-500/20 hover:bg-orange-600"
                        >
                            <Save class="h-5 w-5" />
                            {{
                                form.processing
                                    ? 'جار الحفظ...'
                                    : 'حفظ التغييرات'
                            }}
                        </Button>
                        <p class="text-xs font-semibold text-slate-500">
                            سيتم تحديث الصورة والبيانات فور الحفظ.
                        </p>
                    </div>
                </section>

                <aside
                    class="border-t border-slate-100 bg-slate-50 p-6 md:p-8 lg:border-t-0 lg:border-r"
                >
                    <div class="sticky top-6 space-y-6">
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm"
                        >
                            <div class="relative mx-auto h-44 w-44">
                                <div
                                    class="flex h-full w-full items-center justify-center overflow-hidden rounded-full border-4 border-white bg-blue-100 text-5xl font-black text-blue-900 shadow-lg ring-1 ring-slate-200"
                                >
                                    <img
                                        v-if="avatarPreview"
                                        :src="avatarPreview"
                                        :alt="form.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <span v-else>{{ initials }}</span>
                                </div>
                                <button
                                    type="button"
                                    class="absolute right-3 bottom-3 rounded-full bg-orange-500 p-3 text-white shadow-lg transition hover:bg-orange-600"
                                    @click="fileInput?.click()"
                                    aria-label="تغيير الصورة الشخصية"
                                >
                                    <Camera class="h-5 w-5" />
                                </button>
                            </div>

                            <input
                                ref="fileInput"
                                type="file"
                                name="avatar"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="hidden"
                                @change="onAvatarChange"
                            />

                            <h2 class="mt-5 text-xl font-black text-slate-950">
                                {{ form.name }}
                            </h2>
                            <p class="mt-1 text-sm font-bold text-slate-500">
                                {{ primaryRole }}
                            </p>

                            <Button
                                type="button"
                                variant="outline"
                                class="mt-5 h-11 gap-2 rounded-xl border-slate-300 bg-white font-black"
                                @click="fileInput?.click()"
                            >
                                <Upload class="h-4 w-4" />
                                اختيار صورة
                            </Button>

                            <InputError
                                class="mt-3 text-center"
                                :message="form.errors.avatar"
                            />
                            <p class="mt-3 text-xs leading-6 text-slate-500">
                                PNG أو JPG أو WEBP. الحد الأقصى 2MB.
                            </p>
                        </div>
                    </div>
                </aside>
            </form>
        </section>

        <section
            class="rounded-3xl border border-red-100 bg-white p-6 shadow-sm md:p-8"
        >
            <DeleteUser />
        </section>
    </main>
</template>
