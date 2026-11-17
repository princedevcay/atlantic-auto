# Accessibility: Atlantic Auto and Shipping Services

Baseline WCAG 2.2 AA (PG-016). Every rule below is testable.

## Keyboard
- Every link, button, form field, accordion and admin control is reachable and usable by keyboard in visual order.
- A "Skip to content" link is the first focusable element.
- The mobile menu traps focus while open and closes on Escape, returning focus to the menu button.
- Admin drawer closes on Escape and returns focus to the row that opened it.

## Focus
Visible focus ring on every interactive element: 2px `color.primary` outline with 2px offset; on navy bands use a white ring. Never `outline: none` without a replacement.

## Contrast
Text and UI colours are the validated pairs in `tokens.json` (text, muted and primary_contrast all at least 4.5:1). White text on the navy hero and footer. Text over client hero images always sits on a navy overlay strong enough for 4.5:1; check every supplied image.

## Forms
- Every field has a visible label tied with `for`/`id`; hints and errors linked with `aria-describedby`.
- Required fields marked in text, not colour alone.
- On submit failure, an error summary at the top receives focus and links to each field.
- Phone fields accept spaces and a leading 0 or +233.
- Success panels are announced with `role="status"`.

## Content
- One `h1` per page, logical heading order.
- Client images get alt text describing the scene; decorative ones use empty alt.
- Timeline stages are an ordered list, with the current stage marked by `aria-current="step"`.
- Language set to `en` (English only, AS-04).

## Motion
Fades only, all disabled under `prefers-reduced-motion: reduce`.

## Verify before release
- Keyboard-only pass of home, both forms, track lookup, tracking page and admin inbox.
- Screen reader pass (VoiceOver on iPhone and macOS) of both forms and a tracking page.
- Contrast check of hero text over every supplied image.
- 200% zoom and 320px width with no horizontal scroll.
- Select boxes show the custom chevron in Safari and Chrome on mobile.
