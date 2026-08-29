# Phase model

Phases exist so a human can **stop, look, and poke** — not so an agent can batch work.

## Phase groups

Every plan assigns each phase to one group:

### See — "I want to see it"

- Make the feature **visible or tangible** as early as possible.
- Wire the thinnest possible path to something on screen / in CLI / in a test dump.
- Incomplete, stubbed, or broken behavior is OK if the shape is inspectable.
- Typical slices: route + empty CP view, fieldtype shell, config key that appears, nav item, read-only listing with fake data.
- Exit criterion: user can open/run something and recognize the feature.

### Work — "I want to see it work"

- Make the **core happy path** real.
- Still avoid secondary drivers, rare edge cases, and polish.
- Typical slices: create/edit/delete, persistence, validation, primary UX interaction, essential permissions.
- Exit criterion: user can complete the main job without fake data or "TODO" walls.

### Deepen — "make it product-grade"

- Integrations and parity that would have slowed See/Work.
- Examples for Statamic: Eloquent driver, GraphQL/REST, revisions, multisite, translations, docs, performance, addon hooks, a11y pass.
- Exit criterion: each deepen phase has its own demo/verify story; don't amalgamate into "the rest".

## How many phases?

As many as needed. Prefer **more small phases** over few large ones.

Good:

- See-1: CP nav + blank screen
- See-2: list UI with hard-coded rows
- Work-1: read from Stache
- Work-2: create form saves
- Work-3: edit + delete
- Deepen-1: permissions
- Deepen-2: Eloquent parity
- Deepen-3: translations + docs

Bad:

- Phase 1: everything backend
- Phase 2: everything frontend
- Phase 3: tests and docs

## Atomic slices

A slice is too big if you cannot:

- explain it in one sentence, **and**
- verify it without building the next slice, **and**
- commit it without leaving half-related files "for later" uncommitted.

If a slice needs a temporary seam (stub, fake data, feature flag), prefer that over pulling forward the next slice's real implementation.

## Vertical over horizontal

Prefer end-to-end thin cuts:

`nav → view → one action → one test`

over layer cakes:

`all models → all controllers → all Vue → all tests`

## Pause points

After every See phase and every Work phase, the plan must include:

> **Pause:** try X. Say continue when ready.

Build mode honors these unless the user pre-authorizes a longer run ("do all See phases").
