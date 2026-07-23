<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import { Mail } from '@lucide/vue'
    import Heading from '@/components/Heading.vue'
    import { BlueskyIcon, GithubIcon, XIcon } from '@/components/icons'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Card, CardContent, CardHeader } from '@/components/ui/card'
    import { useInitials } from '@/composables/useInitials'
    import { connections as connectionsRoute } from '@/routes'

    type Connection = {
        meeting_id: string
        name: string
        pronouns: string | null
        avatar_url: string | null
        socials: { github?: string; x?: string; bluesky?: string }
        email?: string
        question: string
        answer: string | null
        answerRedacted: boolean
        rating: number | null
    }

    defineProps<{ connections: Connection[] }>()

    defineOptions({
        layout: {
            breadcrumbs: [{ title: 'Connections', href: connectionsRoute() }],
        },
    })

    const { getInitials } = useInitials()
</script>

<template>
    <Head title="Connections" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
        <Heading title="Connections" description="Everyone you've met — your follow-up list." />

        <p v-if="connections.length === 0" class="text-sm text-muted-foreground" data-test="connections-empty">
            No connections yet. Scan someone to start meeting people!
        </p>

        <Card v-for="connection in connections" :key="connection.meeting_id" data-test="connection">
            <CardHeader>
                <div class="flex items-center gap-3">
                    <Avatar class="size-11">
                        <AvatarImage v-if="connection.avatar_url" :src="connection.avatar_url" :alt="connection.name" />
                        <AvatarFallback>{{ getInitials(connection.name) }}</AvatarFallback>
                    </Avatar>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">
                            {{ connection.name }}
                            <span v-if="connection.pronouns" class="text-sm font-normal text-muted-foreground">
                                · {{ connection.pronouns }}
                            </span>
                        </p>
                        <div class="mt-1 flex items-center gap-3">
                            <a
                                v-if="connection.socials.github"
                                :href="connection.socials.github"
                                target="_blank"
                                rel="noopener"
                                aria-label="GitHub"
                            >
                                <GithubIcon
                                    class="size-4 text-muted-foreground transition-colors hover:text-foreground"
                                />
                            </a>
                            <a
                                v-if="connection.socials.x"
                                :href="connection.socials.x"
                                target="_blank"
                                rel="noopener"
                                aria-label="X"
                            >
                                <XIcon class="size-4 text-muted-foreground transition-colors hover:text-foreground" />
                            </a>
                            <a
                                v-if="connection.socials.bluesky"
                                :href="connection.socials.bluesky"
                                target="_blank"
                                rel="noopener"
                                aria-label="Bluesky"
                            >
                                <BlueskyIcon
                                    class="size-4 text-muted-foreground transition-colors hover:text-foreground"
                                />
                            </a>
                            <a v-if="connection.email" :href="`mailto:${connection.email}`" aria-label="Email">
                                <Mail class="size-4 text-muted-foreground transition-colors hover:text-foreground" />
                            </a>
                        </div>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="space-y-1">
                <p class="text-sm font-medium">{{ connection.question }}</p>
                <p
                    v-if="connection.answerRedacted"
                    class="text-sm text-muted-foreground italic"
                    data-test="answer-redacted"
                >
                    Answer redacted
                </p>
                <p v-else class="text-sm text-muted-foreground">{{ connection.answer }}</p>
                <p v-if="connection.rating" class="text-sm text-yellow-400" data-test="rating">
                    {{ '★'.repeat(connection.rating) }}{{ '☆'.repeat(5 - connection.rating) }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
