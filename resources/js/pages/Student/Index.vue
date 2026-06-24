<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronLeft,
    ChevronRight,
    Edit2,
    Eye,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';
import RegistrationClosedDialog from '@/components/RegistrationClosedDialog.vue';
import { formatDisplayDate } from '@/lib/date';

interface Specialization {
    id: number;
    name: string;
    department?: { id: number; name: string } | null;
}

interface Student {
    id: number;
    registration_number: string;
    full_name: string;
    national_id: string;
    gender: 'Male' | 'Female';
    nationality: string;
    birth_date: string;
    mobile: string | null;
    admission_date: string;
    qualification: string | null;
    current_semester_level: number;
    status: 'Active' | 'Suspended' | 'Transferred_Out' | 'Graduated';
    current_specialization?: Specialization | null;
}

interface PaginationLinkType {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCollection<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLinkType[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface RegistrationAvailability {
    is_open: boolean;
    message: string;
    semester?: {
        code: string;
        registration_start: string | null;
        registration_end: string | null;
    } | null;
}

const props = defineProps<{
    students: PaginatedCollection<Student>;
    specializations: Specialization[];
    registrationAvailability?: RegistrationAvailability;
    filters: {
        search: string | null;
        status: string | null;
        specialization: string | null;
    };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const specialization = ref(props.filters.specialization || '');
const registrationDialogOpen = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
const handleSearch = (resetPage = true) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/students',
            {
                search: search.value,
                status: status.value,
                specialization: specialization.value,
                ...(resetPage ? {} : { page: props.students.current_page }),
            },
            { preserveState: true, replace: true },
        );
    }, 400);
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    specialization.value = '';
    handleSearch();
};

const handleDelete = (id: number) => {
    if (confirm('هل أنت متأكد من حذف ملف هذا الطالب؟ لا يمكن التراجع.')) {
        router.delete(`/students/${id}`);
    }
};

const statusConfig: Record<string, { label: string; classes: string }> = {
    Active: {
        label: 'مُقيد نشط',
        classes: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    },
    Suspended: {
        label: 'موقوف',
        classes: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    },
    Graduated: {
        label: 'متخرج',
        classes: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    },
    Transferred_Out: {
        label: 'منتقل',
        classes: 'bg-purple-50 text-purple-700 ring-purple-600/20',
    },
};
</script>

