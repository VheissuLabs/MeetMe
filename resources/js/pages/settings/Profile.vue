<script setup lang="ts">
    import { Form, Head, usePage } from '@inertiajs/vue3'
    import { computed } from 'vue'
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController'
    import DeleteUser from '@/components/DeleteUser.vue'
    import Heading from '@/components/Heading.vue'
    import { BlueskyIcon, XIcon } from '@/components/icons'
    import InputError from '@/components/InputError.vue'
    import { Button } from '@/components/ui/button'
    import { Checkbox } from '@/components/ui/checkbox'
    import { Input } from '@/components/ui/input'
    import { Label } from '@/components/ui/label'
    import { edit } from '@/routes/profile'

    defineOptions({
        layout: {
            breadcrumbs: [
                {
                    title: 'Profile settings',
                    href: edit(),
                },
            ],
        },
    })

    const props = defineProps<{
        avatarOptions: Partial<Record<'github' | 'x' | 'gravatar', string>>
    }>()

    const page = usePage()
    const user = computed(() => page.props.auth.user)

    const avatarSourceLabels: Record<string, string> = {
        github: 'GitHub',
        x: 'X',
        gravatar: 'Gravatar',
    }

    const avatarChoices = computed(() =>
        Object.entries(props.avatarOptions).map(([source, url]) => ({
            source,
            url,
            label: avatarSourceLabels[source] ?? source,
        })),
    )
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading variant="small" title="Profile" description="Update your name and email address" />

        <Form v-bind="ProfileController.update.form()" class="space-y-6" v-slot="{ errors, processing, validate }">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="pronouns">Pronouns</Label>
                <Input
                    id="pronouns"
                    class="mt-1 block w-full"
                    name="pronouns"
                    :default-value="user.pronouns ?? ''"
                    maxlength="30"
                    placeholder="e.g. she/her, he/they"
                    @change="validate('pronouns')"
                />
                <p class="text-sm text-muted-foreground">
                    Optional — shown next to your name wherever your profile appears.
                </p>
                <InputError class="mt-2" :message="errors.pronouns" />
            </div>

            <div class="grid gap-2">
                <Label for="x_username" class="flex items-center gap-1.5"><XIcon class="size-3.5" /> X username</Label>
                <Input
                    id="x_username"
                    class="mt-1 block w-full"
                    name="x_username"
                    :default-value="user.x_username ?? ''"
                    placeholder="username"
                    @change="validate('x_username')"
                />
                <InputError class="mt-2" :message="errors.x_username" />
            </div>

            <div class="grid gap-2">
                <Label for="bluesky_handle" class="flex items-center gap-1.5"
                    ><BlueskyIcon class="size-3.5" /> Bluesky handle</Label
                >
                <Input
                    id="bluesky_handle"
                    class="mt-1 block w-full"
                    name="bluesky_handle"
                    :default-value="user.bluesky_handle ?? ''"
                    placeholder="name.bsky.social"
                    @change="validate('bluesky_handle')"
                />
                <InputError class="mt-2" :message="errors.bluesky_handle" />
            </div>

            <div class="grid gap-2">
                <Label>Profile photo</Label>
                <div class="mt-1 grid grid-cols-3 gap-2">
                    <label
                        v-for="choice in avatarChoices"
                        :key="choice.source"
                        class="flex cursor-pointer flex-col items-center gap-2 rounded-lg border p-3 text-sm transition-colors has-checked:border-primary has-checked:bg-accent"
                    >
                        <input
                            type="radio"
                            name="avatar_source"
                            :value="choice.source"
                            :checked="user.avatar_source === choice.source"
                            class="sr-only"
                        />
                        <img
                            :src="choice.url"
                            :alt="`${choice.label} avatar preview`"
                            class="size-14 rounded-full object-cover"
                            loading="lazy"
                        />
                        <span>{{ choice.label }}</span>
                    </label>
                </div>
                <p class="text-sm text-muted-foreground">Your photo comes from the platform you pick — no uploads.</p>
                <InputError class="mt-2" :message="errors.avatar_source" />
            </div>

            <div class="grid gap-2">
                <Label for="email_visible" class="flex items-center space-x-3">
                    <Checkbox id="email_visible" name="email_visible" value="1" :default-checked="user.email_visible" />
                    <span>Share my email with confirmed connections</span>
                </Label>
                <p class="text-sm text-muted-foreground">
                    Off by default. When on, people you've met can see your email on their connections page and recap
                    email.
                </p>
                <InputError class="mt-2" :message="errors.email_visible" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button">Save</Button>
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
