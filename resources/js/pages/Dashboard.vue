<script setup lang="ts">
    import { Head, Link, usePage } from '@inertiajs/vue3'
    import { computed } from 'vue'
    import PendingInbox from '@/components/PendingInbox.vue'
    import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
    import { Card, CardContent } from '@/components/ui/card'
    import { connections, dashboard } from '@/routes'
    import { edit as profileEdit } from '@/routes/profile'

    defineProps<{
        meetUrl: string
        qrSvg: string
        needsSocials: boolean
    }>()

    defineOptions({
        layout: {
            breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
        },
    })

    const page = usePage()
    const score = computed(() => page.props.score)
</script>

<template>
    <Head title="Dashboard" />

    <div class="mx-auto flex h-full w-full max-w-2xl flex-1 flex-col gap-6 p-4">
        <Alert v-if="needsSocials" data-test="socials-nudge">
            <AlertTitle>Add your socials</AlertTitle>
            <AlertDescription>
                People you meet can only follow you if you share your handles.
                <Link :href="profileEdit()" class="font-medium underline underline-offset-4">Add them now</Link>.
            </AlertDescription>
        </Alert>

        <Card>
            <CardContent class="flex flex-col items-center gap-4 p-6">
                <p class="text-sm text-muted-foreground">Let someone scan this to meet you</p>
                <!-- eslint-disable-next-line vue/no-v-html -- server-generated QR SVG, no user input -->
                <div class="w-full max-w-[320px] [&_svg]:h-auto [&_svg]:w-full" v-html="qrSvg" />
                <Link :href="connections()" prefetch class="text-center transition-opacity hover:opacity-70">
                    <p class="text-4xl font-bold" data-test="score">{{ score }}</p>
                    <p class="text-sm text-muted-foreground">{{ score === 1 ? 'connection' : 'connections' }}</p>
                </Link>
            </CardContent>
        </Card>

        <PendingInbox />
    </div>
</template>
