<script setup lang="ts">
import { TrendingUp, Users, Calendar, Activity } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
}>();

const loading = ref(false);
const analytics = ref<any>(null);
const error = ref<string | null>(null);

const fetchAnalytics = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch('/employee/activity-logs/analytics', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('فشل تحميل التحليلات');
        }

        const data = await response.json();
        analytics.value = data.data;
    } catch (err: any) {
        error.value = err.message;
    } finally {
        loading.value = false;
    }
};

watch(() => props.open, (isOpen) => {
    if (isOpen && !analytics.value && !loading.value) {
        fetchAnalytics();
    }
});

const closeModal = () => {
    emit('update:open', false);
    emit('close');
};

const getEventColor = (type: string) => {
    const colors: Record<string, string> = {
        created: 'bg-green-100 text-green-800',
        updated: 'bg-blue-100 text-blue-800',
        deleted: 'bg-red-100 text-red-800',
        restored: 'bg-yellow-100 text-yellow-800',
        login: 'bg-purple-100 text-purple-800',
        logout: 'bg-gray-100 text-gray-800',
    };

    return colors[type] || 'bg-orange-100 text-orange-800';
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="sm:max-w-4xl max-h-[85vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <TrendingUp class="h-5 w-5 text-orange-500" />
                    تحليلات النشاطات
                </DialogTitle>
                <DialogDescription>
                    إحصائيات واتجاهات نشاطات النظام
                </DialogDescription>
            </DialogHeader>

            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
            </div>

            <div v-else-if="error" class="text-center text-red-500 py-8">
                {{ error }}
            </div>

            <div v-else-if="analytics" class="space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl text-center">
                        <Activity class="h-6 w-6 text-blue-600 mx-auto mb-2" />
                        <p class="text-2xl font-bold">{{ analytics.total_activities }}</p>
                        <p class="text-sm text-gray-600">إجمالي النشاطات</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl text-center">
                        <Calendar class="h-6 w-6 text-green-600 mx-auto mb-2" />
                        <p class="text-2xl font-bold">{{ analytics.activities_today }}</p>
                        <p class="text-sm text-gray-600">نشاطات اليوم</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-xl text-center">
                        <Calendar class="h-6 w-6 text-purple-600 mx-auto mb-2" />
                        <p class="text-2xl font-bold">{{ analytics.activities_this_week }}</p>
                        <p class="text-sm text-gray-600">آخر 7 أيام</p>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-xl text-center">
                        <Calendar class="h-6 w-6 text-orange-600 mx-auto mb-2" />
                        <p class="text-2xl font-bold">{{ analytics.activities_this_month }}</p>
                        <p class="text-sm text-gray-600">آخر 30 يوم</p>
                    </div>
                </div>

                <!-- Top Users -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-semibold flex items-center gap-2 mb-3">
                        <Users class="h-5 w-5 text-blue-600" />
                        أكثر المستخدمين نشاطاً
                    </h3>
                    <div class="space-y-2">
                        <div v-for="user in analytics.top_users" :key="user.id"
                            class="flex justify-between items-center border-b pb-2">
                            <span>{{ user.name }}</span>
                            <Badge>{{ user.activity_count }} نشاط</Badge>
                        </div>
                        <div v-if="!analytics.top_users?.length" class="text-gray-500 text-sm">
                            لا توجد بيانات
                        </div>
                    </div>
                </div>

                <!-- Popular Models -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-semibold flex items-center gap-2 mb-3">
                        <Activity class="h-5 w-5 text-green-600" />
                        النماذج الأكثر استخداماً
                    </h3>
                    <div class="space-y-2">
                        <div v-for="model in analytics.popular_models" :key="model.type"
                            class="flex justify-between items-center border-b pb-2">
                            <span>{{ model.name }}</span>
                            <Badge>{{ model.activity_count }} نشاط</Badge>
                        </div>
                        <div v-if="!analytics.popular_models?.length" class="text-gray-500 text-sm">
                            لا توجد بيانات
                        </div>
                    </div>
                </div>

                <!-- Timeline Chart (simplified bar representation) -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-semibold flex items-center gap-2 mb-3">
                        <TrendingUp class="h-5 w-5 text-purple-600" />
                        النشاطات اليومية (آخر 30 يوم)
                    </h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div v-for="day in analytics.timeline" :key="day.date" class="flex items-center gap-2">
                            <span class="w-24 text-sm">{{ day.date }} ({{ day.day_name }})</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" :style="{ width: day.percentage + '%' }">
                                </div>
                            </div>
                            <span class="text-sm font-medium w-8">{{ day.count }}</span>
                        </div>
                        <div v-if="!analytics.timeline?.length" class="text-gray-500 text-sm">
                            لا توجد بيانات
                        </div>
                    </div>
                </div>

                <!-- Event Types (if any) -->
                <div v-if="analytics.event_types?.length" class="border rounded-xl p-4">
                    <h3 class="font-semibold mb-3">توزيع أنواع الأحداث</h3>
                    <div class="space-y-2">
                        <div v-for="type in analytics.event_types" :key="type.name"
                            class="flex justify-between items-center">
                            <Badge :class="getEventColor(type.name)">{{ type.name }}</Badge>
                            <span>{{ type.count }} ({{ type.percentage }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <Button variant="outline" @click="closeModal">إغلاق</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
