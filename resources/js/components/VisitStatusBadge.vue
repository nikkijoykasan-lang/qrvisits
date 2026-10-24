<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { VisitStatus } from '@/types';

const props = defineProps<{
    status: VisitStatus;
    label?: string;
}>();

const label = computed(() => props.label ?? props.status);

const classes = computed(() => {
    switch (props.status) {
        case 'approved':
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300';
        case 'inside':
            return 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300';
        case 'denied':
            return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300';
        case 'completed':
            return 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300';
        default:
            return 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300';
    }
});
</script>

<template>
    <Badge variant="outline" :class="classes">{{ label }}</Badge>
</template>
