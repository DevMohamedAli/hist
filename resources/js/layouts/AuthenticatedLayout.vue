<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    Building2,
    CalendarRange,
    ChevronDown,
    Clock3,
    FileSpreadsheet,
    GraduationCap,
    LayoutDashboard,
    ListChecks,
    LogOut,
    Menu,
    PanelRightClose,
    Settings,
    ShieldCheck,
    UserCheck,
    UserPlus,
    Users,
    UsersRound,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import type { Component } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import RegistrationClosedDialog from '@/components/RegistrationClosedDialog.vue';
import { logout } from '@/routes';
import type { User } from '@/types';

interface NavItem {
    title: string;
    href: string;
    icon: Component;
    activePrefixes: string[];
    visible?: () => boolean;
}

interface NavSection {
    group: string;
    items: NavItem[];
}

interface PageProps {
    auth?: { user?: User };
    registration?: {
        is_open: boolean;
        message?: string;
        semester?: {
            code?: string | null;
            registration_start?: string | null;
            registration_end?: string | null;
        } | null;
    };
    [key: string]: unknown;
}

const page = usePage<PageProps>();
const sidebarOpen = ref(false);
const profileOpen = ref(false);
const desktopCollapsed = ref(false);
const registrationDialogOpen = ref(false);
const currentDateTime = ref(new Date());
const clockReady = ref(false);
let dateTimeInterval: ReturnType<typeof setInterval> | null = null;

const user = computed(() => page.props.auth?.user);
const registrationStatus = computed(() => page.props.registration ?? null);

const userRoles = computed(() => {
    const roles = user.value?.roles;

    return Array.isArray(roles)
        ? roles.map((role) => (typeof role === 'string' ? role : role.name))
        : [];
});

const userPermissions = computed(() => {
    const permissions = user.value?.permissions;

    return Array.isArray(permissions)
        ? permissions.map((permission) =>
              typeof permission === 'string' ? permission : permission.name,
          )
        : [];
});

const hasRole = (...roles: string[]) => {
    return (
        userRoles.value.includes('super_admin') ||
        roles.some((role) => userRoles.value.includes(role))
    );
};

const hasPermission = (permission: string) => {
    return (
        userRoles.value.includes('super_admin') ||
        userPermissions.value.includes(permission)
    );
};

const canUseEmployeeArea = computed(() => hasRole('employee'));
const canUseTeacherArea = computed(() => hasRole('teacher', 'employee'));
const canManageAccess = computed(() => hasPermission('manage access control'));
const canViewActivityLog = computed(() => hasRole('employee'));

const roleLabels: Record<string, string> = {
    super_admin: 'مدير عام',
    employee: 'موظف',
    teacher: 'عضو هيئة تدريس',
    student: 'طالب',
};

const displayRoles = computed(() => {
    return userRoles.value.map((role) => roleLabels[role] ?? role);
});

const userInitial = computed(() => user.value?.name?.trim().charAt(0) || 'م');
const userAvatar = computed(() =>
    typeof user.value?.avatar === 'string' && user.value.avatar.trim() !== ''
        ? user.value.avatar
        : null,
);

const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
const arabicDays = [
    'الأحد',
    'الاثنين',
    'الثلاثاء',
    'الأربعاء',
    'الخميس',
    'الجمعة',
    'السبت',
];
const arabicMonths = [
    'يناير',
    'فبراير',
    'مارس',
    'أبريل',
    'مايو',
    'يونيو',
    'يوليو',
    'أغسطس',
    'سبتمبر',
    'أكتوبر',
    'نوفمبر',
    'ديسمبر',
];

const toArabicDigits = (value: number | string) => String(value);

const tripoliDate = computed(() => {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Africa/Tripoli',
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
    }).formatToParts(currentDateTime.value);

    const year = Number(parts.find((part) => part.type === 'year')?.value);
    const month =
        Number(parts.find((part) => part.type === 'month')?.value) - 1;
    const day = Number(parts.find((part) => part.type === 'day')?.value);
    const weekday = new Date(Date.UTC(year, month, day)).getUTCDay();

    return { day, month, year, weekday };
});

const arabicDate = computed(() => {
    const date = tripoliDate.value;

    return `${arabicDays[date.weekday]}، ${toArabicDigits(date.day)} ${arabicMonths[date.month]} ${toArabicDigits(date.year)}`;
});

const arabicTime = computed(() =>
    new Intl.DateTimeFormat('ar-LY-u-nu-latn', {
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        timeZone: 'Africa/Tripoli',
    }).format(currentDateTime.value),
);

