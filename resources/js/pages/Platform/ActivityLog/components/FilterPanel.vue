<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search, X, Calendar, User, Filter, Trash2 } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    filters: {
        search?: string;
        event?: string;
        causer_id?: number | string;
        date_from?: string;
        date_to?: string;
    };
    eventTypes: string[];
    causers: Array<{ id: number; name: string; email: string }>;
}>();

const search = ref(props.filters.search || '');
const selectedEvent = ref(props.filters.event || 'all');
const selectedCauser = ref(props.filters.causer_id?.toString() || 'all');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Debounce search
let timeout: ReturnType<typeof setTimeout>;
const applyFilters = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/employee/activity-logs', {
            search: search.value,
            event: selectedEvent.value === 'all' ? '' : selectedEvent.value,
            causer_id: selectedCauser.value === 'all' ? '' : selectedCauser.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

const resetFilters = () => {
    search.value = '';
    selectedEvent.value = 'all';
    selectedCauser.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

watch([search, selectedEvent, selectedCauser, dateFrom, dateTo], applyFilters);

// Active filters count for badge
const activeFiltersCount = computed(() => {
    let count = 0;

    if (search.value) {
        count++;
    }

    if (selectedEvent.value && selectedEvent.value !== 'all') {
        count++;
    }

    if (selectedCauser.value && selectedCauser.value !== 'all') {
        count++;
    }

    if (dateFrom.value) {
        count++;
    }

    if (dateTo.value) {
        count++;
    }

    return count;
});
</script>

<template>
    <div class="activity-filter-panel relative">
        <!-- Main filter card with glassmorphism -->
        <div
            class="overflow-hidden rounded-2xl border border-white/20 bg-white/80 shadow-xl backdrop-blur-md transition-all dark:bg-gray-800/80">
            <div class="p-5">
                <!-- Header with title and active filters badge -->
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <Filter class="h-5 w-5 text-orange-500" />
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">فلاتر البحث المتقدمة</h3>
                        <Badge v-if="activeFiltersCount > 0" variant="secondary"
                            class="bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300">
                            {{ activeFiltersCount }} نشط
                        </Badge>
                    </div>
                    <Button @click="resetFilters" variant="ghost" size="sm"
                        class="gap-1 text-gray-500 hover:text-red-600 dark:text-gray-400"
                        :disabled="activeFiltersCount === 0">
                        <Trash2 class="h-4 w-4" />
                        مسح الكل
                    </Button>
                </div>

                <!-- Filter grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Search -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">بحث</Label>
                        <div class="relative">
                            <Search class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <Input v-model="search" placeholder="الحدث، الوصف، المستخدم..."
                                class="pr-9 transition-all focus:ring-2 focus:ring-orange-500" />
                        </div>
                    </div>

                    <!-- Event type -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">نوع الحدث</Label>
                        <Select v-model="selectedEvent">
                            <SelectTrigger class="transition-all focus:ring-2 focus:ring-orange-500">
                                <SelectValue placeholder="الكل" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">الكل</SelectItem>
                                <SelectItem v-for="e in eventTypes" :key="e" :value="e">
                                    {{ e }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- User -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">المستخدم</Label>
                        <div class="relative">
                            <User
                                class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <Select v-model="selectedCauser">
                                <SelectTrigger class="pr-9 transition-all focus:ring-2 focus:ring-orange-500">
                                    <SelectValue placeholder="الكل" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">الكل</SelectItem>
                                    <SelectItem v-for="u in causers" :key="u.id" :value="String(u.id)">
                                        {{ u.name }} ({{ u.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Date from -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">من تاريخ</Label>
                        <div class="relative">
                            <Calendar
                                class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <Input type="date" v-model="dateFrom"
                                class="pr-9 transition-all focus:ring-2 focus:ring-orange-500" />
                        </div>
                    </div>

                    <!-- Date to -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">إلى تاريخ</Label>
                        <div class="relative">
                            <Calendar
                                class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <Input type="date" v-model="dateTo"
                                class="pr-9 transition-all focus:ring-2 focus:ring-orange-500" />
                        </div>
                    </div>
                </div>

                <!-- Active filters chips (visible when any filter is active) -->
                <div v-if="activeFiltersCount > 0"
                    class="mt-5 flex flex-wrap gap-2 border-t border-gray-200/50 pt-4 dark:border-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400">الفلاتر النشطة:</span>
                    <Badge v-if="search" variant="outline" class="gap-1 bg-gray-100 dark:bg-gray-700">
                        بحث: {{ search }}
                        <X @click="search = ''" class="h-3 w-3 cursor-pointer" />
                    </Badge>
                    <Badge v-if="selectedEvent && selectedEvent !== 'all'" variant="outline"
                        class="gap-1 bg-gray-100 dark:bg-gray-700">
                        الحدث: {{ selectedEvent }}
                        <X @click="selectedEvent = 'all'" class="h-3 w-3 cursor-pointer" />
                    </Badge>
                    <Badge v-if="selectedCauser && selectedCauser !== 'all'" variant="outline"
                        class="gap-1 bg-gray-100 dark:bg-gray-700">
                        المستخدم: {{causers.find(c => c.id.toString() === selectedCauser)?.name}}
                        <X @click="selectedCauser = 'all'" class="h-3 w-3 cursor-pointer" />
                    </Badge>
                    <Badge v-if="dateFrom" variant="outline" class="gap-1 bg-gray-100 dark:bg-gray-700">
                        من: {{ dateFrom }}
                        <X @click="dateFrom = ''" class="h-3 w-3 cursor-pointer" />
                    </Badge>
                    <Badge v-if="dateTo" variant="outline" class="gap-1 bg-gray-100 dark:bg-gray-700">
                        إلى: {{ dateTo }}
                        <X @click="dateTo = ''" class="h-3 w-3 cursor-pointer" />
                    </Badge>
                </div>
            </div>
        </div>
    </div>
</template>
