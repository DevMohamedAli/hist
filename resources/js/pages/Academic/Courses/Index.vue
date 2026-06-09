<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Plus,
    Eye,
    Pencil,
    Trash2,
    Search,
    X,
    ChevronLeft,
    ChevronRight,
    Sparkles,
    Filter,
    CheckCircle2,
    XCircle,
    GraduationCap,
    Award,
    Layers,
} from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted } from 'vue';
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

interface Specialization {
    id: number;
    name: string;
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
    specializations?: Specialization[];
    prerequisites?: Prerequisite[];
}

interface PaginatedCourses {
    data: Course[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    courses: PaginatedCourses;
    filters: {
        search?: string;
        specialization?: string;
        units?: string;
        has_practical?: boolean | string;
        per_page?: number;
    };
    specializations: Specialization[];
}>();

// Local filter state
const search = ref(props.filters.search || '');
const selectedSpecialization = ref(props.filters.specialization || 'all');
const selectedUnits = ref(props.filters.units || '');
const selectedPractical = ref(props.filters.has_practical?.toString() || '');

// Debounced filter application
let timeout: ReturnType<typeof setTimeout>;
const applyFilters = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/academic/courses', {
            search: search.value,
            specialization: selectedSpecialization.value === 'all' ? '' : selectedSpecialization.value,
            units: selectedUnits.value === 'all' ? '' : selectedUnits.value,
            has_practical: selectedPractical.value === 'all' ? '' : selectedPractical.value,
            per_page: props.filters.per_page || 25,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 400);
};

const resetFilters = () => {
    search.value = '';
    selectedSpecialization.value = 'all';
    selectedUnits.value = '';
    selectedPractical.value = '';
    applyFilters();
};

watch([search, selectedSpecialization, selectedUnits, selectedPractical], applyFilters);

// Delete modal
const showDeleteModal = ref(false);
const deletingCourse = ref<Course | null>(null);

