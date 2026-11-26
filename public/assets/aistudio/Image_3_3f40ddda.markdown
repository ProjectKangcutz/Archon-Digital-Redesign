---
name: ArchonDigital
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#44474d'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#74777e'
  outline-variant: '#c4c6cd'
  surface-tint: '#4d6079'
  primary: '#000e20'
  on-primary: '#ffffff'
  primary-container: '#10243b'
  on-primary-container: '#798ca7'
  inverse-primary: '#b5c8e6'
  secondary: '#006973'
  on-secondary: '#ffffff'
  secondary-container: '#4eebfe'
  on-secondary-container: '#006772'
  tertiary: '#050d20'
  on-tertiary: '#ffffff'
  tertiary-container: '#1b2336'
  on-tertiary-container: '#828aa2'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d3e4ff'
  primary-fixed-dim: '#b5c8e6'
  on-primary-fixed: '#071c33'
  on-primary-fixed-variant: '#354861'
  secondary-fixed: '#92f1ff'
  secondary-fixed-dim: '#32daed'
  on-secondary-fixed: '#001f23'
  on-secondary-fixed-variant: '#004f57'
  tertiary-fixed: '#dae2fd'
  tertiary-fixed-dim: '#bec6e0'
  on-tertiary-fixed: '#131b2e'
  on-tertiary-fixed-variant: '#3f465c'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-hero:
    fontFamily: Plus Jakarta Sans
    fontSize: 56px
    fontWeight: '800'
    lineHeight: 64px
    letterSpacing: -0.03em
  display-hero-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '800'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.025em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
    letterSpacing: -0.005em
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
    letterSpacing: 0.005em
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  margin: 1.5rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system establishes an authoritative, reliable, and contemporary presence for an Indonesian enterprise web development and digital technology agency. The visual language blends precision-driven corporate modernism with high-clarity minimalism: vast breathable whitespace, precise borders, balanced typographic contrast, and deliberate, high-impact cyan accents.

### Target Audience & Tone
- **Audience:** Indonesian and regional enterprise leaders, CTOs, startup founders, and government institutions seeking premium digital engineering and reliable product execution.
- **Tone:** Methodical, technically elite, transparent, and direct. The interface avoids speculative tech tropes, generic AI sparkles, and saturated mesh gradients in favor of enterprise clarity, structured informational pathways, and friction-free direct-to-consultation conversion channels.

## Colors

The palette relies on structural darkness anchored by Deep Navy (`#10243B`) paired with the luminous precision of Cyan (`#29D5E8`) as a targeted focal accent.

### Color Roles & Distribution
- **Deep Navy (`#10243B`):** Serves as the primary brand anchor. Used for primary interactive triggers, high-contrast display headlines, navigation headers, dark-themed hero blocks, and anchor footer sections.
- **Electric Cyan (`#29D5E8`):** Restrained high-conversion accent. Applied strictly to visual highlights, active step indicators, focus rings, call-to-action hover enhancements, and badge pips. Must never be used for long-form body text due to contrast ratios against light surfaces.
- **Slate Canvas & Surfaces:**
  - Base Page Canvas: `#F3F6F9` (cool neutral light)
  - Card & Container Surface: `#FFFFFF` (crisp white)
  - Structural Borders & Hairlines: `#E2E8F0`
- **Typographic Hierarchy:**
  - Primary Headlines / Heavy Labels: `#0F172A` (Ink Navy Slate)
  - Long-form Body Text: `#334155` (Slate 700, meeting WCAG AAA standard)
  - Secondary Metadata / Subtitles: `#64748B` (Muted Slate)
- **Functional & Conversion Signals:**
  - WhatsApp Direct Connect: `#25D366` (used exclusively for direct WA trigger accents alongside Deep Navy)

## Typography

The typographic hierarchy utilizes Plus Jakarta Sans for titles and headers to deliver clean geometry, paired with Inter for long-form narrative clarity, Indonesian localized phrasing, and technical copy.

### Architectural Rules
- **Localization & Tone:** Indonesian phrasing often uses longer word strings than English; line heights are calibrated to a generous minimum of 1.6x for body text to maintain optimal scannability across multi-syllable terminology.
- **Headline Rendering:** Plus Jakarta Sans should always use negative tracking (`-0.015em` to `-0.03em`) on scales above 28px to maintain compact, authoritative weight.
- **Numbers and Monospaced Metrics:** Project metrics, pricing quotations (IDR), and SLA percentages leverage tabular figures (`font-variant-numeric: tabular-nums`) within Inter for precise alignment.

## Layout & Spacing

The layout model is anchored on an 8pt structural rhythm combined with a 12-column responsive fluid grid bounded at a maximum container width of `1280px`.

### Grid & Breakpoints
- **Mobile (< 768px):** 4 columns, `1rem` (16px) gutters, `1.25rem` (20px) outer canvas margin. Elements stack vertically into single-column modules.
- **Tablet (768px – 1024px):** 8 columns, `1.5rem` (24px) gutters, `2rem` (32px) margins. Process tracks, features, and case studies align into 2-column matrices.
- **Desktop (> 1024px):** 12 columns, `1.5rem` (24px) gutters, `2.5rem` to `auto` margins within the `1280px` canvas. Side-by-side metric dashboards and sticky consultative navigation sidebars activate at this tier.

