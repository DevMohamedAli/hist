<!-- Filter Panel Component - RTL / Arabic / University Theme -->
<div x-data="filterPanel()"
    x-init="init()"
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

    <!-- Delete Confirmation Modal -->
    <div x-data="{ showDeleteModal: false, viewToDelete: null }"
        @delete-view.window="viewToDelete = $event.detail; showDeleteModal = true"
        x-show="showDeleteModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                @click="showDeleteModal = false"></div>
            <div x-show="showDeleteModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">حذف العرض المحفوظ</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">هل أنت متأكد من حذف هذا العرض؟ لا يمكن التراجع عن هذا الإجراء.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteSavedView(viewToDelete); showDeleteModal = false; viewToDelete = null"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        حذف
                    </button>
                    <button @click="showDeleteModal = false; viewToDelete = null"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:mr-3 sm:w-auto sm:text-sm">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Header with expand/collapse -->
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">الفلاتر</h3>
                <span x-show="hasActiveFilters"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                    نشطة
                </span>
            </div>

            <!-- Action Buttons Group (responsive) -->
            <div class="flex items-center gap-1">
                <!-- Toggle Filters -->
                <button @click="expanded = !expanded"
                    class="inline-flex items-center justify-center p-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-500 transition-colors"
                    :title="expanded ? 'إخفاء الفلاتر' : 'إظهار الفلاتر'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                    </svg>
                </button>

                <!-- Clear Filters -->
                <button @click="clearAllFilters()"
                    :disabled="!hasActiveFilters"
                    class="inline-flex items-center justify-center p-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    title="مسح جميع الفلاتر">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Saved Views Dropdown -->
                @if(config('activitylog-ui.features.saved_views', true))
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="inline-flex items-center justify-center p-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-500 transition-colors"
                        title="العروض المحفوظة">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </button>
                    <div x-show="open"
                        x-cloak
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        class="origin-top-left absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-20">
                        <div class="py-1">
                            <template x-for="savedView in savedViews" :key="savedView.id">
                                <div class="flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <button @click="loadSavedView(savedView); open = false"
                                        class="grow text-right px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                        x-text="savedView.name"></button>
                                    <button @click.stop="$dispatch('delete-view', savedView.id)"
                                        class="px-2 py-2 text-sm text-red-600 dark:text-red-400 hover:text-red-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <div x-show="savedViews.length === 0" class="px-4 py-2 text-sm text-gray-500">لا توجد عروض محفوظة</div>
                            <div class="border-t border-gray-100 dark:border-gray-700">
                                <button @click="showSaveViewModal(); open = false"
                                    class="block w-full text-right px-4 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-gray-100">
                                    + حفظ العرض الحالي
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Content (collapsible) -->
    <div x-show="expanded" x-collapse class="px-4 sm:px-6 py-4 space-y-5">
        <!-- Search -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">بحث</label>
            <div class="relative">
                <input type="text"
                    x-model="filters.search"
                    @input.debounce.300ms="applyFilters()"
                    placeholder="بحث في النشاطات، المستخدمين، الوصف..."
                    class="block w-full pr-10 pl-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Date Range -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">النطاق الزمني</label>
            <div class="flex flex-wrap gap-2 mb-3">
                <template x-for="preset in datePresets" :key="preset.value">
                    <button @click="setDatePreset(preset.value)"
                        :class="{
                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border-orange-300': filters.date_preset === preset.value,
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300': filters.date_preset !== preset.value
                        }"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors"
                        x-text="preset.label">
                    </button>
                </template>
            </div>

            <div x-show="filters.date_preset === 'custom'"
                x-transition
                class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                    <input type="date"
                        x-model="filters.start_date"
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                    <input type="date"
                        x-model="filters.end_date"
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>
        </div>

        <!-- Event Types -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">أنواع الأحداث</label>
            <div class="space-y-2 max-h-32 overflow-y-auto custom-scrollbar pr-1">
                <template x-for="eventType in availableEventTypes" :key="eventType.value">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                            :value="eventType.value"
                            x-model="filters.event_types"
                            @change="applyFilters()"
                            class="rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                        <span class="mr-2 text-sm text-gray-700 dark:text-gray-300" x-text="eventType.label"></span>
                        <span :style="`background-color: ${eventType.styling.color}`" class="mr-auto w-2 h-2 rounded-full"></span>
                    </label>
                </template>
            </div>
        </div>

        <!-- User/Causer -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">المستخدم / المنفذ</label>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm pr-3 pl-10 py-2 text-right text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <span class="block truncate" x-text="selectedCauserText || 'جميع المستخدمين'"></span>
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </span>
                </button>
                <div x-show="open"
                    x-cloak
                    @click.away="open = false"
                    x-transition
                    class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 ring-1 ring-black ring-opacity-5 overflow-auto">
                    <div class="sticky top-0 bg-white dark:bg-gray-800 px-3 py-2 border-b border-gray-200">
                        <input type="text"
                            x-model="causerSearch"
                            @input.debounce.300ms="searchCausers()"
                            placeholder="بحث عن مستخدم..."
                            class="block w-full px-3 py-1 border rounded-md text-sm focus:ring-orange-500">
                    </div>
                    <button @click="selectCauser(null); open = false"
                        class="w-full text-right px-4 py-2 text-sm hover:bg-gray-100">جميع المستخدمين</button>
                    <template x-for="causer in filteredCausers" :key="causer.id">
                        <button @click="selectCauser(causer); open = false"
                            class="w-full text-right px-4 py-2 text-sm hover:bg-gray-100">
                            <div class="flex flex-col">
                                <span x-text="causer.name"></span>
                                <span class="text-xs text-gray-500" x-text="causer.email"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Advanced Filters Toggle -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
            <button @click="showAdvanced = !showAdvanced"
                class="inline-flex items-center text-sm text-orange-600 hover:text-orange-700">
                <svg :class="showAdvanced ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-1 transform transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span x-text="showAdvanced ? 'إخفاء الفلاتر المتقدمة' : 'إظهار الفلاتر المتقدمة'"></span>
            </button>
        </div>

        <!-- Advanced Filters -->
        <div x-show="showAdvanced" x-collapse class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">نوع الموضوع</label>
                <select x-model="filters.subject_type"
                    @change="applyFilters()"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع الأنواع</option>
                    <template x-for="type in availableSubjectTypes" :key="type.value">
                        <option :value="type.value" x-text="type.label"></option>
                    </template>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('filterPanel', () => ({
            initialized: false,
            expanded: true,
            showAdvanced: false,
            filters: {
                search: '',
                date_preset: 'all',
                start_date: '',
                end_date: '',
                event_types: [],
                causer_id: null,
                subject_type: ''
            },
            savedViews: [],
            availableEventTypes: [],
            availableSubjectTypes: [],
            availableCausers: [],
            filteredCausers: [],
            causerSearch: '',
            selectedCauser: null,
            datePresets: <?php echo Illuminate\Support\Js::from(collect(config('activitylog-ui.filters.date_presets', [
                                'all' => 'الكل',
                                'today' => 'اليوم',
                                'yesterday' => 'أمس',
                                'last_7_days' => 'آخر 7 أيام',
                                'this_month' => 'هذا الشهر',
                                'last_month' => 'الشهر الماضي',
                                'custom' => 'نطاق مخصص',
                            ]))->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()); ?>,

            init() {
                if (this.initialized) return;
                this.initialized = true;
                this.loadCausers();
                this.restorePersistedState();
                window.dispatchEvent(new CustomEvent('filter-panel-ready', {
                    detail: this.filters
                }));
            },

            async loadCausers() {
                try {
                    const res = await fetch('{{ route("activitylog-ui.api.filter.options") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });
                    const data = await window.ActivitylogUi.parseJsonResponse(res, 'تحميل خيارات الفلاتر');
                    this.availableCausers = data.causers || [];
                    this.filteredCausers = this.availableCausers;
                    this.availableSubjectTypes = data.subject_types || [];
                    this.availableEventTypes = (data.event_types || []).map(et => ({
                        value: et.value,
                        label: et.label,
                        styling: {
                            color: window.ActivityTypeStyler.getColor(et.value)
                        }
                    }));
                } catch (e) {
                    this.availableCausers = [];
                    this.filteredCausers = [];
                    this.availableEventTypes = [{
                            value: 'created',
                            label: 'إنشاء',
                            styling: {
                                color: 'green'
                            }
                        },
                        {
                            value: 'updated',
                            label: 'تحديث',
                            styling: {
                                color: 'blue'
                            }
                        },
                        {
                            value: 'deleted',
                            label: 'حذف',
                            styling: {
                                color: 'red'
                            }
                        },
                        {
                            value: 'restored',
                            label: 'استعادة',
                            styling: {
                                color: 'yellow'
                            }
                        }
                    ];
                }
            },

            searchCausers() {
                if (!this.causerSearch) this.filteredCausers = this.availableCausers;
                else {
                    const s = this.causerSearch.toLowerCase();
                    this.filteredCausers = this.availableCausers.filter(c => c.name.toLowerCase().includes(s) || c.email.toLowerCase().includes(s));
                }
            },

            selectCauser(causer) {
                this.selectedCauser = causer;
                this.filters.causer_id = causer?.id || null;
                this.applyFilters();
            },

            get selectedCauserText() {
                return this.selectedCauser?.name || null;
            },

            setDatePreset(preset) {
                this.filters.date_preset = preset;
                const today = new Date();
                if (preset === 'today') {
                    this.filters.start_date = this.filters.end_date = today.toISOString().split('T')[0];
                } else if (preset === 'yesterday') {
                    const yest = new Date(today);
                    yest.setDate(today.getDate() - 1);
                    this.filters.start_date = this.filters.end_date = yest.toISOString().split('T')[0];
                } else if (preset === 'last_7_days') {
                    const start = new Date(today);
                    start.setDate(today.getDate() - 7);
                    this.filters.start_date = start.toISOString().split('T')[0];
                    this.filters.end_date = today.toISOString().split('T')[0];
                } else if (preset === 'this_month') {
                    this.filters.start_date = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    this.filters.end_date = today.toISOString().split('T')[0];
                } else if (preset === 'last_month') {
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                    this.filters.start_date = lastMonth.toISOString().split('T')[0];
                    this.filters.end_date = lastMonthEnd.toISOString().split('T')[0];
                } else if (preset === 'all') {
                    this.filters.start_date = '';
                    this.filters.end_date = '';
                }
                if (preset !== 'custom') this.applyFilters();
                else if (preset === 'custom' && (this.filters.start_date || this.filters.end_date)) this.applyFilters();
            },

            get hasActiveFilters() {
                const f = this.filters;
                return f.search || (f.date_preset !== 'all' && f.date_preset !== 'custom') || f.event_types.length || f.causer_id || f.subject_type;
            },

            applyFilters() {
                window.dispatchEvent(new CustomEvent('filter-changed', {
                    detail: this.filters
                }));
                // Persist to localStorage
                localStorage.setItem('activitylog_date_preset', this.filters.date_preset);
                localStorage.setItem('activitylog_start_date', this.filters.start_date || '');
                localStorage.setItem('activitylog_end_date', this.filters.end_date || '');
                localStorage.setItem('activitylog_search', this.filters.search || '');
                localStorage.setItem('activitylog_event_types', JSON.stringify(this.filters.event_types || []));
                localStorage.setItem('activitylog_causer_id', this.filters.causer_id || '');
                localStorage.setItem('activitylog_subject_type', this.filters.subject_type || '');
                localStorage.setItem('activitylog_selected_causer', JSON.stringify(this.selectedCauser || null));
            },

            clearAllFilters() {
                this.filters = {
                    search: '',
                    date_preset: 'all',
                    start_date: '',
                    end_date: '',
                    event_types: [],
                    causer_id: null,
                    subject_type: ''
                };
                this.selectedCauser = null;
                this.causerSearch = '';
                this.applyFilters();
            },

            loadSavedView(view) {
                this.filters = {
                    ...this.filters,
                    ...view.filters
                };
                this.applyFilters();
                if (window.notify) window.notify.success('تم التحميل', `تم تحميل العرض "${view.name}"`);
            },

            showSaveViewModal() {
                window.dispatchEvent(new CustomEvent('show-save-view-modal', {
                    detail: this.filters
                }));
            },

            restorePersistedState() {
                const preset = localStorage.getItem('activitylog_date_preset');
                if (preset) this.filters.date_preset = preset;
                this.filters.start_date = localStorage.getItem('activitylog_start_date') || '';
                this.filters.end_date = localStorage.getItem('activitylog_end_date') || '';
                this.filters.search = localStorage.getItem('activitylog_search') || '';
                this.filters.subject_type = localStorage.getItem('activitylog_subject_type') || '';
                const causerId = localStorage.getItem('activitylog_causer_id');
                if (causerId) this.filters.causer_id = causerId ? parseInt(causerId) : null;
                const eventTypes = localStorage.getItem('activitylog_event_types');
                if (eventTypes) try {
                    this.filters.event_types = JSON.parse(eventTypes);
                } catch (e) {}
                const selected = localStorage.getItem('activitylog_selected_causer');
                if (selected) try {
                    this.selectedCauser = JSON.parse(selected);
                } catch (e) {}
            },

            async loadSavedViews() {
                try {
                    const res = await fetch('{{ route("activitylog-ui.api.views.index") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });
                    const data = await window.ActivitylogUi.parseJsonResponse(res, 'تحميل العروض المحفوظة');
                    this.savedViews = data.data || [];
                } catch (e) {
                    this.savedViews = [];
                }
            },

            async deleteSavedView(viewId) {
                try {
                    await fetch('{{ route("activitylog-ui.api.views.delete") }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            view_id: viewId
                        })
                    });
                    await this.loadSavedViews();
                    if (window.notify) window.notify.success('تم الحذف', 'تم حذف العرض المحفوظ');
                } catch (e) {
                    if (window.notify) window.notify.error('خطأ', 'فشل حذف العرض');
                }
            }
        }));
    });
</script>