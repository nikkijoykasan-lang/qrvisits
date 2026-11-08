<script setup lang="ts">
import type { Component } from 'vue';
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    label: string;
    value: number | string;
    hint?: string;
    icon?: Component;
}>();
</script>

<template>
    <Card
        class="rounded-2xl border-border/70 shadow-sm transition-shadow duration-200 hover:shadow-md"
    >
        <CardContent class="flex items-start justify-between gap-4 p-5">
            <div class="min-w-0 space-y-1">
                <p
                    class="text-[11px] font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                >
                    {{ label }}
                </p>
                <p
                    class="text-2xl font-semibold tracking-tight tabular-nums sm:text-[1.75rem]"
                >
                    {{ value }}
                </p>
                <p
                    v-if="hint"
                    class="line-clamp-2 text-xs text-muted-foreground"
                >
                    {{ hint }}
                </p>
            </div>
            <div
                v-if="icon"
                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary ring-1 ring-primary/15"
            >
                <component
                    :is="icon"
                    class="size-5 stroke-[2.25] text-primary"
                />
            </div>
        </CardContent>
    </Card>
</template>
