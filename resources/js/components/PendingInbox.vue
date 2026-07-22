<script setup lang="ts">
    import { Link, useHttp } from '@inertiajs/vue3'
    import { onMounted, onUnmounted, ref } from 'vue'
    import InboxController from '@/actions/App/Http/Controllers/InboxController'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
    import { useInitials } from '@/composables/useInitials'
    import { show } from '@/routes/meetings'

    type InboxMeeting = {
        id: string
        question: string
        answer: string | null
        answered_at: string | null
        initiator: { name: string; pronouns: string | null; avatar_url: string | null }
    }

    const { getInitials } = useInitials()
    const http = useHttp<Record<string, never>, { meetings: InboxMeeting[] }>({})
    const meetings = ref<InboxMeeting[]>([])
    const loaded = ref(false)

    function refresh() {
        http.get(InboxController.url()).then((response) => {
            meetings.value = response.meetings
            loaded.value = true
        })
    }

    onMounted(() => {
        refresh()
        window.addEventListener('focus', refresh)
    })

    onUnmounted(() => window.removeEventListener('focus', refresh))

    defineExpose({ refresh })
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Awaiting your confirmation</CardTitle>
            <CardDescription>People who recorded an answer and need you to confirm your meeting.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-2">
            <p v-if="loaded && meetings.length === 0" class="text-sm text-muted-foreground" data-test="inbox-empty">
                Nothing waiting right now. Go scan someone!
            </p>

            <Link
                v-for="meeting in meetings"
                :key="meeting.id"
                :href="show(meeting.id)"
                class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                data-test="inbox-item"
            >
                <Avatar class="size-10">
                    <AvatarImage
                        v-if="meeting.initiator.avatar_url"
                        :src="meeting.initiator.avatar_url"
                        :alt="meeting.initiator.name"
                    />
                    <AvatarFallback>{{ getInitials(meeting.initiator.name) }}</AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ meeting.initiator.name }}</p>
                    <p class="truncate text-sm text-muted-foreground">{{ meeting.question }}</p>
                </div>
            </Link>
        </CardContent>
    </Card>
</template>
