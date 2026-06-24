<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Plus,
    Edit3,
    Trash2,
    Calendar,
    User,
    Sparkles,
    CheckCircle2,
    XCircle,
    GraduationCap,
} from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import CreateCourseClassModal from './components/CreateCourseClassModal.vue';
import EditCourseClassModal from './components/EditCourseClassModal.vue';

interface Course {
    id: number;
    code?: string | null;
    name: string;
    units?: number | null;
}

interface Instructor {
    id: number;
    name: string;
    employee_id?: string | null;
}

interface StudyGroup {
    id: number;
    group_name: string;
    semester_level?: number;
    academic_semester?: { code: string } | null;
    specialization?: { name: string } | null;
}

interface CourseClass {
    id: number;
    group_name: string;
    course_id: number;
    study_group_id: number | null;
    instructor_id: number;
    student_count: number;
    course?: Course | null;
    semester?: { code: string } | null;
    study_group?: StudyGroup | null;
    instructor?: Instructor | null;
}

interface PageProps {
    flash?: { success?: string; error?: string };
    [key: string]: unknown;
}

const props = defineProps<{
    classes: CourseClass[];
    studyGroups: StudyGroup[];
    courses: Course[];
    instructors: Instructor[];
}>();

const page = usePage<PageProps>();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingItem = ref<CourseClass | null>(null);
const deletingId = ref<number | null>(null);
const deletingName = ref('');
const isScrolled = ref(false);

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const openCreateModal = () => {
    showCreateModal.value = true;
};

const openEditModal = (item: CourseClass) => {
    editingItem.value = item;
    showEditModal.value = true;
};

