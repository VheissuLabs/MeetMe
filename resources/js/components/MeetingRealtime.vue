<script setup lang="ts">
    import { router, usePage } from '@inertiajs/vue3'
    import { useEcho } from '@laravel/echo-vue'
    import { computed } from 'vue'
    import { toast } from 'vue-sonner'
    import { show } from '@/routes/meetings'

    const page = usePage()
    const channel = computed(() => `App.Models.User.${page.props.auth.user.id}`)

    // Keep the layout's shared score + pending count fresh without a full
    // page load, so the dashboard updates the moment a meeting changes.
    function refreshStats() {
        router.reload({ only: ['score', 'pendingCount'] })
    }

    useEcho<{ meeting_id: string; initiator_name: string }>(
        channel.value,
        '.MeetingAwaitingConfirmation',
        (payload) => {
            refreshStats()
            toast.info(`${payload.initiator_name} recorded your answer`, {
                description: 'Tap to confirm or reject your meeting.',
                action: { label: 'Review', onClick: () => router.visit(show(payload.meeting_id).url) },
            })
        },
    )

    useEcho<{ meeting_id: string; status: string; recipient_name: string }>(
        channel.value,
        '.MeetingResolved',
        (payload) => {
            refreshStats()

            if (payload.status === 'confirmed') {
                toast.success(`${payload.recipient_name} confirmed — you both scored!`)
            } else {
                toast.info(`${payload.recipient_name} didn't confirm your meeting.`)
            }
        },
    )
</script>

<template>
    <slot />
</template>
