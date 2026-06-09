<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    LayoutDashboard,
    Palette,
    ShieldCheck,
    UserRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const settingsNavItems: NavItem[] = [
    {
        title: 'الملف الشخصي',
        href: editProfile(),
        icon: UserRound,
    },
    {
        title: 'الأمان',
        href: editSecurity(),
        icon: ShieldCheck,
    },
    {
        title: 'الواجهة',
        href: editAppearance(),
        icon: Palette,
    },
];

const page = usePage();
const { isCurrentOrParentUrl } = useCurrentUrl();

const user = computed(() => page.props.auth.user as { name?: string; email?: string });
</script>

<template>
    <main class="min-h-screen bg-slate-50 text-slate-950" dir="rtl">
        <header class="border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-11 items-center justify-center rounded-2xl bg-blue-950 text-white shadow-sm">
                            <AppLogoIcon class="size-7 fill-current" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-950">
                                منظومة إدارة المعهد
                            </p>
                            <p class="truncate text-xs font-bold text-slate-500">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            class="h-10 gap-2 rounded-xl border-slate-200 bg-white font-black"
                            as-child
                        >
                            <Link :href="dashboard()">
                                <LayoutDashboard class="h-4 w-4" />
                                لوحة التحكم
                            </Link>
                        </Button>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <Link
                            :href="dashboard()"
                            class="inline-flex items-center gap-2 text-sm font-black text-blue-900 transition hover:text-orange-600"
                        >
                            <ArrowRight class="h-4 w-4" />
                            العودة للنظام
                        </Link>
                        <h1 class="mt-3 text-2xl font-black tracking-normal text-slate-950 md:text-3xl">
                            إعدادات الحساب
                        </h1>
                        <p class="mt-1 text-sm font-bold text-slate-500">
                            إدارة بياناتك الشخصية، الأمان، وتفضيلات الواجهة.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600">
                        {{ user.name }}
                    </div>
                </div>

                <nav
                    class="flex w-full flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm"
                    aria-label="إعدادات الحساب"
                >
                    <Button
                        v-for="item in settingsNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'h-11 min-w-36 flex-1 justify-center gap-2 rounded-xl text-sm font-black sm:flex-none sm:px-5',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-blue-50 text-blue-900 shadow-sm ring-1 ring-blue-100'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </Button>
                </nav>
            </div>
        </header>

        <section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </section>
    </main>
</template>
