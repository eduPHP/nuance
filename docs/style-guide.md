# Humanizer Style Guide (Landing + Marketing)

This guide is the source of truth for future marketing pages and landing sections.
It is based on:

- `resources/css/app.css` semantic color tokens
- `resources/views/welcome.blade.php` component patterns

## 1. Visual Direction

- Tone: warm, editorial, human-first
- Mood: calm background + high-clarity typography + orange action accents
- Rule: prioritize semantic tokens (`bg-background`, `text-foreground`, `bg-card`, `text-muted-foreground`, `bg-primary`) over raw color utilities

## 2. Color System

Use semantic tokens only. Do not hardcode random hex colors in markup.

### Core Light Tokens

- `--color-background`: `#f6f0eb`
- `--color-foreground`: `#2f2523`
- `--color-card`: `#faf7f4`
- `--color-primary`: `#e8842c`
- `--color-secondary`: `#ede4dd`
- `--color-muted`: `#efe7e1`
- `--color-muted-foreground`: `#7d6f68`
- `--color-accent`: `#f6e9dd`
- `--color-accent-foreground`: `#d36f1f`
- `--color-border`: `#e1d6cf`
- `--color-destructive`: `#dc2626`

### Usage Rules

- App/page background: `bg-background`
- Main text: `text-foreground`
- Secondary text: `text-muted-foreground`
- Panels/cards: `bg-card border border-border`
- Primary CTA: `bg-primary text-primary-foreground`
- Soft badges or icon containers: `bg-accent text-accent-foreground`

## 3. Typography

- Font family: `Instrument Sans` from `resources/css/app.css`
- Hero H1: `text-4xl md:text-6xl lg:text-7xl font-bold tracking-tight`
- Section heading: `text-3xl md:text-4xl font-bold tracking-tight`
- Body text: `text-lg leading-relaxed text-muted-foreground`
- Small labels/meta: `text-sm` or `text-xs` with muted foreground

## 4. Layout + Spacing

- Main max width: `max-w-6xl`
- Horizontal padding: `px-6`
- Section vertical rhythm: `py-20 md:py-28`
- Hero vertical rhythm: `py-20 md:py-32`
- Card radius: `rounded-2xl` (use `rounded-3xl` for major CTA blocks)
- Global border style: `border border-border/60` or `border-border/50`

## 5. Component Patterns

### Navbar

- Wrapper: `sticky top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-xl`
- Brand mark: rounded square with primary fill
- Nav links: `text-sm font-medium text-muted-foreground hover:text-foreground`
- Primary action: rounded full orange button

### Hero Badge

- `inline-flex rounded-full border border-primary/20 bg-accent px-4 py-2 text-sm font-medium text-accent-foreground`

### Buttons

- Primary: `rounded-full bg-primary px-8 py-3 text-base font-semibold text-primary-foreground hover:bg-primary/90`
- Secondary: `rounded-full border border-border bg-card px-8 py-3 text-base font-semibold text-foreground hover:bg-secondary`

### Feature Cards

- `rounded-2xl border border-border/60 bg-card p-7`
- Hover: `hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5`
- Icon block: `h-12 w-12 rounded-xl bg-accent`

### Analysis Preview Card

- Outer: `rounded-2xl border border-border bg-card shadow-2xl shadow-primary/5`
- Toolbar dots: muted/destructive/primary tints
- Highlight text:
  - High-risk: `bg-destructive/10 text-destructive`
  - Moderate-risk: `bg-primary/10 text-primary`

### Pricing Cards

- Default card: `border-border/60 bg-card`
- Featured card: `border-primary bg-card shadow-xl shadow-primary/10`
- Featured badge: small pill above card in primary color

### CTA Block

- Container: `rounded-3xl bg-primary`
- Text: `text-primary-foreground`
- Action button: white/light card surface with dark text

## 6. Decorative Elements

- Use low-opacity primary circles for atmosphere (`bg-primary/5`, `bg-primary/10`)
- Avoid dark decorative blobs in light mode
- Decorations should never reduce text contrast

## 7. Interaction + Accessibility

- Keep hover transitions subtle and fast
- Maintain clear focus styles via ring tokens (`ring-ring`)
- Preserve contrast on all text/button states
- Use semantic tags (`header`, `main`, `section`, `footer`)

## 8. Responsive Rules

- Mobile first
- Collapse desktop nav into menu on `md` and below
- Keep hero actions stacked on small screens (`flex-col sm:flex-row`)
- Use `md:grid-cols-*` and `lg:grid-cols-*` for section grids

## 9. Implementation Checklist

Before shipping a new page:

1. Use semantic color classes instead of random hex classes.
2. Match section rhythm (`py-20 md:py-28`) unless there is a clear reason not to.
3. Reuse existing button/card/badge patterns from `welcome.blade.php`.
4. Confirm desktop + mobile layouts.
5. Run `vendor/bin/pint --dirty --format agent`.

## 10. Component Extraction Findings

`resources/views/welcome.blade.php` has been reduced to orchestration and data arrays.
Most markup now lives in anonymous Blade components under `resources/views/components/marketing`.

### Extracted Components

- `marketing.layout`: full document shell (head/meta/fonts/Vite + body wrapper)
- `marketing.header`: sticky navbar container and responsive composition
- `marketing.brand`: reusable Nuance logo/wordmark (`default` and `small` sizes)
- `marketing.nav-links`: reusable navigation link renderer
- `marketing.header-actions`: shared auth-aware header CTAs (desktop + mobile variants)
- `marketing.mobile-menu`: mobile `<details>` menu wrapper
- `marketing.hero-section`: hero block and trust metrics
- `marketing.analysis-preview`: demo detection UI card in hero
- `marketing.section-heading`: reusable section eyebrow/title/description pattern
- `marketing.features-section`: features section shell
- `marketing.feature-card`: single feature card
- `marketing.how-it-works-section`: process section shell
- `marketing.step-card`: single step card with optional connector
- `marketing.pricing-section`: pricing section shell
- `marketing.pricing-card`: single pricing plan card with auth-aware CTA
- `marketing.cta-section`: bottom call-to-action block
- `marketing.footer`: footer shell + account/product groups
- `marketing.footer-link-group`: reusable footer link list group

### Findings

- Repetition was highest in navigation links, section headings, and card structures.
- Auth-driven CTA branching is now centralized in `header-actions`, `pricing-card`, and `footer`.
- Section composition is now explicit in `welcome.blade.php`, making future section reorder/removal low-risk.
- Styling patterns documented above now map directly to reusable component boundaries.
