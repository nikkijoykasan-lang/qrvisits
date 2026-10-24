<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeCheck,
    Clock3,
    FileText,
    MapPin,
    Printer,
    QrCode,
    ShieldCheck,
    User,
} from '@lucide/vue';
import { index as gateIndex } from '@/actions/App/Http/Controllers/GateController';
import { store as approve } from '@/actions/App/Http/Controllers/VisitApprovalController';
import { index, destroy } from '@/actions/App/Http/Controllers/VisitController';
import { store as deny } from '@/actions/App/Http/Controllers/VisitDenialController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import VisitStatusBadge from '@/components/VisitStatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatDateTime, formatTime } from '@/lib/datetime';
import type { BreadcrumbItem, Visit } from '@/types';

defineProps<{
    visit: Visit;
    isVisitor: boolean;
    canApprove: boolean;
}>();

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Visits', href: index() },
                { title: 'Visit details', href: '' },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

const printPass = (): void => {
    window.print();
};
</script>

<template>
    <Head :title="`Visit - ${visit.visitor_name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-wrap items-start justify-between gap-3 print:hidden"
        >
            <div class="space-y-2">
                <Button variant="ghost" size="sm" as-child class="-ml-2">
                    <Link :href="index()">
                        <ArrowLeft class="size-4" />
                        Back to visits
                    </Link>
                </Button>
                <h1
                    class="flex flex-wrap items-center gap-2 text-xl font-semibold"
                >
                    {{ visit.visitor_name }}
                    <VisitStatusBadge
                        :status="visit.status"
                        :label="visit.status_label"
                    />
                    <span
                        v-if="visit.is_walk_in"
                        class="rounded bg-muted px-2 py-0.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Walk-in
                    </span>
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ visit.purpose }} · {{ formatDate(visit.visit_date) }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <template v-if="canApprove && visit.status === 'pending'">
                    <Form
                        v-bind="approve.form(visit.id)"
                        :options="{ preserveScroll: true }"
                        v-slot="{ processing }"
                    >
                        <Button type="submit" :disabled="processing">
                            <BadgeCheck class="size-4" />
                            Approve and issue pass
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
                </template>

                <Dialog v-if="isVisitor && visit.status === 'pending'">
                    <DialogTrigger as-child>
                        <Button variant="outline">Cancel request</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <Form
                            v-bind="destroy.form(visit.id)"
                            :options="{ preserveScroll: true }"
                            class="space-y-4"
                            v-slot="{ processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Cancel this request?</DialogTitle>
                                <DialogDescription>
                                    The request will be removed. You can submit
                                    a new one at any time.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button variant="secondary" type="button">
                                        Keep it
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    :disabled="processing"
                                >
                                    Cancel request
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>

                <Button v-if="!isVisitor" variant="outline" as-child>
                    <Link :href="gateIndex()">Open gate scan</Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <Card v-if="visit.pass_code" class="overflow-hidden">
                    <CardHeader
                        class="flex flex-row items-start justify-between"
                    >
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <QrCode class="size-4" />
                                Visitor pass
                            </CardTitle>
                            <CardDescription>
                                Valid only on
                                {{ formatDate(visit.visit_date) }}. Scan at the
                                gate.
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            class="print:hidden"
                            @click="printPass"
                        >
                            <Printer class="size-4" />
                            Print
                        </Button>
                    </CardHeader>
                    <CardContent
                        class="flex flex-col items-center gap-4 sm:flex-row sm:items-start"
                    >
                        <div class="rounded-xl border bg-white p-3 shadow-sm">
                            <img
                                v-if="visit.pass_qr"
                                :src="visit.pass_qr"
                                :alt="`QR pass for ${visit.visitor_name}`"
                                class="size-44"
                            />
                        </div>
                        <dl class="grid flex-1 gap-3 text-sm">
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Visitor
                                </dt>
                                <dd class="font-medium">
                                    {{ visit.visitor_name }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Pass code
                                </dt>
                                <dd
                                    class="font-mono text-lg font-semibold tracking-widest"
                                >
                                    {{ visit.pass_code }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Date
                                </dt>
                                <dd>{{ formatDate(visit.visit_date) }}</dd>
                            </div>
                            <div v-if="visit.expected_arrival">
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Expected arrival
                                </dt>
                                <dd>
                                    {{ formatTime(visit.expected_arrival) }}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card v-else-if="visit.status === 'pending'">
                    <CardHeader>
                        <CardTitle>Pass not issued yet</CardTitle>
                        <CardDescription>
                            The QR pass appears here as soon as an admin
                            approves this request.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card v-else>
                    <CardHeader>
                        <CardTitle>No pass issued</CardTitle>
                        <CardDescription>
                            {{
                                visit.denial_reason
                                    ? `Denied: ${visit.denial_reason}`
                                    : 'This visit has no active pass.'
                            }}
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Visit details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div class="flex gap-3">
                                <User
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                />
                                <div class="min-w-0">
                                    <dt
                                        class="text-xs text-muted-foreground uppercase"
                                    >
                                        Visitor
                                    </dt>
                                    <dd class="truncate">
                                        {{ visit.visitor_name }}
                                    </dd>
                                    <dd
                                        class="truncate text-sm text-muted-foreground"
                                    >
                                        {{ visit.visitor_contact }}
                                        <template v-if="visit.visitor_email">
                                            · {{ visit.visitor_email }}
                                        </template>
                                    </dd>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <MapPin
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                />
                                <div class="min-w-0">
                                    <dt
                                        class="text-xs text-muted-foreground uppercase"
                                    >
                                        Person or office
                                    </dt>
                                    <dd class="truncate">
                                        {{ visit.host_name }}
                                    </dd>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <Clock3
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                />
                                <div>
                                    <dt
                                        class="text-xs text-muted-foreground uppercase"
                                    >
                                        Visit date
                                    </dt>
                                    <dd>{{ formatDate(visit.visit_date) }}</dd>
                                    <dd
                                        v-if="visit.expected_arrival"
                                        class="text-sm text-muted-foreground"
                                    >
                                        Expected
                                        {{ formatTime(visit.expected_arrival) }}
                                    </dd>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <ShieldCheck
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                />
                                <div>
                                    <dt
                                        class="text-xs text-muted-foreground uppercase"
                                    >
                                        Purpose
                                    </dt>
                                    <dd>{{ visit.purpose }}</dd>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <FileText
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                />
                                <div class="min-w-0">
                                    <dt
                                        class="text-xs text-muted-foreground uppercase"
                                    >
                                        Valid ID
                                    </dt>
                                    <dd v-if="visit.id_document_url">
                                        <a
                                            :href="visit.id_document_url"
                                            target="_blank"
                                            rel="noopener"
                                            class="font-medium underline underline-offset-4"
                                        >
                                            View uploaded ID
                                        </a>
                                        <span
                                            v-if="visit.id_document_name"
                                            class="block truncate text-xs text-muted-foreground"
                                        >
                                            {{ visit.id_document_name }}
                                        </span>
                                    </dd>
                                    <dd v-else class="text-muted-foreground">
                                        No ID uploaded
                                    </dd>
                                </div>
                            </div>
                        </dl>

                        <div
                            v-if="visit.denial_reason"
                            class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300"
                        >
                            Denied: {{ visit.denial_reason }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="h-fit">
                <CardHeader>
                    <CardTitle>Timeline</CardTitle>
                    <CardDescription>
                        Who approved and scanned this visit.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4 text-sm">
                    <div>
                        <p class="text-xs text-muted-foreground uppercase">
                            Requested
                        </p>
                        <p>{{ formatDateTime(visit.created_at) }}</p>
                    </div>
                    <div v-if="visit.approved_at">
                        <p class="text-xs text-muted-foreground uppercase">
                            Reviewed
                        </p>
                        <p>{{ formatDateTime(visit.approved_at) }}</p>
                        <p
                            v-if="visit.approved_by_name"
                            class="text-muted-foreground"
                        >
                            by {{ visit.approved_by_name }}
                        </p>
                    </div>
                    <div v-if="visit.time_in">
                        <p class="text-xs text-muted-foreground uppercase">
                            Checked in
                        </p>
                        <p>{{ formatDateTime(visit.time_in) }}</p>
                        <p
                            v-if="visit.checked_in_by_name"
                            class="text-muted-foreground"
                        >
                            by {{ visit.checked_in_by_name }}
                        </p>
                    </div>
                    <div v-if="visit.time_out">
                        <p class="text-xs text-muted-foreground uppercase">
                            Checked out
                        </p>
                        <p>{{ formatDateTime(visit.time_out) }}</p>
                        <p
                            v-if="visit.checked_out_by_name"
                            class="text-muted-foreground"
                        >
                            by {{ visit.checked_out_by_name }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
