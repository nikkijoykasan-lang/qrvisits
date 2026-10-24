<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { BadgeCheck, ClipboardCheck, Clock3 } from '@lucide/vue';
import {
    index as approvalsIndex,
    store as approve,
} from '@/actions/App/Http/Controllers/VisitApprovalController';
import { show } from '@/actions/App/Http/Controllers/VisitController';
import { store as deny } from '@/actions/App/Http/Controllers/VisitDenialController';
import EmptyState from '@/components/EmptyState.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatDateTime, formatTime } from '@/lib/datetime';
import type { BreadcrumbItem, Visit } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Approvals', href: approvalsIndex() },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    visits: Visit[];
    counts: {
        pending: number;
        walk_ins: number;
    };
}>();
</script>

<template>
    <Head title="Visit approvals" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-xl font-semibold">Visit approvals</h1>
            <p class="text-sm text-muted-foreground">
                {{ counts.pending }} pending
                <template v-if="counts.walk_ins > 0">
                    · {{ counts.walk_ins }} walk-in
                    {{ counts.walk_ins === 1 ? 'visitor' : 'visitors' }}
                    waiting at the gate
                </template>
            </p>
        </div>

        <div v-if="visits.length > 0" class="grid gap-4 xl:grid-cols-2">
            <Card v-for="visit in visits" :key="visit.id">
                <CardHeader
                    class="flex flex-row items-start justify-between gap-3"
                >
                    <div class="min-w-0">
                        <CardTitle class="flex flex-wrap items-center gap-2">
                            {{ visit.visitor_name }}
                            <span
                                v-if="visit.is_walk_in"
                                class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-amber-800 uppercase dark:bg-amber-950 dark:text-amber-300"
                            >
                                Walk-in
                            </span>
                        </CardTitle>
                        <p class="truncate text-sm text-muted-foreground">
                            {{ visit.purpose }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <a
                            v-if="visit.id_document_url"
                            :href="visit.id_document_url"
                            target="_blank"
                            rel="noopener"
                            class="text-xs font-medium underline underline-offset-4"
                        >
                            View ID
                        </a>
                        <Link
                            :href="show(visit.id)"
                            class="text-xs font-medium underline underline-offset-4"
                        >
                            Details
                        </Link>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase">
                                Host
                            </dt>
                            <dd class="truncate">{{ visit.host_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase">
                                Visit date
                            </dt>
                            <dd>
                                {{ formatDate(visit.visit_date) }}
                                <span v-if="visit.expected_arrival">
                                    · {{ formatTime(visit.expected_arrival) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase">
                                Contact
                            </dt>
                            <dd>{{ visit.visitor_contact }}</dd>
                        </div>
                        <div>
                            <dt
                                class="flex items-center gap-1 text-xs text-muted-foreground uppercase"
                            >
                                <Clock3 class="size-3" />
                                Requested
                            </dt>
                            <dd>{{ formatDateTime(visit.created_at) }}</dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap gap-2">
                        <Form
                            v-bind="approve.form(visit.id)"
                            :options="{ preserveScroll: true }"
                            v-slot="{ processing }"
                        >
                            <Button type="submit" :disabled="processing">
                                <BadgeCheck class="size-4" />
                                Approve
                            </Button>
                        </Form>

                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="destructive">Deny</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="deny.form(visit.id)"
                                    :options="{ preserveScroll: true }"
                                    reset-on-success
                                    class="space-y-4"
                                    v-slot="{ errors, processing }"
                                >
                                    <DialogHeader>
                                        <DialogTitle>
                                            Deny this visit request?
                                        </DialogTitle>
                                        <DialogDescription>
                                            The visitor will see this reason on
                                            their visit page.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="grid gap-2">
                                        <Label for="reason">Reason</Label>
                                        <textarea
                                            id="reason"
                                            name="reason"
                                            required
                                            rows="3"
                                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder="e.g. Incomplete details, schedule conflict"
                                        />
                                        <InputError :message="errors.reason" />
                                    </div>
                                    <DialogFooter class="gap-2">
                                        <DialogClose as-child>
                                            <Button
                                                variant="secondary"
                                                type="button"
                                            >
                                                Keep pending
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                        >
                                            Deny request
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </div>

        <EmptyState
            v-else
            title="No requests waiting"
            description="New visit requests and walk-ins will appear here for approval."
            :icon="ClipboardCheck"
        />
    </div>
</template>
