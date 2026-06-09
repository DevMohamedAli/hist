<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('activitylog-ui.ui.brand', 'سجل النشاطات') }}</title>

    <!-- Favicon (if exists) -->
    @if(file_exists(public_path('vendor/activitylog-ui/images/favicon.svg')))
    <link rel="icon" type="image/svg+xml" href="{{ asset('vendor/activitylog-ui/images/favicon.svg') }}">
    @endif
    @if(file_exists(public_path('vendor/activitylog-ui/images/favicon.ico')))
    <link rel="icon" type="image/x-icon" href="{{ asset('vendor/activitylog-ui/images/favicon.ico') }}">
    @endif

    <!-- Cairo Font (matching university style) -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af', // blue-800
                            900: '#1e3a8a',
                        },
                        orange: {
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js with collapse plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- University Theme Overrides -->
    <link rel="stylesheet" href="{{ asset('assets/activitylog-ui-theme.css') }}">

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar - RTL friendly */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #1f2937;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        /* Flip icons for RTL where needed (chevrons, pagination arrows) */
        [dir="rtl"] .flip-rtl {
            transform: scaleX(-1);
        }
    </style>

    @stack('head')
</head>

<body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-900"
    x-data
    x-init="$store.darkMode.init()"
    :class="{ 'dark': $store.darkMode.on }">
    <div class="min-h-full">
        <!-- Navigation Header - University Style -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 activitylog-university-nav">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo and Brand -->
                        <div class="shrink-0 flex items-center">
                            @if(config('activitylog-ui.ui.logo'))
                            <img class="h-8 w-auto" src="{{ config('activitylog-ui.ui.logo') }}" alt="{{ config('activitylog-ui.ui.brand') }}">
                            @else
                            <!-- University Logo SVG (blue/orange) -->
                            <svg class="h-8 w-auto" width="120" height="40" viewBox="0 0 120 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="4" width="32" height="32" rx="8" fill="#1e40af" />
                                <path d="M18 12L12 16V24L18 28L24 24V16L18 12Z" fill="white" fill-opacity="0.9" />
                                <text x="42" y="18" font-family="Cairo, sans-serif" font-size="14" font-weight="700" fill="#1e40af">HIST</text>
                                <text x="42" y="30" font-family="Cairo, sans-serif" font-size="10" font-weight="600" fill="#f97316">سجل النشاطات</text>
                            </svg>
                            @endif
                        </div>

                        <!-- Navigation Links (RTL order) -->
                        <div class="hidden sm:mr-8 sm:flex sm:space-x-8 sm:space-x-reverse">
                            <a href="{{ route('activitylog-ui.dashboard') }}"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors
                                      {{ request()->routeIs('activitylog-ui.dashboard')
                                         ? 'border-orange-500 text-blue-800 dark:text-white'
                                         : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-blue-300 hover:text-blue-700 dark:hover:text-gray-300' }}">
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5h2a2 2 0 012 2v6a2 2 0 01-2 2h-2a2 2 0 01-2-2V7a2 2 0 012-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6"></path>
                                </svg>
                                {{ __('سجل النشاطات') }}
                            </a>
                            <a href="{{ route('employee.dashboard') }}"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 dark:text-gray-400 hover:border-orange-300 hover:text-blue-800 dark:hover:text-gray-300 transition-colors">
                                لوحة الموظفين
                            </a>
                        </div>
                    </div>

                    <!-- Right side (dark mode toggle + user menu) -->
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <!-- Dark mode toggle -->
                        <button @click="$store.darkMode.toggle()"
                            class="p-2 rounded-md text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg x-show="!$store.darkMode.on" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <svg x-show="$store.darkMode.on" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>

                        <!-- User menu -->
                        @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                @click.away="open = false"
                                class="flex items-center space-x-3 space-x-reverse p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                <span class="text-sm">{{ auth()->user()->name ?? auth()->user()->email }}</span>
                                <div class="w-8 h-8 rounded-full bg-linear-to-br from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 flex items-center justify-center shadow-sm">
                                    <span class="text-sm font-medium text-white">
                                        {{ substr(auth()->user()->name ?? auth()->user()->email, 0, 1) }}
                                    </span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 z-50">
                                <div class="py-1">
                                    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name ?? 'مستخدم' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}" class="block">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-right px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                            <div class="flex items-center justify-end">
                                                <span>تسجيل الخروج</span>
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-6">
            @yield('content')
        </main>

        <!-- Global Notifications -->
        <div x-data="notifications()"
            x-init="init()"
            class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50">
            <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
                <template x-for="notification in notifications" :key="notification.id">
                    <div x-show="notification.show"
                        x-transition:enter="transform ease-out duration-300"
                        x-transition:enter-start="translate-x-full opacity-0"
                        x-transition:enter-end="translate-x-0 opacity-100"
                        x-transition:leave="transform ease-in duration-200"
                        x-transition:leave-start="translate-x-0 opacity-100"
                        x-transition:leave-end="translate-x-full opacity-0"
                        class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10">
                        <div class="flex-1 w-0 p-4">
                            <div class="flex items-start">
                                <div class="shrink-0">
                                    <svg x-show="notification.type === 'success'" class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <svg x-show="notification.type === 'error'" class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <svg x-show="notification.type === 'warning'" class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <svg x-show="notification.type === 'info'" class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="mr-3 w-0 flex-1 pt-0.5">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="notification.title"></p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="notification.message"></p>
                                </div>
                            </div>
                        </div>
                        <div class="flex border-r border-gray-200 dark:border-gray-700">
                            <button @click="remove(notification.id)"
                                class="w-full border border-transparent rounded-none rounded-l-lg p-4 flex items-center justify-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Scripts & Global Helpers -->
    @stack('scripts')

    <script>
        // Notification system (Arabic)
        function notifications() {
            return {
                notifications: [],
                init() {
                    window.notify = {
                        success: (title, message) => this.add('success', title, message),
                        error: (title, message) => this.add('error', title, message),
                        warning: (title, message) => this.add('warning', title, message),
                        info: (title, message) => this.add('info', title, message)
                    };
                },
                add(type, title, message) {
                    const id = Date.now() + Math.random();
                    this.notifications.push({
                        id,
                        type,
                        title,
                        message,
                        show: true
                    });
                    setTimeout(() => this.remove(id), 5000);
                },
                remove(id) {
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index > -1) {
                        this.notifications[index].show = false;
                        setTimeout(() => this.notifications.splice(index, 1), 300);
                    }
                }
            };
        }

        // Dark mode store
        document.addEventListener('alpine:init', () => {
            Alpine.store('darkMode', {
                on: false,
                toggle() {
                    this.on = !this.on;
                    localStorage.setItem('darkMode', this.on);
                },
                init() {
                    this.on = localStorage.getItem('darkMode') === 'true' ||
                        (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                }
            });
        });

        // Activity Type Styler (same as before, but kept for compatibility)
        window.ActivityTypeStyler = {
            getColor(event) {
                const colors = {
                    created: 'green',
                    updated: 'blue',
                    deleted: 'red',
                    restored: 'yellow',
                    login: 'purple',
                    logout: 'indigo'
                };
                return colors[event] || 'gray';
            },
            getBadgeClasses(event) {
                const color = this.getColor(event);
                return `bg-${color}-100 dark:bg-${color}-900/30 text-${color}-800 dark:text-${color}-300 border-${color}-200 dark:border-${color}-800`;
            },
            getTimelineClasses(event) {
                const color = this.getColor(event);
                return `bg-gradient-to-br from-${color}-500 to-${color}-600 dark:from-${color}-400 dark:to-${color}-500`;
            },
            getIcon(event) {
                const icons = {
                    created: 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                    updated: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                    deleted: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'
                };
                return icons[event] || 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
            }
        };

        window.ActivitylogUi = {
            async parseJsonResponse(response, context) {
                const body = await response.text();
                if (!response.ok) throw new Error(`${context} failed with HTTP ${response.status}: ${body.slice(0, 100)}`);
                try {
                    return JSON.parse(body);
                } catch (e) {
                    throw new Error(`${context} returned invalid JSON: ${body.slice(0, 100)}`);
                }
            }
        };

        // Export function (Arabic notifications)
        window.exportData = async function(format, filters = {}) {
            try {
                if (window.notify) window.notify.success('بدء التصدير', `جاري تصدير النشاطات بصيغة ${format.toUpperCase()}...`);
                const response = await fetch('{{ route("activitylog-ui.api.export") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        format,
                        filters
                    })
                });
                const result = await window.ActivitylogUi.parseJsonResponse(response, 'تصدير النشاطات');
                if (result.download_url) {
                    window.location.href = result.download_url;
                    if (window.notify) window.notify.success('اكتمل التصدير', `تم تصدير النشاطات كملف ${format.toUpperCase()}`);
                } else if (result.job_id && window.notify) {
                    window.notify.info('قيد المعالجة', 'التصدير كبير الحجم وسيتم إشعارك عند اكتماله.');
                }
            } catch (error) {
                console.error(error);
                if (window.notify) window.notify.error('فشل التصدير', 'حدث خطأ أثناء التصدير. حاول مرة أخرى.');
            }
        };

        <?php if (config('activitylog-ui.features.saved_views', true)): ?>
            window.saveView = async function(viewName, filters) {
                if (!viewName.trim()) return;
                try {
                    const response = await fetch('{{ route("activitylog-ui.api.views.save") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            name: viewName,
                            filters
                        })
                    });
                    await window.ActivitylogUi.parseJsonResponse(response, 'حفظ العرض');
                    if (window.notify) window.notify.success('نجاح', `تم حفظ العرض "${viewName}"`);
                    window.dispatchEvent(new CustomEvent('saved-views-updated'));
                } catch (error) {
                    if (window.notify) window.notify.error('خطأ', 'فشل حفظ العرض');
                }
            };
        <?php endif; ?>
    </script>
</body>

</html>