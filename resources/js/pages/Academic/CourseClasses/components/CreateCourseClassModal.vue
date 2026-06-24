<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
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

const props = defineProps<{
    open: boolean;
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

const submit = () => {
    form.post('/academic/course-classes', {
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
    <Dialog :open="props.open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="overflow-hidden rounded-2xl border border-white/20 p-0 shadow-2xl backdrop-blur-md sm:max-w-md"
            dir="rtl"
        >
            <!-- Styled Modern Header -->
            <div
                class="relative overflow-hidden bg-linear-to-l from-blue-900 via-blue-800 to-orange-600 p-6 text-white shadow-md"
            >
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-size-[20px_20px]"
                ></div>
                <DialogHeader class="relative z-10 space-y-1 text-right">
                    <DialogTitle
                        class="flex items-center gap-2 text-xl font-extrabold text-white drop-shadow-sm"
                    >
                        <Plus class="h-5 w-5 animate-pulse text-orange-300" />
                        إسناد محاضر جديد
                    </DialogTitle>
                    <DialogDescription class="text-xs text-blue-100"
                        >قم باختيار المقرر، المجموعة الدراسية، وتعيين المحاضر
                        المناسب</DialogDescription
                    >
                </DialogHeader>
            </div>

            <!-- Form Content Box -->
            <form
                @submit.prevent="submit"
                class="space-y-5 bg-white p-6 dark:bg-gray-900"
            >
                <div class="space-y-2">
                    <Label
                        class="text-sm font-bold text-gray-700 dark:text-gray-300"
                        >المجموعة الدراسية</Label
                    >
                    <Select v-model="form.study_group_id">
                        <SelectTrigger
                            class="w-full border-gray-200/80 transition-all focus:ring-2 focus:ring-orange-500/20"
                        >
                            <SelectValue placeholder="اختر المجموعة" />
                        </SelectTrigger>
                        <SelectContent dir="rtl">
                            <SelectItem
                                v-for="g in props.studyGroups"
                                :key="g.id"
                                :value="String(g.id)"
                            >
                                {{ g.group_name }} —
                                {{ g.specialization?.name }}
                                <span
                                    class="text-xs text-gray-400"
                                    v-if="g.academic_semester?.code"
                                    >({{ g.academic_semester?.code }})</span
                                >
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.study_group_id"
                        class="mt-1 text-xs font-semibold text-red-500"
                    >
                        {{ form.errors.study_group_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label
                        class="text-sm font-bold text-gray-700 dark:text-gray-300"
                        >المقرر الدراسي</Label
                    >
                    <Select v-model="form.course_id">
                        <SelectTrigger
                            class="w-full border-gray-200/80 transition-all focus:ring-2 focus:ring-orange-500/20"
                        >
                            <SelectValue placeholder="اختر المقرر" />
                        </SelectTrigger>
                        <SelectContent dir="rtl">
                            <SelectItem
                                v-for="c in props.courses"
                                :key="c.id"
                                :value="String(c.id)"
                            >
                                {{ c.name }}
                                <span class="text-xs text-gray-400"
                                    >({{ c.code }})</span
                                >
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.course_id"
                        class="mt-1 text-xs font-semibold text-red-500"
                    >
                        {{ form.errors.course_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label
                        class="text-sm font-bold text-gray-700 dark:text-gray-300"
                        >المحاضر</Label
                    >
                    <Select v-model="form.instructor_id">
                        <SelectTrigger
                            class="w-full border-gray-200/80 transition-all focus:ring-2 focus:ring-orange-500/20"
                        >
                            <SelectValue placeholder="اختر المحاضر" />
                        </SelectTrigger>
                        <SelectContent dir="rtl">
                            <SelectItem
                                v-for="i in props.instructors"
                                :key="i.id"
                                :value="String(i.id)"
                            >
                                {{ i.name }}
                                <span
                                    class="text-xs text-gray-400"
                                    v-if="i.employee_id"
                                    >({{ i.employee_id }})</span
                                >
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.instructor_id"
                        class="mt-1 text-xs font-semibold text-red-500"
                    >
                        {{ form.errors.instructor_id }}
                    </p>
                </div>

                <!-- Action Footer Layer -->
                <DialogFooter
                    class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeModal"
                        class="border-gray-200 transition-all hover:bg-gray-50"
                    >
                        إلغاء
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-orange-500 px-6 text-white shadow-lg transition-all hover:bg-orange-600"
                    >
                        حفظ الإسناد
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
