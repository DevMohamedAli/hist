<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Edit3 } from 'lucide-vue-next';
import { watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Course {
    id: number;
    code?: string | null;
    name: string;
}

interface Instructor {
    id: number;
    name: string;
    employee_id?: string | null;
}

interface StudyGroup {
    id: number;
    group_name: string;
    specialization?: { name: string } | null;
    academic_semester?: { code: string } | null;
}

interface CourseClass {
    id: number;
    study_group_id: number | null;
    course_id: number;
    instructor_id: number;
}

const props = defineProps<{
    open: boolean;
    item: CourseClass | null;
    studyGroups: StudyGroup[];
    courses: Course[];
    instructors: Instructor[];
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    study_group_id: '',
    course_id: '',
    instructor_id: '',
});

watch(() => props.item, (newItem) => {
    if (newItem) {
        form.study_group_id = String(newItem.study_group_id ?? '');
        form.course_id = String(newItem.course_id);
        form.instructor_id = String(newItem.instructor_id);
        form.clearErrors();
    }
}, { immediate: true });

const submit = () => {
    if (!props.item) {
        return;
    }

    form.patch(`/academic/course-classes/${props.item.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('success');
            emit('close');
        },
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="sm:max-w-md p-0 overflow-hidden rounded-2xl border border-white/20 shadow-2xl backdrop-blur-md"
            dir="rtl">

            <!-- Styled Modern Header -->
            <div
                class="relative overflow-hidden bg-linear-to-l from-blue-900 via-blue-800 to-orange-600 p-6 text-white shadow-md">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-size-[20px_20px]">
                </div>
                <DialogHeader class="relative z-10 text-right space-y-1">
                    <DialogTitle class="flex items-center gap-2 text-xl font-extrabold text-white drop-shadow-sm">
                        <Edit3 class="h-5 w-5 text-orange-300 animate-pulse" />
                        تعديل الإسناد الحالي
                    </DialogTitle>
                    <DialogDescription class="text-blue-100 text-xs">تحديث وتعديل بيانات إسناد المحاضر للشعبة الدراسية
                    </DialogDescription>
                </DialogHeader>
            </div>

            <!-- Form Content Box -->
            <form @submit.prevent="submit" class="p-6 space-y-5 bg-white dark:bg-gray-900">
                <div class="space-y-2">
                    <Label class="text-sm font-bold text-gray-700 dark:text-gray-300">المجموعة الدراسية</Label>
                    <Select v-model="form.study_group_id">
                        <SelectTrigger
                            class="w-full transition-all border-gray-200/80 focus:ring-2 focus:ring-blue-500/20">
                            <SelectValue placeholder="اختر المجموعة" />
                        </SelectTrigger>
                        <SelectContent dir="rtl">
                            <SelectItem v-for="g in studyGroups" :key="g.id" :value="String(g.id)">
                                {{ g.group_name }} — {{ g.specialization?.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.study_group_id" class="text-xs font-semibold text-red-500 mt-1">{{
                        form.errors.study_group_id }}</p>
                </div>

                <div class="space-y-2">
                    <Label class="text-sm font-bold text-gray-700 dark:text-gray-300">المقرر الدراسي</Label>
                    <Select v-model="form.course_id">
                        <SelectTrigger
                            class="w-full transition-all border-gray-200/80 focus:ring-2 focus:ring-blue-500/20">
                            <SelectValue placeholder="اختر المقرر" />
                        </SelectTrigger>
                        <SelectContent dir="rtl">
                            <SelectItem v-for="c in courses" :key="c.id" :value="String(c.id)">
                                {{ c.name }} <span class="text-xs text-gray-400">({{ c.code }})</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.course_id" class="text-xs font-semibold text-red-500 mt-1">{{
                        form.errors.course_id }}</p>
                </div>

                <div class="space-y-2">
                    <Label class="text-sm font-bold text-gray-700 dark:text-gray-300">المحاضر</Label>
                    <Select v-model="form.instructor_id">
                        <SelectTrigger
                            class="w-full transition-all border-gray-200/80 focus:ring-2 focus:ring-blue-500/20">
                            <SelectValue placeholder="اختر المحاضر" />
                        </SelectTrigger>
                        <SelectContent dir="rtl">
                            <SelectItem v-for="i in instructors" :key="i.id" :value="String(i.id)">
                                {{ i.name }} <span class="text-xs text-gray-400" v-if="i.employee_id">({{ i.employee_id
                                    }})</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.instructor_id" class="text-xs font-semibold text-red-500 mt-1">{{
                        form.errors.instructor_id }}</p>
                </div>

                <!-- Action Footer Layer -->
                <DialogFooter
                    class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <Button type="button" variant="outline" @click="closeModal"
                        class="border-gray-200 hover:bg-gray-50 transition-all">
                        إلغاء
                    </Button>
                    <Button type="submit" :disabled="form.processing"
                        class="bg-blue-600 text-white shadow-md hover:bg-blue-700 transition-all px-5">
                        تحديث البيانات
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
