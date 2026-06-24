<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FileText,
    FolderGit2,
    Globe2,
    Inbox,
    LayoutGrid,
    MessageSquare,
    Settings,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem, User } from '@/types';

const page = usePage<{ auth: { user: User | null } }>();

const permissionNames = () => {
    const permissions = page.props.auth.user?.permissions;

    return Array.isArray(permissions)
        ? permissions.map((permission) =>
              typeof permission === 'string' ? permission : permission.name,
          )
        : [];
};

const hasPermission = (permission: string) =>
    permissionNames().includes(permission);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    ...(hasPermission('correspondence.view-own')
        ? [
              {
                  title: 'Correspondence',
                  href: '/correspondence/inbox',
                  icon: Inbox,
              },
              {
                  title: 'Sent Correspondence',
                  href: '/correspondence/sent',
                  icon: FileText,
              },
          ]
        : []),
    ...(hasPermission('website.pages.view')
        ? [
              {
                  title: 'Website Admin',
                  href: '/admin/website',
                  icon: Globe2,
              },
              {
                  title: 'عن المعهد',
                  href: '/admin/website/pages',
                  icon: FileText,
              },
              {
                  title: 'Website News',
                  href: '/admin/website/posts',
                  icon: FileText,
              },
          ]
        : []),
    ...(hasPermission('website.banners.view')
        ? [
              {
                  title: 'Website Slider',
                  href: '/admin/website/banners',
                  icon: Globe2,
              },
          ]
        : []),
    ...(hasPermission('website.faqs.view')
        ? [
              {
                  title: 'Website FAQs',
                  href: '/admin/website/faqs',
                  icon: FileText,
              },
          ]
        : []),
    ...(hasPermission('website.settings.manage')
        ? [
              {
                  title: 'Website Settings',
                  href: '/admin/website/settings',
                  icon: Settings,
              },
          ]
        : []),
    ...(hasPermission('website.contact-submissions.view')
        ? [
              {
                  title: 'Website Messages',
                  href: '/admin/website/contact-submissions',
                  icon: MessageSquare,
              },
          ]
        : []),
];

const footerNavItems: NavItem[] = [
    {
        title: 'Ministry of Technical and Vocational Education',
        href: 'https://tve.gov.ly/',
        icon: FolderGit2,
    },
    {
        title: 'Unified Electronic Services Portal',
        href: 'https://e.tve.gov.ly/',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" side="right" dir="rtl">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
