<script setup lang="ts">
    import { Head, InfiniteScroll, Link } from '@inertiajs/vue3'
    import { Mail, Pencil } from '@lucide/vue'
    import { BlueskyIcon, GithubIcon, LinkedInIcon, XIcon } from '@/components/icons'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Button } from '@/components/ui/button'
    import { Card, CardContent } from '@/components/ui/card'
    import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
    import { useInitials } from '@/composables/useInitials'
    import { connections as connectionsRoute } from '@/routes'
    import { edit as profileEdit } from '@/routes/profile'

    type Profile = {
        qr_token: string
        name: string
        pronouns: string | null
        headline: string | null
        job_title: string | null
        company: string | null
        avatar_url: string | null
        socials: { github?: string; x?: string; bluesky?: string; linkedin?: string }
        email?: string
    }

    type FeedItem = {
        meeting_id: string
        question: string
        answer: string
        rating: number | null
        asker: string | null
    }

    const props = defineProps<{
        profile: Profile
        isSelf: boolean
        stats: { connections: number; averageRating: number | null }
        feed: { data: FeedItem[] }
        achievements: Array<{ key: string; name: string; description: string; icon: string }>
    }>()

    defineOptions({
        layout: {
            breadcrumbs: [{ title: 'Connections', href: connectionsRoute() }],
        },
    })

    const { getInitials } = useInitials()

    const socialLinks = [
        { key: 'github', label: 'GitHub', icon: GithubIcon },
        { key: 'x', label: 'X', icon: XIcon },
        { key: 'bluesky', label: 'Bluesky', icon: BlueskyIcon },
        { key: 'linkedin', label: 'LinkedIn', icon: LinkedInIcon },
    ] as const

    const roleLine = [props.profile.job_title, props.profile.company].filter(Boolean).join(' · ')

    const stars = (rating: number | null): string =>
        rating === null ? '' : '★'.repeat(rating) + '☆'.repeat(5 - rating)
</script>

<template>
    <Head :title="profile.name" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <!-- Identity card -->
        <div class="flex flex-col gap-4">
            <div class="flex items-start gap-4">
                <Avatar class="size-20 shrink-0">
                    <AvatarImage v-if="profile.avatar_url" :src="profile.avatar_url" :alt="profile.name" />
                    <AvatarFallback class="text-2xl">{{ getInitials(profile.name) }}</AvatarFallback>
                </Avatar>

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold">
                        {{ profile.name }}
                        <span v-if="profile.pronouns" class="text-base font-normal text-muted-foreground">
                            · {{ profile.pronouns }}
                        </span>
                    </h1>
                    <p v-if="roleLine" class="text-sm text-muted-foreground">{{ roleLine }}</p>
                </div>

                <Button v-if="isSelf" as-child variant="outline" size="sm" class="shrink-0">
                    <Link :href="profileEdit()"><Pencil class="size-3.5" /> Edit</Link>
                </Button>
            </div>

            <p v-if="profile.headline" class="text-pretty">{{ profile.headline }}</p>

            <div class="flex items-center gap-4">
                <a
                    v-for="social in socialLinks"
                    v-show="profile.socials[social.key]"
                    :key="social.key"
                    :href="profile.socials[social.key]"
                    target="_blank"
                    rel="noopener"
                    :aria-label="social.label"
                >
                    <component
                        :is="social.icon"
                        class="size-5 text-muted-foreground transition-colors hover:text-foreground"
                    />
                </a>
                <a v-if="profile.email" :href="`mailto:${profile.email}`" aria-label="Email">
                    <Mail class="size-5 text-muted-foreground transition-colors hover:text-foreground" />
                </a>
            </div>

            <TooltipProvider v-if="achievements.length" :delay-duration="0">
                <div class="flex flex-wrap gap-2">
                    <Tooltip v-for="achievement in achievements" :key="achievement.key">
                        <TooltipTrigger as-child>
                            <span
                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs"
                                :data-test="`achievement-${achievement.key}`"
                            >
                                <span>{{ achievement.icon }}</span>
                                <span>{{ achievement.name }}</span>
                            </span>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{{ achievement.description }}</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            </TooltipProvider>

            <p class="text-sm text-muted-foreground">
                <span class="font-semibold text-foreground">{{ stats.connections }}</span>
                {{ stats.connections === 1 ? 'connection' : 'connections' }}
                <template v-if="stats.averageRating !== null">
                    · <span class="text-yellow-400">★</span> {{ stats.averageRating }} avg
                </template>
            </p>
        </div>

        <!-- Icebreaker feed -->
        <div class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">Icebreakers</h2>

            <p v-if="feed.data.length === 0" class="text-sm text-muted-foreground" data-test="feed-empty">
                {{ isSelf ? "You haven't answered any icebreakers yet." : 'No answered icebreakers to show yet.' }}
            </p>

            <InfiniteScroll v-else data="feed" class="flex flex-col gap-3">
                <Card v-for="item in feed.data" :key="item.meeting_id" data-test="feed-item">
                    <CardContent class="space-y-2 p-4">
                        <p class="text-xs text-muted-foreground">
                            <span class="font-medium text-foreground">{{ item.asker ?? 'Someone' }}</span> asked
                        </p>
                        <p class="font-medium">{{ item.question }}</p>
                        <p class="text-muted-foreground">{{ item.answer }}</p>
                        <p v-if="item.rating" class="text-sm text-yellow-400">{{ stars(item.rating) }}</p>
                    </CardContent>
                </Card>
            </InfiniteScroll>
        </div>
    </div>
</template>
