<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Layers3, PlusCircle } from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({
    layout: [],
});

interface Specialization {
    id: number;
    name: string;
}

interface Department {
    id: number;
    name: string;
    specializations?: Specialization[];
}

interface Props {
    departments: Department[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/academic/departments', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="إدارة الأقسام العلمية" />

    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
            <div class="mx-auto max-w-6xl space-y-6">
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
                                إدارة الأقسام العلمية
                            </h1>
                            <p class="mt-2 text-sm text-gray-600">
                                إضافة الأقسام الرئيسية ومتابعة التخصصات التابعة
                                لها.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-800"
                        >
                            <Building2 class="h-6 w-6" />
                            <div class="text-start">
                                <p class="text-xs font-medium text-gray-500">
                                    إجمالي الأقسام
                                </p>
                                <p class="text-xl font-bold">
                                    {{ props.departments.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6 shadow-md">
                    <div class="mb-6 border-b border-gray-200 pb-4 text-start">
                        <h2 class="text-xl font-bold text-gray-900">
                            إضافة قسم جديد
                        </h2>
                    </div>

                    <form
                        class="grid grid-cols-1 gap-6 md:grid-cols-[1fr_auto]"
                        @submit.prevent="submit"
                    >
                        <div>
                            <label
                                for="name"
                                class="block text-start text-sm font-semibold text-gray-700"
                            >
                                اسم القسم العلمي
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-start text-gray-900 shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 focus:outline-none"
                                placeholder="مثال: قسم الحاسوب"
                                required
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-start text-sm text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="flex items-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600 focus:ring-2 focus:ring-orange-500/30 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 md:w-auto"
                            >
                                <PlusCircle class="h-5 w-5" />
                                <span>
                                    {{
                                        form.processing
                                            ? 'جاري الحفظ...'
                                            : 'إضافة القسم'
                                    }}
                                </span>
                            </button>
                        </div>
                    </form>
                </section>

                <section class="rounded-lg bg-white shadow-md">
                    <div
                        class="flex flex-col gap-2 border-b border-gray-200 p-6 text-start md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                قائمة الأقسام العلمية
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                عرض الأقسام والتخصصات المرتبطة بها.
                            </p>
                        </div>
                    </div>

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
                                        اسم القسم
                                    </th>
                                    <th
                                        class="px-6 py-4 text-center font-semibold"
                                    >
                                        عدد التخصصات
                                    </th>
                                    <th
                                        class="px-6 py-4 text-start font-semibold"
                                    >
                                        التخصصات
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="(
                                        department, index
                                    ) in props.departments"
                                    :key="department.id"
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
                                            <Building2
                                                class="h-5 w-5 text-orange-500"
                                            />
                                            <span>{{ department.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800"
                                        >
                                            <Layers3 class="h-4 w-4" />
                                            {{
                                                department.specializations
                                                    ?.length || 0
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-start text-gray-700"
                                    >
                                        <div
                                            v-if="
                                                department.specializations &&
                                                department.specializations
                                                    .length > 0
                                            "
                                            class="flex flex-wrap gap-2"
                                        >
                                            <span
                                                v-for="specialization in department.specializations"
                                                :key="specialization.id"
                                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700"
                                            >
                                                {{ specialization.name }}
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-400">
                                            لا توجد تخصصات مرتبطة
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="props.departments.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-10 text-center text-gray-500"
                                    >
                                        لا توجد أقسام مسجلة حتى الآن.
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
