<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Building2,
    GraduationCap,
    Plus,
    Save,
    ShieldCheck,
    Trash2,
    UserPlus,
} from 'lucide-vue-next';
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
import { Label } from '@/components/ui/label';

interface Department {
    id: number;
    name: string;
}

interface Qualification {
    id: number;
    degree_name: string;
    institution: string;
}

interface QualificationForm {
    id: number | null;
    degree_name: string;
    institution: string;
}

interface Instructor {
    id: number;
    name: string;
    department_id: number | null;
    national_id: string | null;
    email: string | null;
    phone: string | null;
    academic_rank: string | null;
    status: 'Active' | 'On_Leave' | 'Suspended';
    qualifications: QualificationForm[];
}

const props = withDefaults(defineProps<{
    open?: boolean;
    instructor: Instructor;
    departments: Department[];
    qualifications: Qualification[];
}>(), {
    open: false,
    qualifications: () => [],
});

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const emptyQualification = (): QualificationForm => ({
    id: null,
    degree_name: '',
    institution: '',
});

const qualificationPayload = (instructor: Instructor): QualificationForm[] => {
    return (instructor.qualifications ?? []).map((qualification) => ({
        id: qualification.id,
        degree_name: qualification.degree_name ?? '',
        institution: qualification.institution ?? '',
    }));
};

const form = useForm({
    name: '',
    national_id: '',
    email: '',
    phone: '',
    academic_rank: '',
    department_id: '',
    status: 'Active',
    qualifications: [] as QualificationForm[],
});

const rankOptions = ['', 'معيد', 'محاضر مساعد', 'محاضر', 'أستاذ مساعد', 'أستاذ مشارك', 'أستاذ'];

const qualificationOptions = computed(() => {
    const currentQualifications = form.qualifications
        .filter((qualification): qualification is Qualification => qualification.id !== null);
    const options = [...props.qualifications, ...currentQualifications];
    const seen = new Set<string>();

    return options.filter((qualification) => {
        const key = String(qualification.id);

        if (seen.has(key)) {
            return false;
        }

        seen.add(key);
        return true;
    });
});

const fillForm = (instructor: Instructor) => {
    form.name = instructor.name;
    form.national_id = instructor.national_id ?? '';
    form.email = instructor.email ?? '';
    form.phone = instructor.phone ?? '';
    form.academic_rank = instructor.academic_rank ?? '';
    form.department_id = instructor.department_id ? String(instructor.department_id) : '';
    form.status = instructor.status;
    form.qualifications = qualificationPayload(instructor);
    form.clearErrors();
};

watch(() => props.instructor, (instructor) => fillForm(instructor), { immediate: true, deep: true });

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        fillForm(props.instructor);
    }
});

const closeModal = () => {
    form.clearErrors();
    emit('close');
};

