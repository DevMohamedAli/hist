<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    BookOpenCheck,
    Layers3,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import MinistryNewsFeed, { type MinistryNewsItem } from '@/components/MinistryNewsFeed.vue';

interface Instructor {
    name: string;
    employee_id?: string | null;
}

interface ClassItem {
    id: number;
    group_name: string;
    student_count: number;
    course?: { name: string; code?: string; units?: number };
    semester?: { code: string };
    study_group?: {
        group_name: string;
        semester_level?: number;
        specialization?: { name: string };
    } | null;
}

defineProps<{
    instructor: Instructor;
    classes: ClassItem[];
    ministryNews?: MinistryNewsItem[];
}>();
</script>

<template>
    <Head title="لوحة عضو هيئة التدريس" />

    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <UserCheck class="h-12 w-12 text-orange-500" />
                        <div>
                            <p class="text-sm font-bold text-orange-500">
                                بوابة عضو هيئة التدريس
                            </p>
                            <h1 class="text-2xl font-extrabold text-blue-800">
                                {{ instructor.name }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600">
                                الرقم الوظيفي:
                                {{ instructor.employee_id ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <MinistryNewsFeed :items="ministryNews" />

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <BookOpenCheck class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">الشعب المسندة</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{ classes.length }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <Users class="mb-3 h-6 w-6 text-orange-500" />
                    <p class="text-sm text-gray-500">إجمالي الطلاب</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{
                            classes.reduce(
                                (total, item) =>
                                    total + (item.student_count ?? 0),
                                0,
                            )
                        }}
                    </p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <Layers3 class="mb-3 h-6 w-6 text-blue-800" />
                    <p class="text-sm text-gray-500">المجموعات الدراسية</p>
                    <p class="text-2xl font-extrabold text-blue-800">
                        {{
                            new Set(
                                classes.map(
                                    (item) =>
                                        item.study_group?.group_name ??
                                        item.group_name,
                                ),
                            ).size
                        }}
                    </p>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b p-5">
                    <h2 class="text-xl font-bold text-blue-800">
                        الشعب الجاهزة لرصد الدرجات
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        تظهر هنا الشعب المسندة لك فقط حسب المقرر والمجموعة
                        الدراسية والفصل.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead class="bg-blue-800 text-white">
                            <tr>
                                <th class="px-5 py-3 text-start">المقرر</th>
                                <th class="px-5 py-3 text-start">
                                    المجموعة الدراسية
                                </th>
                                <th class="px-5 py-3 text-center">التخصص</th>
                                <th class="px-5 py-3 text-center">الفصل</th>
                                <th class="px-5 py-3 text-center">الطلاب</th>
                                <th class="px-5 py-3 text-center">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="classItem in classes"
                                :key="classItem.id"
                                class="hover:bg-orange-50/50"
                            >
                                <td class="px-5 py-3">
                                    <p class="font-bold text-gray-900">
                                        {{ classItem.course?.name ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ classItem.course?.code ?? '-' }} ·
                                        {{ classItem.course?.units ?? 0 }} وحدات
                                    </p>
                                </td>
                                <td class="px-5 py-3 font-bold text-blue-800">
                                    {{
                                        classItem.study_group?.group_name ??
                                        classItem.group_name
                                    }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{
                                        classItem.study_group?.specialization
                                            ?.name ?? '-'
                                    }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{ classItem.semester?.code ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{ classItem.student_count }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <Link
                                        :href="`/teacher/classes/${classItem.id}/grades`"
                                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 focus:outline-none"
                                    >
                                        <Award class="h-4 w-4" />
                                        رصد الدرجات
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="classes.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-10 text-center text-gray-500"
                                >
                                    لا توجد شعب مسندة لك حاليا. يرجى التواصل مع
                                    الموظف المختص لإسناد المقررات.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</template>
