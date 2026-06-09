<script setup lang="ts">
import { KeyRound, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Passkey } from '@/types/auth';

const props = defineProps<{
    passkey: Passkey;
}>();

const emit = defineEmits<{
    remove: [id: number, onError: () => void];
}>();

const isDeleting = ref(false);

const handleDelete = () => {
    isDeleting.value = true;
    emit('remove', props.passkey.id, () => {
        isDeleting.value = false;
    });
};
</script>

<template>
    <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-4 last:border-b-0" dir="rtl">
        <div class="flex min-w-0 items-center gap-4">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50"
            >
                <KeyRound class="h-5 w-5 text-blue-700" />
            </div>
            <div class="min-w-0 space-y-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <p class="truncate font-black tracking-tight text-slate-950">{{ passkey.name }}</p>
                    <span
                        v-if="passkey.authenticator"
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-black text-slate-500 ring-1 ring-slate-200 ring-inset"
                    >
                        {{ passkey.authenticator }}
                    </span>
                </div>
                <p class="text-sm font-bold text-slate-500">
                    تمت الإضافة {{ passkey.created_at_diff }}
                    <template v-if="passkey.last_used_at_diff">
                        <span class="mx-1 text-slate-300">/</span>
                        آخر استخدام {{ passkey.last_used_at_diff }}
                    </template>
                </p>
            </div>
        </div>

        <Dialog>
            <DialogTrigger as-child>
                <Button
                    variant="ghost"
                    size="sm"
                    class="text-red-600 hover:bg-red-50 hover:text-red-700"
                >
                    <Trash2 class="h-4 w-4" />
                    <span class="sr-only">حذف</span>
                </Button>
            </DialogTrigger>

            <DialogContent dir="rtl">
                <DialogTitle>حذف مفتاح المرور</DialogTitle>
                <DialogDescription>
                    هل تريد حذف مفتاح المرور "{{ passkey.name }}"؟ لن تتمكن من استخدامه لتسجيل الدخول بعد الحذف.
                </DialogDescription>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">إلغاء</Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        :disabled="isDeleting"
                        @click="handleDelete"
                    >
                        {{ isDeleting ? 'جار الحذف...' : 'حذف المفتاح' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
