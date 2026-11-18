<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { RefreshCw, ScanLine, Users } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { index as gateIndex } from '@/actions/App/Http/Controllers/GateController';
import { show } from '@/actions/App/Http/Controllers/VisitController';
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
import type { BreadcrumbItem, Visit } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Live board', href: '' },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    visits: Visit[];
    expected: Visit[];
    stats: {
        inside: number;
        expected_today: number;
        completed_today: number;
    };
}>();

const refreshing = ref(false);
const lastUpdated = ref(new Date());
let timer: ReturnType<typeof setInterval> | null = null;

const updatedLabel = computed(() =>
    new Intl.DateTimeFormat(undefined, {
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
    }).format(lastUpdated.value),
);

const refresh = (): void => {
    refreshing.value = true;

    router.reload({
        only: ['visits', 'expected', 'stats'],
        onFinish: () => {
            refreshing.value = false;
            lastUpdated.value = new Date();
        },
    });
};

onMounted(() => {
    timer = setInterval(refresh, 15000);
});

onBeforeUnmount(() => {
    if (timer !== null) {
        clearInterval(timer);
    }
});
</script>

<template>
    <Head title="Live board" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <Users class="size-5" />
                    Live board
                </h1>
                <p class="text-sm text-muted-foreground">
                    Everyone currently inside the campus. Refreshes every 15
                    seconds.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground tabular-nums">
                    Updated {{ updatedLabel }}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="refreshing"
                    @click="refresh"
                >
                    <RefreshCw
                        class="size-4"
                        :class="{ 'animate-spin': refreshing }"
                    />
                    Refresh
                </Button>
                <Button size="sm" as-child>
                    <Link :href="gateIndex()">
                        <ScanLine class="size-4" />
                        Scan
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Inside now" :value="stats.inside" />
            <StatCard
                label="Still expected today"
                :value="stats.expected_today"
            />
            <StatCard
                label="Checked out today"
                :value="stats.completed_today"
            />
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Inside right now</CardTitle>
                <CardDescription>
                    Sorted by the time they entered.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="visits.length > 0"
                    class="overflow-x-auto rounded-lg border"
                >
                    <table class="w-full min-w-[640px] text-sm">
                        <thead class="bg-muted/50 text-left text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Visitor</th>
                                <th class="px-4 py-3 font-medium">Purpose</th>
                                <th class="px-4 py-3 font-medium">Host</th>
                                <th class="px-4 py-3 font-medium">In at</th>
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
                                    </p>
                                </td>
                                <td class="max-w-[220px] px-4 py-3">
                                    <p class="truncate" :title="visit.purpose">
                                        {{ visit.purpose }}
                                    </p>
                                </td>
                                <td class="max-w-[180px] px-4 py-3">
                                    <p
                                        class="truncate"
                                        :title="visit.host_name"
                                    >
                                        {{ visit.host_name }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 tabular-nums">
                                    {{ formatTime(visit.time_in) }}
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
                    title="Nobody is inside"
                    description="When a pass is scanned in, the visitor appears here."
                    :icon="Users"
                />
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Approved for today, not yet scanned in</CardTitle>
                <CardDescription>
                    Visitors who still need to arrive.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="visit in expected"
                    :key="visit.id"
                    class="flex flex-wrap items-center justify-between gap-2 rounded-lg border p-3"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">
                            {{ visit.visitor_name }}
                            <span
                                v-if="visit.is_walk_in"
                                class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-amber-800 uppercase dark:bg-amber-950 dark:text-amber-300"
                            >
                                Walk-in
                            </span>
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ visit.purpose }} · {{ visit.host_name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs text-muted-foreground tabular-nums"
                        >
                            <template v-if="visit.expected_arrival">
                                Expected
                                {{ formatTime(visit.expected_arrival) }}
                            </template>
                            <template v-else>Not yet arrived</template>
                        </span>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="show(visit.id)">View pass</Link>
                        </Button>
                    </div>
                </div>
                <p
                    v-if="expected.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No approved visitors are still expected today.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
