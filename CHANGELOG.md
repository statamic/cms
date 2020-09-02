# Release Notes

## v3.0.3 (2020-09-02)

### What's new
- Bard now has an option to always show the "Add Set" button.

### What's improved
- Widespread accessibility improvements through `aria` attributes and matching form labels+IDs
- The Array fieldtype is now full width by default. It makes it look better more of the time. [#2315](https://github.com/statamic/cms/issues/2315)
- Filter badges are no longer forced to lowercase for selfish aesthetic purposes. There are many legitimate cases for case sensitivity. [#2219](https://github.com/statamic/cms/issues/2219)
- A bunch of form improvements. Fields are loopable, values are augmented like in entries, submission index and show views are prettier, and more. [#2326](https://github.com/statamic/cms/pull/2326)

### What's fixed
- The link tag now properly prefixes URLs with the current site base url. [#2317](https://github.com/statamic/cms/issues/2317)
- Super long Select field values no longer spill out of the box like when you have too much spaghetti in your back pocket and sit down. 🍝 [#2324](https://github.com/statamic/cms/issues/2324)
- Non-reorderable Grid rows can now be deleted, as one would expect. [#2306](https://github.com/statamic/cms/issues/2306)
- A global variable named `title` will be used in templates, rather than the title of the set itself. [#2329](https://github.com/statamic/cms/issues/2329)
- PHP files can no longer be uploaded to asset containers.

## v3.0.2 (2020-08-27)

### What's new
- Parent field is localizable by default. [#2211](https://github.com/statamic/cms/issues/2211)
- Site selector when reordering entries.

### What's fixed
- Prevent overwriting the entry variable. Prevents title, slug, parent, etc from incorrectly falling back to the root value. [#2211](https://github.com/statamic/cms/issues/2211)
- Fix a "does not exist in structure" error when localizing a page. [#2176](https://github.com/statamic/cms/issues/2176)
- The "Visit URL" button is updated when you change sites, or update the slug. [#1864](https://github.com/statamic/cms/issues/1864)
- Fix an error when switching sites when creating an entry. [#2261](https://github.com/statamic/cms/issues/2261)
- Fix entry reordering when using multiple sites. [#1869](https://github.com/statamic/cms/issues/1869)
- Fix select fieldtype not rendering when you have numeric options/values. [#2302](https://github.com/statamic/cms/issues/2302)
- The `wrap` modifier only wraps if there's something to wrap. [#2299](https://github.com/statamic/cms/issues/2299)
- Fix missing breadcrumb. [#2236](https://github.com/statamic/cms/issues/2236)
- Section fieldtype shouldn't be localizable. [#2236](https://github.com/statamic/cms/issues/2236)
- Fix facade IDE typehint [#2297](https://github.com/statamic/cms/issues/2297)
- Adjust contrast on some UI elements.

## v3.0.1 (2020-08-26)

### What's new
- Localizable field toggle. [#2045](https://github.com/statamic/cms/issues/2045)
- The `form` tags get a `submission_created` boolean. [#2285](https://github.com/statamic/cms/issues/2285)
- The `template` fieldtype will ignore views in the `partials` directory when `hide_partials` is enabled. [#2249](https://github.com/statamic/cms/issues/2249)
- The "first child" option is only in `link` fieldtypes if the entry is in a structured collection. [#2209](https://github.com/statamic/cms/issues/2209)
- A Blueprint's `parent` will be the Collection/Taxonomy when creating an Entry/Term.
- Collection view mode button tooltips. [#2241](https://github.com/statamic/cms/issues/2241)
- PHP short tags will be sanitized in Antlers templates.

### What's fixed
- Vuex store gets the site when creating entries. [#2237](https://github.com/statamic/cms/issues/2237)
- Entry locale defaults to the default site. [#2275](https://github.com/statamic/cms/issues/2275)
- Entry inherits its layout from an origin entry, if one exists. [#1830](https://github.com/statamic/cms/issues/1830)
- Global site selector is scrollable. [#1838](https://github.com/statamic/cms/issues/1838)
- Rogue closing tag removed. [#2253](https://github.com/statamic/cms/issues/2253)
- The `FormSubmitted` event gets a `submission` property. [#2271](https://github.com/statamic/cms/issues/2271)
- Images are inline in Replicator previews. [#2267](https://github.com/statamic/cms/issues/2267)
- Addon thumbnail alignment. [#2272](https://github.com/statamic/cms/issues/2272)
- Simplify how our custom cache store creates paths. Fixes a Windows pathing issue. [#952](https://github.com/statamic/cms/issues/952)
- Fix shrunken toggle. [#2170](https://github.com/statamic/cms/issues/2170)
- Translations. [#2282](https://github.com/statamic/cms/issues/2282) [#2256](https://github.com/statamic/cms/issues/2256)



## v3.0.0 (2020-08-19)

### Statamic 3 is Official! 🎉
The day has finally come. Statamic 3 is out of beta and into the wild!

**Learn more in our [launch announcement](https://statamic.com/blog/statamic-3-launch-announcement)**
