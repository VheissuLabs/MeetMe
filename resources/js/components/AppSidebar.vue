<script setup lang="ts">
    import { Link, usePage } from '@inertiajs/vue3'
    import { FolderGit2, IdCard, LayoutGrid, ScanLine, Trophy, Users } from '@lucide/vue'
    import { computed } from 'vue'
    import AppLogo from '@/components/AppLogo.vue'
    import NavFooter from '@/components/NavFooter.vue'
    import NavMain from '@/components/NavMain.vue'
    import NavUser from '@/components/NavUser.vue'
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar'
    import { dashboard } from '@/routes'
    import { show as userProfile } from '@/routes/users'
    import type { NavItem } from '@/types'

    const page = usePage()

    const mainNavItems = computed<NavItem[]>(() => [
        {
            title: 'My QR',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'My Profile',
            href: userProfile(page.props.auth.user.qr_token),
            icon: IdCard,
        },
        {
            title: 'Scan',
            href: '/scan',
            icon: ScanLine,
        },
        {
            title: 'Connections',
            href: '/connections',
            icon: Users,
        },
        {
            title: 'Leaderboard',
            href: '/leaderboard',
            icon: Trophy,
        },
    ])

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/VheissuLabs/MeetMe',
            icon: FolderGit2,
        },
    ]
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
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
