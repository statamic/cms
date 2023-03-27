# Release Notes

## 4.0.0-alpha.1 (2023-03-27)

### What's new
- Laravel 10 support. [#7540](https://github.com/statamic/cms/issues/7540)
- Blueprints fields can be organized into both tabs and sections. [#7746](https://github.com/statamic/cms/issues/7746)
- Ability to hide field display labels. [#7737](https://github.com/statamic/cms/issues/7737)
- Redesigned Bard/Replicator set picker with search, groups, and descriptions.
- Tailwind 3. [#7519](https://github.com/statamic/cms/issues/7519)
- Tailwind Container Queries plugin, used to control fields widths. [#7557](https://github.com/statamic/cms/issues/7557)
- A "fullscreen" Vue component, used to make bring fullscreen mode to a bunch of fieldtypes. [#7569](https://github.com/statamic/cms/issues/7569)
- Width fieldtype. [#7582](https://github.com/statamic/cms/issues/7582)

### What's improved
- A myriad of UI improvements. [#7559](https://github.com/statamic/cms/issues/7559)
- All "save" buttons have been moved to the tops of pages. [#7600](https://github.com/statamic/cms/issues/7600)
- Consolidated all icon fonts down to one. [#7548](https://github.com/statamic/cms/issues/7548)

### What's changed
- Dropped support for PHP 7 and Laravel 8. [#7490](https://github.com/statamic/cms/issues/7490)
- Composer actions (updates, installing addons) can no longer be performed in the Control Panel. [#7703](https://github.com/statamic/cms/issues/7703)
- AMP support has been removed. [#7498](https://github.com/statamic/cms/issues/7498)
- A bunch of JavaScript packages have been removed. [#7504](https://github.com/statamic/cms/issues/7504)
- The SortableList component now no default `delay`. [#7755](https://github.com/statamic/cms/issues/7755)
- Popper.js has been replaced by Floating UI. Popover contents get portalled to the end of the page. [#7744](https://github.com/statamic/cms/issues/7744)
- `Statamic\Support\Arr` and `Statamic\Support\Str` inheritance behavior has been changed. [#7592](https://github.com/statamic/cms/issues/7592)
- `Statamic\Support\Str::replace()` arguments are swapped to match Laravel's. [#7603](https://github.com/statamic/cms/issues/7603)
- Route namespaces have been removed. [#7609](https://github.com/statamic/cms/issues/7609)
- Deprecations have been removed. [#7536](https://github.com/statamic/cms/issues/7536)
- Dropped support for Commonmark v1. [#7496](https://github.com/statamic/cms/issues/7496)
- Dropped support for Flysystem v1. [#7491](https://github.com/statamic/cms/issues/7491)
- Less JavaScript config variables are exposed outside the Control Panel.  [#7735](https://github.com/statamic/cms/issues/7735)
- Internal build tooling has been migrated to Vite. [#7485](https://github.com/statamic/cms/issues/7485)
