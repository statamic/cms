# Release Notes

## 4.58.2 (2024-05-09)

### What's fixed
- Fix Eloquent User `notifications` conflict. [#10024](https://github.com/statamic/cms/issues/10024) by @duncanmcclean
- Fix EloquentQueryBuilder orderby bug. [#10023](https://github.com/statamic/cms/issues/10023) by @duncanmcclean



## 4.58.1 (2024-05-08)

### What's improved
- French translations. [#10017](https://github.com/statamic/cms/issues/10017) by @PatrickJunod

### What's fixed
- Fix queue worker state issue around assets. [#9690](https://github.com/statamic/cms/issues/9690) by @aerni
- Fix button group wrapping on non-fieldtype buttons. [#10021](https://github.com/statamic/cms/issues/10021) by @PatrickJunod
- Fix child URIs being outdated when parent slug changes. [#9454](https://github.com/statamic/cms/issues/9454) by @jacksleight
- Fix Eloquent ordering when using `lazy()` or `chunk()`. [#9956](https://github.com/statamic/cms/issues/9956) by @helloiamlukas
- Fix recursive editing check. [#10012](https://github.com/statamic/cms/issues/10012) by @duncanmcclean



## 4.58.0 (2024-05-07)

### What's new
- Add Vite content tag. [#9973](https://github.com/statamic/cms/issues/9973) by @jimblue
- Include `alt` field in shallow augmented assets. [#10013](https://github.com/statamic/cms/issues/10013) by @duncanmcclean

### What's improved
- French translations. [#10015](https://github.com/statamic/cms/issues/10015) by @duncanmcclean

### What's fixed
- Fix ButtonGroup from overflowing. [#10000](https://github.com/statamic/cms/issues/10000) by @PatrickJunod
- Fix docblock on `Parse` facade's `template` method. [#10008](https://github.com/statamic/cms/issues/10008) by @duncanmcclean
- Ensure blueprint tabs & sections always have handles. [#10014](https://github.com/statamic/cms/issues/10014) by @duncanmcclean
- Fix JSON being returned when using the back button sometimes. [#10016](https://github.com/statamic/cms/issues/10016) by @duncanmcclean
- Fix fullscreen button in Group Fieldtype. [#10009](https://github.com/statamic/cms/issues/10009) by @duncanmcclean
- Remove clear button of readonly date field. [#9993](https://github.com/statamic/cms/issues/9993) by @PatrickJunod



## 4.57.3 (2024-05-02)

### What's improved
- German translations. [#9957](https://github.com/statamic/cms/issues/9957) by @helloDanuk

### What's fixed
- Fix Starter Kit installation on Windows. [#9968](https://github.com/statamic/cms/issues/9968) by @JohnathonKoster
- Fix `starter-kit:install` with custom branch when branch has slash. [#9978](https://github.com/statamic/cms/issues/9978) by @jesseleite
- Fix first child redirects when using array syntax. [#9965](https://github.com/statamic/cms/issues/9965) by @jasonvarga
- Fix wrong permission used for configuring navs. [#9961](https://github.com/statamic/cms/issues/9961) by @simonolog
- Fix `DataReferenceUpdater` when field data from array is null. [#9954](https://github.com/statamic/cms/issues/9954) by @duncanmcclean
- Fix dated collection listing when time is enabled. [#9951](https://github.com/statamic/cms/issues/9951) by @jacksleight
- Fix route parameters in Statamic routes with closures. [#9953](https://github.com/statamic/cms/issues/9953) by @arthurperton
- Fake composer installs in make command tests. [#9955](https://github.com/statamic/cms/issues/9955) by @jasonvarga



## 4.57.2 (2024-04-23)

### What's fixed
- Fix missing composer binary (starter kits couldn't install). [#9950](https://github.com/statamic/cms/issues/9950) by @duncanmcclean



## 4.57.1 (2024-04-22)

### What's improved
- French translations. [#9939](https://github.com/statamic/cms/issues/9939) by @ebeauchamps

### What's fixed
- Require composer/semver instead of composer/composer. [#9947](https://github.com/statamic/cms/issues/9947) by @jasonvarga
- Fix `DimensionsRule` for Livewire. [#9927](https://github.com/statamic/cms/issues/9927) by @aerni
- Fix Blade compiler error. [#9946](https://github.com/statamic/cms/issues/9946) by @duncanmcclean
- Prevent passing HTML to the Video fieldtype. [#9944](https://github.com/statamic/cms/issues/9944) by @duncanmcclean
- Fix missing OAuth controller argument. [#9942](https://github.com/statamic/cms/issues/9942) by @simonolog



## 4.57.0 (2024-04-17)

### What's new
- Add deleteQuietly methods. [#9666](https://github.com/statamic/cms/issues/9666) by @ryanmitchell
- Support using closures for Statamic route data. [#9868](https://github.com/statamic/cms/issues/9868) by @arthurperton

### What's fixed
- Fix single unpublished entries in listing column. [#9917](https://github.com/statamic/cms/issues/9917) by @jacksleight
- Prevent computed fields from being sortable in listing tables. [#9916](https://github.com/statamic/cms/issues/9916) by @duncanmcclean
- Fix Grid styles messing up row-controls of nested fields. [#9910](https://github.com/statamic/cms/issues/9910) by @duncanmcclean
- Fix icons color. [#9895](https://github.com/statamic/cms/issues/9895) by @peimn
- Fix Nova icon not displaying in Icon Fieldtype. [#9906](https://github.com/statamic/cms/issues/9906) by @duncanmcclean
- Add title fallback for roles & groups. [#9907](https://github.com/statamic/cms/issues/9907) by @duncanmcclean
- Fix Bard scrolling editor/page on link insert. [#9886](https://github.com/statamic/cms/issues/9886) by @jacksleight
- Hash API cache keys the key to handle long route and query parameters. [#9858](https://github.com/statamic/cms/issues/9858) by @Smef
- Fix regression on bard/replicator group set previews. [#9901](https://github.com/statamic/cms/issues/9901) by @caseydwyer
- Fix arrow direction in fav creator for RTL. [#9897](https://github.com/statamic/cms/issues/9897) by @peimn
- Fixes for asset/term reference updater strictness. [#9878](https://github.com/statamic/cms/issues/9878) by @jesseleite
- Add `date` to reserved fields for form blueprints. [#9872](https://github.com/statamic/cms/issues/9872) by @duncanmcclean
- Fix tables in Bard not saving updates. [#9867](https://github.com/statamic/cms/issues/9867) by @jacksleight
- Add tailwind safelist with horizontal margins and padding. [#9864](https://github.com/statamic/cms/issues/9864) by @jasonvarga
- Fix PHPUnit deprecations. [#9912](https://github.com/statamic/cms/issues/9912) by @jasonvarga



## 4.56.1 (2024-04-09)

### What's fixed
- Fix max depth validation on "Parent" field when collection has no max depth set. [#9850](https://github.com/statamic/cms/issues/9850) by @duncanmcclean
- Update URIs for mounted collection entries only if slug on mounted entry changed. [#9851](https://github.com/statamic/cms/issues/9851) by @marcorieser



## 4.56.0 (2024-04-08)

### What's new
- Bard hooks. [#9823](https://github.com/statamic/cms/issues/9823) by @jacksleight
- Ability to disable SVG sanitization on upload. [#9839](https://github.com/statamic/cms/issues/9839) by @duncanmcclean
- Add Edit Blueprint link to form page dropdown. [#9840](https://github.com/statamic/cms/issues/9840) by @jacksleight
- Track Laravel version in the Outpost. [#9820](https://github.com/statamic/cms/issues/9820) by @jasonvarga

### What's fixed
- Prevent recursive editing via relationship fieldtype. [#9841](https://github.com/statamic/cms/issues/9841) by @duncanmcclean
- Improve array fieldtype validation for dynamically keyed fields. [#9834](https://github.com/statamic/cms/issues/9834) by @jesseleite
- Fix overlapping set group & set name in Safari. [#9837](https://github.com/statamic/cms/issues/9837) by @duncanmcclean
- Arr::wrap in/notIn values. [#9833](https://github.com/statamic/cms/issues/9833) by @ryanmitchell
- Fix filter preset issues. [#9826](https://github.com/statamic/cms/issues/9826) by @duncanmcclean
- Ensure redirects work for localized entries. [#9819](https://github.com/statamic/cms/issues/9819) by @duncanmcclean
- Fix OAuth login when using independent auth guards. [#9816](https://github.com/statamic/cms/issues/9816) by @duncanmcclean
- Fix icons in preference save options. [#9827](https://github.com/statamic/cms/issues/9827) by @duncanmcclean
- Prevent "Permanently added the ECDSA host key for IP address" from being logged as a Git error. [#9828](https://github.com/statamic/cms/issues/9828) by @duncanmcclean
- Fix deleted sets breaking Bard & Replicator. [#9818](https://github.com/statamic/cms/issues/9818) by @duncanmcclean
- Prevent overwriting filter views. [#9792](https://github.com/statamic/cms/issues/9792) by @duncanmcclean
- Fix serializing entries when `slug` property is a closure. [#9791](https://github.com/statamic/cms/issues/9791) by @duncanmcclean
- Use the configured Git binary in commands. [#9793](https://github.com/statamic/cms/issues/9793) by @duncanmcclean
- Enforce max depth when validating entry parent. [#9799](https://github.com/statamic/cms/issues/9799) by @duncanmcclean
- Fix new lines not working in user activation email. [#9798](https://github.com/statamic/cms/issues/9798) by @duncanmcclean
- Hide "Create Entry" button when all collection blueprints are hidden. [#9744](https://github.com/statamic/cms/issues/9744) by @duncanmcclean
- Bump vite from 4.5.2 to 4.5.3 [#9821](https://github.com/statamic/cms/issues/9821) by @dependabot



## 4.55.0 (2024-03-27)

### What's new
- Add tags blade directive. [#9732](https://github.com/statamic/cms/issues/9732) by @Jade-GG
- Ability to provide additional urls for `static:warm`. [#9303](https://github.com/statamic/cms/issues/9303) by @ryanmitchell
- Enable configuration of full measure static cache permissions. [#9755](https://github.com/statamic/cms/issues/9755) by @ryanmitchell
- Listing page state gets added to URLs to become shareable. [#9408](https://github.com/statamic/cms/issues/9408) by @jacksleight
- Add `augmented` hooks. [#9625](https://github.com/statamic/cms/issues/9625) by @ryanmitchell

### What's improved
- French and English translations. [#9774](https://github.com/statamic/cms/issues/9774) by @ebeauchamps

### What's fixed
- Fix Revealer state issues when closing Live Preview. [#9797](https://github.com/statamic/cms/issues/9797) by @jesseleite
- Fix Bard IME input in Safari. [#9788](https://github.com/statamic/cms/issues/9788) by @jacksleight
- Fix Glide cache not clearing on image reupload if `append_original_filename` is enabled. [#9610](https://github.com/statamic/cms/issues/9610) by @daun
- Update entry parent index on collection tree save. [#9443](https://github.com/statamic/cms/issues/9443) by @jacksleight
- Support chunk on query builders in Antlers. [#9157](https://github.com/statamic/cms/issues/9157) by @ryanmitchell
- Fix return type of `AssetContainer:all()`. [#9777](https://github.com/statamic/cms/issues/9777) by @daun
- Avoid using a pipeline if there are no Hooks registered. [#9772](https://github.com/statamic/cms/issues/9772) by @jasonvarga
- Enable pro fix and improvements. [#9763](https://github.com/statamic/cms/issues/9763) by @jesseleite
- Flush entire `static_cache` cache store when running `static:clear`. [#9770](https://github.com/statamic/cms/issues/9770) by @duncanmcclean



## 4.54.0 (2024-03-21)

### What's new
- Collections etc are hidden when unavailable to currently selected site in the CP. [#9583](https://github.com/statamic/cms/issues/9583) by @pdipatrizio
- Add ability to install starter kit from specific branch. [#9766](https://github.com/statamic/cms/issues/9766) by @jesseleite
- Widgets can be restricted to specific sites. [#9600](https://github.com/statamic/cms/issues/9600) by @aerni
- Add Ukrainian translations. [#9750](https://github.com/statamic/cms/issues/9750) by @osbre
- Add direct link to preferences. [#9740](https://github.com/statamic/cms/issues/9740) by @jasonvarga

### What's improved
- Polish translations. [#9771](https://github.com/statamic/cms/issues/9771) by @PaperTurtle
- French translations. [#9736](https://github.com/statamic/cms/issues/9736) by @ebeauchamps
- Russian translations. [#9722](https://github.com/statamic/cms/issues/9722) by @dragomano
- Improve locale preference selector. [#9739](https://github.com/statamic/cms/issues/9739) by @jasonvarga
- Throw more helpful exception when invalid values are passed to `whereCollection`/`whereTaxonomy` methods. [#9751](https://github.com/statamic/cms/issues/9751) by @duncanmcclean

### What's fixed
- Fix 403 views not using error template. [#9768](https://github.com/statamic/cms/issues/9768) by @edalzell
- Fix field locking user avatar size. [#9761](https://github.com/statamic/cms/issues/9761) by @duncanmcclean
- Fix markdown and code fieldtype read-only modes. [#9764](https://github.com/statamic/cms/issues/9764) by @duncanmcclean
- Fix blueprint error when creating user. [#9276](https://github.com/statamic/cms/issues/9276) by @duncanmcclean
- Allow slashes in comb search queries. [#9754](https://github.com/statamic/cms/issues/9754) by @ryanmitchell
- Add entry and term empty view permission checks. [#9377](https://github.com/statamic/cms/issues/9377) by @jacksleight
- Ensure changes to "Parent" get saved when using revisions. [#9079](https://github.com/statamic/cms/issues/9079) by @duncanmcclean
- Replace Mix paths with Vite paths in `starter-kit.yaml` stub. [#9741](https://github.com/statamic/cms/issues/9741) by @duncanmcclean
- Fix `{{ nocache }}` tag when URL contains URL fragment. [#9742](https://github.com/statamic/cms/issues/9742) by @duncanmcclean
- Add missing DocBlocks to the `Markdown` facade. [#9746](https://github.com/statamic/cms/issues/9746) by @osbre
- Add missing config items. [#9734](https://github.com/statamic/cms/issues/9734) by @jasonvarga
- Fix stacked grid margin-top styling. [#9733](https://github.com/statamic/cms/issues/9733) by @jesseleite
- Ensure pagination is always displayed at the bottom of collection widget. [#9726](https://github.com/statamic/cms/issues/9726) by @duncanmcclean
- Bump follow-redirects from 1.15.4 to 1.15.6 [#9748](https://github.com/statamic/cms/issues/9748) by @dependabot



## 4.53.2 (2024-03-13)

### What's improved
- Persian translations. [#9711](https://github.com/statamic/cms/issues/9711) by @peimn

### What's fixed
- Fix "A field with a handle of X already exists" error when editing fieldsets. [#9718](https://github.com/statamic/cms/issues/9718) by @duncanmcclean
- Fix arrow direction in RTL. [#9712](https://github.com/statamic/cms/issues/9712) by @peimn



## 4.53.1 (2024-03-12)

### What's fixed
- Fix broken navigation tree. [#9709](https://github.com/statamic/cms/issues/9709) by @duncanmcclean



## 4.53.0 (2024-03-11)

### What's new
- Persian translation. [#9707](https://github.com/statamic/cms/issues/9707) by @peimn
- Japanese translation. [#9683](https://github.com/statamic/cms/issues/9683) by @kusaka-kouki
- Field based redirects may provide a status. [#9417](https://github.com/statamic/cms/issues/9417) by @ryanmitchell
- RTL support in the Control Panel. [#9447](https://github.com/statamic/cms/issues/9447) by @peimn
- Add AssetCreating, AssetCreated and AssetSaving events. [#9378](https://github.com/statamic/cms/issues/9378) by @ryanmitchell
- Add validation to prevent duplicate field handles. [#9337](https://github.com/statamic/cms/issues/9337) by @duncanmcclean
- Show blueprint title in tree view. [#9413](https://github.com/statamic/cms/issues/9413) by @mmodler
- Bard & Replicators: Show set group in UI. [#9670](https://github.com/statamic/cms/issues/9670) by @duncanmcclean
- Users Listing: Allow for configuring default sort field & direction. [#9671](https://github.com/statamic/cms/issues/9671) by @duncanmcclean
- E.T. Phone Home. [#8416](https://github.com/statamic/cms/issues/8416) by @jackmcdade
- Date/time fieldtypes use native time fields. [#9662](https://github.com/statamic/cms/issues/9662) by @aaronbushnell

### What's fixed
- Fix "Create Entry" button on collection widget in multisite. [#9699](https://github.com/statamic/cms/issues/9699) by @duncanmcclean
- Ensure submission values take precedence over globals data. [#9698](https://github.com/statamic/cms/issues/9698) by @duncanmcclean
- Fix preferences when user has role via group. [#8957](https://github.com/statamic/cms/issues/8957) by @duncanmcclean
- Fix bug in deleting users in the CP controller. [#9677](https://github.com/statamic/cms/issues/9677) by @ryanmitchell
- Fix Collection::computed docblock. [#9673](https://github.com/statamic/cms/issues/9673) by @ajnsn
- Prevent localizing entries without edit permission. [#9605](https://github.com/statamic/cms/issues/9605) by @duncanmcclean



## 4.52.0 (2024-03-04)

### What's new
- OAuth improvements including support for SAML2 providers. [#9612](https://github.com/statamic/cms/issues/9612) by @duncanmcclean

### What's fixed
- Fix entries not being "linked" to their localizations corrected. [#9661](https://github.com/statamic/cms/issues/9661) by @ryanmitchell
- Fix prop type warning in validation builder. [#9665](https://github.com/statamic/cms/issues/9665) by @jasonvarga
- Only suggest fields in the same replicator set. [#9663](https://github.com/statamic/cms/issues/9663) by @jasonvarga
- Roll back to initial simple isAjax() check on front end forms. [#9629](https://github.com/statamic/cms/issues/9629) by @ryanmitchell
- Fix Antlers sections not being yieldable in Blade layouts. [#9614](https://github.com/statamic/cms/issues/9614) by @JohnathonKoster
- Fix Antlers strict equality inside conditions. [#9621](https://github.com/statamic/cms/issues/9621) by @JohnathonKoster
- Fix test that fails on February 29th. [#9620](https://github.com/statamic/cms/issues/9620) by @jasonvarga
- Filter away bad bard nodes during preprocessing. [#9608](https://github.com/statamic/cms/issues/9608) by @SylvesterDamgaard
- Fix page url value in TreeBuilder. [#9611](https://github.com/statamic/cms/issues/9611) by @0kyn



## 4.51.0 (2024-02-28)

### What's new
- Add `isDirty` / `isClean`. [#5502](https://github.com/statamic/cms/issues/5502) by @ryanmitchell
- Support for validation Rule objects. [#9332](https://github.com/statamic/cms/issues/9332) by @martyf

### What's fixed
- Use protection scheme from data before using site-wide protection scheme. [#9607](https://github.com/statamic/cms/issues/9607) by @duncanmcclean
- Fix search dropdown being hidden on Taggable Fieldtype. [#9606](https://github.com/statamic/cms/issues/9606) by @duncanmcclean
- Tweak SuggestsConditionalFields behaviour when dealing with a fields prefix. [#9592](https://github.com/statamic/cms/issues/9592) by @martyf
- Only run custom validation errors when not precognitive. [#9599](https://github.com/statamic/cms/issues/9599) by @ryanmitchell
- Fix docblock of FluentTag param method. [#9601](https://github.com/statamic/cms/issues/9601) by @ajnsn
- Fix pixel gap on relationship fieldtype items and prevent padding issue. [#9597](https://github.com/statamic/cms/issues/9597) by @robdekort



## 4.50.0 (2024-02-26)

### What's new
- Add `hex_to_rgb` modifier. [#9582](https://github.com/statamic/cms/issues/9582) by @DanielDarrenJones
- Add UI mode option to forms fieldtype config. [#9591](https://github.com/statamic/cms/issues/9591) by @jacksleight
- First invalid field will be scrolled into view when submitting publish forms. [#9577](https://github.com/statamic/cms/issues/9577) by @jacksleight
- Add Antlers shorthand parameter value syntax. [#9505](https://github.com/statamic/cms/issues/9505) by @JohnathonKoster
- Hooks. [#9481](https://github.com/statamic/cms/issues/9481) by @ryanmitchell
- Add `Entry::findOrFail`. [#9506](https://github.com/statamic/cms/issues/9506) by @benfurfie

### What's fixed
- Fix field conditions in Grid fields. [#9586](https://github.com/statamic/cms/issues/9586) by @duncanmcclean
- Fix pixel gap on relationship fieldtype items. [#9579](https://github.com/statamic/cms/issues/9579) by @jasonvarga
- Use static_cache store for nocache. [#9527](https://github.com/statamic/cms/issues/9527) by @ryanmitchell
- Make tags using old-style __call method compatible with Macroable. [#9553](https://github.com/statamic/cms/issues/9553) by @SylvesterDamgaard
- Fix `locales` tag inside replicator, bard, and grid. [#9566](https://github.com/statamic/cms/issues/9566) by @aerni
- Add `accepted_if` validation to Bard `enable_input_rules` [#9555](https://github.com/statamic/cms/issues/9555) by @robdekort
- Add `accepted_if` validation rule [#9557](https://github.com/statamic/cms/issues/9557) by @robdekort
- Fix modal height UI issue. [#9538](https://github.com/statamic/cms/issues/9538) by @JohnathonKoster
- Fix arrays being returned by translations. [#9525](https://github.com/statamic/cms/issues/9525) by @duncanmcclean
- Fix Stache pathing issue on Windows. [#9537](https://github.com/statamic/cms/issues/9537) by @JohnathonKoster
- Antlers: Resolve values from augmented values when there is more data to process. [#9548](https://github.com/statamic/cms/issues/9548) by @JohnathonKoster
- Antlers: Stop double-initial execution of tags within conditions. [#9504](https://github.com/statamic/cms/issues/9504) by @JohnathonKoster
- Fix error in event listener when uploading file in front-end forms. [#9542](https://github.com/statamic/cms/issues/9542) by @ryanmitchell
- Drop Laravel 6 mail view. [#9545](https://github.com/statamic/cms/issues/9545) by @duncanmcclean
- Remove any uploaded assets when submission silently fails or validation fails. [#9549](https://github.com/statamic/cms/issues/9549) by @ryanmitchell
- Check if form request wantsJson. [#9533](https://github.com/statamic/cms/issues/9533) by @ryanmitchell
- Pass parent field and index down to imported fields. [#9550](https://github.com/statamic/cms/issues/9550) by @jacksleight
- Fix protection redirect URLs when they contain query parameters. [#9543](https://github.com/statamic/cms/issues/9543) by @duncanmcclean
- Fix issues when saving entries with `JsonResource::withoutWrapping()`. [#9519](https://github.com/statamic/cms/issues/9519) by @duncanmcclean



## 4.49.0 (2024-02-16)

### What's new
- Support YouTube Shorts in `embed_code` modifier. [#9521](https://github.com/statamic/cms/issues/9521) by @mnlmaier
- Allow number literals inside Antlers tag parameters. [#9503](https://github.com/statamic/cms/issues/9503) by @JohnathonKoster
- Add clear value button to popover `date` fieldtype. [#9478](https://github.com/statamic/cms/issues/9478) by @jacksleight
- Add GraphQL type for `group` fieldtype. [#9499](https://github.com/statamic/cms/issues/9499) by @duncanmcclean
- Add PHP `fieldPathPrefix` method. [#9080](https://github.com/statamic/cms/issues/9080) by @jacksleight

### What's improved
- French translations. [#9476](https://github.com/statamic/cms/issues/9476) by @ebeauchamps
- Improve speed of "Duplicate" action by only searching for descendants if multi-site is enabled. [#9528](https://github.com/statamic/cms/issues/9528) by @helloiamlukas
- Improve CP page speed by cleaning up some JS event handlers. [#9500](https://github.com/statamic/cms/issues/9500) by @jasonvarga

### What's fixed
- Exclude super when using a custom field. [#9536](https://github.com/statamic/cms/issues/9536) by @jasonvarga
- Fix table drag handles disappearing. [#9522](https://github.com/statamic/cms/issues/9522) by @jasonvarga
- Prevent non-images being processed through source preset. [#9517](https://github.com/statamic/cms/issues/9517) by @duncanmcclean
- Fix numbers not being cast in API filters. [#9511](https://github.com/statamic/cms/issues/9511) by @jasonvarga
- Fix scrolling in Inline Publish Form on Safari on iOS. [#9510](https://github.com/statamic/cms/issues/9510) by @duncanmcclean
- Prevent warming redirect URLs. [#9509](https://github.com/statamic/cms/issues/9509) by @duncanmcclean
- Fix pagination with the `nocache` tag. [#9394](https://github.com/statamic/cms/issues/9394) by @duncanmcclean
- Fix missing translations. [#9450](https://github.com/statamic/cms/issues/9450) by @peimn
- Fix error from `code` fieldtype when switching sites in global. [#9488](https://github.com/statamic/cms/issues/9488) by @duncanmcclean
- Fix `$authenticatedUser` error with third-party addon events. [#9490](https://github.com/statamic/cms/issues/9490) by @duncanmcclean
- Fix directory separator in `templates` fieldtype on Windows. [#9483](https://github.com/statamic/cms/issues/9483) by @duncanmcclean
- Include `honeypot` in Alpine.js form data. [#9498](https://github.com/statamic/cms/issues/9498) by @duncanmcclean
- Localize entry & term fields in Taxonomy Term GraphQL queries. [#9492](https://github.com/statamic/cms/issues/9492) by @duncanmcclean
- Make `BlueprintRepository` a singleton. [#9489](https://github.com/statamic/cms/issues/9489) by @jacksleight
- Allow namespace to be passed in `Blueprint::make()`. [#9484](https://github.com/statamic/cms/issues/9484) by @ryanmitchell
- Fix update counter. [#9479](https://github.com/statamic/cms/issues/9479) by @jasonvarga
- Fix missing updates badge. [#9477](https://github.com/statamic/cms/issues/9477) by @jasonvarga
- Test suite uses PHPUnit 10. [#9529](https://github.com/statamic/cms/issues/9529) by @jasonvarga
- Fix Windows tests not running in GitHub Actions. [#9482](https://github.com/statamic/cms/issues/9482) by @duncanmcclean



## 4.48.0 (2024-02-06)

### What's new
- Ability to customize Echo client configuration. [#9464](https://github.com/statamic/cms/issues/9464) by @jacksleight
- Tags are macroable. [#9466](https://github.com/statamic/cms/issues/9466) by @SylvesterDamgaard
- Statamic route views can be implied. [#9436](https://github.com/statamic/cms/issues/9436) by @jasonvarga
- Add sites to support details output. [#9461](https://github.com/statamic/cms/issues/9461) by @jasonvarga

### What's fixed
- Fix unit translations. [#9472](https://github.com/statamic/cms/issues/9472) by @jasonvarga
- Remove typo inside UserProvider. [#9459](https://github.com/statamic/cms/issues/9459) by @jonassiewertsen
- Translate more untranslated strings. [#9451](https://github.com/statamic/cms/issues/9451) by @peimn
- Fix timeout when using `nocache` tag. [#9449](https://github.com/statamic/cms/issues/9449) by @duncanmcclean
- Revert Stache watcher performance PR. [#9448](https://github.com/statamic/cms/issues/9448) by @jasonvarga
- Tidy up replicator field styles to match normal fields. [#9446](https://github.com/statamic/cms/issues/9446) by @duncanmcclean
- Ensure template and termTemplate are accessed correctly in Taxonomy controller. [#9444](https://github.com/statamic/cms/issues/9444) by @ryanmitchell



## 4.47.0 (2024-01-31)

### What's new
- Allow a custom static caching url store to be specified. [#9405](https://github.com/statamic/cms/issues/9405) by @ryanmitchell
- Add reset button to color fieldtype. [#9419](https://github.com/statamic/cms/issues/9419) by @duncanmcclean
- Ability to add inline scripts into the CP. [#9386](https://github.com/statamic/cms/issues/9386) by @jacksleight

### What's improved
- Improve the workflow around enabling Statamic Pro. [#9435](https://github.com/statamic/cms/issues/9435) by @jesseleite
- Improve collection Stache watcher performance. [#9302](https://github.com/statamic/cms/issues/9302) by @JohnathonKoster
- French translations. [#9402](https://github.com/statamic/cms/issues/9402) by @ebeauchamps

### What's fixed
- Fix issue with set previews in Bard. [#9422](https://github.com/statamic/cms/issues/9422) by @duncanmcclean
- Improve UX of field conditions builder for select & toggle fields. [#9379](https://github.com/statamic/cms/issues/9379) by @duncanmcclean
- Don't set termTemplate and template if they are the defaults. [#9421](https://github.com/statamic/cms/issues/9421) by @ryanmitchell
- Prevent serialization errors with `@nocache` directive when using Blade view components. [#9409](https://github.com/statamic/cms/issues/9409) by @duncanmcclean
- Fix user wizard error when user blueprint has Bard field. [#9416](https://github.com/statamic/cms/issues/9416) by @jesseleite
- Hide listing filters when reordering entries. [#9420](https://github.com/statamic/cms/issues/9420) by @duncanmcclean
- Rename route binding parameter to prevent overlapping. [#9415](https://github.com/statamic/cms/issues/9415) by @duncanmcclean
- Fix Entries fieldtype tree view on Assets publish form. [#9404](https://github.com/statamic/cms/issues/9404) by @duncanmcclean
- Fix translations in `nocache` tag. [#9400](https://github.com/statamic/cms/issues/9400) by @duncanmcclean
- Translate dimension conjunction. [#9393](https://github.com/statamic/cms/issues/9393) by @peimn



## 4.46.0 (2024-01-25)

### What's new
- Allow roles and groups to be database driven. [#5686](https://github.com/statamic/cms/issues/5686) by @ryanmitchell
- Add console command to import file-based groups and roles into database. [#6774](https://github.com/statamic/cms/issues/6774) by @ryanmitchell
- SVG images get sanitized upon upload. [#9365](https://github.com/statamic/cms/issues/9365) by @duncanmcclean
- Allow `.html` to be stripped from `parent_uri` in routes. [#9364](https://github.com/statamic/cms/issues/9364) by @duncanmcclean

### What's improved
- German translations. [#9374](https://github.com/statamic/cms/issues/9374) by @helloDanuk

### What's fixed
- Support data in Eloquent based user groups. [#9395](https://github.com/statamic/cms/issues/9395) by @jasonvarga
- Revert overzealous file extension renaming feature. [#9389](https://github.com/statamic/cms/issues/9389) by @jasonvarga
- Make copy reset password link action opt-in. [#9390](https://github.com/statamic/cms/issues/9390) [#9397](https://github.com/statamic/cms/issues/9397) by @jasonvarga
- Fix importing fieldsets in custom blueprint namespaces. [#9387](https://github.com/statamic/cms/issues/9387) by @duncanmcclean
- Catch errors when sending user activation email. [#9382](https://github.com/statamic/cms/issues/9382) by @duncanmcclean
- Only use site language for form submission validation messages if submitted from front-end. [#9383](https://github.com/statamic/cms/issues/9383) by @jasonvarga
- Avoid caching URLs with a token. [#9381](https://github.com/statamic/cms/issues/9381) by @jasonvarga
- Fix user groups/roles querying. [#6131](https://github.com/statamic/cms/issues/6131) by @ryanmitchell
- Avoid saving icons to field configs. [#9372](https://github.com/statamic/cms/issues/9372) by @duncanmcclean
- Hide actions on form index/show pages when user is missing relevant permissions. [#9371](https://github.com/statamic/cms/issues/9371) by @duncanmcclean
- Bard: Only configure placeholder extension when placeholder is provided. [#9369](https://github.com/statamic/cms/issues/9369) by @duncanmcclean
- Always show slug rengerate button if enabled. [#9366](https://github.com/statamic/cms/issues/9366) by @jasonvarga
- Fix some translations. [#9361](https://github.com/statamic/cms/issues/9361) by @peimn
- Fix whereTime affecting the date as well as time. [#9360](https://github.com/statamic/cms/issues/9360) by @ryanmitchell
- Passthrough Cmd/Ctrl + Enter inside Bard. [#9313](https://github.com/statamic/cms/issues/9313) by @godismyjudge95
- Support brackets in translation strings in translator helper. [#9380](https://github.com/statamic/cms/issues/9380) by @jasonvarga
- Bump vite from 4.2.3 to 4.5.2. [#9367](https://github.com/statamic/cms/issues/9367) by @dependabot



## 4.45.0 (2024-01-18)

### What's new
- Allow search index updates to be queued. [#9358](https://github.com/statamic/cms/issues/9358) by @jasonvarga
- Autoload addon blueprints. [#9324](https://github.com/statamic/cms/issues/9324) by @aerni
- Add CC and BCC fields to form email config. [#9336](https://github.com/statamic/cms/issues/9336) by @godismyjudge95

### What's improved
- Prevent handling field previews when previews are disabled. [#9353](https://github.com/statamic/cms/issues/9353) by @duncanmcclean
- Update entry order and uri by ids. [#9350](https://github.com/statamic/cms/issues/9350) by @juliawarnke
- Dutch translations. [#9347](https://github.com/statamic/cms/issues/9347) by @jeroenpeters1986

### What's fixed
- Improve Bard cut/copy/pasting sets. [#7606](https://github.com/statamic/cms/issues/7606) by @jacksleight
- Catch validation exceptions thrown in FormSubmitted events. [#9348](https://github.com/statamic/cms/issues/9348) by @ryanmitchell
- Allow optional asset fields in form submissions. [#9356](https://github.com/statamic/cms/issues/9356) by @AndreasSchantl
- Fix error from Relationship Index Fieldtype after toggling column. [#9355](https://github.com/statamic/cms/issues/9355) by @duncanmcclean
- Prevent creating revision when localizing entry unless revisions are enabled on the collection. [#8908](https://github.com/statamic/cms/issues/8908) by @duncanmcclean
- Fix "Read Only" text for section fields. [#9315](https://github.com/statamic/cms/issues/9315) by @duncanmcclean
- Fix additional blueprints in multi-part namespaces. [#9325](https://github.com/statamic/cms/issues/9325) by @jacksleight
- Prevent Bard causing dirty state issues. [#9344](https://github.com/statamic/cms/issues/9344) by @duncanmcclean
- Prevent original addon blueprint being deleted when saving. [#9326](https://github.com/statamic/cms/issues/9326) by @ryanmitchell
- Fix Bard sets being inserted too early. [#9216](https://github.com/statamic/cms/issues/9216) by @duncanmcclean
- Fix too many redirects on collection. [#9318](https://github.com/statamic/cms/issues/9318) by @aerni
- Fix Statamic compiled assets not working when running in a subdirectory. [#8984](https://github.com/statamic/cms/issues/8984) by @singer-d



## 4.44.0 (2024-01-11)

### What's new
- Support custom blueprint namespaces. [#8516](https://github.com/statamic/cms/issues/8516) by @ryanmitchell

### What's improved
- German translations. [#9295](https://github.com/statamic/cms/issues/9295) by @helloDanuk

### What's fixed
- Fix missing checkbox on tree view in the entries field type. [#9308](https://github.com/statamic/cms/issues/9308) by @duncanmcclean
- Fix spacing around fullscreen button on Stacked Grid. [#9307](https://github.com/statamic/cms/issues/9307) by @duncanmcclean
- Fix Asset browser firing 2 requests on initial Assets page load. [#8981](https://github.com/statamic/cms/issues/8981) by @modrictin
- Use correct set_time_limit no limit value. [#9297](https://github.com/statamic/cms/issues/9297) by @robinvdvleuten
- Fix Bard's sticky toolbar in Live Preview. [#9301](https://github.com/statamic/cms/issues/9301) by @duncanmcclean
- Fix asset meta file not being deleted with asset. [#9300](https://github.com/statamic/cms/issues/9300) by @duncanmcclean
- Fix creating entries with `JsonResource::withoutWrapping()`. [#9296](https://github.com/statamic/cms/issues/9296) by @duncanmcclean



## 4.43.0 (2024-01-09)

### What's new
- Add deleting events. [#9227](https://github.com/statamic/cms/issues/9227) by @ryanmitchell
- Add `saveQuietly` to `LocalizedTerm`. [#9278](https://github.com/statamic/cms/issues/9278) by @joshuablum
- Presets are regenerated after updating focal point. [#9019](https://github.com/statamic/cms/issues/9019) by @duncanmcclean
- Allow removal of scopes. [#9264](https://github.com/statamic/cms/issues/9264) by @ryanmitchell

### What's fixed
- Fix disabled save button when creating term inside term inside stack. [#9152](https://github.com/statamic/cms/issues/9152) by @duncanmcclean
- Fix `metaPath` for root assets. [#9287](https://github.com/statamic/cms/issues/9287) by @duncanmcclean
- Prevent corrupt submission file from causing errors. [#9282](https://github.com/statamic/cms/issues/9282) by @duncanmcclean
- Clear selections when navigating pagination. [#9286](https://github.com/statamic/cms/issues/9286) by @duncanmcclean
- Avoid custom exception handler for API requests. [#9275](https://github.com/statamic/cms/issues/9275) by @duncanmcclean
- Fix usage of children tag with multisite and specified url. [#9280](https://github.com/statamic/cms/issues/9280) by @MedRochon
- Fix mobile issues with Fieldset Listing & Field Settings stack. [#9250](https://github.com/statamic/cms/issues/9250) by @duncanmcclean
- Prevent updating a term's slug resulting in two Stache terms. [#9260](https://github.com/statamic/cms/issues/9260) by @ryanmitchell
- Change asset unlink icon. [#9204](https://github.com/statamic/cms/issues/9204) by @edalzell
- Fix error from static caching invalidator when deleting entries. [#9191](https://github.com/statamic/cms/issues/9191) by @duncanmcclean
- Throw 404 exception on Taxonomy Term Entries endpoint when term doesn't exist. [#9273](https://github.com/statamic/cms/issues/9273) by @duncanmcclean
- Replace problematic JSON directive. [#9271](https://github.com/statamic/cms/issues/9271) by @JohnathonKoster
- Use authenticated user in Git events even when queued. [#9225](https://github.com/statamic/cms/issues/9225) by @duncanmcclean
- Fix "Update All" in search utility. [#9269](https://github.com/statamic/cms/issues/9269) by @duncanmcclean
- Prevent users without "edit" permission editing navs. [#9265](https://github.com/statamic/cms/issues/9265) by @duncanmcclean
- Localize revision dates. [#9266](https://github.com/statamic/cms/issues/9266) by @jasonvarga
- Use the site locale when auto generating titles. [#9261](https://github.com/statamic/cms/issues/9261) by @ryanmitchell
- Bump axios from 0.21.4 to 1.6.4 [#8974](https://github.com/statamic/cms/issues/8974) by @dependabot



## 4.42.1 (2024-01-04)

### What's fixed
- Ensure error message is displayed when uploading large file. [#9258](https://github.com/statamic/cms/issues/9258) by @duncanmcclean
- Prevent Bard augmentation error after enabling "Save HTML" option. [#9198](https://github.com/statamic/cms/issues/9198) by @duncanmcclean
- Avoid compiling certain user defined strings. [#9256](https://github.com/statamic/cms/issues/9256) by @jasonvarga
- Fix an issue with short interpolated variables in Antlers. [#9253](https://github.com/statamic/cms/issues/9253) by @JohnathonKoster
- Fix issue with AuthServiceProvider and Laravel Octane. [#9240](https://github.com/statamic/cms/issues/9240) by @nadinengland
- Allow CP Nav to be created each request under Laravel Octane. [#9241](https://github.com/statamic/cms/issues/9241) by @nadinengland
- Fix Link Fieldtype inside nested Bard. [#9252](https://github.com/statamic/cms/issues/9252) by @duncanmcclean
- Clear permission cache when setting or removing permissions from a role. [#9244](https://github.com/statamic/cms/issues/9244) by @aerni
- Bump tj-actions/changed-files from 36 to 41. [#9247](https://github.com/statamic/cms/issues/9247) by @dependabot



## 4.42.0 (2023-12-18)

### What's improved
- Submission date now uses localized date format [#9215](https://github.com/statamic/cms/issues/9215) by @mmodler
- French translations [#9218](https://github.com/statamic/cms/issues/9218) by @ebeauchamps

### What's fixed
- Fix nested JSON field handles not passing validation [#9217](https://github.com/statamic/cms/issues/9217) by @caseydwyer
- Fix null values not being filtered on front-end forms [#9212](https://github.com/statamic/cms/issues/9212) by @ryanmitchell
- Fix `{{ children }}` tag for collections other than Pages [#9210](https://github.com/statamic/cms/issues/9210) by @MedRochon
- Use `setTimeout` to fix dirty state issue [#9213](https://github.com/statamic/cms/issues/9213) by @duncanmcclean



## 4.41.0 (2023-12-14)

### What's new
- Ability to configure templates & layouts for taxonomies [#8372](https://github.com/statamic/cms/issues/8372) by @ryanmitchell
- Add `query_scopes` option to the Assets fieldtype [#8459](https://github.com/statamic/cms/issues/8459) by @jacksleight

### What's improved
- Entries and terms are now provided lazily in search [#9171](https://github.com/statamic/cms/issues/9171) by @ryanmitchell
- When an entry has an origin, the mount will now be fetched from the origin [#9063](https://github.com/statamic/cms/issues/9063) by @ryanmitchell

### What's fixed
- Fix dirty state issue on the entry publish form [#9203](https://github.com/statamic/cms/issues/9203) by @vluijkx
- Fix error when a navigation's tree file is missing [#9032](https://github.com/statamic/cms/issues/9032) by @duncanmcclean
- Asset field now supports mixed permissions [#9156](https://github.com/statamic/cms/issues/9156) by @edalzell
- Prevent precognitive validation on asset fields [#9170](https://github.com/statamic/cms/issues/9170) by @ryanmitchell
- Fix stack hover offset on close [#9201](https://github.com/statamic/cms/issues/9201) by @jacksleight
- Prevent configuring multiple conditions for the same field [#9199](https://github.com/statamic/cms/issues/9199) by @duncanmcclean
- Fix section showing without any visible fields [#9137](https://github.com/statamic/cms/issues/9137) by @aerni
- Fix tabs showing without any visible fields [#9135](https://github.com/statamic/cms/issues/9135) by @edalzell
- Fix template selector on Windows [#9197](https://github.com/statamic/cms/issues/9197) by @duncanmcclean
- Fix previewing revisions via the Relationship Fieldtype [#9190](https://github.com/statamic/cms/issues/9190) by @duncanmcclean
- Update the bug report template [#9195](https://github.com/statamic/cms/issues/9195) by @jelleroorda
- Fix issue with meta being updated wrongly in Link Fieldtype [#9189](https://github.com/statamic/cms/issues/9189) by @duncanmcclean
- Fix Read Only icon when viewing revisions [#9188](https://github.com/statamic/cms/issues/9188) by @duncanmcclean
- Fix Global Set without a blueprint breaking the Fieldsets page [#9187](https://github.com/statamic/cms/issues/9187) by @duncanmcclean



## 4.40.0 (2023-12-11)

### What's new
- Add nocache regions and CSRF to `statamic:nocache.replaced` event. [#9167](https://github.com/statamic/cms/issues/9167) by @ryanmitchell
- Add `collection` to augmented term values. [#9070](https://github.com/statamic/cms/issues/9070) by @ryanmitchell

### What's improved
- Improve CollectionStructure performance. [#8800](https://github.com/statamic/cms/issues/8800) by @mauricewijnia
- Clarify which changes will stay in sync. [#9179](https://github.com/statamic/cms/issues/9179) by @duncanmcclean
- German translations. [#9164](https://github.com/statamic/cms/issues/9164) by @helloDanuk

### What's fixed
- Render layout on 403 Forbidden pages. [#9180](https://github.com/statamic/cms/issues/9180) by @duncanmcclean
- Fix tree view when configuring collection mount. [#9128](https://github.com/statamic/cms/issues/9128) by @duncanmcclean
- Delete collection tree files when deleting collections. [#9183](https://github.com/statamic/cms/issues/9183) by @duncanmcclean
- Prevent events being added to an element that doesn't exist when in grid table mode. [#9130](https://github.com/statamic/cms/issues/9130) by @ryanmitchell
- Use date facade and carbon interface. [#9114](https://github.com/statamic/cms/issues/9114) by @jasonvarga
- Ensure publish date field can only be in `single` mode. [#9178](https://github.com/statamic/cms/issues/9178) by @duncanmcclean
- Fix template lookup with dots in view path. [#9176](https://github.com/statamic/cms/issues/9176) by @marijoo
- Ensure field exists before checking if it imports a fieldset. [#9175](https://github.com/statamic/cms/issues/9175) by @ryanmitchell
- Translate validation attributes when pulled from display. [#9169](https://github.com/statamic/cms/issues/9169) by @ryanmitchell
- Fix updating localized search index through the CP. [#9160](https://github.com/statamic/cms/issues/9160) by @ryanmitchell
- If collection is not available on a site, redirect back. [#9071](https://github.com/statamic/cms/issues/9071) by @ryanmitchell
- Fix Antlers string interpolation checks running Str::contains on objects. [#9163](https://github.com/statamic/cms/issues/9163) by @JohnathonKoster
- Fix deleting collections with localized entries. [#9165](https://github.com/statamic/cms/issues/9165) by @duncanmcclean
- Update Laravel Pint. [#9181](https://github.com/statamic/cms/issues/9181) by @duncanmcclean
- Update contribution guide. [#9133](https://github.com/statamic/cms/issues/9133) by @joshuablum



## 4.39.0 (2023-12-07)

### What's new
- Add parent to asset blueprint, include asset in blueprint found event. [#8644](https://github.com/statamic/cms/issues/8644) by @jacksleight
- Add current url resolver to sites. [#9098](https://github.com/statamic/cms/issues/9098) by @ajnsn
- Add `children` tag. [#8990](https://github.com/statamic/cms/issues/8990) by @ryanmitchell
- Add `chunk` and `lazy` to query builders. [#9142](https://github.com/statamic/cms/issues/9142) [#9148](https://github.com/statamic/cms/issues/9148) by @ryanmitchell
- Add ability to configure the mailer for each form email. [#9136](https://github.com/statamic/cms/issues/9136) by @aerni
- Add Laravel Pulse link to global header. [#9125](https://github.com/statamic/cms/issues/9125) by @joshuablum

### What's improved
- French translations. [#9139](https://github.com/statamic/cms/issues/9139) by @ebeauchamps

### What's fixed
- Fix templates fieldtype when views are in a non-default location. [#9085](https://github.com/statamic/cms/issues/9085) by @duncanmcclean
- Fix styling of grid stacked mode header when deleting is not possible. [#9129](https://github.com/statamic/cms/issues/9129) by @ryanmitchell
- Ensure `user:is` and `user:isnt` work with `roles` fieldtype. [#9041](https://github.com/statamic/cms/issues/9041) by @ryanmitchell
- Exclude taxonomy index pages from `nav:breadcrumbs` tag when view is missing. [#9154](https://github.com/statamic/cms/issues/9154) by @duncanmcclean
- Make the `container` field in the `assets` fieldtype required. [#9151](https://github.com/statamic/cms/issues/9151) by @robdekort
- Improve handling around deleted blueprints & fieldsets. [#8989](https://github.com/statamic/cms/issues/8989) by @duncanmcclean
- Fix collapsed Bard set revealer data loss. [#9153](https://github.com/statamic/cms/issues/9153) by @jacksleight
- "Configure asset containers" permission should override other asset permissions. [#9134](https://github.com/statamic/cms/issues/9134) by @duncanmcclean
- Revert mount from augmented collection. [#9145](https://github.com/statamic/cms/issues/9145) by @jasonvarga
- Fix missing group title translation string. [#9143](https://github.com/statamic/cms/issues/9143) by @ryanmitchell
- Fix replicator preview for `date` fieldtype when time is empty. [#9099](https://github.com/statamic/cms/issues/9099) by @duncanmcclean
- Make arrayable link url method public. [#9131](https://github.com/statamic/cms/issues/9131) by @ryanmitchell
- Change nocache performance optimizations to be opt-in. [#9124](https://github.com/statamic/cms/issues/9124) by @joshuablum
- Handle glide exceptions gracefully. [#9109](https://github.com/statamic/cms/issues/9109) by @ryanmitchell
- Fix prefixed fieldset imports being lost when there's an ensured blueprint field. [#9116](https://github.com/statamic/cms/issues/9116) by @ryanmitchell
- When appending glide filename consider that the extension may have changed. [#9097](https://github.com/statamic/cms/issues/9097) by @ryanmitchell
- Fix Antlers issue regarding conditions resolving variables. [#9106](https://github.com/statamic/cms/issues/9106) by @JohnathonKoster
- Fix "Hide Display" setting not being persisted on reference field. [#9111](https://github.com/statamic/cms/issues/9111) by @duncanmcclean
- Harden collection handle check in Taxonomy findByUri to prevent partial matches. [#9108](https://github.com/statamic/cms/issues/9108) by @ryanmitchell
- Make url and uri available in preview targets. [#9104](https://github.com/statamic/cms/issues/9104) by @NeoIsRecursive
- Prevent terms fieldtype in typehead mode showing results until a search is entered. [#9082](https://github.com/statamic/cms/issues/9082) by @ryanmitchell
- Prevent showing "Saved" toast message when listener cancels save. [#9040](https://github.com/statamic/cms/issues/9040) by @duncanmcclean
- Fix adding Bard sets with horizontal cursor. [#9064](https://github.com/statamic/cms/issues/9064) by @duncanmcclean
- Improve comb search driver performance. [#9102](https://github.com/statamic/cms/issues/9102) by @jacksleight
- Adjust Laravel Pint config to prevent a bunch of changes caused by an update. [#9126](https://github.com/statamic/cms/issues/9126) by @duncanmcclean



## 4.38.0 (2023-11-30)

### What's new
- Support Laravel Precognition on front end forms. [#8886](https://github.com/statamic/cms/issues/8886) by @ryanmitchell

### What's fixed
- Fix 404 redirect after creating entry. [#9095](https://github.com/statamic/cms/issues/9095) by @jasonvarga
- Fix date field not populating with current date, revert prevention of ensuring fields if they already exist. [#9094](https://github.com/statamic/cms/issues/9094) by @jasonvarga
- Fix date replicator preview in more configurations. [#9093](https://github.com/statamic/cms/issues/9093) by @jasonvarga
- Fix autofocus on textareas [#9089](https://github.com/statamic/cms/issues/9089) by @robdekort



## 4.37.0 (2023-11-29)

### What's new
- Add `group` field type. [#8836](https://github.com/statamic/cms/issues/8836) by @godismyjudge95
- User creation wizard now shows all fields from the blueprint with validation. [#9003](https://github.com/statamic/cms/issues/9003) by @ryanmitchell
- Tag conditions can query on sub-fields using dot notation. [#9069](https://github.com/statamic/cms/issues/9069) by @ryanmitchell
- Search indexes are updated with better memory efficiency using lazy collections. [#9072](https://github.com/statamic/cms/issues/9072) by @ryanmitchell
- The `ray` modifier can specify color. [#9073](https://github.com/statamic/cms/issues/9073) by @joschuba
- Add `UrlInvalidated` event for when a URL is invalidated from the static cache. [#8902](https://github.com/statamic/cms/issues/8902) by @ryanmitchell
- Add `Creating` events. [#7377](https://github.com/statamic/cms/issues/7377) by @ryanmitchell
- Add option to append original filename to Glide URLs. [#8661](https://github.com/statamic/cms/issues/8661) by @ryanmitchell

### What's improved
- Improve replicator preview for Date Fieldtype. [#9057](https://github.com/statamic/cms/issues/9057) by @duncanmcclean
- Make whole branches clickable in Entries fieldtype in tree view. [#9074](https://github.com/statamic/cms/issues/9074) by @duncanmcclean
- Add validation to limit characters in slugs. [#9046](https://github.com/statamic/cms/issues/9046) by @duncanmcclean
- Improve sanitization of Replicator set preview text. [#9047](https://github.com/statamic/cms/issues/9047) by @jasonvarga

### What's fixed
- Scope filters can return null. [#9058](https://github.com/statamic/cms/issues/9058) by @jonassiewertsen
- Fix passing null to strtolower. [#9042](https://github.com/statamic/cms/issues/9042) by @miloslavkostir
- Fix `redirect: @child` redirecting to `@child` when not a link fieldtype. [#9062](https://github.com/statamic/cms/issues/9062) by @ryanmitchell
- Prevent existing term data being overwritten in terms fieldtype. [#9076](https://github.com/statamic/cms/issues/9076) by @ryanmitchell
- Translate widget header and collection widget title. [#9077](https://github.com/statamic/cms/issues/9077) by @ryanmitchell
- Fix redirect actions showing a toast error. [#9054](https://github.com/statamic/cms/issues/9054) by @ryanmitchell
- Fix appropriate site not being used in the listing when redirecting from an entry. [#9075](https://github.com/statamic/cms/issues/9075) by @duncanmcclean
- Use `RedirectIfAuthorized` middleware on password reset & activate pages. [#9053](https://github.com/statamic/cms/issues/9053) by @ryanmitchell
- Allow focus on textarea fieldtypes. [#9055](https://github.com/statamic/cms/issues/9055) by @robdekort
- Fix pagination in relationship fieldtype causing page to scroll to the top. [#9060](https://github.com/statamic/cms/issues/9060) by @duncanmcclean
- Fix error when copying assets across filesystems. [#9065](https://github.com/statamic/cms/issues/9065) by @mbale
- Fix utility permissions not showing when using route caching. [#9059](https://github.com/statamic/cms/issues/9059) by @duncanmcclean
- Revert auto-population of array fieldtype options. [#9066](https://github.com/statamic/cms/issues/9066) by @duncanmcclean
- Fix checkbox selection in listing tables jumping on Safari. [#9052](https://github.com/statamic/cms/issues/9052) by @ryanmitchell
- Assets get downloaded when no URL is available. [#9018](https://github.com/statamic/cms/issues/9018) by @duncanmcclean
- Fix section header padding in the sidebar. [#9051](https://github.com/statamic/cms/issues/9051) by @jackmcdade
- Pass autocomplete config param to CP text inputs. [#9013](https://github.com/statamic/cms/issues/9013) by @ryanmitchell
- Fix Glide tag stripping domain when using unsupported extension. [#9031](https://github.com/statamic/cms/issues/9031) by @duncanmcclean
- Allow revision actions to be translated. [#9023](https://github.com/statamic/cms/issues/9023) by @ryanmitchell
- Fix URI validation error when collection route uses entry IDs. [#9035](https://github.com/statamic/cms/issues/9035) by @duncanmcclean
- Fix Outpost locking code for cache drivers without locking support. [#9029](https://github.com/statamic/cms/issues/9029) by @duncanmcclean
- Fix missing `nestedListing` method on `Html` class. [#9034](https://github.com/statamic/cms/issues/9034) by @duncanmcclean
- Fix entries tag not filtering by taxonomy when terms field is `max_items: 1`. [#9045](https://github.com/statamic/cms/issues/9045) by @ryanmitchell
- Fix select field value not being shown in listings when option label is missing. [#9038](https://github.com/statamic/cms/issues/9038) by @duncanmcclean



## 4.36.0 (2023-11-20)

### What's new
- Add `href` attribute to relationship item links. [#9030](https://github.com/statamic/cms/issues/9030) by @jacksleight
- Only allow uploading certain file extensions, with an option to add more. [#9037](https://github.com/statamic/cms/issues/9037) by @jasonvarga

### What's fixed
- Fix nocache tag when URL ends with a question mark. [#9016](https://github.com/statamic/cms/issues/9016) by @duncanmcclean
- Fix missing globals and asset container translations. [#9024](https://github.com/statamic/cms/issues/9024) by @ryanmitchell
- Validate that field handles are in snake case. [#9039](https://github.com/statamic/cms/issues/9039) by @duncanmcclean
- Appropriate file extension are applied when uploading. [#9033](https://github.com/statamic/cms/issues/9033) by @jasonvarga
- Don't prompt user to select search index when there's only 1 configured. [#9036](https://github.com/statamic/cms/issues/9036) by @duncanmcclean
- Fix `range` tag with `times="0"` parameter outputting incorrectly. [#9022](https://github.com/statamic/cms/issues/9022) by @ryanmitchell
- Fix error in collection entry listing that occurs on certain server setups (i.e Vapor and AWS function URLs). [#9028](https://github.com/statamic/cms/issues/9028) by @duncanmcclean



## 4.35.0 (2023-11-16)

### What's new
- Ability to set settings on Algolia indexes. [#8830](https://github.com/statamic/cms/issues/8830) by @godismyjudge95
- Ability to customize Bard/Replicator set icons directory. [#8931](https://github.com/statamic/cms/issues/8931) by @jesseleite

### What's improved
- Improve Bard invalid content error reporting. [#8580](https://github.com/statamic/cms/issues/8580) by @jacksleight
- Dutch translations. [#8993](https://github.com/statamic/cms/issues/8993) by @jeroenpeters1986

### What's fixed
- Fix nocache tag error when using the regex antlers parser. [#9009](https://github.com/statamic/cms/issues/9009) by @jasonvarga
- Fix error when using Eloquent users but you still have user files. [#9006](https://github.com/statamic/cms/issues/9006) by @duncanmcclean
- Fix missing cursor when editing inline code in Bard. [#9002](https://github.com/statamic/cms/issues/9002) by @o1y
- Fix modified revision values not being shown on save. [#8961](https://github.com/statamic/cms/issues/8961) by @ryanmitchell
- Check if user roles and groups exist before creating. [#8998](https://github.com/statamic/cms/issues/8998) by @ryanmitchell
- Check if navigation exists before creating. [#8995](https://github.com/statamic/cms/issues/8995) by @ryanmitchell
- Check if global exists before creating. [#8996](https://github.com/statamic/cms/issues/8996) by @ryanmitchell
- Check if fieldset exists before creating. [#8994](https://github.com/statamic/cms/issues/8994) by @ryanmitchell
- Show error when there is a duplicate taxonomy blueprint name. [#8997](https://github.com/statamic/cms/issues/8997) by @ryanmitchell
- Prevent concurrent requests to the Outpost. [#9000](https://github.com/statamic/cms/issues/9000) by @duncanmcclean
- Resolve dynamically declared properties. [#8999](https://github.com/statamic/cms/issues/8999) by @martinoak



## 4.34.0 (2023-11-14)

### What's new
- Auto-populate `array` fieldtype options. [#8980](https://github.com/statamic/cms/issues/8980) by @duncanmcclean
- Add Bard support to `read_time` modifier. [#8976](https://github.com/statamic/cms/issues/8976) by @duncanmcclean
- Antlers identifier finder. [#8965](https://github.com/statamic/cms/issues/8965) by @jasonvarga

### What's improved
- Nocache performance improvements. [#8956](https://github.com/statamic/cms/issues/8956) by @jasonvarga
- French translations. [#8977](https://github.com/statamic/cms/issues/8977) by @ebeauchamps

### What's fixed
- More php file validation. [#8991](https://github.com/statamic/cms/issues/8991) by @jasonvarga
- Fix super not saving on eloquent users. [#8979](https://github.com/statamic/cms/issues/8979) by @ryanmitchell
- Hide export submissions button when there are no valid exporters. [#8985](https://github.com/statamic/cms/issues/8985) by @ryanmitchell
- Only namespace asset validation attributes when on a CP route. [#8987](https://github.com/statamic/cms/issues/8987) by @ryanmitchell
- Fix for edit form page saying edit collection. [#8967](https://github.com/statamic/cms/issues/8967) by @ryanmitchell
- Fix new child entries not propagating to appropriate position in other sites trees. [#7302](https://github.com/statamic/cms/issues/7302) by @arthurperton
- Fix impersonation redirect. [#8973](https://github.com/statamic/cms/issues/8973) by @jasonvarga
- Fix error when getting alt on bard image when asset is missing. [#8959](https://github.com/statamic/cms/issues/8959) by @morphsteve
- Prevent requiring current password when changing another user's password. [#8966](https://github.com/statamic/cms/issues/8966) by @duncanmcclean
- Fix global attribute support on bard's small mark. [#8969](https://github.com/statamic/cms/issues/8969) by @jacksleight



## 4.33.0 (2023-11-10)

### What's new
- Bard supports cmd+k for links. [#8950](https://github.com/statamic/cms/issues/8950) by @o1y
- The Entries fieldtype use columns from preferences in the stack selector. [#8900](https://github.com/statamic/cms/issues/8900) by @duncanmcclean
- Bind `AssetContainerContents` to the service provider. [#8954](https://github.com/statamic/cms/issues/8954) by @ryanmitchell
- Support arrays in wrap modifier [#8942](https://github.com/statamic/cms/issues/8942) by @jacksleight
- Add `mount` to augmented collection. [#8928](https://github.com/statamic/cms/issues/8928) by @duncanmcclean
- Require `pint` in dev. [#8955](https://github.com/statamic/cms/issues/8955) by @ryanmitchell

### What's improved
- French translations. [#8945](https://github.com/statamic/cms/issues/8945) [#8934](https://github.com/statamic/cms/issues/8934) by @ebeauchamps
- German translations. [#8939](https://github.com/statamic/cms/issues/8939) by @doriengr

### What's fixed
- Front-end form asset field php file validation. [#8968](https://github.com/statamic/cms/issues/8968) by @jasonvarga
- Fix entries fieldtype not respecting collection sort column & direction. [#8894](https://github.com/statamic/cms/issues/8894) by @duncanmcclean
- Fix duplicate entry action translation. [#8946](https://github.com/statamic/cms/issues/8946) by @jasonvarga
- Fix SortableList not reacting to disabled prop changes. [#8949](https://github.com/statamic/cms/issues/8949) by @duncanmcclean
- Remove debounce when renaming assets & folders. [#8953](https://github.com/statamic/cms/issues/8953) by @duncanmcclean
- Use translations from fallback locale when primary locale is missing translations. [#8940](https://github.com/statamic/cms/issues/8940) by @duncanmcclean
- Fix missing title on relationship fields in multi-site. [#8936](https://github.com/statamic/cms/issues/8936) by @duncanmcclean
- Prevent ensuring fields on entries if they already exist. [#8926](https://github.com/statamic/cms/issues/8926) by @duncanmcclean
- Fix `statamic.web` middleware not being merged. [#8935](https://github.com/statamic/cms/issues/8935) by @duncanmcclean
- Fix infinite loop on listing table of mounted collection. [#8937](https://github.com/statamic/cms/issues/8937) by @duncanmcclean
- Fix "Always Save" toggle not being saved when used on linked field. [#8927](https://github.com/statamic/cms/issues/8927) by @duncanmcclean
- Fix slug field not targeting sibling fields inside a replicator. [#8929](https://github.com/statamic/cms/issues/8929) by @duncanmcclean



## 4.32.0 (2023-11-03)

### What's new
- Entries fieldtype gets a tree view in the stack selector. [#8899](https://github.com/statamic/cms/issues/8899) by @duncanmcclean
- Link fieldtype supports array syntax for getting underlying entry, asset, etc. [#8911](https://github.com/statamic/cms/issues/8911) by @edalzell
- Ability to duplicate fields in blueprint/fieldset builders. [#8916](https://github.com/statamic/cms/issues/8916) by @duncanmcclean
- Support paste events in Taggable Fieldtype. [#8903](https://github.com/statamic/cms/issues/8903) by @duncanmcclean
- Add helper to more easily remove child item in CP navigation. [#8883](https://github.com/statamic/cms/issues/8883) by @jesseleite

### What's fixed
- Fix entries on the same date being ignored by collection previous/next tags. [#8921](https://github.com/statamic/cms/issues/8921) by @duncanmcclean
- Remove schema check on import users command. [#8909](https://github.com/statamic/cms/issues/8909) by @ryanmitchell
- Fix slugify when using hyphens surrounded by spaces. [#8923](https://github.com/statamic/cms/issues/8923) by @duncanmcclean
- Fix collection listing's sort direction on reorder. [#8910](https://github.com/statamic/cms/issues/8910) by @o1y
- Prevent root entries being deleted in listing view. [#8912](https://github.com/statamic/cms/issues/8912) by @ryanmitchell
- Handle unauthorized response in Inline Publish Form. [#8918](https://github.com/statamic/cms/issues/8918) by @duncanmcclean
- Hide publish action fields when saving. [#8917](https://github.com/statamic/cms/issues/8917) by @ryanmitchell
- Handle empty values in collection tag filters. [#8915](https://github.com/statamic/cms/issues/8915) by @duncanmcclean
- Fix missing translation of some user defined strings. [#8914](https://github.com/statamic/cms/issues/8914) by @ryanmitchell
- Fix CP nav item active status regressions. [#8880](https://github.com/statamic/cms/issues/8880) by @jesseleite
- Validate that Select & Button Group options have keys. [#8905](https://github.com/statamic/cms/issues/8905) by @duncanmcclean
- Fix permissions for asset upload and folder creation buttons in CP [#8925](https://github.com/statamic/cms/issues/8925) by @joshuablum



## 4.31.0 (2023-10-30)

### What's new
- PHP 8.3 support [#8845](https://github.com/statamic/cms/issues/8845) by @jasonvarga
- Custom form submission exporters. [#8837](https://github.com/statamic/cms/issues/8837) by @ryanmitchell
- Add `RevisionSaving` event. [#8551](https://github.com/statamic/cms/issues/8551) by @ryanmitchell
- Allow using globals in form email configs. [#8892](https://github.com/statamic/cms/issues/8892) by @duncanmcclean
- Improve Entries fieldtype search index logic, and add option to define an explicit one. [#8885](https://github.com/statamic/cms/issues/8885) by @edalzell

### What's improved
- Improve UI of link fieldtype in smaller spaces. [#8882](https://github.com/statamic/cms/issues/8882) by @godismyjudge95
- French translations. [#8889](https://github.com/statamic/cms/issues/8889) by @ebeauchamps

### What's fixed
- Fix status column moving when resetting columns on entry listing. [#8896](https://github.com/statamic/cms/issues/8896) by @duncanmcclean
- Ignore single smart quotes when slugifying entries. [#8895](https://github.com/statamic/cms/issues/8895) by @duncanmcclean
- Fix Live Preview not updating when relationship items are updated. [#8893](https://github.com/statamic/cms/issues/8893) by @duncanmcclean
- Fix global site selector not closing when clicking outside. [#8888](https://github.com/statamic/cms/issues/8888) by @o1y
- Fix Bard text align when no headings are enabled. [#8878](https://github.com/statamic/cms/issues/8878) by @jacksleight



## 4.30.0 (2023-10-20)

### What's new
- Multi-site Duplicator Support. [#8665](https://github.com/statamic/cms/issues/8665) by @duncanmcclean
- Add `EntryDeleting` to allow you to prevent items being deleted [#8833](https://github.com/statamic/cms/issues/8833) by @ryanmitchell

### What's improved
- Cache Blueprint columns. [#8840](https://github.com/statamic/cms/issues/8840) by @jonassiewertsen

### What's fixed
- Default to using the CP broker when multiple are available. [#8872](https://github.com/statamic/cms/issues/8872) by @ryanmitchell



## 4.29.0 (2023-10-19)

### What's new
- Add form reference to field during render. [#8862](https://github.com/statamic/cms/issues/8862) by @martyf
- Add config to enable frontend route binding and support binding by field. [#8853](https://github.com/statamic/cms/issues/8853) by @ryanmitchell
- Allow overwriting the column for `unique_user_value` validation. [#8852](https://github.com/statamic/cms/issues/8852) by @marcorieser

### What's improved
- German translations [#8857](https://github.com/statamic/cms/issues/8857) by @helloDanuk
- French translations [#8848](https://github.com/statamic/cms/issues/8848) by @ebeauchamps

### What's fixed
- Remove double-render of fields in Form tag. [#8861](https://github.com/statamic/cms/issues/8861) by @martyf
- Fix navigation 'Save Changes' button state. [#8864](https://github.com/statamic/cms/issues/8864) by @duncanmcclean
- Fix missing replicator set previews. [#8855](https://github.com/statamic/cms/issues/8855) by @jacksleight
- Bump @babel/traverse from 7.21.3 to 7.23.2 [#8870](https://github.com/statamic/cms/issues/8870) by @dependabot



## 4.28.0 (2023-10-13)

### What's new
- Allow user defined fields in the CP to be translatable. [#8664](https://github.com/statamic/cms/issues/8664) by @ryanmitchell

### What's improved
- French translations. [#8835](https://github.com/statamic/cms/issues/8835) [#8831](https://github.com/statamic/cms/issues/8831) by @ebeauchamps

### What's fixed
- Fix lowercasing of asset filenames to include file extension. [#8842](https://github.com/statamic/cms/issues/8842) by @joshuablum
- Only show status indicator in stack view if item has a status. [#8832](https://github.com/statamic/cms/issues/8832) by @ryanmitchell
- Fix console warning generated by invalid tabulator config. [#8834](https://github.com/statamic/cms/issues/8834) by @joseph-d



## 4.27.0 (2023-10-11)

### What's new
- Multi-site Permissions [#5946](https://github.com/statamic/cms/issues/5946) by @jackmcdade



## 4.26.1 (2023-10-11)

### What's fixed
- Fix assets being deleted when renaming snake_case folder to kebab-case. [#8826](https://github.com/statamic/cms/issues/8826) by @jasonvarga



## 4.26.0 (2023-10-10)

### What's new
- Support for Antlers template-defined variables to be available in the layout. [#8775](https://github.com/statamic/cms/issues/8775) by @JohnathonKoster
- Add Bard link email, phone and relationship options. [#8777](https://github.com/statamic/cms/issues/8777) by @jacksleight

### What's improved
- Dutch translations. [#8823](https://github.com/statamic/cms/issues/8823) by @jeroenpeters1986
- Improve post-save performance with many Bard and Revealer fields. [#8712](https://github.com/statamic/cms/issues/8712) by @jacksleight
- Improve initial render speed of Replicators with many sets. [#8716](https://github.com/statamic/cms/issues/8716) by @jacksleight
- Simplify usage of the icon fieldtype with the SVG tag. [#8815](https://github.com/statamic/cms/issues/8815) by @JohnathonKoster

### What's fixed
- Fix section fieldtype first-child's top margin. [#8822](https://github.com/statamic/cms/issues/8822) by @caseydwyer



## 4.25.0 (2023-10-09)

### What's new
- Support for Submission-specific form redirects. [#8729](https://github.com/statamic/cms/issues/8729) by @martyf
- Support for additional CP thumbnail presets. [#8811](https://github.com/statamic/cms/issues/8811) by @jacksleight
- The toggle fieldtype gets an inline label when truthy setting. [#8814](https://github.com/statamic/cms/issues/8814) by @caseydwyer
- Fieldtypes can define additional renderable data to be available when using front-end forms. [#8730](https://github.com/statamic/cms/issues/8730) by @martyf

### What's improved
- Validation translations. [#8819](https://github.com/statamic/cms/issues/8819) by @caseydwyer
- Dutch translations. [#8799](https://github.com/statamic/cms/issues/8799) by @robdekort
- French translations. [#8792](https://github.com/statamic/cms/issues/8792) by @ebeauchamps
- Clarify default field instructions. [#8808](https://github.com/statamic/cms/issues/8808) by @caseydwyer

### What's fixed
- Fix error when saving entry where content is empty array. [#8813](https://github.com/statamic/cms/issues/8813) by @mauricewijnia
- Antlers: Fix custom variable assignment inside tags. [#8818](https://github.com/statamic/cms/issues/8818) by @JohnathonKoster
- GraphQL: Fix assets not resolving query builders. [#8809](https://github.com/statamic/cms/issues/8809) by @arcs-
- Fix the "Set to now" button being visible when read only. [#8816](https://github.com/statamic/cms/issues/8816) by @ryanmitchell
- Remove requirement of orderable collection from next/prev tags. [#8810](https://github.com/statamic/cms/issues/8810) by @jasonvarga
- Clean up Section fieldtype styles. [#8807](https://github.com/statamic/cms/issues/8807) by @caseydwyer
- Fix missing response from `afterRequestCompleted`. [#8801](https://github.com/statamic/cms/issues/8801) by @jacksleight
- Bump postcss from 8.4.21 to 8.4.31 [#8817](https://github.com/statamic/cms/issues/8817) by @dependabot



## 4.24.0 (2023-10-02)

### What's new
- Date range filter. [#8779](https://github.com/statamic/cms/issues/8779) by @ryanmitchell
- Add `site` to preview target variables. [#8780](https://github.com/statamic/cms/issues/8780) by @arcs-

### What's fixed
- Fix toggle fieldtype shrinkage. [#8790](https://github.com/statamic/cms/issues/8790) by @caseydwyer
- Ensure we only check that visible fields are filled in filters. [#8778](https://github.com/statamic/cms/issues/8778) by @ryanmitchell
- Default to first collection's sort config in entries fieldtype. [#8782](https://github.com/statamic/cms/issues/8782) by @ryanmitchell
- Use autocomplete attribute in the default text template. [#8774](https://github.com/statamic/cms/issues/8774) by @jeroenimpres
- Propagate save withEvents to the direct descendants on entry save. [#8786](https://github.com/statamic/cms/issues/8786) by @ryanmitchell
- Fix commands registered by class causing an error in `please`. [#8784](https://github.com/statamic/cms/issues/8784) by @SylvesterDamgaard
- Bump composer requirement. [#8789](https://github.com/statamic/cms/issues/8789) by @jasonvarga



## 4.23.2 (2023-09-25)

### What's fixed
- Fix impersonation of own account when using Eloquent users. [#8763](https://github.com/statamic/cms/issues/8763) by @ryanmitchell
- Fix static caching with Livewire 3. [#8762](https://github.com/statamic/cms/issues/8762) by @aerni



## 4.23.1 (2023-09-22)

### What's improved
- German translations. [#8757](https://github.com/statamic/cms/issues/8757) by @helloDanuk
- French translations. [#8754](https://github.com/statamic/cms/issues/8754) [#8750](https://github.com/statamic/cms/issues/8750) by @ebeauchamps
- Dutch translations. [#8747](https://github.com/statamic/cms/issues/8747) by @robdekort

### What's fixed
- Fix eloquent like query error. [#8753](https://github.com/statamic/cms/issues/8753) by @ryanmitchell



## 4.23.0 (2023-09-20)

### What's new
- Add ability to impersonate a user. [#8622](https://github.com/statamic/cms/issues/8622) by @ryanmitchell
- New modifier for AP and MLA style headlines. [#8731](https://github.com/statamic/cms/issues/8731) by @jackmcdade
- Add `orderByDesc` method to the query builder. [#8735](https://github.com/statamic/cms/issues/8735) by @duncanmcclean
- Add replicator_preview toggle to Blueprint editor. [#8297](https://github.com/statamic/cms/issues/8297) by @jacksleight

### What's fixed
- Fix incorrect entries_count in multisite when using localized term slugs. [#8743](https://github.com/statamic/cms/issues/8743) by @ryanmitchell
- Fix CSRF field related test failures. [#8746](https://github.com/statamic/cms/issues/8746) by @jasonvarga
- Make title in revision preview computed. [#8745](https://github.com/statamic/cms/issues/8745) by @jonassiewertsen
- Adjust data passed to live preview targets. [#8742](https://github.com/statamic/cms/issues/8742) by @jasonvarga
- Fix sidebar missing background on term publish form. [#8741](https://github.com/statamic/cms/issues/8741) by @jasonvarga
- Apply overflow styling to user listing. [#8739](https://github.com/statamic/cms/issues/8739) by @jasonvarga
- Empty form widget styling is now consistent with other widgets. [#8736](https://github.com/statamic/cms/issues/8736) by @jackmcdade
- Fall back to default site when selected one is invalid. [#8721](https://github.com/statamic/cms/issues/8721) by @jackmcdade
- Fix single digit month not working on whereMonth. [#8697](https://github.com/statamic/cms/issues/8697) by @arifhp86
- Make eloquent 'like' queries case insensitive. [#8243](https://github.com/statamic/cms/issues/8243) by @ryanmitchell
- Fix custom Antlers variables not being updated within recursive loops. [#8725](https://github.com/statamic/cms/issues/8725) by @JohnathonKoster
- Fix Antlers view variable leak. [#8728](https://github.com/statamic/cms/issues/8728) by @JohnathonKoster



## 4.22.0 (2023-09-18)

### What's new
- Ability to give access to only form blueprints and not all blueprints. [#7923](https://github.com/statamic/cms/issues/7923) by @ryanmitchell
- Add an `@antlers` Blade directive pair. [#8692](https://github.com/statamic/cms/issues/8692) by @JohnathonKoster
- Allow for a honeypot field on `user:register` tag. [#8704](https://github.com/statamic/cms/issues/8704) by @ryanmitchell
- Add 'on' and 'off' autocomplete values to text field. [#8679](https://github.com/statamic/cms/issues/8679) by @stoffelio

### What's improved
- Autofocus on new array field row's first input. [#8710](https://github.com/statamic/cms/issues/8710) by @jackmcdade
- Add placeholder text to make Taggable usage more clear. [#8703](https://github.com/statamic/cms/issues/8703) by @jackmcdade
- Improve visibility of overflowing set picker items. [#8701](https://github.com/statamic/cms/issues/8701) by @jackmcdade

### What's fixed
- Fix nav item active status on user modified navs. [#8685](https://github.com/statamic/cms/issues/8685) by @jesseleite
- Make condition operators translatable. [#8724](https://github.com/statamic/cms/issues/8724) by @jackmcdade
- Fix cache tag sometimes outputting placeholder Antlers strings. [#8401](https://github.com/statamic/cms/issues/8401) by @JohnathonKoster
- Fix search snippets for bard/replicator content. [#7545](https://github.com/statamic/cms/issues/7545) by @stephensamra
- Fix sidebar's empty card when no actions are present. [#8720](https://github.com/statamic/cms/issues/8720) by @jackmcdade
- Fix fluent tag camelCase params. [#8715](https://github.com/statamic/cms/issues/8715) by @jackmcdade
- Prevent deletion of selection when filtering in stack selector. [#8693](https://github.com/statamic/cms/issues/8693) by @wiebkevogel
- More thoroughly escape and truncate Code replicator previews. [#8718](https://github.com/statamic/cms/issues/8718) by @jackmcdade
- Stop forcing max_items: 1 on form fields. [#8713](https://github.com/statamic/cms/issues/8713) by @jackmcdade
- Fix hamburger icon wompyness. [#8700](https://github.com/statamic/cms/issues/8700) by @jackmcdade



## 4.21.0 (2023-09-07)

### What's new
- Add fullscreen and rulers to the code fieldtype. [#8509](https://github.com/statamic/cms/issues/8509) by @petemolinero
- Add CollectionTree and NavTree contracts and bindings. [#8658](https://github.com/statamic/cms/issues/8658) by @ryanmitchell
- Add config setting so CSV submission export headers can use field `display` instead of `handle`. [#8660](https://github.com/statamic/cms/issues/8660) by @ryanmitchell
- Register/export set and field related components. [#8577](https://github.com/statamic/cms/issues/8577) by @jacksleight
- Add autocomplete attribute to text fieldtype. [#8623](https://github.com/statamic/cms/issues/8623) by @jeroenimpres
- Add error redirect on user password reset form. [#7935](https://github.com/statamic/cms/issues/7935) by @ryanmitchell

### What's improved
- French translations. [#8653](https://github.com/statamic/cms/issues/8653) by @ebeauchamps

### What's fixed
- Fix list fieldtype focus infinite loop. [#8674](https://github.com/statamic/cms/issues/8674) by @ryanmitchell
- Fix nested Bard addEventListener error. [#8676](https://github.com/statamic/cms/issues/8676) by @jacksleight
- Set end range date to end of day in the date fieldtype. [#8648](https://github.com/statamic/cms/issues/8648) by @jonassiewertsen
- Ensure Live Preview is always excluded from static caching. [#7183](https://github.com/statamic/cms/issues/7183) by @FrittenKeeZ
- Use Laravel url helper instead to get the site url. [#8659](https://github.com/statamic/cms/issues/8659) by @jonassiewertsen
- Prevent form section instructions cascading into field instructions. [#8651](https://github.com/statamic/cms/issues/8651) by @jesseleite
- Reticulate fewer splines. [#8655](https://github.com/statamic/cms/issues/8655) by @robdekort



## 4.20.0 (2023-08-30)

### What's new
- Add duration field to GraphQL AssetInterface. [#8638](https://github.com/statamic/cms/issues/8638) by @notnek

### What's improved
- German translations. [#8649](https://github.com/statamic/cms/issues/8649) by @helloDanuk
- Dutch translations. [#8629](https://github.com/statamic/cms/issues/8629) by @jeroenpeters1986
- Navs fieltype icon. [#8621](https://github.com/statamic/cms/issues/8621) by @jackmcdade

### What's fixed
- Fix changing image format on upload when using source preset. [#8645](https://github.com/statamic/cms/issues/8645) by @jesseleite
- Fix asset & term reference updaters when using new set groups blueprint config. [#8630](https://github.com/statamic/cms/issues/8630) by @jesseleite
- Fix data loss when reordering sets with revealer fields. [#8620](https://github.com/statamic/cms/issues/8620) by @jacksleight
- Pint updates. [#8650](https://github.com/statamic/cms/issues/8650) by @jasonvarga



## 4.19.0 (2023-08-23)

### What's new
- Navs fieldtype. [#8619](https://github.com/statamic/cms/issues/8619) by @jasonvarga
- Support line breaks in Bard inline mode. [#8598](https://github.com/statamic/cms/issues/8598) by @jacksleight
- Add a way to determine which entry saved event was the initiator. [#8605](https://github.com/statamic/cms/issues/8605) by @jasonvarga

### What's improved
- You now redirect to the CP login screen when logging out. [#8602](https://github.com/statamic/cms/issues/8602) by @jasonvarga
- French translations. [#8612](https://github.com/statamic/cms/issues/8612) by @ebeauchamps
- Swedish translations. [#8600](https://github.com/statamic/cms/issues/8600) by @andreasbohman

### What's fixed
- Fix v4 addons not appearing in listing. [#8611](https://github.com/statamic/cms/issues/8611) by @jasonvarga
- Fix Bard set picker positioning. [#8574](https://github.com/statamic/cms/issues/8574) by @o1y
- Fix dropdowns list positioning. [#8607](https://github.com/statamic/cms/issues/8607) by @flolanger
- Fix a hardcoded string. [#8601](https://github.com/statamic/cms/issues/8601) by @andreasbohman



## 4.18.0 (2023-08-17)

### What's new
- Expose `uniqid` JS function for generating unique IDs. [#8571](https://github.com/statamic/cms/issues/8571) by @jacksleight
- Allow renaming of row id handle in Grid, Bard, and Replicator. [#8407](https://github.com/statamic/cms/issues/8407) by @jonassiewertsen
- Support arbitrary attributes on the vite tag. [#8305](https://github.com/statamic/cms/issues/8305) by @jackmcdade

### What's fixed
- Make uploader synchronous. [#8592](https://github.com/statamic/cms/issues/8592) by @jasonvarga
- Fix alignment of menu icon. [#8589](https://github.com/statamic/cms/issues/8589) by @caseydwyer
- Pint updates. [#8586](https://github.com/statamic/cms/issues/8586) by @jasonvarga
- Fix slugify error. [#8583](https://github.com/statamic/cms/issues/8583) by @jasonvarga
- Only save generated title if it's different. [#8101](https://github.com/statamic/cms/issues/8101) by @aerni
- Make the views field handle reserved. [#8576](https://github.com/statamic/cms/issues/8576) by @jasonvarga
- Fix special character handling in created CP nav sections. [#8568](https://github.com/statamic/cms/issues/8568) by @jesseleite



## 4.17.0 (2023-08-10)

### What's improved
- Improve performance of getting asset metadata when using the local filesystem. [#7887](https://github.com/statamic/cms/issues/7887) by @FrittenKeeZ

### What's fixed
- Fix globals save event. [#8564](https://github.com/statamic/cms/issues/8564) by @jasonvarga
- REST API Globals return resolved relations via opt-in method. [#8555](https://github.com/statamic/cms/issues/8555) by @martink635



## 4.16.0 (2023-08-08)

### What's new
- Split global set variables into its own repository and Stache store. [#8343](https://github.com/statamic/cms/issues/8343) by @ryanmitchell
- Add `is_svg` to augmented assets. [#8549](https://github.com/statamic/cms/issues/8549) by @ryanmitchell

### What's fixed
- Fix a number of multisite issues regarding data fallbacks, search, queries, and more. [#8505](https://github.com/statamic/cms/issues/8505) by @jasonvarga
- Fix nested field ids and focus behavior of some fieldtypes. [#8531](https://github.com/statamic/cms/issues/8531) by @jackmcdade
- Fix Bard legacy content handling. [#8544](https://github.com/statamic/cms/issues/8544) by @jasonvarga
- Consolidate behavior of searching in users listing and users field type. [#8543](https://github.com/statamic/cms/issues/8543) by @ryanmitchell
- Make UpdatesBadge component only update the count when the response is a number. [#8540](https://github.com/statamic/cms/issues/8540) by @martyf
- Fix missing Control Panel favicons. [#8532](https://github.com/statamic/cms/issues/8532) by @martyf



## 4.15.0 (2023-08-02)

### What's new
- Allow entries fieldtypes to be filtered by `title`. [#8464](https://github.com/statamic/cms/issues/8464) by @ryanmitchell
- Add hooks for Globals Publish Form. [#7618](https://github.com/statamic/cms/issues/7618) by @duncanmcclean
- Add `query_scopes` and searching to the form fieldtype. [#8533](https://github.com/statamic/cms/issues/8533) by @ryanmitchell

### What's fixed
- Fix variable name collisions when using the `as` tag param. [#8386](https://github.com/statamic/cms/issues/8386) by @JohnathonKoster
- Fix missing support for Collections and QueryBuilders in the `random` modifier. [#8398](https://github.com/statamic/cms/issues/8398) by @edalzell
- Fix entry listing hit target. [#8538](https://github.com/statamic/cms/issues/8538) by @jackmcdade
- Fix Bard image alt logic. [#8537](https://github.com/statamic/cms/issues/8537) by @jackmcdade
- Fix case sensitivity of operators in the query builder. [#8522](https://github.com/statamic/cms/issues/8522) by @ryanmitchell



## 4.14.0 (2023-08-01)

### What's improved
- Improved the Bard Inline Image Extension. [#8131](https://github.com/statamic/cms/issues/8131) by @o1y
- French translations. [#8496](https://github.com/statamic/cms/issues/8496) by @ebeauchamps

### What's fixed
- Apostrophes no longer get slugified in JS. [#8524](https://github.com/statamic/cms/issues/8524) by @jackmcdade
- Allow relative URLs as preview targets. [#8490](https://github.com/statamic/cms/issues/8490) by @helloiamlukas
- Lower the Trial Banner Z-Index. [#8530](https://github.com/statamic/cms/issues/8530) by @jackmcdade
- Handle separate first & last name fields in User fieldtype. [#8507](https://github.com/statamic/cms/issues/8507) by @duncanmcclean
- Fix Relationship Index Field Item height. [#8529](https://github.com/statamic/cms/issues/8529) by @jackmcdade
- Conform Bard Set Headers to match Replicator. [#8528](https://github.com/statamic/cms/issues/8528) by @jackmcdade
- Fix form listing table corner clip. [#8527](https://github.com/statamic/cms/issues/8527) by @jackmcdade
- Fix Relationship Item long titles. [#8526](https://github.com/statamic/cms/issues/8526) by @jackmcdade
- Implement `ContainsQueryableValues` on users. [#8455](https://github.com/statamic/cms/issues/8455) by @ryanmitchell
- Ignore processing GIFs on file upload. [#8512](https://github.com/statamic/cms/issues/8512) by @duncanmcclean
- Fix link insert cancel in Markdown field. [#8525](https://github.com/statamic/cms/issues/8525) by @jackmcdade
- Fix the History Icon path. [#8517](https://github.com/statamic/cms/issues/8517) by @jackmcdade
- Reset page in asset browser when searching. [#8506](https://github.com/statamic/cms/issues/8506) by @duncanmcclean
- Fix incorrect doctype in `Submission` contract. [#8504](https://github.com/statamic/cms/issues/8504) by @duncanmcclean



## 4.13.2 (2023-07-26)

### What's fixed
- Fix create entry button. [#8493](https://github.com/statamic/cms/issues/8493) by @jasonvarga



## 4.13.1 (2023-07-25)

### What's fixed
- Fix ranged date validation. [#8447](https://github.com/statamic/cms/issues/8447) by @AndreasSchantl
- Use site in create entry button on collection tree view. [#8487](https://github.com/statamic/cms/issues/8487) by @jasonvarga



## 4.13.0 (2023-07-24)

### What's new
- Add word count option to Bard. [#8445](https://github.com/statamic/cms/issues/8445) by @markguleno
- Support querying entries in a specific site in GraphQL. [#8446](https://github.com/statamic/cms/issues/8446) by @fabiangigler
- Support for using recursion on arbitrary array data in Antlers. [#8421](https://github.com/statamic/cms/issues/8421) by @JohnathonKoster

### What's fixed
- Fix a hardcoded live preview URL when editing taxonomy terms. [#8461](https://github.com/statamic/cms/issues/8461) by @ryanmitchell
- Hide heading when there are no unlisted addons. [#8479](https://github.com/statamic/cms/issues/8479) by @duncanmcclean
- Handle `nocache` tag error. [#8449](https://github.com/statamic/cms/issues/8449) by @jasonvarga
- Avoid showing Stache size label in the Cache utility when there is no size. [#8480](https://github.com/statamic/cms/issues/8480) by @duncanmcclean



## 4.12.0 (2023-07-20)

### What's new
- Add `query_scopes` option to the relationship fieldtypes. [#8456](http://github.com/statamic/cms/pull/8456) by @jacksleight

### What's improved
- French translations. [#8451](http://github.com/statamic/cms/pull/8451) by @ebeauchamps

### What's fixed
- Fix error handling for recent curl bug. [#8475](http://github.com/statamic/cms/pull/8475) by @jesseleite
- Bump word-wrap from 1.2.3 to 1.2.4. [#8466](http://github.com/statamic/cms/pull/8466) by @dependabot
- Only show code block copy button on https. [#8457](http://github.com/statamic/cms/pull/8457) by @jasonvarga



## 4.11.0 (2023-07-13)

### What's new
- Markdown upgrades. [#8417](https://github.com/statamic/cms/issues/8417) by @jackmcdade

### What's fixed
- Swap SVG sanitizer packages for one with an appropriate license. [#8428](https://github.com/statamic/cms/issues/8428) by @jasonvarga
- Fix taxonomy term filtering inconsistencies between Tag and API. [#8389](https://github.com/statamic/cms/issues/8389) by @jesseleite
- Bump `semver` from 5.7.1 to 5.7.2 [#8434](https://github.com/statamic/cms/issues/8434) by @dependabot
- Pint formatting [#8444](https://github.com/statamic/cms/issues/8444) by @jasonvarga



## 4.10.2 (2023-07-10)

### What's improved
- Chinese translations. [#8418](https://github.com/statamic/cms/issues/8418) by @xuchunyang

### What's fixed
- Fix pagination in entries stack selector. [#8426](https://github.com/statamic/cms/issues/8426) by @jasonvarga
- Bump tough-cookie from 4.1.2 to 4.1.3 [#8423](https://github.com/statamic/cms/issues/8423) by @dependabot



## 4.10.1 (2023-07-06)

### What's improved
- French translations. [#8409](https://github.com/statamic/cms/issues/8409) by @ebeauchamps

### What's fixed
- Fix blueprint events being dispatched repeatedly, especially when using Laravel Telescope. [#8048](https://github.com/statamic/cms/issues/8048) by @morhi
- Fix mapping of search results in entries fieldtype. [#8414](https://github.com/statamic/cms/issues/8414) by @jasonvarga
- Fix Live Preview viewport not being reset when switching back to "Responsive". [#8402](https://github.com/statamic/cms/issues/8402) by @wiebkevogel



## 4.10.0 (2023-07-05)

### What's new
- Added `sanitize` param to the `svg` tag. [#8408](https://github.com/statamic/cms/issues/8408) by @jasonvarga

### What's improved
- French translations. [#8388](https://github.com/statamic/cms/issues/8388) by @ebeauchamps

### What's fixed
- Bring back the password reset link for non-OAuth sites. [#8396](https://github.com/statamic/cms/issues/8396) by @jackmcdade
- Add some missing translation calls. [#8387](https://github.com/statamic/cms/issues/8387) by @ebeauchamps



## 4.9.2 (2023-06-30)

### What's fixed
- Fix search index not being used in the entries fieldtype. [#8253](https://github.com/statamic/cms/issues/8253) by @ryanmitchell
- Fix Antlers Profiler memory issue. [#8384](https://github.com/statamic/cms/issues/8384) by @JohnathonKoster
- Fix `search:results` duplicated code, and pagination parameter types. [#8314](https://github.com/statamic/cms/issues/8314) by @ryanmitchell
- Fix missing `lowercase` validation message. [#8383](https://github.com/statamic/cms/issues/8383) by @marcorieser
- Fix `user:profile_form` not catching certain validation rules. [#8264](https://github.com/statamic/cms/issues/8264) by @ryanmitchell
- Fix Antlers Profilder depth and disabled layout logic. [#8368](https://github.com/statamic/cms/issues/8368) by @JohnathonKoster
- Fix bottom margin on a section fieldtype when no instructions are present. [#8371](https://github.com/statamic/cms/issues/8371) by @martyf
- Fix `toggle` fields collapsing too far and not wrapping instructions in sidebars. [#8366](https://github.com/statamic/cms/issues/8366) by @jackmcdade
- Fix Grid field instructions tooltip not rendering as HTML properly. [#8367](https://github.com/statamic/cms/issues/8367) by @jackmcdade
- Switch StyleCI with Pint. Apply Pint code style fixes. [#8310](https://github.com/statamic/cms/issues/8310) by @jasonvarga
- Remove some Laravel 8 specific code. [#8385](https://github.com/statamic/cms/issues/8385) by @jesseleite



## 4.9.1 (2023-06-27)

### What's fixed
- Fix visibility of Cache FileStore path method. [#8365](https://github.com/statamic/cms/issues/8365) by @jasonvarga
- Adjust how Antlers Profiler handles larger amounts of data to prevent JS errors. [#8358](https://github.com/statamic/cms/issues/8358) by @JohnathonKoster



## 4.9.0 (2023-06-26)

### What's new
- Add setting to disable Antlers profiler. [#8356](https://github.com/statamic/cms/issues/8356) by @jasonvarga
- Add Spacer fieldtype. [#8326](https://github.com/statamic/cms/issues/8326) by @aerni
- Add `is_external_url` modifer. [#8351](https://github.com/statamic/cms/issues/8351) by @martyf
- Entry data values can be queried directly for efficiency. [#7371](https://github.com/statamic/cms/issues/7371) by @ryanmitchell

### What's improved
- Use Blink Cache for flattened pages in Collection Structure. [#7476](https://github.com/statamic/cms/issues/7476) by @o1y
- French translations. [#8348](https://github.com/statamic/cms/issues/8348) by @ebeauchamps

### What's fixed
- Fix links within Bard fields not being localized. [#8319](https://github.com/statamic/cms/issues/8319) by @modrictin
- Fix checkboard background on asset tiles. [#8355](https://github.com/statamic/cms/issues/8355) by @jackmcdade
- Fix relationship fieldtype encoding issue. [#8349](https://github.com/statamic/cms/issues/8349) by @zsoltjanes



## 4.8.0 (2023-06-23)

### What's new
- Antlers performance profiler tab for Debugbar. [#8323](https://github.com/statamic/cms/issues/8323) by @JohnathonKoster
- User Groups may have a blueprint to allow for custom fields. [#6506](https://github.com/statamic/cms/issues/6506) by @ryanmitchell
- Arrays can be passed to the `user_groups` tag. [#8336](https://github.com/statamic/cms/issues/8336) by @ryanmitchell
- Added `cpDownloadUrl` method to the `Asset` class. [#8334](https://github.com/statamic/cms/issues/8334) by @jonassiewertsen

### What's fixed
- Fix missing GraphQL types for more complex fields (e.g. Grid) in user blueprints. [#8335](https://github.com/statamic/cms/issues/8335) by @jesseleite



## 4.7.0 (2023-06-20)

### What's new
- Debugbar support for Antlers. [#8296](https://github.com/statamic/cms/issues/8296) by @JohnathonKoster
- Allow sorting user listing by last login, and hide arrows for unsortable columns. [#8283](https://github.com/statamic/cms/issues/8283) by @jacksleight
- Allow icon fieldtype to output CP icons. [#8306](https://github.com/statamic/cms/issues/8306) by @jackmcdade

### What's fixed
- Forgot password link is available with OAuth. [#8330](https://github.com/statamic/cms/issues/8330) by @jasonvarga
- Fix stache lock config comment. [#8293](https://github.com/statamic/cms/issues/8293) by @SimJoSt
- Fix overly strict comparisons in Antlers. [#8327](https://github.com/statamic/cms/issues/8327) by @JohnathonKoster
- Check previous URL for preserving Live Preview iframe scroll. [#7769](https://github.com/statamic/cms/issues/7769) by @GioChocolateBro
- Fix aspect ratio in video fieldtype. [#8302](https://github.com/statamic/cms/issues/8302) by @jackmcdade



## 4.6.0 (2023-06-07)

### What's new
- Added methods to reduce computed value callback calls. [#8248](https://github.com/statamic/cms/issues/8248) by @jacksleight
- Added custom sort field methods to Collection [#8278](https://github.com/statamic/cms/issues/8278) by @jasonvarga

### What's improved
- French translations. [#8262](https://github.com/statamic/cms/issues/8262) by @ebeauchamps

### What's fixed
- Bump vite from 4.2.1 to 4.2.3 [#8268](https://github.com/statamic/cms/issues/8268) by @dependabot



## 4.5.0 (2023-06-02)

### What's new
- Add a CSS `classes` modifier. [#8237](https://github.com/statamic/cms/issues/8237) by @JohnathonKoster
- Add Blade support to `user` tags. [#8223](https://github.com/statamic/cms/issues/8223) [#8242](https://github.com/statamic/cms/issues/8242) by @ryanmitchell

### What's improved
- Add missing validation messages for `starts_with` and `ends_with`. [#8247](https://github.com/statamic/cms/issues/8247) by @ryanmitchell
- Use real Facade for `Cascade`. [#8198](https://github.com/statamic/cms/issues/8198) by @edalzell

### What's fixed
- Fix relationship fields not being scoped to selected site in nav item editor. [#8212](https://github.com/statamic/cms/issues/8212) by @duncanmcclean
- Fix error when searching users with a search index configured. [#8239](https://github.com/statamic/cms/issues/8239) by @ryanmitchell
- Fix `embed_url` modifier not handling start time on YouTube URLs. [#8250](https://github.com/statamic/cms/issues/8250) by @JohnathonKoster
- Fix replacements so the `unique_user_value` validation rule works as expected. [#8241](https://github.com/statamic/cms/issues/8241) by @ryanmitchell
- Fix form actions. [#8240](https://github.com/statamic/cms/issues/8240) by @duncanmcclean
- Fix asset grid button visibility. [#8232](https://github.com/statamic/cms/issues/8232) by @jasonvarga
- Fix asset grid folder dropdown. [#8228](https://github.com/statamic/cms/issues/8228) by @jacksleight
- Fix missing bard settings. [#8231](https://github.com/statamic/cms/issues/8231) by @jasonvarga



## 4.4.0 (2023-05-30)

### What's new
- Expose tiptap/vue-2 in the Bard JS API. [#8197](https://github.com/statamic/cms/issues/8197) by @jacksleight

### What's improved
- More quotes added to the Flat Camp command. [#8206](https://github.com/statamic/cms/issues/8206) by @jasonvarga
- Improve entry status display in the entry selector stack. [#8210](https://github.com/statamic/cms/issues/8210) by @duncanmcclean
- Change visibility of some Cascade methods. [#8204](https://github.com/statamic/cms/issues/8204) by @modrictin

### What's fixed
- Fix custom fieldtype SVGs. [#8207](https://github.com/statamic/cms/issues/8207) by @duncanmcclean
- Fix read-only state in the assets fieldtype. [#8214](https://github.com/statamic/cms/issues/8214) by @jesseleite
- Fix searching with asset folder fieldtype. [#8215](https://github.com/statamic/cms/issues/8215) by @duncanmcclean
- Fix nav builder icons. [#8221](https://github.com/statamic/cms/issues/8221) by @jasonvarga
- Fix utility handle to slug conversion. [#8213](https://github.com/statamic/cms/issues/8213) by @jasonvarga
- Fix asset selection request query length. [#8209](https://github.com/statamic/cms/issues/8209) by @duncanmcclean
- Fix 'resolving deltas' on git push being logged as error. [#8176](https://github.com/statamic/cms/issues/8176) by @jesseleite
- Fix date validation. [#8205](https://github.com/statamic/cms/issues/8205) [#8219](https://github.com/statamic/cms/issues/8219) by @jasonvarga
- Fix line breaks not being displayed in automagic form emails. [#8200](https://github.com/statamic/cms/issues/8200) by @aerni



## 4.3.0 (2023-05-24)

### What's new
- Flat Camp! 🏕️ [#8191](https://github.com/statamic/cms/issues/8191) by @jasonvarga

### What's improved
- Norwegian translations. [#8186](https://github.com/statamic/cms/issues/8186) by @espenlg
- French translations. [#8178](https://github.com/statamic/cms/issues/8178) by @ebeauchamps



## 4.2.0 (2023-05-19)

### What's new
- New addons/fieldtypes use Vite. [#8126](https://github.com/statamic/cms/issues/8126) by @jasonvarga
- Allow custom searchables to be excluded from CP search. [#7700](https://github.com/statamic/cms/issues/7700) by @jacksleight

### What's improved
- French translations. [#8169](https://github.com/statamic/cms/issues/8169) by @ebeauchamps

### What's fixed
- Fix date validation. [#8174](https://github.com/statamic/cms/issues/8174) by @jasonvarga
- Fix fullscreen mode buttons in Grid and Replicator. [#8168](https://github.com/statamic/cms/issues/8168) by @jasonvarga



## 4.1.3 (2023-05-17)

### What's improved
- French translations [#8142](https://github.com/statamic/cms/issues/8142) by @ebeauchamps

### What's fixed
- Fix margins disappearing in Replicator when hitting max items. [#8164](https://github.com/statamic/cms/issues/8164) by @jasonvarga
- Fix CP asset reupload not working for non-super users. [#8163](https://github.com/statamic/cms/issues/8163) by @joshuablum
- Fix suggestable condition fields. [#8160](https://github.com/statamic/cms/issues/8160) by @jasonvarga
- Fix path for SVG copy icon in the Updater popover. [#8161](https://github.com/statamic/cms/issues/8161) by @joshuablum
- Fix 404 response status view cascade hydration. [#8159](https://github.com/statamic/cms/issues/8159) by @jesseleite
- Fix error when exporting starter kit. [#8156](https://github.com/statamic/cms/issues/8156) by @ryanmitchell
- Fix publishables not getting auto published. [#8151](https://github.com/statamic/cms/issues/8151) by @jasonvarga
- Fix asset fieldtype min_files validation and the show set alt option. [#8148](https://github.com/statamic/cms/issues/8148) by @jasonvarga
- Fix missing default field in `color` fieldtype settings. [#8152](https://github.com/statamic/cms/issues/8152) by @jackmcdade
- Fix visibility of white swatch in the `color` fieldtype. [#8153](https://github.com/statamic/cms/issues/8153) by @mytchallb



## 4.1.2 (2023-05-15)

### What's improved
- Invalid Avatar URL falls back to initials. [#8139](https://github.com/statamic/cms/issues/8139) by @jasonvarga
- Russian translations. [#8135](https://github.com/statamic/cms/issues/8135) by @dragomano

### What's fixed
- Adjust a couple of fieldtype translations. [#8141](https://github.com/statamic/cms/issues/8141) by @jasonvarga
- Fix icon fieldtype default. [#8140](https://github.com/statamic/cms/issues/8140) by @jasonvarga



## 4.1.1 (2023-05-12)

### What's fixed
- Fix relationship field buttons drag delay. [#8121](https://github.com/statamic/cms/issues/8121) by @o1y
- Fix imported Bard button config override. [#8122](https://github.com/statamic/cms/issues/8122) by @jacksleight
- Fix link fieldtype's options appearing behind things. [#8130](https://github.com/statamic/cms/issues/8130) by @jasonvarga



## 4.1.0 (2023-05-11)

### What's new
- Include URL in Live Preview post message. [#8100](https://github.com/statamic/cms/issues/8100) by @jacksleight

### What's improved
- French translations. [#8109](https://github.com/statamic/cms/issues/8109) by @ebeauchamps
- German translations. [#8096](https://github.com/statamic/cms/issues/8096) by @helloDanuk

### What's fixed
- Fix confirmation modal's confirm button text. [#8111](https://github.com/statamic/cms/issues/8111) by @jasonvarga
- Fix asset upload button disappearing if you have selections. [#8097](https://github.com/statamic/cms/issues/8097) by @jackmcdade
- Fix template fieldtype's options appearing behind things. [#8119](https://github.com/statamic/cms/issues/8119) by @jasonvarga
- Fix full screen mode translation casing. [#8108](https://github.com/statamic/cms/issues/8108) by @jasonvarga
- Fix consistency of sortable items. [#8083](https://github.com/statamic/cms/issues/8083) by @jasonvarga
- Fix items disappearing behind stacks. [#8103](https://github.com/statamic/cms/issues/8103) by @jasonvarga
- Fix issue with Time fields inside Grid. [#8094](https://github.com/statamic/cms/issues/8094) by @jackmcdade
- Fix asset grid tile size. [#8095](https://github.com/statamic/cms/issues/8095) by @jackmcdade
- Fix Bard fullscreen button aria-label. [#8089](https://github.com/statamic/cms/issues/8089) by @jacksleight



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
