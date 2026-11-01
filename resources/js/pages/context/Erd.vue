<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, Database, Printer, Table2 } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import ContextErdController from '@/actions/App/Http/Controllers/ContextErdController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                {
                    title: 'Database ERD',
                    href: ContextErdController(),
                },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

const MERMAID_CDN =
    'https://cdn.jsdelivr.net/npm/mermaid@11.4.1/dist/mermaid.min.js';

type MermaidApi = {
    initialize: (config: Record<string, unknown>) => void;
    render: (
        id: string,
        text: string,
    ) => Promise<{ svg: string; bindFunctions?: (element: Element) => void }>;
};

const diagramSvg = ref('');
const diagramLoading = ref(true);
const diagramError = ref(false);

const erdDefinition = `erDiagram
    USERS {
        uuid id PK
        string name
        string email UK
        string role "visitor | guard | admin"
        string contact_number "nullable"
        timestamp email_verified_at "nullable"
        string password "hashed"
        text two_factor_secret "nullable"
        text two_factor_recovery_codes "nullable"
        timestamp two_factor_confirmed_at "nullable"
        string remember_token "nullable"
        timestamp created_at
        timestamp updated_at
    }

    VISITS {
        uuid id PK
        uuid user_id FK "nullable - null for walk-ins"
        string visitor_name
        string visitor_contact
        string purpose
        string host_name
        date visit_date
        time expected_arrival "nullable, legacy"
        string status "pending | approved | denied | inside | completed"
        string pass_code UK "nullable until approved"
        text denial_reason "nullable"
        boolean is_walk_in "default false"
        string id_document_path "nullable - private file path"
        string id_document_name "nullable - original file name"
        timestamp approved_at "nullable"
        uuid approved_by FK "nullable"
        timestamp time_in "nullable"
        timestamp time_out "nullable"
        uuid checked_in_by FK "nullable"
        uuid checked_out_by FK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ VISITS : "submits"
    USERS |o--o{ VISITS : "approves"
    USERS |o--o{ VISITS : "checks in"
    USERS |o--o{ VISITS : "checks out"
`;

function mermaidApi(): MermaidApi | null {
    return (window as Window & { mermaid?: MermaidApi }).mermaid ?? null;
}

function waitForMermaid(): Promise<MermaidApi> {
    const existing = document.querySelector<HTMLScriptElement>(
        'script[data-qrvisits-mermaid]',
    );

    if (existing) {
        return new Promise((resolve, reject) => {
            existing.addEventListener('load', () => {
                const api = mermaidApi();

                if (api) {
                    resolve(api);
                } else {
                    reject(new Error('Mermaid did not initialise.'));
                }
            });
            existing.addEventListener('error', () =>
                reject(new Error('Mermaid failed to load.')),
            );
        });
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = MERMAID_CDN;
        script.async = true;
        script.dataset.qrvisitsMermaid = 'true';
        script.onload = () => {
            const api = mermaidApi();

            if (api) {
                resolve(api);
            } else {
                reject(new Error('Mermaid did not initialise.'));
            }
        };
        script.onerror = () => reject(new Error('Mermaid failed to load.'));
        document.head.appendChild(script);
    });
}

onMounted(async () => {
    try {
        const mermaid = await waitForMermaid();

        mermaid.initialize({
            startOnLoad: false,
            theme: 'neutral',
            er: { useMaxWidth: true },
            securityLevel: 'strict',
        });

        const { svg } = await mermaid.render('qrvisits-erd', erdDefinition);
        diagramSvg.value = svg;
    } catch {
        diagramError.value = true;
    } finally {
        diagramLoading.value = false;
    }
});

type Column = {
    name: string;
    type: string;
    key?: 'PK' | 'FK' | 'UK';
    nullable: boolean;
    defaultValue?: string;
    notes: string;
};

