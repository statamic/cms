# Release Notes

## 3.0.14 (2020-09-30)

### What's improved
- Prevent the asset browser's bulk action toolbar pushing everything down in grid mode.
- You can define a fieldset's handle when creating it. [#1717](https://github.com/statamic/cms/issues/1717)
- Added a `down` method to the auth migration. [#2546](https://github.com/statamic/cms/issues/2546)
- Increase performance of blueprint lookups. [#2552](https://github.com/statamic/cms/issues/2552)
- Entry and Term static cache URLs will be invalidated when they're deleted. [#2393](https://github.com/statamic/cms/issues/2393)
- Fix the listings' "check all" checkbox position.
- The `user:forgot_password_form` tag plays nicer with other forms on the page.
- A bunch of translations have been updated.

### What's fixed
- Stop forcing the title field to the start. [#2536](https://github.com/statamic/cms/issues/2536)
- Fix position of Bard toolbar inside a Stack. [#1911](https://github.com/statamic/cms/issues/1911)
- The translator tool will no longer null out nested arrays. [#2544](https://github.com/statamic/cms/issues/2544)
- Fixed the `success` variable on the `user:forgot_password_form` tag. [#1777](https://github.com/statamic/cms/issues/1777)
- Fixed the `mount` variable in routes sometimes doubling up or being for the wrong site. [#2569](https://github.com/statamic/cms/issues/2569)
- Match the "discovered addon" color to Laravel's "discovered package" color in the `statamic:install` output.



## 3.0.13 (2020-09-25)

### What's new
- The partial tag now supports [slots](https://statamic.dev/tags/partial#slots).

### What's improved
- The preview area of the asset editor has been improved for SVGs.
- The get_content tag has been given a little performance boost.
- French translations have been updated. [#2504](https://github.com/statamic/cms/issues/2504)
- Improve the UX of defining field conditions.

### What's fixed
- Status icons update when saving entries without needing to refresh. [#1822](https://github.com/statamic/cms/issues/1822)
- Fixed entries in a structured collection (i.e. pages) ignoring content protection. [#2526](https://github.com/statamic/cms/issues/2526)
- A markdown field with no value will be treated that way, rather than as an empty string. [#2503](https://github.com/statamic/cms/issues/2503)
- Actions with redirects now actually redirect. [#1946](https://github.com/statamic/cms/issues/1946)
- Action confirmation modals remain open on failures. [#1576](https://github.com/statamic/cms/issues/1576)
- Bard will now render strikethrough elements. [#2517](https://github.com/statamic/cms/issues/2517)
- Bumped html-to-prosemirror and prosemirror-to-html packages. 
- Bumped lodash version [#2089](https://github.com/statamic/cms/issues/2089)



## 3.0.12 (2020-09-22)

### What's improved
- Added labels to scaffolding checkboxes. [#2488](https://github.com/statamic/cms/issues/2488)
- French and Portuguese translations have been updated. [#2493](https://github.com/statamic/cms/issues/2493) [#2474](https://github.com/statamic/cms/issues/2474)
- Yo dawg, I heard you like HTML, so the HTML fieldtype gets an HTML field so you can write HTML to display as HTML.

### What's fixed
- Removed the handle field in the navigation edit form. [#1959](https://github.com/statamic/cms/issues/1959)
- Fixed the missing icons when you add new fields. [#1959](https://github.com/statamic/cms/issues/1959)
- Long links in Bard get wrapped. [#1814](https://github.com/statamic/cms/issues/1814)
- Asset upload instructions actually explain asset uploads. [#1686](https://github.com/statamic/cms/issues/1686)
- Invalid dates no longer cause an error. [#2038](https://github.com/statamic/cms/issues/2038)
- SVGs get rendered in the asset editor modal. [#2484](https://github.com/statamic/cms/issues/2484)
- Fixed some breadcrumb links. [#2475](https://github.com/statamic/cms/issues/2475)
- The YAML fieldtype is now actually read only when it needs to be, instead of just saying it is. [#2082](https://github.com/statamic/cms/issues/2082)
- Upgraded the Pickr library, which stops the color fieldtype dropping off the page. [#2110](https://github.com/statamic/cms/issues/2110)
- The Revealer fieldtype's label is hidden inside Replicator. [#2468](https://github.com/statamic/cms/issues/2468)
- The `has` method on data classes like entries will return `true` if it has a value of `false` or `null`.
- The submission class sets its data and supplements properties to collections.



## 3.0.11 (2020-09-21)

### What's new
- Ability to remove a blueprint section programmatically. [#2491](https://github.com/statamic/cms/issues/2491)
- You can pass a variable into the `nav` tag's `from` parameter and it will make sure it has a slash, allowing you to do `:from="segment_1"`.

### What's fixed
- Fixed a protection related error on taxonomy URLs. [#2472](https://github.com/statamic/cms/issues/2472) [#2481](https://github.com/statamic/cms/issues/2481)
- Relationship tags filter out invalid IDs when augmenting. [#1752](https://github.com/statamic/cms/issues/1752)
- The `nav:breadcrumbs` tag supports multi-site. [#1807](https://github.com/statamic/cms/issues/1807) [#2487](https://github.com/statamic/cms/issues/2487)
- Prevent `nav from="/"` returning nothing. [#1683](https://github.com/statamic/cms/issues/1683) [#1542](https://github.com/statamic/cms/issues/1542)
- Fixed pluralisation issue. [#1695](https://github.com/statamic/cms/issues/1695)
- Fixed an error when adding a Bard set. [#1718](https://github.com/statamic/cms/issues/1718)
- Entries' `order` variables are available in templates.



## 3.0.10 (2020-09-18)

### What's improved
- German, French, and Portuguese translations have been updated. [#2445](https://github.com/statamic/cms/issues/2445) [#2444](https://github.com/statamic/cms/issues/2444) [#2458](https://github.com/statamic/cms/issues/2458)
- When an asset search returns no results it says "No results" instead of "This container is empty".
- Asset container handle are generated using snake_case as you type the title.
- The "and" the sentence_list modifier is translated. [#2463](https://github.com/statamic/cms/issues/2463)

### What's fixed
- Actions that don't want to be confirmed... won't be. [#1497](https://github.com/statamic/cms/issues/1497) [#2446](https://github.com/statamic/cms/issues/2446)
- Assets can be searched in grid mode. [#2318](https://github.com/statamic/cms/issues/2318) [#2442](https://github.com/statamic/cms/issues/2442)
- User avatars can be output in templates. [#2017](https://github.com/statamic/cms/issues/2017)
- The glide:generate tag uses the appropriate generation methods.
- Resolved an error in a taxonomy term listing when using a terms field on another term. [#2307](https://github.com/statamic/cms/issues/2307)
- Fix an issue where the sort modifier would output nothing. [#2450](https://github.com/statamic/cms/issues/2450)
- Prevent the tree from disappearing when switching from list view. [#2408](https://github.com/statamic/cms/issues/2408)

### What's removed
- "Angle brackets can now be used in modifiers" from 3.0.9 has been reverted for now. [#2022](https://github.com/statamic/cms/issues/2022)



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