<template>
    <Head title="سجل الطلاب" />

    <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-orange-500">
                            شؤون الطلاب | سجل الطلاب
                        </p>
                        <h1 class="mt-1 text-2xl font-bold text-blue-800">
                            سجل الطلاب المقيدين
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            إجمالي الطلاب المسجلين:
                            <span class="font-bold">
                                {{ students.total.toLocaleString() }}
                            </span>
                            طالب
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-800"
                    >
                        <Users class="h-6 w-6" />
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                إجمالي الطلاب
                            </p>
                            <p class="text-xl font-bold">
                                {{ students.total }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <Link
                    v-if="props.registrationAvailability?.is_open"
                    href="/students/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600"
                >
                    <Plus class="h-5 w-5" />
                    تسجيل طالب جديد
                </Link>
                <button
                    v-else
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-6 py-2.5 text-sm font-bold text-amber-900 shadow-sm transition hover:bg-amber-100"
                    @click="registrationDialogOpen = true"
                >
                    <Plus class="h-5 w-5" />
                    تسجيل طالب جديد
                </button>
            </div>

            <section class="rounded-lg bg-white p-6 shadow-md">
                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4"
                >
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            البحث
                        </label>
                        <div class="relative mt-1">
                            <Search
                                class="absolute top-2.5 right-3 h-4 w-4 text-gray-400"
                            />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="الاسم أو رقم القيد..."
                                class="block w-full rounded-lg border border-gray-300 py-2 pr-10 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                                @keyup.enter="handleSearch()"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            الحالة
                        </label>
                        <select
                            v-model="status"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-2 pr-10 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            @change="handleSearch()"
                        >
                            <option value="">جميع الحالات</option>
                            <option value="Active">مُقيد نشط</option>
                            <option value="Suspended">موقوف</option>
                            <option value="Graduated">متخرج</option>
                            <option value="Transferred_Out">منتقل</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            التخصص
                        </label>
                        <select
                            v-model="specialization"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-2 pr-10 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            @change="handleSearch()"
                        >
                            <option value="">جميع التخصصات</option>
                            <option
                                v-for="spec in specializations"
                                :key="spec.id"
                                :value="String(spec.id)"
                            >
                                {{ spec.name }}
                                <template v-if="spec.department?.name">
                                    ({{ spec.department.name }})
                                </template>
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            @click="resetFilters"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <RotateCcw class="h-4 w-4" />
                            إعادة ضبط
                        </button>
                        <button
                            @click="handleSearch()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                        >
                            <Search class="h-4 w-4" />
                            بحث
                        </button>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-200 text-start text-sm">
                        <thead class="bg-blue-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-start font-semibold">
                                    رقم القيد
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    الاسم الكامل
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    الجنس
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    التخصص الأكاديمي
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    الحالة
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    تاريخ القبول
                                </th>
                                <th class="px-6 py-4 text-center font-semibold">
                                    الإجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="student in students.data"
                                :key="student.id"
                                class="transition hover:bg-orange-50/60"
                            >
                                <td class="px-6 py-4 font-mono font-bold text-blue-800">
                                    {{ student.registration_number }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    {{ student.full_name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                        :class="
                                            student.gender === 'Male'
                                                ? 'bg-sky-50 text-sky-700 ring-sky-600/20'
                                                : 'bg-pink-50 text-pink-700 ring-pink-600/20'
                                        "
                                    >
                                        {{ student.gender === 'Male' ? 'ذكر' : 'أنثى' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <div class="flex items-center gap-1.5">
                                        <BookOpen class="h-4 w-4 text-gray-400" />
                                        {{ student.current_specialization?.name ?? '—' }}
                                        <span
                                            v-if="student.current_specialization?.department"
                                            class="text-xs text-gray-500"
                                        >
                                            ({{ student.current_specialization.department.name }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                        :class="
                                            statusConfig[student.status]?.classes ||
                                            'bg-gray-50 text-gray-700 ring-gray-600/20'
                                        "
                                    >
                                        {{ statusConfig[student.status]?.label || student.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ formatDisplayDate(student.admission_date) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link
                                            :href="`/students/${student.id}`"
                                            class="inline-flex items-center gap-1 rounded bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-200"
                                            title="عرض"
                                        >
                                            <Eye class="h-3.5 w-3.5" /> عرض
                                        </Link>
                                        <Link
                                            :href="`/students/${student.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 transition hover:bg-blue-100"
                                            title="تعديل"
                                        >
                                            <Edit2 class="h-3.5 w-3.5" /> تعديل
                                        </Link>
                                        <button
                                            @click="handleDelete(student.id)"
                                            class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100"
                                            title="حذف"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" /> حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="students.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-10 text-center text-gray-500"
                                >
                                    <div class="flex flex-col items-center">
                                        <Users class="mb-3 h-12 w-12 text-gray-300" />
                                        <p class="font-semibold">
                                            لا يوجد طلاب مطابقون للبحث
                                        </p>
                                        <button
                                            @click="resetFilters"
                                            class="mt-2 text-sm text-blue-800 hover:underline"
                                        >
                                            إعادة ضبط الفلاتر
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="students.total > students.per_page"
                    class="flex items-center justify-center gap-1 border-t border-gray-200 px-6 py-4"
                >
                    <Link
                        v-if="students.prev_page_url"
                        :href="students.prev_page_url"
                        preserve-scroll
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ChevronRight class="h-4 w-4" />
                        السابق
                    </Link>
                    <template v-for="link in students.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium"
                            :class="
                                link.active
                                    ? 'bg-blue-800 text-white'
                                    : 'text-gray-700 hover:bg-gray-100'
                            "
                        >
                            {{ link.label }}
                        </Link>
                        <span
                            v-else
                            class="inline-flex h-8 w-8 items-center justify-center text-gray-400"
                        >
                            {{ link.label }}
                        </span>
                    </template>
                    <Link
                        v-if="students.next_page_url"
                        :href="students.next_page_url"
                        preserve-scroll
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        التالي
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </div>
            </section>
        </div>

        <RegistrationClosedDialog
            v-model:open="registrationDialogOpen"
            :registration="registrationAvailability"
        />
    </main>
</template>
