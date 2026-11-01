<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarCheck,
    FileText,
    QrCode,
    ScanLine,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const features = [
    {
        icon: QrCode,
        title: 'Pre-registered QR passes',
        description:
            'Visitors request a visit online and receive a QR pass after approval.',
    },
    {
        icon: ScanLine,
        title: 'Fast gate scanning',
        description:
            'One scan records time-in. A second scan records time-out.',
    },
    {
        icon: Users,
        title: 'Live campus view',
        description:
            'See exactly who is inside right now, with a printable emergency list.',
    },
    {
        icon: FileText,
        title: 'Clean digital records',
        description:
            'Searchable visit history, approvals and exportable reports.',
    },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="relative min-h-screen overflow-hidden bg-background">
        <div
            class="pointer-events-none absolute -top-40 -right-32 size-[32rem] rounded-full bg-primary/10 blur-3xl dark:bg-primary/15"
        />
        <div
            class="pointer-events-none absolute -bottom-48 -left-40 size-[30rem] rounded-full bg-primary/5 blur-3xl dark:bg-primary/10"
        />

        <div
            class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-6 lg:px-8"
        >
            <header class="flex items-center justify-between gap-4">
                <Link href="/" class="flex items-center gap-2.5">
                    <span
                        class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-sm"
                    >
                        <AppLogoIcon class="size-5 fill-current" />
                    </span>
                    <span class="text-lg font-semibold tracking-tight">
                        QRVisits
                    </span>
                </Link>

                <nav class="flex items-center gap-2">
                    <template v-if="$page.props.auth.user">
                        <Button as-child>
                            <Link :href="dashboard()">
                                Dashboard
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" as-child>
                            <Link :href="login()">Log in</Link>
                        </Button>
                        <Button v-if="canRegister" as-child>
                            <Link :href="register()">Create account</Link>
                        </Button>
                    </template>
                </nav>
            </header>

            <main
                class="grid flex-1 items-center gap-12 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:py-10"
            >
                <section>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-medium text-primary dark:border-primary/25 dark:bg-primary/10"
                    >
                        <ShieldCheck class="size-3.5" />
                        Campus security, simplified
                    </span>

                    <h1
                        class="mt-5 text-4xl leading-tight font-semibold tracking-tight text-balance sm:text-5xl"
                    >
                        Visitor registration and gate security
                        <span class="text-primary">in one place.</span>
                    </h1>

                    <p
                        class="mt-5 max-w-xl text-base leading-relaxed text-muted-foreground"
                    >
                        Visitors pre-register and upload a valid ID. Admins
                        approve the visit. Guards scan the QR pass at the gate —
                        no more paper logbooks or gate queues.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <template v-if="$page.props.auth.user">
                            <Button size="lg" as-child>
                                <Link :href="dashboard()">
                                    Open dashboard
                                    <ArrowRight class="size-4" />
                                </Link>
                            </Button>
                        </template>
                        <template v-else>
                            <Button size="lg" as-child>
                                <Link :href="login()">
                                    Sign in
                                    <ArrowRight class="size-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="canRegister"
                                size="lg"
                                variant="outline"
                                as-child
                            >
                                <Link :href="register()"
                                    >Create visitor account</Link
                                >
                            </Button>
                        </template>
                    </div>

                    <dl
                        class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t pt-6"
                    >
                        <div>
                            <dt
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Gate check-in
                            </dt>
                            <dd class="mt-1 text-sm font-medium">Seconds</dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Records
                            </dt>
                            <dd class="mt-1 text-sm font-medium">
                                Fully digital
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Emergency list
                            </dt>
                            <dd class="mt-1 text-sm font-medium">One click</dd>
                        </div>
                    </dl>
                </section>

                <section class="relative">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#123B2C] via-[#0B2A20] to-[#06120E] p-6 shadow-2xl shadow-primary/20 sm:p-8"
                    >
                        <div
                            class="absolute -top-16 -right-10 size-56 rounded-full border border-white/10"
                        />
                        <div
                            class="absolute -bottom-24 -left-10 size-72 rounded-full border border-white/10"
                        />

                        <div class="relative flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs tracking-[0.2em] text-emerald-200/70 uppercase"
                                >
                                    Visitor pass
                                </p>
                                <p
                                    class="mt-1 text-lg font-semibold text-white"
                                >
                                    Maria Santos
                                </p>
                                <p class="text-sm text-emerald-100/70">
                                    Parent meeting · Registrar Office
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-emerald-400/15 px-2.5 py-1 text-[11px] font-medium text-emerald-300 ring-1 ring-emerald-400/30"
                            >
                                Approved
                            </span>
                        </div>

                        <div
                            class="relative mt-8 flex flex-col items-center gap-4 sm:flex-row"
                        >
                            <div class="rounded-2xl bg-white p-3 shadow-lg">
                                <div class="grid grid-cols-7 gap-1">
                                    <span
                                        v-for="cell in 49"
                                        :key="cell"
                                        class="size-3 rounded-[3px] sm:size-3.5"
                                        :class="
                                            [
                                                1, 2, 3, 5, 7, 8, 10, 12, 14,
                                                15, 16, 18, 19, 21, 22, 24, 25,
                                                27, 28, 31, 33, 34, 36, 38, 39,
                                                40, 42, 44, 45, 47, 48, 49,
                                            ].includes(cell)
                                                ? 'bg-[#0B2A20]'
                                                : 'bg-[#E7F2EC]'
                                        "
                                    />
                                </div>
                            </div>

                            <div class="w-full space-y-2 text-sm">
                                <div
                                    class="flex justify-between gap-3 border-b border-white/10 pb-2"
                                >
                                    <span class="text-emerald-100/60"
                                        >Date</span
                                    >
                                    <span class="text-white">Today</span>
                                </div>
                                <div
                                    class="flex justify-between gap-3 border-b border-white/10 pb-2"
                                >
                                    <span class="text-emerald-100/60"
                                        >Pass code</span
                                    >
                                    <span
                                        class="font-mono tracking-widest text-white"
                                        >4KX9TQ2M</span
                                    >
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-emerald-100/60"
                                        >Valid</span
                                    >
                                    <span class="text-white"
                                        >Visit date only</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            class="relative mt-6 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white/5 px-3 py-2 text-xs text-emerald-100/80 ring-1 ring-white/10"
                        >
                            <span class="inline-flex items-center gap-2">
                                <CalendarCheck
                                    class="size-4 text-emerald-300"
                                />
                                Scanned in at the gate · 09:41 AM
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 text-emerald-300"
                            >
                                <Users class="size-3.5" />
                                4 inside campus
                            </span>
                        </div>
                    </div>
                </section>
            </main>

            <section class="grid gap-4 pb-10 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-2xl border bg-card/70 p-4 backdrop-blur"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <component :is="feature.icon" class="size-4" />
                    </span>
                    <p class="mt-3 text-sm font-medium">{{ feature.title }}</p>
                    <p
                        class="mt-1 text-xs leading-relaxed text-muted-foreground"
                    >
                        {{ feature.description }}
                    </p>
                </div>
            </section>

            <footer
                class="flex flex-wrap items-center justify-between gap-2 border-t py-5 text-xs text-muted-foreground"
            >
                <span
                    >QRVisits · Campus visitor registration and monitoring</span
                >
                <span>Built for faster, safer gate operations.</span>
            </footer>
        </div>
    </div>
</template>