const openDeleteModal = (course: Course) => {
    deletingCourse.value = course;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (deletingCourse.value) {
        router.delete(`/academic/courses/${deletingCourse.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                deletingCourse.value = null;
            },
        });
    }
};

// Pagination
const changePage = (url: string | null) => {
    if (url) {
        window.location.href = url;
    }
};

// Scroll for floating action button
const isScrolled = ref(false);
const handleScroll = () => {
    isScrolled.value = window.scrollY > 200;
};
onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

// Helper for unit badge color
const getUnitBadgeClass = (units: number) => {
    if (units <= 2) {
        return 'bg-emerald-100 text-emerald-800';
    }

    if (units <= 4) {
        return 'bg-blue-100 text-blue-800';
    }

    return 'bg-purple-100 text-purple-800';
};
</script>

<template>

    <Head title="إدارة المقررات الدراسية" />

    <div class="min-h-screen bg-linear-to-br from-slate-50 to-blue-50/30 p-4 md:p-6" dir="rtl">
        <!-- Animated gradient header -->
        <div
            class="relative mb-8 overflow-hidden rounded-2xl bg-linear-to-l from-blue-900 via-blue-800 to-orange-600 p-6 shadow-xl">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-size-[20px_20px]">
            </div>
            <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-3xl font-extrabold text-white drop-shadow-md">
                        <BookOpen class="h-7 w-7 text-orange-300 animate-pulse" />
                        إدارة المقررات الدراسية
                    </h1>
                    <p class="mt-1 text-blue-100">إدارة المقررات وربطها بالخطط الدراسية للتخصصات</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-white/20 px-5 py-2 backdrop-blur-sm text-center">
                        <p class="text-xs font-semibold text-blue-100">إجمالي المقررات</p>
                        <p class="text-3xl font-extrabold text-white">{{ props.courses.total }}</p>
                    </div>
                    <Link href="/academic/courses/create">
                        <Button class="gap-2 bg-orange-500 text-white shadow-lg hover:bg-orange-600 transition-all">
                            <Plus class="h-4 w-4" />
                            مقرر جديد
                        </Button>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Filter Panel with Filter icon -->
        <div
            class="mb-6 overflow-hidden rounded-2xl border border-white/20 bg-white/80 shadow-xl backdrop-blur-md dark:bg-gray-800/80 p-4">
            <div class="flex items-center gap-2 mb-3">
                <Filter class="h-5 w-5 text-orange-500" />
                <span class="font-semibold text-gray-700">فلاتر البحث</span>
            </div>
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-45">
                    <Label class="text-sm font-medium">بحث</Label>
                    <div class="relative mt-1">
                        <Search class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <Input v-model="search" placeholder="الرمز أو اسم المقرر..." class="pr-9" />
                    </div>
                </div>
                <div class="w-48">
                    <Label class="text-sm font-medium">التخصص</Label>
                    <Select v-model="selectedSpecialization">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="الكل" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem v-for="spec in specializations" :key="spec.id" :value="String(spec.id)">
                                {{ spec.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="w-32">
                    <Label class="text-sm font-medium">الوحدات</Label>
                    <Select v-model="selectedUnits">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="الكل" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="1">1</SelectItem>
                            <SelectItem value="2">2</SelectItem>
                            <SelectItem value="3">3</SelectItem>
                            <SelectItem value="4">4</SelectItem>
                            <SelectItem value="5">5</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="w-32">
                    <Label class="text-sm font-medium">جانب عملي</Label>
                    <Select v-model="selectedPractical">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="الكل" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="1">نعم</SelectItem>
                            <SelectItem value="0">لا</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <Button variant="outline" @click="resetFilters" class="gap-1">
                    <X class="h-4 w-4" />
                    إعادة ضبط
                </Button>
            </div>
        </div>

        <!-- Courses Table (Desktop) -->
        <div
            class="hidden md:block overflow-hidden rounded-2xl border border-white/20 bg-white/80 shadow-xl backdrop-blur-md">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="border-b border-gray-200/50 bg-linear-to-r from-gray-50/80 to-transparent">
                            <TableHead class="font-semibold">الرمز</TableHead>
                            <TableHead class="font-semibold">اسم المقرر</TableHead>
                            <TableHead class="font-semibold">
                                <div class="flex items-center gap-1">
                                    <Layers class="h-4 w-4" />
                                    التخصصات والخطط
                                </div>
                            </TableHead>
                            <TableHead class="text-center font-semibold">
                                <div class="flex items-center justify-center gap-1">
                                    <Award class="h-4 w-4" />
                                    الوحدات
                                </div>
                            </TableHead>
                            <TableHead class="text-center font-semibold">عملي</TableHead>
                            <TableHead class="font-semibold">المتطلبات السابقة</TableHead>
                            <TableHead class="text-center font-semibold">الإجراءات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(course, idx) in courses.data" :key="course.id"
                            class="group transition-all duration-300 hover:bg-orange-50/40"
                            :style="{ animationDelay: `${idx * 30}ms` }">
                            <TableCell class="font-mono font-bold text-blue-800">{{ course.code }}</TableCell>
                            <TableCell class="font-semibold">{{ course.name }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-for="spec in course.specializations" :key="spec.id" variant="secondary"
                                        class="bg-blue-100 text-blue-800 gap-1">
                                        <GraduationCap class="h-3 w-3" />
                                        {{ spec.name }} (سمستر {{ spec.pivot?.semester_level }})
                                    </Badge>
                                    <span v-if="!course.specializations?.length" class="text-xs text-gray-400">—</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge :class="getUnitBadgeClass(course.units)">{{ course.units }}</Badge>
                            </TableCell>
                            <TableCell class="text-center">
                                <span v-if="course.has_practical" class="text-green-600 inline-flex items-center gap-1">
                                    <CheckCircle2 class="h-4 w-4" /> نعم
                                </span>
                                <span v-else class="text-gray-400 inline-flex items-center gap-1">
                                    <XCircle class="h-4 w-4" /> لا
                                </span>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-for="prereq in course.prerequisites" :key="prereq.id" variant="outline"
                                        class="bg-amber-50 text-amber-800">
                                        {{ prereq.code }} - {{ prereq.name }}
                                    </Badge>
                                    <span v-if="!course.prerequisites?.length" class="text-xs text-gray-400">لا
                                        يوجد</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-center">
                                <div class="flex justify-center gap-2">
                                    <Link :href="`/academic/courses/${course.id}`"
                                        class="text-blue-600 hover:text-blue-800">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                    <Link :href="`/academic/courses/${course.id}/edit`"
                                        class="text-amber-600 hover:text-amber-800">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button @click="openDeleteModal(course)" class="text-red-600 hover:text-red-800">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="courses.data.length === 0">
                            <TableCell colspan="7" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <Sparkles class="h-12 w-12" />
                                    <p>لا توجد مقررات تطابق معايير البحث</p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="courses.last_page > 1" class="border-t px-4 py-3 flex justify-between items-center">
                <div class="text-sm text-gray-500">عرض {{ courses.data.length }} من {{ courses.total }} مقرر</div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="courses.current_page === 1"
                        @click="changePage(courses.links.find(l => l.label === '&laquo; Previous')?.url || null)">
                        <ChevronRight class="h-4 w-4" /> السابق
                    </Button>
                    <span class="px-3 py-1 text-sm">صفحة {{ courses.current_page }} من {{ courses.last_page }}</span>
                    <Button variant="outline" size="sm" :disabled="courses.current_page === courses.last_page"
                        @click="changePage(courses.links.find(l => l.label === 'Next &raquo;')?.url || null)">
                        التالي
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3">
            <div v-for="course in courses.data" :key="course.id"
                class="rounded-2xl border border-white/20 bg-white/80 p-4 shadow-lg backdrop-blur-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-mono font-bold text-blue-800">{{ course.code }}</p>
                        <p class="font-bold text-lg">{{ course.name }}</p>
                    </div>
                    <Badge :class="getUnitBadgeClass(course.units)">{{ course.units }} وحدات</Badge>
                </div>
                <div class="mt-2 text-sm text-gray-600 flex flex-wrap gap-3">
                    <span class="flex items-center gap-1">
                        <CheckCircle2 v-if="course.has_practical" class="h-3 w-3 text-green-600" />
                        <XCircle v-else class="h-3 w-3 text-gray-400" /> عملي: {{ course.has_practical ? 'نعم' : 'لا' }}
                    </span>
                    <span><span class="font-semibold">متطلبات:</span> {{course.prerequisites?.length ?
                        course.prerequisites.map(p => p.code).join(', ') : 'لا يوجد'}}</span>
                </div>
                <div class="mt-2 flex gap-2 justify-end">
                    <Link :href="`/academic/courses/${course.id}`" class="p-2 text-blue-600">
                        <Eye class="h-4 w-4" />
                    </Link>
                    <Link :href="`/academic/courses/${course.id}/edit`" class="p-2 text-amber-600">
                        <Pencil class="h-4 w-4" />
                    </Link>
                    <button @click="openDeleteModal(course)" class="p-2 text-red-600">
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>
            </div>
            <div v-if="courses.data.length === 0" class="py-12 text-center text-gray-500">
                <Sparkles class="mx-auto h-12 w-12" />
                <p>لا توجد مقررات</p>
            </div>
            <!-- Pagination mobile -->
            <div v-if="courses.last_page > 1" class="flex justify-between items-center pt-2">
                <Button variant="outline" size="sm" :disabled="courses.current_page === 1"
                    @click="changePage(courses.links.find(l => l.label === '&laquo; Previous')?.url || null)">السابق</Button>
                <span class="text-sm">صفحة {{ courses.current_page }} من {{ courses.last_page }}</span>
                <Button variant="outline" size="sm" :disabled="courses.current_page === courses.last_page"
                    @click="changePage(courses.links.find(l => l.label === 'Next &raquo;')?.url || null)">التالي</Button>
            </div>
        </div>

        <!-- Floating Action Button -->
        <Transition name="float-button">
            <button v-if="isScrolled" @click="router.get('/academic/courses/create')"
                class="fixed bottom-6 left-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-orange-500 text-white shadow-2xl transition-all hover:bg-orange-600 hover:scale-110"
                title="إضافة مقرر جديد">
                <Plus class="h-6 w-6" />
            </button>
        </Transition>

        <!-- Delete Modal -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle class="text-red-600">تأكيد الحذف</DialogTitle>
                    <DialogDescription>
                        هل أنت متأكد من حذف المقرر "{{ deletingCourse?.name }}"؟ لا يمكن التراجع عن هذا الإجراء.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">إلغاء</Button>
                    <Button @click="confirmDelete" class="bg-red-600 hover:bg-red-700">حذف</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

tbody tr {
    animation: fadeSlideUp 0.3s ease-out forwards;
    opacity: 0;
}

tbody tr:nth-child(1) {
    animation-delay: 0ms;
}

tbody tr:nth-child(2) {
    animation-delay: 30ms;
}

tbody tr:nth-child(3) {
    animation-delay: 60ms;
}

.float-button-enter-active,
.float-button-leave-active {
    transition: all 0.2s ease;
}

.float-button-enter-from,
.float-button-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
