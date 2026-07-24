<script setup lang="ts">
    import { Link } from '@inertiajs/vue3'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { useInitials } from '@/composables/useInitials'
    import { show as userProfile } from '@/routes/users'

    type Row = {
        name: string
        pronouns: string | null
        avatar_url: string | null
        value: string | number
        meta?: string | null
        qr_token?: string
    }

    defineProps<{
        heading: string
        tagline: string
        emptyMessage: string
        rankings: Row[]
    }>()

    const { getInitials } = useInitials()

    const medal = (rank: number): string => ['🥇', '🥈', '🥉'][rank] ?? ''
</script>

<template>
    <section class="flex flex-col gap-4">
        <header class="text-center">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ heading }}</h2>
            <p class="mt-1 text-muted-foreground">{{ tagline }}</p>
        </header>

        <p v-if="rankings.length === 0" class="text-center text-xl text-muted-foreground" data-test="leaderboard-empty">
            {{ emptyMessage }}
        </p>

        <ol v-else class="flex flex-col gap-2">
            <li
                v-for="(row, index) in rankings"
                :key="index"
                class="flex items-center gap-4 rounded-xl border p-4"
                :class="index < 3 ? 'bg-accent/50' : ''"
                data-test="leaderboard-row"
            >
                <span class="w-10 shrink-0 text-center text-2xl font-bold tabular-nums">
                    {{ medal(index) || index + 1 }}
                </span>
                <Avatar class="size-12">
                    <AvatarImage v-if="row.avatar_url" :src="row.avatar_url" :alt="row.name" />
                    <AvatarFallback>{{ getInitials(row.name) }}</AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                    <Link
                        v-if="row.qr_token"
                        :href="userProfile(row.qr_token)"
                        class="truncate text-xl font-semibold hover:underline"
                    >
                        {{ row.name }}
                    </Link>
                    <p v-else class="truncate text-xl font-semibold">{{ row.name }}</p>
                    <p v-if="row.pronouns" class="truncate text-sm text-muted-foreground">{{ row.pronouns }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <span class="text-3xl font-bold tabular-nums">{{ row.value }}</span>
                    <p v-if="row.meta" class="text-xs text-muted-foreground">{{ row.meta }}</p>
                </div>
            </li>
        </ol>
    </section>
</template>
