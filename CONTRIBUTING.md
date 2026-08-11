# How to Contribute to Statamic

:sparkles: Before we get started, thank you for taking the time to contribute! :sparkles:

This is a guideline for contributing to Statamic, its documentation, and addons. All of these wonderful things are hosted here in the [Statamic organization](https://github.com/statamic) on GitHub. We welcome your feedback, proposed changes, and updates to these guidelines. We will always welcome thoughtful issues and consider pull requests.

#### Table of Contents

- [What You Should Know Before Contributing](#what-you-should-know-before-contributing)
- [Statamic isn’t FOSS](#statamic-isnt-foss)
- [How to Get Support](#how-to-get-support)
- [How You Can Contribute](#how-you-can-contribute)
- [Which Repo?](#which-repo)
- [Bug Reports](#bug-reports)
- [Control Panel Performance Reports](#control-panel-performance-reports)
- [Feature Requests](#feature-requests)
- [Security Disclosures](#security-disclosures)
- [Core Enhancements](#core-enhancements)
- [Compiled Assets](#compiled-assets)
- [Control Panel Translations](#control-panel-translations)
- [Documentation Edits](#documentation-edits)
- [Pull Requests](#pull-requests)

## What You Should Know Before Contributing

### Statamic isn’t FOSS

While Statamic's source code is open source, publicly available, and can be [used for free](https://statamic.dev/licensing#solo-vs-pro) in many cases, it is proprietary. Everything in this repo, including any community-contributed code, is the property of Statamic LLC. For that reason there are a few limitations on how you can use the code:

- You cannot alter anything related to licensing, updating, version or edition checking, purchasing, first party notifications or banners, or anything else that attempts to circumvent paying for features that are designated as Statamic Pro features. We want to stay in business so we can better support _you_ and the community.
- You can’t publicly maintain a long-term fork of Statamic.

### How to Get Support

If you're looking for official developer support (and you have an active license), please visit [statamic.com/support](https://statamic.com/support). We will always do our best to reply in a timely manner. **Github issues are intended for reporting bugs.**

You can chat and collaborate with other developers in the community in [Github Discussions](https://github.com/statamic/cms/discussions) or [Discord](https://statamic.com/discord). You will find many helpful folks who may be willing to help.

## How You Can Contribute

### Which Repo?

Statamic is broken out into a few Github repositories. Here's a quick summary of each.

- [`statamic/cms`](https://github.com/statamic/cms) is the core package. It doesn't run by itself but is instead a dependency consumed by Laravel apps. 99% of the work goes on here.
- [`statamic/statamic`](https://github.com/statamic/statamic) is the starter Laravel app used to build a new site.
- [`statamic/docs`](https://github.com/statamic/docs) is the Statamic documentation site that is curently running on [statamic.dev](https://statamic.dev).

### Bug Reports

First things first. If the bug is security related refer to our [security disclosures](#security-disclosures) procedures instead of opening an issue.

Next, please search through the [open issues](https://github.com/statamic/cms/issues) to see if it has already been opened.

If you _do_ find a similar issue, upvote it by adding a :thumbsup: [reaction](https://github.com/blog/2119-add-reactions-to-pull-requests-issues-and-comments). Only leave a comment if you have relevant information to add.

If no one has filed the issue yet, feel free to [submit a new one](https://github.com/statamic/cms/issues/new?template=bug_report.yml). Please include a clear description of the issue, follow along with the issue template, and provide and as much relevant information as possible. Code examples demonstrating the issue are the best way to ensure a timely solution to the issue.

### Control Panel Performance Reports

If you're reporting sluggish Bard, Replicator, or publish-form behavior, please include a structured perf report when you can:

1. Open the Control Panel and run this in the browser console:

```js
localStorage.setItem('statamic.perf', '1')
```

2. Reload the page (required — instrumentation starts in the **mount** phase on boot).
3. Reproduce the issue. Useful recipes:
   - **Slow initial render:** open the entry, wait until idle, then `Statamic.$perf.report()` — look at `phase.mount` and the `mount.*` rows.
   - **Slow save:** click Save, wait for it to finish, then report — look at `phase.save` and `save.publish.save.*`.
   - **Slow typing / editing:** after the form is idle, run `Statamic.$perf.reset()` to clear mount noise, reproduce the interaction, then report — look at `interact.*`.
4. Run:

```js
Statamic.$perf.report()
```

You'll get a color-coded list grouped by phase (`mount` → `save` → `interact`). Times are **milliseconds**. Headline wall clocks: `phase.mount` (initial render) and `phase.save` (full save pipeline). Heat levels: `critical`, `hot`, `warm`, `ok`, or `count` (tally only).

5. Export / paste into the GitHub issue (DevTools `console.table` is preview-only — use these):

```js
Statamic.$perf.copy('md')     // markdown table → clipboard (best for GitHub)
Statamic.$perf.copy()         // TSV → clipboard (spreadsheets)
Statamic.$perf.copy('json')   // versioned snapshot → clipboard
Statamic.$perf.download()     // download snapshot JSON
```

To compare two runs over time:

```js
const before = Statamic.$perf.snapshot('before-fix')
// …change code / reload / reproduce…
Statamic.$perf.diff(before)           // console delta table
Statamic.$perf.copyDiff(before)       // TSV deltas → clipboard
```

To turn instrumentation off afterward:

```js
Statamic.$perf.disable()
// or: localStorage.removeItem('statamic.perf')
```

This uses the browser User Timing API under the hood (marks also show up in the Chrome DevTools Performance panel). Core maintainers compare changes against the Vitest browser benchmark suite — see [`benchmarks/README.md`](benchmarks/README.md).

### Feature Requests

Feature requests should be created in the [statamic/ideas](https://github.com/statamic/ideas) repository.

### Security Disclosures

If you discover a security vulnerability, please review our [Security Policy](https://github.com/statamic/cms/security/policy), then report the issue directly to us from [statamic.com/support](https://statamic.com/support). We will review and respond privately via email.

### Documentation Edits

Statamic's documentation lives in the [https://github.com/statamic/docs](https://github.com/statamic/docs) repository. Improvements or corrections to them can be submitted as a pull request.

### Core Enhancements

If you would like to work on a new core feature or improvement, first create a [Github issue](https://github.com/statamic/cms/issues) for it if there’s not one already. While we appreciate community contributions, we do remain selective about what features make it into Statamic itself, so don’t take it the wrong way if we recommend that you pursue the idea as an addon instead.

### Compiled Assets

If you are submitting a change that will affect a compiled file, such as most of the files in `resources/sass` or `resources/js`, do not commit the compiled files. Due to their large size, they cannot realistically be reviewed by our team. This could be exploited as a way to inject malicious code into Statamic. In order to defensively prevent this, all compiled files will be generated and committed by the core Statamic team.

### Control Panel Translations

We welcome new translations and updates! Please follow [these instructions](https://statamic.dev/cp-translations#contributing-a-new-translation) on how to contribute to Statamic's translation files.

### Pull Requests

Pull requests should clearly describe the problem and solution. Include the relevant issue number if there is one. If the pull request fixes a bug, it should include a new test case that demonstrates the issue, if possible.

Creating a pull request that introduces a new feature or changes current behavior? Please open an issue referencing your PR in the [statamic/docs](https://github.com/statamic/docs/issues) repo. No need to write the docs yourself. We'll take care of that for you. Any hints or bullet points are appreciated though!

PR titles should include the major version number they're targeted at — e.g. [4.x] or [3.x].

<br>
Thank you! Stay rad. If you're not already rad, tell us and we will make it so.

:sparkles:
