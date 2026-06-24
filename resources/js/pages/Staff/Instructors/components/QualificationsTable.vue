<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Edit3, Plus, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Qualification {
    id: number;
    degree_name: string;
    institution: string;
}

const props = defineProps<{
    instructorId: number;
    qualifications: Qualification[];
}>();

const emit = defineEmits<{
    add: [];
    edit: [qualification: Qualification];
}>();

const deleteQualification = (id: number) => {
    if (!confirm('هل أنت متأكد من حذف هذا المؤهل العلمي؟')) {
        return;
    }

    router.delete(`/qualifications/${id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-blue-900">
                    المؤهلات العلمية
                </h3>
                <p class="text-xs text-slate-500">
                    يعرض الجدول اسم المؤهل والمؤسسة المانحة فقط.
                </p>
            </div>
            <Button
                class="gap-2 bg-blue-800 text-white hover:bg-blue-900"
                @click="emit('add')"
            >
                <Plus class="h-4 w-4" />
                إضافة مؤهل
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm"
        >
            <table class="w-full text-right text-sm">
                <thead class="bg-blue-50 text-blue-900">
                    <tr>
                        <th class="px-4 py-3 font-semibold">اسم المؤهل</th>
                        <th class="px-4 py-3 font-semibold">المؤسسة المانحة</th>
                        <th class="px-4 py-3 text-center font-semibold">
                            الإجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    <tr
                        v-for="qualification in qualifications"
                        :key="qualification.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ qualification.degree_name }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ qualification.institution }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-blue-700 hover:text-blue-900"
                                    @click="emit('edit', qualification)"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-red-600 hover:text-red-800"
                                    @click="
                                        deleteQualification(qualification.id)
                                    "
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="qualifications.length === 0">
                        <td
                            colspan="3"
                            class="px-4 py-8 text-center text-slate-500"
                        >
                            لا توجد مؤهلات علمية مسجلة لهذا المحاضر.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
