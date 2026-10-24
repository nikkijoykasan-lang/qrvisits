<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    CalendarCheck,
    ClipboardList,
    LayoutGrid,
    ScanLine,
    ShieldAlert,
    Siren,
    UserCog,
    UserPlus,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import EmergencyListController from '@/actions/App/Http/Controllers/EmergencyListController';
import { index as gateIndex } from '@/actions/App/Http/Controllers/GateController';
import { index as liveBoardIndex } from '@/actions/App/Http/Controllers/LiveBoardController';
import { index as reportIndex } from '@/actions/App/Http/Controllers/ReportController';
import { index as staffIndex } from '@/actions/App/Http/Controllers/StaffAccountController';
import { index as approvalsIndex } from '@/actions/App/Http/Controllers/VisitApprovalController';
import { index as visitsIndex } from '@/actions/App/Http/Controllers/VisitController';
import { create as walkInCreate } from '@/actions/App/Http/Controllers/WalkInController';
import AppLogo from '@/components/AppLogo.vue';
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
import type { NavItem } from '@/types';

const page = usePage();
const role = computed(() => page.props.auth.user.role);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: role.value === 'visitor' ? 'My visits' : 'Visits',
            href: visitsIndex(),
            icon: CalendarCheck,
        },
    ];

    if (role.value === 'admin') {
        items.push({
            title: 'Approvals',
            href: approvalsIndex(),
            icon: ClipboardList,
        });
    }

    if (role.value !== 'visitor') {
        items.push(
            {
                title: 'Gate scan',
                href: gateIndex(),
                icon: ScanLine,
            },
            {
                title: 'Live board',
                href: liveBoardIndex(),
                icon: Users,
            },
            {
                title: 'Register walk-in',
                href: walkInCreate(),
                icon: UserPlus,
            },
            {
                title: 'Emergency list',
                href: EmergencyListController(),
                icon: Siren,
            },
        );
    }

    if (role.value === 'admin') {
        items.push(
            {
                title: 'Reports',
                href: reportIndex(),
                icon: BarChart3,
            },
            {
                title: 'Staff accounts',
                href: staffIndex(),
                icon: UserCog,
            },
        );
    }

    return items;
});

const roleLabel = computed(() => {
    switch (role.value) {
        case 'admin':
            return 'Administrator';
        case 'guard':
            return 'Security guard';
        default:
            return 'Visitor';
    }
});
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
            <div class="px-4 pt-2 group-data-[collapsible=icon]:hidden">
                <p
                    class="flex items-center gap-1.5 text-xs text-muted-foreground"
                >
                    <ShieldAlert class="size-3.5" />
                    {{ roleLabel }}
                </p>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
