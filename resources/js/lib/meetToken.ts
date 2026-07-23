const ULID = '[0-9A-HJKMNP-TV-Z]{26}'

/**
 * Pull a MeetMe qr_token out of a scanned QR value. Accepts either a
 * bare ULID or a `/meet/{token}` deep-link URL. Returns null for
 * anything that isn't a MeetMe code, so the scanner can react gracefully.
 */
export function extractQrToken(decoded: string): string | null {
    const trimmed = decoded.trim()

    if (new RegExp(`^${ULID}$`, 'i').test(trimmed)) {
        return trimmed.toUpperCase()
    }

    const match = trimmed.match(new RegExp(`/meet/(${ULID})/?$`, 'i'))

    return match ? match[1].toUpperCase() : null
}
