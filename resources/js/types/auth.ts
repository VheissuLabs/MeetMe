export type User = {
    id: number
    name: string
    email: string
    qr_token: string
    avatar?: string
    email_verified_at: string | null
    two_factor_enabled?: boolean
    has_password: boolean
    pronouns: string | null
    headline: string | null
    job_title: string | null
    company: string | null
    x_username: string | null
    bluesky_handle: string | null
    linkedin_username: string | null
    avatar_url: string | null
    avatar_source: 'github' | 'x' | 'gravatar'
    email_visible: boolean
    answers_visible: boolean
    created_at: string
    updated_at: string
    [key: string]: unknown
}

export type Auth = {
    user: User
}

/* @chisel-passkeys */
export type Passkey = {
    id: number
    name: string
    authenticator: string | null
    created_at_diff: string
    last_used_at_diff: string | null
}
/* @end-chisel-passkeys */

export type TwoFactorConfigContent = {
    title: string
    description: string
    buttonText: string
}
