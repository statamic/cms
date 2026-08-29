---
name: plan-feature
description: >-
  Plan new features from a brain dump: gap analysis, competitive research,
  clarifying questions, and a phased See → Work → Deepen plan with atomic
  slices. Use for /plan-feature, "plan this", "come up with a plan", or when
  starting a feature from a rough dump. Does not implement — hand off to
  /build-feature when the plan is approved.
icon: map
color: blue
---

# Plan Feature

Turn a rough feature dump into a researched, phased plan with atomic slices.
**Do not write feature code in this skill.** When the plan is approved, tell
the user to run `/build-feature`.

Read `references/phase-model.md` before writing phases.
Read `references/plan-template.md` when drafting the plan document.
Read `references/competitive-research.md` when doing competitor / peer research.

Companion: `/build-feature` executes the approved plan.

---

## 1. Ingest the dump

Treat the user's message(s) as raw material, not a finished spec.

- Extract goals, constraints, non-goals, named UI/UX beats, tech hunches, and open worries.
- Do **not** start coding.
- Do **not** ask one question at a time mid-ingest. Gather context first.

## 2. Ground in this repo

Before proposing anything:

- Find existing related code, fieldtypes, CP patterns, APIs, config, and tests.
- Prefer extending established patterns over inventing parallel systems.
- Note Eloquent / Stache / GraphQL / REST / CP / frontend touchpoints that may matter later (often Deepen-phase work).

## 3. Gap analysis (things they forgot)

Actively hunt for omissions. Typical Statamic/CMS gaps:

- Permissions, roles, and Pro vs free boundaries
- Multisite, localization, and publish states
- Revisions, blueprints, and default config
- Stache vs Eloquent driver parity
- API / GraphQL exposure
- Upgrade path, migrations, and backward compatibility
- Empty states, errors, loading, and keyboard/a11y in CP
- Docs, changelog, and translation strings
- Performance, caching, and large-site behavior
- Addon extension points / events / tags / modifiers

List forgotten items as either **must decide**, **defer to Deepen**, or **out of scope**.

## 4. Competitive / peer research

Do lightweight but real research. Use web search / docs fetch when available.

- Compare how peer products solve the same job (not feature checklists for vanity).
- Capture: interaction model, naming, defaults, killer detail worth stealing, traps to avoid.
- Prefer primary docs and recent product UI over random blogs.
- Fold findings into the plan as **recommendations**, not a separate essay.

See `references/competitive-research.md` for who to check by domain.

## 5. Clarifying questions

After research + gap analysis, ask a **single batched** set of questions.

Rules:

- Only ask what changes the plan or sequencing.
- Prefer multiple-choice / opinionated defaults when possible ("Default: X — override?").
- Separate **blockers** (need answer before planning further) from **nice-to-know**.
- If the user said "just plan" and leftovers are non-blocking, state assumptions and continue.

Wait for answers on blockers before finalizing the plan (unless they explicitly want a draft plan with assumptions called out).

## 6. Produce the plan

Write the plan using `references/plan-template.md`.

Hard requirements for every plan:

1. **Phase groups** — organize work into groups the human can pause and explore:
   - **See** — visible presence / spike. OK if incomplete or broken. Goal: *I can see it.*
   - **Work** — core path actually works. Goal: *I can use it.*
   - **Deepen** — integrations, parity, polish, edge cases (Eloquent, APIs, a11y, docs, etc.). Goal: *It belongs in the product.*
2. **Phases inside groups** — as many as needed (2 or 10). Each phase ends in something the user can open, click, run, or otherwise inspect.
3. **Atomic slices** — every phase is broken into the **smallest** finishable pieces. One concern per slice. No "set up the whole feature" slices.
4. **Linear dependency** — slices build on prior slices. No parallel mega-scaffolding.
5. **Per-slice contract** — each slice lists: intent, files/areas touched (best guess), how to verify, and commit message intent.
6. **Stop points** — explicit "pause for Jack to explore" markers after See phases and after Work phases (and anytime a phase changes what can be demoed).

Anti-patterns (reject these in your own draft):

- "Phase 1: scaffold models, CP Vue, API, and tests"
- Big-bang branches with everything half-wired
- Deepen work (Eloquent, GraphQL, …) blocking the first visible spike
- Vague slices ("improve UX", "handle edge cases") without a verify step

## 7. Handoff

Present the plan. Ask for approval or edits. **Do not implement.**

When the plan looks good (approved, or user says go / build / start), end with an
explicit handoff — not a silent mode switch:

> Plan's ready. Run **`/build-feature`** to execute it (clean branch, one slice
> at a time, pause at See/Work checkpoints). Say which phases to run if you
> don't want the whole thing yet (e.g. "See only").

If they ask you to build in the same turn without invoking the build skill,
still point them at `/build-feature` and follow that skill's instructions
(read `.claude/skills/build-feature/SKILL.md`) before writing code.

---

## Communication style

- Treat the user as an expert. Be terse and opinionated.
- Lead with the plan / questions, not process narration.
- Competitive notes: sharp takeaways only.
- Never drift into implementation during planning.
