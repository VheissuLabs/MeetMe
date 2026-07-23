<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3'
    import { ref } from 'vue'
    import { QrcodeStream } from 'vue-qrcode-reader'
    import Heading from '@/components/Heading.vue'
    import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
    import { extractQrToken } from '@/lib/meetToken'
    import { store } from '@/routes/meetings'

    defineOptions({
        layout: {
            breadcrumbs: [{ title: 'Scan', href: '/scan' }],
        },
    })

    type DetectedCode = { rawValue: string }

    const error = ref<string | null>(null)
    const paused = ref(false)

    function onDetect(codes: DetectedCode[]) {
        const token = codes.map((code) => extractQrToken(code.rawValue)).find((value) => value !== null)

        if (!token) {
            error.value = "That doesn't look like a MeetMe code. Point your camera at someone's MeetMe QR."

            return
        }

        error.value = null
        paused.value = true

        router.post(
            store().url,
            { qr_token: token },
            {
                onError: (errors) => {
                    error.value = Object.values(errors)[0] ?? 'Something went wrong. Try again.'
                    paused.value = false
                },
            },
        )
    }

    function onError(err: Error) {
        error.value =
            {
                NotAllowedError: 'Camera access was denied. Enable it in your browser settings to scan.',
                NotFoundError: 'No camera found on this device.',
                NotReadableError: 'Your camera is already in use by another app.',
                InsecureContextError: 'Camera access requires a secure (https) connection.',
            }[err.name] ?? `Could not start the camera: ${err.message}`
    }
</script>

<template>
    <Head title="Scan" />

    <div class="mx-auto flex w-full max-w-md flex-col gap-4 p-4">
        <Heading title="Scan a code" description="Point your camera at someone's MeetMe QR to meet them." />

        <Alert v-if="error" variant="destructive" data-test="scan-error">
            <AlertTitle>Heads up</AlertTitle>
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <div class="aspect-square overflow-hidden rounded-xl border">
            <QrcodeStream :paused="paused" @detect="onDetect" @error="onError" />
        </div>
    </div>
</template>
