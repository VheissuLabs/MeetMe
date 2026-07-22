<script setup lang="ts">
    import { Form, Head, router } from '@inertiajs/vue3'
    import { computed, ref } from 'vue'
    import MeetingAnswerController from '@/actions/App/Http/Controllers/MeetingAnswerController'
    import MeetingAnswerRedactionController from '@/actions/App/Http/Controllers/MeetingAnswerRedactionController'
    import MeetingResolveController from '@/actions/App/Http/Controllers/MeetingResolveController'
    import Heading from '@/components/Heading.vue'
    import InputError from '@/components/InputError.vue'
    import StarRating from '@/components/StarRating.vue'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Badge } from '@/components/ui/badge'
    import { Button } from '@/components/ui/button'
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
    import { Label } from '@/components/ui/label'
    import { Spinner } from '@/components/ui/spinner'
    import { Textarea } from '@/components/ui/textarea'
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
            canRedact: boolean
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

    const canResolve = computed(() => !props.meeting.isInitiator && props.meeting.status === 'answered')

    const rating = ref<number | null>(null)
    const resolving = ref(false)

    function resolve(status: 'confirmed' | 'rejected') {
        resolving.value = true

        router.patch(
            MeetingResolveController(props.meeting.id).url,
            status === 'confirmed' ? { status, rating: rating.value } : { status },
            {
                preserveScroll: true,
                optimistic: (props: Record<string, unknown>) => ({
                    meeting: {
                        ...(props.meeting as object),
                        status,
                        rating: status === 'confirmed' ? rating.value : null,
                    },
                }),
                onFinish: () => (resolving.value = false),
            },
        )
    }
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
            <CardContent class="space-y-4">
                <p class="text-lg" data-test="question">{{ meeting.question }}</p>

                <Form
                    v-if="meeting.isInitiator && meeting.status === 'pending'"
                    v-bind="MeetingAnswerController.form(meeting.id)"
                    class="space-y-3"
                    v-slot="{ errors, processing, validate }"
                >
                    <div class="grid gap-2">
                        <Label for="answer">Their answer</Label>
                        <Textarea
                            id="answer"
                            name="answer"
                            required
                            rows="3"
                            placeholder="Type what they said…"
                            @change="validate('answer')"
                        />
                        <InputError :message="errors.answer" />
                    </div>
                    <Button type="submit" :disabled="processing" data-test="submit-answer">
                        <Spinner v-if="processing" />
                        Send for confirmation
                    </Button>
                </Form>
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
            <CardContent class="space-y-3">
                <p v-if="meeting.answerRedacted" class="text-muted-foreground italic" data-test="answer-redacted">
                    Answer redacted
                </p>
                <p v-else data-test="answer">{{ meeting.answer }}</p>

                <Form
                    v-if="meeting.canRedact"
                    v-bind="MeetingAnswerRedactionController.form(meeting.id)"
                    v-slot="{ processing }"
                    :options="{ preserveScroll: true }"
                >
                    <Button type="submit" variant="ghost" size="sm" :disabled="processing" data-test="redact-answer">
                        Remove my answer
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <Card v-if="canResolve">
            <CardHeader>
                <CardTitle>Did they get it right?</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-sm text-muted-foreground">
                    Rate how well they captured your answer — rating confirms the meeting and scores you both a point.
                </p>
                <StarRating v-model="rating" />
                <div class="flex items-center gap-3">
                    <Button
                        :disabled="rating === null || resolving"
                        data-test="confirm-meeting"
                        @click="resolve('confirmed')"
                    >
                        <Spinner v-if="resolving" />
                        Confirm
                    </Button>
                    <Button
                        variant="ghost"
                        :disabled="resolving"
                        data-test="reject-meeting"
                        @click="resolve('rejected')"
                    >
                        Didn't happen
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
