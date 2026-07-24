<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import { Mail } from '@lucide/vue'
    import { BlueskyIcon, GithubIcon, LinkedInIcon, XIcon } from '@/components/icons'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Card, CardContent, CardHeader } from '@/components/ui/card'
    import { useInitials } from '@/composables/useInitials'
    import { connections as connectionsRoute } from '@/routes'

    type Profile = {
        meeting_id: string
        qr_token: string
        name: string
        pronouns: string | null
        avatar_url: string | null
        socials: { github?: string; x?: string; bluesky?: string; linkedin?: string }
        email?: string
        question: string
        answer: string | null
        answerRedacted: boolean
        rating: number | null
    }

    const props = defineProps<{ profile: Profile }>()

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
</script>

<template>
    <Head :title="props.profile.name" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
        <Card>
            <CardHeader>
                <div class="flex items-center gap-4">
                    <Avatar class="size-16">
                        <AvatarImage v-if="profile.avatar_url" :src="profile.avatar_url" :alt="profile.name" />
                        <AvatarFallback class="text-lg">{{ getInitials(profile.name) }}</AvatarFallback>
                    </Avatar>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold">
                            {{ profile.name }}
                            <span v-if="profile.pronouns" class="text-sm font-normal text-muted-foreground">
                                · {{ profile.pronouns }}
                            </span>
                        </h1>
                        <div class="mt-2 flex items-center gap-3">
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
                    </div>
                </div>
            </CardHeader>
            <CardContent class="space-y-1">
                <p class="text-sm font-medium">{{ profile.question }}</p>
                <p
                    v-if="profile.answerRedacted"
                    class="text-sm text-muted-foreground italic"
                    data-test="answer-redacted"
                >
                    Answer redacted
                </p>
                <p v-else class="text-sm text-muted-foreground">{{ profile.answer }}</p>
                <p v-if="profile.rating" class="text-sm text-yellow-400" data-test="rating">
                    {{ '★'.repeat(profile.rating) }}{{ '☆'.repeat(5 - profile.rating) }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
