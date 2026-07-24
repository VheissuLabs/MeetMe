<script setup lang="ts">
    import { BlueskyIcon, GithubIcon, XIcon } from '@/components/icons'
    import LastUsedBadge from '@/components/LastUsedBadge.vue'
    import { Button } from '@/components/ui/button'
    import { redirect } from '@/routes/social'
</script>

<template>
    <div class="grid gap-4">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t" />
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-background px-2 text-muted-foreground">or continue with</span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="relative">
                <Button variant="outline" as-child class="w-full">
                    <a :href="redirect.url('github')" data-test="github-login-button">
                        <GithubIcon class="size-4" />
                        GitHub
                    </a>
                </Button>
                <LastUsedBadge method="github" />
            </div>

            <div class="relative">
                <Button variant="outline" as-child class="w-full">
                    <a :href="redirect.url('x')" data-test="x-login-button">
                        <XIcon class="size-4" />
                        X
                    </a>
                </Button>
                <LastUsedBadge method="x" />
            </div>

            <div class="relative">
                <Button variant="outline" as-child class="w-full">
                    <a :href="redirect.url('bluesky')" data-test="bluesky-login-button">
                        <BlueskyIcon class="size-4" />
                        Bluesky
                    </a>
                </Button>
                <LastUsedBadge method="bluesky" />
            </div>
        </div>
    </div>
</template>
