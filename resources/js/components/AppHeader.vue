<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu, Search } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

// محاذاة ألوان العناصر النشطة لتتوافق مع ألوان المعهد الرسمية
const activeItemStyles =
    'text-blue-800 dark:text-blue-400 font-semibold bg-blue-50/50 dark:bg-blue-950/20';

// تحويل الروابط من المسميات البرمجية الافتراضية إلى روابط أكاديمية فعلية تخدم المنظومة
const mainNavItems: NavItem[] = [
    {
        title: 'الرئيسية',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [
    {
        title: 'المقررات الدراسية',
        href: '/academic/courses',
        icon: Folder,
    },
    {
        title: 'التقويم الأكاديمي',
        href: '/academic/semesters',
        icon: BookOpen,
    },
];
</script>

<template>
    <!-- جعل رأس الصفحة مثبتاً في الأعلى مع خلفية زجاجية ضبابية عصرية -->
    <div
        class="sticky top-0 z-40 w-full border-b border-neutral-200/80 bg-white/85 backdrop-blur-md transition-all dark:border-neutral-800/80 dark:bg-neutral-900/85"
    >
        <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
            <!-- Mobile Menu Drawer (يدعم الهوامش المنطقية للغات) -->
            <div class="lg:hidden">
                <Sheet>
                    <SheetTrigger :as-child="true">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="me-2 h-9 w-9"
                        >
                            <Menu class="h-5 w-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-75 p-6" dir="rtl">
                        <SheetTitle class="sr-only">قائمة التنقل</SheetTitle>
                        <SheetHeader class="flex justify-start text-start">
                            <!-- الشعار الجانبي للهاتف محدد القياس بدقة -->
                            <AppLogoIcon
                                class="h-10 w-auto text-blue-800 dark:text-white"
                            />
                        </SheetHeader>
                        <div
                            class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                        >
                            <nav class="-mx-3 space-y-1">
                                <Link
                                    v-for="item in mainNavItems"
                                    :key="item.title"
                                    :href="item.href"
                                    class="flex items-center gap-x-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                    :class="
                                        whenCurrentUrl(
                                            item.href,
                                            activeItemStyles,
                                        )
                                    "
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="h-5 w-5 text-neutral-500"
                                    />
                                    {{ item.title }}
                                </Link>
                            </nav>
                            <div class="flex flex-col space-y-3 border-t pt-4">
                                <a
                                    v-for="item in rightNavItems"
                                    :key="item.title"
                                    :href="toUrl(item.href)"
                                    class="flex items-center gap-x-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="h-5 w-5 text-neutral-500"
                                    />
                                    <span>{{ item.title }}</span>
                                </a>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>

            <!-- رابط الشعار الرئيسي (محدد الارتفاع والأبعاد بدقة عالية) -->
            <Link
                :href="dashboard()"
                class="flex h-10 items-center justify-center transition-opacity hover:opacity-90"
            >
                <AppLogo class="h-11 w-auto max-w-50 object-contain" />
            </Link>

            <!-- Desktop Menu (استخدام gap-x بدلاً من space-x لتفادي مشاكل الـ RTL) -->
            <div class="hidden h-full lg:flex lg:flex-1">
                <NavigationMenu class="ms-10 flex h-full items-stretch">
                    <NavigationMenuList
                        class="flex h-full items-stretch gap-x-2"
                    >
                        <NavigationMenuItem
                            v-for="(item, index) in mainNavItems"
                            :key="index"
                            class="relative flex h-full items-center"
                        >
                            <Link
                                :class="[
                                    navigationMenuTriggerStyle(),
                                    whenCurrentUrl(item.href, activeItemStyles),
                                    'h-9 cursor-pointer px-4 font-medium transition-colors',
                                ]"
                                :href="item.href"
                            >
                                <component
                                    v-if="item.icon"
                                    :is="item.icon"
                                    class="me-2 h-4 w-4"
                                />
                                {{ item.title }}
                            </Link>
                            <!-- مؤشر التحديد السفلي البرتقالي المتناسق مع الشعار والمنظومة -->
                            <div
                                v-if="isCurrentUrl(item.href)"
                                class="absolute bottom-0 left-0 h-0.5 w-full bg-orange-500"
                            ></div>
                        </NavigationMenuItem>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>

            <!-- قائمة الأدوات اليمنى والملف الشخصي للمستخدم -->
            <div class="ms-auto flex items-center gap-x-3">
                <div class="relative flex items-center gap-x-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="group h-9 w-9 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    >
                        <Search
                            class="size-5 opacity-80 group-hover:opacity-100"
                        />
                    </Button>

                    <div class="hidden gap-x-1 lg:flex">
                        <template
                            v-for="item in rightNavItems"
                            :key="item.title"
                        >
                            <TooltipProvider :delay-duration="0">
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                            class="group h-9 w-9 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                        >
                                            <a :href="toUrl(item.href)">
                                                <span class="sr-only">{{
                                                    item.title
                                                }}</span>
                                                <component
                                                    :is="item.icon"
                                                    class="size-5 opacity-80 group-hover:opacity-100"
                                                />
                                            </a>
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ item.title }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </template>
                    </div>
                </div>

                <!-- قائمة حساب المستخدم الشخصي -->
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="relative size-10 w-auto cursor-pointer rounded-full p-1 focus-within:ring-2 focus-within:ring-blue-800 focus-within:ring-offset-2"
                        >
                            <Avatar
                                class="size-8 overflow-hidden rounded-full border border-neutral-200 dark:border-neutral-700"
                            >
                                <AvatarImage
                                    v-if="auth.user.avatar"
                                    :src="auth.user.avatar"
                                    :alt="auth.user.name"
                                />
                                <AvatarFallback
                                    class="rounded-lg bg-neutral-200 text-xs font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                >
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="mt-2 w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- ترويسة مسار الصفحة (Breadcrumbs) لدعم الملاحة السهلة -->
        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-t border-neutral-200/50 bg-neutral-50/50 dark:border-neutral-800/50 dark:bg-neutral-900/30"
        >
            <div
                class="mx-auto flex h-10 w-full items-center justify-start px-4 text-xs text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
