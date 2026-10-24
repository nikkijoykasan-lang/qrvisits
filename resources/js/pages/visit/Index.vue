<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarSearch, Plus } from '@lucide/vue';
import { reactive } from 'vue';
import {
    create,
    index,
    show,
} from '@/actions/App/Http/Controllers/VisitController';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import VisitStatusBadge from '@/components/VisitStatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatTime } from '@/lib/datetime';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Pagination, Visit } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                {
                    title: 'Visits',
                    href: index(),
                },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

const props = defineProps<{
    visits: Visit[];
    pagination: Pagination;
    filters: {
        status: string | null;
        date: string | null;
        search: string | null;
    };
    isVisitor: boolean;
}>();

const filter = reactive({
    status: props.filters.status ?? '',
    date: props.filters.date ?? '',
    search: props.filters.search ?? '',
});

const applyFilters = (): void => {
    router.get(
        index.url(),
        {
            status: filter.status || undefined,
            date: filter.date || undefined,
            search: filter.search || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = (): void => {
    filter.status = '';
    filter.date = '';
    filter.search = '';
    applyFilters();
};
</script>

<template>
    <Head title="Visits" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-xl font-semibold">
                    {{ isVisitor ? 'My visits' : 'Visit records' }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{
                        isVisitor
                            ? 'Every visit request you have submitted.'
                            : 'Search and review every campus visit.'
                    }}
                </p>
            </div>
            <Button v-if="isVisitor" as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Request a visit
                </Link>
            </Button>
        </div>

        <form
            class="grid gap-3 rounded-xl border p-4 sm:grid-cols-2 lg:grid-cols-4"
            @submit.prevent="applyFilters"
        >
            <Input
                v-model="filter.search"
                type="search"
                placeholder="Search visitor, host or pass code"
            />
            <Input v-model="filter.date" type="date" aria-label="Visit date" />
            <select
                v-model="filter.status"
                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                aria-label="Status"
            >
                <option value="">All statuses</option>
                <option value="pending">Pending approval</option>
                <option value="approved">Approved</option>
                <option value="inside">Inside campus</option>
                <option value="completed">Completed</option>
                <option value="denied">Denied</option>
            </select>
            <div class="flex gap-2">
                <Button type="submit" class="flex-1">Apply filters</Button>
                <Button type="button" variant="ghost" @click="clearFilters">
                    Clear
                </Button>
            </div>
        </form>

        <div v-if="visits.length > 0" class="overflow-x-auto rounded-xl border">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-muted/50 text-left text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 font-medium">Visitor</th>
                        <th class="px-4 py-3 font-medium">Purpose</th>
                        <th class="px-4 py-3 font-medium">Host</th>
                        <th class="px-4 py-3 font-medium">Visit date</th>
                        <th class="px-4 py-3 font-medium">Time in / out</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="visit in visits"
                        :key="visit.id"
                        class="border-t align-top"
                    >
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ visit.visitor_name }}</p>
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
                        <td class="max-w-[220px] px-4 py-3">
                            <p class="truncate" :title="visit.purpose">
                                {{ visit.purpose }}
                            </p>
                        </td>
                        <td class="max-w-[180px] px-4 py-3">
                            <p class="truncate" :title="visit.host_name">
                                {{ visit.host_name }}
                            </p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ formatDate(visit.visit_date) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ formatTime(visit.time_in) }} →
                            {{ formatTime(visit.time_out) }}
                        </td>
                        <td class="px-4 py-3">
                            <VisitStatusBadge
                                :status="visit.status"
                                :label="visit.status_label"
                            />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="show(visit.id)">View</Link>
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <EmptyState
            v-else
            title="No visits found"
            :description="
                isVisitor
                    ? 'Submit a visit request to see it here.'
                    : 'Try clearing the filters or wait for new requests.'
            "
            :icon="CalendarSearch"
        >
            <Button v-if="isVisitor" size="sm" as-child>
                <Link :href="create()">Request a visit</Link>
            </Button>
            <Button v-else size="sm" variant="outline" @click="clearFilters">
                Clear filters
            </Button>
        </EmptyState>

        <div
            v-if="pagination.last_page > 1"
            class="flex items-center justify-between gap-3 text-sm"
        >
            <p class="text-muted-foreground">
                Page {{ pagination.current_page }} of
                {{ pagination.last_page }}
            </p>
            <div class="flex gap-2">
                <Button
                    v-if="pagination.previous_page_url"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link :href="pagination.previous_page_url" preserve-scroll>
                        Previous
                    </Link>
                </Button>
                <Button v-else variant="outline" size="sm" disabled>
                    Previous
                </Button>
                <Button
                    v-if="pagination.next_page_url"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link :href="pagination.next_page_url" preserve-scroll>
                        Next
                    </Link>
                </Button>
                <Button v-else variant="outline" size="sm" disabled>
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
