<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Edit3, Plus } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Qualification {
    id: number;
    degree_name: string;
    institution: string;
}

const props = defineProps<{
    open: boolean;
    item: Qualification | null;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    degree_name: '',
    institution: '',
});

const isEditing = computed(() => Boolean(props.item?.id));
const modalTitle = computed(() =>
    isEditing.value ? 'تعديل المؤهل' : 'إضافة مؤهل جديد',
);
const modalDescription = computed(() =>
    isEditing.value
        ? 'حدّث بيانات المؤهل في جدول المؤهلات العام.'
        : 'أدخل بيانات المؤهل ليصبح متاحاً للاستخدام في النظام.',
);
const modalIcon = computed(() => (isEditing.value ? Edit3 : Plus));
const submitButtonText = computed(() =>
    isEditing.value ? 'تحديث المؤهل' : 'حفظ المؤهل',
);

watch(
    () => props.item,
    (newItem) => {
        if (newItem) {
            form.degree_name = newItem.degree_name;
            form.institution = newItem.institution;
            form.clearErrors();

            return;
        }

        form.reset();
        form.clearErrors();
    },
    { immediate: true },
);

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('success');
            emit('close');
        },
    };

    if (isEditing.value && props.item) {
        form.patch(`/qualifications/${props.item.id}`, options);

        return;
    }

    form.post('/qualifications', options);
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="(value) => !value && closeModal()">
        <DialogContent
            class="max-h-[92vh] overflow-y-auto p-0 sm:max-w-2xl"
            dir="rtl"
        >
            <DialogHeader class="border-b bg-slate-50 px-6 py-5 text-right">
                <DialogTitle
                    class="flex items-center gap-2 text-xl font-bold text-slate-950"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white"
                    >
                        <component :is="modalIcon" class="h-5 w-5" />
                    </span>
                    {{ modalTitle }}
                </DialogTitle>
                <DialogDescription class="text-sm text-slate-500">
                    {{ modalDescription }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-5 px-6 py-5" @submit.prevent="submit">
                <div class="grid gap-5">
                    <div class="space-y-2">
                        <Label for="degree_name">اسم المؤهل *</Label>
                        <Input
                            id="degree_name"
                            v-model="form.degree_name"
                            autocomplete="off"
                            placeholder="مثال: بكالوريوس، ماجستير، دكتوراه"
                        />
                        <p
                            v-if="form.errors.degree_name"
                            class="text-xs font-semibold text-red-600"
                        >
                            {{ form.errors.degree_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="institution">المؤسسة المانحة *</Label>
                        <Input
                            id="institution"
                            v-model="form.institution"
                            autocomplete="off"
                            placeholder="مثال: جامعة طرابلس"
                        />
                        <p
                            v-if="form.errors.institution"
                            class="text-xs font-semibold text-red-600"
                        >
                            {{ form.errors.institution }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="gap-2 border-t pt-5">
                    <Button type="button" variant="outline" @click="closeModal">
                        إلغاء
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-700 text-white hover:bg-blue-800"
                    >
                        {{ submitButtonText }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
