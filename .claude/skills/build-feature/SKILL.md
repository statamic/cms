---
name: build-feature
description: >-
  Execute an approved feature plan as atomic slices on a clean branch. Use for
  /build-feature, or when the user says build/start/go after /plan-feature.
  One slice at a time: implement, verify, describe, commit; pause at See/Work
  checkpoints. Never big-bang scaffold. Requires a plan from /plan-feature
  (or an equivalent phased slice list in the conversation).
icon: hammer
color: green
---

# Build Feature

Execute an approved `/plan-feature` plan as a linear sequence of tiny,
finishable slices. Never scaffold a whole feature up front.

Read `references/phase-model.md` for See / Work / Deepen rules and pause points.

Companion: `/plan-feature` produces the plan. If there is no approved plan (or
equivalent phased slice list) in the conversation, stop and tell the user to
run `/plan-feature` first — or ask them to paste/attach the plan.

---

## 0. Preconditions

Before any code:

1. Confirm the plan (or the subset to run: "See only", "through Work", specific phase IDs).
2. Confirm working tree is clean of unrelated work; stop if mixed junk is present.
3. Create a **brand new branch** off the plan's base (usually `6.x`):

```bash
git fetch origin
git checkout <base>
git pull origin <base>
git checkout -b <type>/<short-feature-name>
```

- Never pile a new feature onto a dirty mixed-purpose branch.
- One feature effort → one branch (stacked branches only if the plan says so).

## 1. One slice at a time

Work slices in plan order. For each slice:

1. **Announce** the slice id + intent (e.g. `W2.1 — save create form`).
2. **Implement only that slice** — nothing speculative for later slices.
3. **Verify** — run the slice's stated checks (PHPUnit/Vitest/manual CP poke).
4. **Describe** — short note of what changed and how to see it.
5. **Commit** — focused message matching the slice's commit intent.
6. **Honor pause points** — after See / Work phase boundaries (and any plan-marked pause), stop. Tell the user what to open/click/run. Wait for "continue" unless they pre-approved a longer run ("do all See", "through Work").

## 2. Progression rules

- Each commit leaves the project **coherent** — not necessarily complete, not a landmine. Prefer vertical thin slices over horizontal layers.
- See-phase code may be ugly or partial; still runnable enough to inspect.
- Finish Work happy path before starting Deepen.
- If a slice proves the plan wrong: pause, propose a small plan amendment, get a nod. For a full re-plan, send them back to `/plan-feature`.

## 3. Testing bar

- Add/adjust tests in the same slice that introduces behavior.
- No final "Phase N: dump all tests" unless the plan explicitly isolated a test-only slice for good reason.
- Manual CP verification counts for UI-only slices — say exactly where to look.

## 4. Anti-patterns

- Scaffolding models + CP + API + tests in one go
- Skipping commits until "it all works"
- Implementing Deepen items early "while we're here"
- Continuing past a pause point without approval
- Re-planning from scratch instead of a surgical amendment (unless asked)

## 5. Done / pause summary

At each pause and at the end:

- What shipped (phase / slice ids)
- How to try it
- What's next (next slice/phase, or deferred Deepen)
- If the agreed scope is finished, say so plainly

---

## Communication style

- Expert, terse. Slice id first, then work.
- No giant status essays between slices.
- Don't re-litigate the plan unless blocked.
