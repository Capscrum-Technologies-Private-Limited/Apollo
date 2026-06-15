# Apollo Services — Design System

**A warm, premium, trust-forward design language for a commercial cleaning & support-services platform.**
Version 1.0 · Albury NSW · Built for the Apollo Cleaning Platform

---

## 1. Brand Foundations

### 1.1 Positioning
Apollo Services is a locally-owned cleaning and personal-support company serving the Albury–Wodonga region. The brand voice is **warm, dependable, and human** — the opposite of the cold, corporate "data-platform" look. Where competitors play cool with gray grids, Apollo leans into a **cream canvas, saturated single-color cards, rounded display type, and playful motion**.

The aesthetic is inspired by the warmth of modern consumer-SaaS brands (Clay, Notion, Linear-grade polish) but applied to a service business: it should feel **trustworthy, friendly, premium, and effortless to book**.

### 1.2 Design Principles
1. **Warm, never clinical.** The cream canvas (`#fffaf0`) is the floor of every screen. No cool grays.
2. **Color is the voltage.** Saturated single-color cards carry the energy; the layout stays calm around them.
3. **Rounded, friendly type.** A rounded display face at medium weight gives warmth without shouting.
4. **Motion with personality.** Springy, staggered entrances and a signature intro reveal — playful, not flashy.
5. **One clear action per surface.** Every section funnels toward *Book service* or *Request quote*.
6. **Generous radius + whitespace.** Big rounded corners and 96px section rhythm create premium calm.

---

## 2. Color System

### 2.1 Brand & Accent
| Token | Hex | Use |
|---|---|---|
| `--accent-pink` | `#ff4d8b` | Hot-pink feature/service card; primary energy accent |
| `--accent-teal` | `#1a3a3a` | Deep teal card; featured pricing tier, estimate panel, dark surfaces |
| `--accent-lavender` | `#b8a4ed` | Soft lavender card; AI assistant band |
| `--accent-peach` | `#ffb084` | Warm peach card and illustration shapes |
| `--accent-ochre` | `#e8b94a` | Mustard/ochre card and illustration accents |
| `--accent-mint` | `#a4d4c5` | Mint accent on badges and illustration shapes |
| `--accent-coral` | `#ff6b5a` | Coral accent for emergency / priority highlights |

> **Card text rule:** On **pink, teal, coral** cards → white text. On **lavender, peach, ochre, mint, cream** cards → near-black ink text (`#0a0a0a`). These lighter saturations carry enough contrast for dark text.

### 2.2 Surface
| Token | Hex | Use |
|---|---|---|
| `--canvas` | `#fffaf0` | Default page background (cream-tinted white) |
| `--surface-soft` | `#faf5e8` | Footer, CTA bands, hero illustration card |
| `--surface-card` | `#f5f0e0` | Cream cards, testimonial card, catalogue pills |
| `--surface-strong` | `#ebe6d6` | Emphasised bands (Why Apollo) |
| `--hairline` | `#ece6d6` | 1px card/input borders |
| `--hairline-strong` | `#e0d9c6` | Slightly stronger border on inputs/buttons |

### 2.3 Text
| Token | Hex | Use |
|---|---|---|
| `--ink` | `#0a0a0a` | Headlines and primary text |
| `--body` | `#3a3a3a` | Default running text |
| `--muted` | `#6a6a6a` | Sub-headings, captions, footer body |
| `--muted-soft` | `#9a9a9a` | Fine print, placeholders, fine labels |
| `--on-dark` | `#ffffff` | Text on primary buttons + dark cards |

### 2.4 Semantic
| Token | Hex | Use |
|---|---|---|
| `--success` | `#22c55e` | Online/confirmed status dots |
| `--warning` | `#f59e0b` | Warning callouts |
| `--error` | `#ef4444` | Validation errors |

---

## 3. Typography

### 3.1 Families
- **Display / Headlines:** `Fredoka` (rounded, warm) — weights 400/500/600, used at **500** for nearly all display sizes with negative letter-spacing.
- **Body / UI:** `Hanken Grotesk` — weights 400/500/600/700/800.
- **Fallback stack:** `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`.

> Mixing the two is functional, not decorative: **Fredoka for Fredoka moments** (headlines, big numbers, prices), **Hanken Grotesk for everything else** (running text, UI, buttons, labels).

