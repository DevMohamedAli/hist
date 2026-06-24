<script setup lang="ts">
import { BookOpen, CalendarDays, FileSpreadsheet, GraduationCap, Users } from 'lucide-vue-next';
import type { Component } from 'vue';

interface SupportedType {
    value: string;
    label: string;
}

defineProps<{
    supportedTypes: SupportedType[];
}>();

const model = defineModel<string>({ required: true });

const icons: Record<string, Component> = {
    courses: BookOpen,
    exam_schedules: CalendarDays,
    grade_workbook: FileSpreadsheet,
    grades: GraduationCap,
    instructors: GraduationCap,
    students: Users,
};
</script>

<template>
    <section class="grid gap-3 md:grid-cols-3 xl:grid-cols-5">
        <button
            v-for="type in supportedTypes"
            :key="type.value"
            type="button"
            class="group flex min-h-24 items-center gap-3 rounded-lg border bg-white p-4 text-right shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
            :class="
                model === type.value
                    ? 'border-blue-700 ring-2 ring-blue-700/10'
                    : 'border-gray-200'
            "
            @click="model = type.value"
        >
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md"
                :class="
                    model === type.value
                        ? 'bg-blue-700 text-white'
                        : 'bg-blue-50 text-blue-800'
                "
            >
                <component :is="icons[type.value] ?? Users" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-gray-900">
                    {{ type.label }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{
                        model === type.value ? 'محدد الآن' : 'اختر نوع البيانات'
                    }}
                </p>
            </div>
        </button>
    </section>
</template>
