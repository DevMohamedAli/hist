<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, GraduationCap } from 'lucide-vue-next';

interface Specialization {
    id: number;
    name: string;
}
defineProps<{
    department: {
        id: number;
        name: string;
        code?: string | null;
        description?: string | null;
        specializations?: Specialization[];
    };
}>();
</script>

<template>
    <Head :title="department.name" />
    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <section
            class="mx-auto max-w-4xl rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
        >
            <Link
                href="/academic/departments"
                class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-500"
                ><ChevronRight class="h-4 w-4" /> العودة للأقسام</Link
            >
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-orange-500">قسم علمي</p>
                    <h1 class="text-2xl font-extrabold text-blue-800">
                        {{ department.name }}
                    </h1>
                </div>
                <Link
                    :href="`/academic/departments/${department.id}/edit`"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600"
                    >تعديل</Link
                >
            </div>
            <p class="mt-4 rounded-xl bg-blue-50 p-4 text-gray-700">
                {{ department.description || 'لا يوجد وصف مسجل.' }}
            </p>
            <div class="mt-6">
                <h2 class="mb-3 text-lg font-bold text-blue-800">
                    التخصصات التابعة
                </h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="spec in department.specializations"
                        :key="spec.id"
                        class="flex items-center gap-2 rounded-lg border p-3"
                    >
                        <GraduationCap class="h-5 w-5 text-orange-500" />{{
                            spec.name
                        }}
                    </div>
                    <p
                        v-if="!department.specializations?.length"
                        class="text-gray-500"
                    >
                        لا توجد تخصصات تابعة لهذا القسم.
                    </p>
                </div>
            </div>
        </section>
    </main>
</template>
