<script setup lang="ts">
import {
    Building2,
    Eye,
    FileText,
    GraduationCap,
    Mail,
    Phone,
    UserSquare2,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Department {
    id: number;
    name: string;
}

interface Instructor {
    id: number;
    name: string;
    department?: Department | null;
    national_id: string | null;
    email: string | null;
    phone: string | null;
    academic_rank: string | null;
    status: 'Active' | 'On_Leave' | 'Suspended';
    qualifications: Array<{
        id: number;
        degree_name: string;
        institution: string;
    }>;
}

const props = defineProps<{
    open: boolean;
    instructor: Instructor;
}>();

const emit = defineEmits<{
    close: [];
    edit: [];
}>();

const statusBadge = (status: string) => {
    const map = {
        Active: { label: 'نشط', class: 'bg-emerald-100 text-emerald-800 ring-emerald-600/20' },
        On_Leave: { label: 'في إجازة', class: 'bg-amber-100 text-amber-800 ring-amber-600/20' },
        Suspended: { label: 'موقوف', class: 'bg-red-100 text-red-800 ring-red-600/20' },
    };

    return map[status as keyof typeof map] || { label: status, class: 'bg-slate-100 text-slate-800' };
};
</script>

<template>
    <Dialog :open="open" @update:open="(value) => !value && emit('close')">
        <DialogContent class="flex max-h-[90vh] flex-col overflow-hidden p-0 sm:max-w-3xl" dir="rtl">
            <div class="bg-blue-900 p-6 text-white">
                <DialogHeader class="text-right">
                    <DialogTitle class="flex items-center gap-2 text-2xl font-bold text-white">
                        <Eye class="h-6 w-6 text-orange-300" />
                        الملف التعريفي للمحاضر
                    </DialogTitle>
                    <DialogDescription class="text-blue-100">
                        بيانات المحاضر والمؤهلات العلمية المسجلة في النظام.
                    </DialogDescription>
                </DialogHeader>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto bg-slate-50 p-6">
                <div class="flex items-center gap-4 rounded-xl border bg-white p-5 shadow-sm">
                    <div class="rounded-xl bg-blue-50 p-3 text-blue-900">
                        <UserSquare2 class="h-10 w-10" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-blue-900">{{ props.instructor.name }}</h2>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">
                                {{ props.instructor.academic_rank || 'عضو هيئة تدريس' }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset"
                                :class="statusBadge(props.instructor.status).class"
                            >
                                {{ statusBadge(props.instructor.status).label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <Building2 class="mb-1 h-5 w-5 text-orange-500" />
                        <p class="text-xs font-bold text-slate-400">القسم الأكاديمي</p>
                        <p class="mt-0.5 font-bold text-slate-800">{{ props.instructor.department?.name || '—' }}</p>
                    </div>
                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <FileText class="mb-1 h-5 w-5 text-orange-500" />
                        <p class="text-xs font-bold text-slate-400">الرقم الوطني</p>
                        <p class="mt-0.5 font-mono font-bold text-slate-800">{{ props.instructor.national_id || '—' }}</p>
                    </div>
                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <Mail class="mb-1 h-5 w-5 text-orange-500" />
                        <p class="text-xs font-bold text-slate-400">البريد الإلكتروني</p>
                        <p class="mt-0.5 truncate font-medium text-slate-700" :title="props.instructor.email ?? ''">
                            {{ props.instructor.email || '—' }}
                        </p>
                    </div>
                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <Phone class="mb-1 h-5 w-5 text-orange-500" />
                        <p class="text-xs font-bold text-slate-400">رقم الهاتف</p>
                        <p class="mt-0.5 font-bold text-slate-800">{{ props.instructor.phone || '—' }}</p>
                    </div>
                </div>

                <section class="space-y-3">
                    <h3 class="flex items-center gap-1.5 font-bold text-blue-900">
                        <GraduationCap class="h-5 w-5 text-blue-800" />
                        المؤهلات العلمية
                    </h3>

                    <div
                        v-if="props.instructor.qualifications && props.instructor.qualifications.length"
                        class="overflow-hidden rounded-xl border bg-white shadow-sm"
                    >
                        <table class="w-full text-right text-sm">
                            <thead class="bg-blue-50 text-blue-900">
                                <tr>
                                    <th class="p-3 font-bold">اسم المؤهل</th>
                                    <th class="p-3 font-bold">المؤسسة المانحة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr v-for="qualification in instructor.qualifications" :key="qualification.id">
                                    <td class="p-3 font-bold text-blue-900">{{ qualification.degree_name }}</td>
                                    <td class="p-3 font-medium">{{ qualification.institution }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-400">
                        لم يتم تسجيل أي مؤهلات علمية لهذا المحاضر بعد.
                    </div>
                </section>
            </div>

            <DialogFooter class="gap-2 border-t bg-white p-4 sm:justify-start">
                <Button class="bg-orange-500 font-bold text-white hover:bg-orange-600" @click="emit('edit')">
                    تعديل البيانات
                </Button>
                <Button variant="outline" @click="emit('close')">إغلاق</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
