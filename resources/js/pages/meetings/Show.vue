<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import { computed } from 'vue'
    import Heading from '@/components/Heading.vue'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Badge } from '@/components/ui/badge'
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
    import { useInitials } from '@/composables/useInitials'

    const props = defineProps<{
        meeting: {
            id: string
            status: 'pending' | 'answered' | 'confirmed' | 'rejected'
            question: string | null
            answer: string | null
            answerRedacted: boolean
            rating: number | null
            isInitiator: boolean
            otherParty: { name: string; pronouns: string | null; avatar_url: string | null }
        }
    }>()

    const { getInitials } = useInitials()

    const statusVariant = computed(
        () =>
            ({
                pending: 'secondary',
                answered: 'secondary',
                confirmed: 'default',
                rejected: 'outline',
            })[props.meeting.status] as 'secondary' | 'default' | 'outline',
    )

    const stateMessage = computed(() => {
        const name = props.meeting.otherParty.name
        const byState: Record<string, { initiator: string; recipient: string }> = {
            pending: {
                initiator: `Ask ${name} this question out loud, then record their answer.`,
                recipient: `${name} scanned your code — they're about to ask you something. No peeking!`,
            },
            answered: {
                initiator: `Waiting for ${name} to confirm your meeting.`,
                recipient: `${name} recorded your answer. Check it captures what you said.`,
            },
            confirmed: {
                initiator: `Confirmed — you and ${name} both scored a point.`,
                recipient: `Confirmed — you and ${name} both scored a point.`,
            },
            rejected: {
                initiator: `${name} didn't confirm this meeting.`,
                recipient: `You rejected this meeting.`,
            },
        }

        return byState[props.meeting.status][props.meeting.isInitiator ? 'initiator' : 'recipient']
    })
</script>

<template>
    <Head title="Meeting" />

    <div class="mx-auto flex w-full max-w-xl flex-col gap-6 p-4">
        <div class="flex items-center gap-4">
            <Avatar class="size-14">
                <AvatarImage
                    v-if="meeting.otherParty.avatar_url"
                    :src="meeting.otherParty.avatar_url"
                    :alt="meeting.otherParty.name"
                />
                <AvatarFallback>{{ getInitials(meeting.otherParty.name) }}</AvatarFallback>
            </Avatar>
            <Heading :title="meeting.otherParty.name" :description="meeting.otherParty.pronouns ?? undefined" />
            <Badge :variant="statusVariant" class="ml-auto capitalize" data-test="meeting-status">{{
                meeting.status
            }}</Badge>
        </div>

        <p class="text-sm text-muted-foreground" data-test="state-message">{{ stateMessage }}</p>

        <Card v-if="meeting.question">
            <CardHeader>
                <CardTitle>Icebreaker</CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-lg" data-test="question">{{ meeting.question }}</p>
            </CardContent>
        </Card>

        <Card v-if="meeting.answer || meeting.answerRedacted">
            <CardHeader>
                <CardTitle class="flex items-center justify-between">
                    Their answer
                    <span v-if="meeting.rating" class="text-sm font-normal text-muted-foreground" data-test="rating">
                        {{ '★'.repeat(meeting.rating) }}{{ '☆'.repeat(5 - meeting.rating) }}
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p v-if="meeting.answerRedacted" class="text-muted-foreground italic" data-test="answer-redacted">
                    Answer redacted
                </p>
                <p v-else data-test="answer">{{ meeting.answer }}</p>
            </CardContent>
        </Card>
    </div>
</template>
