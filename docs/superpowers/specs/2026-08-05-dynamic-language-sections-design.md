# Dynamic Language Sections — Design

**Date:** 2026-08-05
**Status:** Approved
**Scope:** Task 1 of 4 from the language-page/admin audit. Tasks 2–4 (admin↔frontend
gaps, mobile carousels, admin usability) are documented at the end and handled
separately.

## Problem

The admin cannot add a new language section (Arabic, French, …) to the Languages
page. Three blockers, all hardcoded in the frontend:

1. `src/components/LanguagesSection.tsx:24` — `GROUPS` is a hardcoded array. Tab
   label, short label, section heading, subtitle and icon for English/German/Korean
   live in code. A new language cannot produce a new tab.
2. `src/components/LanguagesSection.tsx:157` — `normalizeGroup()` is a hardcoded
   if/else whose final `return "korean"` is a silent catch-all. A program with
   `language_code = "arabic"` would render under the **Korean** tab.
3. `russellsinternational-api/app/Filament/Resources/LanguageProgramResource.php:32`
   — `language_code` is a fixed `Select` with four options. No way to add one.

Two further defects found while investigating:

4. `DEFAULT_PROGRAMS` (8 demo cards) renders whenever a tab has zero programs.
   Those cards do not exist in the admin, so the owner can neither edit nor remove
   them. Currently dormant because each tab has exactly one program — it triggers
   the moment the owner deletes a tab's last program.
5. `language_code = 'ielts'` is offered in the admin as a separate section but the
   frontend silently merges it into English. A lorem-ipsum test record
   ("Acton Kim", badge "Officiis enim labore") is live on the client's site under it.

## Requirements

- The admin can create, reorder, hide and delete language sections without a developer.
- Per section the admin controls: tab label, mobile short label, heading, subtitle,
  icon, colour, order, visibility.
- Icon and colour are chosen from curated pickers. The admin never sees or types a
  Tailwind class.
- The tab strip stays usable on mobile with 6+ sections.
- Everything visible on the Languages page is editable in the admin. No ghost content.
- Existing admin functionality must not regress.

## Approach

A new `language_sections` table with `language_programs` belonging to it.

Rejected alternatives:

- **Reuse `page_sections`.** No concept of icon/colour/order per tab; the admin
  would effectively edit JSON. Fails the non-technical-owner requirement.
- **Derive sections from distinct `language_code` values.** No control over
  heading/subtitle/icon/colour/order; typos create duplicate tabs ("arabic" vs
  "Arabic"); ordering undefined. Fails the stated requirements.

## Data model

Field names follow the conventions already used by `services`, `courses`,
`why_choose_us_items` and `language_programs`: a Lucide icon name in `icon_name` and
a Tailwind class pair in `color_class`. Introducing `icon_key` / `color_key` here
would leave the codebase with two conventions for the same idea.

```
language_sections                     language_programs
─────────────────                     ─────────────────
id                                    id
slug          unique, from label      language_section_id   FK → language_sections
label         "Arabic Tests"          title
short_label   "Arabic"                duration
heading       "Arabic Language …"     badge
subtitle      "ALPT and practical …"  description
icon_name     "Globe"                 benefits
color_class   "bg-amber-50 …"         color_class   (unchanged)
sort_order    4                       image
is_active     true                    sort_order
timestamps                            is_active
```

`color_class` keeps storing a Tailwind class pair, but the admin never types one:
the picker's option *values* are the class pairs and its labels are colour names.
`language_programs.color_class` is therefore **not** renamed — only its admin input
changes. Renaming it on one of the four tables that share the column would create
exactly the kind of inconsistency this design is trying to remove.

`short_label` falls back to `label` in a model accessor, so the API always returns a
usable value and the frontend needs no conditional.

`language_programs.language_code` is dropped after backfill. Keeping it would leave
two sources of truth for the same fact, which is the root of defect 2 above.

`language_section_id` uses `nullOnDelete` at the database level, but section
deletion is blocked in the admin while programs still reference it (see Admin).
The database rule is the backstop; the admin rule is the user-facing guardrail.

## Migration

One deployment, each step idempotent so a re-run cannot duplicate data.

1. Create `language_sections`. Seed English, German and Korean using exactly the
   label / heading / subtitle / icon / colour currently hardcoded in `GROUPS`, so the
   live page does not change by a pixel. `icon_name` takes the Lucide names already
   referenced there (`Languages`, `BookOpenText`, `MessageCircle`).
2. Add `language_section_id` to `language_programs`; backfill from `language_code`.
   Map `ielts` → English, matching what the frontend already renders.
3. Import the 8 `DEFAULT_PROGRAMS` demos as real records, skipping any whose title
   already exists in the target section.
4. Delete the "Acton Kim" lorem record.
5. Drop `language_code`.

Take a production database backup before deploying. Every step has a `down()`.

## API

`GET /api/v1/language-sections` returns active sections ordered by `sort_order`,
each with its active programs nested — one request instead of two, which matters on
mobile.