### 3.2 Type Scale
| Token | Family | Size | Weight | Letter-spacing | Use |
|---|---|---|---|---|---|
| `display-xl` | Fredoka | 60px | 500 | -2px | Hero H1 |
| `display-lg` | Fredoka | 46–48px | 500 | -1.5px | Section heads |
| `display-md` | Fredoka | 40px | 500 | -1px | Sub-section heads |
| `display-sm` | Fredoka | 28–32px | 500 | -1px | Modal titles, CTA heads |
| `title-lg` | Fredoka | 21–24px | 500 | -0.5px | Card titles, pricing names |
| `title-md` | Hanken | 17–18px | 600 | 0 | Small card titles, FAQ questions |
| `stat` | Fredoka | 52px | 500 | -2px | Animated counters, prices |
| `body-md` | Hanken | 15–16px | 400 | 0 | Default running text |
| `body-sm` | Hanken | 13–14px | 400 | 0 | Footer, card descriptions, fine print |
| `label-caps` | Hanken | 12px | 600 | 1.5px (uppercase) | Section eyebrows, field labels |
| `button` | Hanken | 14px | 600 | 0 | Button labels |
| `nav-link` | Hanken | 14px | 500 | 0 | Top-nav items |

---

## 4. Spacing & Layout

### 4.1 Spacing Scale (base 4px)
`4 · 8 · 12 · 16 · 24 · 32 · 48 · 80 · 96`

- **Section rhythm:** 80–96px vertical padding between major bands.
- **Card padding:** 32px (feature/pricing), 24px (standard), 18px (compact 4-up service cards).
- **Page gutters:** 28px horizontal.

### 4.2 Grid & Container
- **Max content width:** 1280px, centered.
- **Hero:** 7/5 split — copy left, illustration card right.
- **Service cards:** 4-up desktop (compact), collapsing to 2-up tablet, 1-up mobile.
- **Feature/why/process/pricing:** 3–4-up desktop.

---

## 5. Shape & Elevation

### 5.1 Border Radius
| Token | Value | Use |
|---|---|---|
| `radius-xs` | 6px | Checkboxes, small marks |
| `radius-sm` | 10–12px | Buttons, inputs |
| `radius-md` | 14–16px | Content cards, service cards, modals |
| `radius-lg` | 20–24px | Feature cards, CTA bands, hero illustration |
| `radius-pill` | 9999px | Chips, badges, status dots, avatars |

### 5.2 Elevation
The system avoids heavy shadows; depth comes from **color contrast** between cream canvas and saturated cards.
- **Flat:** body sections, nav, hero — no shadow.
- **Hairline:** 1px `--hairline` border on cards/inputs.
- **Saturated card:** brand-color fill, no shadow.
- **Hover-elevated (interactive):** soft drop shadow tinted with the card's own accent color (e.g. `0 26px 54px rgba(accent, 0.30)`) + matching border tint, applied on hover only.

---

## 6. Components

### 6.1 Buttons
- **Primary:** `--ink` background, white text, 14px/600, height 42–48px, radius 12px. Hover → `#262626`.
- **Secondary:** cream (`--canvas`) background, ink text, 1px `--hairline-strong` border. Hover → `--surface-soft`.
- **On-color:** white background, ink text — used over saturated cards.
- **Text link:** inline, no background — "Sign in", "Request quote".

### 6.2 Service Card (compact, 4-up)
Vertical card: 4px accent top-bar → 130px cover image → 18px body (Fredoka 17px title, clipped 13px description, 3 accent-checked bullets, "Learn more →" button). Entire card is clickable. **Hover:** lift 10px, image zoom 1.09, accent-tinted glow + border, arrow nudge. **Entrance:** scroll-triggered springy tilt-in (`back.out`, alternating ±rotation, staggered).

### 6.3 Service Detail Modal
Opened from any card. Accent-colored banner (title + close), then long description, a 2-column key-features grid, and one or more "includes / process" sub-sections, closing with a *Book this service →* CTA. Dismiss on backdrop click or ✕.

### 6.4 Live Estimator
Two-panel: left config (service chips, area slider, frequency segmented control, add-on toggles, priority toggle) → right sticky **teal** summary panel (per-visit + monthly price, live). Pricing formula:

