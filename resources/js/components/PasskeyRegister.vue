<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const emit = defineEmits<{
    success: [];
}>();

const getDefaultPasskeyName = () => {
    const ua = navigator.userAgent;

    const browser = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'].find(
        (browser) => new RegExp(browser).test(ua),
    );

    const os = ['iPhone', 'iPad', 'Android', 'Mac', 'Windows'].find((os) =>
        new RegExp(os).test(ua),
    );

    return [browser, os].filter(Boolean).join(' على ') || '';
};

const name = ref(getDefaultPasskeyName());
const showForm = ref(false);

const { register, isLoading, error, isSupported } = usePasskeyRegister({
    onSuccess: () => {
        name.value = '';
        showForm.value = false;
        emit('success');
    },
});

const handleSubmit = async (event: Event) => {
    event.preventDefault();

    if (!name.value.trim()) {
        return;
    }

    await register(name.value);
};

const handleCancel = () => {
    showForm.value = false;
    name.value = '';
};
</script>

<template>
    <div v-if="!isSupported" class="text-sm font-bold text-slate-500">
        مفاتيح المرور غير مدعومة في هذا المتصفح.
    </div>

    <Button
        v-else-if="!showForm"
        variant="outline"
        class="h-11 gap-2 rounded-xl border-blue-200 bg-blue-50 font-black text-blue-900 hover:bg-blue-100"
        @click="showForm = true"
    >
        <Plus class="h-4 w-4" />
        إضافة مفتاح مرور
    </Button>

    <form
        v-else
        @submit="handleSubmit"
        class="w-full space-y-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 sm:max-w-md"
        dir="rtl"
    >
        <div class="grid gap-2">
            <Label for="passkey-name" class="font-black text-slate-800">اسم مفتاح المرور</Label>
            <Input
                id="passkey-name"
                v-model="name"
                type="text"
                placeholder="مثال: جهازي الشخصي، iPhone"
                class="mt-1 block h-11 w-full rounded-xl border-slate-300 bg-white text-right"
                autofocus
            />
            <p class="text-xs font-bold text-slate-500">
                الاسم يساعدك على معرفة الجهاز لاحقاً.
            </p>
        </div>

        <InputError v-if="error" :message="error" />

        <div class="flex gap-2">
            <Button type="submit" :disabled="isLoading || !name.trim()" class="rounded-xl font-black">
                {{ isLoading ? 'جار التسجيل...' : 'تسجيل المفتاح' }}
            </Button>
            <Button type="button" variant="ghost" class="rounded-xl font-black" @click="handleCancel">
                إلغاء
            </Button>
        </div>
    </form>
</template>
