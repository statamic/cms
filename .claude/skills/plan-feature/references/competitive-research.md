# Competitive / peer research

Research informs the plan. It is not a slide deck.

## How to research

1. Name the **job** (e.g. "schedule publish UI", "asset folder permissions"), not the implementation.
2. Pick 2–4 peers that actually solve that job.
3. Prefer official docs + current product behavior over third-party listicles.
4. For each peer, capture only:
   - How users accomplish the job
   - Defaults / opinionated choices
   - One thing they do better than our likely approach
   - One thing to avoid
5. End with a **recommendation** that changes our phases or UX — or explicitly "no change; peers confirm X".

Timebox: enough to improve the plan, not a market study.

## Who to check (by domain)

Use judgment; skip irrelevant columns.

| Domain | Often worth checking |
| --- | --- |
| CMS / content modeling | Craft CMS, WordPress (+ Gutenberg), Kirby, Directus, Sanity, Storyblok, Contentful |
| Control panel UX | Craft CP, Filament, Nova, WordPress admin (for familiarity traps) |
| Assets / media | Craft Assets, WordPress Media, Cloudinary-style DAM patterns |
| Permissions / roles | Craft, WordPress roles/caps, Laravel policies / Filament shields |
| Live preview / front-end editing | Craft live preview, WordPress editor, visual editors generally |
| Search | Scout-style, Algolia docs patterns, Meilisearch |
| Multisite / i18n | Craft multi-site, WordPress multisite / multilingual plugins, Kirby languages |
| Forms | Craft Formie-class patterns, WordPress form plugins, Typeform-ish UX only if relevant |
| Commerce-ish | Only if the feature is commerce; otherwise skip |

Also check **our own** prior art: Statamic addons, old issues, discussions, and similar fieldtypes/CP tools in this repo.

## Output shape in the plan

Keep it brutal:

```markdown
## Competitive notes
- **Craft**: … → we should …
- **Kirby**: … → avoid …
- **Recommendation**: Prefer A over B in See/Work; leave C for Deepen.
```

## Anti-patterns

- Feature matrices with 20 columns and no decision
- Citing competitors without tying to a phase or UX choice
- Letting competitor scope inflate Deepen into a rewrite of their entire product