const navigation: NavSection[] = [
    {
        group: 'عام',
        items: [
            {
                title: 'لوحة التحكم',
                href: '/dashboard',
                icon: LayoutDashboard,
                activePrefixes: [
                    '/dashboard',
                    '/employee/dashboard',
                    '/teacher/dashboard',
                    '/student/dashboard',
                ],
            },
        ],
    },
    {
        group: 'الهيكل الأكاديمي',
        items: [
            {
                title: 'الأقسام العلمية',
                href: '/academic/departments',
                icon: Building2,
                activePrefixes: ['/academic/departments'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'التخصصات',
                href: '/academic/specializations',
                icon: GraduationCap,
                activePrefixes: ['/academic/specializations'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'المقررات الدراسية',
                href: '/academic/courses',
                icon: BookOpen,
                activePrefixes: ['/academic/courses'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'الفصول الدراسية',
                href: '/academic/semesters',
                icon: CalendarRange,
                activePrefixes: ['/academic/semesters'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'المجموعات الدراسية',
                href: '/academic/study-groups',
                icon: Users,
                activePrefixes: ['/academic/study-groups'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'سلامة البيانات',
                href: '/academic/audit',
                icon: ListChecks,
                activePrefixes: ['/academic/audit'],
                visible: () => canUseEmployeeArea.value,
            },
        ],
    },
    {
        group: 'هيئة التدريس',
        items: [
            {
                title: 'المحاضرون',
                href: '/staff/instructors',
                icon: UserCheck,
                activePrefixes: ['/staff/instructors'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'المؤهلات العلمية',
                href: '/qualifications',
                icon: Award,
                activePrefixes: ['/qualifications'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'إسناد المحاضرين',
                href: '/academic/course-classes',
                icon: UsersRound,
                activePrefixes: ['/academic/course-classes'],
                visible: () => canUseEmployeeArea.value,
            },
        ],
    },
    {
        group: 'إدارة الطلاب',
        items: [
            {
                title: 'الطلاب',
                href: '/students',
                icon: Users,
                activePrefixes: ['/students'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'تسجيل طالب جديد',
                href: '/students/create',
                icon: UserPlus,
                activePrefixes: ['/students/create'],
                visible: () => canUseEmployeeArea.value,
            },
            {
                title: 'الخريجون',
                href: '/graduations',
                icon: GraduationCap,
                activePrefixes: ['/graduations', '/graduation-records'],
                visible: () => hasPermission('manage graduations'),
            },
            {
                title: 'رصد الدرجات',
                href: '/grades',
                icon: Award,
                activePrefixes: ['/grades'],
                visible: () => canUseTeacherArea.value,
            },
            {
                title: 'استيراد البيانات',
                href: '/imports',
                icon: FileSpreadsheet,
                activePrefixes: ['/imports'],
                visible: () => hasPermission('manage imports'),
            },
        ],
    },
    {
        group: 'إدارة الموقع',
        items: [
            {
                title: 'لوحة الموقع',
                href: '/admin/website',
                icon: Building2,
                activePrefixes: ['/admin/website'],
                visible: () => hasPermission('website.pages.view'),
            },
            {
                title: 'عن المعهد',
                href: '/admin/website/pages',
                icon: BookOpen,
                activePrefixes: ['/admin/website/pages'],
                visible: () => hasPermission('website.pages.view'),
            },
            {
                title: 'الأخبار والإعلانات',
                href: '/admin/website/posts',
                icon: FileSpreadsheet,
                activePrefixes: ['/admin/website/posts'],
                visible: () => hasPermission('website.posts.view'),
            },
            {
                title: 'السلايدر والبنرات',
                href: '/admin/website/banners',
                icon: CalendarRange,
                activePrefixes: ['/admin/website/banners'],
                visible: () => hasPermission('website.banners.view'),
            },
            {
                title: 'الأسئلة الشائعة',
                href: '/admin/website/faqs',
                icon: ListChecks,
                activePrefixes: ['/admin/website/faqs'],
                visible: () => hasPermission('website.faqs.view'),
            },
            {
                title: 'إعدادات الموقع',
                href: '/admin/website/settings',
                icon: Settings,
                activePrefixes: ['/admin/website/settings'],
                visible: () => hasPermission('website.settings.manage'),
            },
            {
                title: 'رسائل التواصل',
                href: '/admin/website/contact-submissions',
                icon: Users,
                activePrefixes: ['/admin/website/contact-submissions'],
                visible: () => hasPermission('website.contact-submissions.view'),
            },
        ],
    },
    {
        group: 'النظام والإعدادات',
        items: [
            {
                title: 'الأدوار والصلاحيات',
                href: '/admin/access-control',
                icon: ShieldCheck,
                activePrefixes: ['/admin/access-control'],
                visible: () => canManageAccess.value,
            },
            {
                title: 'سجل النشاطات',
                href: '/employee/activity-logs',
                icon: ListChecks,
                activePrefixes: ['/employee/activity-logs'],
                visible: () => canViewActivityLog.value,
            },
            {
                title: 'إعدادات الحساب',
                href: '/settings/profile',
                icon: Settings,
                activePrefixes: [
                    '/settings/profile',
                    '/settings/security',
                    '/settings/appearance',
                ],
            },
        ],
    },
];

const visibleNavigation = computed<NavSection[]>(() =>
    navigation
        .map((section) => ({
            ...section,
            items: section.items.filter((item) => item.visible?.() ?? true),
        }))
        .filter((section) => section.items.length > 0),
);

const isActive = (item: NavItem) =>
    item.activePrefixes.some(
        (prefix) => page.url === prefix || page.url.startsWith(`${prefix}/`),
    );

const currentItem = computed(() =>
    visibleNavigation.value
        .flatMap((section) => section.items)
        .find((item) => isActive(item)),
);

const currentSection = computed(() =>
    visibleNavigation.value.find((section) =>
        section.items.some((item) => isActive(item)),
    ),
);

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const closeProfile = () => {
    profileOpen.value = false;
};

const openRegistrationDialog = () => {
    registrationDialogOpen.value = true;
};

const handleLogout = () => {
    router.flushAll();
};

onMounted(() => {
    clockReady.value = true;
    currentDateTime.value = new Date();

    dateTimeInterval = setInterval(() => {
        currentDateTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (dateTimeInterval) {
        clearInterval(dateTimeInterval);
    }
});
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 font-['Cairo'] text-slate-950"
        dir="rtl"
    >
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-sm lg:hidden"
            @click="closeSidebar"
        />

        <aside
            class="fixed inset-y-0 inset-s-0 z-50 flex transform flex-col bg-blue-900 text-white shadow-2xl transition-all duration-200 lg:translate-x-0"
            :class="[
                sidebarOpen
                    ? 'translate-x-0'
                    : 'translate-x-full lg:translate-x-0',
                desktopCollapsed ? 'lg:w-20' : 'w-72 lg:w-80',
            ]"
        >
            <div class="border-b border-white/10 p-4">
                <div class="flex items-start justify-between gap-3">
                    <Link
                        href="/dashboard"
                        class="flex min-w-0 items-start gap-3"
                        @click="closeSidebar"
                    >
                        <AppLogoIcon class="h-11 w-11 shrink-0 rounded-xl" />
                        <div v-show="!desktopCollapsed" class="min-w-0">
                            <p class="text-sm leading-6 font-extrabold">
                                المعهد العالي للعلوم والتقنية
                            </p>
                            <p class="text-sm font-bold text-orange-200">
                                العجيلات
                            </p>
                        </div>
                    </Link>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="hidden rounded-lg p-2 text-blue-100 hover:bg-white/10 lg:inline-flex"
                            :title="
                                desktopCollapsed
                                    ? 'توسيع القائمة'
                                    : 'طي القائمة'
                            "
                            @click="desktopCollapsed = !desktopCollapsed"
                        >
                            <PanelRightClose class="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-blue-100 hover:bg-white/10 lg:hidden"
                            @click="closeSidebar"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <p
                    v-show="!desktopCollapsed"
                    class="mt-4 rounded-lg bg-white/10 px-3 py-2 text-xs font-bold text-blue-50"
                >
                    منظومة الإدارة الأكاديمية
                </p>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
                <section
                    v-for="section in visibleNavigation"
                    :key="section.group"
                >
                    <h2
                        v-show="!desktopCollapsed"
                        class="mb-2 px-3 text-xs font-extrabold tracking-wide text-blue-100"
                    >
                        {{ section.group }}
                    </h2>

                    <div class="space-y-1">
                        <template v-for="item in section.items" :key="item.title">
                            <button
                                v-if="
                                    item.href === '/students/create' &&
                                    !registrationStatus?.is_open
                                "
                                type="button"
                                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-bold text-blue-100/75 transition hover:bg-white/10"
                                :title="desktopCollapsed ? item.title : undefined"
                                @click="openRegistrationDialog"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-5 w-5 shrink-0"
                                />
                                <span
                                    v-show="!desktopCollapsed"
                                    class="truncate"
                                >
                                    {{ item.title }}
                                </span>
                            </button>
                            <Link
                                v-else
                                :href="item.href"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-bold transition"
                                :class="
                                    isActive(item)
                                        ? 'bg-orange-500 text-white shadow-md'
                                        : 'text-blue-50 hover:bg-white/10'
                                "
                                :title="desktopCollapsed ? item.title : undefined"
                                @click="closeSidebar"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-5 w-5 shrink-0"
                                />
                                <span
                                    v-show="!desktopCollapsed"
                                    class="truncate"
                                >
                                    {{ item.title }}
                                </span>
                            </Link>
                        </template>
                    </div>
                </section>
            </nav>

            <div class="border-t border-white/10 p-3">
                <div
                    class="flex items-center gap-3 rounded-xl bg-white/10 p-3"
                    :class="desktopCollapsed ? 'justify-center' : ''"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white text-sm font-extrabold text-blue-900 ring-2 ring-white/20"
                    >
                        <img
                            v-if="userAvatar"
                            :src="userAvatar"
                            :alt="user?.name || 'صورة المستخدم'"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>{{ userInitial }}</span>
                    </div>
                    <div v-show="!desktopCollapsed" class="min-w-0">
                        <p class="truncate text-sm font-extrabold">
                            {{ user?.name || 'المستخدم' }}
                        </p>
                        <p class="truncate text-xs text-blue-100">
                            {{ displayRoles.join('، ') || 'حساب النظام' }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <div
            class="transition-all duration-200"
            :class="desktopCollapsed ? 'lg:ps-20' : 'lg:ps-80'"
        >
            <header
                class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur"
            >
                <div
                    class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <span class="sr-only">فتح القائمة</span>
                            <Menu class="h-6 w-6" />
                        </button>

                        <div class="min-w-0 text-start">
                            <p class="truncate text-xs font-bold text-gray-500">
                                {{ currentSection?.group ?? 'المسار الحالي' }}
                            </p>
                            <h1
                                class="truncate text-lg font-extrabold text-blue-900"
                            >
                                {{
                                    currentItem?.title ??
                                    'منظومة الإدارة الأكاديمية'
                                }}
                            </h1>
                        </div>
                    </div>

                    <div
                        v-if="clockReady"
                        class="hidden items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-2 text-start lg:flex"
                        title="التاريخ والوقت الحالي"
                    >
                        <Clock3 class="h-5 w-5 shrink-0 text-blue-800" />
                        <div class="leading-tight">
                            <p class="text-xs font-extrabold text-blue-950">
                                {{ arabicDate }}
                            </p>
                            <p class="mt-0.5 text-xs font-bold text-orange-600">
                                {{ arabicTime }}
                            </p>
                        </div>
                    </div>

                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-3 rounded-xl px-2 py-2 text-start transition hover:bg-gray-50 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1"
                            @click="profileOpen = !profileOpen"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-extrabold text-blue-800 ring-2 ring-orange-100"
                            >
                                <img
                                    v-if="userAvatar"
                                    :src="userAvatar"
                                    :alt="user?.name || 'صورة المستخدم'"
                                    class="h-full w-full object-cover"
                                />
                                <span v-else>{{ userInitial }}</span>
                            </div>
                            <div class="hidden min-w-0 sm:block">
                                <p
                                    class="truncate text-sm font-bold text-gray-900"
                                >
                                    {{ user?.name || 'المستخدم' }}
                                </p>
                                <p class="truncate text-xs text-gray-500">
                                    {{ user?.email || 'حساب النظام' }}
                                </p>
                            </div>
                            <ChevronDown class="h-4 w-4 text-gray-500" />
                        </button>

                        <div
                            v-if="profileOpen"
                            class="absolute inset-e-0 mt-2 w-72 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl"
                        >
                            <div
                                class="border-b border-gray-100 px-4 py-3 text-start"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-extrabold text-blue-800 ring-2 ring-orange-100"
                                    >
                                        <img
                                            v-if="userAvatar"
                                            :src="userAvatar"
                                            :alt="user?.name || 'صورة المستخدم'"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else>{{ userInitial }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-extrabold text-gray-950"
                                        >
                                            {{ user?.name || 'المستخدم' }}
                                        </p>
                                        <p
                                            class="mt-1 truncate text-xs text-gray-500"
                                        >
                                            {{ user?.email || 'حساب النظام' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span
                                        v-for="role in displayRoles"
                                        :key="role"
                                        class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-bold text-blue-800"
                                    >
                                        {{ role }}
                                    </span>
                                </div>
                            </div>

                            <Link
                                href="/settings/profile"
                                class="flex w-full items-center gap-2 px-4 py-3 text-start text-sm font-bold text-gray-700 hover:bg-blue-50"
                                @click="closeProfile"
                            >
                                <Settings class="h-5 w-5 text-blue-800" />
                                <span>إعدادات الحساب</span>
                            </Link>

                            <Link
                                :href="logout()"
                                as="button"
                                method="post"
                                class="flex w-full items-center gap-2 px-4 py-3 text-start text-sm font-bold text-red-600 hover:bg-red-50"
                                @click="handleLogout"
                            >
                                <LogOut class="h-5 w-5" />
                                <span>تسجيل الخروج</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main class="min-h-[calc(100vh-4rem)] bg-slate-50">
                <slot />
            </main>
        </div>

        <RegistrationClosedDialog
            v-model:open="registrationDialogOpen"
            :registration="registrationStatus"
        />
    </div>
</template>
