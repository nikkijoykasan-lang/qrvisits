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
    <Card>
        <CardContent class="flex items-start justify-between gap-3 p-4">
            <div class="min-w-0">
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    {{ label }}
                </p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">
                    {{ value }}
                </p>
                <p
                    v-if="hint"
                    class="mt-1 truncate text-xs text-muted-foreground"
                >
                    {{ hint }}
                </p>
            </div>
            <div
                v-if="icon"
                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
            >
                <component :is="icon" class="size-4" />
            </div>
        </CardContent>
    </Card>
</template>
