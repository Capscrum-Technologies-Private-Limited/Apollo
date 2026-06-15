# Apollo Services — Multi-Page Platform

A premium, warm, conversion-focused multi-page website for Apollo Services (Albury NSW), built as self-contained Design Components.

## Pages (multi-page app — nav links between them)
| File | Page |
|---|---|
| `index.dc.html` | **Home** — intro animation, hero, trust bar, services preview, calculator, testimonials, FAQ, contact CTA |
| `services.dc.html` | **Services** — all 14 services with detail modals + why-choose |
| `about.dc.html` | **About** — mission, business hours, emergency line, animated stats, values |
| `portfolio.dc.html` | **Portfolio** — filterable works/case-study gallery |
| `career.dc.html` | **Career** — perks, open roles, application form |
| `contact.dc.html` | **Contact** — contact cards, message form, location |

Supporting files: `support.js` (DC runtime, required), `page-transition.js` (smooth between-page loader), `image-slot.js` (drag-drop image component), `DESIGN.md` (design system).

## How to run
Serve the folder with any static server and open `index.dc.html`:

```bash
npx serve .
```

The top navigation links between all six pages. Content is extracted from apollosupportservices.com.au (Albury NSW).

## Notes
- Images are themed stock photos (loremflickr) with accent-color fallbacks — replace the URLs with your own photos for production.
- Forms are front-end demos (show a success state); wire them to your backend/email service to go live.

© 2026 Apollo Services · Albury NSW