const openDeleteModal = (item: CourseClass) => {
    deletingId.value = item.id;
    deletingName.value = item.course?.name || item.group_name;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deletingId.value) {
        return;
    }

    router.delete(`/academic/course-classes/${deletingId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            deletingId.value = null;
            deletingName.value = '';
        },
    });
};

const handleScroll = () => {
    isScrolled.value = window.scrollY > 200;
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

const getStudentBadgeClass = (count: number) => {
    if (count === 0) {
        return 'bg-gray-100 text-gray-600 border-gray-200';
    }

    if (count < 10) {
        return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    }

    return 'bg-green-100 text-green-700 border-green-200';
};
</script>

<template>

    <Head title="إسناد المحاضرين للشعب الدراسية" />

    <div class="academic-course-classes-page flex-1 w-full h-full min-h-screen bg-linear-to-br from-slate-50 to-blue-50/30 p-4 md:p-6" dir="rtl">

        <div
            class="relative mb-8 overflow-hidden rounded-2xl bg-linear-to-l from-blue-900 via-blue-800 to-orange-600 p-6 shadow-xl">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-size-[20px_20px]">
            </div>

            <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-3xl font-extrabold text-white drop-shadow-md">
                        <BookOpen class="h-7 w-7 text-orange-300 animate-pulse" />
                        إسناد المحاضرين للشعب الدراسية
                    </h1>
                    <p class="mt-1 text-blue-100">كل مقرر داخل مجموعة دراسية يجب أن يكون مسنداً لمحاضر واحد</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-white/20 px-5 py-2 backdrop-blur-sm text-center">
                        <p class="text-xs font-semibold text-blue-100">الإسنادات</p>
                        <p class="text-3xl font-extrabold text-white">{{ props.classes.length }}</p>
                    </div>
                    <Button @click="openCreateModal"
                        class="gap-2 bg-orange-500 text-white shadow-lg hover:bg-orange-600 transition-all">
                        <Plus class="h-4 w-4" />
                        إسناد جديد
                    </Button>
                </div>
            </div>
        </div>

        <div v-if="successMessage" class="mb-5 animate-in slide-in-from-top-2 fade-in duration-300">
            <div
                class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700 shadow-sm">
                <CheckCircle2 class="h-5 w-5" />
                {{ successMessage }}
            </div>
        </div>
        <div v-if="errorMessage" class="mb-5 animate-in slide-in-from-top-2 fade-in duration-300">
            <div
                class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700 shadow-sm">
                <XCircle class="h-5 w-5" />
                {{ errorMessage }}
            </div>
        </div>

        <div class="hidden md:block">
            <div
                class="overflow-hidden rounded-2xl border border-white/20 bg-white/80 shadow-xl backdrop-blur-md transition-all dark:bg-gray-800/80">
                <div class="overflow-x-auto w-full">
                    <Table>
                        <TableHeader>
                            <TableRow
                                class="border-b border-gray-200/50 bg-linear-to-r from-gray-50/80 to-transparent dark:from-gray-800/50">
                                <TableHead class="font-semibold">المقرر</TableHead>
                                <TableHead class="font-semibold">المجموعة الدراسية</TableHead>
                                <TableHead class="text-center font-semibold">الفصل</TableHead>
                                <TableHead class="text-center font-semibold">المحاضر</TableHead>
                                <TableHead class="text-center font-semibold">الطلاب</TableHead>
                                <TableHead class="text-center font-semibold">الإجراءات</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(classItem, idx) in classes" :key="classItem.id"
                                class="group transition-all duration-300 hover:bg-linear-to-r hover:from-orange-50/50 hover:to-transparent dark:hover:from-orange-900/20"
                                :style="{ animationDelay: `${idx * 30}ms` }">
                                <TableCell>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ classItem.course?.name ||
                                            '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ classItem.course?.code || '-' }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <p class="font-semibold text-blue-800 dark:text-blue-300">{{
                                            classItem.study_group?.group_name || classItem.group_name }}</p>
                                        <p class="text-xs text-gray-500">{{ classItem.study_group?.specialization?.name
                                            || '-' }}</p>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="outline" class="bg-gray-100 text-gray-700">
                                        {{ classItem.semester?.code || '-' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <User class="h-3 w-3 text-gray-400" />
                                        <span>{{ classItem.instructor?.name || '-' }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge :class="getStudentBadgeClass(classItem.student_count)" class="font-semibold">
                                        {{ classItem.student_count }} طالب
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="ghost" size="icon" @click="openEditModal(classItem)"
                                            class="h-8 w-8 text-blue-600 hover:bg-blue-100">
                                            <Edit3 class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="openDeleteModal(classItem)"
                                            class="h-8 w-8 text-red-600 hover:bg-red-100">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="classes.length === 0">
                                <TableCell colspan="6" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Sparkles class="h-12 w-12 text-gray-300" />
                                        <p class="text-gray-500">لا توجد إسنادات بعد</p>
                                        <Button @click="openCreateModal" variant="outline" class="mt-2">إضافة إسناد
                                            جديد</Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <div class="md:hidden space-y-3">
            <div v-for="classItem in classes" :key="classItem.id"
                class="transform rounded-xl border border-white/20 bg-white/80 p-4 shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-xl dark:bg-gray-800/80">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-bold text-gray-900">{{ classItem.course?.name }}</p>
                        <p class="text-xs text-gray-500">{{ classItem.course?.code }}</p>
                    </div>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="icon" @click="openEditModal(classItem)"
                            class="h-8 w-8 text-blue-600">
                            <Edit3 class="h-3.5 w-3.5" />
                        </Button>
                        <Button variant="ghost" size="icon" @click="openDeleteModal(classItem)"
                            class="h-8 w-8 text-red-600">
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-3 text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                        <GraduationCap class="h-3 w-3" /> {{ classItem.study_group?.group_name }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Calendar class="h-3 w-3" /> {{ classItem.semester?.code }}
                    </div>
                    <div class="flex items-center gap-1">
                        <User class="h-3 w-3" /> {{ classItem.instructor?.name }}
                    </div>
                </div>
                <div class="mt-2 flex justify-between">
                    <Badge :class="getStudentBadgeClass(classItem.student_count)">{{ classItem.student_count }} طالب
                    </Badge>
                </div>
            </div>
            <div v-if="classes.length === 0" class="py-16 text-center">
                <Sparkles class="mx-auto h-12 w-12 text-gray-300" />
                <p class="mt-2 text-gray-500">لا توجد إسنادات</p>
                <Button @click="openCreateModal" variant="outline" class="mt-4">إضافة إسناد جديد</Button>
            </div>
        </div>

        <Transition name="float-button">
            <button v-if="isScrolled" @click="openCreateModal"
                class="fixed bottom-6 left-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-orange-500 text-white shadow-2xl transition-all hover:bg-orange-600 hover:scale-110"
                title="إسناد جديد">
                <Plus class="h-6 w-6" />
            </button>
        </Transition>

        <CreateCourseClassModal :open="showCreateModal" :study-groups="studyGroups" :courses="courses"
            :instructors="instructors" @close="showCreateModal = false" @success="showCreateModal = false" />

        <EditCourseClassModal :open="showEditModal" :item="editingItem" :study-groups="studyGroups" :courses="courses"
            :instructors="instructors" @close="showEditModal = false; editingItem = null"
            @success="showEditModal = false; editingItem = null" />

        <Dialog v-model:open="showDeleteModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-red-600">
                        <Trash2 class="h-5 w-5" />
                        تأكيد الحذف
                    </DialogTitle>
                    <DialogDescription>
                        هل أنت متأكد من حذف إسناد مقرر "{{ deletingName }}"؟
                        هذا الإجراء لا يمكن التراجع عنه.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteModal = false">إلغاء</Button>
                    <Button type="button" @click="confirmDelete" class="bg-red-600 hover:bg-red-700">حذف</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
