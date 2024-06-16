# Release Notes

## 5.7.3 (2024-06-13)

### What's fixed
- Improve performance of nested imported fieldsets [#10280](https://github.com/statamic/cms/issues/10280) by @riasvdv



## 5.7.2 (2024-06-06)

### What's fixed
- Prevent adding content to Blade stacks twice [#10271](https://github.com/statamic/cms/issues/10271) by @JohnathonKoster
- Fix publish form actions permission check [#10274](https://github.com/statamic/cms/issues/10274) by @jacksleight



## 5.7.1 (2024-06-05)

### What's fixed
- Fix tiny top left border radius on collection widget [#10266](https://github.com/statamic/cms/issues/10266) by @duncanmcclean
- Prevent text in overflowing code blocks in the Control Panel [#10269](https://github.com/statamic/cms/issues/10269) by @rob
- Fix status not being visible in collection widget [#10267](https://github.com/statamic/cms/issues/10267) by @duncanmcclean
- Fix failed slug validation when slug contains underscores [#10265](https://github.com/statamic/cms/issues/10265) by @o1y
- Fix unnecessary array default fieldtype values [#10272](https://github.com/statamic/cms/issues/10272) by @duncanmcclean
- French translations [#10261](https://github.com/statamic/cms/issues/10261) by @ebeauchamps



## 5.7.0 (2024-06-04)

### What's new
- Validation may be added to asset containers for file uploads [#10227](https://github.com/statamic/cms/issues/10227) by @edalzell
- Allow overriding `statusIcons` property in relationship fieldtype [#10253](https://github.com/statamic/cms/issues/10253) by @duncanmcclean

### What's fixed
- Merge defaults into field publish arrays [#10246](https://github.com/statamic/cms/issues/10246) by @jasonvarga
- Merge config field defaults into field config [#10139](https://github.com/statamic/cms/issues/10139) by @duncanmcclean
- Fix dark mode entry and navigation deletion modals [#10254](https://github.com/statamic/cms/issues/10254) by @aaronbushnell
- Fix broken docs URLs; rename docs URL redirect to permalink [#10249](https://github.com/statamic/cms/issues/10249) by @rob
- Localize field titles in collection filter [#10250](https://github.com/statamic/cms/issues/10250) by @robdekort
- Only run parent code in `Revisable` trait when item is an entry [#10245](https://github.com/statamic/cms/issues/10245) by @duncanmcclean
- Make `select` field values in entry tables localizable [#10241](https://github.com/statamic/cms/issues/10241) by @robdekort
- Fix styling of "Live Preview" and "Visit URL" buttons on terms publish form [#10237](https://github.com/statamic/cms/issues/10237) by @duncanmcclean
- Use multibyte methods for obfuscate [#10201](https://github.com/statamic/cms/issues/10201) by @lakkes-ra
- Ensure `prose`-based strong tag is readable in dark mode [#10236](https://github.com/statamic/cms/issues/10236) by @aaronbushnell
- Fix relative modifier test [#10242](https://github.com/statamic/cms/issues/10242) by @jasonvarga
- Decrease the failure rate of RandomTest [#10238](https://github.com/statamic/cms/issues/10238) by @jasonvarga
- Address slow Windows GitHub actions [#10243](https://github.com/statamic/cms/issues/10243) by @jasonvarga
- Danish translations [#10231](https://github.com/statamic/cms/issues/10231) by @mortenebak
- Norwegian translations [#10248](https://github.com/statamic/cms/issues/10248) by @espenlg



## 5.6.2 (2024-05-30)

### What's fixed
- Prevent user registration form saving `password_confirmation` [#10228](https://github.com/statamic/cms/issues/10228) by @ryanmitchell
- Fix needing to deselect an asset to reselect when using max_files 1 [#10225](https://github.com/statamic/cms/issues/10225) by @jackmcdade
- Allow long bard/replicator set names to wrap [#10223](https://github.com/statamic/cms/issues/10223) by @jackmcdade
- Fix Code fieldtype not removing the overflow hidden style on body when closing Fullscreen Mode [#10221](https://github.com/statamic/cms/issues/10221) by @jackmcdade


## 5.6.1 (2024-05-29)

### What's fixed
- Revert `select` modifier. [#10218](https://github.com/statamic/cms/issues/10218) by @jasonvarga
- Make label on Add Set button localizable [#10216](https://github.com/statamic/cms/issues/10216) by @duncanmcclean
- Fix JavaScript length error from Add Set button label [#10217](https://github.com/statamic/cms/issues/10217) by @duncanmcclean
- French translations [#10209](https://github.com/statamic/cms/issues/10209) by @ebeauchamps
- Danish translations [#10214](https://github.com/statamic/cms/issues/10214) by @mortenebak



## 5.6.0 (2024-05-28)

### What's new
- Allow extra `x-data` to be passed to alpine forms [#10174](https://github.com/statamic/cms/issues/10174) by @ryanmitchell
- Add `to_qs` modifier [#10196](https://github.com/statamic/cms/issues/10196) by @godismyjudge95
- Add `site` filter to TermsQuery [#10131](https://github.com/statamic/cms/issues/10131) by @arcs-
- Add `keys` and `values` modifiers [#10185](https://github.com/statamic/cms/issues/10185) by @godismyjudge95
- Add `merge` method to Eloquent User class [#10192](https://github.com/statamic/cms/issues/10192) by @duncanmcclean
- Add `depth` parameter to `flatten` modifier [#10187](https://github.com/statamic/cms/issues/10187) by @godismyjudge95
- ~Add `select` modifier~ (Reverted in 5.6.1) [#10183](https://github.com/statamic/cms/issues/10183) by @godismyjudge95
- Replicator "add set" button label option [#9806](https://github.com/statamic/cms/issues/9806) by @godismyjudge95

### What's fixed
- Static caching file path fallback [#9306](https://github.com/statamic/cms/issues/9306) by @royduin
- Fix error when augmenting Bard fields [#10104](https://github.com/statamic/cms/issues/10104) by @duncanmcclean
- Fix `ensureFieldHasConfig` for imported fields [#9243](https://github.com/statamic/cms/issues/9243) by @aerni
- Allows Antlers & Blade stacks to be used interchangeably [#10200](https://github.com/statamic/cms/issues/10200) by @JohnathonKoster
- Fix error when serializing eloquent query builders [#10189](https://github.com/statamic/cms/issues/10189) by @duncanmcclean
- Fix `@see` in docblock on `FormSubmission` facade [#10197](https://github.com/statamic/cms/issues/10197) by @duncanmcclean
- Maintain order of views after renaming [#10193](https://github.com/statamic/cms/issues/10193) by @duncanmcclean
- Filter out global set variables associated to deleted sites [#10195](https://github.com/statamic/cms/issues/10195) by @duncanmcclean
- Prevent some folders from listing in template fieldtype [#10031](https://github.com/statamic/cms/issues/10031) by @peimn
- Handle `null` in `bardText` modifier [#10199](https://github.com/statamic/cms/issues/10199) by @edalzell
- Fix border and shadow in closed nav [#10186](https://github.com/statamic/cms/issues/10186) by @peimn
- Fix link fieldtype state [#10182](https://github.com/statamic/cms/issues/10182) by @jasonvarga
- Fix Dark Mode Tree Node margins. [#10179](https://github.com/statamic/cms/issues/10179) by @jackmcdade
- Fix wrong dark mode colors in the updater [#10178](https://github.com/statamic/cms/issues/10178) by @jackmcdade
- Add dark mode to new User Wizard [#10171](https://github.com/statamic/cms/issues/10171) by @martyf
- Fix dark mode for Set Picker [#10173](https://github.com/statamic/cms/issues/10173) by @martyf
- Add dark mode support for list items [#10172](https://github.com/statamic/cms/issues/10172) by @martyf
- German translations [#10175](https://github.com/statamic/cms/issues/10175) by @helloDanuk
- French translations [#10170](https://github.com/statamic/cms/issues/10170) by @ebeauchamps



## 5.5.0 (2024-05-22)

### What's new
- Provide git binary var to commands array in config [#10154](https://github.com/statamic/cms/issues/10154) by @jesseleite
- Abstract a super-btn component [#10153](https://github.com/statamic/cms/issues/10153) by @jackmcdade

### What's fixed
- More Dark Fixes [#10165](https://github.com/statamic/cms/issues/10165) by @jackmcdade
- Fix routeData on null error [#10169](https://github.com/statamic/cms/issues/10169) by @jasonvarga
- Run GitHub Actions workflows only once [#10156](https://github.com/statamic/cms/issues/10156) by @Jubeki
- Update GitHub Actions workflow versions [#10136](https://github.com/statamic/cms/issues/10136) by @Jubeki



## 5.4.0 (2024-05-21)

### What's new
- Add `get_site` tag [#9580](https://github.com/statamic/cms/issues/9580) by @aerni
- Add support for a dark mode custom logo [#10123](https://github.com/statamic/cms/issues/10123) by @martyf
- Attribute Modifier [#9796](https://github.com/statamic/cms/issues/9796) by @potsky
- Available query scopes will show as options in field settings [#9933](https://github.com/statamic/cms/issues/9933) by @duncanmcclean

### What's fixed
- Prevent opening set picker when `max_sets` has been exceeded [#10133](https://github.com/statamic/cms/issues/10133) by @duncanmcclean
- Remove unnecessary `overflow-scroll` on submission listing [#10148](https://github.com/statamic/cms/issues/10148) by @duncanmcclean
- Reference git binary as a variable, rather than config setting [#10134](https://github.com/statamic/cms/issues/10134) by @duncanmcclean
- Fix actions in assets, forms, and form submissions [#10132](https://github.com/statamic/cms/issues/10132) by @duncanmcclean
- Fix 404 issues by reverting caching of site absolute url [#10135](https://github.com/statamic/cms/issues/10135) by @jasonvarga
- Remove unnecessary rounded corners on th elements [#10146](https://github.com/statamic/cms/issues/10146) by @jackmcdade
- Taggable fieldtype tweaks [#10121](https://github.com/statamic/cms/issues/10121) by @jasonvarga
- Style the Dark Mode login [#10143](https://github.com/statamic/cms/issues/10143) by @jackmcdade
- Fix dark mode license banner [#10147](https://github.com/statamic/cms/issues/10147) by @jackmcdade
- Fix styling issues with Assets Grid & Assets Fieldtype [#10149](https://github.com/statamic/cms/issues/10149) by @duncanmcclean
- Fix dark mode preference not being applied to login by saving to local storage [#10140](https://github.com/statamic/cms/issues/10140) by @jasonvarga
- Fix light mode drag handle colors [#10144](https://github.com/statamic/cms/issues/10144) by @jackmcdade
- Fix to add dark mode to Widget pagination background [#10122](https://github.com/statamic/cms/issues/10122) by @martyf
- Fixing up some missed layout elements for dark mode outside the cp [#10151](https://github.com/statamic/cms/issues/10151) by @jackmcdade



## 5.3.0 (2024-05-20)

### What's new
- Dark Mode [#10117](https://github.com/statamic/cms/issues/10117) by @peimn
- Ability to run actions from publish forms [#6375](https://github.com/statamic/cms/issues/6375) by @jacksleight
- Support Laravel precognition on user forms [#8924](https://github.com/statamic/cms/issues/8924) by @ryanmitchell

### What's fixed
- Fix issue returning some collections from tags [#10113](https://github.com/statamic/cms/issues/10113) by @JohnathonKoster
- Fix augmentation issues for URL nav items [#10086](https://github.com/statamic/cms/issues/10086) by @duncanmcclean
- Fix blueprints not being able to be manipulated more than once [#10061](https://github.com/statamic/cms/issues/10061) by @aerni
- Pluralize user activation email message [#10118](https://github.com/statamic/cms/issues/10118) by @jasonvarga
- Correct id and for pairs in user sign up Wizard [#10115](https://github.com/statamic/cms/issues/10115) by @martyf
- Prevent configuring multiple conditions for the same field [#10110](https://github.com/statamic/cms/issues/10110) by @duncanmcclean
- Respect the current site when returning a View [#10109](https://github.com/statamic/cms/issues/10109) by @aerni
- Fix entry model not being updated when importing entries [#10107](https://github.com/statamic/cms/issues/10107) by @duncanmcclean
- Sync entry form values after revision publish [#10095](https://github.com/statamic/cms/issues/10095) by @jacksleight
- Fix querying by status on non-dated collections [#10099](https://github.com/statamic/cms/issues/10099) by @jasonvarga
- Better performance when hydrating globals [#10096](https://github.com/statamic/cms/issues/10096) by @modrictin
- Ensure correct exclusion of URLs in static:warm command [#10092](https://github.com/statamic/cms/issues/10092) by @aerni
- Fix YAML fieldtype UI [#10097](https://github.com/statamic/cms/issues/10097) by @jackmcdade
- Organize user controllers [#10093](https://github.com/statamic/cms/issues/10093) by @jasonvarga
- Ensure default config values are available in form tag [#10088](https://github.com/statamic/cms/issues/10088) by @duncanmcclean



## 5.2.0 (2024-05-15)

### What's new
- Ability to opt out of async slug behavior, and opt out in field settings [#10075](https://github.com/statamic/cms/issues/10075) by @jasonvarga

### What's fixed
- Bring back support for archetype v1 [#10087](https://github.com/statamic/cms/issues/10087) by @jasonvarga
- Wrap columns in query builder [#10076](https://github.com/statamic/cms/issues/10076) by @jasonvarga
- Dutch translations [#10085](https://github.com/statamic/cms/issues/10085) by @dnwjn



## 5.1.0 (2024-05-14)

### What's new
- Bard's default buttons can be configured [#10062](https://github.com/statamic/cms/issues/10062) by @ryanmitchell

### What's fixed
- Handle entries in Link field with `is_external_url` modifier [#10071](https://github.com/statamic/cms/issues/10071) by @ammonitedigital
- Remove `submissions` key from `forms` config [#10066](https://github.com/statamic/cms/issues/10066) by @duncanmcclean
- Prevent errors when viewing nav after collection has been deleted [#10049](https://github.com/statamic/cms/issues/10049) by @duncanmcclean
- Prevent certain blueprint config keys getting stripped out [#10059](https://github.com/statamic/cms/issues/10059) by @jasonvarga
- Fix `nocache` and OAuth routes for Laravel 11 apps [#10070](https://github.com/statamic/cms/issues/10070) by @duncanmcclean
- Fix incorrect revision edit URLs [#10057](https://github.com/statamic/cms/issues/10057) by @duncanmcclean
- Allow 'Statamic/Pro/Free' to be written using locale-specific characters [#10045](https://github.com/statamic/cms/issues/10045) by @peimn
- Translate moment relative date statements [#10030](https://github.com/statamic/cms/issues/10030) by @peimn
- Update `ajthinking/archetype` dependency so PHPUnit 11 can be used. [#10048](https://github.com/statamic/cms/issues/10048) by @duncanmcclean
- German translations [#10058](https://github.com/statamic/cms/issues/10058) by @helloDanuk



## 5.0.2 (2024-05-10)

### What's fixed
- Warnings are output when `env()` calls are detected in site config update script [#10044](https://github.com/statamic/cms/issues/10044) by @jesseleite
- Fix translations within title tag breadcrumbs [#10039](https://github.com/statamic/cms/issues/10039) by @peimn
- Fix page tree branches not being openable in new tabs [#10041](https://github.com/statamic/cms/issues/10041) by @duncanmcclean
- Fix some RTL issues [#10029](https://github.com/statamic/cms/issues/10029) by @peimn
- Fix some translations in collection & taxonomy controllers [#10038](https://github.com/statamic/cms/issues/10038) by @peimn
- Fix translation command compatibility [#10037](https://github.com/statamic/cms/issues/10037) by @ryanmitchell
- Fix missing overflow styling on form submissions table [#10036](https://github.com/statamic/cms/issues/10036) by @duncanmcclean
- Fix `is_external_url` modifier with link fields [#10027](https://github.com/statamic/cms/issues/10027) by @duncanmcclean
- Dutch translations [#10042](https://github.com/statamic/cms/issues/10042) by @robdekort
- Persian translations [#10035](https://github.com/statamic/cms/issues/10035) by @peimn
- French translations [#10032](https://github.com/statamic/cms/issues/10032) by @PatrickJunod



## 5.0.1 (2024-05-09)

### What's fixed
- Fix Eloquent User `notifications` conflict. [#10024](https://github.com/statamic/cms/issues/10024) by @duncanmcclean
- Fix EloquentQueryBuilder orderby bug. [#10023](https://github.com/statamic/cms/issues/10023) by @duncanmcclean



## 5.0.0 (2024-05-09)

### What's new
- Official 5.0 release! 🎉

### What's fixed
- Allow opting out of svg tag sanitization [#10020](https://github.com/statamic/cms/issues/10020) by @jasonvarga
- Use `Statamic.$slug` helper instead of `$slugify` [#10018](https://github.com/statamic/cms/issues/10018) by @duncanmcclean



## 5.0.0-beta.4 (2024-05-07)

### What's changed
- Prevent excessive nocache cache growth. [#9999](https://github.com/statamic/cms/issues/9999) by @JohnathonKoster
- Ensures values are resolved when checking Antlers parsing settings. [#10003](https://github.com/statamic/cms/issues/10003) by @JohnathonKoster



## 5.0.0-beta.3 (2024-05-04)

### What's changed
- Fix 404s due to entry uri caching changes. [#9998](https://github.com/statamic/cms/issues/9998) by @jasonvarga
- Fix please command `--help` listing. [#9977](https://github.com/statamic/cms/issues/9977) by @jesseleite



## 5.0.0-beta.2 (2024-05-03)

### What's changed
- Prevent falsey values from returning blueprint defaults. [#9990](https://github.com/statamic/cms/issues/9990) by @JohnathonKoster
- Extract whereSite query method to trait. [#9991](https://github.com/statamic/cms/issues/9991) by @jasonvarga
- Multi site config help text. [#9986](https://github.com/statamic/cms/issues/9986) by @jackmcdade
- Improve the fake sql config code comment. [#9985](https://github.com/statamic/cms/issues/9985) by @jackmcdade



## 5.0.0-beta.1 (2024-05-02)

### What's changed
- Augmentation performance improvements. [#9636](https://github.com/statamic/cms/issues/9636) by @JohnathonKoster
- Offline License Validation. [#9975](https://github.com/statamic/cms/issues/9975) by @duncanmcclean
- Entry URI caching. [#9844](https://github.com/statamic/cms/issues/9844) by @jasonvarga



## 5.0.0-alpha.6 (2024-04-26)

### What's changed
- Add tokens to eloquent cli install. [#9962](https://github.com/statamic/cms/issues/9962) by @ryanmitchell
- Token class changes [#9964](https://github.com/statamic/cms/issues/9964) by @jasonvarga
- Revert caching entry to property on Page instances [#9958](https://github.com/statamic/cms/issues/9958) by @jasonvarga
- Changes to `User` role methods [#9921](https://github.com/statamic/cms/issues/9921) by @duncanmcclean



## 5.0.0-alpha.5 (2024-04-22)

### What's changed
- Blink augmentation of terms and entries fieldtypes. [#9938](https://github.com/statamic/cms/issues/9938) by @jasonvarga
- Fix slowdown caused by status PR. [#9928](https://github.com/statamic/cms/issues/9928) by @jasonvarga
- Update GraphiQL. [#9934](https://github.com/statamic/cms/issues/9934) by @duncanmcclean
- Drop legacy `rebing/graphql-laravel` code. [#9935](https://github.com/statamic/cms/issues/9935) by @duncanmcclean
- Fix statamic-prefixed commands not working when running `Artisan::call()` within please. [#9926](https://github.com/statamic/cms/issues/9926) by @jasonvarga
- Make `tearDown` method protected on `AddonTestCase`. [#9924](https://github.com/statamic/cms/issues/9924) by @duncanmcclean
- Fix please version. [#9925](https://github.com/statamic/cms/issues/9925) by @jasonvarga



## 5.0.0-alpha.4 (2024-04-17)

### What's changed
- JSON Serialization. [#9672](https://github.com/statamic/cms/issues/9672) by @jasonvarga
- Default field values defined in blueprints will be used for output rather than only on publish
  forms. [#9010](https://github.com/statamic/cms/issues/9010) by @duncanmcclean
- Always append original filenames to Glide URLs. [#9616](https://github.com/statamic/cms/issues/9616) by @duncanmcclean
- Ability to set custom Glide hashes. [#9918](https://github.com/statamic/cms/issues/9918) by @jasonvarga
- Remove manual Glide filenames. [#9913](https://github.com/statamic/cms/issues/9913) by @jasonvarga
- Reduce the number of times the `fieldsCache` is reset. [#9585](https://github.com/statamic/cms/issues/9585) by
  @JohnathonKoster
  @duncanmcclean
- Add `install:collaboration` command [#9760](https://github.com/statamic/cms/issues/9760) by @duncanmcclean
- Add `install:eloquent-driver` command [#9669](https://github.com/statamic/cms/issues/9669) by @duncanmcclean
- Improve handling of recursive fieldsets. [#9539](https://github.com/statamic/cms/issues/9539) by @JohnathonKoster
- Improvements to `please` commands. [#9720](https://github.com/statamic/cms/issues/9720) by @duncanmcclean
- Fix issues with Please commands on Laravel 11. [#9877](https://github.com/statamic/cms/issues/9877) by @duncanmcclean
- Fix event listeners not being triggered with Laravel 11. [#9876](https://github.com/statamic/cms/issues/9876) by
  @duncanmcclean
- Addon Testing Changes. [#9871](https://github.com/statamic/cms/issues/9871) by @duncanmcclean
- Make the SVG tag fail gracefully when `src` value is empty. [#9905](https://github.com/statamic/cms/issues/9905) by
- Fix duplicated field config header. [#9896](https://github.com/statamic/cms/issues/9896) by @peimn



## 5.0.0-alpha.3 (2024-04-11)

### What's changed
- Add new 'Settings' CP Nav section. [#9857](https://github.com/statamic/cms/issues/9857) by @jesseleite
- Avoid querying status. [#9317](https://github.com/statamic/cms/issues/9317) by @jasonvarga
- Ensure expectation count isn't negative if Version is not called. [#9863](https://github.com/statamic/cms/issues/9863) by @ryanmitchell



## 5.0.0-alpha.2 (2024-04-10)

### What's changed
- Change tag parameter parse-prevention character from @ to backslash. [#9856](https://github.com/statamic/cms/issues/9856) by @jasonvarga
- Revert requirement of prefixing attributes with `:attr`. [#9854](https://github.com/statamic/cms/issues/9854) by @jasonvarga



## 5.0.0-alpha.1 (2024-04-09)

### What's new
- [Breaking] Laravel 11 support. Drop Laravel 9 and PHP 8.0. [#9434](https://github.com/statamic/cms/issues/9434) by @jasonvarga
- [Breaking] Sites can now be managed in the Control Panel. [#9632](https://github.com/statamic/cms/issues/9632) by @jesseleite
- [Breaking] Dropped support for the old Antlers regex-based parser. [#9442](https://github.com/statamic/cms/issues/9442) by @duncanmcclean
- [Breaking] Laravel Reverb support. [#9758](https://github.com/statamic/cms/issues/9758) by @duncanmcclean
- Add filtering to form submissions listing. [#8906](https://github.com/statamic/cms/issues/8906) by @ryanmitchell
- Add Form submissions query builder and Facade. [#6455](https://github.com/statamic/cms/issues/6455) by @ryanmitchell
- [Breaking] Add `findOrFail` to repositories. [#9619](https://github.com/statamic/cms/issues/9619) by @ryanmitchell
- [Breaking] Add `findOrFail` method to `EntryRepository` interface. [#9596](https://github.com/statamic/cms/issues/9596) by @duncanmcclean
- Add `pluck` to query builder. [#9686](https://github.com/statamic/cms/issues/9686) by @JohnathonKoster
- Ability to use `files` fieldtype in forms for attaching temporary files. [#9084](https://github.com/statamic/cms/issues/9084) by @duncanmcclean
- Slugs can be generated on the server from JS. [#9440](https://github.com/statamic/cms/issues/9440) by @duncanmcclean
- Add `TextDirection` helper class. [#9730](https://github.com/statamic/cms/issues/9730) by @jesseleite
- Add `install:ssg` command. [#9622](https://github.com/statamic/cms/issues/9622) by @duncanmcclean
- Add simplified `TestCase` for addons. [#9573](https://github.com/statamic/cms/issues/9573) by @duncanmcclean
- The `make:addon` command will now scaffold a test suite using the new TestCase. [#9593](https://github.com/statamic/cms/issues/9593) by @duncanmcclean
- Add syntax for preventing parsing inside Antlers tag parameters. [#8887](https://github.com/statamic/cms/issues/8887) by @JohnathonKoster
- Ability to log fake SQL queries. [#9695](https://github.com/statamic/cms/issues/9695) by @JohnathonKoster
- Make Bard/Replicator/Grid sets sit at the bottom of the field configs. [#9516](https://github.com/statamic/cms/issues/9516) by @duncanmcclean

### What's fixed
- A myriad of performance improvements.
  [#9643](https://github.com/statamic/cms/issues/9643)
  [#9693](https://github.com/statamic/cms/issues/9693)
  [#9637](https://github.com/statamic/cms/issues/9637)
  [#9584](https://github.com/statamic/cms/issues/9584)
  [#9675](https://github.com/statamic/cms/issues/9675)
  [#9642](https://github.com/statamic/cms/issues/9642)
  [#9687](https://github.com/statamic/cms/issues/9687)
  [#9639](https://github.com/statamic/cms/issues/9639)
  [#9692](https://github.com/statamic/cms/issues/9692)
  [#9646](https://github.com/statamic/cms/issues/9646)
  [#9638](https://github.com/statamic/cms/issues/9638)
  [#9650](https://github.com/statamic/cms/issues/9650)
  [#9640](https://github.com/statamic/cms/issues/9640)
  [#9653](https://github.com/statamic/cms/issues/9653)
  [#9649](https://github.com/statamic/cms/issues/9649)
  [#9641](https://github.com/statamic/cms/issues/9641)
  [#9581](https://github.com/statamic/cms/issues/9581)
  [#9645](https://github.com/statamic/cms/issues/9645)
  [#9648](https://github.com/statamic/cms/issues/9648)
  [#9644](https://github.com/statamic/cms/issues/9644)
  [#9647](https://github.com/statamic/cms/issues/9647)
  [#9589](https://github.com/statamic/cms/issues/9589)
  [#9659](https://github.com/statamic/cms/issues/9659)
  [#9657](https://github.com/statamic/cms/issues/9657)
  [#9658](https://github.com/statamic/cms/issues/9658)
  [#9656](https://github.com/statamic/cms/issues/9656)
  [#9654](https://github.com/statamic/cms/issues/9654)
  [#9655](https://github.com/statamic/cms/issues/9655)
  [#9676](https://github.com/statamic/cms/issues/9676)
  by @JohnathonKoster
- Fix Ignition exceptions. [#9745](https://github.com/statamic/cms/issues/9745) by @jasonvarga
- Update `please` for Laravel 11. [#9729](https://github.com/statamic/cms/issues/9729) by @jasonvarga
- [Breaking] Bard values are now real objects instead of JSON strings. [#8958](https://github.com/statamic/cms/issues/8958) by @jacksleight
- [Breaking] Stop explicitly defining the pagination view, use the app's default. [#9843](https://github.com/statamic/cms/issues/9843) by @duncanmcclean
- [Breaking] Drop `laravel/helpers` dependency. [#9811](https://github.com/statamic/cms/issues/9811) by @duncanmcclean
- [Breaking] Sanitize entities using `htmlspecialchars`. [#9800](https://github.com/statamic/cms/issues/9800) by @duncanmcclean
- [Breaking] Retain headers with half-measure static caching. [#9020](https://github.com/statamic/cms/issues/9020) by @duncanmcclean
- [Breaking] Update our custom string based rules to class based rules. [#9785](https://github.com/statamic/cms/issues/9785) by @jesseleite
- Update deprecated contract usages in custom validation rules. [#9780](https://github.com/statamic/cms/issues/9780) by @jesseleite
- Adjust broadcasting enabled check for Laravel 11. [#9752](https://github.com/statamic/cms/issues/9752) by @duncanmcclean
- Remove redundant config options from blueprint YAML. [#9685](https://github.com/statamic/cms/issues/9685) by @duncanmcclean
- Improve handle and slug validation. [#9778](https://github.com/statamic/cms/issues/9778) by @jesseleite
- [Breaking] Remove unnecessary controller `destroy` methods. [#9689](https://github.com/statamic/cms/issues/9689) by @duncanmcclean
- [Breaking] Clone the date in `modifyDate` modifier. [#9688](https://github.com/statamic/cms/issues/9688) by @duncanmcclean
- [Breaking] Ensure structured collections are ordered by `order` by default. [#9704](https://github.com/statamic/cms/issues/9704) by @duncanmcclean
- [Breaking] Remove deprecated `revisions` method from Collection. [#9441](https://github.com/statamic/cms/issues/9441) by @duncanmcclean
- [Breaking] OAuth syntax tweaks. [#9623](https://github.com/statamic/cms/issues/9623) by @jasonvarga
- [Breaking] Refactor Form and SVG tags to use `:attr` prefix instead of `$knownTagParams`. [#9576](https://github.com/statamic/cms/issues/9576) by @JohnathonKoster
- [Breaking] Sanitize `svg` tag output by default. [#9575](https://github.com/statamic/cms/issues/9575) by @JohnathonKoster
- [Breaking] Implement `Localization` interface on `LocalizedTerm`. [#9496](https://github.com/statamic/cms/issues/9496) by @duncanmcclean
- Internal test suite uses PHPUnit 10. [#9715](https://github.com/statamic/cms/issues/9715) by @jasonvarga
