<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { KeyRound, LockKeyhole, Save, ShieldCheck, Sparkles } from 'lucide-vue-next';
import SecurityController from '@/actions/Modules/Auth/Http/Controllers/SecurityController';
import InputError from '@/components/InputError.vue';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'إعدادات الأمان',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="إعدادات الأمان" />

    <main class="space-y-6" dir="rtl">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-blue-950 px-6 py-8 text-white md:px-8">
                <p class="flex items-center gap-2 text-sm font-black text-orange-300">
                    <Sparkles class="h-4 w-4" />
                    مركز الحماية
                </p>
                <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="text-3xl font-black tracking-normal md:text-4xl">
                            أمان الحساب
                        </h1>
                        <p class="mt-3 text-sm leading-7 text-blue-100 md:text-base">
                            حدّث كلمة المرور، فعّل التحقق الثنائي، وأدر مفاتيح الدخول بدون كلمة مرور من مكان واحد.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <ShieldCheck class="h-7 w-7 text-emerald-300" />
                            <p class="mt-2 text-xs font-bold text-blue-100">طبقة حماية إضافية</p>
                            <p class="font-black">تحقق ثنائي</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <KeyRound class="h-7 w-7 text-orange-300" />
                            <p class="mt-2 text-xs font-bold text-blue-100">دخول أسرع</p>
                            <p class="font-black">مفاتيح مرور</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="rounded-2xl bg-blue-50 p-3 text-blue-700">
                                <LockKeyhole class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-950">تحديث كلمة المرور</h2>
                                <p class="text-sm font-bold text-slate-500">
                                    استخدم كلمة مرور قوية وغير مستخدمة في أي نظام آخر.
                                </p>
                            </div>
                        </div>

                        <Form
                            v-bind="SecurityController.update.form()"
                            :options="{
                                preserveScroll: true,
                            }"
                            reset-on-success
                            :reset-on-error="[
                                'password',
                                'password_confirmation',
                                'current_password',
                            ]"
                            class="space-y-5"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-2">
                                <Label for="current_password" class="font-black text-slate-800">
                                    كلمة المرور الحالية
                                </Label>
                                <PasswordInput
                                    id="current_password"
                                    name="current_password"
                                    class="mt-1 block h-12 w-full rounded-xl border-slate-300 text-right"
                                    autocomplete="current-password"
                                    placeholder="أدخل كلمة المرور الحالية"
                                />
                                <InputError :message="errors.current_password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password" class="font-black text-slate-800">
                                    كلمة المرور الجديدة
                                </Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    class="mt-1 block h-12 w-full rounded-xl border-slate-300 text-right"
                                    autocomplete="new-password"
                                    placeholder="اكتب كلمة مرور قوية"
                                    :passwordrules="props.passwordRules"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation" class="font-black text-slate-800">
                                    تأكيد كلمة المرور
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="mt-1 block h-12 w-full rounded-xl border-slate-300 text-right"
                                    autocomplete="new-password"
                                    placeholder="أعد كتابة كلمة المرور الجديدة"
                                    :passwordrules="props.passwordRules"
                                />
                                <InputError :message="errors.password_confirmation" />
                            </div>

                            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center">
                                <Button
                                    :disabled="processing"
                                    data-test="update-password-button"
                                    class="h-12 gap-2 rounded-xl bg-orange-500 px-8 text-base font-black text-white shadow-lg shadow-orange-500/20 hover:bg-orange-600"
                                >
                                    <Save class="h-5 w-5" />
                                    {{ processing ? 'جار الحفظ...' : 'حفظ كلمة المرور' }}
                                </Button>
                                <p class="text-xs font-semibold text-slate-500">
                                    سيتم تسجيل الخروج من الأجهزة غير الموثوقة عند الحاجة.
                                </p>
                            </div>
                        </Form>
                    </section>

                    <aside class="rounded-3xl border border-blue-100 bg-blue-50 p-5">
                        <ShieldCheck class="h-8 w-8 text-blue-700" />
                        <h3 class="mt-4 text-lg font-black text-blue-950">نصيحة سريعة</h3>
                        <p class="mt-2 text-sm font-bold leading-7 text-blue-900/70">
                            اجعل كلمة المرور طويلة، وفعّل التحقق الثنائي إذا كان متاحاً. الحسابات الإدارية تحتاج حماية زيادة، خصوصاً داخل منظومة أكاديمية.
                        </p>
                    </aside>
                </div>
            </div>
        </section>

        <ManageTwoFactor
            :canManageTwoFactor="canManageTwoFactor"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
        <ManagePasskeys
            :canManagePasskeys="canManagePasskeys"
            :passkeys="passkeys"
        />
    </main>
</template>
