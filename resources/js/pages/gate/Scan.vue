<script setup lang="ts">
import { Head, Link, router, useHttp } from '@inertiajs/vue3';
import {
    Camera,
    CameraOff,
    CheckCircle2,
    Keyboard,
    ScanLine,
    ShieldAlert,
    Users,
    XCircle,
} from '@lucide/vue';
import { computed, onBeforeUnmount, ref, useTemplateRef } from 'vue';
import { index as liveBoardIndex } from '@/actions/App/Http/Controllers/LiveBoardController';
import PassScanController from '@/actions/App/Http/Controllers/PassScanController';
import { create as walkInCreate } from '@/actions/App/Http/Controllers/WalkInController';
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
import VisitStatusBadge from '@/components/VisitStatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatTime } from '@/lib/datetime';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, ScanResponse, Visit } from '@/types';

defineOptions({
    layout: [
        AppLayout,
        {
            breadcrumbs: [
                { title: 'Gate scan', href: dashboard() },
            ] satisfies BreadcrumbItem[],
        },
    ],
});

defineProps<{
    stats: {
        inside: number;
        expected_today: number;
        completed_today: number;
    };
}>();

type ScanEntry = ScanResponse & { scanned_at: string };

const video = useTemplateRef<HTMLVideoElement>('video');
const code = ref('');
const cameraActive = ref(false);
const cameraError = ref<string | null>(null);
const result = ref<ScanResponse | null>(null);
const history = ref<ScanEntry[]>([]);

const form = useHttp<{ code: string }, ScanResponse>({ code: '' });

let stream: MediaStream | null = null;
let detector: BarcodeDetector | null = null;
let loop: ReturnType<typeof setTimeout> | null = null;
let stopped = false;

const barcodeSupported = computed(
    () => typeof window !== 'undefined' && 'BarcodeDetector' in window,
);

const lastVisit = computed<Visit | null>(() => result.value?.visit ?? null);

const submitCode = async (rawCode: string): Promise<void> => {
    const value = rawCode.trim();

    if (value === '' || form.processing) {
        return;
    }

    form.code = value;
    const response = await form.post(PassScanController.url());
    result.value = response;
    history.value = [
        { ...response, scanned_at: new Date().toISOString() },
        ...history.value,
    ].slice(0, 6);
    code.value = '';

    if (response.success) {
        router.reload({ only: ['stats'] });
    }
};

const submitManual = async (): Promise<void> => {
    await submitCode(code.value);
};

const stopCamera = (): void => {
    stopped = true;
    cameraActive.value = false;
    detector = null;

    if (loop !== null) {
        clearTimeout(loop);
        loop = null;
    }

    stream?.getTracks().forEach((track) => track.stop());
    stream = null;

    if (video.value) {
        video.value.srcObject = null;
    }
};

const startCamera = async (): Promise<void> => {
    cameraError.value = null;

    if (!barcodeSupported.value) {
        cameraError.value =
            'This browser cannot scan with the camera. Type the pass code below instead.';
        return;
    }

    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });

        if (!video.value) {
            return;
        }

        video.value.srcObject = stream;
        await video.value.play();

        const Detector = window.BarcodeDetector;

        if (!Detector) {
            cameraError.value =
                'This browser cannot scan with the camera. Type the pass code below instead.';
            stopCamera();
            return;
        }

        detector = new Detector({ formats: ['qr_code'] });
        stopped = false;
        cameraActive.value = true;
        void detectLoop();
    } catch {
        cameraError.value =
            'Camera unavailable. Check the camera permission, or type the pass code below.';
        stopCamera();
    }
};

const detectLoop = async (): Promise<void> => {
    if (stopped || !cameraActive.value) {
        return;
    }

    if (detector && video.value && video.value.readyState >= 2) {
        try {
            const codes = await detector.detect(video.value);

            if (codes.length > 0 && !form.processing) {
                const value = codes[0]?.rawValue ?? '';

                if (value !== '') {
                    stopCamera();
                    await submitCode(value);
                    return;
                }
            }
        } catch {
            // Ignore individual frame failures and keep scanning.
        }
    }

    loop = setTimeout(() => void detectLoop(), 350);
};

const dismissResult = (): void => {
    result.value = null;
};

onBeforeUnmount(stopCamera);
</script>

