<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    create,
    index,
    store,
} from '@/actions/App/Http/Controllers/StaffAccountController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
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
                { title: 'Staff accounts', href: index() },
                { title: 'Create', href: create() },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    passwordRules: string;
    roles: { value: string; label: string }[];
}>();
</script>

<template>
    <Head title="Create staff account" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-xl font-semibold">Create a staff account</h1>
            <p class="text-sm text-muted-foreground">
                The new guard or admin can sign in immediately with this email
                and password.
            </p>
        </div>

        <Card class="max-w-xl">
            <CardHeader>
                <CardTitle>Account details</CardTitle>
                <CardDescription>
                    Share the password with them privately and ask them to
                    change it after signing in.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    class="flex flex-col gap-5"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Full name *</Label>
                        <Input id="name" name="name" required v-focus />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address *</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role *</Label>
                        <select
                            id="role"
                            name="role"
                            required
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option
                                v-for="option in roles"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="errors.role" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Temporary password *</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            :passwordrules="passwordRules"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirm password *
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            :passwordrules="passwordRules"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Create account
                        </Button>
                        <Button variant="ghost" as-child>
                            <Link :href="index()">Cancel</Link>
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
