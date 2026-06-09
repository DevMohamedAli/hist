<!-- Export Modal - RTL / Arabic / University Theme -->
<div x-data="{
        open: false,
        selectedFormat: '',
        currentFilters: {},
        isExporting: false,

        async exportData(format, filters) {
            if (this.isExporting) return;
            this.isExporting = true;
            this.selectedFormat = format;

            try {
                const response = await fetch('{{ route("activitylog-ui.api.export") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        format: format,
                        filters: filters
                    })
                });

                const result = await window.ActivitylogUi.parseJsonResponse(response, 'تصدير النشاطات');

    if (result.download_url) {
                    window.location.href = result.download_url;
    if (window.notify) {
    window.notify.success('اكتمل التصدير', `تم تصدير النشاطات كملف ${format.toUpperCase()}`);
    }
                    this.open = false;
    } else if (result.job_id && window.notify) {
    window.notify.info('قيد المعالجة', 'التصدير كبير الحجم وسيتم إشعارك عند اكتماله.' );
                    this.open = false;
    }
    } catch (error) {
    console.error('Export error:', error);
    if (window.notify) {
    window.notify.error('فشل التصدير', 'حدث خطأ أثناء التصدير. حاول مرة أخرى.' );
    }
    } finally {
                this.isExporting = false;
                this.selectedFormat = '';
    }
    }
    }"
    @@show-export-modal.window="open = true; currentFilters = $event.detail?.filters || {}"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-opacity"
        @click="open = false"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl dark:shadow-gray-900/50 border border-gray-200 dark:border-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M7 7h10a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تصدير النشاطات</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">تحميل بيانات النشاطات بصيغة مختلفة</p>
                    </div>
                </div>
                <button @click="open = false"
                    class="rounded-lg text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    اختر الصيغة المناسبة لتصدير بيانات النشاطات. سيتم تطبيق جميع الفلاتر الحالية على التصدير.
                </p>

                @php($enabledFormats = config('activitylog-ui.exports.enabled_formats', ['xlsx', 'csv', 'pdf', 'json']))
                <div class="space-y-3">
                    @if(in_array('xlsx', $enabledFormats))
                    <button @click="exportData('xlsx', currentFilters)"
                        :disabled="isExporting"
                        :class="selectedFormat === 'xlsx' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="w-full flex items-center justify-between p-4 border rounded-xl transition-all duration-200 group disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-linear-to-br from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 rounded-xl flex items-center justify-center ml-4 shadow-sm">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">ملف Excel</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">بيانات منسقة وجداول (.xlsx)</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">مُوصى به للتحليل</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif

                    @if(in_array('csv', $enabledFormats))
                    <button @click="exportData('csv', currentFilters)"
                        :disabled="isExporting"
                        :class="selectedFormat === 'csv' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="w-full flex items-center justify-between p-4 border rounded-xl transition-all duration-200 group disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-linear-to-br from-blue-500 to-blue-600 dark:from-blue-400 dark:to-blue-500 rounded-xl flex items-center justify-center ml-4 shadow-sm">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">ملف CSV</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">قيم مفصولة بفواصل (.csv)</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">متوافق مع جميع البرامج</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif

                    @if(in_array('pdf', $enabledFormats))
                    <button @click="exportData('pdf', currentFilters)"
                        :disabled="isExporting"
                        :class="selectedFormat === 'pdf' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="w-full flex items-center justify-between p-4 border rounded-xl transition-all duration-200 group disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-linear-to-br from-red-500 to-red-600 dark:from-red-400 dark:to-red-500 rounded-xl flex items-center justify-center ml-4 shadow-sm">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">تقرير PDF</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">تقرير منسق للطباعة والمشاركة (.pdf)</p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">مثالي للطباعة</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif

                    @if(in_array('json', $enabledFormats))
                    <button @click="exportData('json', currentFilters)"
                        :disabled="isExporting"
                        :class="selectedFormat === 'json' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="w-full flex items-center justify-between p-4 border rounded-xl transition-all duration-200 group disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-linear-to-br from-purple-500 to-purple-600 dark:from-purple-400 dark:to-purple-500 rounded-xl flex items-center justify-center ml-4 shadow-sm">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M7 7h10a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">بيانات JSON</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">صيغة قابلة للقراءة آلياً (.json)</p>
                                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">مثالي للمطورين</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif
                </div>

                <!-- Active Filters Display -->
                <div x-show="Object.keys(currentFilters).length > 0 && Object.values(currentFilters).some(v => v && v !== '' && !(Array.isArray(v) && v.length === 0))"
                    class="mt-5 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 ml-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-300">الفلاتر النشطة</h4>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">سيتم تطبيق الفلاتر التالية على التصدير:</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <template x-for="[key, value] in Object.entries(currentFilters)" :key="key">
                                    <span x-show="value && value !== '' && !(Array.isArray(value) && value.length === 0)"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800 dark:text-amber-200">
                                        <span x-text="key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>:
                                        <span x-text="Array.isArray(value) ? value.join(', ') : value" class="mr-1 font-normal"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Filters Warning -->
                <div x-show="Object.keys(currentFilters).length === 0 || !Object.values(currentFilters).some(v => v && v !== '' && !(Array.isArray(v) && v.length === 0))"
                    class="mt-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 ml-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 13.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-red-900 dark:text-red-300">لا توجد فلاتر مطبقة</h4>
                            <p class="text-xs text-red-700 dark:text-red-400 mt-1">
                                سيتم تصدير جميع سجلات النشاطات، مما قد يستغرق وقتاً طويلاً وينشئ ملفاً كبيراً. يفضل تطبيق الفلاتر أولاً.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Export Info -->
                <div class="mt-5 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 ml-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300">معلومات التصدير</h4>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                                سيتضمن التصدير النشاطات التي تطابق الفلاتر فقط. قد تستغرق عمليات التصدير الكبيرة بضع لحظات.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-500 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
</div>
