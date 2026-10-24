<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarCheck, CalendarPlus, Clock3, Ticket } from '@lucide/vue';
import {
    create,
    index,
    show,
} from '@/actions/App/Http/Controllers/VisitController';
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
import { formatDate, formatTime } from '@/lib/datetime';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Visit, VisitorStats } from '@/types';

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
    stats: VisitorStats;
    nextVisit: Visit | null;
    recentVisits: Visit[];
}>();

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-xl font-semibold">Welcome, {{ user.name }}</h1>
                <p class="text-sm text-muted-foreground">
                    Request a campus visit and show your QR pass at the gate.
                </p>
            </div>
            <Button as-child>
                <Link :href="create()">
                    <CalendarPlus class="size-4" />
                    Request a visit
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Pending approval" :value="stats.pending" />
            <StatCard label="Approved" :value="stats.approved" />
            <StatCard label="Total requests" :value="stats.total" />
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <Card class="lg:col-span-3">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Ticket class="size-4" />
                        Your next visit
                    </CardTitle>
                    <CardDescription>
                        Show this pass at the guard house on your visit date.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="nextVisit"
                        class="flex flex-col gap-5 sm:flex-row sm:items-center"
                    >
                        <div
                            class="mx-auto w-fit rounded-xl border bg-white p-3 shadow-sm"
                        >
                            <img
                                v-if="nextVisit.pass_qr"
                                :src="nextVisit.pass_qr"
                                alt="QR pass for this visit"
                                class="size-40"
                            />
                        </div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <VisitStatusBadge
                                :status="nextVisit.status"
                                :label="nextVisit.status_label"
                            />
                            <p class="truncate text-sm font-medium">
                                {{ nextVisit.purpose }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(nextVisit.visit_date) }}
                                <span v-if="nextVisit.expected_arrival">
                                    · arrival
                                    {{ formatTime(nextVisit.expected_arrival) }}
                                </span>
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Host: {{ nextVisit.host_name }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Pass code:
                                <span
                                    class="font-mono text-base font-semibold tracking-widest text-foreground"
                                >
                                    {{ nextVisit.pass_code }}
                                </span>
                            </p>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="show(nextVisit.id)">
                                    View pass
                                </Link>
                            </Button>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="No approved visit yet"
                        description="Once the admin approves your request, your QR pass will appear here."
                        :icon="Ticket"
                    >
                        <Button size="sm" as-child>
                            <Link :href="create()">Request a visit</Link>
                        </Button>
                    </EmptyState>
                </CardContent>
            </Card>

            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock3 class="size-4" />
                        Recent requests
                    </CardTitle>
                    <CardDescription>
                        Your latest visit requests and their status.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="visit in recentVisits"
                        :key="visit.id"
                        class="flex items-start justify-between gap-3 rounded-lg border p-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ visit.purpose }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ formatDate(visit.visit_date) }} ·
                                {{ visit.host_name }}
                            </p>
                        </div>
                        <VisitStatusBadge
                            :status="visit.status"
                            :label="visit.status_label"
                        />
                    </div>
                    <EmptyState
                        v-if="recentVisits.length === 0"
                        title="No requests yet"
                        description="Start by requesting your first campus visit."
                        :icon="CalendarCheck"
                    />
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="index()">View all visits</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
