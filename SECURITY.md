# Security Policy

MeetMe handles real attendee data — names, emails, and social handles — so we take security
seriously.

## Reporting a vulnerability

**Please do not open a public issue for security vulnerabilities.**

Instead, report it privately via GitHub's [security advisory](https://github.com/VheissuLabs/MeetMe/security/advisories/new)
form, or email **security@vheissulabs.com**. Include:

- A description of the vulnerability and its impact.
- Steps to reproduce (a proof of concept helps).
- Any suggested remediation.

We'll acknowledge your report as quickly as we can and keep you posted on the fix. Please give
us a reasonable window to address the issue before any public disclosure.

## Scope

MeetMe is deployed per-event, so the most relevant concerns are attendee data exposure
(emails are opt-in per §6.1 and enforced server-side), authentication, and the QR/deep-link
scan flow. Reports in these areas are especially appreciated.