<template>
    <Head title="Gate scan" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <ScanLine class="size-5" />
                    Gate scan
                </h1>
                <p class="text-sm text-muted-foreground">
                    First scan records time-in. Scan again when the visitor
                    leaves.
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="liveBoardIndex()">
                        <Users class="size-4" />
                        Who is inside ({{ stats.inside }})
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="walkInCreate()">Register walk-in</Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Camera class="size-4" />
                            Scan the QR pass
                        </CardTitle>
                        <CardDescription>
                            Hold the visitor's pass in front of the camera.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            class="relative overflow-hidden rounded-xl border bg-black/90"
                        >
                            <video
                                ref="video"
                                class="aspect-video w-full object-cover"
                                :class="{ hidden: !cameraActive }"
                                muted
                                playsinline
                            />
                            <div
                                v-if="!cameraActive"
                                class="flex aspect-video flex-col items-center justify-center gap-2 p-6 text-center text-sm text-white/80"
                            >
                                <CameraOff class="size-8" />
                                <p>Camera is off.</p>
                                <p class="max-w-xs text-xs text-white/60">
                                    {{
                                        barcodeSupported
                                            ? 'Start the camera to scan, or type the pass code below.'
                                            : 'Camera scanning is not supported in this browser. Type the pass code below.'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <Button
                                v-if="!cameraActive"
                                type="button"
                                class="flex-1"
                                @click="startCamera"
                            >
                                <Camera class="size-4" />
                                Start camera
                            </Button>
                            <Button
                                v-else
                                type="button"
                                variant="destructive"
                                class="flex-1"
                                @click="stopCamera"
                            >
                                <CameraOff class="size-4" />
                                Stop camera
                            </Button>
                        </div>

                        <p
                            v-if="cameraError"
                            class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300"
                        >
                            {{ cameraError }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Keyboard class="size-4" />
                            Type the pass code
                        </CardTitle>
                        <CardDescription>
                            Always available, even when the camera is not.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form
                            class="flex flex-col gap-3 sm:flex-row"
                            @submit.prevent="submitManual"
                        >
                            <div class="flex-1">
                                <Label for="code" class="sr-only">
                                    Pass code
                                </Label>
                                <Input
                                    id="code"
                                    v-model="code"
                                    autocomplete="off"
                                    autocapitalize="characters"
                                    placeholder="e.g. 4KX9TQ2M"
                                    class="font-mono text-base tracking-widest uppercase"
                                    v-focus
                                />
                            </div>
                            <Button
                                type="submit"
                                :disabled="
                                    form.processing || code.trim() === ''
                                "
                                class="sm:w-40"
                            >
                                <ScanLine class="size-4" />
                                {{
                                    form.processing
                                        ? 'Checking…'
                                        : 'Record scan'
                                }}
                            </Button>
                        </form>
                        <p
                            v-if="form.hasErrors"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.code }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-6">
                <Card
                    v-if="result"
                    :class="
                        result.success
                            ? 'border-emerald-300 dark:border-emerald-800'
                            : 'border-red-300 dark:border-red-800'
                    "
                >
                    <CardHeader
                        class="flex flex-row items-start justify-between"
                    >
                        <div class="flex items-start gap-3">
                            <CheckCircle2
                                v-if="result.success"
                                class="mt-0.5 size-5 shrink-0 text-emerald-600"
                            />
                            <XCircle
                                v-else
                                class="mt-0.5 size-5 shrink-0 text-red-600"
                            />
                            <div>
                                <CardTitle class="text-base">
                                    {{ result.message }}
                                </CardTitle>
                                <CardDescription v-if="lastVisit">
                                    {{ lastVisit.visitor_name }} ·
                                    {{ lastVisit.purpose }}
                                </CardDescription>
                            </div>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="dismissResult"
                        >
                            Dismiss
                        </Button>
                    </CardHeader>
                    <CardContent v-if="lastVisit" class="space-y-3">
                        <div class="flex items-center gap-2">
                            <VisitStatusBadge
                                :status="lastVisit.status"
                                :label="lastVisit.status_label"
                            />
                            <span
                                v-if="lastVisit.is_walk_in"
                                class="rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground uppercase"
                            >
                                Walk-in
                            </span>
                        </div>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Host
                                </dt>
                                <dd class="truncate">
                                    {{ lastVisit.host_name }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Contact
                                </dt>
                                <dd>{{ lastVisit.visitor_contact }}</dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Time in
                                </dt>
                                <dd>{{ formatTime(lastVisit.time_in) }}</dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs text-muted-foreground uppercase"
                                >
                                    Time out
                                </dt>
                                <dd>{{ formatTime(lastVisit.time_out) }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShieldAlert class="size-4" />
                            Recent scans
                        </CardTitle>
                        <CardDescription>
                            This session's latest results.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="(entry, index) in history"
                            :key="`${entry.scanned_at}-${index}`"
                            class="flex items-start justify-between gap-3 rounded-lg border p-3"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{
                                        entry.visit?.visitor_name ??
                                        'Unknown pass'
                                    }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ entry.message }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 text-xs text-muted-foreground tabular-nums"
                            >
                                {{ formatTime(entry.scanned_at) }}
                            </span>
                        </div>
                        <p
                            v-if="history.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            No scans yet. Results appear here as you scan.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
