@extends('activitylog-ui::layouts.app')

@section('title', config('activitylog-ui.ui.title', 'سجل النشاطات') . ' - لوحة التحكم')

@section('content')
<div x-data="activityDashboard()" x-init="init()" class="space-y-6">
    <!-- Header with title and actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-blue-800 dark:text-white">{{ config('activitylog-ui.ui.title', 'سجل النشاطات') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">مراقبة وتحليل جميع نشاطات النظام</p>

            <!-- Page indicator for table view -->
            <div x-show="currentView === 'table' && currentPage > 1" class="mt-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    أنت الآن في الصفحة <span x-text="currentPage"></span> من <span x-text="totalPages"></span>
                </span>
            </div>
        </div>

        <!-- View Switcher & Export -->
        <div class="flex items-center gap-3">
            <!-- Export Button -->
            @if(config('activitylog-ui.features.exports', true))
            <button @click="exportActivities()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M7 7h10a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z"></path>
                </svg>
                تصدير
            </button>
            @endif

            <!-- View Switcher Tabs -->
            <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1">
                <button @click="switchView('table')"
                    :class="currentView === 'table' ? 'bg-orange-500 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9"></path>
                    </svg>
                    جدول
                </button>
                <button @click="switchView('timeline')"
                    :class="currentView === 'timeline' ? 'bg-orange-500 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors mr-1">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    خط زمني
                </button>
                @if(config('activitylog-ui.features.analytics', true))
                <button @click="switchView('analytics')"
                    :class="currentView === 'analytics' ? 'bg-orange-500 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors mr-1">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    تحليلات
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Area: Filter Panel + Views -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Unified Filter Panel (hidden on analytics view) -->
        <div x-show="currentView !== 'analytics'" class="w-full lg:w-72 xl:w-80 lg:shrink-0">
            @include('activitylog-ui::components.filter-panel')
        </div>

        <!-- Dynamic View Content -->
        <div class="w-full lg:flex-1 lg:min-w-0" :class="{ 'lg:ml-0': currentView === 'analytics' }">

            <!-- Loading State -->
            <div x-show="loading" class="flex items-center justify-center py-12">
                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>جاري تحميل النشاطات...</span>
                </div>
            </div>

            <!-- Table View -->
            <div x-show="currentView === 'table' && !loading">
                @include('activitylog-ui::components.table-view')
            </div>

            <!-- Timeline View -->
            <div x-show="currentView === 'timeline' && !loading">
                @include('activitylog-ui::components.timeline-view')
            </div>

            <!-- Analytics View -->
            @if(config('activitylog-ui.features.analytics', true))
            <div x-show="currentView === 'analytics' && !loading">
                @include('activitylog-ui::components.analytics-dashboard')
            </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    @include('activitylog-ui::components.activity-detail-modal')
    @if(config('activitylog-ui.features.exports', true))
    @include('activitylog-ui::components.export-modal')
    @endif
    @if(config('activitylog-ui.features.saved_views', true))
    @include('activitylog-ui::components.save-view-modal')
    @endif
</div>
@endsection

@push('scripts')
<script>
    function activityDashboard() {
        return {
            initialized: false,
            currentView: <?php echo Illuminate\Support\Js::from($view ?? 'table'); ?>,
            loading: false,
            activities: [],
            totalActivities: 0,
            currentPage: 1,
            perPage: <?php echo (int) config('activitylog-ui.ui.default_per_page', 25); ?>,
            totalPages: 1,
            currentFilters: {},

            init() {
                if (this.initialized) return;
                this.initialized = true;

                // Listen for filter changes
                window.addEventListener('filter-changed', (event) => {
                    this.currentFilters = event.detail || {};
                    this.currentPage = 1;
                    this.loadActivities();
                    if (this.currentView === 'analytics') this.reloadAnalytics();
                });

                // Load initial data
                if (this.currentView === 'analytics') {
                    this.reloadAnalytics();
                } else {
                    this.loadActivities();
                }
            },

            async loadActivities(page = 1) {
                if (this.loading) return;
                this.loading = true;
                this.currentPage = page;

                try {
                    const params = new URLSearchParams();
                    params.append('page', page);
                    params.append('per_page', this.perPage);

                    Object.keys(this.currentFilters || {}).forEach(key => {
                        const val = this.currentFilters[key];
                        if (val !== null && val !== undefined && val !== '') {
                            if (Array.isArray(val)) val.forEach(v => params.append(`${key}[]`, v));
                            else params.append(key, val);
                        }
                    });

                    const baseUrl = "{{ route('activitylog-ui.api.activities.index') }}";
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const response = await fetch(baseUrl + '?' + params, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : ''
                        }
                    });
                    const result = await window.ActivitylogUi.parseJsonResponse(response, 'تحميل النشاطات');
                    this.activities = result.data || [];
                    this.totalActivities = result.total || 0;
                    this.totalPages = result.last_page || 1;
                    if (window.notify) window.notify.success('نجاح', `تم تحميل ${this.activities.length} نشاط`);
                } catch (error) {
                    this.activities = [];
                    if (window.notify) window.notify.error('خطأ', 'فشل تحميل النشاطات');
                } finally {
                    this.loading = false;
                }
            },

            changePage(page) {
                if (page >= 1 && page <= this.totalPages) this.loadActivities(page);
            },

            get hasActiveFilters() {
                const f = this.currentFilters || {};
                return Object.keys(f).some(k => {
                    const v = f[k];
                    return v !== '' && v !== null && (Array.isArray(v) ? v.length > 0 : true) && !(k === 'date_preset' && v === 'all');
                });
            },

            exportActivities() {
                window.dispatchEvent(new CustomEvent('show-export-modal', {
                    detail: {
                        filters: this.currentFilters
                    }
                }));
            },

            showActivityDetail(activity) {
                window.dispatchEvent(new CustomEvent('show-activity-detail', {
                    detail: activity
                }));
            },

            switchView(view) {
                const prev = this.currentView;
                this.currentView = view;
                if (view === 'timeline') {
                    this.currentPage = 1;
                    this.loadActivities();
                } else if (view === 'table') {
                    this.loadActivities();
                } else if (view === 'analytics') {
                    this.reloadAnalytics();
                }
            },

            reloadAnalytics() {
                const analyticsComp = document.querySelector('[x-data*="analyticsData"]');
                if (analyticsComp && analyticsComp._x_dataStack) {
                    const comp = analyticsComp._x_dataStack[0];
                    if (comp && comp.loadAnalytics) comp.loadAnalytics(this.currentFilters);
                }
            }
        };
    }
</script>
@endpush
