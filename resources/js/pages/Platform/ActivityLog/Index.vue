<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Filter, Download, BarChart3, Activity, Users, CalendarDays,
    Sparkles, TrendingUp, ChevronDown, ChevronUp, RefreshCw
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import ActivityDetailModal from './components/ActivityDetailModal.vue';
import ActivityTable from './components/ActivityTable.vue';
import AnalyticsModal from './components/AnalyticsModal.vue';
import FilterPanel from './components/FilterPanel.vue';

interface Activity {
    id: number;
    description: string;
    event: string;
    causer: { id: number; name: string; email: string } | null;
    subject_type: string | null;
    subject_id: number | null;
    properties: any;
    created_at: string;
}

interface PaginatedActivities {
    data: Activity[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = withDefaults(defineProps<{
    activities: PaginatedActivities;
    filters: {
        search?: string;
        event?: string;
        causer_id?: number | string;
        date_from?: string;
        date_to?: string;
    };
    eventTypes: string[];
    causers: Array<{ id: number; name: string; email: string }>;
    specializations?: Array<{ id: number; name: string }>;
}>(), {
    specializations: () => [],
});

const selectedActivity = ref<Activity | null>(null);
const showDetailModal = ref(false);
const showAnalyticsModal = ref(false);
const filterExpanded = ref(true);
const isScrolled = ref(false);

// Statistics derived from activities
const totalActivities = computed(() => props.activities.total);
const todayCount = computed(() => {
    const today = new Date().toISOString().slice(0, 10);

    return props.activities.data.filter(a => a.created_at.startsWith(today)).length;
});

// Scroll listener for floating button
const handleScroll = () => {
    isScrolled.value = window.scrollY > 200;
};
onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

const openDetail = (activity: Activity) => {
    selectedActivity.value = activity;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedActivity.value = null;
};

const exportCSV = () => {
    const params = new URLSearchParams();
    params.append('format', 'csv');
    params.append('filters', JSON.stringify(props.filters));
    window.location.href = `/employee/activity-logs/export?${params.toString()}`;
};

const openAnalytics = () => {
    showAnalyticsModal.value = true;
};

const refresh = () => router.reload({ only: ['activities', 'filters'] });
</script>

<template>

    <Head title="سجل النشاطات" />

    <div class="activity-log-page min-h-screen bg-linear-to-br from-slate-50 to-blue-50/30 dark:from-gray-900 dark:to-gray-800 p-4 md:p-6"
        dir="rtl">
        <!-- Animated gradient header -->
        <div
            class="relative mb-8 overflow-hidden rounded-2xl bg-linear-to-l from-blue-900 via-blue-800 to-orange-600 p-6 shadow-xl">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-size-[20px_20px]">
            </div>
            <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-3xl font-extrabold text-white drop-shadow-md">
                        <Sparkles class="h-7 w-7 text-yellow-300 animate-pulse" />
                        سجل النشاطات
                    </h1>
                    <p class="mt-1 text-blue-100">مراقبة وتحليل جميع نشاطات النظام بلحظة</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="secondary" @click="openAnalytics"
                        class="gap-2 bg-white/20 text-white backdrop-blur hover:bg-white/30">
                        <BarChart3 class="h-4 w-4" />
                        تحليلات
                    </Button>
                    <Button @click="exportCSV"
                        class="gap-2 bg-orange-500 text-white shadow-lg hover:bg-orange-600 transition-all">
                        <Download class="h-4 w-4" />
                        تصدير CSV
                    </Button>
                </div>
            </div>
            <!-- Stats mini cards -->
            <div class="mt-6 flex flex-wrap gap-4">
                <div class="flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 backdrop-blur-sm">
                    <Activity class="h-4 w-4 text-white" />
                    <span class="text-sm font-semibold text-white">{{ totalActivities.toLocaleString() }} إجمالي</span>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 backdrop-blur-sm">
                    <CalendarDays class="h-4 w-4 text-white" />
                    <span class="text-sm font-semibold text-white">{{ todayCount }} اليوم</span>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 backdrop-blur-sm">
                    <Users class="h-4 w-4 text-white" />
                    <span class="text-sm font-semibold text-white">{{ props.causers.length }} مستخدمين</span>
                </div>
            </div>
        </div>

        <!-- Toggle filter bar with animation -->
        <div class="mb-5 flex items-center justify-between">
            <button @click="filterExpanded = !filterExpanded"
                class="flex items-center gap-1 rounded-full bg-white/70 px-4 py-2 text-sm font-medium text-blue-800 shadow-sm transition-all hover:bg-white dark:bg-gray-800/70 dark:text-white">
                <Filter class="h-4 w-4" />
                الفلاتر
                <ChevronDown v-if="filterExpanded" class="h-4 w-4 transition-transform" />
                <ChevronUp v-else class="h-4 w-4 transition-transform" />
            </button>
            <Button variant="ghost" size="sm" @click="refresh" class="gap-1 text-blue-600">
                <RefreshCw class="h-4 w-4" />
                تحديث
            </Button>
        </div>

        <Transition name="slide-fade">
            <div v-show="filterExpanded" class="mb-6">
                <FilterPanel :filters="filters" :event-types="eventTypes" :causers="causers" />
            </div>
        </Transition>

        <!-- Activities Table with glassmorphism -->
        <Card
            class="overflow-hidden border-0 bg-white/80 shadow-xl backdrop-blur-sm transition-all dark:bg-gray-800/80">
            <CardHeader
                class="border-b border-gray-200/50 bg-linear-to-r from-gray-50/50 to-transparent dark:border-gray-700/50">
                <CardTitle class="flex items-center gap-2 text-xl">
                    <TrendingUp class="h-5 w-5 text-orange-500" />
                    قائمة النشاطات
                    <span class="mr-2 rounded-full bg-orange-100 px-2 py-0.5 text-xs font-normal text-orange-700">
                        {{ props.activities.total.toLocaleString() }}
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <ActivityTable :activities="activities" @view-detail="openDetail" />
            </CardContent>
        </Card>

        <!-- Floating export button (appears when scrolled) -->
        <Transition name="float-button">
            <button v-if="isScrolled" @click="exportCSV"
                class="fixed bottom-6 left-6 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-orange-500 text-white shadow-2xl transition-all hover:bg-orange-600 hover:scale-110"
                title="تصدير CSV">
                <Download class="h-5 w-5" />
            </button>
        </Transition>

        <!-- Modals -->
        <ActivityDetailModal :activity="selectedActivity" :open="showDetailModal"
            :specializations="props.specializations" @close="closeDetail" />
        <AnalyticsModal v-model:open="showAnalyticsModal" />
    </div>
</template>