```
visit = area_m2 × (service_rate + Σ addon_rates)
if priority: visit ×= 1.25
monthly = visit × visits_per_month[frequency]
```

### 6.5 AI Assistant
Lavender band holding a cream chat card: header with status dot, message stream (ink user bubbles / cream assistant bubbles), typing indicator, suggested-prompt chips, and a faux input. Scripted Q&A grounded in real service facts.

### 6.6 Testimonials Carousel
Cream card, 100%-width slides translated on a track, auto-advancing every 6s with dot indicators + prev/next controls.

### 6.7 FAQ Accordion
Cream cards; click toggles a single open item; the "+" icon rotates 45° to "×".

### 6.8 Trust Bar
Status chips (rating, clients, staff, eco) + an infinite client-logo marquee with edge fades.

### 6.9 Contact / Booking
Two-column band: contact methods (phone, email, address, hours) + a quick-booking form (name, phone, service select, date, submit).

### 6.10 Footer
**Cream** (never dark) — 4-column links over the signature horizon: large overlapping rounded "mountain" shapes in lavender/peach/ochre that parallax on scroll.

---

## 7. Motion

| Moment | Behavior |
|---|---|
| **Intro reveal** | On every load: colored pinwheel pills + "Apollo" wordmark assemble (`back.out`), the cream overlay lifts, then 5 brand-color panels sweep up in sequence (`power4.inOut`) to unveil the hero, which staggers in. |
| **Section reveals** | `[data-reveal]` fade + rise on scroll (`power3.out`, start `top 88%`). |
| **Grid staggers** | `[data-stagger]` children rise + scale in with stagger. |
| **Service cards** | Springy tilt-in entrance; accent-glow lift + image zoom on hover. |
| **Parallax** | `[data-float]` shapes drift against scroll (scrubbed). |
| **Counters** | Stats count up (cubic ease-out) when scrolled into view. |
| **Marquee** | Continuous CSS keyframe logo scroll. |

**Engine:** GSAP 3 + ScrollTrigger. Safe fallback: if the library is slow/unavailable, overlays auto-hide and content shows un-animated.

---

## 8. Voice & Content

- **Tone:** warm, plain-spoken, confident. "Commercial cleaning, finally handled."
- **No emoji** in UI chrome (Clay-style restraint).
- **Real proof:** 3,000+ clients · 35+ trained staff · 4.9★ · eco-friendly · Albury NSW.
- **Contact:** 0421 602 524 · Apollo.au@outlook.com · Dean St, Albury NSW 2640 · Mon–Sat 6am–10:30pm, Sun 7:30am–7pm.

---

## 9. Service Catalogue
Office · Residential · Carpet · Commercial · Window · Building · Self Support · Cleaning & Lawn Mowing · Outings (Day & Night) · Transport · End of Lease · Personal Care · Pressure Cleaning · Facility Management.

Each has: short card description, 3 feature bullets, long description, 6-item key-feature list, and process/includes sub-sections (see Service Detail Modal).

---

## 10. Responsive Behavior
| Breakpoint | Key changes |
|---|---|
| Mobile < 768px | Hamburger nav; hero H1 ~36px; hero stacks; service cards 1-up; estimator stacks |
| Tablet 768–1024px | Tightened nav; service cards 2-up; feature grids 2-up |
| Desktop 1024–1440px | Full nav; service cards 4-up; feature grids 3-up |
| Wide > 1440px | Same as desktop, content capped at 1280px |

**Touch targets:** buttons & inputs ≥ 44px tall.

---

## 11. Implementation Notes
- Built as a **Design Component** (`Apollo Cleaning - Warm.dc.html`): an inline-styled template + a `Component extends DCLogic` logic class, rendered by `support.js`.
- **Styling is inline** (no external stylesheet); only `@font-face`/`@keyframes`/resets live in a `<helmet><style>` block.
- **Images:** service/case cards use themed stock photos with an accent-color fallback. Each service card also supports a drag-and-drop image slot (`image-slot.js`) so real photos can be dropped in and persisted.
- **Fonts:** loaded from Google Fonts (Fredoka, Hanken Grotesk).

---

*© 2026 Apollo Services · Albury NSW. Design system documentation for internal and developer handoff.*
