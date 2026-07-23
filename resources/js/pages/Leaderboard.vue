<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3'
    import { useEcho } from '@laravel/echo-vue'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { useInitials } from '@/composables/useInitials'

    defineProps<{
        conferenceName: string
        rankings: Array<{ name: string; pronouns: string | null; avatar_url: string | null; score: number }>
    }>()

    const { getInitials } = useInitials()

    // Dumb event, smart refetch: the broadcast carries no payload, so we
    // just reload the rankings prop when a meeting confirms.
    useEcho('leaderboard', '.LeaderboardChanged', () => router.reload({ only: ['rankings'] }), [], 'public')

    const medal = (rank: number): string => ['🥇', '🥈', '🥉'][rank] ?? ''
</script>

<template>
    <Head :title="`Leaderboard — ${conferenceName}`" />

    <div class="min-h-screen bg-background px-4 py-8 text-foreground sm:px-8">
        <div class="mx-auto flex max-w-3xl flex-col gap-8">
            <header class="text-center">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ conferenceName }} Leaderboard</h1>
                <p class="mt-2 text-lg text-muted-foreground">Most connections wins. Scan someone to climb.</p>
            </header>

            <p
                v-if="rankings.length === 0"
                class="text-center text-xl text-muted-foreground"
                data-test="leaderboard-empty"
            >
                No confirmed meetings yet — be the first on the board!
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
                        <p class="truncate text-xl font-semibold">{{ row.name }}</p>
                        <p v-if="row.pronouns" class="truncate text-sm text-muted-foreground">{{ row.pronouns }}</p>
                    </div>
                    <span class="shrink-0 text-3xl font-bold tabular-nums">{{ row.score }}</span>
                </li>
            </ol>
        </div>
    </div>
</template>
