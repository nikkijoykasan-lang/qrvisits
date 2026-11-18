<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Building2,
    CalendarCheck,
    CalendarDays,
    CalendarPlus,
    ChevronRight,
    Clock3,
    QrCode,
    Ticket,
    UserPlus,
} from '@lucide/vue';
import {
    create,
    index,
    show,
} from '@/actions/App/Http/Controllers/VisitController';
import { create as walkInCreate } from '@/actions/App/Http/Controllers/WalkInController';
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

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <p
                    class="text-[11px] font-semibold tracking-[0.18em] text-primary uppercase"
                >
                    Visitor dashboard
                </p>
                <h1
                    class="mt-1 text-2xl font-semibold tracking-tight text-balance sm:text-[1.75rem]"
                >
                    Welcome back, {{ user.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Request a campus visit and show your QR pass at the gate.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    variant="outline"
                    size="lg"
                    class="bg-background shadow-sm"
                    as-child
                >
                    <Link :href="walkInCreate()">
                        <UserPlus class="size-4" />
                        Walk-in pass
                    </Link>
                </Button>
                <Button size="lg" class="shadow-sm" as-child>
                    <Link :href="create()">
                        <CalendarPlus class="size-4" />
                        Request a visit
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
            <StatCard
                label="Pending approval"
                :value="stats.pending"
                hint="Waiting for admin review"
                :icon="Clock3"
            />
            <StatCard
                label="Approved visits"
                :value="stats.approved"
                hint="QR passes ready to use"
                :icon="BadgeCheck"
            />
            <StatCard
                class="col-span-2 sm:col-span-1"
                label="Total requests"
                :value="stats.total"
                hint="All visits you have requested"
                :icon="CalendarCheck"
            />
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-5">
            <Card
                class="overflow-hidden rounded-2xl border-border/70 shadow-sm lg:col-span-3"
            >
                <CardHeader
                    class="flex flex-row items-start justify-between gap-3 pb-4"
                >
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold"
                        >
                            <span
                                class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Ticket class="size-4" />
                            </span>
                            Your next visit
                        </CardTitle>
                        <CardDescription class="mt-1.5">
                            Show this pass at the guard house on your visit
                            date.
                        </CardDescription>
                    </div>
                    <span
                        v-if="nextVisit"
                        class="shrink-0 rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary"
                    >
                        Pass ready
                    </span>
                </CardHeader>

                <CardContent class="pb-5">
                    <div
                        v-if="nextVisit"
                        class="grid gap-6 md:grid-cols-[1fr_auto] md:items-center"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <VisitStatusBadge
                                    :status="nextVisit.status"
                                    :label="nextVisit.status_label"
                                />
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDate(nextVisit.visit_date) }}
                                </span>
                            </div>

                            <h2
                                class="mt-2.5 text-xl font-semibold tracking-tight text-balance"
                            >
                                {{ nextVisit.purpose }}
                            </h2>

                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div class="flex items-start gap-2.5">
                                    <Building2
                                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                    />
                                    <div class="min-w-0">
                                        <dt
                                            class="text-[11px] tracking-wide text-muted-foreground uppercase"
                                        >
                                            Person or office
                                        </dt>
                                        <dd class="truncate font-medium">
                                            {{ nextVisit.host_name }}
                                        </dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <CalendarDays
                                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                    />
                                    <div>
                                        <dt
                                            class="text-[11px] tracking-wide text-muted-foreground uppercase"
                                        >
                                            Visit date
                                        </dt>
                                        <dd class="font-medium">
                                            {{
                                                formatDate(nextVisit.visit_date)
                                            }}
                                        </dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <Clock3
                                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                    />
                                    <div>
                                        <dt
                                            class="text-[11px] tracking-wide text-muted-foreground uppercase"
                                        >
                                            Expected out
                                        </dt>
                                        <dd class="font-medium">
                                            {{
                                                nextVisit.expected_time_out
                                                    ? formatTime(
                                                          nextVisit.expected_time_out,
                                                      )
                                                    : '—'
                                            }}
                                        </dd>
                                    </div>
                                </div>
                            </dl>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <Button class="shadow-sm" as-child>
                                    <Link :href="show(nextVisit.id)">
                                        View pass
                                        <ChevronRight class="size-4" />
                                    </Link>
                                </Button>
                                <Button variant="outline" as-child>
                                    <Link :href="index()">My visits</Link>
                                </Button>
                            </div>
                        </div>

                        <div
                            class="relative mx-auto w-full max-w-[15rem] overflow-hidden rounded-2xl bg-gradient-to-br from-[#123B2C] via-[#0B2A20] to-[#06120E] p-5 text-center text-white shadow-lg"
                        >
                            <div
                                class="pointer-events-none absolute -top-12 -right-10 size-36 rounded-full border border-white/10"
                            />
                            <div
                                class="pointer-events-none absolute -bottom-14 -left-10 size-40 rounded-full border border-white/10"
                            />

                            <p
                                class="relative text-[10px] font-semibold tracking-[0.22em] text-emerald-200/70 uppercase"
                            >
                                Scan at gate
                            </p>

                            <div
                                class="relative mx-auto mt-3 w-fit rounded-xl bg-white p-2.5 shadow-md"
                            >
                                <img
                                    v-if="nextVisit.pass_qr"
                                    :src="nextVisit.pass_qr"
                                    alt="QR pass for this visit"
                                    class="size-36"
                                />
                                <div
                                    v-else
                                    class="flex size-36 items-center justify-center"
                                >
                                    <QrCode
                                        class="size-10 text-muted-foreground"
                                    />
                                </div>
                            </div>

                            <p
                                class="relative mt-3 font-mono text-sm font-semibold tracking-[0.22em] text-white"
                            >
                                {{ nextVisit.pass_code }}
                            </p>
                            <p
                                class="relative mt-1 text-[11px] text-emerald-100/60"
                            >
                                Valid on
                                {{ formatDate(nextVisit.visit_date) }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-border bg-muted/20 px-6 py-10 text-center"
                    >
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/15"
                        >
                            <Ticket class="size-6" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold tracking-tight">
                            No upcoming approved visit
                        </h3>
                        <p
                            class="mx-auto mt-1 max-w-sm text-sm leading-relaxed text-muted-foreground"
                        >
                            Once an admin approves your request, your QR pass
                            will appear here with the visit details.
                        </p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            <Button class="shadow-sm" as-child>
                                <Link :href="create()">
                                    <CalendarPlus class="size-4" />
                                    Request a visit
                                </Link>
                            </Button>
                            <Button variant="outline" as-child>
                                <Link :href="index()">My visits</Link>
                            </Button>
                        </div>

                        <ol
                            class="mx-auto mt-7 grid max-w-md gap-2 text-left text-xs text-muted-foreground sm:grid-cols-3"
                        >
                            <li class="flex items-center gap-2">
                                <span
                                    class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-semibold text-primary"
                                >
                                    1
                                </span>
                                Request your visit
                            </li>
                            <li class="flex items-center gap-2">
                                <span
                                    class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-semibold text-primary"
                                >
                                    2
                                </span>
                                Admin approves
                            </li>
                            <li class="flex items-center gap-2">
                                <span
                                    class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-semibold text-primary"
                                >
                                    3
                                </span>
                                Show QR at the gate
                            </li>
                        </ol>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border-border/70 shadow-sm lg:col-span-2">
                <CardHeader
                    class="flex flex-row items-start justify-between gap-3 pb-3"
                >
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold"
                        >
                            <span
                                class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Clock3 class="size-4" />
                            </span>
                            Recent requests
                        </CardTitle>
                        <CardDescription class="mt-1.5">
                            Your latest visit requests and their status.
                        </CardDescription>
                    </div>
                    <Button variant="ghost" size="sm" class="-mt-1" as-child>
                        <Link :href="index()" class="gap-1">
                            View all
                            <ChevronRight class="size-3.5" />
                        </Link>
                    </Button>
                </CardHeader>

                <CardContent class="px-2 pb-2">
                    <div v-if="recentVisits.length > 0" class="divide-y">
                        <Link
                            v-for="visit in recentVisits"
                            :key="visit.id"
                            :href="show(visit.id)"
                            class="group flex items-center gap-3 rounded-lg px-3 py-3 transition-colors hover:bg-muted/50"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ visit.purpose }}
                                </p>
                                <p
                                    class="mt-0.5 truncate text-xs text-muted-foreground"
                                >
                                    {{ formatDate(visit.visit_date) }} ·
                                    {{ visit.host_name }}
                                </p>
                            </div>
                            <VisitStatusBadge
                                :status="visit.status"
                                :label="visit.status_label"
                            />
                            <ChevronRight
                                class="size-4 shrink-0 text-muted-foreground/40 transition-all group-hover:translate-x-0.5 group-hover:text-foreground"
                            />
                        </Link>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-center gap-2 rounded-xl px-4 py-10 text-center"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-full bg-muted text-muted-foreground"
                        >
                            <CalendarCheck class="size-5" />
                        </div>
                        <p class="text-sm font-medium">No requests yet</p>
                        <p class="max-w-[16rem] text-xs text-muted-foreground">
                            Start by requesting your first campus visit.
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            class="mt-2"
                            as-child
                        >
                            <Link :href="create()">Request a visit</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
