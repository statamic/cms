# Release Notes

## 4.0.0 (2023-05-09)

### What's new
- Official 4.0 release! 🎉

### What's fixed
- Fix set picker text selection when using Firefox. [#8076](https://github.com/statamic/cms/issues/8076) by @o1y
- Fix missing gap cursor in Bard fullscreen mode. [#8074](https://github.com/statamic/cms/issues/8074) by @jasonvarga
- Reset stacking context to prevent elements leaking into other stacks. [#8073](https://github.com/statamic/cms/issues/8073) by @jasonvarga



## 4.0.0-beta.4 (2023-05-06)

### What's fixed
- Fix combination of `yield` and `else` in Antlers templates causing blank pages. [#8067](https://github.com/statamic/cms/issues/8067) by @JohnathonKoster



## 4.0.0-beta.3 (2023-05-05)

### What's fixed
- Fix relationship fieldtype max items to value mismatch. [#8061](https://github.com/statamic/cms/issues/8061) by @jesseleite
- Fix fields not being droppable onto new sections. [#8065](https://github.com/statamic/cms/issues/8065) by @jasonvarga
- Fix date filter. [#8064](https://github.com/statamic/cms/issues/8064) by @jasonvarga
- UI fixes. [#8058](https://github.com/statamic/cms/issues/8058) by @jackmcdade
- Update password activation table name. [#8059](https://github.com/statamic/cms/issues/8059) by @jasonvarga
- Remove unused config option. [#8057](https://github.com/statamic/cms/issues/8057) by @jasonvarga
- Replace local version of upload package. [#8050](https://github.com/statamic/cms/issues/8050) by @jasonvarga
- Fix Bard button settings drag UX. [#8043](https://github.com/statamic/cms/issues/8043) by @jackmcdade
- Replicator and Bard sets fieldtype improvements and fixes. [#8049](https://github.com/statamic/cms/issues/8049) by @jasonvarga
- Remove array fieldtype mirror. [#8046](https://github.com/statamic/cms/issues/8046) by @jasonvarga
- Fix toggle listing icons. [#8054](https://github.com/statamic/cms/issues/8054) by @jasonvarga
- Changes from 3.4



## 4.0.0-beta.2 (2023-05-02)

### What's new
- Export ProseMirror model and view. [#8032](https://github.com/statamic/cms/issues/8032) by @jacksleight
- Add actions to assign roles and groups to users from the users listing. [#8013](https://github.com/statamic/cms/issues/8013) by @jesseleite

### What's improved
- Bring back Bard's Sticky Toolbar, but only for top-level fields. [#8022](https://github.com/statamic/cms/issues/8022) by @jackmcdade
- Improve column resizing UI in Bard table. [#8025](https://github.com/statamic/cms/issues/8025) by @o1y

### What's fixed
- UI fixes. [#8033](https://github.com/statamic/cms/issues/8033) by @jackmcdade
- Fix date field issues. [#8036](https://github.com/statamic/cms/issues/8036) by @jasonvarga
- Revise asset folder creation modals. [#8034](https://github.com/statamic/cms/issues/8034) by @jasonvarga
- Fix losing super when editing self user in CP. [#8012](https://github.com/statamic/cms/issues/8012) by @jesseleite
- Fix relationship selector search autofocus. [#8021](https://github.com/statamic/cms/issues/8021) by @o1y
- Text field size consistency. [#8028](https://github.com/statamic/cms/issues/8028) by @jackmcdade
- Fix Bard Toolbar position when in fullscreen mode. [#8024](https://github.com/statamic/cms/issues/8024) by @o1y
- Fix Reference Error when using bard toolbar link button. [#8019](https://github.com/statamic/cms/issues/8019) by @jasonvarga



## 4.0.0-beta.1 (2023-04-27)

### What's improved
- Listing filter refinements. [#8001](https://github.com/statamic/cms/issues/8001) by @jesseleite

### What's fixed
- Fix replicator error when value references a non-configured set. [#8011](https://github.com/statamic/cms/issues/8011) by @jasonvarga
- Fix double popover opened event. [#8004](https://github.com/statamic/cms/issues/8004) by @jasonvarga
- Fix Firefox data-table issues. [#8003](https://github.com/statamic/cms/issues/8003) by @jackmcdade



## 4.0.0-alpha.5 (2023-04-26)

### What's new
- Add support for looping over blueprint sections in frontend forms [#7778](https://github.com/statamic/cms/issues/7778) by @jesseleite

### What's improved
- Nav item editor supports blueprint sections. [#7990](https://github.com/statamic/cms/issues/7990) by @jasonvarga
- Bard content gets dedicated class name for styling. [#7997](https://github.com/statamic/cms/issues/7997) by @jacksleight

### What's fixed
- Date field handling and improvements. [#7955](https://github.com/statamic/cms/issues/7955) [#7974](https://github.com/statamic/cms/issues/7974) by @jasonvarga
- Fix processing completely `null` date fieldtype values. [#7953](https://github.com/statamic/cms/issues/7953) by @jacksleight
- Fix blueprint error when section is missing fields. [#7994](https://github.com/statamic/cms/issues/7994) by @SylvesterDamgaard
- Fix select field option positioning. [#7988](https://github.com/statamic/cms/issues/7988) by @jasonvarga
- Fix error when using Bard's view source button. [#7987](https://github.com/statamic/cms/issues/7987) by @jasonvarga
- Revert stopping propagation of Popover clicks. [#7981](https://github.com/statamic/cms/issues/7981) by @jasonvarga
- Misc UI fixes. [#7978](https://github.com/statamic/cms/issues/7978) by @jackmcdade
- Fix Live Preview UI. [#7977](https://github.com/statamic/cms/issues/7977) by @jackmcdade
- Select and Color fieldtype fixes. [#7973](https://github.com/statamic/cms/issues/7973) by @jasonvarga
- Fix field conditions UI. [#7957](https://github.com/statamic/cms/issues/7957) by @jackmcdade
- Fix asset grid UI. [#7943](https://github.com/statamic/cms/issues/7943) by @jasonvarga
- Remove unnecessary tabindex target. [#7945](https://github.com/statamic/cms/issues/7945) by @jackmcdade
- Fix business mode buttons. [#7944](https://github.com/statamic/cms/issues/7944) by @jackmcdade
- Fix OAuth login styles. [#7942](https://github.com/statamic/cms/issues/7942) by @jackmcdade
- Import oauth controller in web routes. [#7941](https://github.com/statamic/cms/issues/7941) by @simonolog
- Fix Bard fullscreen mode styling. [#7938](https://github.com/statamic/cms/issues/7938) by @jasonvarga
- Fix z-index overlap issue when replicator display label is hidden. [#8002](https://github.com/statamic/cms/issues/8002) by @jackmcdade
- Brought over changes from 3.4

### What's changed
- Entry date behavior is based on the blueprint field.
- Entries in non-dated collections cannot have the date set on them.



## 4.0.0-alpha.4 (2023-04-17)

### What's improved
- Date and Time fieldtype improvements. [#7753](https://github.com/statamic/cms/issues/7753)
- Move column customizer into a modal for better long list management. [#7905](https://github.com/statamic/cms/issues/7905)

### What's fixed
- Fix focal point editor offset issue. [#7930](https://github.com/statamic/cms/issues/7930)
- Fix Bard fullscreen mode. [#7927](https://github.com/statamic/cms/issues/7927)
- Fix set handle not synced with display. [#7912](https://github.com/statamic/cms/issues/7912)
- Fix a couple of `time` fieldtype issues. [#7903](https://github.com/statamic/cms/issues/7903)
- Misc UI fixes. [#7911](https://github.com/statamic/cms/issues/7911)
- More Misc fixes. [#7907](https://github.com/statamic/cms/issues/7907)

### What's changed
- GraphQL and REST API filters are now opt-in. [#7717](https://github.com/statamic/cms/issues/7717)



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
