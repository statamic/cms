---
name: changelog
description: Generate a changelog for a release
disable-model-invocation: true
---

Generate a changelog entry for the CHANGELOG.md file by following these steps:

## 1. Get commits since last tag

Run `git --no-pager log $(git describe --tags --abbrev=0)..HEAD --oneline --no-decorate --first-parent --no-merges`) to get the list of commits.

## 2. Process each commit

For each commit:
- Extract the PR number from the commit message (e.g., `(#13331)`)
- Remove the commit SHA and `[6.x]` prefix from the message
- Fetch the PR author from GitHub using `gh pr view <number> --json author --jq '.author.login'` for all PRs in a single command.

## 3. Skip certain commits

Skip commits that are:
- Test fixture updates (e.g., "Update composer test fixtures")
- CI/workflow changes (e.g., "Only run lint workflow...")

Do not skip dependency bumps from dependabot.

## 4. Categorize commits

- **What's new**: Commits that add new features (title contains "Add" or introduces new functionality). An "improvement" is not to be considered new. 
- **What's fixed**: Bug fixes, improvements, translation updates, and other changes

## 5. Format each entry

Format: `- Description [#NUMBER](https://github.com/statamic/cms/issues/NUMBER) by @username`

## 6. Order entries

- Reverse the list so earliest commits come first (git log shows newest first)
- Within each category, maintain chronological order
- Translation PRs get moved to the bottom of the list

## 7. Determine version number

- Look at the previous version in CHANGELOG.md
- If there are new features → increment minor version (e.g., 6.0.0 → 6.1.0)
- If only fixes → increment patch version (e.g., 6.0.0 → 6.0.1)

## 8. Format the release

```markdown
## X.Y.Z (YYYY-MM-DD)

### What's new
- Entry 1
- Entry 2

### What's fixed
- Entry 1
- Entry 2
```

- Use today's date
- Leave 3 empty lines between releases
- If there are no new features, omit the "What's new" section

## 9. Update CHANGELOG.md

Insert the new release entry at the top of the file, after the `# Release Notes` heading.

## 10. Provide a summary to the user

Inform the user of anything noteworthy, or any liberties you took. e.g. "I skipped PR 123 because of reason" or "I moved PR 123 to the bottom of the list"

You do not need to list *everything*. Only the noteworthy bits. The user will be able to inspect the diff of the changelog to see what you're adding.
