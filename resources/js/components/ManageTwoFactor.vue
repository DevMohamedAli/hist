<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ShieldCheck, ShieldOff } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <section
        v-if="canManageTwoFactor"
        class="space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"
        dir="rtl"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <div
                    :class="[
                        'rounded-2xl p-3',
                        twoFactorEnabled ? 'bg-emerald-50 text-emerald-700' : 'bg-orange-50 text-orange-600',
                    ]"
                >
                    <ShieldCheck v-if="twoFactorEnabled" class="h-6 w-6" />
                    <ShieldOff v-else class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-950">التحقق الثنائي</h2>
                    <p class="mt-1 text-sm font-bold leading-6 text-slate-500">
                        أضف رمز تحقق من تطبيق المصادقة لحماية الدخول إلى حسابك.
                    </p>
                </div>
            </div>

            <span
                :class="[
                    'rounded-full px-4 py-2 text-xs font-black',
                    twoFactorEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600',
                ]"
            >
                {{ twoFactorEnabled ? 'مفعّل' : 'غير مفعّل' }}
            </span>
        </div>

        <div v-if="!twoFactorEnabled" class="rounded-2xl border border-orange-100 bg-orange-50 p-5">
            <p class="text-sm font-bold leading-7 text-orange-950">
                عند التفعيل سيطلب النظام رمزاً مؤقتاً من تطبيق المصادقة عند تسجيل الدخول. هذه خطوة صغيرة لكنها ترفع أمان الحساب كثيراً.
            </p>

            <div class="mt-4">
                <Button
                    v-if="hasSetupData"
                    class="h-11 gap-2 rounded-xl bg-blue-600 px-6 font-black text-white hover:bg-blue-700"
                    @click="showSetupModal = true"
                >
                    <ShieldCheck class="h-4 w-4" />
                    متابعة الإعداد
                </Button>
                <Form
                    v-else
                    v-bind="enable.form()"
                    @success="showSetupModal = true"
                    #default="{ processing }"
                >
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="h-11 gap-2 rounded-xl bg-blue-600 px-6 font-black text-white hover:bg-blue-700"
                    >
                        <ShieldCheck class="h-4 w-4" />
                        {{ processing ? 'جار التفعيل...' : 'تفعيل التحقق الثنائي' }}
                    </Button>
                </Form>
            </div>
        </div>

        <div v-else class="space-y-5">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                <p class="text-sm font-bold leading-7 text-emerald-950">
                    التحقق الثنائي مفعّل. سيُطلب رمز أمان من تطبيق المصادقة عند تسجيل الدخول.
                </p>

                <Form v-bind="disable.form()" #default="{ processing }" class="mt-4">
                    <Button
                        variant="destructive"
                        type="submit"
                        :disabled="processing"
                        class="h-11 rounded-xl px-6 font-black"
                    >
                        {{ processing ? 'جار التعطيل...' : 'تعطيل التحقق الثنائي' }}
                    </Button>
                </Form>
            </div>

            <TwoFactorRecoveryCodes />
        </div>

        <TwoFactorSetupModal
            v-model:isOpen="showSetupModal"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
    </section>
</template>
