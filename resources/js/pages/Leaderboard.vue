<script setup lang="ts">
    import { Head, router, usePage } from '@inertiajs/vue3'
    import { useEcho } from '@laravel/echo-vue'
    import { computed } from 'vue'
    import LeaderboardBoard from '@/components/LeaderboardBoard.vue'
    import AppLayout from '@/layouts/AppLayout.vue'
    import { leaderboard } from '@/routes'

    type Ranking = { name: string; pronouns: string | null; avatar_url: string | null; qr_token?: string }

    const props = defineProps<{
        conferenceName: string
        rankings: Array<Ranking & { score: number }>
        conversationalists: Array<Ranking & { average: number; rating_count: number }>
    }>()

    const page = usePage()

    const isAuthed = computed(() => page.props.auth?.user != null)

    const connectionRows = computed(() => props.rankings.map((row) => ({ ...row, value: row.score })))

    const conversationalistRows = computed(() =>
        props.conversationalists.map((row) => ({
            ...row,
            value: `★ ${row.average.toFixed(1)}`,
            meta: `${row.rating_count} ${row.rating_count === 1 ? 'rating' : 'ratings'}`,
        })),
    )

    // Dumb event, smart refetch: a confirmed meeting can change both boards,
    // so reload both props when one fires.
    useEcho(
        'leaderboard',
        '.LeaderboardChanged',
        () => router.reload({ only: ['rankings', 'conversationalists'] }),
        [],
        'public',
    )
</script>

<template>
    <Head :title="`Leaderboard — ${conferenceName}`" />

    <AppLayout v-if="isAuthed" :breadcrumbs="[{ title: 'Leaderboard', href: leaderboard() }]">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-10 p-4">
            <h1 class="text-center text-4xl font-bold tracking-tight sm:text-5xl">{{ conferenceName }} Leaderboard</h1>
            <LeaderboardBoard
                heading="Most Connections"
                tagline="Most connections wins. Scan someone to climb."
                empty-message="No confirmed meetings yet — be the first on the board!"
                :rankings="connectionRows"
            />
            <LeaderboardBoard
                heading="Top Conversationalists"
                tagline="Best-rated icebreaker answers."
                empty-message="No rated answers yet — give a great answer to climb."
                :rankings="conversationalistRows"
            />
        </div>
    </AppLayout>

    <div v-else class="min-h-screen bg-background px-4 py-8 text-foreground sm:px-8">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-10">
            <h1 class="text-center text-4xl font-bold tracking-tight sm:text-5xl">{{ conferenceName }} Leaderboard</h1>
            <LeaderboardBoard
                heading="Most Connections"
                tagline="Most connections wins. Scan someone to climb."
                empty-message="No confirmed meetings yet — be the first on the board!"
                :rankings="connectionRows"
            />
            <LeaderboardBoard
                heading="Top Conversationalists"
                tagline="Best-rated icebreaker answers."
                empty-message="No rated answers yet — give a great answer to climb."
                :rankings="conversationalistRows"
            />
        </div>
    </div>
</template>
