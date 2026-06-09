<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Layers3, PlusCircle, Pencil, Trash2 } from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({
    layout: [],
});

interface Department {
    id: number;
    name: string;
}

interface Specialization {
    id: number;
    name: string;
    code: string | null;
    description: string | null;
    department: Department | null;
}

interface Props {
    specializations: Specialization[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="قائمة الشعب الدراسية" />

    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
            <div class="mx-auto max-w-6xl space-y-6">
                <!-- Header Section -->
                <section
                    class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md"
                >
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="text-start">
                            <p class="text-sm font-semibold text-orange-500">
                                الوحدة الأكاديمية
                            </p>
                            <h1 class="mt-1 text-2xl font-bold text-blue-800">
                                قائمة الشعب الدراسية
                            </h1>
                            <p class="mt-2 text-sm text-gray-600">
                                استعراض الشعب وتفاصيلها المرتبطة بالأقسام.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-800"
                        >
                            <Layers3 class="h-6 w-6" />
                            <div class="text-start">
                                <p class="text-xs font-medium text-gray-500">
                                    إجمالي الشعب
                                </p>
                                <p class="text-xl font-bold">
                                    {{ props.specializations.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Toolbar with Add button -->
                <div class="flex justify-end">
                    <Link
                        href="/academic/specializations/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600"
                    >
                        <PlusCircle class="h-5 w-5" />
                        <span>إضافة شعبة جديدة</span>
                    </Link>
                </div>

                <!-- Specializations Table -->
                <section class="rounded-lg bg-white shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-160 text-start text-sm">
                            <thead class="bg-blue-800 text-white">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        اسم الشعبة
                                    </th>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        القسم
                                    </th>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        الرمز
                                    </th>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        عدد السمسترات
                                    </th>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        الوصف
                                    </th>
                                    <th
                                        class="px-6 py-4 text-center font-semibold"
                                    >
                                        إجراءات
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="(
                                        spec, index
                                    ) in props.specializations"
                                    :key="spec.id"
                                    class="transition hover:bg-orange-50/60"
                                >
                                    <td
                                        class="px-6 py-4 font-bold text-blue-800"
                                    >
                                        {{ index + 1 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-semibold text-gray-900"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Layers3
                                                class="h-5 w-5 text-orange-500"
                                            />
                                            <span>{{ spec.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ spec.department?.name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <span
                                            v-if="spec.code"
                                            class="rounded bg-gray-100 px-2 py-1 font-mono text-xs"
                                            >{{ spec.code }}</span
                                        >
                                        <span v-else class="text-gray-400"
                                            >—</span
                                        >
                                    </td>
                                    <td
                                        class="max-w-xs truncate px-6 py-4 text-gray-700"
                                    >
                                        {{ spec.description ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            class="flex items-center justify-center gap-2"
                                        >
                                            <Link
                                                :href="`/academic/specializations/${spec.id}/edit`"
                                                class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 hover:bg-blue-100"
                                            >
                                                <Pencil class="h-3.5 w-3.5" />
                                                تعديل
                                            </Link>
                                            <Link
                                                :href="`/academic/specializations/${spec.id}`"
                                                method="delete"
                                                as="button"
                                                class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-100"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                                حذف
                                            </Link>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="props.specializations.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-10 text-center text-gray-500"
                                    >
                                        لا توجد شعب مسجلة حتى الآن.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
