<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
    <!-- Timeline Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الخط الزمني للنشاطات</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="totalActivities"></span> نشاطاً بترتيب زمني (من الأحدث إلى الأقدم)
                </p>
                <div x-show="activities.length > 0 && activities.length < totalActivities" class="mt-2">
                    <div class="inline-flex items-center px-2 py-1 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-xs">
                        <svg class="w-3 h-3 ml-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        يعرض الخط الزمني النشاطات من الأحدث إلى الأقدم - انتقل للأسفل واضغط "تحميل المزيد" للنشاطات الأقدم
                    </div>
                </div>
            </div>

            <!-- Initial Load Size Selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 dark:text-gray-400">التحميل الأولي:</label>
                <select x-model="perPage"
                    @change="currentPage = 1; loadActivities(1)"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500">
                    @foreach(config('activitylog-ui.ui.per_page_options', [10, 25, 50, 100]) as $option)
                    <option value="{{ $option }}">{{ $option }} نشاط</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Timeline Items -->
    <div class="space-y-4">
        <template x-for="(activity, index) in activities" :key="activity.id">
            <div class="relative group">
                <!-- Timeline connecting line -->
                <div x-show="index < activities.length - 1"
                    class="absolute right-4 top-10 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700 -z-10"></div>

                <div class="flex items-start gap-4">
                    <!-- Timeline Icon -->
                    <div class="relative shrink-0">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-gray-800 shadow-sm"
                            :class="window.ActivityTypeStyler?.getTimelineClasses(activity.event) || 'bg-linear-to-br from-gray-500 to-gray-600 dark:from-gray-400 dark:to-gray-500'">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="window.ActivityTypeStyler?.getIcon(activity.event) || 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Timeline Content Card -->
                    <div class="min-w-0 flex-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-600">
                        <!-- Header Row -->
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Event Badge -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                    :class="window.ActivityTypeStyler?.getBadgeClasses(activity.event) || 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'">
                                    <span x-text="activity.event ? activity.event.toUpperCase() : 'UNKNOWN'"></span>
                                </span>

                                <!-- Subject -->
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    <span x-text="activity.subject_type"></span>
                                    <span class="text-gray-500 dark:text-gray-400 font-normal">#<span x-text="activity.subject_id"></span></span>
                                </span>

                                <!-- User Info -->
                                <span x-show="activity.causer" class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-md">
                                    بواسطة <span class="font-medium" x-text="activity.causer?.name || 'غير معروف'"></span>
                                </span>
                                <span x-show="!activity.causer" class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-md font-medium">
                                    بواسطة النظام
                                </span>
                            </div>

                            <!-- Date & Actions -->
                            <div class="flex items-center gap-2">
                                <time class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-md"
                                    x-text="new Date(activity.created_at).toLocaleString('ar-EG', {
                                          month: 'short',
                                          day: 'numeric',
                                          hour: '2-digit',
                                          minute: '2-digit'
                                      })"></time>
                                <button @click="showActivityDetail(activity)"
                                    class="text-orange-600 dark:text-orange-400 hover:text-orange-700 p-1.5 rounded-md hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="text-sm text-gray-700 dark:text-gray-300 font-medium leading-relaxed" x-text="activity.description"></div>

                        <!-- Attribute Changes (collapsible) -->
                        <div x-data="{ expanded: false, get changes() { return activity.attribute_changes || (activity.properties && (activity.properties.old || activity.properties.attributes) ? { old: activity.properties.old, attributes: activity.properties.attributes } : null); } }"
                            x-show="changes"
                            class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button @click="expanded = !expanded"
                                class="flex items-center text-xs text-orange-600 hover:text-orange-700 font-medium bg-orange-50 dark:bg-orange-900/20 px-2 py-1 rounded-md">
                                <svg :class="expanded ? 'rotate-90' : ''"
                                    class="w-3 h-3 ml-1 transform transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span x-text="expanded ? 'إخفاء' : 'إظهار'"></span> التغييرات
                            </button>
                            <div x-show="expanded" x-collapse class="mt-3 space-y-3">
                                <template x-if="changes.old">
                                    <div>
                                        <h5 class="text-xs font-semibold text-red-600 dark:text-red-400 mb-2 flex items-center">
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                            القيم السابقة
                                        </h5>
                                        <pre class="text-xs bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 rounded-md text-red-800 dark:text-red-300 overflow-x-auto"
                                            x-text="JSON.stringify(changes.old, null, 2)"></pre>
                                    </div>
                                </template>
                                <template x-if="changes.attributes">
                                    <div>
                                        <h5 class="text-xs font-semibold text-green-600 dark:text-green-400 mb-2 flex items-center">
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            القيم الجديدة
                                        </h5>
                                        <pre class="text-xs bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 rounded-md text-green-800 dark:text-green-300 overflow-x-auto"
                                            x-text="JSON.stringify(changes.attributes, null, 2)"></pre>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Custom Properties (excluding legacy diff keys) -->
                        <div x-show="activity.properties && Object.keys(activity.properties).filter(k => k !== 'old' && k !== 'attributes').length > 0"
                            x-data="{ propsExpanded: false }"
                            class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button @click="propsExpanded = !propsExpanded"
                                class="flex items-center text-xs text-purple-600 hover:text-purple-700 font-medium bg-purple-50 dark:bg-purple-900/20 px-2 py-1 rounded-md">
                                <svg :class="propsExpanded ? 'rotate-90' : ''"
                                    class="w-3 h-3 ml-1 transform transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span x-text="propsExpanded ? 'إخفاء' : 'إظهار'"></span> الخصائص الإضافية
                            </button>
                            <div x-show="propsExpanded" x-collapse class="mt-3">
                                <pre class="text-xs bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 p-3 rounded-md text-gray-800 dark:text-gray-300 overflow-x-auto"
                                    x-text="JSON.stringify(Object.fromEntries(Object.entries(activity.properties || {}).filter(([k]) => k !== 'old' && k !== 'attributes')), null, 2)"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="activities.length === 0" class="text-center py-16">
        <div class="inline-flex items-center justify-center w-20 h-20 mx-auto mb-6 bg-gray-100 dark:bg-gray-700 rounded-full">
            <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">لا توجد نشاطات</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">لا توجد نشاطات تطابق معايير البحث الحالية. جرب تعديل الفلاتر.</p>
    </div>

    <!-- Load More Section -->
    <div x-show="currentPage < totalPages" class="mt-8 text-center">
        <button @click="loadMoreActivities()"
            :disabled="loading"
            class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 shadow-sm text-base font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 transition-all duration-200 hover:shadow-md">
            <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg x-show="!loading" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            <span x-show="!loading">تحميل المزيد من النشاطات</span>
            <span x-show="loading">جاري التحميل...</span>
        </button>

        <!-- Progress Indicator -->
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                عرض <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="activities.length"></span>
                من أصل <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="totalActivities"></span> نشاط
            </p>
            <div class="w-full max-w-xs mx-auto bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-linear-to-l from-orange-500 to-orange-600 h-2 rounded-full transition-all duration-300 shadow-sm"
                    :style="`width: ${totalActivities > 0 ? (activities.length / totalActivities) * 100 : 0}%`"></div>
            </div>
        </div>
    </div>

    <!-- All Loaded Message -->
    <div x-show="activities.length > 0 && currentPage >= totalPages" class="mt-8 text-center">
        <div class="inline-flex items-center px-4 py-3 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300">
            <svg class="w-5 h-5 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-sm font-semibold">تم تحميل جميع النشاطات</span>
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            إجمالي <span class="font-medium text-gray-700 dark:text-gray-300" x-text="activities.length"></span> نشاط معروض
        </p>
    </div>
</div>

<style>
    /* Custom timeline hover effects */
    .timeline-view .group:hover .bg-white {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .dark .timeline-view .group:hover .bg-gray-800 {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
    }
</style>