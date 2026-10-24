<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { BarChart3, Download, FileSpreadsheet } from '@lucide/vue';
import { computed, reactive } from 'vue';
import { index } from '@/actions/App/Http/Controllers/ReportController';
import { show } from '@/actions/App/Http/Controllers/VisitController';
import VisitExportController from '@/actions/App/Http/Controllers/VisitExportController';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import VisitStatusBadge from '@/components/VisitStatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatTime } from '@/lib/datetime';
import type { BreadcrumbItem, Visit } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Reports', href: index() },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

const props = defineProps<{
    visits: Visit[];
    filters: {
        from: string;
        to: string;
    };
    summary: {
        total: number;
        completed: number;
        inside: number;
        pending: number;
        denied: number;
        walk_ins: number;
        average_minutes: number | null;
    };
}>();

const filter = reactive({
    from: props.filters.from,
    to: props.filters.to,
});

const exportUrl = computed(() =>
    VisitExportController.url({
        query: { from: filter.from, to: filter.to },
    }),
);

const applyFilter = (): void => {
    router.get(
        index.url(),
        { from: filter.from, to: filter.to },
        { preserveState: true, replace: true },
    );
};

const averageLabel = computed(() => {
    if (props.summary.average_minutes === null) {
        return '—';
    }

    const minutes = props.summary.average_minutes;
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    return hours > 0 ? `${hours}h ${rest}m` : `${rest}m`;
});
</script>

<template>
    <Head title="Reports" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <BarChart3 class="size-5" />
                    Visit reports
                </h1>
                <p class="text-sm text-muted-foreground">
                    Daily totals for a date range. Export the same data as a
                    spreadsheet file.
                </p>
            </div>
            <Button as-child>
                <a :href="exportUrl">
                    <Download class="size-4" />
                    Export CSV
                </a>
            </Button>
        </div>

        <form
            class="flex flex-wrap items-end gap-3 rounded-xl border p-4"
            @submit.prevent="applyFilter"
        >
            <div class="grid gap-2">
                <Label for="from">From</Label>
                <Input id="from" v-model="filter.from" type="date" />
            </div>
            <div class="grid gap-2">
                <Label for="to">To</Label>
                <Input id="to" v-model="filter.to" type="date" />
            </div>
            <Button type="submit">Apply</Button>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Total visits" :value="summary.total" />
            <StatCard label="Completed" :value="summary.completed" />
            <StatCard label="Still inside" :value="summary.inside" />
            <StatCard label="Pending approval" :value="summary.pending" />
            <StatCard label="Denied" :value="summary.denied" />
            <StatCard label="Walk-ins" :value="summary.walk_ins" />
            <StatCard
                label="Average stay"
                :value="averageLabel"
                hint="For completed visits"
            />
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Visits in this range</CardTitle>
                <CardDescription>
                    {{ formatDate(filters.from) }} to
                    {{ formatDate(filters.to) }} · showing up to 500 records.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="visits.length > 0"
                    class="overflow-x-auto rounded-lg border"
                >
                    <table class="w-full min-w-[800px] text-sm">
                        <thead class="bg-muted/50 text-left text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Visitor</th>
                                <th class="px-4 py-3 font-medium">Purpose</th>
                                <th class="px-4 py-3 font-medium">Host</th>
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 font-medium">In</th>
                                <th class="px-4 py-3 font-medium">Out</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="visit in visits"
                                :key="visit.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-medium">
                                        {{ visit.visitor_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ visit.visitor_contact }}
                                        <span
                                            v-if="visit.is_walk_in"
                                            class="ml-1 rounded bg-muted px-1.5 py-0.5 text-[10px] uppercase"
                                        >
                                            Walk-in
                                        </span>
                                    </p>
                                </td>
                                <td class="max-w-[200px] px-4 py-3">
                                    <p class="truncate" :title="visit.purpose">
                                        {{ visit.purpose }}
                                    </p>
                                </td>
                                <td class="max-w-[160px] px-4 py-3">
                                    <p
                                        class="truncate"
                                        :title="visit.host_name"
                                    >
                                        {{ visit.host_name }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ formatDate(visit.visit_date) }}
                                </td>
                                <td class="px-4 py-3 tabular-nums">
                                    {{ formatTime(visit.time_in) }}
                                </td>
                                <td class="px-4 py-3 tabular-nums">
                                    {{ formatTime(visit.time_out) }}
                                </td>
                                <td class="px-4 py-3">
                                    <VisitStatusBadge
                                        :status="visit.status"
                                        :label="visit.status_label"
                                    />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="show(visit.id)">View</Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <EmptyState
                    v-else
                    title="No visits in this range"
                    description="Choose a different date range to see records."
                    :icon="FileSpreadsheet"
                />
            </CardContent>
        </Card>
    </div>
</template>
