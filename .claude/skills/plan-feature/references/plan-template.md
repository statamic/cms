# Plan template

Copy this structure. Omit sections only when truly empty — prefer an explicit "None" over silent gaps.

```markdown
# Feature: <name>

## Problem / job to be done
<1–3 sentences>

## Goals
- …

## Non-goals
- …

## Assumptions
- … (call out anything guessed)

## Existing code to lean on
- `path` — why

## Gaps / risks we almost forgot
| Item | Decision |
| --- | --- |
| … | must decide / defer (Deepen-N) / out of scope |

## Competitive notes
- **<product>**: takeaway → how it affects us
- **Recommendation**: …

## Open questions
### Blockers
1. …

### Non-blocking
1. … (default: …)

## Approach (short)
<paragraph or short bullet architecture — not a novel>

## Phased build

### See
#### Phase S1 — <title>
**Demo:** <what Jack opens/clicks/runs>
**Pause after:** yes/no

| Slice | Intent | Verify | Commit intent |
| --- | --- | --- | --- |
| S1.1 | … | … | … |
| S1.2 | … | … | … |

#### Phase S2 — …
…

### Work
#### Phase W1 — …
…

### Deepen
#### Phase D1 — …
…

## Branch
- Base: `6.x` (or …)
- Branch: `feature/<name>`

## Definition of done (for agreed scope)
- [ ] …

## Handoff
Execute with `/build-feature` (optionally: See only / through Work / full plan).
```

## Writing tips

- Slice IDs stay stable (`S1.2`, `W2.1`) so `/build-feature` can say "doing W2.1".
- Verify column must be concrete: `./vendor/bin/phpunit --filter FooTest`, "CP → Foo → create → save", etc.
- If competitive research found nothing useful, say so in one line — don't pad.
- Always close the plan by pointing at `/build-feature`.
