<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Eye,
    Filter,
    GraduationCap,
    Layers,
    Pencil,
    Plus,
    Search,
    SlidersHorizontal,
    Trash2,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Department {
    id: number;
    name: string;
}

interface Specialization {
    id: number;
    department_id?: number;
    name: string;
    department?: Department | null;
    pivot?: { semester_level: number };
}

interface Prerequisite {
    id: number;
    code: string;
    name: string;
}

interface Course {
    id: number;
    code: string;
    name: string;
    units: number;
    has_practical: boolean | number;
    specializations_count?: number;
    prerequisites_count?: number;
    specializations?: Specialization[];
    prerequisites?: Prerequisite[];
}

interface PaginatedCourses {
    data: Course[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}

type CourseFilters = {
    search?: string;
    department?: string;
    specialization?: string;
    semester_level?: string;
    units?: string;
    has_practical?: boolean | string;
    prerequisite_status?: string;
    curriculum_status?: string;
    sort?: string;
    direction?: string;
    per_page?: number;
};

const sortOptions = ['code', 'name', 'units', 'specializations', 'created_at'] as const;
type SortOption = (typeof sortOptions)[number];

const props = defineProps<{
    courses: PaginatedCourses;
    filters: CourseFilters;
    departments: Department[];
    specializations: Specialization[];
    unitOptions: number[];
    semesterLevels: number[];
}>();

const search = ref(props.filters.search || '');
const selectedDepartment = ref(props.filters.department || 'all');
const selectedSpecialization = ref(props.filters.specialization || 'all');
const selectedLevel = ref(props.filters.semester_level || 'all');
const selectedUnits = ref(props.filters.units || 'all');
const selectedPractical = ref(props.filters.has_practical?.toString() || 'all');
const selectedPrerequisites = ref(props.filters.prerequisite_status || 'any');
const selectedCurriculum = ref(props.filters.curriculum_status || 'any');
const normalizeSort = (value: unknown): SortOption => {
    return typeof value === 'string' && sortOptions.includes(value as SortOption) ? value as SortOption : 'code';
};

const selectedSort = ref<SortOption>(normalizeSort(props.filters.sort));
const selectedDirection = ref(props.filters.direction || 'asc');
const selectedPerPage = ref(String(props.filters.per_page || props.courses.per_page || 25));

const showDeleteModal = ref(false);
const deletingCourse = ref<Course | null>(null);
let timeout: ReturnType<typeof setTimeout> | null = null;
let skipNextAutoApply = false;

const filteredSpecializations = computed(() => {
    if (selectedDepartment.value === 'all') {
        return props.specializations;
    }

    return props.specializations.filter(
        (specialization) => String(specialization.department_id) === selectedDepartment.value,
    );
});

const activeFilters = computed(() => {
    const items: { key: string; label: string; clear: () => void }[] = [];
    const department = props.departments.find((item) => String(item.id) === selectedDepartment.value);
    const specialization = props.specializations.find((item) => String(item.id) === selectedSpecialization.value);
    const sortLabels: Record<string, string> = {
        code: 'الرمز',
        name: 'اسم المقرر',
        units: 'الوحدات',
        specializations: 'عدد الخطط',
        created_at: 'الأحدث إضافة',
    };

    if (search.value.trim()) {
        items.push({
            key: 'search',
            label: `بحث: ${search.value}`,
            clear: () => {
                search.value = '';
                applyFiltersImmediately();
            },
        });
    }

    if (department) {
        items.push({
            key: 'department',
            label: `القسم: ${department.name}`,
            clear: () => {
                selectedDepartment.value = 'all';
                applyFiltersImmediately();
            },
        });
    }

    if (specialization) {
        items.push({
            key: 'specialization',
            label: `التخصص: ${specialization.name}`,
            clear: () => {
                selectedSpecialization.value = 'all';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedLevel.value !== 'all') {
        items.push({
            key: 'level',
            label: `المستوى: ${selectedLevel.value}`,
            clear: () => {
                selectedLevel.value = 'all';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedUnits.value !== 'all') {
        items.push({
            key: 'units',
            label: `الوحدات: ${selectedUnits.value}`,
            clear: () => {
                selectedUnits.value = 'all';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedPractical.value !== 'all') {
        items.push({
            key: 'practical',
            label: selectedPractical.value === '1' ? 'عملي' : 'نظري فقط',
            clear: () => {
                selectedPractical.value = 'all';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedPrerequisites.value !== 'any') {
        items.push({
            key: 'prerequisites',
            label: selectedPrerequisites.value === 'with' ? 'له متطلبات' : 'بدون متطلبات',
            clear: () => {
                selectedPrerequisites.value = 'any';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedCurriculum.value !== 'any') {
        items.push({
            key: 'curriculum',
            label: selectedCurriculum.value === 'assigned' ? 'ضمن خطة' : 'غير مرتبط بخطة',
            clear: () => {
                selectedCurriculum.value = 'any';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedSort.value !== 'code') {
        items.push({
            key: 'sort',
            label: `الفرز: ${sortLabels[selectedSort.value] ?? selectedSort.value}`,
            clear: () => {
                selectedSort.value = 'code';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedDirection.value !== 'asc') {
        items.push({
            key: 'direction',
            label: `الاتجاه: ${selectedDirection.value === 'asc' ? 'تصاعدي' : 'تنازلي'}`,
            clear: () => {
                selectedDirection.value = 'asc';
                applyFiltersImmediately();
            },
        });
    }

    if (selectedPerPage.value !== '25') {
        items.push({
            key: 'per_page',
            label: `عدد الصفوف: ${selectedPerPage.value}`,
            clear: () => {
                selectedPerPage.value = '25';
                applyFiltersImmediately();
            },
        });
    }

    return items;
});

const buildParams = () => ({
    search: search.value || undefined,
    department: selectedDepartment.value === 'all' ? undefined : selectedDepartment.value,
    specialization: selectedSpecialization.value === 'all' ? undefined : selectedSpecialization.value,
    semester_level: selectedLevel.value === 'all' ? undefined : selectedLevel.value,
    units: selectedUnits.value === 'all' ? undefined : selectedUnits.value,
    has_practical: selectedPractical.value === 'all' ? undefined : selectedPractical.value,
    prerequisite_status: selectedPrerequisites.value === 'any' ? undefined : selectedPrerequisites.value,
    curriculum_status: selectedCurriculum.value === 'any' ? undefined : selectedCurriculum.value,
    sort: selectedSort.value === 'code' ? undefined : selectedSort.value,
    direction: selectedDirection.value === 'asc' ? undefined : selectedDirection.value,
    per_page: selectedPerPage.value === '25' ? undefined : selectedPerPage.value,
});

const applyFilters = (delay = 350) => {
    if (timeout) {
        clearTimeout(timeout);
    }

    if (delay <= 0) {
        router.get('/academic/courses', buildParams(), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });

        return;
    }

    timeout = setTimeout(() => {
        router.get('/academic/courses', buildParams(), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, delay);
};

const applyFiltersImmediately = () => {
    if (timeout) {
        clearTimeout(timeout);
        timeout = null;
    }

    skipNextAutoApply = true;
    applyFilters(0);
};

const setSelectedSort = (value: unknown) => {
    selectedSort.value = normalizeSort(value);
};

const resetFilters = () => {
    search.value = '';
    selectedDepartment.value = 'all';
    selectedSpecialization.value = 'all';
    selectedLevel.value = 'all';
    selectedUnits.value = 'all';
    selectedPractical.value = 'all';
    selectedPrerequisites.value = 'any';
    selectedCurriculum.value = 'any';
    selectedSort.value = 'code';
    selectedDirection.value = 'asc';
    selectedPerPage.value = '25';
    applyFiltersImmediately();
};

const changePage = (url: string | null) => {
    if (url) {
        router.visit(url, { preserveScroll: true, preserveState: true });
    }
};

const openDeleteModal = (course: Course) => {
    deletingCourse.value = course;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deletingCourse.value) {
        return;
    }

    router.delete(`/academic/courses/${deletingCourse.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingCourse.value = null;
        },
    });
};

const getUnitBadgeClass = (units: number) => {
    if (units <= 2) {
        return 'bg-emerald-100 text-emerald-800';
    }

    if (units <= 4) {
        return 'bg-blue-100 text-blue-800';
    }

    return 'bg-violet-100 text-violet-800';
};

const previousUrl = computed(() => props.courses.links.find((link) => link.label.includes('Previous'))?.url || null);
const nextUrl = computed(() => props.courses.links.find((link) => link.label.includes('Next'))?.url || null);

watch(selectedDepartment, () => {
    if (
        selectedSpecialization.value !== 'all'
        && !filteredSpecializations.value.some((item) => String(item.id) === selectedSpecialization.value)
    ) {
        selectedSpecialization.value = 'all';
    }
});

watch(
    [
        search,
        selectedDepartment,
        selectedSpecialization,
        selectedLevel,
        selectedUnits,
        selectedPractical,
        selectedPrerequisites,
        selectedCurriculum,
        selectedSort,
        selectedDirection,
        selectedPerPage,
    ],
    () => {
        if (skipNextAutoApply) {
            skipNextAutoApply = false;

            return;
        }

        applyFilters();
    },
);
</script>

<template>
    <Head title="إدارة المقررات الدراسية" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-5">
            <section class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-orange-600">الوحدة الأكاديمية</p>
                    <h1 class="mt-1 flex items-center gap-2 text-2xl font-black text-blue-950">
                        <BookOpen class="h-6 w-6 text-blue-800" />
                        إدارة المقررات الدراسية
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                        تصفية المقررات حسب الخطة الدراسية، المستوى، الوحدات، المتطلبات السابقة، ونوع المقرر.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-md border border-blue-100 bg-white px-4 py-2 text-center shadow-sm">
                        <p class="text-xs font-bold text-slate-500">نتائج البحث</p>
                        <p class="text-xl font-black text-blue-950">{{ courses.total }}</p>
                    </div>
                    <Link href="/academic/courses/create">
                        <Button class="gap-2 bg-orange-500 text-white hover:bg-orange-600">
                            <Plus class="h-4 w-4" />
                            مقرر جديد
                        </Button>
                    </Link>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-2">
                        <Filter class="h-5 w-5 text-blue-800" />
                        <h2 class="text-base font-black text-slate-950">الفلاتر والفرز</h2>
                    </div>
                    <Button variant="outline" class="gap-2" @click="resetFilters">
                        <X class="h-4 w-4" />
                        مسح الفلاتر
                    </Button>
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="md:col-span-2">
                        <Label class="text-xs font-bold text-slate-600">بحث بالرمز أو الاسم</Label>
                        <div class="relative mt-1">
                            <Search class="absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <Input v-model="search" class="pr-9" placeholder="مثال: CHEM101 أو الكيمياء العامة" />
                        </div>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">القسم</Label>
                        <Select v-model="selectedDepartment">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">كل الأقسام</SelectItem>
                                <SelectItem v-for="department in departments" :key="department.id" :value="String(department.id)">
                                    {{ department.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">التخصص</Label>
                        <Select v-model="selectedSpecialization">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">كل التخصصات</SelectItem>
                                <SelectItem v-for="specialization in filteredSpecializations" :key="specialization.id" :value="String(specialization.id)">
                                    {{ specialization.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">المستوى الدراسي</Label>
                        <Select v-model="selectedLevel">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">كل المستويات</SelectItem>
                                <SelectItem v-for="level in semesterLevels" :key="level" :value="String(level)">
                                    المستوى {{ level }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">الوحدات</Label>
                        <Select v-model="selectedUnits">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">كل الوحدات</SelectItem>
                                <SelectItem v-for="units in unitOptions" :key="units" :value="String(units)">
                                    {{ units }} وحدة
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">نوع المقرر</Label>
                        <Select v-model="selectedPractical">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">نظري وعملي</SelectItem>
                                <SelectItem value="1">به جانب عملي</SelectItem>
                                <SelectItem value="0">نظري فقط</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">المتطلبات السابقة</Label>
                        <Select v-model="selectedPrerequisites">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="any">الكل</SelectItem>
                                <SelectItem value="with">له متطلبات</SelectItem>
                                <SelectItem value="without">بدون متطلبات</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">الارتباط بالخطة</Label>
                        <Select v-model="selectedCurriculum">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="any">الكل</SelectItem>
                                <SelectItem value="assigned">ضمن خطة دراسية</SelectItem>
                                <SelectItem value="unassigned">غير مرتبط بخطة</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">الفرز</Label>
                        <Select :model-value="selectedSort" @update:model-value="setSelectedSort">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="code">الرمز</SelectItem>
                                <SelectItem value="name">اسم المقرر</SelectItem>
                                <SelectItem value="units">الوحدات</SelectItem>
                                <SelectItem value="specializations">عدد الخطط</SelectItem>
                                <SelectItem value="created_at">الأحدث إضافة</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">اتجاه الفرز</Label>
                        <Select v-model="selectedDirection">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="asc">تصاعدي</SelectItem>
                                <SelectItem value="desc">تنازلي</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label class="text-xs font-bold text-slate-600">عدد الصفوف</Label>
                        <Select v-model="selectedPerPage">
                            <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="10">10</SelectItem>
                                <SelectItem value="25">25</SelectItem>
                                <SelectItem value="50">50</SelectItem>
                                <SelectItem value="100">100</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div v-if="activeFilters.length" class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                    <button
                        v-for="filter in activeFilters"
                        :key="filter.key"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800 hover:bg-blue-100"
                        @click="filter.clear"
                    >
                        {{ filter.label }}
                        <X class="h-3 w-3" />
                    </button>
                </div>
            </section>

            <section class="hidden overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm md:block">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-slate-50">
                                <TableHead class="font-black">الرمز</TableHead>
                                <TableHead class="font-black">اسم المقرر</TableHead>
                                <TableHead class="font-black">
                                    <span class="inline-flex items-center gap-1"><Layers class="h-4 w-4" /> الخطط</span>
                                </TableHead>
                                <TableHead class="text-center font-black">
                                    <span class="inline-flex items-center gap-1"><Award class="h-4 w-4" /> الوحدات</span>
                                </TableHead>
                                <TableHead class="text-center font-black">النوع</TableHead>
                                <TableHead class="font-black">المتطلبات</TableHead>
                                <TableHead class="text-center font-black">الإجراءات</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="course in courses.data" :key="course.id" class="hover:bg-orange-50/40">
                                <TableCell class="font-mono font-black text-blue-800">{{ course.code }}</TableCell>
                                <TableCell>
                                    <div class="font-bold text-slate-950">{{ course.name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ course.specializations_count ?? course.specializations?.length ?? 0 }} خطة مرتبطة
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex max-w-md flex-wrap gap-1">
                                        <Badge
                                            v-for="spec in course.specializations"
                                            :key="spec.id"
                                            variant="secondary"
                                            class="gap-1 bg-blue-50 text-blue-800"
                                        >
                                            <GraduationCap class="h-3 w-3" />
                                            {{ spec.name }} / مستوى {{ spec.pivot?.semester_level }}
                                        </Badge>
                                        <span v-if="!course.specializations?.length" class="text-xs font-bold text-rose-500">غير مرتبط بخطة</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge :class="getUnitBadgeClass(course.units)">{{ course.units }}</Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span v-if="course.has_practical" class="inline-flex items-center gap-1 text-sm font-bold text-emerald-700">
                                        <CheckCircle2 class="h-4 w-4" /> عملي
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 text-sm font-bold text-slate-500">
                                        <XCircle class="h-4 w-4" /> نظري
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex max-w-sm flex-wrap gap-1">
                                        <Badge v-for="prereq in course.prerequisites" :key="prereq.id" variant="outline" class="bg-amber-50 text-amber-800">
                                            {{ prereq.code }}
                                        </Badge>
                                        <span v-if="!course.prerequisites?.length" class="text-xs text-slate-400">لا يوجد</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex justify-center gap-2">
                                        <Link :href="`/academic/courses/${course.id}`" class="rounded-md p-2 text-blue-700 hover:bg-blue-50" title="عرض">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link :href="`/academic/courses/${course.id}/edit`" class="rounded-md p-2 text-amber-700 hover:bg-amber-50" title="تعديل">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                        <button class="rounded-md p-2 text-red-700 hover:bg-red-50" title="حذف" @click="openDeleteModal(course)">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="courses.data.length === 0">
                                <TableCell colspan="7" class="py-14 text-center">
                                    <SlidersHorizontal class="mx-auto h-10 w-10 text-slate-300" />
                                    <p class="mt-3 font-bold text-slate-600">لا توجد مقررات مطابقة للفلاتر الحالية.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div v-if="courses.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
                    <p class="text-sm font-bold text-slate-500">
                        عرض {{ courses.from ?? 0 }} - {{ courses.to ?? courses.data.length }} من {{ courses.total }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="!previousUrl" @click="changePage(previousUrl)">
                            <ChevronRight class="h-4 w-4" /> السابق
                        </Button>
                        <span class="px-2 text-sm font-bold text-slate-600">صفحة {{ courses.current_page }} من {{ courses.last_page }}</span>
                        <Button variant="outline" size="sm" :disabled="!nextUrl" @click="changePage(nextUrl)">
                            التالي <ChevronLeft class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </section>

            <section class="space-y-3 md:hidden">
                <article v-for="course in courses.data" :key="course.id" class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-mono text-sm font-black text-blue-800">{{ course.code }}</p>
                            <h2 class="mt-1 text-base font-black text-slate-950">{{ course.name }}</h2>
                        </div>
                        <Badge :class="getUnitBadgeClass(course.units)">{{ course.units }} وحدات</Badge>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-600">
                        <span>{{ course.has_practical ? 'عملي' : 'نظري' }}</span>
                        <span>{{ course.specializations?.length || 0 }} خطة</span>
                        <span>{{ course.prerequisites?.length || 0 }} متطلب</span>
                    </div>

                    <div class="mt-3 flex justify-end gap-2">
                        <Link :href="`/academic/courses/${course.id}`" class="rounded-md p-2 text-blue-700"><Eye class="h-4 w-4" /></Link>
                        <Link :href="`/academic/courses/${course.id}/edit`" class="rounded-md p-2 text-amber-700"><Pencil class="h-4 w-4" /></Link>
                        <button class="rounded-md p-2 text-red-700" @click="openDeleteModal(course)"><Trash2 class="h-4 w-4" /></button>
                    </div>
                </article>
            </section>
        </div>

        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle class="text-red-700">تأكيد حذف المقرر</DialogTitle>
                    <DialogDescription>
                        سيتم حذف "{{ deletingCourse?.name }}" من النظام. لا يمكن التراجع عن هذا الإجراء.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">إلغاء</Button>
                    <Button class="bg-red-600 hover:bg-red-700" @click="confirmDelete">حذف</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </main>
</template>
