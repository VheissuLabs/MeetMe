<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import PendingInbox from '@/components/PendingInbox.vue'
    import { dashboard } from '@/routes'

    defineOptions({
        layout: {
            breadcrumbs: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                },
            ],
        },
    })
</script>

<template>
    <Head title="Dashboard" />

    <div class="mx-auto flex h-full w-full max-w-2xl flex-1 flex-col gap-4 p-4">
        <PendingInbox />
    </div>
</template>
