<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import Heading from '@/components/Heading.vue'
    import { Badge } from '@/components/ui/badge'
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

    defineProps<{
        meeting: {
            id: string
            status: 'pending' | 'answered' | 'confirmed' | 'rejected'
            question: string
            answer: string | null
            rating: number | null
            isInitiator: boolean
            otherParty: { name: string; pronouns: string | null; avatar_url: string | null }
        }
    }>()
</script>

<template>
    <Head title="Meeting" />

    <div class="mx-auto flex w-full max-w-xl flex-col gap-6 p-4">
        <Heading
            :title="`You met ${meeting.otherParty.name}`"
            :description="meeting.otherParty.pronouns ?? undefined"
        />

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center justify-between">
                    Icebreaker
                    <Badge variant="secondary">{{ meeting.status }}</Badge>
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-lg">{{ meeting.question }}</p>
                <p v-if="meeting.answer" class="text-muted-foreground">{{ meeting.answer }}</p>
            </CardContent>
        </Card>
    </div>
</template>
