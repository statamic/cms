---
name: plan-feature
description: >-
  Plan and build features as researched, phased, atomic work. Use when the user
  brain-dumps a feature idea, asks to come up with a plan, wants clarifying
  questions / gap analysis / competitive research baked into a plan, or wants
  implementation broken into smallest testable slices on a clean branch with
  See → Work → Deepen phase groups. Invoke for /plan-feature, "plan this",
  "come up with a plan", or starting a new feature from a rough dump.
icon: map
color: blue
---

# Plan Feature

Turn a rough feature dump into a researched plan, then execute it as a linear
sequence of tiny, finishable slices. Never scaffold a whole feature up front.

Read `references/phase-model.md` before writing phases.
Read `references/plan-template.md` when drafting the plan document.
Read `references/competitive-research.md` when doing competitor / peer research.

## Modes

This skill has two modes. Default to **Plan** until the user explicitly says to
build / implement / start / go.

| Mode | Trigger | Output |
| --- | --- | --- |
| **Plan** | Brain dump, "come up with a plan", `/plan-feature` | Questions + full plan (no code) |
| **Build** | "Build it", "start", "go", approve a plan | Atomic slices on a clean branch |

Do not mix modes. In Plan mode: no implementation. In Build mode: follow the
approved plan slice-by-slice; do not re-litigate the plan unless blocked.

---

## Plan mode

### 1. Ingest the dump

Treat the user's message(s) as raw material, not a finished spec.

- Extract goals, constraints, non-goals, named UI/UX beats, tech hunches, and open worries.
- Do **not** start coding.
- Do **not** ask one question at a time mid-ingest. Gather context first.

### 2. Ground in this repo

Before proposing anything:

- Find existing related code, fieldtypes, CP patterns, APIs, config, and tests.
- Prefer extending established patterns over inventing parallel systems.
- Note Eloquent / Stache / GraphQL / REST / CP / frontend touchpoints that may matter later (often Deepen-phase work).

### 3. Gap analysis (things they forgot)

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

### 4. Competitive / peer research

Do lightweight but real research. Use web search / docs fetch when available.

- Compare how peer products solve the same job (not feature checklists for vanity).
- Capture: interaction model, naming, defaults, killer detail worth stealing, traps to avoid.
- Prefer primary docs and recent product UI over random blogs.
- Fold findings into the plan as **recommendations**, not a separate essay.

See `references/competitive-research.md` for who to check by domain.

### 5. Clarifying questions

After research + gap analysis, ask a **single batched** set of questions.

Rules:

- Only ask what changes the plan or sequencing.
- Prefer multiple-choice / opinionated defaults when possible ("Default: X — override?").
- Separate **blockers** (need answer before planning further) from **nice-to-know**.
- If the user said "just plan" and leftovers are non-blocking, state assumptions and continue.

Wait for answers on blockers before finalizing the plan (unless they explicitly want a draft plan with assumptions called out).

### 6. Produce the plan

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

Present the plan. Ask for approval or edits. Do not build yet.

---

## Build mode

Only after explicit go-ahead on a plan (or a clearly scoped subset).

### 0. Clean branch first

Always start from a fresh branch off the current base (usually `6.x`):

```bash
git fetch origin
git checkout <base>
git pull origin <base>
git checkout -b <type>/<short-feature-name>
```

- Never pile a new feature onto a dirty mixed-purpose branch.
- If the working tree already has unrelated changes, stop and sort that out before building.
- One feature effort → one branch (or stacked branches only if the plan says so).

### 1. One slice at a time

For each slice, in order:

1. **Implement only that slice** — nothing speculative for later slices.
2. **Verify** — run the slice's stated checks (PHPUnit/Vitest/manual CP poke as applicable).
3. **Describe** — short note of what changed and how to see it (for the user / PR body).
4. **Commit** — focused commit message matching the slice intent.
5. **Stop if the plan says to** — especially after See / Work phase boundaries. Tell the user what to open/click/run next. Wait for "continue" unless they pre-approved running through a phase group.

### 2. Progression rules

- Each commit should leave the project **coherent** — not necessarily feature-complete, but not a landmine. Prefer vertical thin slices over horizontal layers.
- See-phase code may be ugly or partial; still keep it runnable enough to inspect.
- Work-phase slices must make the happy path actually work before Deepen starts.
- If a slice reveals the plan is wrong, pause, propose a plan amendment, and get a nod before rewriting the roadmap.

### 3. Testing bar

- Prefer adding/adjusting tests in the same slice that introduces behavior.
- Don't defer all tests to a final "Phase N: tests" dump.
- Manual CP verification counts when UI-only; say exactly where to look.

### 4. Done

When the agreed phases are complete (or the user stops at a pause point):

- Summarize what shipped per phase.
- List what remains (deferred Deepen items).
- Note how to try it.

---

## Communication style

- Treat the user as an expert. Be terse and opinionated.
- Lead with the plan / questions, not process narration.
- Competitive notes: sharp takeaways only.
- During Build: say which slice you're on, then do it — no giant status essays.
