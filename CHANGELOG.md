# Release Notes

## 4.0.0-alpha.3 (2023-04-11)

### What's new
- Redesigned and simplified the `color` fieldtype. [#7828](https://github.com/statamic/cms/issues/7828) [#7830](https://github.com/statamic/cms/issues/7830)
- Ability to get the fields of a blueprint section. [#7852](https://github.com/statamic/cms/issues/7852)

### What's improved
- Relationship fields in listings will show first 2 items with a toggle to show them all. [#7871](https://github.com/statamic/cms/issues/7871)
- Reorganize icons and add social ones. [#7854](https://github.com/statamic/cms/issues/7854) [#7864](https://github.com/statamic/cms/issues/7864)
- Use floating ui to position select options. [#7847](https://github.com/statamic/cms/issues/7847)
- Update Tiptap to stable. [#7848](https://github.com/statamic/cms/issues/7848)

### What's fixed
- Assorted UI fixes. [#7873](https://github.com/statamic/cms/issues/7873) [#7849](https://github.com/statamic/cms/issues/7849) [#7843](https://github.com/statamic/cms/issues/7843)
- More assorted fixes. [#7872](https://github.com/statamic/cms/issues/7872)
- Fix fullscreen button on grid stacked mode. [#7869](https://github.com/statamic/cms/issues/7869)
- Fix some container related padding issues. [#7868](https://github.com/statamic/cms/issues/7868)
- Fix Replicator sorting. [#7867](https://github.com/statamic/cms/issues/7867)
- Fix Bard and Replicator in GraphQL when using set groups. [#7863](https://github.com/statamic/cms/issues/7863)
- Fix Replicator set pickers not closing when opening a second one. [#7862](https://github.com/statamic/cms/issues/7862)
- Fix unnecessary navigate-away dialog. [#7857](https://github.com/statamic/cms/issues/7857)
- Fix global site selector styles. [#7853](https://github.com/statamic/cms/issues/7853)
- Fix Replicator issues. [#7827](https://github.com/statamic/cms/issues/7827)
- Bard/Replicator set picker now has a max-height and is scrollable. [#7845](https://github.com/statamic/cms/issues/7845)
- Fix fieldset import label spacing. [#7846](https://github.com/statamic/cms/issues/7846)
- Popovers now stop click propagation. [#7844](https://github.com/statamic/cms/issues/7844)
- Fix icon dropdown inside modals. [#7841](https://github.com/statamic/cms/issues/7841)
- Fix data-list columns not being removable. [#7829](https://github.com/statamic/cms/issues/7829)
- Fix tab fade element z-index. [#7831](https://github.com/statamic/cms/issues/7831)

### What's changed
- The `color` fieldtype now only supports hex values.



## 4.0.0-alpha.2 (2023-04-04)

### What's new
- Ability to promote a user to super from within the CP. [#7716](https://github.com/statamic/cms/issues/7716)

### What's improved
- A bunch of UI improvements. [#7819](https://github.com/statamic/cms/issues/7819) [#7803](https://github.com/statamic/cms/issues/7803)
- Portal improvements. [#7821](https://github.com/statamic/cms/issues/7821)

### What's fixed
- Fix taggable fieldtype not being deletable [#7824](https://github.com/statamic/cms/issues/7824)
- Bard set picker positioning. [#7818](https://github.com/statamic/cms/issues/7818)
- Avoid removing popover contents when closed. [#7794](https://github.com/statamic/cms/issues/7794)
- Fix errors related to minification. [#7776](https://github.com/statamic/cms/issues/7776)
- A variety of other misc fixes. [#7806](https://github.com/statamic/cms/issues/7806)

### What's changed
- Panes have been removed. [#7812](https://github.com/statamic/cms/issues/7812)
- PortalVue's component has been renamed to `<v-portal>` since Statamic now has a `<portal>` component.



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
- Icon fieldtype. [#7582](https://github.com/statamic/cms/issues/7740)

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
