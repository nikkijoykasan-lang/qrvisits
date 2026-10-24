<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer, Siren } from '@lucide/vue';
import { computed } from 'vue';
import { index as liveBoardIndex } from '@/actions/App/Http/Controllers/LiveBoardController';
import { Button } from '@/components/ui/button';
import { formatDateTime, formatTime } from '@/lib/datetime';
import type { Visit } from '@/types';

const props = defineProps<{
    visits: Visit[];
    generatedAt: string;
}>();

const printedAt = computed(() =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date()),
);

const printList = (): void => {
    window.print();
};
</script>

<template>
    <Head title="Emergency visitor list" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-6">
        <div
            class="flex flex-wrap items-start justify-between gap-3 print:hidden"
        >
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <Siren class="size-5 text-red-600" />
                    Emergency visitor list
                </h1>
                <p class="text-sm text-muted-foreground">
                    Everyone currently inside the campus. Print this list and
                    take it to the evacuation area.
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="liveBoardIndex()">
                        <ArrowLeft class="size-4" />
                        Back to board
                    </Link>
                </Button>
                <Button @click="printList">
                    <Printer class="size-4" />
                    Print list
                </Button>
            </div>
        </div>

        <div
            class="rounded-xl border bg-white p-6 text-black print:border-0 print:p-0"
        >
            <header class="mb-4 border-b pb-3">
                <p class="text-xs tracking-widest text-gray-500 uppercase">
                    QRVisits
                </p>
                <h2 class="text-lg font-bold">Campus emergency visitor list</h2>
                <p class="text-sm text-gray-600">
                    Generated {{ formatDateTime(generatedAt) }} ·
                    {{ visits.length }} people inside
                </p>
            </header>

            <table v-if="visits.length > 0" class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-xs text-gray-500 uppercase">
                        <th class="py-2 pr-3">#</th>
                        <th class="py-2 pr-3">Visitor</th>
                        <th class="py-2 pr-3">Contact</th>
                        <th class="py-2 pr-3">Purpose</th>
                        <th class="py-2 pr-3">Host</th>
                        <th class="py-2">Time in</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(visit, index) in visits"
                        :key="visit.id"
                        class="border-b align-top last:border-0"
                    >
                        <td class="py-2 pr-3 tabular-nums">
                            {{ index + 1 }}
                        </td>
                        <td class="py-2 pr-3 font-medium">
                            {{ visit.visitor_name }}
                        </td>
                        <td class="py-2 pr-3 whitespace-nowrap">
                            {{ visit.visitor_contact }}
                        </td>
                        <td class="py-2 pr-3">{{ visit.purpose }}</td>
                        <td class="py-2 pr-3">{{ visit.host_name }}</td>
                        <td class="py-2 whitespace-nowrap tabular-nums">
                            {{ formatTime(visit.time_in) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-else class="py-8 text-center text-sm text-gray-600">
                No visitors are currently inside the campus.
            </p>

            <footer
                class="mt-6 flex justify-between border-t pt-3 text-xs text-gray-500"
            >
                <span>Printed {{ printedAt }}</span>
                <span>Guard signature: ______________________</span>
            </footer>
        </div>
    </div>
</template>
