<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, GraduationCap } from 'lucide-vue-next';

defineProps<{
    specialization: {
        id: number;
        name: string;
        code?: string | null;
        description?: string | null;
        department?: { name: string };
    };
}>();
</script>

<template>
    <Head :title="specialization.name" />
    <main
        class="min-h-screen bg-gray-50 p-4 font-['Cairo'] sm:p-6 lg:p-8"
        dir="rtl"
    >
        <section
            class="mx-auto max-w-4xl rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
        >
            <Link
                href="/academic/specializations"
                class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-500"
                ><ChevronRight class="h-4 w-4" /> العودة للتخصصات</Link
            >
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-orange-500">
                        {{ specialization.department?.name ?? 'قسم غير محدد' }}
                    </p>
                    <h1 class="text-2xl font-extrabold text-blue-800">
                        {{ specialization.name }}
                    </h1>
                </div>
                <Link
                    :href="`/academic/specializations/${specialization.id}/edit`"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600"
                    >تعديل</Link
                >
            </div>
            <div class="mt-8 rounded-xl bg-blue-50 p-5">
                <GraduationCap class="mb-2 h-6 w-6 text-blue-800" />
                <p class="text-sm text-gray-500">الوصف</p>
                <p class="font-semibold text-gray-800">
                    {{ specialization.description || 'لا يوجد وصف مسجل.' }}
                </p>
            </div>
        </section>
    </main>
</template>
