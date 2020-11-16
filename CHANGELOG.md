# Release Notes

## 3.0.27 (2020-11-12)

### What's fixed
- Fixed some issues around asset caching. [#2831](https://github.com/statamic/cms/issues/2831) [#2840](https://github.com/statamic/cms/issues/2840)



## 3.0.26 (2020-11-10)

### What's improved
- Asset browsing has been given some performance improvements when dealing with large amounts of assets. [#2828](https://github.com/statamic/cms/issues/2828)
- The `embed_url` modifier load embeds without cookies from Vimeo or YouTube. [#2820](https://github.com/statamic/cms/issues/2820)
- Increase scannability of the fieldtype picker. [statamic/ideas#384](https://github.com/statamic/ideas/issues/384)

### What's fixed
- Fixed some issues with nav and breadcrumb tags on multisite. [#2269](https://github.com/statamic/cms/issues/2269)
- Fix legacy bard data not displaying in listings. [13b70fa79](https://github.com/statamic/cms/commit/13b70fa79)
- Fix an issue where Bard text doesn't show up in listings sometimes. [ab4194c88](https://github.com/statamic/cms/commit/ab4194c88)



## 3.0.25 (2020-11-06)

### What's new
- Field names in form validation can be translated. [#2764](https://github.com/statamic/cms/issues/2764)
- Added an Indonesian translation [#2426](https://github.com/statamic/cms/issues/2426)
- The `assets` tag can search by collection, and fields, and filter by type. [#2716](https://github.com/statamic/cms/issues/2716)

### What's improved
- French, German, and Danish translations have been updated. [#2808](https://github.com/statamic/cms/issues/2808) [#2804](https://github.com/statamic/cms/issues/2804) [#2809](https://github.com/statamic/cms/issues/2809)
- The name is passed along when creating custom search index drivers. [#2781](https://github.com/statamic/cms/issues/2781)
- The `search:update` command defaults to `all` so you can just hit enter. [a0c7ad908](https://github.com/statamic/cms/commit/a0c7ad908)
- Exceptions thrown within modifiers will now show the real exception in the stack trace. [0c443f751](https://github.com/statamic/cms/commit/0c443f751)
- The `text` fieldtype will save integers instead of strings when `input_type` is `number`. [#2708](https://github.com/statamic/cms/issues/2708)

### What's fixed
- Fix shallow augmentation for nested relations. [#2801](https://github.com/statamic/cms/issues/2801)
- Fix a recursion issue in Bard that made the page hang. [#2805](https://github.com/statamic/cms/issues/2805)
- Prevent an error when adding a new set in Bard or Replicator. [f9c448d1c](https://github.com/statamic/cms/commit/f9c448d1c)
- Prevent an error when there's a Replicator set without any fields. [402feb229](https://github.com/statamic/cms/commit/402feb229)
- Fix issue where only the last field's value of a Replicator is shown in the preview. [a5fd579f4](https://github.com/statamic/cms/commit/a5fd579f4)
- Fix a weird table shrinky overflowy UI thing.  [#2595](https://github.com/statamic/cms/issues/2595)
- Localized entries get appropriate fallback values placed in the search index. [#2789](https://github.com/statamic/cms/issues/2789) [88b245b](https://github.com/statamic/cms/commit/88b245b)
- Fix the `sum` modifier not being able to handle `Value` objects. [#2703](https://github.com/statamic/cms/issues/2703)
- Prevent `select` fields with lots of text from overflowing. [#2702](https://github.com/statamic/cms/issues/2702)
- Prevent blueprint fields with long labels from overflowing. [#2673](https://github.com/statamic/cms/issues/2673)



## 3.0.24 (2020-11-04)

### What's new
- Added Stache Locking. Reduces resource spikes on busy sites while building the cache. [#2794](https://github.com/statamic/cms/issues/2794)
- Added an `entriesCount` method to taxonomy terms which lets us perform a more efficient count query. This speeds up Stache build time on taxonomy-heavy sites. [#2792](https://github.com/statamic/cms/issues/2792)

### What's improved
- Added some missing translations. [36d973eb](https://github.com/statamic/cms/commit/36d973eb)

### What's fixed
- Fixed a typo in a Dutch translation. [#2796](https://github.com/statamic/cms/issues/2796)



## 3.0.23 (2020-11-01)

### What's new
- Replicator can configure it's set collapsing behavior. Everything by default [#2771](https://github.com/statamic/cms/issues/2771), or accordion style [979daebec](https://github.com/statamic/cms/commit/979daebec).
- Static caching supports invalidaton by Navigation and Global. [#2778](https://github.com/statamic/cms/issues/2778)

### What's improved
- Radio fieldtype labels are shown in listings instead of just the values. [#2731](https://github.com/statamic/cms/issues/2731)
- Add Slovene translation. [#2777](https://github.com/statamic/cms/issues/2777)
- Improve Replicator and Bard performance when collapsing sets. [#2787](https://github.com/statamic/cms/issues/2787)

### What's fixed
- Fixed field conditions in Grids and imported fieldsets with prefixes. [#2767](https://github.com/statamic/cms/issues/2767)
- Only look up addon editions if they're installed. [#2782](https://github.com/statamic/cms/issues/2782)
- Taggable and Relationship fieldtype in select mode is reorderable by drag and drop. [#2059](https://github.com/statamic/cms/issues/2059)
- Fix a few instances of title, status, etc not being updated in the UI appropriately. [#1822](https://github.com/statamic/cms/issues/1822)
- Prevent select fields from converting booleans when used as config field. [b2a425079](https://github.com/statamic/cms/commit/b2a425079)
- Prevent an overzealous blink cache clear. [818c4fdc4](https://github.com/statamic/cms/commit/818c4fdc4)



## 3.0.22 (2020-10-29)

### What's fixed
- Addon directories should include a trailing slash. [seo-pro#140](https://github.com/statamic/seo-pro/issues/140)



## 3.0.21 (2020-10-28)

### What's improved
- Addons can get their directory without needing it in the manifest. [#2761](https://github.com/statamic/cms/issues/2761)
- Structure tree entries get eager loaded. [#2573](https://github.com/statamic/cms/issues/2573)
- Composer 2 is used within the control panel. [facca2693](https://github.com/statamic/cms/commit/facca2693)
- `Str::isUrl()` checks more URLs. [#2759](https://github.com/statamic/cms/issues/2759)
- Dutch translation has been updated. [#2754](https://github.com/statamic/cms/issues/2754)
- The Entry facade docblock has been updated. [#2720](https://github.com/statamic/cms/issues/2720)
- The `@svg` Blade directive is only registered on CP routes. Prevents conflicts with things like Blade UI Kit. [99e812e6c](https://github.com/statamic/cms/commit/99e812e6c)
- The `shuffle` modifier works for Collections. [#2709](https://github.com/statamic/cms/issues/2709)
- The `.idea` directory is git ignored, and we now require `ext-json`, which improves the experience for PhpStorm users. [#2735](https://github.com/statamic/cms/issues/2735)

### What's fixed
- Fix how data gets passed into `*recursive var*` in the parser and structure tag. [#2719](https://github.com/statamic/cms/issues/2719)
- Addon views are registered only if the views directory exists. [#2707](https://github.com/statamic/cms/issues/2707)
- Actions can return any type of `Request` for downloads, like a `StreamedResponse`. [#2738](https://github.com/statamic/cms/issues/2738)
- Update some JS dependencies to patch security issues. [49e4ce819](https://github.com/statamic/cms/commit/49e4ce819) [c290a86ec](https://github.com/statamic/cms/commit/c290a86ec)
- Prevent an error when using the `entries` fieldtype in a non entry (e.g. a user) [8ede3718b](https://github.com/statamic/cms/commit/8ede3718b)
- Prevent making addon instances multiple times [d4ce47099](https://github.com/statamic/cms/commit/d4ce47099)
- Fixed a PSR-4 incompatible test. [#2734](https://github.com/statamic/cms/issues/2734)



## 3.0.20 (2020-10-20)

### What's new
- Added a `smartypants` modifier and `Html::smartypants()` method. [#2689](https://github.com/statamic/cms/issues/2689)

### What's improved
- Danish and Dutch translations have been updated. [#2693](https://github.com/statamic/cms/issues/2693) [#2691](https://github.com/statamic/cms/issues/2691)

### What's fixed
- A bunch of taxonomy cache fixes. Listed below for good measure. [#2686](https://github.com/statamic/cms/issues/2686)
- Prevent taxonomy terms hanging around after you delete them. [#1349](https://github.com/statamic/cms/issues/1349)
- Prevent taxonomy terms showing the slug instead of the title. [#1982](https://github.com/statamic/cms/issues/1982)
- Prevent an undefined offset error when creating terms. [#2020](https://github.com/statamic/cms/issues/2020)
- Entry-term assocation is actually removed when expected. [#1870](https://github.com/statamic/cms/issues/1870)
- Prevent terms being displayed as the ID when you create new ones on an entry.
- An empty taxonomy parameter no longer tries to filter. [#2672](https://github.com/statamic/cms/issues/2672)
- Entries and Terms fieldtypes will show all results in select mode (instead of just the first paginated page). [#1727](https://github.com/statamic/cms/issues/1727)
- Entries fieldtype will show localized entries in select and typehead modes. [#1835](https://github.com/statamic/cms/issues/1835)
- Prevent changing publish status from wiping out the origin. [#2451](https://github.com/statamic/cms/issues/2451)
- YAML content should be null if it's just whitespace [#2677](https://github.com/statamic/cms/issues/2677)
- Delete Eloquent user through the repository. [da9335936](https://github.com/statamic/cms/commit/da9335936) [#2697](https://github.com/statamic/cms/issues/2697)
- Fixed variable name in down migration [#2676](https://github.com/statamic/cms/issues/2676)
- Updated docs urls [898889ce5](https://github.com/statamic/cms/commit/898889ce5)



## 3.0.19 (2020-10-15)

### What's improved
- The French translation has been updated. [#2664](https://github.com/statamic/cms/issues/2664)

### What's fixed
- Prevent exception when an invalid or outdated entry is selected in an entries fieldtype. [#2660](https://github.com/statamic/cms/issues/2660)
- Fix a handful of Antlers conditional issues. [#2663](https://github.com/statamic/cms/issues/2663) [#1193](https://github.com/statamic/cms/issues/1193) [#2614](https://github.com/statamic/cms/issues/2614) [#2537](https://github.com/statamic/cms/issues/2537) [#2456](https://github.com/statamic/cms/issues/2456)



## 3.0.18 (2020-10-14)

### What's new
- Added an [`md5` modifier](https://statamic.dev/modifiers/md5). [#2652](https://github.com/statamic/cms/issues/2652)

### What's fixed
- Form validation errors are translated appropriately. [#2387](https://github.com/statamic/cms/issues/2387)
- Form emails are localized based on the site where they were submitted. [#2658](https://github.com/statamic/cms/issues/2658)
- The `entries` fieldtype will localize its selections in views based on the locale. [#2657](https://github.com/statamic/cms/issues/2657)
- The entry gets passed along in more places, fixing some issues with Replicator, Grid, and Bard. [#2656](https://github.com/statamic/cms/issues/2656)



## 3.0.17 (2020-10-13)

### What's new
- Added a `find` method to the query builder. [#2630](https://github.com/statamic/cms/issues/2630)
- Added a `current_full_url` variable that includes the query string. [#2638](https://github.com/statamic/cms/issues/2638)
- Added a bunch of query parameter related modifiers. [#2638](https://github.com/statamic/cms/issues/2638)

### What's improved
- Deleting entries when using multiple sites will give you options on how to handle localizations. [#2623](https://github.com/statamic/cms/issues/2623)
- When you have a huge bunch of assets, it would cause search indexing while saving entries to be slow. Now it's fast. [#2643](https://github.com/statamic/cms/issues/2643)
- Added `cast_booleans` configuration option to Radio fieldtype [#2601](https://github.com/statamic/cms/issues/2601)
- Listing Vue component is available globally [#2602](https://github.com/statamic/cms/issues/2602)
- Portuguese and Danish translations have been updated.
- Popper.js has been updated. [#2622](https://github.com/statamic/cms/issues/2622)
- The translator generate command recognizes annotations with single asterisks. [8f778d0](https://github.com/statamic/cms/commits/8f778d0)
- Static caching strategy is settable in the `.env` file. [#2648](https://github.com/statamic/cms/issues/2648)

### What's fixed
- Context is provided to the nav tag recursively. [#2610](https://github.com/statamic/cms/issues/2610)
- Template front-matter works across operating systems. [#2607](https://github.com/statamic/cms/issues/2607)
- Partial is used on the licensing page. [#2620](https://github.com/statamic/cms/issues/2620)
- Fixed an error when localizating the root entry in a structured collection. [c9f0255fd](https://github.com/statamic/cms/commit/c9f0255fd)
- Updating an Algolia index will flush it rather than deleting it. [#2645](https://github.com/statamic/cms/issues/2645)
- Password reset errors are now shown (and in the right positions). [#2618](https://github.com/statamic/cms/issues/2618)
- Passing `true` and `false` into API filters will now be treated as booleans. [#2640](https://github.com/statamic/cms/issues/2640)
- Falsey HTML attributes are stripped out. True attributes actually say true. Useful for aria attributes on svg tags. [#2605](https://github.com/statamic/cms/issues/2605)



## 3.0.16 (2020-10-06)

### What's new
- Added a `key` parameter to the [`cache` tag](https://statamic.dev/tags/cache). [#2589](https://github.com/statamic/cms/issues/2589)
- Search indexes can define [transformers](https://statamic.dev/search#transforming-fields). [#2462](https://github.com/statamic/cms/issues/2462)

### What's improved
- The `locale` method on the `Entry` class can accept a `Site` object.
- The `toggle` fieldtype can understand `0` and `1` in your YAML files, rather than just `true` and `false`.
- French translation has been updated. [#2591](https://github.com/statamic/cms/issues/2591)

### What's fixed
- Entries are placed appropriately into a collection's structure when localizing them. [#2471](https://github.com/statamic/cms/issues/2471)
- The `date` method on the `Entry` class checks for `Carbon\Carbon`, rather than `Illuminate\Support\Carbon`.
- The subrequest is passed along to the cascade in Live Preview, which fixes things like `segment_x` variables using the CP URL.



## 3.0.15 (2020-10-05)

### What's new
- Laravel 8 is now supported. [#2547](https://github.com/statamic/cms/issues/2547)

### What's improved
- You can override repositories using `Statamic::repository()` which stops service provider load order being a factor.
- The entry repository uses a container binding for the query builder to make extending simpler.
- Added a query builder contract which the parser will check for instead of a concrete class.
- The Eloquent query builder is more consistent with other query builder classes.
- Taxonomy related entry query builder methods have been extracted into a trait to ease reusability.
- Improve performance related to noticing collection YAML file changes. [#2572](https://github.com/statamic/cms/issues/2572)
- Portuguese, Danish, and German translations have been updated. [#2583](https://github.com/statamic/cms/issues/2583) [#2587](https://github.com/statamic/cms/issues/2587) [#2588](https://github.com/statamic/cms/issues/2588)

### What's fixed
- Fix handling of taxonomy routes when they have multiple words. [#2273](https://github.com/statamic/cms/issues/2273)
- Setting `create: false` on a `terms` field will actually prevent you from being able to enter new terms. [#2453](https://github.com/statamic/cms/issues/2453)
- Prevent seeing validation rules multiple times times. [#2582](https://github.com/statamic/cms/issues/2582)
- If you have permission to view form submissions, you have permission to export them. [#2577](https://github.com/statamic/cms/issues/2577)
- The unique slug validation rule works when your entry IDs are integers.
- Pages can define entries by passing integer IDs, instead of just strings (UUIDs).



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
