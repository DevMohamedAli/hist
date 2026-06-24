<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Building2,
    ChevronLeft,
    ChevronRight,
    Eye,
    Filter,
    Layers,
    Pencil,
    PlusCircle,
    RotateCcw,
    Search,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';
import StudyGroupCreationBlockedDialog from '@/components/StudyGroupCreationBlockedDialog.vue';

interface Department {
    id: number;
    name: string;
}

interface Specialization {
    id: number;
    name: string;
    department?: Department | null;
}

interface AcademicSemester {
    id: number;
    code: string;
}

interface StudyGroup {
    id: number;
    group_name: string;
    semester_level: number;
    capacity: number;
    enrollments_count?: number;
    specialization?: Specialization | null;
    academic_semester?: AcademicSemester | null;
}

interface PaginationLinkType {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCollection<T> {
    current_page: number;
    data: T[];
    from: number | null;
    last_page: number;
    links: PaginationLinkType[];
    next_page_url: string | null;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface CreationAvailability {
    is_open: boolean;
    message: string;
    days_remaining?: number | null;
    semester?: {
        id: number;
        code: string;
        registration_start: string | null;
        registration_end: string | null;
    } | null;
}

interface Props {
    studyGroups: PaginatedCollection<StudyGroup>;
    specializations: Specialization[];
    semesters: AcademicSemester[];
    creationAvailability: CreationAvailability;
    filters: {
        search: string | null;
        specialization_id: string | null;
        academic_semester_id: string | null;
        semester_level: string | null;
        per_page: string | null;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');
const specializationId = ref(props.filters.specialization_id ?? '');
const academicSemesterId = ref(props.filters.academic_semester_id ?? '');
const semesterLevel = ref(props.filters.semester_level ?? '');
const perPage = ref(props.filters.per_page ?? String(props.studyGroups.per_page ?? 10));
const creationDialogOpen = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;

const applyFilters = (resetPage = true) => {
    router.get(
        '/academic/study-groups',
        {
            search: search.value || undefined,
            specialization_id: specializationId.value || undefined,
            academic_semester_id: academicSemesterId.value || undefined,
            semester_level: semesterLevel.value || undefined,
            per_page: perPage.value || undefined,
            ...(resetPage ? {} : { page: props.studyGroups.current_page }),
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 350);
};

const resetFilters = () => {
    search.value = '';
    specializationId.value = '';
    academicSemesterId.value = '';
    semesterLevel.value = '';
    perPage.value = '10';
    applyFilters();
};

const confirmDelete = (id: number, groupName: string) => {
    if (confirm(`هل أنت متأكد من حذف المجموعة "${groupName}"؟`)) {
        router.delete(`/academic/study-groups/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="المجموعات التدريسية" />

    <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-orange-500">الوحدة الأكاديمية</p>
                        <h1 class="mt-1 text-2xl font-bold text-blue-800">المجموعات التدريسية</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            إدارة مجموعات الطلاب حسب التخصص والمستوى والفصل مع تقييد الإنشاء على الفصل النشط المفتوح للتسجيل.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-800">
                        <Users class="h-6 w-6" />
                        <div>
                            <p class="text-xs font-medium text-gray-500">إجمالي النتائج</p>
                            <p class="text-xl font-bold">{{ props.studyGroups.total }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-5 rounded-lg border p-4 text-sm"
                    :class="
                        props.creationAvailability.is_open
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                            : 'border-amber-200 bg-amber-50 text-amber-900'
                    "
                >
                    <p class="font-bold">{{ props.creationAvailability.message }}</p>
                    <p v-if="props.creationAvailability.semester" class="mt-1">
                        الفصل: {{ props.creationAvailability.semester.code }} |
                        نافذة التسجيل: {{ props.creationAvailability.semester.registration_start ?? '-' }}
                        إلى {{ props.creationAvailability.semester.registration_end ?? '-' }}
                    </p>
                </div>
            </section>

            <div class="flex justify-end">
                <Link
                    v-if="props.creationAvailability.is_open"
                    href="/academic/study-groups/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600"
                >
                    <PlusCircle class="h-5 w-5" />
                    <span>إضافة مجموعة جديدة</span>
                </Link>
                <button
                    v-else
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-6 py-2.5 text-sm font-bold text-amber-900 shadow-sm transition hover:bg-amber-100"
                    @click="creationDialogOpen = true"
                >
                    <PlusCircle class="h-5 w-5" />
                    <span>إضافة مجموعة جديدة</span>
                </button>
            </div>

            <section class="rounded-lg bg-white p-6 shadow-md">
                <div class="mb-4 flex items-center gap-2 text-sm font-bold text-blue-800">
                    <Filter class="h-4 w-4" />
                    أدوات التصفية والعرض
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">البحث</label>
                        <div class="relative mt-1">
                            <Search class="absolute top-2.5 right-3 h-4 w-4 text-gray-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="اسم المجموعة أو التخصص أو الفصل"
                                class="block w-full rounded-lg border border-gray-300 py-2 pr-10 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                                @input="handleSearch"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">التخصص</label>
                        <select
                            v-model="specializationId"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            @change="() => applyFilters()"
                        >
                            <option value="">جميع التخصصات</option>
                            <option
                                v-for="specialization in props.specializations"
                                :key="specialization.id"
                                :value="String(specialization.id)"
                            >
                                {{ specialization.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">الفصل الدراسي</label>
                        <select
                            v-model="academicSemesterId"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            @change="() => applyFilters()"
                        >
                            <option value="">جميع الفصول</option>
                            <option
                                v-for="semester in props.semesters"
                                :key="semester.id"
                                :value="String(semester.id)"
                            >
                                {{ semester.code }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">المستوى</label>
                        <select
                            v-model="semesterLevel"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            @change="() => applyFilters()"
                        >
                            <option value="">جميع المستويات</option>
                            <option v-for="level in 12" :key="level" :value="String(level)">
                                المستوى {{ level }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">عدد السجلات</label>
                        <select
                            v-model="perPage"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-3 text-start shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                            @change="() => applyFilters()"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="resetFilters"
                    >
                        <RotateCcw class="h-4 w-4" />
                        إعادة ضبط
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                        @click="() => applyFilters()"
                    >
                        <Search class="h-4 w-4" />
                        تطبيق التصفية
                    </button>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-225 text-start text-sm">
                        <thead class="bg-blue-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-start font-semibold">#</th>
                                <th class="px-6 py-4 text-start font-semibold">المجموعة</th>
                                <th class="px-6 py-4 text-start font-semibold">التخصص</th>
                                <th class="px-6 py-4 text-start font-semibold">الفصل</th>
                                <th class="px-6 py-4 text-center font-semibold">المستوى</th>
                                <th class="px-6 py-4 text-center font-semibold">الطاقة</th>
                                <th class="px-6 py-4 text-center font-semibold">المسجلين</th>
                                <th class="px-6 py-4 text-center font-semibold">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="(group, index) in props.studyGroups.data"
                                :key="group.id"
                                class="transition hover:bg-orange-50/60"
                            >
                                <td class="px-6 py-4 font-bold text-blue-800">
                                    {{ (props.studyGroups.from ?? 1) + index }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <Layers class="h-5 w-5 text-orange-500" />
                                        <span>{{ group.group_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <div class="flex items-center gap-1.5">
                                        <Building2 class="h-4 w-4 text-gray-400" />
                                        {{ group.specialization?.name ?? '—' }}
                                        <span
                                            v-if="group.specialization?.department"
                                            class="text-xs text-gray-500"
                                        >
                                            ({{ group.specialization.department.name }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ group.academic_semester?.code ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold">{{ group.semester_level }}</td>
                                <td class="px-6 py-4 text-center">{{ group.capacity }}</td>
                                <td class="px-6 py-4 text-center font-bold text-blue-800">
                                    {{ group.enrollments_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link
                                            :href="`/academic/study-groups/${group.id}`"
                                            class="inline-flex items-center gap-1 rounded bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-200"
                                        >
                                            <Eye class="h-3.5 w-3.5" /> عرض
                                        </Link>
                                        <Link
                                            :href="`/academic/study-groups/${group.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 transition hover:bg-blue-100"
                                        >
                                            <Pencil class="h-3.5 w-3.5" /> تعديل
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100"
                                            @click="confirmDelete(group.id, group.group_name)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" /> حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="props.studyGroups.data.length === 0">
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                    لا توجد مجموعات مطابقة للفلاتر الحالية.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-4 border-t border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-gray-600">
                        عرض
                        <span class="font-bold text-gray-900">{{ props.studyGroups.from ?? 0 }}</span>
                        إلى
                        <span class="font-bold text-gray-900">{{ props.studyGroups.to ?? 0 }}</span>
                        من أصل
                        <span class="font-bold text-gray-900">{{ props.studyGroups.total }}</span>
                        مجموعة
                    </p>

                    <div v-if="props.studyGroups.total > props.studyGroups.per_page" class="flex items-center justify-center gap-1">
                        <Link
                            v-if="props.studyGroups.prev_page_url"
                            :href="props.studyGroups.prev_page_url"
                            preserve-scroll
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <ChevronRight class="h-4 w-4" />
                            السابق
                        </Link>
                        <template v-for="link in props.studyGroups.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm font-medium"
                                :class="link.active ? 'bg-blue-800 text-white' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                {{ link.label }}
                            </Link>
                            <span
                                v-else
                                class="inline-flex h-8 min-w-8 items-center justify-center px-2 text-gray-400"
                            >
                                {{ link.label }}
                            </span>
                        </template>
                        <Link
                            v-if="props.studyGroups.next_page_url"
                            :href="props.studyGroups.next_page_url"
                            preserve-scroll
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            التالي
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </section>
        </div>

        <StudyGroupCreationBlockedDialog
            v-model:open="creationDialogOpen"
            :availability="props.creationAvailability"
        />
    </main>
</template>