const usersColumns: Column[] = [
    {
        name: 'id',
        type: 'uuid',
        key: 'PK',
        nullable: false,
        notes: 'Primary identifier.',
    },
    {
        name: 'name',
        type: 'string',
        nullable: false,
        notes: 'Full name; used on visit records.',
    },
    {
        name: 'email',
        type: 'string',
        key: 'UK',
        nullable: false,
        notes: 'Login email; unique.',
    },
    {
        name: 'role',
        type: 'string',
        nullable: false,
        defaultValue: 'visitor',
        notes: 'visitor, guard or admin.',
    },
    {
        name: 'contact_number',
        type: 'string(30)',
        nullable: true,
        notes: 'Used to prefill visit requests.',
    },
    {
        name: 'email_verified_at',
        type: 'timestamp',
        nullable: true,
        notes: 'Email verification time.',
    },
    {
        name: 'password',
        type: 'string',
        nullable: false,
        notes: 'Hashed password.',
    },
    {
        name: 'two_factor_secret',
        type: 'text',
        nullable: true,
        notes: 'Two-factor secret (optional).',
    },
    {
        name: 'two_factor_recovery_codes',
        type: 'text',
        nullable: true,
        notes: 'Two-factor recovery codes.',
    },
    {
        name: 'two_factor_confirmed_at',
        type: 'timestamp',
        nullable: true,
        notes: 'Two-factor confirmation time.',
    },
    {
        name: 'remember_token',
        type: 'string',
        nullable: true,
        notes: 'Remember-me token.',
    },
    {
        name: 'created_at',
        type: 'timestamp',
        nullable: false,
        notes: 'Created time.',
    },
    {
        name: 'updated_at',
        type: 'timestamp',
        nullable: false,
        notes: 'Last updated time.',
    },
];

const visitColumns: Column[] = [
    {
        name: 'id',
        type: 'uuid',
        key: 'PK',
        nullable: false,
        notes: 'Visit identifier.',
    },
    {
        name: 'user_id',
        type: 'uuid',
        key: 'FK',
        nullable: true,
        notes: 'Visitor account. Null for walk-ins; set null if the account is deleted.',
    },
    {
        name: 'visitor_name',
        type: 'string',
        nullable: false,
        notes: 'Name shown to the guard and admin.',
    },
    {
        name: 'visitor_contact',
        type: 'string(30)',
        nullable: false,
        notes: 'Contact number given for this visit.',
    },
    {
        name: 'purpose',
        type: 'string',
        nullable: false,
        notes: 'Reason for visiting.',
    },
    {
        name: 'host_name',
        type: 'string',
        nullable: false,
        notes: 'Person or office being visited.',
    },
    {
        name: 'visit_date',
        type: 'date',
        nullable: false,
        notes: 'Day the pass is valid.',
    },
    {
        name: 'expected_arrival',
        type: 'time',
        nullable: true,
        notes: 'Legacy field; not collected on new requests.',
    },
    {
        name: 'status',
        type: 'string',
        nullable: false,
        defaultValue: 'pending',
        notes: 'pending, approved, denied, inside or completed.',
    },
    {
        name: 'pass_code',
        type: 'string(12)',
        key: 'UK',
        nullable: true,
        notes: 'Short code encoded in the QR pass; unique; issued on approval.',
    },
    {
        name: 'denial_reason',
        type: 'text',
        nullable: true,
        notes: 'Reason shown to the visitor when denied.',
    },
    {
        name: 'is_walk_in',
        type: 'boolean',
        nullable: false,
        defaultValue: 'false',
        notes: 'Registered at the gate by a guard.',
    },
    {
        name: 'id_document_path',
        type: 'string',
        nullable: true,
        notes: 'Private disk path for the uploaded valid ID.',
    },
    {
        name: 'id_document_name',
        type: 'string',
        nullable: true,
        notes: 'Original uploaded file name.',
    },
    {
        name: 'approved_at',
        type: 'timestamp',
        nullable: true,
        notes: 'When the admin approved or denied.',
    },
    {
        name: 'approved_by',
        type: 'uuid',
        key: 'FK',
        nullable: true,
        notes: 'Admin who decided.',
    },
    {
        name: 'time_in',
        type: 'timestamp',
        nullable: true,
        notes: 'Recorded by the first successful gate scan.',
    },
    {
        name: 'time_out',
        type: 'timestamp',
        nullable: true,
        notes: 'Recorded by the second successful gate scan.',
    },
    {
        name: 'checked_in_by',
        type: 'uuid',
        key: 'FK',
        nullable: true,
        notes: 'Guard who scanned the visitor in.',
    },
    {
        name: 'checked_out_by',
        type: 'uuid',
        key: 'FK',
        nullable: true,
        notes: 'Guard who scanned the visitor out.',
    },
    {
        name: 'created_at',
        type: 'timestamp',
        nullable: false,
        notes: 'Created time.',
    },
    {
        name: 'updated_at',
        type: 'timestamp',
        nullable: false,
        notes: 'Last updated time.',
    },
];

const tableCount = computed(() => 2);

const printPage = (): void => {
    window.print();
};
</script>

