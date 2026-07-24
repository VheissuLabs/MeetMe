<script setup lang="ts">
    import { computed } from 'vue'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { useInitials } from '@/composables/useInitials'
    import type { User } from '@/types'

    type Props = {
        user: User
        showEmail?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        showEmail: false,
    })

    const { getInitials } = useInitials()

    // Prefer the resolved profile photo (avatar_url); fall back to initials.
    const avatarUrl = computed(() => props.user.avatar_url || props.user.avatar || '')
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="user.name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
    </div>
</template>
