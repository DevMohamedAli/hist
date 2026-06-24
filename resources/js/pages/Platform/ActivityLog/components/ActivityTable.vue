<script setup lang="ts">
import {
    Eye,
    Calendar,
    User,
    Sparkles,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDisplayDateTime } from '@/lib/date';

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

const props = defineProps<{
    activities: PaginatedActivities;
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'view-detail', activity: Activity): void;
}>();

const getEventBadgeClass = (event: string) => {
    const map: Record<string, string> = {
        created:
            'bg-gradient-to-r from-emerald-400 to-emerald-500 text-white border-0 shadow-sm',
        updated:
            'bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-0 shadow-sm',
        deleted:
            'bg-gradient-to-r from-rose-500 to-red-600 text-white border-0 shadow-sm',
        restored:
            'bg-gradient-to-r from-amber-400 to-yellow-500 text-white border-0 shadow-sm',
        login: 'bg-gradient-to-r from-purple-500 to-pink-500 text-white border-0 shadow-sm',
        logout: 'bg-gradient-to-r from-gray-500 to-slate-600 text-white border-0 shadow-sm',
    };

    return (
        map[event] ||
        'bg-gradient-to-r from-orange-400 to-orange-600 text-white border-0 shadow-sm'
    );
};

const formatDate = (date: string) => {
    return formatDisplayDateTime(date);
};

const formatRelativeTime = (date: string) => {
    const now = new Date();
    const then = new Date(date);
    const diffMs = now.getTime() - then.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
        return 'الآن';
    }

    if (diffMins < 60) {
        return `منذ ${diffMins} دقيقة`;
    }

    if (diffHours < 24) {
        return `منذ ${diffHours} ساعة`;
    }

    if (diffDays === 1) {
        return 'أمس';
    }

    return formatDate(date);
};

const changePage = (url: string | null) => {
    if (url && !props.loading) {
        window.location.href = url;
    }
};

// Skeleton rows for loading state
const skeletonRows = Array(5).fill(null);
</script>

