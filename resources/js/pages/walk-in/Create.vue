<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { UserPlus } from '@lucide/vue';
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
    defaults: {
        expected_arrival: string;
        expected_time_out: string;
    };
}>();
</script>

<template>
    <Head title="Register walk-in" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <UserPlus class="size-5" />
                Register a walk-in visitor
            </h1>
            <p class="text-sm text-muted-foreground">
                For visitors who arrive without pre-registering. The admin must
                approve the visit before the visitor can enter.
            </p>
        </div>

        <Card class="max-w-2xl">
            <CardHeader>
                <CardTitle>Visitor details</CardTitle>
                <CardDescription>
                    Ask for the visitor's name and contact number.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    class="flex flex-col gap-5"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="visitor_name">Visitor name *</Label>
                            <Input
                                id="visitor_name"
                                name="visitor_name"
                                required
                                maxlength="255"
                                v-focus
                            />
                            <InputError :message="errors.visitor_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="visitor_contact"
                                >Contact number *</Label
                            >
                            <Input
                                id="visitor_contact"
                                name="visitor_contact"
                                type="tel"
                                required
                                placeholder="09XXXXXXXXX"
                            />
                            <InputError :message="errors.visitor_contact" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="purpose">Purpose of visit *</Label>
                        <Input
                            id="purpose"
                            name="purpose"
                            required
                            maxlength="255"
                            placeholder="e.g. Delivery, parent meeting"
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
                            <Label for="expected_arrival"> Time arrived </Label>
                            <Input
                                id="expected_arrival"
                                name="expected_arrival"
                                type="time"
                                :default-value="defaults.expected_arrival"
                            />
                            <InputError :message="errors.expected_arrival" />
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
                                Guards and admins are alerted if the visitor is
                                still inside after this time.
                            </p>
                            <InputError :message="errors.expected_time_out" />
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Register walk-in
                        </Button>
                        <Button variant="ghost" as-child>
                            <Link :href="visitsIndex()">Cancel</Link>
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
