<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    title: string;
    description?: string;
    icon?: Component;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-sidebar-border/70 px-6 py-12 text-center dark:border-sidebar-border"
    >
        <component
            :is="icon"
            v-if="icon"
            class="size-6 text-muted-foreground"
        />
        <p class="text-sm font-medium">{{ title }}</p>
        <p v-if="description" class="max-w-md text-sm text-muted-foreground">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-2">
            <slot />
        </div>
    </div>
</template>
