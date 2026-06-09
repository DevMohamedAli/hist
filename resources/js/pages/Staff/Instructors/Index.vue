<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    UsersRound,
    GraduationCap,
    Mail,
    Building2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import CreateInstructorModal from './components/CreateInstructorModal.vue';
import EditInstructorModal from './components/EditInstructorModal.vue';
import ShowInstructorModal from './components/ShowInstructorModal.vue';

interface Department {
    id: number;
    name: string;
}

interface Qualification {
    id: number;
    degree_name: string;
    institution: string;
}

interface Instructor {
    id: number;
    name: string;
    department?: Department | null;
    department_id: number | null;
    national_id: string | null;
    email: string | null;
    phone: string | null;
    academic_rank: string | null;
    status: 'Active' | 'On_Leave' | 'Suspended';
    qualifications: Qualification[];
}

defineProps<{
    instructors: Instructor[];
    departments: Department[];
    qualifications: Qualification[];
}>();

// التحكم في حالات النوافذ المنبثقة والمحاضر المختار
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isShowOpen = ref(false);
const selectedInstructor = ref<Instructor | null>(null);

const openCreate = () => {
    isCreateOpen.value = true;
};

const openEdit = (instructor: Instructor) => {
    selectedInstructor.value = instructor;
    isEditOpen.value = true;
};

const openShow = (instructor: Instructor) => {
    selectedInstructor.value = instructor;
    isShowOpen.value = true;
};

const refreshData = () => {
    router.reload({ only: ['instructors', 'qualifications'] });
};

const confirmDelete = (id: number, name: string) => {
    if (confirm(`هل أنت متأكد من حذف المحاضر "${name}"؟`)) {
        router.delete(`/staff/instructors/${id}`, { preserveScroll: true });
    }
};

const statusBadge = (status: string) => {
    const map = {
        Active: {
            label: 'نشط',
            class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        },
        On_Leave: {
            label: 'في إجازة',
            class: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        },
        Suspended: {
            label: 'موقوف',
            class: 'bg-red-50 text-red-700 ring-red-600/20',
        },
    };

    return (
        map[status as keyof typeof map] || {
            label: status,
            class: 'bg-gray-50 text-gray-700 ring-gray-600/20',
        }
    );
};
</script>

<template>

    <Head title="هيئة التدريس" />

    <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="relative overflow-hidden rounded-xl bg-linear-to-l from-blue-900 via-blue-800 to-blue-900 p-6 text-white shadow-xl">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-size-[20px_20px]">
                </div>

                <div class="relative z-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-orange-400">شؤون الموظفين</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight">إدارة أعضاء هيئة التدريس</h1>
                        <p class="mt-2 text-sm text-blue-100/80">متابعة وتحديث كافة بيانات الكادر الأكاديمي والمؤهلات
                            المرتبطة بهم.</p>
                    </div>
                    <div
                        class="flex items-center gap-3 rounded-lg bg-white/10 p-4 text-white backdrop-blur-md border border-white/10 shadow-inner">
                        <UsersRound class="h-6 w-6 text-orange-400" />
                        <div>
                            <p class="text-xs font-medium text-blue-200">إجمالي الأعضاء</p>
                            <p class="text-2xl font-black">{{ instructors.length }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all duration-200 hover:bg-orange-600 hover:-translate-y-0.5 active:translate-y-0">
                    <Plus class="h-5 w-5" />
                    إضافة عضو جديد
                </button>
            </div>

            <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-250 text-start text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-6 py-4 text-start font-bold w-12">#</th>
                                <th class="px-6 py-4 text-start font-bold">الاسم</th>
                                <th class="px-6 py-4 text-start font-bold">الرقم الوطني</th>
                                <th class="px-6 py-4 text-start font-bold">البريد الإلكتروني</th>
                                <th class="px-6 py-4 text-start font-bold">القسم</th>
                                <th class="px-6 py-4 text-start font-bold">الدرجة العلمية</th>
                                <th class="px-6 py-4 text-center font-bold">الحالة</th>
                                <th class="px-6 py-4 text-center font-bold">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(instructor, index) in instructors" :key="instructor.id"
                                class="transition duration-150 hover:bg-orange-50/40">
                                <td class="px-6 py-4 font-bold text-blue-900">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <GraduationCap class="h-5 w-5 text-orange-500" />
                                        <span>{{ instructor.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-600">{{
                                    instructor.national_id || '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    <div class="flex items-center gap-1.5">
                                        <Mail class="h-4 w-4 text-gray-400" />
                                        <span class="truncate max-w-45">{{ instructor.email || '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <div class="flex items-center gap-1.5">
                                        <Building2 class="h-4 w-4 text-gray-400" />
                                        <span>{{ instructor.department?.name || 'غير محدد' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-700">{{ instructor.academic_rank || '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset"
                                        :class="statusBadge(instructor.status).class">
                                        {{ statusBadge(instructor.status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openShow(instructor)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 text-xs font-bold text-gray-700 transition hover:bg-gray-200">
                                            <Eye class="h-3.5 w-3.5" />
                                            عرض
                                        </button>
                                        <button @click="openEdit(instructor)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-bold text-blue-800 transition hover:bg-blue-100">
                                            <Pencil class="h-3.5 w-3.5" />
                                            تعديل
                                        </button>
                                        <button @click="confirmDelete(instructor.id, instructor.name)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-100">
                                            <Trash2 class="h-3.5 w-3.5" />
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="instructors.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    لا توجد بيانات لأعضاء هيئة التدريس حتى الآن.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <CreateInstructorModal :open="isCreateOpen" :departments="departments" :qualifications="qualifications" @close="isCreateOpen = false"
            @success="refreshData" />

        <EditInstructorModal v-if="selectedInstructor" :open="isEditOpen" :instructor="selectedInstructor"
            :departments="departments" :qualifications="qualifications" @close="isEditOpen = false" @success="refreshData" />

        <ShowInstructorModal v-if="selectedInstructor" :open="isShowOpen" :instructor="selectedInstructor"
            @close="isShowOpen = false" @edit="() => { isShowOpen = false; openEdit(selectedInstructor!); }" />
    </main>
</template>
