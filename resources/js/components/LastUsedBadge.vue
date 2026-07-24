<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3'
    import { computed } from 'vue'

    const props = defineProps<{ method: string }>()

    const show = computed(() => usePage().props.lastLogin === props.method)
</script>

<template>
    <span
        v-if="show"
        class="pointer-events-none absolute -top-2 right-2 rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-medium text-primary-foreground shadow-sm"
    >
        Last used
    </span>
</template>
