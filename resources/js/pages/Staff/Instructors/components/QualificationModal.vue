<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
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

const props = defineProps<{
    open: boolean;
    qualification?: {
        id: number;
        degree_name: string;
        institution: string;
    };
    instructorId: number;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    degree_name: props.qualification?.degree_name || '',
    institution: props.qualification?.institution || '',
});

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => emit('success'),
    };

    if (props.qualification?.id) {
        form.patch(`/qualifications/${props.qualification.id}`, options);
        return;
    }

    form.post('/qualifications', options);
};
</script>

<template>
    <Dialog :open="open" @update:open="(value) => !value && emit('close')">
        <DialogContent class="max-w-md" dir="rtl">
            <DialogHeader class="text-right">
                <DialogTitle class="text-blue-900">
                    {{ qualification ? 'تعديل مؤهل علمي' : 'إضافة مؤهل علمي' }}
                </DialogTitle>
                <DialogDescription>
                    أدخل اسم المؤهل والمؤسسة المانحة فقط.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4 py-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="degree_name">اسم المؤهل</Label>
                    <Input id="degree_name" v-model="form.degree_name" placeholder="مثال: ماجستير" />
                    <span v-if="form.errors.degree_name" class="text-xs text-red-500">
                        {{ form.errors.degree_name }}
                    </span>
                </div>

                <div class="grid gap-2">
                    <Label for="institution">المؤسسة المانحة</Label>
                    <Input id="institution" v-model="form.institution" placeholder="مثال: جامعة طرابلس" />
                    <span v-if="form.errors.institution" class="text-xs text-red-500">
                        {{ form.errors.institution }}
                    </span>
                </div>

                <DialogFooter class="gap-2 pt-4">
                    <Button variant="outline" type="button" @click="emit('close')">إلغاء</Button>
                    <Button type="submit" class="bg-orange-500 text-white hover:bg-orange-600">
                        {{ qualification ? 'تحديث' : 'حفظ' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
