<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Info } from '@lucide/vue';
import { index, store } from '@/actions/App/Http/Controllers/VisitController';
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
import { todayIso } from '@/lib/datetime';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Visits', href: index() },
                { title: 'Request a visit', href: '' },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    defaults: {
        visitor_contact: string;
        visit_date: string;
    };
}>();
</script>

<template>
    <Head title="Request a visit" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-xl font-semibold">Request a campus visit</h1>
            <p class="text-sm text-muted-foreground">
                Fill in the details below. An admin will review your request and
                issue your QR pass once approved.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Visit details</CardTitle>
                    <CardDescription>
                        Fields marked with * are required.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="store.form()"
                        class="flex flex-col gap-5"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="purpose">Purpose of visit *</Label>
                            <Input
                                id="purpose"
                                name="purpose"
                                required
                                maxlength="255"
                                placeholder="e.g. Parent meeting, document request"
                                v-focus
                            />
                            <InputError :message="errors.purpose" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="host_name">
                                Person or office to visit *
                            </Label>
                            <Input
                                id="host_name"
                                name="host_name"
                                required
                                maxlength="255"
                                placeholder="e.g. Registrar Office"
                            />
                            <InputError :message="errors.host_name" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="visit_date">Visit date *</Label>
                                <Input
                                    id="visit_date"
                                    name="visit_date"
                                    type="date"
                                    required
                                    :min="todayIso()"
                                    :default-value="defaults.visit_date"
                                />
                                <InputError :message="errors.visit_date" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="visitor_contact">
                                    Contact number *
                                </Label>
                                <Input
                                    id="visitor_contact"
                                    name="visitor_contact"
                                    type="tel"
                                    required
                                    :default-value="defaults.visitor_contact"
                                    placeholder="09XXXXXXXXX"
                                />
                                <InputError
                                    :message="errors.visitor_contact"
                                />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="id_document">Valid ID *</Label>
                            <Input
                                id="id_document"
                                name="id_document"
                                type="file"
                                required
                                accept="image/jpeg,image/png,application/pdf"
                                class="cursor-pointer file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium"
                            />
                            <p class="text-xs text-muted-foreground">
                                Upload a photo or scan of a school ID, driver's
                                license, passport, or other government ID. JPG,
                                PNG or PDF, up to 2 MB.
                            </p>
                            <InputError :message="errors.id_document" />
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button type="submit" :disabled="processing">
                                <Spinner v-if="processing" />
                                Submit request
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="index()">Cancel</Link>
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>

            <Card class="h-fit">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Info class="size-4" />
                        What happens next
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm text-muted-foreground">
                    <p>
                        1. An admin reviews your request. You will see the
                        status change on your dashboard.
                    </p>
                    <p>
                        2. Once approved, a QR pass appears on the visit page.
                        Show it at the gate on your visit date.
                    </p>
                    <p>
                        3. The pass only works on its visit date. If your plans
                        change, cancel a pending request and submit a new one.
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