```json
{ "success": true,
  "data": [
    { "id": 1, "slug": "english", "label": "English Tests",
      "short_label": "English", "heading": "English Test Preparation",
      "subtitle": "IELTS, PTE, LanguageCert …",
      "icon_name": "Languages", "color_class": "bg-blue-50 text-blue-600",
      "sort_order": 1,
      "programs": [ { "id": 1, "title": "IELTS Preparation", "duration": "8 Weeks",
                      "badge": "Most Popular", "description": "…",
                      "benefits": ["…"], "color_class": "bg-blue-50 text-blue-600",
                      "image_url": null } ] } ] }
```

Sections with no active programs are excluded from the response, so a visitor never
meets an empty tab. `GET /api/v1/language-programs` stays for backward compatibility
but the site no longer uses it.

## Frontend

`LanguagesSection.tsx`: delete `GROUPS`, `DEFAULT_PROGRAMS` and `normalizeGroup()`.
Tabs, headings and subtitles come from the API.

**Shared icon registry.** `ICON_MAP` is currently duplicated in `WhyChooseUs.tsx`
and `FeaturedCourses.tsx`, each with its own `?? fallback`. This feature needs a
third, so the two existing maps move to one `src/lib/icons.ts` exporting the map and
a `resolveIcon(name)` helper that falls back to a default. Both existing components
switch to it. An unknown `icon_name` can then never break any page.

`color_class` needs no registry — the stored value is already the class pair the
existing components split on.

**Swipeable tab strip:** horizontal flex, `overflow-x-auto`, scroll-snap, hidden
scrollbar, active tab pulled into view with `scrollIntoView`. Centred on desktop.
Vertical space stays constant regardless of section count.

**Empty states:** no sections at all → render nothing rather than a broken shell.

## Admin

A new **Language Sections** resource, surfaced on the Language Page hub:

- List: reorderable, with a **program count** column, icon and colour preview, and an
  inline visibility toggle.
- Form: Label (required), Short label (falls back to Label when blank), Heading,
  Subtitle, Icon picker, Colour picker, Order, Visible. `slug` is generated from the
  label and never shown.
- Every field carries plain-English helper text naming where it appears on the site.

`LanguageProgramResource` changes:

- `language_code` Select → `language_section_id` relationship Select showing section
  labels, required.
- `color_class` free-text Tailwind input → the same curated colour picker (column
  unchanged; only the input changes).
- `flag_emoji` — currently labelled "Short Code / Flag" and holding values like "GB"
  — gets helper text saying where it appears, or is hidden if it appears nowhere.
  Verify against the frontend during implementation before deciding.

**Delete guardrail:** deleting a section that still has programs is blocked with a
message naming the count ("Move or delete this section's 3 programs first"). Without
it, `nullOnDelete` would drop those programs off the site silently.

## Testing

Backend (PHPUnit):

- Migration is idempotent — running it twice creates no duplicates.
- `ielts` programs land in the English section; the lorem record is gone.
- API: shape, ordering, active-only filtering, sections without active programs omitted.
- Filament: section CRUD, reorder, program created against a section.
- Delete guardrail blocks a section that still has programs.

Frontend (vitest):

- `resolveIcon` returns the default for an unknown or missing `icon_name`.
- Tabs render from API data; a section with no programs produces no tab.
- `WhyChooseUs` and `FeaturedCourses` still resolve their icons after moving to the
  shared registry.

Browser, local then production:

- Create an "Arabic Tests" section, confirm the tab appears, add a program, confirm
  the card renders and its image is servable.
- Mobile 390px: tab strip swipes, active tab scrolls into view, zero horizontal overflow.

Regression: the existing 67-test backend suite stays green, PHPStan stays clean, and
the 174-page admin crawl reports no new findings.

## Out of scope — the remaining three tasks

Recorded here so they are not lost. Each gets its own spec.

**Task 2 — admin↔frontend gaps.** `ServicesSection` and the stats component are dead
code: 6 active Services and 4 active Stats are editable in the admin but render
nowhere. Six components are unreferenced (`ServicesSection`, `SocialClubSection`,
`AboutSection`, `AboutPreview`, `HomePreviews`, `HeroSection`). Conversely ~10 section
headings are hardcoded and not editable ("Speak the World", "Real Success, Real
People", "Elevate Your Skillset", …). `team_members` has 0 rows, so the About team
section renders empty.

**Task 3 — mobile scroll and carousels.** Measured at 390×844: home 9248px (11.0
screens), careers 6049px (7.2), about 4226px (5.0), events 3597px (4.3),
study-abroad 3117px (3.7), skills 2893px (3.4), languages 2821px (3.3). No
horizontal overflow anywhere. No card grid uses a carousel on mobile; the heaviest
are WhyChooseUs (1692px, 6 cards), DualFocus (2016px), Testimonials (1819px, 5
cards) and Internships (1822px, 4 cards).

**Task 4 — admin usability.** ~20 tap targets under 40px per page. Remaining raw
Tailwind inputs and other fields needing plain-language labels and pickers.