### Spacing Philosophy
- Section padding adheres to balanced vertical intervals: `4rem` (64px) on mobile scaling to `6rem`–`8rem` (96px–128px) on desktop to preserve elite technical composure and prevent visual crowding.

## Elevation & Depth

Visual hierarchy relies on structural hairline delineation, delicate ambient drop shadows, and subtle surface layering rather than aggressive skew or heavy drop shadows.

### Elevation Strategy
- **Layer 0 (Canvas):** Base background `#F3F6F9`. Completely matte.
- **Layer 1 (Cards & Structural Modules):** Pure white `#FFFFFF` surface bordered by a crisp 1px `#E2E8F0` hairline. Elevated by an ultra-diffused, cool-tinted shadow: `0 1px 3px 0 rgba(16, 36, 59, 0.04), 0 4px 12px 0 rgba(16, 36, 59, 0.03)`.
- **Layer 2 (Interactive Floating & Navigation):** Fixed header bars and active hover cards. Drop shadow: `0 10px 25px -5px rgba(16, 36, 59, 0.07), 0 8px 10px -6px rgba(16, 36, 59, 0.04)`.
- **Interactive Focus & Hover States:** When hovering over interactive surface cards, the border shifts from `#E2E8F0` to a refined Cyan outline (`#29D5E8`), accompanied by an upward translation of `-2px`.

## Shapes

The design system maintains an intentional geometric hybrid: crisp architectural rectangular structure for spatial surfaces and modules, balanced with fully rounded pill elements for dynamic actions, status tags, and contact links.

### Geometry Hierarchy
- **Base Cards, Modals & Field Containers:** Medium corner radius (`0.5rem` / 8px to `0.75rem` / 12px) to ensure technical structure and enterprise stability.
- **Buttons, Badge Chips & Input Indicators:** High-affinity pill geometry (`9999px`) or `rounded-xl` (`1rem` / 16px) to maximize tactile appeal, finger-friendly hit targets, and modern agency character.

## Components

### 1. Buttons
- **Primary Action (Deep Navy):** Background `#10243B`, text `#FFFFFF`, radius `9999px` (pill) or `0.75rem`, font `Inter` semibold 15px. On hover, background deepens to `#0B192A` with an ambient glow shadow tinted with cyan (`0 4px 14px rgba(41, 213, 232, 0.25)`).
- **Secondary / Accent Action (Cyan Glow):** Background `#29D5E8`, text `#10243B` (bold contrast), radius `9999px`. Used selectively for prime lead capture or primary conversion triggers.
- **WhatsApp Direct Connect Button:** Background `#10243B`, border 1px solid `#25D366`, text `#FFFFFF` with WhatsApp green indicator dot (`#25D366`). Pre-configured for direct link to `+6285159117451`. Hover shifts background to a refined translucent green tint (`rgba(37, 211, 102, 0.08)`).
- **Ghost / Tertiary Button:** Border 1px solid `#E2E8F0`, background transparent, text `#10243B`. Hover transitions border to `#10243B`.

### 2. Badge Chips & Micro-Labels
- Rounded pill structure (`9999px`), internal padding `0.25rem 0.75rem`.
- Background `#FFFFFF`, border 1px solid `#E2E8F0`, typography `label-sm` in `#10243B`. Often accompanied by a 6px circular dot in `#29D5E8` to denote active service availability or technology stack tags.

### 3. Crisp Cards & Process Cards
- **Base Style:** `#FFFFFF` background, 1px solid `#E2E8F0`, padding `1.75rem` (28px), radius `0.75rem`.
- **Hover State:** Border shifts dynamically to `#29D5E8` with a smooth 200ms ease-out transition.
- **Step-by-Step Process Variant:** Features a top-aligned sequence indicator badge (e.g., `01`, `02`, `03`) styled in `Plus Jakarta Sans` bold, slate-muted, transitioning to Electric Cyan text color when hovered.

### 4. Input Fields & Lead Capture Forms
- Background `#FFFFFF`, 1px solid `#E2E8F0`, radius `0.5rem`, padding `0.75rem 1rem`, text `#0F172A`.
- Active focus state applies a crisp 1.5px border in `#29D5E8` with a subtle outline ring (`box-shadow: 0 0 0 3px rgba(41, 213, 232, 0.15)`). Helper text and labels render in Inter 13px (`#64748B`).

### 5. FAQ Accordion
- Bordered modular list style: each item wrapped in a standalone white card or divided by a clean `#E2E8F0` border.
- Headers employ `headline-sm` in `#10243B` with a clean plus/minus or chevron icon in `#64748B` that rotates 180 degrees and turns `#29D5E8` upon expansion. Expanded panel reveals `#334155` body text with generous breathing space.

### 6. Header Navigation & Footer
- **Header:** Sticky top navigation with a frosted white background (`background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px)`), bottom 1px border in `#E2E8F0`. Features high-contrast agency branding, clean navigation links with cyan underline active indicators, and a persistent WhatsApp CTA.
- **Footer:** Immersive Deep Navy (`#10243B`) background. Text structured in `#E2E8F0` and `#64748B`. Includes Indonesian business registration disclosures, office locations, service taxonomy, and a dedicated contact column highlighting direct WhatsApp chat (`+6285159117451`).