<template>
    <div class="activity-table-component w-full">
        <!-- Desktop Table (visible on md and up) -->
        <div class="hidden overflow-x-auto md:block">
            <Table>
                <TableHeader>
                    <TableRow
                        class="border-b border-gray-200/50 bg-linear-to-r from-gray-50/80 to-transparent dark:from-gray-800/50"
                    >
                        <TableHead class="w-24 font-semibold">الحدث</TableHead>
                        <TableHead class="font-semibold">الوصف</TableHead>
                        <TableHead class="w-32 font-semibold"
                            >المستخدم</TableHead
                        >
                        <TableHead class="w-44 font-semibold"
                            >التاريخ</TableHead
                        >
                        <TableHead class="w-16"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <!-- Loading State Skeleton -->
                    <template v-if="loading">
                        <TableRow
                            v-for="i in skeletonRows"
                            :key="i"
                            class="animate-pulse"
                        >
                            <TableCell>
                                <div
                                    class="h-8 w-20 rounded bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </TableCell>
                            <TableCell>
                                <div
                                    class="h-8 w-48 rounded bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </TableCell>
                            <TableCell>
                                <div
                                    class="h-8 w-24 rounded bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </TableCell>
                            <TableCell>
                                <div
                                    class="h-8 w-32 rounded bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </TableCell>
                            <TableCell>
                                <div
                                    class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </TableCell>
                        </TableRow>
                    </template>

                    <!-- Real Data with staggered transition -->
                    <template v-else>
                        <TableRow
                            v-for="(activity, idx) in activities.data"
                            :key="activity.id"
                            class="group cursor-pointer transition-all duration-300 hover:bg-linear-to-r hover:from-orange-50/50 hover:to-transparent dark:hover:from-orange-900/20"
                            :style="{ animationDelay: `${idx * 30}ms` }"
                        >
                            <TableCell>
                                <Badge
                                    :class="getEventBadgeClass(activity.event)"
                                    class="font-semibold shadow-sm transition-transform group-hover:scale-105"
                                >
                                    {{ activity.event || 'غير معروف' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <span
                                    class="line-clamp-2 text-gray-700 dark:text-gray-300"
                                    >{{ activity.description }}</span
                                >
                            </TableCell>
                            <TableCell>
                                <div
                                    v-if="activity.causer"
                                    class="flex items-center gap-2"
                                >
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-linear-to-br from-blue-500 to-purple-600 text-xs font-bold text-white shadow-sm"
                                    >
                                        {{
                                            activity.causer.name
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <span class="text-sm font-medium">{{
                                        activity.causer.name
                                    }}</span>
                                </div>
                                <span
                                    v-else
                                    class="flex items-center gap-1 text-sm text-gray-500"
                                >
                                    <User class="h-3 w-3" />
                                    النظام
                                </span>
                            </TableCell>
                            <TableCell class="whitespace-nowrap">
                                <div
                                    class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <Calendar class="h-3 w-3" />
                                    <span>{{
                                        formatRelativeTime(activity.created_at)
                                    }}</span>
                                    <span
                                        class="hidden text-xs text-gray-400 sm:inline"
                                        >({{
                                            formatDate(activity.created_at)
                                        }})</span
                                    >
                                </div>
                            </TableCell>
                            <TableCell>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="emit('view-detail', activity)"
                                    class="transition-all duration-200 hover:scale-110 hover:bg-orange-100 dark:hover:bg-orange-900/30"
                                >
                                    <Eye
                                        class="h-4 w-4 text-gray-600 hover:text-orange-600 dark:text-gray-400"
                                    />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </template>

                    <!-- Empty State -->
                    <TableRow v-if="!loading && activities.data.length === 0">
                        <TableCell colspan="5" class="py-16 text-center">
                            <div
                                class="flex flex-col items-center justify-center gap-3"
                            >
                                <Sparkles
                                    class="h-12 w-12 text-gray-300 dark:text-gray-600"
                                />
                                <p
                                    class="text-lg font-medium text-gray-500 dark:text-gray-400"
                                >
                                    لا توجد نشاطات
                                </p>
                                <p
                                    class="text-sm text-gray-400 dark:text-gray-500"
                                >
                                    حاول تعديل الفلاتر أو إجراء نشاط جديد
                                </p>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Mobile Card View (visible on small screens) -->
        <div class="space-y-3 md:hidden">
            <!-- Loading skeleton for mobile -->
            <template v-if="loading">
                <div
                    v-for="i in skeletonRows"
                    :key="i"
                    class="animate-pulse rounded-xl bg-white/80 p-4 shadow-sm dark:bg-gray-800/80"
                >
                    <div class="flex justify-between">
                        <div
                            class="h-6 w-20 rounded bg-gray-200 dark:bg-gray-700"
                        ></div>
                        <div
                            class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700"
                        ></div>
                    </div>
                    <div
                        class="mt-3 h-4 w-full rounded bg-gray-200 dark:bg-gray-700"
                    ></div>
                    <div
                        class="mt-2 h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700"
                    ></div>
                    <div class="mt-3 flex justify-between">
                        <div
                            class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700"
                        ></div>
                        <div
                            class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700"
                        ></div>
                    </div>
                </div>
            </template>

            <!-- Real data cards -->
            <div
                v-for="(activity, idx) in activities.data"
                :key="activity.id"
                class="transform rounded-xl border border-white/20 bg-white/80 p-4 shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-xl dark:bg-gray-800/80"
                :style="{ animationDelay: `${idx * 50}ms` }"
            >
                <div class="flex items-start justify-between">
                    <Badge
                        :class="getEventBadgeClass(activity.event)"
                        class="shadow-sm"
                    >
                        {{ activity.event || 'غير معروف' }}
                    </Badge>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="emit('view-detail', activity)"
                        class="h-8 w-8 transition-all hover:scale-110"
                    >
                        <Eye class="h-4 w-4" />
                    </Button>
                </div>
                <p
                    class="mt-3 line-clamp-3 text-sm text-gray-700 dark:text-gray-300"
                >
                    {{ activity.description }}
                </p>
                <div
                    class="mt-3 flex flex-wrap justify-between gap-2 text-xs text-gray-500 dark:text-gray-400"
                >
                    <div class="flex items-center gap-1">
                        <User class="h-3 w-3" />
                        <span>{{ activity.causer?.name || 'النظام' }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <Calendar class="h-3 w-3" />
                        <span>{{
                            formatRelativeTime(activity.created_at)
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Empty state mobile -->
            <div
                v-if="!loading && activities.data.length === 0"
                class="py-16 text-center"
            >
                <Sparkles class="mx-auto h-12 w-12 text-gray-300" />
                <p class="mt-3 text-gray-500">لا توجد نشاطات</p>
            </div>
        </div>

        <!-- Animated Pagination -->
        <div
            v-if="activities.last_page > 1 && !loading"
            class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-gray-200/50 pt-4 sm:flex-row"
        >
            <div class="text-sm text-gray-500 dark:text-gray-400">
                عرض {{ activities.data.length }} من {{ activities.total }} نشاط
            </div>
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="activities.current_page === 1"
                    @click="
                        changePage(
                            activities.links.find(
                                (l) => l.label === '&laquo; Previous',
                            )?.url || null,
                        )
                    "
                    class="transition-all hover:scale-105 disabled:opacity-50"
                >
                    <ChevronRight class="ml-1 h-4 w-4" />
                    السابق
                </Button>
                <div class="flex items-center gap-1">
                    <span
                        class="rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-700 dark:bg-orange-900 dark:text-orange-300"
                    >
                        {{ activities.current_page }}
                    </span>
                    <span class="text-sm text-gray-500"
                        >من {{ activities.last_page }}</span
                    >
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="activities.current_page === activities.last_page"
                    @click="
                        changePage(
                            activities.links.find(
                                (l) => l.label === 'Next &raquo;',
                            )?.url || null,
                        )
                    "
                    class="transition-all hover:scale-105 disabled:opacity-50"
                >
                    التالي
                    <ChevronLeft class="mr-1 h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
