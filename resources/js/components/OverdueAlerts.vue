<script setup lang="ts">
import { Link, useHttp, usePage } from '@inertiajs/vue3';
import { BellRing, TriangleAlert } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { index as liveBoardIndex } from '@/actions/App/Http/Controllers/LiveBoardController';
import OverdueVisitController from '@/actions/App/Http/Controllers/OverdueVisitController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { OverdueAlertResponse, OverdueVisitorAlert } from '@/types';

const POLL_INTERVAL = 30_000;

const page = usePage();
const isStaff = computed(() => page.props.auth.user.role !== 'visitor');
const alerts = ref<OverdueVisitorAlert[]>([]);
const http = useHttp<Record<string, never>, OverdueAlertResponse>({});

const knownIds = new Set<string>();
let firstLoad = true;
let timer: ReturnType<typeof setInterval> | null = null;

const count = computed(() => alerts.value.length);

const minutesLabel = (minutes: number): string => {
    if (minutes < 60) {
        return `${minutes} min`;
    }

    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    if (hours < 24) {
        return rest === 0 ? `${hours} hr` : `${hours} hr ${rest} min`;
    }

    const days = Math.floor(hours / 24);
    const leftoverHours = hours % 24;

    return leftoverHours === 0
        ? `${days} day${days === 1 ? '' : 's'}`
        : `${days} day${days === 1 ? '' : 's'} ${leftoverHours} hr`;
};

const load = async (): Promise<void> => {
    if (!isStaff.value || document.visibilityState === 'hidden') {
        return;
    }

    const response = await http.get(OverdueVisitController.url());
    alerts.value = response.visits;

    const currentIds = new Set(response.visits.map((visit) => visit.id));

    if (!firstLoad) {
        response.visits
            .filter((visit) => !knownIds.has(visit.id))
            .forEach((visit) => {
                toast.warning(`${visit.visitor_name} is overdue`, {
                    description: `ID ${visit.pass_code ?? '—'} · expected out at ${visit.expected_time_out}`,
                    duration: 8000,
                });
            });
    }

    currentIds.forEach((id) => knownIds.add(id));
    [...knownIds].forEach((id) => {
        if (!currentIds.has(id)) {
            knownIds.delete(id);
        }
    });

    firstLoad = false;
};

const onVisibilityChange = (): void => {
    if (document.visibilityState === 'visible') {
        void load();
    }
};

onMounted(() => {
    if (!isStaff.value) {
        return;
    }

    void load();
    timer = setInterval(() => void load(), POLL_INTERVAL);
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onBeforeUnmount(() => {
    if (timer !== null) {
        clearInterval(timer);
    }

    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>

<template>
    <template v-if="isStaff">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button
                    variant="ghost"
                    size="icon"
                    class="relative"
                    :aria-label="
                        count > 0
                            ? `${count} overdue visitors`
                            : 'No overdue visitors'
                    "
                >
                    <BellRing class="size-4" />
                    <span
                        v-if="count > 0"
                        class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white tabular-nums"
                    >
                        {{ count > 9 ? '9+' : count }}
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-80 sm:w-96">
                <DropdownMenuLabel class="flex items-center gap-2">
                    <TriangleAlert class="size-4 text-amber-500" />
                    Overdue visitors
                    <span
                        v-if="count > 0"
                        class="ml-auto rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300"
                    >
                        {{ count }}
                    </span>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />

                <div
                    v-if="alerts.length === 0"
                    class="px-2 py-6 text-center text-sm text-muted-foreground"
                >
                    Nobody is overdue. Visitors still inside after their
                    expected time-out will appear here.
                </div>

                <DropdownMenuItem
                    v-for="visit in alerts"
                    :key="visit.id"
                    as-child
                    class="items-start"
                >
                    <Link :href="visit.visit_url" class="flex flex-col gap-1">
                        <span
                            class="flex w-full items-center justify-between gap-2"
                        >
                            <span class="truncate font-medium">
                                {{ visit.visitor_name }}
                            </span>
                            <span
                                class="shrink-0 rounded bg-amber-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 uppercase dark:text-amber-300"
                            >
                                {{ minutesLabel(visit.minutes_overdue) }} late
                            </span>
                        </span>
                        <span class="text-xs text-muted-foreground">
                            ID {{ visit.pass_code ?? '—' }} · expected out
                            {{ visit.expected_time_out }}
                        </span>
                    </Link>
                </DropdownMenuItem>

                <DropdownMenuSeparator />
                <DropdownMenuItem as-child>
                    <Link
                        :href="liveBoardIndex()"
                        class="justify-center text-xs font-medium"
                    >
                        Open live board
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </template>
</template>
