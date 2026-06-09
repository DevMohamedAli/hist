<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { KeyRound } from 'lucide-vue-next';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
import PasskeyItem from '@/components/PasskeyItem.vue';
import PasskeyRegister from '@/components/PasskeyRegister.vue';
import type { Passkey } from '@/types/auth';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

withDefaults(defineProps<Props>(), {
    canManagePasskeys: false,
    passkeys: () => [],
});

const handleDelete = (id: number, onError: () => void) => {
    router.delete(destroy.url(id), {
        preserveScroll: true,
        onError,
    });
};

const handleRegisterSuccess = () => {
    router.reload();
};
</script>

<template>
    <section
        v-if="canManagePasskeys"
        class="space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"
        dir="rtl"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="rounded-2xl bg-blue-50 p-3 text-blue-700">
                    <KeyRound class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-950">مفاتيح المرور</h2>
                    <p class="mt-1 text-sm font-bold leading-6 text-slate-500">
                        سجّل الدخول ببصمة الجهاز أو رمز النظام بدل كلمة المرور.
                    </p>
                </div>
            </div>
            <PasskeyRegister @success="handleRegisterSuccess" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <template v-if="passkeys.length">
                <PasskeyItem
                    v-for="passkey in passkeys"
                    :key="passkey.id"
                    :passkey="passkey"
                    @remove="handleDelete"
                />
            </template>

            <div v-else class="p-8 text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100"
                >
                    <KeyRound class="h-8 w-8 text-slate-500" />
                </div>
                <p class="font-black text-slate-950">لا توجد مفاتيح مرور بعد</p>
                <p class="mt-1 text-sm font-bold text-slate-500">
                    أضف مفتاح مرور لتسجيل الدخول بسرعة وبدون كلمة مرور.
                </p>
            </div>
        </div>
    </section>
</template>
