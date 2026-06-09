<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { KeyRound, LockKeyhole, ShieldCheck } from 'lucide-vue-next';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: {
        title: 'تأكيد كلمة المرور',
        description:
            'هذه خطوة حماية إضافية قبل تنفيذ إجراء حساس داخل المنظومة.',
    },
});

const localizedError = (message?: string) => {
    if (!message) {
        return undefined;
    }

    const translations: Record<string, string> = {
        'The password field is required.': 'كلمة المرور مطلوبة للمتابعة.',
        'The provided password was incorrect.': 'كلمة المرور غير صحيحة.',
        'This password does not match our records.': 'كلمة المرور غير مطابقة لسجلاتنا.',
    };

    return translations[message] ?? message;
};
</script>
<template>
    <Head title="تأكيد كلمة المرور" />

    <section
        class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xl"
        dir="rtl"
    >
        <div class="bg-blue-950 px-6 py-6 text-white">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-orange-300 ring-1 ring-white/15"
                >
                    <ShieldCheck class="h-6 w-6" />
                </div>
                <div class="min-w-0 text-start">
                    <p class="text-xs font-extrabold text-orange-300">
                        مساحة آمنة
                    </p>
                    <h1 class="mt-1 text-2xl font-extrabold">
                        تأكيد الهوية
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-blue-100">
                        أدخل كلمة المرور الحالية أو استخدم مفتاح المرور حتى نسمح بمتابعة الإجراء.
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6 px-6 py-6">
            <div
                class="flex items-start gap-3 rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-start"
            >
                <LockKeyhole class="mt-0.5 h-5 w-5 shrink-0 text-orange-600" />
                <p class="text-sm leading-7 text-orange-900">
                    لن يتم تغيير كلمة المرور هنا. هذه الصفحة فقط تؤكد أنك صاحب الحساب قبل الوصول إلى إعدادات أو عمليات حساسة.
                </p>
            </div>

            <div class="rounded-xl border border-gray-100 bg-slate-50 p-4">
                <PasskeyVerify
                    :routes="{
                        options: confirmOptions(),
                        submit: confirmStore(),
                    }"
                    label="تأكيد باستخدام مفتاح المرور"
                    loading-label="جارٍ التأكيد..."
                    separator="أو أكد باستخدام كلمة المرور"
                />

                <Form
                    v-bind="store.form()"
                    reset-on-success
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-5">
                        <div class="grid gap-2 text-start">
                            <Label for="password" class="font-bold text-gray-800">
                                كلمة المرور الحالية
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                class="mt-1 block h-12 w-full rounded-xl border-gray-200 bg-white text-start shadow-sm focus:border-blue-700 focus:ring-blue-700/20"
                                required
                                autocomplete="current-password"
                                autofocus
                                placeholder="أدخل كلمة المرور"
                            />
                            <InputError :message="localizedError(errors.password)" />
                        </div>

                        <Button
                            class="h-12 w-full rounded-xl bg-orange-500 text-base font-extrabold text-white shadow-lg shadow-orange-500/20 hover:bg-orange-600"
                            :disabled="processing"
                            data-test="confirm-password-button"
                        >
                            <Spinner v-if="processing" />
                            <KeyRound v-else class="h-5 w-5" />
                            {{ processing ? 'جارٍ التحقق...' : 'تأكيد والمتابعة' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </section>
</template>
