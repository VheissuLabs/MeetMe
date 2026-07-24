<script setup lang="ts">
    import { Head, router, usePage } from '@inertiajs/vue3'
    import { useEcho } from '@laravel/echo-vue'
    import { computed } from 'vue'
    import LeaderboardBoard from '@/components/LeaderboardBoard.vue'
    import AppLayout from '@/layouts/AppLayout.vue'
    import { leaderboard } from '@/routes'

    defineProps<{
        conferenceName: string
        rankings: Array<{ name: string; pronouns: string | null; avatar_url: string | null; score: number }>
    }>()

    const page = usePage()

    // Signed-in visitors get the app shell (sidebar); the public TV view
    // renders standalone and fullscreen.
    const isAuthed = computed(() => page.props.auth?.user != null)

    // Dumb event, smart refetch: the broadcast carries no payload, so we
    // just reload the rankings prop when a meeting confirms.
    useEcho('leaderboard', '.LeaderboardChanged', () => router.reload({ only: ['rankings'] }), [], 'public')
</script>

<template>
    <Head :title="`Leaderboard — ${conferenceName}`" />

    <AppLayout v-if="isAuthed" :breadcrumbs="[{ title: 'Leaderboard', href: leaderboard() }]">
        <div class="p-4">
            <LeaderboardBoard :conference-name="conferenceName" :rankings="rankings" />
        </div>
    </AppLayout>

    <div v-else class="min-h-screen bg-background px-4 py-8 text-foreground sm:px-8">
        <LeaderboardBoard :conference-name="conferenceName" :rankings="rankings" />
    </div>
</template>
