<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
    <!-- Table Header -->
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">سجل النشاطات</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="totalActivities"></span> نشاطاً
                    <span x-show="hasActiveFilters" class="text-orange-600 dark:text-orange-400 font-medium">(مفلتر)</span>
                </p>
            </div>

            <!-- Per Page Selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 dark:text-gray-400">عرض:</label>
                <select x-model="perPage"
                    @change="currentPage = 1; loadActivities(1)"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 shadow-sm">
                    @foreach(config('activitylog-ui.ui.per_page_options', [10, 25, 50, 100]) as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-600 dark:text-gray-400">لكل صفحة</span>
            </div>
        </div>
    </div>

    <!-- Table (Responsive) -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        الحدث
                    </th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        الوصف
                    </th>
                    <th class="hidden md:table-cell px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        الموضوع
                    </th>
                    <th class="hidden sm:table-cell px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        المستخدم
                    </th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        التاريخ
                    </th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="activity in activities" :key="activity.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-150 group">
                        <!-- Event Type -->
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                :class="window.ActivityTypeStyler?.getBadgeClasses(activity.event) || 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'">
                                <span class="w-1.5 h-1.5 ml-1 sm:ml-1.5 rounded-full"
                                    :class="`bg-${window.ActivityTypeStyler?.getColor(activity.event) || 'gray'}-500`"></span>
                                <span class="hidden sm:inline" x-text="activity.event || 'غير معروف'"></span>
                                <span class="sm:hidden" x-text="activity.event ? activity.event.charAt(0).toUpperCase() : '?'"></span>
                            </span>
                        </td>

                        <!-- Description -->
                        <td class="px-3 sm:px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                <div class="font-medium" x-text="activity.description"></div>
                                <!-- Mobile: show subject & user inline -->
                                <div class="sm:hidden mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-medium" x-text="activity.subject_type"></span><span class="text-gray-400">#</span><span x-text="activity.subject_id"></span>
                                    <span x-show="activity.causer" class="text-gray-400"> • </span><span x-show="activity.causer" x-text="activity.causer?.name || 'غير معروف'"></span>
                                </div>
                            </div>
                        </td>

                        <!-- Subject (hidden on mobile) -->
                        <td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900 dark:text-gray-100" x-text="activity.subject_type"></span>
                                <span class="text-gray-500 dark:text-gray-400">#<span x-text="activity.subject_id"></span></span>
                            </div>
                        </td>

                        <!-- User (hidden on small screens) -->
                        <td class="hidden sm:table-cell px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div x-show="activity.causer" class="flex items-center">
                                <div class="shrink-0 h-6 sm:h-8 w-6 sm:w-8">
                                    <div class="h-6 sm:h-8 w-6 sm:w-8 rounded-full bg-linear-to-br from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 flex items-center justify-center shadow-sm">
                                        <span class="text-xs font-medium text-white"
                                            x-text="activity.causer?.name?.charAt(0) || '?'"></span>
                                    </div>
                                </div>
                                <div class="mr-2 sm:mr-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="activity.causer?.name || 'غير معروف'"></div>
                                    <div class="hidden sm:block text-xs text-gray-500 dark:text-gray-400" x-text="activity.causer?.email || ''"></div>
                                </div>
                            </div>
                            <div x-show="!activity.causer" class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                <div class="shrink-0 h-6 sm:h-8 w-6 sm:w-8 ml-2 sm:ml-3">
                                    <div class="h-6 sm:h-8 w-6 sm:w-8 rounded-full bg-linear-to-br from-gray-500 to-gray-600 dark:from-gray-400 dark:to-gray-500 flex items-center justify-center shadow-sm">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                النظام
                            </div>
                        </td>

                        <!-- Date -->
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div class="sm:hidden text-xs font-medium" x-text="new Date(activity.created_at).toLocaleDateString('ar-EG', {month: 'short', day: 'numeric'})"></div>
                            <div class="hidden sm:block font-medium" x-text="new Date(activity.created_at).toLocaleDateString('ar-EG')"></div>
                            <div class="hidden sm:block text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="new Date(activity.created_at).toLocaleTimeString('ar-EG')"></div>
                        </td>

                        <!-- Actions -->
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                            <button @click="showActivityDetail(activity)"
                                class="text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 p-1 rounded-md hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                                <span class="hidden sm:inline font-medium">تفاصيل</span>
                                <svg class="sm:hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Empty State -->
        <div x-show="!loading && activities.length === 0"
            class="text-center py-16 bg-white dark:bg-gray-800">
            <div class="inline-flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">لا توجد نشاطات</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">لم يتم العثور على نشاطات تطابق المعايير المحددة.</p>
            <button @click="clearAllFilters"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                مسح الفلاتر
            </button>
        </div>
    </div>

    <!-- Pagination -->
    <div x-show="totalPages > 1"
        x-data="{
             pageInput: currentPage,
             loading: false,
             error: false,
             errorMessage: '',
             getPageNumbers() {
                 const pages = [];
                 const side = 2;
                 pages.push(1);
                 let start = Math.max(2, currentPage - side);
                 let end = Math.min(totalPages - 1, currentPage + side);
                 if (start > 2) pages.push('dots1');
                 for (let i = start; i <= end; i++) pages.push(i);
                 if (end < totalPages - 1) pages.push('dots2');
                 if (totalPages > 1) pages.push(totalPages);
                 return pages;
             },
             async goToPage(page) {
                 const target = parseInt(page);
                 if (isNaN(target) || target < 1 || target > totalPages || target === currentPage) {
                     this.pageInput = currentPage;
                     return;
                 }
                 this.loading = true;
                 try {
                     changePage(target);
                     this.pageInput = target;
                 } catch(e) {
                     this.error = true;
                     this.errorMessage = e.message;
                     this.pageInput = currentPage;
                 } finally {
                     this.loading = false;
                 }
             }
         }"
        class="relative bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">

        <!-- Loading Overlay -->
        <div x-show="loading"
            x-transition
            class="absolute inset-0 bg-white/70 dark:bg-gray-800/70 flex items-center justify-center z-10">
            <div class="flex items-center gap-3 px-4 py-2 rounded-lg bg-white dark:bg-gray-700 shadow-sm">
                <div class="animate-spin rounded-full h-5 w-5 border-2 border-orange-500 border-t-transparent"></div>
                <span class="text-sm">جاري تحميل الصفحة <span x-text="pageInput"></span>...</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Mobile Previous/Next -->
            <div class="flex items-center gap-3 sm:hidden">
                <button @click="goToPage(currentPage - 1)"
                    :disabled="currentPage <= 1 || loading"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium bg-white dark:bg-gray-800 disabled:opacity-50">
                    → السابق
                </button>
                <span class="text-sm">صفحة <span x-text="currentPage"></span></span>
                <button @click="goToPage(currentPage + 1)"
                    :disabled="currentPage >= totalPages || loading"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium bg-white dark:bg-gray-800 disabled:opacity-50">
                    التالي ←
                </button>
            </div>

            <!-- Desktop Pagination -->
            <nav class="hidden sm:flex items-center gap-1" aria-label="Pagination">
                <template x-for="page in getPageNumbers()" :key="page">
                    <button x-if="page === 'dots1' || page === 'dots2'"
                        class="w-8 h-8 flex items-center justify-center text-sm text-gray-500">...</button>
                    <button x-else
                        @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-orange-500 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition-colors"
                        x-text="page"></button>
                </template>
            </nav>

            <!-- Go to page input -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:inline">انتقل إلى:</span>
                <input type="number"
                    x-model="pageInput"
                    @keydown.enter="goToPage(pageInput)"
                    min="1"
                    :max="totalPages"
                    class="w-20 px-2 py-1 text-center text-sm border border-gray-300 rounded-lg bg-white dark:bg-gray-700 text-gray-900 focus:ring-orange-500">
                <span class="text-sm text-gray-500">/ <span x-text="totalPages"></span></span>
                <button @click="goToPage(pageInput)"
                    class="px-3 py-1 text-sm bg-blue-800 text-white rounded-lg hover:bg-blue-900">
                    اذهب
                </button>
            </div>
        </div>
    </div>
</div>