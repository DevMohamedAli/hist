<!-- Analytics Dashboard Component - RTL / Arabic / University Theme -->
<div x-data="analyticsData()"
    x-init="init()"
    class="space-y-6">

    <!-- Analytics Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">لوحة التحليلات</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">إحصائيات واتجاهات النشاطات عبر الزمن</p>
            </div>

            <!-- Time Period Selector -->
            <div class="flex flex-wrap gap-2">
                <button @click="selectedPeriod = 'today'; loadAnalytics()"
                    :class="{
                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border-orange-300': selectedPeriod === 'today',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300': selectedPeriod !== 'today'
                        }"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                    اليوم
                </button>
                <button @click="selectedPeriod = '7'; loadAnalytics()"
                    :class="{
                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border-orange-300': selectedPeriod === '7',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300': selectedPeriod !== '7'
                        }"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                    7 أيام
                </button>
                <button @click="selectedPeriod = '30'; loadAnalytics()"
                    :class="{
                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border-orange-300': selectedPeriod === '30',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300': selectedPeriod !== '30'
                        }"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                    30 يوم
                </button>
                <button @click="selectedPeriod = '90'; loadAnalytics()"
                    :class="{
                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border-orange-300': selectedPeriod === '90',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300': selectedPeriod !== '90'
                        }"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                    90 يوم
                </button>
                <button @click="selectedPeriod = 'custom'; showCustomDateRange = true"
                    :class="{
                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border-orange-300': selectedPeriod === 'custom',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300': selectedPeriod !== 'custom'
                        }"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                    نطاق مخصص
                </button>
            </div>
        </div>

        <!-- Custom Date Range -->
        <div x-show="selectedPeriod === 'custom'"
            x-transition
            class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">من تاريخ</label>
                <input type="date"
                    x-model="customStartDate"
                    @change="loadAnalytics()"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">إلى تاريخ</label>
                <input type="date"
                    x-model="customEndDate"
                    @change="loadAnalytics()"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all">
            <div class="flex items-center">
                <div class="shrink-0">
                    <div class="w-12 h-12 bg-linear-to-br from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي النشاطات</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total || '0'"></p>
                    <p class="text-xs text-gray-400 mt-1">كل الوقت</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all">
            <div class="flex items-center">
                <div class="shrink-0">
                    <div class="w-12 h-12 bg-linear-to-br from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">نشاطات اليوم</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.today || '0'"></p>
                    <p class="text-xs text-gray-400 mt-1">آخر 24 ساعة</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all">
            <div class="flex items-center">
                <div class="shrink-0">
                    <div class="w-12 h-12 bg-linear-to-br from-purple-500 to-purple-600 dark:from-purple-400 dark:to-purple-500 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">هذا الأسبوع</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.activities_this_week || '0'"></p>
                    <p class="text-xs text-gray-400 mt-1">آخر 7 أيام</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all">
            <div class="flex items-center">
                <div class="shrink-0">
                    <div class="w-12 h-12 bg-linear-to-br from-orange-500 to-orange-600 dark:from-orange-400 dark:to-orange-500 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">هذا الشهر</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.activities_this_month || '0'"></p>
                    <p class="text-xs text-gray-400 mt-1">آخر 30 يوم</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Event Types Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">النشاطات حسب النوع</h4>
            <div class="space-y-3">
                <template x-for="type in eventTypes" :key="type.name">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full ml-2"
                                    :style="`background-color: ${type.color}`"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize" x-text="type.name"></span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="type.count"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300"
                                :style="`width: ${type.percentage}%; background-color: ${type.color}`"></div>
                        </div>
                    </div>
                </template>
                <div x-show="!loading && eventTypes.length === 0" class="text-center py-6">
                    <p class="text-sm text-gray-500">لا توجد أنواع نشاطات</p>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">أكثر المستخدمين نشاطاً</h4>
            <div class="space-y-4">
                <template x-for="user in topUsers" :key="user.id">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-linear-to-br from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 flex items-center justify-center shadow-sm">
                                    <span class="text-xs font-medium text-white"
                                        x-text="user.name?.charAt(0) || '?'"></span>
                                </div>
                            </div>
                            <div class="mr-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="user.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="user.email"></p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-orange-600 dark:text-orange-400" x-text="user.activity_count"></span>
                    </div>
                </template>
                <div x-show="!loading && topUsers.length === 0" class="text-center py-6">
                    <p class="text-sm text-gray-500">لا توجد بيانات</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Models -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">النماذج الأكثر استخداماً</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="model in popularModels" :key="model.type">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center">
                        <div class="shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300"
                                    x-text="model.name?.charAt(0) || '?'"></span>
                            </div>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="model.name"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="model.type"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-sm font-semibold text-orange-600" x-text="model.activity_count"></span>
                        <span class="text-xs text-gray-500">نشاط</span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Activity Trends Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">اتجاهات النشاطات</h4>
        <div class="h-80">
            <canvas id="activityTrendsChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">الخط الزمني للنشاطات</h4>
        <div class="space-y-3">
            <template x-for="day in timeline" :key="day.date">
                <div class="flex items-center gap-4">
                    <div class="w-24 sm:w-32 shrink-0">
                        <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="day.date"></span>
                        <span class="mr-1 text-xs text-gray-500" x-text="day.day_name"></span>
                    </div>
                    <div class="flex-1 flex items-center gap-3">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all duration-300 bg-linear-to-l from-blue-600 to-orange-500"
                                :style="`width: ${Math.max(day.percentage, day.count === 0 ? 2 : 0)}%`"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-8 text-left" x-text="day.count"></span>
                    </div>
                </div>
            </template>
            <div x-show="!loading && timeline.length === 0" class="text-center py-6">
                <p class="text-sm text-gray-500">لا توجد بيانات</p>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <svg class="animate-spin h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span class="font-medium">جاري تحميل التحليلات...</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('analyticsData', () => ({
            stats: {},
            eventTypes: [],
            topUsers: [],
            timeline: [],
            popularModels: [],
            activityTrends: {},
            loading: true,
            selectedPeriod: '30',
            customStartDate: '',
            customEndDate: '',
            chart: null,

            init() {
                this.loadAnalytics();
            },

            async loadAnalytics(filters = {}) {
                try {
                    this.loading = true;
                    let url = '{{ route("activitylog-ui.api.analytics") }}';
                    let params = new URLSearchParams();

                    if (this.selectedPeriod === 'custom') {
                        if (this.customStartDate) params.append('start_date', this.customStartDate);
                        if (this.customEndDate) params.append('end_date', this.customEndDate);
                    } else if (this.selectedPeriod === 'today') {
                        params.append('period', 'today');
                    } else {
                        params.append('period', this.selectedPeriod);
                    }

                    // Add any additional filters from parent dashboard
                    Object.keys(filters).forEach(key => {
                        const val = filters[key];
                        if (val && val !== '' && !(Array.isArray(val) && val.length === 0)) {
                            if (Array.isArray(val)) val.forEach(v => params.append(`${key}[]`, v));
                            else params.append(key, val);
                        }
                    });

                    const response = await fetch(`${url}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await window.ActivitylogUi.parseJsonResponse(response, 'تحميل لوحة التحليلات');

                    if (data.success) {
                        this.stats = {
                            total: data.data.total_activities,
                            today: data.data.activities_today,
                            activities_this_week: data.data.activities_this_week,
                            activities_this_month: data.data.activities_this_month
                        };

                        // Assign colors to event types dynamically
                        this.eventTypes = (data.data.event_types || []).map(et => ({
                            ...et,
                            color: window.ActivityTypeStyler?.getColor(et.name) === 'green' ? '#10b981' : window.ActivityTypeStyler?.getColor(et.name) === 'blue' ? '#3b82f6' : window.ActivityTypeStyler?.getColor(et.name) === 'red' ? '#ef4444' : window.ActivityTypeStyler?.getColor(et.name) === 'yellow' ? '#f59e0b' : window.ActivityTypeStyler?.getColor(et.name) === 'purple' ? '#8b5cf6' : window.ActivityTypeStyler?.getColor(et.name) === 'orange' ? '#f97316' : '#6b7280'
                        }));

                        this.topUsers = data.data.top_users || [];
                        this.timeline = data.data.timeline || [];
                        this.popularModels = data.data.popular_models || [];
                        this.activityTrends = data.data.activity_trends || {
                            dates: [],
                            datasets: []
                        };

                        if (this.activityTrends && document.getElementById('activityTrendsChart')) {
                            this.initActivityTrendsChart();
                        }
                    }
                } catch (error) {
                    console.error('Error loading analytics:', error);
                    if (window.notify) {
                        window.notify.error('خطأ', 'فشل تحميل بيانات التحليلات');
                    }
                } finally {
                    this.loading = false;
                }
            },

            initActivityTrendsChart() {
                const canvas = document.getElementById('activityTrendsChart');
                if (!canvas) return;

                if (this.chart instanceof Chart) {
                    this.chart.destroy();
                }

                const ctx = canvas.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.activityTrends.dates || [],
                        datasets: (this.activityTrends.datasets || []).map(dataset => ({
                            label: dataset.label,
                            data: (dataset.data || []).map(d => d.count || 0),
                            borderColor: dataset.color || '#f97316',
                            backgroundColor: `${dataset.color || '#f97316'}20`,
                            tension: 0.4,
                            fill: true
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'عدد النشاطات'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'التاريخ'
                                }
                            }
                        }
                    }
                });
            },

            destroy() {
                if (this.chart instanceof Chart) {
                    this.chart.destroy();
                    this.chart = null;
                }
            }
        }));
    });
</script>