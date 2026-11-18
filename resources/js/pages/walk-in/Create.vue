<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { BadgeCheck, IdCard, UserPlus } from '@lucide/vue';
import { index as visitsIndex } from '@/actions/App/Http/Controllers/VisitController';
import { create, store } from '@/actions/App/Http/Controllers/WalkInController';
import InputError from '@/components/InputError.vue';
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
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Walk-in', href: create() },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    visitor: {
        name: string;
        email: string;
        contact: string;
    };
    defaults: {
        expected_time_out: string;
    };
}>();
</script>

<template>
    <Head title="Walk-in registration" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <div>
            <p
                class="text-[11px] font-semibold tracking-[0.18em] text-primary uppercase"
            >
                At the gate
            </p>
            <h1
                class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight"
            >
                <UserPlus class="size-5 text-primary" />
                Register as a walk-in
            </h1>
            <p class="mt-1 max-w-2xl text-sm text-muted-foreground">
                Use this when you arrive without a pre-registered visit. Your QR
                pass is issued immediately, so the guard can scan you in at the
                gate.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <Card class="rounded-2xl border-border/70 shadow-sm">
                <CardHeader>
                    <CardTitle class="text-base">Walk-in details</CardTitle>
                    <CardDescription>
                        Tell the guard who you are visiting. Fields marked * are
                        required.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="store.form()"
                        class="flex flex-col gap-5"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="visitor_contact"
                                >Contact number *</Label
                            >
                            <Input
                                id="visitor_contact"
                                name="visitor_contact"
                                type="tel"
                                required
                                :default-value="visitor.contact"
                                placeholder="09XXXXXXXXX"
                            />
                            <InputError :message="errors.visitor_contact" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="purpose">Purpose of visit *</Label>
                            <Input
                                id="purpose"
                                name="purpose"
                                required
                                maxlength="255"
                                placeholder="e.g. Delivery, parent meeting"
                                v-focus
                            />
                            <InputError :message="errors.purpose" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="host_name">
                                    Person or office to visit *
                                </Label>
                                <Input
                                    id="host_name"
                                    name="host_name"
                                    required
                                    maxlength="255"
                                    placeholder="e.g. Supply Office"
                                />
                                <InputError :message="errors.host_name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="expected_time_out">
                                    Expected time-out *
                                </Label>
                                <Input
                                    id="expected_time_out"
                                    name="expected_time_out"
                                    type="time"
                                    required
                                    :default-value="defaults.expected_time_out"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Guards and admins are alerted if you are
                                    still inside after this time.
                                </p>
                                <InputError
                                    :message="errors.expected_time_out"
                                />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                type="submit"
                                class="shadow-sm"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />
                                Get walk-in pass
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="visitsIndex()">Cancel</Link>
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>

            <div class="space-y-4">
                <Card class="rounded-2xl border-border/70 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <IdCard class="size-4" />
                            Registering as
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm">
                        <div class="min-w-0">
                            <p
                                class="text-[11px] text-muted-foreground uppercase"
                            >
                                Name
                            </p>
                            <p class="truncate font-medium">
                                {{ visitor.name }}
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-[11px] text-muted-foreground uppercase"
                            >
                                Email
                            </p>
                            <p class="truncate">{{ visitor.email }}</p>
                        </div>
                    </CardContent>
                </Card>

                <div
                    class="rounded-2xl border border-primary/15 bg-primary/5 p-4 text-sm"
                >
                    <p class="flex items-center gap-2 font-medium text-primary">
                        <BadgeCheck class="size-4" />
                        Pass issued instantly
                    </p>
                    <p
                        class="mt-1.5 text-xs leading-relaxed text-muted-foreground"
                    >
                        Your QR pass is valid for today only. Show it to the
                        guard at the gate to record your time-in and time-out.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
