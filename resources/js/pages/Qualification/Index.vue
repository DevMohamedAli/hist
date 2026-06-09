<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Award,
    BookOpenCheck,
    CalendarDays,
    CheckCircle2,
    Edit3,
    FileBadge,
    Plus,
    Search,
    Trash2,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import QualificationModal from './components/QualificationModal.vue';

defineOptions({
    layout: AuthenticatedLayout,
});

interface Qualification {
    id: number;
    degree_name: string;
    institution: string;
    created_at?: string | null;
}

interface PageProps {
    flash?: { success?: string; error?: string };
    qualifications: Qualification[];
    [key: string]: unknown;
}

const page = usePage<PageProps>();

const qualifications = computed(() => page.props.qualifications || []);
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingItem = ref<Qualification | null>(null);
const showDeleteModal = ref(false);
const deletingItem = ref<Qualification | null>(null);

const filteredQualifications = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return qualifications.value;
    }

    return qualifications.value.filter((qualification) => [
        qualification.degree_name,
        qualification.institution,
    ].some((value) => (value || '').toLowerCase().includes(term)));
});

const totalCount = computed(() => qualifications.value.length);
const institutionsCount = computed(() => new Set(
    qualifications.value
        .map((qualification) => qualification.institution?.trim())
        .filter(Boolean),
).size);
const recentYear = computed(() => {
    const dates = qualifications.value
        .map((qualification) => qualification.created_at ? new Date(qualification.created_at).getFullYear() : null)
        .filter((year): year is number => Boolean(year));

    return dates.length ? Math.max(...dates) : null;
});

const openCreateModal = () => {
    editingItem.value = null;
    showCreateModal.value = true;
};

const openEditModal = (item: Qualification) => {
    editingItem.value = item;
    showEditModal.value = true;
};

const openDeleteModal = (item: Qualification) => {
    deletingItem.value = item;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deletingItem.value) {
        return;
    }

    router.delete(`/qualifications/${deletingItem.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            deletingItem.value = null;
        },
    });
};

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <Head title="المؤهلات" />

    <div class="min-h-screen bg-slate-50" dir="rtl">
        <section class="border-b bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm font-semibold text-orange-600">
                            <FileBadge class="h-4 w-4" />
                            وحدة المؤهلات
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-950 md:text-3xl">جدول المؤهلات</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                إدارة المؤهلات كبيانات مستقلة يمكن استخدامها لاحقاً مع الطلاب أو المحاضرين أو الموظفين.
                            </p>
                        </div>
                    </div>

                    <Button class="gap-2 bg-blue-700 text-white hover:bg-blue-800" @click="openCreateModal">
                        <Plus class="h-4 w-4" />
                        إضافة مؤهل
                    </Button>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-lg border bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">إجمالي المؤهلات</p>
                            <Award class="h-5 w-5 text-blue-700" />
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ totalCount }}</p>
                    </div>
                    <div class="rounded-lg border bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">المؤسسات المانحة</p>
                            <BookOpenCheck class="h-5 w-5 text-emerald-700" />
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ institutionsCount }}</p>
                    </div>
                    <div class="rounded-lg border bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">أحدث إضافة</p>
                            <CalendarDays class="h-5 w-5 text-orange-600" />
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ recentYear || '—' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-emerald-700">
                    <CheckCircle2 class="h-5 w-5" />
                    {{ successMessage }}
                </div>
            </div>

            <div v-if="errorMessage" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-red-700">
                    <XCircle class="h-5 w-5" />
                    {{ errorMessage }}
                </div>
            </div>

            <div class="rounded-lg border bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b p-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="font-bold text-slate-950">قائمة المؤهلات</h2>
                        <p class="text-sm text-slate-500">ابحث وعدّل المؤهلات المسجلة في جدول النظام العام.</p>
                    </div>

                    <div class="relative w-full md:w-96">
                        <Search class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <Input
                            v-model="search"
                            class="pr-9"
                            placeholder="بحث باسم المؤهل أو المؤسسة..."
                        />
                        <button
                            v-if="search"
                            type="button"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                            @click="clearSearch"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-slate-50">
                                <TableHead class="text-right">المؤهل</TableHead>
                                <TableHead class="text-right">المؤسسة</TableHead>
                                <TableHead class="text-center">الإجراءات</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="qualification in filteredQualifications" :key="qualification.id">
                                <TableCell class="font-semibold text-slate-950">
                                    {{ qualification.degree_name }}
                                </TableCell>
                                <TableCell>{{ qualification.institution }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-center gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-blue-700 hover:bg-blue-50"
                                            @click="openEditModal(qualification)"
                                        >
                                            <Edit3 class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-red-600 hover:bg-red-50"
                                            @click="openDeleteModal(qualification)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="filteredQualifications.length === 0">
                                <TableCell colspan="3" class="py-14 text-center text-slate-500">
                                    لا توجد مؤهلات مطابقة للبحث الحالي.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="divide-y md:hidden">
                    <div
                        v-for="qualification in filteredQualifications"
                        :key="qualification.id"
                        class="p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-950">{{ qualification.degree_name }}</p>
                                <p class="text-sm text-slate-500">{{ qualification.institution }}</p>
                            </div>
                            <div class="flex gap-1">
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-700" @click="openEditModal(qualification)">
                                    <Edit3 class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600" @click="openDeleteModal(qualification)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredQualifications.length === 0" class="p-10 text-center text-sm text-slate-500">
                        لا توجد مؤهلات مطابقة للبحث الحالي.
                    </div>
                </div>
            </div>
        </main>

        <QualificationModal
            :open="showCreateModal"
            :item="null"
            @close="showCreateModal = false"
            @success="showCreateModal = false"
        />
        <QualificationModal
            :open="showEditModal"
            :item="editingItem"
            @close="showEditModal = false; editingItem = null"
            @success="showEditModal = false; editingItem = null"
        />

        <Dialog v-model:open="showDeleteModal">
            <DialogContent class="sm:max-w-md" dir="rtl">
                <DialogHeader class="text-right">
                    <DialogTitle class="flex items-center gap-2 text-red-600">
                        <Trash2 class="h-5 w-5" />
                        حذف المؤهل
                    </DialogTitle>
                    <DialogDescription>
                        هل تريد حذف مؤهل "{{ deletingItem?.degree_name }}"؟ لا يمكن التراجع عن هذا الإجراء.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showDeleteModal = false">
                        إلغاء
                    </Button>
                    <Button class="bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        حذف
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
