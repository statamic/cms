# Release Notes

## 3.0.9 (2020-09-16)

### What's new
- Antlers now supports [dynamic array access](https://statamic.dev/antlers#dynamic-access) with a brand new (to Antlers) square bracket syntax. [#1983](https://github.com/statamic/cms/issues/1983) (thanks [@morhi](https://github.com/morhi)!)
- The cache tag can be now disabled. [#2328](https://github.com/statamic/cms/issues/2328)
- You can now set a separate database connection for users. [#2416](https://github.com/statamic/cms/issues/2416)
- Form emails now have full access to all global variables. [#2411](https://github.com/statamic/cms/issues/2411)
- Form fields in templates get placeholder attributes. [#2354](https://github.com/statamic/cms/issues/2354)

### What's improved
- The Spanish, Dutch, and French translations have been updated. [#2440](https://github.com/statamic/cms/issues/2440), [#2435](https://github.com/statamic/cms/issues/2435), [#2434](https://github.com/statamic/cms/issues/2434)
- When creating a field, the fieldtype title is used instead of uppercasing the handle. [#1662](https://github.com/statamic/cms/issues/1662)
- Improved the asset uploader drop zone. [#2358](https://github.com/statamic/cms/issues/2358)
- You now get a more helpful exception when using a non-existent collection in the entries fieldtype. [#2422](https://github.com/statamic/cms/issues/2422)
- We now prevent options from showing in the collection listing dropdown if you don't have permission to do the things. [#2412](https://github.com/statamic/cms/issues/2412)
- The Mail config utility area now looks better. [#2380](https://github.com/statamic/cms/issues/2380)

### What's fixed
- Fixed a password reset related error. [#1973](https://github.com/statamic/cms/issues/1973)
- Angle brackets can now be used in modifiers. [#2022](https://github.com/statamic/cms/issues/2022)
- The Markdown fieldtype's dark mode preview text is no longer dark on dark. {insert another Tom Haverford reference} [#2185](https://github.com/statamic/cms/issues/2185)
- Bard's fixed toolbar is now shown when fullscreen and source are disabled, as you'd expect. [#2280](https://github.com/statamic/cms/issues/2280)
- Fixed the asset rename warning translation. [#2329](https://github.com/statamic/cms/issues/2329)
- Fixed the "Pro Mode" text in the Getting Started widget. [#2433](https://github.com/statamic/cms/issues/2433)
- Prevent an error on the entry list when you reference a non-existent user. [#2410](https://github.com/statamic/cms/issues/2410)
- Passing a zero into a tag parameter that expects a number will now use the zero instead of falling back to a one. Weird one. Or weird zero I guess.


## 3.0.8 (2020-09-15)

### What's new
- The Save/Publish button now supports "After Saving" options! You can choose to go to back to the listing, stay and edit, or create another and it will remember your last chosen option next time. [#675](https://github.com/statamic/cms/issues/675)

### What's improved
- The Getting Started widget now explains Pro Mode, and no longer references the beta. [#2402](https://github.com/statamic/cms/issues/2402)
- The French and German translations have been updated.
- The Select field now supports **max items**. [#1771](https://github.com/statamic/cms/issues/1771)
- The Range field has a smarter, configurable default that accounts for the `step` option. [#2328](https://github.com/statamic/cms/issues/2328)
- The Replicator field now looks better when underneath a Section field. [#2375](https://github.com/statamic/cms/issues/2375)

### What's fixed
- Bard's floating toolbar no longer keeps the table icons in the dark. Dark mode is one thing, but dark on dark is Tom Haverford-level silly. [#2189](https://github.com/statamic/cms/issues/2189)
- Bard's overaggressive focus outlines on Safari have been surgically removed. [#2188](https://github.com/statamic/cms/issues/2188)
- The publish sidebar will no longer collapse like a bully shoving a skinny kid into their locker.
- Section fieldtypes are no longer included in listings. [#2425](https://github.com/statamic/cms/issues/2425)
- Global search shows the collection/taxonomy name again. Sorry about that regression — at least it was cosmetic! [#2332](https://github.com/statamic/cms/issues/2332)




## 3.0.7 (2020-09-08)

### What's fixed
- Fix more instances of [#2369](https://github.com/statamic/cms/issues/2369)
- The scope modifier supports collections, not just arrays.
- Pagination in tags now re-keys the values. Fixes an issue where you might get no results when you aren't on the first page.



## v3.0.6 (2020-09-07)

### What's new
- Improved multisite Glide support. [#2379](https://github.com/statamic/cms/issues/2379)
- Added a GlideImageGenerated event. [#2160](https://github.com/statamic/cms/issues/2160)
- The Glide tag will return the item's original URL if it's not resizable (like an svg). [#2122](https://github.com/statamic/cms/issues/2122)

### What's fixed
- Fixed more of that issue from 3.0.4. [#2369](https://github.com/statamic/cms/issues/2369)
- Adjust the margin in the section fieldtype. [#2154](https://github.com/statamic/cms/issues/2154)
- Fix an issue where using a modifier on an array of augmentables (e.g. entries or assets), nothing would be output.


## v3.0.5 (2020-09-04)

### What's fixed
- Fix an issue introduced in 3.0.4 where using a Collection would cause an error.



## v3.0.4 (2020-09-04)

### What's new
- Revamped the validation builder you see when editing a field in a Blueprint or Fieldset.
- The `trans` tag accepts a `locale` parameter if you want to be explicit, just like the `trans()` helper.

### What's fixed
- The table fieldtype is usable inside Replicator. [#1447](https://github.com/statamic/cms/issues/1447)
- The search results tag will now filter results by the current site by default. [#2343](https://github.com/statamic/cms/issues/2343)
- It'll also filter by published results by default. [#2268](https://github.com/statamic/cms/issues/2268)
- Tag pairs using the `scope` modifier have access to cascading variables. [#1550](https://github.com/statamic/cms/issues/1550)
- Using the `where` modifier no longer removes access to cascading variables. [#2224](https://github.com/statamic/cms/issues/2224)
- Fix error when using the `scope` modifier on a Grid fieldtype. [#2250](https://github.com/statamic/cms/issues/2250)



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
