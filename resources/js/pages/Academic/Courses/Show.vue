<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronRight,
    GraduationCap,
    FlaskConical,
    Hash,
    Layers,
    Building2,
    BookMarked,
} from 'lucide-vue-next';

// ── Types ──────────────────────────────────────────────
interface Department {
    id: number;
    name: string;
}

interface Specialization {
    id: number;
    name: string;
    code: string;
    department?: Department;
    pivot?: {
        semester_level: number;
    };
}

interface Course {
    id: number;
    name: string;
    code: string;
    units: number;
    has_practical: boolean;
    // Removed prerequisite_id, added prerequisites array
    prerequisites?: Course[];
    specializations: Specialization[];
}

defineProps<{
    course: Course;
}>();
</script>

<template>
    <Head :title="course.name" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Back link -->
            <Link
                href="/academic/courses"
                class="inline-flex items-center gap-2 text-sm font-bold text-blue-800 transition hover:text-orange-500"
            >
                <ChevronRight class="h-4 w-4" />
                العودة إلى المقررات
            </Link>

            <!-- Main course card -->
            <section
                class="rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <!-- Header -->
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-bold text-orange-500">
                            {{ course.code }}
                        </p>
                        <h1 class="text-2xl font-extrabold text-blue-800">
                            {{ course.name }}
                        </h1>
                    </div>
                    <Link
                        :href="`/academic/courses/${course.id}/edit`"
                        class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-orange-600"
                    >
                        تعديل
                    </Link>
                </div>

                <!-- Quick stats (now includes prerequisites count) -->
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-blue-50 p-5">
                        <Hash class="mb-2 h-5 w-5 text-blue-800" />
                        <p class="text-sm text-gray-500">الوحدات</p>
                        <p class="text-lg font-bold text-blue-800">
                            {{ course.units }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-5">
                        <FlaskConical class="mb-2 h-5 w-5 text-blue-800" />
                        <p class="text-sm text-gray-500">عملي</p>
                        <p class="text-lg font-bold text-blue-800">
                            {{ course.has_practical ? 'نعم' : 'لا' }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-5">
                        <BookMarked class="mb-2 h-5 w-5 text-blue-800" />
                        <p class="text-sm text-gray-500">المتطلبات السابقة</p>
                        <p class="text-lg font-bold text-blue-800">
                            {{ course.prerequisites?.length || 0 }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Prerequisites section (new) -->
            <section class="rounded-xl bg-white p-6 shadow-md">
                <h2
                    class="mb-4 flex items-center gap-2 text-xl font-bold text-blue-800"
                >
                    <BookOpen class="h-6 w-6" />
                    المتطلبات السابقة
                    <span class="text-sm font-normal text-gray-500">
                        ({{ course.prerequisites?.length || 0 }})
                    </span>
                </h2>

                <div
                    v-if="
                        course.prerequisites && course.prerequisites.length > 0
                    "
                >
                    <ul class="space-y-3">
                        <li
                            v-for="prereq in course.prerequisites"
                            :key="prereq.id"
                            class="flex items-center justify-between rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50"
                        >
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ prereq.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    الكود: {{ prereq.code }}
                                </p>
                            </div>
                            <Link
                                :href="`/academic/courses/${prereq.id}`"
                                class="rounded-md bg-orange-100 px-3 py-1.5 text-xs font-bold text-orange-700 transition hover:bg-orange-200"
                            >
                                عرض المقرر
                            </Link>
                        </li>
                    </ul>
                </div>
                <div v-else class="py-10 text-center text-gray-500">
                    <p>لا توجد متطلبات سابقة لهذا المقرر.</p>
                </div>
            </section>

            <!-- Specializations section -->
            <section class="rounded-xl bg-white p-6 shadow-md">
                <h2
                    class="mb-4 flex items-center gap-2 text-xl font-bold text-blue-800"
                >
                    <Layers class="h-6 w-6" />
                    التخصصات المرتبطة
                    <span class="text-sm font-normal text-gray-500">
                        ({{ course.specializations.length }})
                    </span>
                </h2>

                <div v-if="course.specializations.length > 0">
                    <ul class="space-y-3">
                        <li
                            v-for="spec in course.specializations"
                            :key="spec.id"
                            class="flex flex-col justify-between gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50 sm:flex-row sm:items-center"
                        >
                            <div class="flex items-center gap-3">
                                <div class="rounded-full bg-orange-100 p-2">
                                    <GraduationCap
                                        class="h-5 w-5 text-orange-500"
                                    />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ spec.name }}
                                    </p>
                                    <div
                                        class="flex items-center gap-1 text-sm text-gray-500"
                                    >
                                        <Building2 class="h-4 w-4" />
                                        {{ spec.department?.name ?? '—' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"
                                >
                                    الفصل الدراسي:
                                    {{ spec.pivot?.semester_level ?? '—' }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-else class="py-10 text-center text-gray-500">
                    <p>لا توجد تخصصات مرتبطة بهذا المقرر.</p>
                </div>
            </section>
        </div>
    </main>
</template>
