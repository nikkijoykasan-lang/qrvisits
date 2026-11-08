<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarClock,
    ClipboardList,
    ScanLine,
    Siren,
    TriangleAlert,
    UserPlus,
    Users,
} from '@lucide/vue';
import EmergencyListController from '@/actions/App/Http/Controllers/EmergencyListController';
import { index as gateIndex } from '@/actions/App/Http/Controllers/GateController';
import { index as liveBoardIndex } from '@/actions/App/Http/Controllers/LiveBoardController';
import { index as approvalsIndex } from '@/actions/App/Http/Controllers/VisitApprovalController';
import {
    index as visitsIndex,
    show,
} from '@/actions/App/Http/Controllers/VisitController';
import { create as walkInCreate } from '@/actions/App/Http/Controllers/WalkInController';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import VisitStatusBadge from '@/components/VisitStatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatTime } from '@/lib/datetime';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Visit, VisitStats } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    stats: VisitStats;
    pendingVisits: Visit[];
    insideVisits: Visit[];
    canApprove: boolean;
}>();

const quickActions = [
    {
        title: 'Gate scan',
        description: 'Scan a visitor QR pass',
        href: gateIndex(),
        icon: ScanLine,
    },
    {
        title: 'Live board',
        description: 'See who is inside now',
        href: liveBoardIndex(),
        icon: Users,
    },
    {
        title: 'Register walk-in',
        description: 'Visitors without a pass',
        href: walkInCreate(),
        icon: UserPlus,
    },
    {
        title: 'Emergency list',
        description: 'Printable list of everyone inside',
        href: EmergencyListController(),
        icon: Siren,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-xl font-semibold">Guard dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Scan passes, register walk-ins, and watch who is on campus.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <StatCard
                label="Inside now"
                :value="stats.inside"
                hint="People currently on campus"
                :icon="Users"
            />
            <StatCard
                label="Expected today"
                :value="stats.expected_today"
                hint="Approved passes for today"
                :icon="CalendarClock"
            />
            <StatCard
                label="Checked out today"
                :value="stats.completed_today"
                :icon="ScanLine"
            />
            <StatCard
                label="Waiting for approval"
                :value="stats.pending"
                hint="Admin must approve these"
                :icon="ClipboardList"
            />
            <StatCard
                label="Overdue"
                :value="stats.overdue"
                hint="Past expected time-out"
                :icon="TriangleAlert"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="action in quickActions"
                :key="action.title"
                :href="action.href"
                class="group rounded-xl border p-4 transition hover:border-foreground/20 hover:bg-muted/50"
            >
                <component
                    :is="action.icon"
                    class="size-5 text-muted-foreground group-hover:text-foreground"
                />
                <p class="mt-2 text-sm font-medium">{{ action.title }}</p>
                <p class="text-xs text-muted-foreground">
                    {{ action.description }}
                </p>
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader class="flex flex-row items-start justify-between">
                    <div>
                        <CardTitle>Inside right now</CardTitle>
                        <CardDescription>
                            First scan in, next scan out.
                        </CardDescription>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="visitsIndex()">All visits</Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="visit in insideVisits"
                        :key="visit.id"
                        class="flex items-start justify-between gap-3 rounded-lg border p-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ visit.visitor_name }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ visit.purpose }} · {{ visit.host_name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                In at {{ formatTime(visit.time_in) }}
                            </p>
                        </div>
                        <VisitStatusBadge
                            :status="visit.status"
                            :label="visit.status_label"
                        />
                    </div>
                    <EmptyState
                        v-if="insideVisits.length === 0"
                        title="Nobody is inside"
                        description="Scanning a pass will add visitors here."
                        :icon="Users"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-start justify-between">
                    <div>
                        <CardTitle>Waiting for approval</CardTitle>
                        <CardDescription>
                            {{
                                canApprove
                                    ? 'Approve or deny these requests.'
                                    : 'Admins complete approval at the office.'
                            }}
                        </CardDescription>
                    </div>
                    <Button
                        v-if="canApprove"
                        variant="outline"
                        size="sm"
                        as-child
                    >
                        <Link :href="approvalsIndex()">Review</Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="visit in pendingVisits"
                        :key="visit.id"
                        class="flex items-start justify-between gap-3 rounded-lg border p-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ visit.visitor_name }}
                                <span
                                    v-if="visit.is_walk_in"
                                    class="ml-1 rounded bg-muted px-1.5 py-0.5 text-[10px] tracking-wide text-muted-foreground uppercase"
                                >
                                    Walk-in
                                </span>
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ visit.purpose }} · {{ visit.host_name }}
                            </p>
                        </div>
                        <Link
                            :href="show(visit.id)"
                            class="text-xs font-medium underline underline-offset-4"
                        >
                            Details
                        </Link>
                    </div>
                    <EmptyState
                        v-if="pendingVisits.length === 0"
                        title="No pending requests"
                        description="All visit requests have been reviewed."
                        :icon="ClipboardList"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
