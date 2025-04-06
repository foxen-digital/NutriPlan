<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookmarkIcon, BookOpen, CalendarIcon, Folder, HeartIcon, LayoutGrid, ShoppingBasketIcon, Tag } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Recipes',
        href: '/recipes',
        icon: LayoutGrid,
    },
    {
        title: 'Favorites',
        href: '/favorites',
        icon: HeartIcon,
    },
    {
        title: 'Collections',
        href: '/collections',
        icon: BookmarkIcon,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: Tag,
    },
    {
        title: 'Meal Plans',
        href: '/meal-plans',
        icon: CalendarIcon,
    },
    {
        title: 'Shopping Lists',
        href: '/shopping-lists',
        icon: ShoppingBasketIcon,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/mrdth/NutriPlan',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://github.com/mrdth/NutriPlan',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
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
