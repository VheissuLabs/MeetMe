<script setup lang="ts">
    import { ref } from 'vue'
    import { cn } from '@/lib/utils'

    const model = defineModel<number | null>({ default: null })

    defineProps<{ class?: string }>()

    const hovered = ref<number | null>(null)

    const stars = [1, 2, 3, 4, 5]
</script>

<template>
    <div :class="cn('flex items-center gap-1', $props.class)" role="radiogroup" aria-label="Rating">
        <button
            v-for="star in stars"
            :key="star"
            type="button"
            role="radio"
            :aria-checked="model === star"
            :aria-label="`${star} star${star === 1 ? '' : 's'}`"
            class="text-3xl leading-none transition-transform hover:scale-110"
            :class="(hovered ?? model ?? 0) >= star ? 'text-yellow-400' : 'text-muted-foreground/30'"
            :data-test="`star-${star}`"
            @mouseenter="hovered = star"
            @mouseleave="hovered = null"
            @click="model = star"
        >
            ★
        </button>
    </div>
</template>
