<!-- Activity Detail Modal - RTL / Arabic / University Theme -->
<div x-data="{ open: false, activity: null }"
    @@show-activity-detail.window="activity = $event.detail; open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">

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
            class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl dark:shadow-gray-900/50 border border-gray-200 dark:border-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <div class="shrink-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                            x-show="activity"
                            :class="window.ActivityTypeStyler?.getTimelineClasses(activity?.event) || 'bg-linear-to-br from-gray-500 to-gray-600'">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="window.ActivityTypeStyler?.getIcon(activity?.event) || 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تفاصيل النشاط</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="activity?.description || 'جاري التحميل...'"></p>
                    </div>
                </div>
                <button @click="open = false"
                    class="rounded-lg text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <span class="sr-only">إغلاق</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <template x-if="activity">
                    <div class="space-y-6">
                        <!-- Basic Information & Context Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Event Information -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-4 h-4 ml-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    معلومات الحدث
                                </h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">نوع الحدث</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                                :class="window.ActivityTypeStyler?.getBadgeClasses(activity.event) || 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'">
                                                <span x-text="activity.event?.toUpperCase() || 'غير معروف'"></span>
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الوصف</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="activity.description"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">التاريخ والوقت</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100" x-text="new Date(activity.created_at).toLocaleString('ar-EG')"></dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Context Information -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-4 h-4 ml-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    السياق
                                </h4>
                                <dl class="space-y-3">
                                    <div x-show="activity.causer">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تم بواسطة</dt>
                                        <dd class="mt-1 flex items-center">
                                            <div class="w-6 h-6 bg-linear-to-br from-blue-600 to-blue-800 rounded-full flex items-center justify-center ml-2 shadow-sm">
                                                <span class="text-xs font-semibold text-white" x-text="activity.causer?.name?.charAt(0) || '?'"></span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="activity.causer?.name || 'غير معروف'"></span>
                                        </dd>
                                    </div>
                                    <div x-show="!activity.causer">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تم بواسطة</dt>
                                        <dd class="mt-1 flex items-center">
                                            <div class="w-6 h-6 bg-linear-to-br from-gray-500 to-gray-600 rounded-full flex items-center justify-center ml-2 shadow-sm">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">النظام</span>
                                        </dd>
                                    </div>
                                    <div x-show="activity.subject_type">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الموضوع</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <span x-text="activity.subject_type"></span>
                                            <span class="text-gray-500 dark:text-gray-400">#<span x-text="activity.subject_id"></span></span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Attribute Changes (with legacy fallback to properties) -->
                        <div x-data="{
                                 get changes() {
                                     return activity.attribute_changes || (activity.properties && (activity.properties.old || activity.properties.attributes) ? { old: activity.properties.old, attributes: activity.properties.attributes } : null);
                                 }
                             }"
                            x-show="changes"
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800/50">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-4 h-4 ml-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    التغييرات
                                </h4>
                            </div>
                            <div class="p-4">
                                <pre class="text-sm bg-gray-900 dark:bg-gray-900 rounded-lg p-4 text-green-400 overflow-x-auto font-mono"
                                    x-text="JSON.stringify(changes, null, 2)"></pre>
                            </div>
                        </div>

                        <!-- Custom Properties (excluding legacy diff keys) -->
                        <template x-if="activity.properties && Object.keys(activity.properties).filter(k => k !== 'old' && k !== 'attributes').length > 0">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800/50">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                                        <svg class="w-4 h-4 ml-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        خصائص إضافية
                                    </h4>
                                </div>
                                <div class="p-4">
                                    <pre class="text-sm bg-gray-900 dark:bg-gray-900 rounded-lg p-4 text-green-400 overflow-x-auto font-mono"
                                        x-text="JSON.stringify(Object.fromEntries(Object.entries(activity.properties || {}).filter(([k]) => k !== 'old' && k !== 'attributes')), null, 2)"></pre>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button @click="open = false"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    إغلاق
                </button>
            </div>
        </div>
    </div>
</div>
