<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Plus, UserCog, Users } from '@lucide/vue';
import {
    create,
    destroy,
    index,
} from '@/actions/App/Http/Controllers/StaffAccountController';
import EmptyState from '@/components/EmptyState.vue';
import { Badge } from '@/components/ui/badge';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/datetime';
import type { BreadcrumbItem, StaffAccount } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Staff accounts', href: index() },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    staff: StaffAccount[];
}>();

const currentUserId = usePage().props.auth.user.id;
</script>

<template>
    <Head title="Staff accounts" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <UserCog class="size-5" />
                    Staff accounts
                </h1>
                <p class="text-sm text-muted-foreground">
                    Guards scan passes; admins approve visits, manage records
                    and staff.
                </p>
            </div>
            <Button as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Create staff account
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Guards and admins</CardTitle>
                <CardDescription>
                    {{ staff.length }} account{{
                        staff.length === 1 ? '' : 's'
                    }}. Visitors create their own accounts.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="staff.length > 0"
                    class="overflow-x-auto rounded-lg border"
                >
                    <table class="w-full min-w-[560px] text-sm">
                        <thead class="bg-muted/50 text-left text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="px-4 py-3 font-medium">Added</th>
                                <th class="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="member in staff"
                                :key="member.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ member.name }}
                                    <span
                                        v-if="member.id === currentUserId"
                                        class="ml-1 text-xs text-muted-foreground"
                                    >
                                        (you)
                                    </span>
                                </td>
                                <td class="max-w-[240px] truncate px-4 py-3">
                                    {{ member.email }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge variant="outline">
                                        {{ member.role_label }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{
                                        formatDate(
                                            member.created_at.slice(0, 10),
                                        )
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Dialog v-if="member.id !== currentUserId">
                                        <DialogTrigger as-child>
                                            <Button variant="ghost" size="sm">
                                                Remove
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <Form
                                                v-bind="destroy.form(member.id)"
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                class="space-y-4"
                                                v-slot="{ processing }"
                                            >
                                                <DialogHeader>
                                                    <DialogTitle>
                                                        Remove
                                                        {{ member.name }}?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        They will no longer be
                                                        able to sign in. Visit
                                                        records stay in the
                                                        system.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <DialogFooter class="gap-2">
                                                    <DialogClose as-child>
                                                        <Button
                                                            variant="secondary"
                                                            type="button"
                                                        >
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                        :disabled="processing"
                                                    >
                                                        Remove account
                                                    </Button>
                                                </DialogFooter>
                                            </Form>
                                        </DialogContent>
                                    </Dialog>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <EmptyState
                    v-else
                    title="No staff accounts yet"
                    description="Create a guard account so the gate can start scanning."
                    :icon="Users"
                />
            </CardContent>
        </Card>
    </div>
</template>
