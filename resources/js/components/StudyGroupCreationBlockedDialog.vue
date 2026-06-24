<script setup lang="ts">
import { AlertTriangle, CalendarRange, Lock } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    open: boolean;
    availability?: {
        is_open: boolean;
        message?: string;
        days_remaining?: number | null;
        semester?: {
            code?: string | null;
            registration_start?: string | null;
            registration_end?: string | null;
        } | null;
    } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="border-amber-200 bg-white sm:max-w-xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-amber-900">
                    <Lock class="h-5 w-5" />
                    إنشاء مجموعة جديدة غير متاح حالياً
                </DialogTitle>
                <DialogDescription
                    class="text-right text-sm leading-7 text-slate-600"
                >
                    {{
                        availability?.message ??
                        'تعذر فتح إنشاء مجموعة جديدة لأن شروط الفصل والتسجيل غير متحققة حالياً.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <div class="flex items-start gap-3">
                    <AlertTriangle class="mt-0.5 h-5 w-5 text-amber-700" />
                    <div class="space-y-2 text-sm font-bold text-amber-900">
                        <p>
                            يشترط النظام وجود فصل نشط وأن تكون نافذة التسجيل
                            مفتوحة مع بقاء 3 أيام على الأقل قبل الإغلاق.
                        </p>
                        <p v-if="availability?.semester?.code">
                            الفصل المرتبط: {{ availability.semester.code }}
                        </p>
                        <p
                            v-if="
                                availability?.days_remaining !== null &&
                                availability?.days_remaining !== undefined
                            "
                        >
                            الأيام المتبقية حتى إغلاق التسجيل:
                            {{ availability.days_remaining }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="availability?.semester"
                class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:grid-cols-3"
            >
                <div>
                    <p class="text-xs font-bold text-slate-500">الفصل</p>
                    <p class="mt-1 font-extrabold text-slate-900">
                        {{ availability.semester.code ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500">
                        بداية التسجيل
                    </p>
                    <p class="mt-1 font-extrabold text-slate-900">
                        {{ availability.semester.registration_start ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500">
                        نهاية التسجيل
                    </p>
                    <p class="mt-1 font-extrabold text-slate-900">
                        {{ availability.semester.registration_end ?? '-' }}
                    </p>
                </div>
            </div>

            <DialogFooter class="sm:justify-start">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-bold text-white hover:bg-blue-950"
                    @click="emit('update:open', false)"
                >
                    <CalendarRange class="h-4 w-4" />
                    فهمت
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
