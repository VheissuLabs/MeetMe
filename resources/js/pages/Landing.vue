<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3'
    import { GithubIcon } from '@/components/icons'
    import { Button } from '@/components/ui/button'
    import { register } from '@/routes'
    import { redirect as socialRedirect } from '@/routes/social'

    const props = defineProps<{ conferenceName: string }>()

    const steps = [
        { title: 'Scan.', body: 'Point your camera at their code.' },
        {
            title: 'Ask.',
            body: `You get an AI-written icebreaker — "What's the most cursed production bug they've ever shipped?"`,
        },
        { title: 'Score.', body: 'They confirm, you both get a point. The leaderboard is live at the venue.' },
    ]

    const screens = ['Dashboard', 'Your question', 'Live leaderboard']
    const repo = 'https://github.com/VheissuLabs/MeetMe'
</script>

<template>
    <Head :title="`MeetMe — ${props.conferenceName}`" />

    <div class="min-h-screen bg-background text-foreground">
        <main class="mx-auto flex max-w-3xl flex-col gap-16 px-6 py-16 sm:py-24">
            <!-- Hero -->
            <section class="flex flex-col items-center gap-6 text-center">
                <h1 class="text-4xl font-bold tracking-tight text-balance sm:text-6xl">
                    Turn "so, what do you do?" into a game.
                </h1>
                <p class="max-w-xl text-lg text-pretty text-muted-foreground">
                    Every attendee gets a QR code and AI-written icebreakers. Scan someone, ask their question, and you
                    both score. Most connections wins {{ props.conferenceName }}.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <Button as-child size="lg">
                        <a :href="socialRedirect.url('github')">
                            <GithubIcon class="size-4" />
                            Sign in with GitHub
                        </a>
                    </Button>
                    <Button as-child size="lg" variant="outline">
                        <Link :href="register()">Join with email</Link>
                    </Button>
                </div>
            </section>

            <!-- How it works -->
            <section class="grid gap-8 sm:grid-cols-3">
                <div v-for="(step, index) in steps" :key="index" class="flex flex-col gap-3">
                    <div
                        class="flex size-9 items-center justify-center rounded-full bg-primary font-bold text-primary-foreground"
                    >
                        {{ index + 1 }}
                    </div>
                    <p>
                        <span class="font-semibold">{{ step.title }}</span> {{ step.body }}
                    </p>
                    <div class="mt-1 aspect-[9/19] w-full max-w-[150px] rounded-2xl border-4 border-muted bg-muted/40">
                        <div
                            class="flex h-full items-center justify-center p-3 text-center text-xs text-muted-foreground"
                        >
                            {{ screens[index] }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Below the fold -->
            <section class="flex flex-col gap-3 rounded-2xl border p-8">
                <h2 class="text-2xl font-bold">Leave with more than points.</h2>
                <p class="text-pretty text-muted-foreground">
                    Everyone you meet lands on your connections list — GitHub, X, Bluesky, and what they told you. After
                    the conference, one recap email with all of it.
                </p>
            </section>

            <!-- Footer -->
            <footer class="text-center text-sm text-muted-foreground">
                Open source, MIT. Built with Laravel 13, Inertia v3, and Reverb. →
                <a :href="repo" class="font-medium underline underline-offset-4">GitHub</a>
            </footer>
        </main>
    </div>
</template>