<template>
    <Head title="Database ERD" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-wrap items-start justify-between gap-3 print:hidden"
        >
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <Database class="size-5" />
                    Database design — QRVisits
                </h1>
                <p class="text-sm text-muted-foreground">
                    Entity relationship diagram and table structure for the
                    visitor flow. {{ tableCount }} tables.
                </p>
            </div>
            <Button variant="outline" @click="printPage">
                <Printer class="size-4" />
                Print
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Entity relationship diagram</CardTitle>
                <CardDescription>
                    Users and visits only. Walk-ins are visits without a user
                    account. Approvals and gate scans reference the same users
                    table.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="diagramLoading"
                    class="flex h-64 items-center justify-center rounded-xl border border-dashed text-sm text-muted-foreground print:hidden"
                >
                    Loading diagram…
                </div>

                <div
                    v-else-if="diagramError"
                    class="space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200"
                >
                    <div class="flex items-center gap-2 font-medium">
                        <AlertTriangle class="size-4" />
                        The diagram needs an internet connection.
                    </div>
                    <p>
                        Mermaid is loaded from a CDN. The table structure below
                        still works offline. Try refreshing once you are online.
                    </p>
                </div>

                <div
                    v-else
                    class="overflow-x-auto rounded-xl border bg-white p-4 dark:bg-white"
                    v-html="diagramSvg"
                />

                <p class="mt-3 text-xs text-muted-foreground">
                    Relationships: a user may submit many visits; each visit has
                    at most one approver, one check-in guard and one check-out
                    guard. <span class="font-mono">||--o{</span> means one to
                    many; <span class="font-mono">|o--o{</span> means zero or
                    one to many.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Table2 class="size-4" />
                    users
                </CardTitle>
                <CardDescription>
                    Accounts for visitors, guards and admins. Role decides what
                    each person can do.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead class="bg-muted/50 text-left text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Column</th>
                                <th class="px-4 py-3 font-medium">Type</th>
                                <th class="px-4 py-3 font-medium">Key</th>
                                <th class="px-4 py-3 font-medium">Null</th>
                                <th class="px-4 py-3 font-medium">Default</th>
                                <th class="px-4 py-3 font-medium">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="column in usersColumns"
                                :key="column.name"
                                class="border-t align-top"
                            >
                                <td class="px-4 py-2 font-mono text-xs">
                                    {{ column.name }}
                                </td>
                                <td class="px-4 py-2 text-muted-foreground">
                                    {{ column.type }}
                                </td>
                                <td class="px-4 py-2">
                                    <span
                                        v-if="column.key"
                                        class="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px] font-semibold"
                                    >
                                        {{ column.key }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    {{ column.nullable ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-4 py-2 font-mono text-xs">
                                    {{ column.defaultValue ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-muted-foreground">
                                    {{ column.notes }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Table2 class="size-4" />
                    visits
                </CardTitle>
                <CardDescription>
                    One row per visit request or walk-in, including approval,
                    gate scan and uploaded ID details.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full min-w-[820px] text-sm">
                        <thead class="bg-muted/50 text-left text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Column</th>
                                <th class="px-4 py-3 font-medium">Type</th>
                                <th class="px-4 py-3 font-medium">Key</th>
                                <th class="px-4 py-3 font-medium">Null</th>
                                <th class="px-4 py-3 font-medium">Default</th>
                                <th class="px-4 py-3 font-medium">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="column in visitColumns"
                                :key="column.name"
                                class="border-t align-top"
                            >
                                <td class="px-4 py-2 font-mono text-xs">
                                    {{ column.name }}
                                </td>
                                <td class="px-4 py-2 text-muted-foreground">
                                    {{ column.type }}
                                </td>
                                <td class="px-4 py-2">
                                    <span
                                        v-if="column.key"
                                        class="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px] font-semibold"
                                    >
                                        {{ column.key }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    {{ column.nullable ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-4 py-2 font-mono text-xs">
                                    {{ column.defaultValue ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-muted-foreground">
                                    {{ column.notes }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <div class="rounded-xl border p-4 text-xs text-muted-foreground">
            <p class="font-medium text-foreground">Notes</p>
            <ul class="mt-1 list-inside list-disc space-y-1">
                <li>
                    Authentication helper tables (sessions, passkeys, password
                    resets) and framework tables (cache, jobs, telescope) are
                    intentionally left out.
                </li>
                <li>
                    The uploaded valid ID is stored as a private file; only its
                    path and original name are kept in the database.
                </li>
                <li>
                    Audit references use “null on delete”, so visit history
                    survives removal of a staff account.
                </li>
            </ul>
        </div>
    </div>
</template>
