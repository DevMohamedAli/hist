<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Layers,
    PlusCircle,
    Pencil,
    Trash2,
    Eye,
    Users,
    Building2,
} from 'lucide-vue-next';

// ── Types ──────────────────────────────────────────────
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
    code: string; // e.g. "ربيع 2025-2026"
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

interface Props {
    studyGroups: StudyGroup[];
    specializations?: Specialization[];
    semesters?: AcademicSemester[];
}

const props = withDefaults(defineProps<Props>(), {
    specializations: () => [],
    semesters: () => [],
});

// ── Delete handler ─────────────────────────────────────
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
            <!-- Header -->
            <section
                class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-orange-500">
                            الوحدة الأكاديمية
                        </p>
                        <h1 class="mt-1 text-2xl font-bold text-blue-800">
                            المجموعات التدريسية
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            إدارة مجموعات الطلاب حسب التخصص والمستوى والفصل.
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-800"
                    >
                        <Users class="h-6 w-6" />
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                إجمالي المجموعات
                            </p>
                            <p class="text-xl font-bold">
                                {{ props.studyGroups.length }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Create Button -->
            <div class="flex justify-end">
                <Link
                    href="/academic/study-groups/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600"
                >
                    <PlusCircle class="h-5 w-5" />
                    <span>إضافة مجموعة جديدة</span>
                </Link>
            </div>

            <!-- Table -->
            <section class="overflow-hidden rounded-lg bg-white shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-225 text-start text-sm">
                        <thead class="bg-blue-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-start font-semibold">
                                    #
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    المجموعة
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    التخصص
                                </th>
                                <th class="px-6 py-4 text-start font-semibold">
                                    الفصل
                                </th>
                                <th class="px-6 py-4 text-center font-semibold">
                                    المستوى
                                </th>
                                <th class="px-6 py-4 text-center font-semibold">
                                    الطاقة
                                </th>
                                <th class="px-6 py-4 text-center font-semibold">
                                    المسجلين
                                </th>
                                <th class="px-6 py-4 text-center font-semibold">
                                    الإجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="(group, index) in props.studyGroups"
                                :key="group.id"
                                class="transition hover:bg-orange-50/60"
                            >
                                <td class="px-6 py-4 font-bold text-blue-800">
                                    {{ index + 1 }}
                                </td>
                                <td
                                    class="px-6 py-4 font-semibold text-gray-900"
                                >
                                    <div class="flex items-center gap-2">
                                        <Layers
                                            class="h-5 w-5 text-orange-500"
                                        />
                                        <span>{{ group.group_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <div class="flex items-center gap-1.5">
                                        <Building2
                                            class="h-4 w-4 text-gray-400"
                                        />
                                        {{ group.specialization?.name ?? '—' }}
                                        <span
                                            v-if="
                                                group.specialization?.department
                                            "
                                            class="text-xs text-gray-500"
                                        >
                                            ({{
                                                group.specialization.department
                                                    .name
                                            }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ group.academic_semester?.code ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold">
                                    {{ group.semester_level }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ group.capacity }}
                                </td>
                                <td
                                    class="px-6 py-4 text-center font-bold text-blue-800"
                                >
                                    {{ group.enrollments_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <Link
                                            :href="`/academic/study-groups/${group.id}`"
                                            class="inline-flex items-center gap-1 rounded bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-200"
                                            title="عرض"
                                        >
                                            <Eye class="h-3.5 w-3.5" /> عرض
                                        </Link>
                                        <Link
                                            :href="`/academic/study-groups/${group.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 transition hover:bg-blue-100"
                                            title="تعديل"
                                        >
                                            <Pencil class="h-3.5 w-3.5" /> تعديل
                                        </Link>
                                        <button
                                            type="button"
                                            @click="
                                                confirmDelete(
                                                    group.id,
                                                    group.group_name,
                                                )
                                            "
                                            class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100"
                                            title="حذف"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" /> حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="props.studyGroups.length === 0">
                                <td
                                    colspan="8"
                                    class="px-6 py-10 text-center text-gray-500"
                                >
                                    لا توجد مجموعات تدريسية حتى الآن.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</template>