const submit = () => {
    form.patch(`/staff/instructors/${props.instructor.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            emit('close');
        },
    });
};

const addQualification = () => {
    form.qualifications.push(emptyQualification());
};

const selectQualification = (index: number, value: string) => {
    if (value === '__new__') {
        form.qualifications[index] = emptyQualification();
        return;
    }

    const selected = props.qualifications.find((qualification) => qualification.id === Number(value));

    if (!selected) {
        return;
    }

    form.qualifications[index] = {
        id: selected.id,
        degree_name: selected.degree_name,
        institution: selected.institution,
    };
};

const removeQualification = (index: number) => {
    form.qualifications.splice(index, 1);
};

const qualificationError = (index: number, field: keyof QualificationForm) => {
    return form.errors[`qualifications.${index}.${field}` as keyof typeof form.errors];
};
</script>

<template>
    <Dialog :open="props.open" @update:open="(value) => !value && closeModal()">
        <DialogContent
            class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl border-none p-0 shadow-2xl sm:max-w-3xl"
            dir="rtl"
        >
            <div class="shrink-0 bg-linear-to-l from-blue-900 via-blue-800 to-orange-600 p-6 text-white">
                <DialogHeader class="space-y-1 text-right">
                    <DialogTitle class="flex items-center gap-2 text-2xl font-extrabold text-white">
                        <UserPlus class="h-6 w-6 text-orange-300" />
                        تعديل عضو هيئة تدريس
                    </DialogTitle>
                    <DialogDescription class="text-sm text-blue-100">
                        عدّل بيانات عضو هيئة التدريس واختر مؤهلاته من الجدول المعتمد أو أضف مؤهلاً جديداً.
                    </DialogDescription>
                </DialogHeader>
            </div>

            <form class="flex-1 space-y-6 overflow-y-auto bg-white p-6" @submit.prevent="submit">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="font-bold text-gray-700">الاسم الكامل *</Label>
                        <input
                            v-model="form.name"
                            required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        />
                        <p v-if="form.errors.name" class="text-xs font-semibold text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label class="font-bold text-gray-700">الرقم الوطني *</Label>
                        <input
                            v-model="form.national_id"
                            required
                            maxlength="12"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        />
                        <p v-if="form.errors.national_id" class="text-xs font-semibold text-red-500">
                            {{ form.errors.national_id }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label class="font-bold text-gray-700">البريد الإلكتروني</Label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        />
                        <p v-if="form.errors.email" class="text-xs font-semibold text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label class="font-bold text-gray-700">رقم الهاتف</Label>
                        <input
                            v-model="form.phone"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label class="flex items-center gap-2 font-bold text-gray-700">
                            <GraduationCap class="h-4 w-4 text-blue-600" />
                            الدرجة العلمية *
                        </Label>
                        <select
                            v-model="form.academic_rank"
                            required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="" disabled>اختر الدرجة العلمية</option>
                            <option v-for="rank in rankOptions.filter(Boolean)" :key="rank" :value="rank">
                                {{ rank }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label class="flex items-center gap-2 font-bold text-gray-700">
                            <Building2 class="h-4 w-4 text-blue-600" />
                            القسم العلمي *
                        </Label>
                        <select
                            v-model="form.department_id"
                            required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="" disabled>اختر القسم</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label class="flex items-center gap-2 font-bold text-gray-700">
                            <ShieldCheck class="h-4 w-4 text-blue-600" />
                            الحالة الوظيفية
                        </Label>
                        <select
                            v-model="form.status"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 outline-hidden transition focus:border-blue-800 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="Active">نشط</option>
                            <option value="On_Leave">في إجازة</option>
                            <option value="Suspended">موقوف</option>
                        </select>
                    </div>

                </div>

                <section class="space-y-4 rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-blue-900">المؤهلات العلمية</h3>
                            <p class="text-xs text-gray-500">
                                اختر مؤهلاً موجوداً، أو اختر "إنشاء مؤهل جديد" لكتابته من نفس النافذة.
                            </p>
                        </div>
                        <Button type="button" class="gap-2 bg-blue-800 text-white shadow-xs hover:bg-blue-900" @click="addQualification">
                            <Plus class="h-4 w-4" />
                            إضافة مؤهل
                        </Button>
                    </div>

                    <div v-if="form.qualifications.length" class="space-y-3">
                        <div
                            v-for="(qualification, index) in form.qualifications"
                            :key="index"
                            class="relative rounded-xl border border-blue-100 bg-white p-4 shadow-xs"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700">
                                    مؤهل {{ index + 1 }}
                                </span>
                                <button
                                    type="button"
                                    class="rounded-full p-1.5 text-red-600 transition hover:bg-red-50"
                                    @click="removeQualification(index)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="mb-3 space-y-1">
                                <Label class="text-xs font-bold text-gray-700">اختيار من المؤهلات الموجودة</Label>
                                <select
                                    :value="qualification.id ? String(qualification.id) : '__new__'"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm"
                                    @change="selectQualification(index, ($event.target as HTMLSelectElement).value)"
                                >
                                    <option value="__new__">إنشاء مؤهل جديد</option>
                                    <option
                                        v-for="option in qualificationOptions"
                                        :key="option.id"
                                        :value="String(option.id)"
                                    >
                                        {{ option.degree_name }} - {{ option.institution }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="space-y-1">
                                    <Label class="text-xs font-bold text-gray-700">اسم المؤهل *</Label>
                                    <input
                                        v-model="qualification.degree_name"
                                        required
                                        :readonly="Boolean(qualification.id)"
                                        placeholder="مثال: ماجستير"
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm read-only:bg-slate-100 read-only:text-slate-600"
                                    />
                                    <p v-if="qualificationError(index, 'degree_name')" class="text-[11px] font-semibold text-red-500">
                                        {{ qualificationError(index, 'degree_name') }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <Label class="text-xs font-bold text-gray-700">المؤسسة المانحة *</Label>
                                    <input
                                        v-model="qualification.institution"
                                        required
                                        :readonly="Boolean(qualification.id)"
                                        placeholder="مثال: جامعة طرابلس"
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm read-only:bg-slate-100 read-only:text-slate-600"
                                    />
                                    <p v-if="qualificationError(index, 'institution')" class="text-[11px] font-semibold text-red-500">
                                        {{ qualificationError(index, 'institution') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p v-else class="rounded-lg border border-dashed border-blue-200 bg-white/60 p-6 text-center text-sm text-gray-400">
                        لا توجد مؤهلات مضافة بعد.
                    </p>
                </section>

                <DialogFooter class="shrink-0 gap-2 border-t border-gray-100 pt-4 sm:justify-start">
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="gap-2 bg-orange-500 px-8 text-white shadow-lg transition-all duration-200 hover:bg-orange-600"
                    >
                        <Save class="h-4 w-4" />
                        حفظ التعديلات
                    </Button>
                    <Button type="button" variant="outline" class="border-gray-200 text-gray-600" @click="closeModal">
                        إلغاء
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
