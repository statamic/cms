# Release Notes

## 5.58.0 (2025-06-20)

### What's new
- Detect recursion when augmenting Entries [#11854](https://github.com/statamic/cms/issues/11854) by @JohnathonKoster
- Add `hasField` method to `Fieldset` [#11882](https://github.com/statamic/cms/issues/11882) by @duncanmcclean
- Estonian translations [#11886](https://github.com/statamic/cms/issues/11886) by @karlromets

### What's fixed
- Render markdown after antlers when smartypants is enabled [#11814](https://github.com/statamic/cms/issues/11814) by @ryanmitchell
- Fix read-only state of roles and groups fields [#11867](https://github.com/statamic/cms/issues/11867) by @aerni
- Fix files not being removed after cache has been cleared [#11873](https://github.com/statamic/cms/issues/11873) by @indykoning
- Ensure nav blueprint graphql types are registered [#11881](https://github.com/statamic/cms/issues/11881) by @ryanmitchell
- Fix issues with Blade nav tag compiler [#11872](https://github.com/statamic/cms/issues/11872) by @JohnathonKoster
- Ensure propagating entries respects saveQuietly [#11875](https://github.com/statamic/cms/issues/11875) by @ryanmitchell
- Fix authorization error when creating globals [#11883](https://github.com/statamic/cms/issues/11883) by @duncanmcclean
- Fixes typo [#11876](https://github.com/statamic/cms/issues/11876) by @adampatterson
- Updated `AddonServiceProvider::shouldBootRootItems()` to support trailing slashes [#11861](https://github.com/statamic/cms/issues/11861) by @simonworkhouse
- Prevent null in strtolower() [#11869](https://github.com/statamic/cms/issues/11869) by @martinoak
- Ensure Glide treats asset urls starting with the app url as internal assets [#11839](https://github.com/statamic/cms/issues/11839) by @marcorieser
- Remove single quote in Asset upload  [#11858](https://github.com/statamic/cms/issues/11858) by @adampatterson



## 5.57.0 (2025-06-04)

### What's new
- Added the option to add renderers to markdown parsers [#11827](https://github.com/statamic/cms/issues/11827) by @CapitaineToinon
- Add renderer methods to `Markdown` facade docblock [#11845](https://github.com/statamic/cms/issues/11845) by @duncanmcclean
- Add `not_in` parameter to Assets tag [#11820](https://github.com/statamic/cms/issues/11820) by @nopticon
- Added `X-Statamic-Uncacheable` header to prevent responses being statically cached [#11817](https://github.com/statamic/cms/issues/11817) by @macaws

### What's fixed
- Throw 404 response when OAuth provider doesn't exist [#11844](https://github.com/statamic/cms/issues/11844) by @duncanmcclean
- Fix edition check in outpost [#11843](https://github.com/statamic/cms/issues/11843) by @duncanmcclean
- Updated Statamic references in language files [#11835](https://github.com/statamic/cms/issues/11835) by @tommulroy
- Fix create/edit CP nav descendants not properly triggering active status [#11832](https://github.com/statamic/cms/issues/11832) by @jesseleite
- Fix editability of nav items without content reference [#11822](https://github.com/statamic/cms/issues/11822) by @duncanmcclean
- Entries fieldtype: Only show "Allow Creating" option when using stack selector [#11816](https://github.com/statamic/cms/issues/11816) by @duncanmcclean
- French translations [#11826](https://github.com/statamic/cms/issues/11826) by @ebeauchamps
- Added null check for fieldActions within replicator [#11828](https://github.com/statamic/cms/issues/11828) by @martyf
- Perform null check on data in video fieldtype [#11821](https://github.com/statamic/cms/issues/11821) by @martyf
- Added checks for `Closure` instances instead of `is_callable` inside `Route::statamic(...)` [#11809](https://github.com/statamic/cms/issues/11809) by @JohnathonKoster
- Further increase trackDirtyState timeout [#11811](https://github.com/statamic/cms/issues/11811) by @simonerd



## 5.56.0 (2025-05-20)

### What's new
- Add `moveQuietly` method to `Asset` class [#11804](https://github.com/statamic/cms/issues/11804) by @duncanmcclean
- Add --header option to static warm command [#11763](https://github.com/statamic/cms/issues/11763) by @ChristianPraiss

### What's fixed
- Fix values being wrapped in arrays causing multiple selected options  [#11630](https://github.com/statamic/cms/issues/11630) by @simonworkhouse
- Hide read only and computed fields in user creation wizard [#11635](https://github.com/statamic/cms/issues/11635) by @duncanmcclean
- Fix storing submissions of forms with 'files' fieldtypes even when disabled [#11794](https://github.com/statamic/cms/issues/11794) by @andjsch
- Corrects Antlers error logging with PHP nodes [#11800](https://github.com/statamic/cms/issues/11800) by @JohnathonKoster
- Fix facade PhpDocs for better understanding by Laravel Idea [#11798](https://github.com/statamic/cms/issues/11798) by @adelf
- Correct issue with nested noparse and partials [#11801](https://github.com/statamic/cms/issues/11801) by @JohnathonKoster
- Prepare value & operator before passing to Eloquent Query Builder [#11805](https://github.com/statamic/cms/issues/11805) by @duncanmcclean



## 5.55.0 (2025-05-14)

### What's new
- Middleware to redirect absolute domains ending in dot [#11782](https://github.com/statamic/cms/issues/11782) by @indykoning
- Add `increment`/`decrement` methods to `ContainsData` [#11786](https://github.com/statamic/cms/issues/11786) by @duncanmcclean
- Update GraphiQL [#11780](https://github.com/statamic/cms/issues/11780) by @duncanmcclean
- Allow selecting entries from different sites within the link fieldtype [#10546](https://github.com/statamic/cms/issues/10546) by @justkidding96
- Ability to select entries from all sites from Bard link [#11768](https://github.com/statamic/cms/issues/11768) by @edalzell

### What's fixed
- Ensure asset validation rules are returned as strings to GraphQL [#11781](https://github.com/statamic/cms/issues/11781) by @ryanmitchell
- Asset validation rules as string in GraphQL, part 2 [#11790](https://github.com/statamic/cms/issues/11790) by @jasonvarga
- Fix Term filter on entry listing not working when limiting to 1 term [#11735](https://github.com/statamic/cms/issues/11735) by @liucf
- Fix filtering group fieldtype null values [#11788](https://github.com/statamic/cms/issues/11788) by @jacksleight
- Clone internal data collections [#11777](https://github.com/statamic/cms/issues/11777) by @jacksleight
- Fix creating terms in non-default sites [#11746](https://github.com/statamic/cms/issues/11746) by @duncanmcclean
- Ensure `null` values are filtered out in dictionary field config [#11773](https://github.com/statamic/cms/issues/11773) by @duncanmcclean
- Use deep copy of set's data in bard field [#11766](https://github.com/statamic/cms/issues/11766) by @faltjo
- Dutch translations [#11783](https://github.com/statamic/cms/issues/11783) by @rinusvandam
- PHPUnit: Use `#[Test]` attribute instead of `/** @test */` [#11767](https://github.com/statamic/cms/issues/11767) by @duncanmcclean



## 5.54.0 (2025-05-02)

### What's new
- Add `firstOrfail`, `firstOr`, `sole` and `exists` methods to base query builder [#9976](https://github.com/statamic/cms/issues/9976) by @duncanmcclean
- Allow custom nocache db connection [#11716](https://github.com/statamic/cms/issues/11716) by @macaws
- Make Live Preview force reload JS modules optional [#11715](https://github.com/statamic/cms/issues/11715) by @eminos

### What's fixed
- Fix ensured author field when sidebar has empty section [#11747](https://github.com/statamic/cms/issues/11747) by @duncanmcclean
- Improve error handling in entries fieldtype [#11754](https://github.com/statamic/cms/issues/11754) by @duncanmcclean
- Hide "Edit" button on relationship fieldtypes when user is missing permissions [#11748](https://github.com/statamic/cms/issues/11748) by @duncanmcclean
- Fix wrong taxonomies count on multiple sites [#11741](https://github.com/statamic/cms/issues/11741) by @liucf
- GraphQL should return float fieldtype values as floats [#11742](https://github.com/statamic/cms/issues/11742) by @ryanmitchell
- Fix pop-up position of for Bard link inconsistent [#11739](https://github.com/statamic/cms/issues/11739) by @liucf
- Only apply the published filter when not in preview mode [#11652](https://github.com/statamic/cms/issues/11652) by @TheBnl
- Cleanup roles after running `SitesTest@gets_authorized_sites` [#11738](https://github.com/statamic/cms/issues/11738) by @duncanmcclean
- Update nocache map on response [#11650](https://github.com/statamic/cms/issues/11650) by @indykoning
- Fix Add truncate class to flex container in LinkFieldtype [#11689](https://github.com/statamic/cms/issues/11689) by @liucf
- Fix appended form config fields when user locale differs from app locale [#11704](https://github.com/statamic/cms/issues/11704) by @duncanmcclean
- Fix dirty state on preferences edit form [#11655](https://github.com/statamic/cms/issues/11655) by @duncanmcclean
- Fix static caching invalidation for multi-sites [#10669](https://github.com/statamic/cms/issues/10669) by @duncanmcclean
- Safer check on parent tag [#11717](https://github.com/statamic/cms/issues/11717) by @macaws
- Dutch translations  [#11730](https://github.com/statamic/cms/issues/11730) by @jeroenpeters1986
- Bump @babel/runtime from 7.21.0 to 7.27.0 [#11726](https://github.com/statamic/cms/issues/11726) by @dependabot
- Update caniuse-lite [#11725](https://github.com/statamic/cms/issues/11725) by @jasonvarga



## 5.53.1 (2025-04-17)

### What's fixed
- Fix validation of date field nested in a replicator  [#11692](https://github.com/statamic/cms/issues/11692) by @liucf
- Fix collection index search when using a non-dedicated search index [#11711](https://github.com/statamic/cms/issues/11711) by @simonerd
- Handle translation issues in collection widget [#11693](https://github.com/statamic/cms/issues/11693) by @daun
- Remove `templates`/`themes` methods from `CpController` [#11706](https://github.com/statamic/cms/issues/11706) by @duncanmcclean
- Ensure asset references are updated correctly [#11705](https://github.com/statamic/cms/issues/11705) by @duncanmcclean



## 5.53.0 (2025-04-10)

### What's new
- Allow dynamic counter names in `increment` tag [#11671](https://github.com/statamic/cms/issues/11671) by @daun
- Expose field conditions from GraphQL API [#11607](https://github.com/statamic/cms/issues/11607) by @duncanmcclean
- Add Edit Blueprint links to create publish forms [#11625](https://github.com/statamic/cms/issues/11625) by @jacksleight

### What's fixed
- Fix icon selector in nav builder [#11656](https://github.com/statamic/cms/issues/11656) by @duncanmcclean
- Fix docblock in AssetContainer facade [#11658](https://github.com/statamic/cms/issues/11658) by @duncanmcclean
- Revert "Escape start_page Preference to avoid invalid Redirect" [#11651](https://github.com/statamic/cms/issues/11651) by @duncanmcclean
- Restore error message on asset upload server errors [#11642](https://github.com/statamic/cms/issues/11642) by @daun
- Fix dates in localizations when duplicating entries [#11361](https://github.com/statamic/cms/issues/11361) by @duncanmcclean
- Use deep copy of objects in replicator set [#11621](https://github.com/statamic/cms/issues/11621) by @faltjo
- French translations [#11622](https://github.com/statamic/cms/issues/11622) by @ebeauchamps
- Dutch translations [#11686](https://github.com/statamic/cms/issues/11686) by @rogerthat-be



## 5.52.0 (2025-03-25)

### What's new
- Support query scopes in GraphQL [#11533](https://github.com/statamic/cms/issues/11533) by @ryanmitchell
- Support query scopes in REST API [#10893](https://github.com/statamic/cms/issues/10893) by @ryanmitchell
- Added option to exclude asset containers from generating presets [#11613](https://github.com/statamic/cms/issues/11613) by @kevinmeijer97
- Handle collection instances in `first` modifier [#11608](https://github.com/statamic/cms/issues/11608) by @marcorieser
- Autoload scopes from `Query/Scopes` and `Query/Scopes/Filters` [#11601](https://github.com/statamic/cms/issues/11601) by @duncanmcclean
- Allow revisions path to be configurable with an .env [#11594](https://github.com/statamic/cms/issues/11594) by @ryanmitchell
- Ability to exclude parents from nav tag [#11597](https://github.com/statamic/cms/issues/11597) by @jasonvarga
- Allow a custom asset meta cache store to be specified [#11512](https://github.com/statamic/cms/issues/11512) by @ryanmitchell

### What's fixed
- Fix icon fieldtype in nav builder [#11618](https://github.com/statamic/cms/issues/11618) by @jasonvarga
- Escape start_page Preference to avoid invalid Redirect [#11616](https://github.com/statamic/cms/issues/11616) by @naabster
- Fix issue with localization files named like fieldset handles [#11603](https://github.com/statamic/cms/issues/11603) by @ChristianPraiss
- Ensure toasts fired in an AssetUploaded event are delivered to front end [#11592](https://github.com/statamic/cms/issues/11592) by @ryanmitchell
- Prevent null data from being saved to eloquent users [#11591](https://github.com/statamic/cms/issues/11591) by @ryanmitchell
- Change default value of update command selection [#11581](https://github.com/statamic/cms/issues/11581) by @Jade-GG
- Adjust relationship field typeahead no-options message [#11590](https://github.com/statamic/cms/issues/11590) by @jasonvarga
- Bump axios from 1.7.4 to 1.8.2 [#11604](https://github.com/statamic/cms/issues/11604) by @dependabot
- Bump tj-actions/changed-files [#11602](https://github.com/statamic/cms/issues/11602) by @dependabot
- Spanish translations [#11617](https://github.com/statamic/cms/issues/11617) by @nopticon



## 5.51.0 (2025-03-17)

### What's new
- Allow passing computed fields via an associative array [#11528](https://github.com/statamic/cms/issues/11528) by @godismyjudge95
- Enable unselecting fieldtypes for forms [#11559](https://github.com/statamic/cms/issues/11559) by @godismyjudge95

### What's fixed
- Fix implied route views [#11570](https://github.com/statamic/cms/issues/11570) by @duncanmcclean
- When NavPageInterface has no blueprint fields return something [#11537](https://github.com/statamic/cms/issues/11537) by @ryanmitchell
- Password reset action should use custom password reset notification  [#11571](https://github.com/statamic/cms/issues/11571) by @duncanmcclean
- Fix escaped braces in concurrent requests not getting replaced [#11583](https://github.com/statamic/cms/issues/11583) by @o1y
- Fix control panel crashes when titles share name with existing translation file [#11578](https://github.com/statamic/cms/issues/11578) by @daun
- Fix carbon deprecation warning [#11561](https://github.com/statamic/cms/issues/11561) by @jasonvarga
- Fix icon fieldtype [#11560](https://github.com/statamic/cms/issues/11560) by @jasonvarga



## 5.50.0 (2025-03-10)

### What's new
- Support `as` on nav tag [#11522](https://github.com/statamic/cms/issues/11522) by @ryanmitchell

### What's fixed
- Icon fieldtype performance [#11523](https://github.com/statamic/cms/issues/11523) by @jasonvarga
- Fix password protection on 404 pages [#11544](https://github.com/statamic/cms/issues/11544) by @duncanmcclean
- Return validation error when AllowedFile is not an UploadedFile [#11535](https://github.com/statamic/cms/issues/11535) by @ryanmitchell
- Italian translations [#11538](https://github.com/statamic/cms/issues/11538) by @ivanandre
- French translations [#11519](https://github.com/statamic/cms/issues/11519) by @ebeauchamps
- Use ubuntu-latest in GitHub Actions workflow [#11526](https://github.com/statamic/cms/issues/11526) by @jasonvarga



## 5.49.1 (2025-02-27)

### What's fixed
- Query for entry origin within the same collection [#11514](https://github.com/statamic/cms/issues/11514) by @jasonvarga
- Improve validation message when handle starts with a number [#11511](https://github.com/statamic/cms/issues/11511) by @duncanmcclean
- Fix target `.git` repo handling when exporting starter kit with `--clear` [#11509](https://github.com/statamic/cms/issues/11509) by @jesseleite
- Make active toolbar buttons of Bard more visible in dark mode [#11405](https://github.com/statamic/cms/issues/11405) by @carstenjaksch
- Alternate Laravel 12 token repository fix [#11505](https://github.com/statamic/cms/issues/11505) by @jasonvarga



## 5.49.0 (2025-02-25)

### What's new
- Laravel 12 support [#11433](https://github.com/statamic/cms/issues/11433) by @duncanmcclean

### What's fixed
- Asset Container returns relative url for same site [#11372](https://github.com/statamic/cms/issues/11372) by @marcorieser



## 5.48.1 (2025-02-25)

### What's fixed
- Fix session expiry component [#11501](https://github.com/statamic/cms/issues/11501) by @jasonvarga
- Include port in CSP for Live Preview [#11498](https://github.com/statamic/cms/issues/11498) by @dmxmo
- Remove duplicate translation line from `translator` command [#11494](https://github.com/statamic/cms/issues/11494) by @duncanmcclean
- Fix carbon integer casting [#11496](https://github.com/statamic/cms/issues/11496) by @jasonvarga
- Only show spatie/fork prompt when pcntl extension is loaded [#11493](https://github.com/statamic/cms/issues/11493) by @duncanmcclean
- French translations [#11488](https://github.com/statamic/cms/issues/11488) by @ebeauchamps



## 5.48.0 (2025-02-21)

### What's new
- Carbon 3 support [#11348](https://github.com/statamic/cms/issues/11348) by @duncanmcclean
- Allow custom asset container contents cache store [#11481](https://github.com/statamic/cms/issues/11481) by @ryanmitchell
- Add support for `$view` parameter closure with `Route::statamic()` [#11452](https://github.com/statamic/cms/issues/11452) by @jesseleite
- Add Unlink All action to relationship fields [#11475](https://github.com/statamic/cms/issues/11475) by @jacksleight

### What's fixed
- Fix handle dimensions of rotated videos [#11479](https://github.com/statamic/cms/issues/11479) by @grischaerbe
- Entries Fieldtype: Hide tree view when using query scopes [#11484](https://github.com/statamic/cms/issues/11484) by @duncanmcclean
- Fix issue with localization files named like handles [#11482](https://github.com/statamic/cms/issues/11482) by @ChristianPraiss
- Fix cannot use paginate/limit error when one is null [#11478](https://github.com/statamic/cms/issues/11478) by @jacksleight
- Fix primitive type hint handling in Statamic routes [#11476](https://github.com/statamic/cms/issues/11476) by @jesseleite



## 5.47.0 (2025-02-18)

### What's new
- Support Sections in form GraphQL queries [#11466](https://github.com/statamic/cms/issues/11466) by @ryanmitchell
- Add empty/not empty field filters [#11389](https://github.com/statamic/cms/issues/11389) by @jacksleight
- Support arguments in user can/cant tags [#11407](https://github.com/statamic/cms/issues/11407) by @jacksleight
- Add hook into the `multisite` command [#11458](https://github.com/statamic/cms/issues/11458) by @duncanmcclean
- Set the proper CSP headers for multi-domain iframe [#11447](https://github.com/statamic/cms/issues/11447) by @edalzell
- Allow custom render callback for overridden exceptions [#11408](https://github.com/statamic/cms/issues/11408) by @FrittenKeeZ
- Support the Group fieldtype in DataReferenceUpdater [#11410](https://github.com/statamic/cms/issues/11410) by @duncanmcclean

### What's fixed
- Fix emptiness check on Value properties [#11402](https://github.com/statamic/cms/issues/11402) by @godismyjudge95
- Change revision whereKey to return a collection [#11463](https://github.com/statamic/cms/issues/11463) by @ryanmitchell
- If nav:from references an invalid entry, ensure nothing is returned [#11464](https://github.com/statamic/cms/issues/11464) by @ryanmitchell
- Improve performance of the data reference updaters [#11442](https://github.com/statamic/cms/issues/11442) by @duncanmcclean
- Fix search results missing search_snippets [#11450](https://github.com/statamic/cms/issues/11450) by @ivang76
- Ensure consistent order of `get_content` results [#11429](https://github.com/statamic/cms/issues/11429) by @daun
- Fixes empty asset alt <td> element in AssetsFieldtype [#11460](https://github.com/statamic/cms/issues/11460) by @PatrickJunod
- Fix issue with AssetsFieldtype upload controls hidden on @sm size [#11459](https://github.com/statamic/cms/issues/11459) by @PatrickJunod
- Fix test state issues around sites cache [#11455](https://github.com/statamic/cms/issues/11455) by @jesseleite
- Fix InstallEloquentDriver command [#11425](https://github.com/statamic/cms/issues/11425) by @aerni
- Handle extra translation files in breadcrumbs [#11422](https://github.com/statamic/cms/issues/11422) by @jasonvarga
- Select specific repositories for Eloquent Driver install command [#11381](https://github.com/statamic/cms/issues/11381) by @kevinmeijer97
- Adjust video fit in asset editor [#11414](https://github.com/statamic/cms/issues/11414) by @daun
- Dutch translations [#11448](https://github.com/statamic/cms/issues/11448) by @daronspence
- Use `ubuntu-latest` in GitHub Actions workflows [#11443](https://github.com/statamic/cms/issues/11443) by @duncanmcclean



## 5.46.1 (2025-02-04)

### What's fixed
- Fix search:results tag when offset and paginate are set [#11386](https://github.com/statamic/cms/issues/11386) by @nopticon
- Live Preview: Allow changing the position of "Responsive" device option [#11404](https://github.com/statamic/cms/issues/11404) by @duncanmcclean
- Fix additional url segments matching taxonomy terms [#11383](https://github.com/statamic/cms/issues/11383) by @jasonvarga
- Use constructor property promotion in events [#11380](https://github.com/statamic/cms/issues/11380) by @duncanmcclean
- Fix "Curaçao" item in countries dictionary [#11395](https://github.com/statamic/cms/issues/11395) by @duncanmcclean
- Remove duplicate strings from translation files [#11400](https://github.com/statamic/cms/issues/11400) by @j3ll3yfi5h
- German translations [#11399](https://github.com/statamic/cms/issues/11399) by @helloDanuk
- French translations [#11397](https://github.com/statamic/cms/issues/11397) by @ebeauchamps



## 5.46.0 (2025-01-22)

### What's new
- Add empty/not empty filters for replicator, bard and grid [#11354](https://github.com/statamic/cms/issues/11354) by @jacksleight
- Page children as value for field conditions [#11368](https://github.com/statamic/cms/issues/11368) by @heidkaemper
- Allow addons cache path to be set by an environment variable [#11365](https://github.com/statamic/cms/issues/11365) by @ryanmitchell

### What's fixed
- Fix error with disallowed words in Comb search driver [#11336](https://github.com/statamic/cms/issues/11336) by @duncanmcclean
- Fixed ordering search results by origin value [#11334](https://github.com/statamic/cms/issues/11334) by @duncanmcclean
- Fix case insensitive Comb search for UTF-8 characters [#11363](https://github.com/statamic/cms/issues/11363) by @heidkaemper
- Translate name in user group fieldtype [#11343](https://github.com/statamic/cms/issues/11343) by @duncanmcclean
- Fix UI bugs in Safari 18.2 [#11335](https://github.com/statamic/cms/issues/11335) by @marcorieser



## 5.45.2 (2025-01-21)

### What's fixed
- Revert "Allow form fields view to be rendered with single tag" [#11374](https://github.com/statamic/cms/issues/11374) by @duncanmcclean
- Remove `type` attribute in nocache replacer [#11373](https://github.com/statamic/cms/issues/11373) by @marcorieser
- Fix deprecation warning from regex operator [#11337](https://github.com/statamic/cms/issues/11337) by @duncanmcclean
- Fix bug report link in Contribution Guide [#11367](https://github.com/statamic/cms/issues/11367) by @duncanmcclean
- Fix bard undefined href error [#11351](https://github.com/statamic/cms/issues/11351) by @jacksleight
- Suppress “packing” git message [#11326](https://github.com/statamic/cms/issues/11326) by @edalzell



## 5.45.1 (2025-01-07)

### What's fixed
- Throw better exception when asset isn't found [#11321](https://github.com/statamic/cms/issues/11321) by @edalzell
- Add url friendly base64 en/decoding for Glide [#11299](https://github.com/statamic/cms/issues/11299) by @marcorieser
- Update make:fieldtype console message [#11309](https://github.com/statamic/cms/issues/11309) by @Technobabble17
- Make set button label clickable [#11313](https://github.com/statamic/cms/issues/11313) by @carstenjaksch
- French translations [#11297](https://github.com/statamic/cms/issues/11297) by @ebeauchamps
- Fix markdown test [#11315](https://github.com/statamic/cms/issues/11315) by @jasonvarga



## 5.45.0 (2024-12-20)

### What's new
- Allow form fields view to be rendered with single tag [#11293](https://github.com/statamic/cms/issues/11293) by @jasonvarga
- Improve form field accessibility [#10993](https://github.com/statamic/cms/issues/10993) by @daun

### What's fixed
- Prevent duplicate roles & groups [#11270](https://github.com/statamic/cms/issues/11270) by @duncanmcclean
- Improve error handling when using entry publish actions [#11289](https://github.com/statamic/cms/issues/11289) by @ryanmitchell



## 5.44.0 (2024-12-18)

### What's new
- Static warm command will recheck whether page is cached when using queue [#11273](https://github.com/statamic/cms/issues/11273) by @arthurperton
- Add `--max-requests` option to the static warm command [#11278](https://github.com/statamic/cms/issues/11278) by @arthurperton
- Add formStackSize option for inline publish form stacks [#11274](https://github.com/statamic/cms/issues/11274) by @duncanmcclean

### What's fixed
- Fix addon service provider autoloading [#11285](https://github.com/statamic/cms/issues/11285) by @jasonvarga
- Fix CP thumbnail placeholder [#11279](https://github.com/statamic/cms/issues/11279) by @duncanmcclean
- Fix replicator preview for Group fields [#11280](https://github.com/statamic/cms/issues/11280) by @duncanmcclean



## 5.43.2 (2024-12-18)

### What's fixed
- Fix static properties in addon providers [#11283](https://github.com/statamic/cms/issues/11283) by @jasonvarga



## 5.43.1 (2024-12-18)

### What's fixed
- Fix autoload error on Windows [#11282](https://github.com/statamic/cms/issues/11282) by @jasonvarga
- Improve starter kit installer error handling [#11281](https://github.com/statamic/cms/issues/11281) by @jesseleite



## 5.43.0 (2024-12-17)

### What's new
- Add filters from collection/taxonomy list to breadcrumb back link [#11243](https://github.com/statamic/cms/issues/11243) by @florianbrinkmann
- OAuth: option not to create or update user during authentication [#10853](https://github.com/statamic/cms/issues/10853) by @miloslavkostir
- Add some options to the static warm command to limit the number of requests [#11258](https://github.com/statamic/cms/issues/11258) by @arthurperton
- Table Fieldtype: Add `max_columns` and `max_rows` options [#11224](https://github.com/statamic/cms/issues/11224) by @duncanmcclean

### What's fixed
- Handle hidden fields on nav page edit form [#11272](https://github.com/statamic/cms/issues/11272) by @duncanmcclean
- Support Laravel Prompts 0.3+ [#11267](https://github.com/statamic/cms/issues/11267) by @duncanmcclean
- Update `embed_url` and `trackable_embed_url` modifiers to be valid with additional query strings [#11265](https://github.com/statamic/cms/issues/11265) by @martyf
- Fix term filter on entries listing [#11268](https://github.com/statamic/cms/issues/11268) by @duncanmcclean
- Prevent "Set Alt" button from running Replace Asset action prematurely [#11269](https://github.com/statamic/cms/issues/11269) by @duncanmcclean
- Fix autoloading when addon has multiple service providers [#11128](https://github.com/statamic/cms/issues/11128) by @duncanmcclean
- Fix ButtonGroup not showing active state if value are numbers [#10916](https://github.com/statamic/cms/issues/10916) by @morhi
- Support glide urls with URL params [#11003](https://github.com/statamic/cms/issues/11003) by @ryanmitchell
- Throw 404 on collection routes if taxonomy isn’t assigned to collection [#10438](https://github.com/statamic/cms/issues/10438) by @aerni
- Move bard source button into field actions [#11250](https://github.com/statamic/cms/issues/11250) by @jasonvarga
- Fix collection title format when using translations [#11248](https://github.com/statamic/cms/issues/11248) by @ajnsn
- Bump nanoid from 3.3.6 to 3.3.8 [#11251](https://github.com/statamic/cms/issues/11251) by @dependabot



## 5.42.1 (2024-12-11)

### What's fixed
- Fix asset upload concurrency on folder upload [#11225](https://github.com/statamic/cms/issues/11225) by @daun
- Fix subdirectory autodiscovery on Windows [#11246](https://github.com/statamic/cms/issues/11246) by @jasonvarga
- Fix type error in `HandleEntrySchedule` job [#11244](https://github.com/statamic/cms/issues/11244) by @duncanmcclean
- Fix `no_results` cascade [#11234](https://github.com/statamic/cms/issues/11234) by @JohnathonKoster



## 5.42.0 (2024-12-05)

### What's new
- Add new `updatable` and `package` starter kit conventions [#11119](https://github.com/statamic/cms/issues/11119) by @jesseleite
- Add new `starter-kit:init` command [#11215](https://github.com/statamic/cms/issues/11215) by @jesseleite
- Register App extensions also for Classes in Subfolders [#11046](https://github.com/statamic/cms/issues/11046) by @benatoff
- Support rendering model attributes in Antlers [#10869](https://github.com/statamic/cms/issues/10869) by @ryanmitchell
- Add an `--uncached` option to the static warm command [#11188](https://github.com/statamic/cms/issues/11188) by @arthurperton

### What's fixed
- Fixed an issue where stache indexing can cause an infinite loop for workers [#11185](https://github.com/statamic/cms/issues/11185) by @kingsven
- Add validation replacements to replicator and grid field types [#10255](https://github.com/statamic/cms/issues/10255) by @florianbrinkmann
- Fix localized error messages on forms when previous URL is incorrect [#11219](https://github.com/statamic/cms/issues/11219) by @jasonvarga
- Fix null coalescence operator evaluation [#11221](https://github.com/statamic/cms/issues/11221) by @godismyjudge95
- Fix wrong url on the link mark node in bard fieldtype [#11207](https://github.com/statamic/cms/issues/11207) by @christophstockinger
- Fix REST API errors when CP route is empty [#11213](https://github.com/statamic/cms/issues/11213) by @duncanmcclean
- Throw an error when running `static:clear` when static caching is disabled [#11193](https://github.com/statamic/cms/issues/11193) by @duncanmcclean
- Fix entry links when Bard value is HTML [#11192](https://github.com/statamic/cms/issues/11192) by @duncanmcclean
- Ensure updating references gets all global variables [#11186](https://github.com/statamic/cms/issues/11186) by @ryanmitchell
- Ensure cache factory is passed to the `StartSession` middleware. [#11191](https://github.com/statamic/cms/issues/11191) by @duncanmcclean
- Fix search query orderBy [#11210](https://github.com/statamic/cms/issues/11210) by @jasonvarga
- Allow Values object and Group fieldtype to be iterated [#11182](https://github.com/statamic/cms/issues/11182) by @jasonvarga
- French translations [#11196](https://github.com/statamic/cms/issues/11196) by @ebeauchamps



## 5.41.0 (2024-11-27)

### What's new
- PHP 8.4 Support [#11114](https://github.com/statamic/cms/issues/11114) by @duncanmcclean



## 5.40.0 (2024-11-26)

### What's new
- Add `default` config for select starter kit modules [#11045](https://github.com/statamic/cms/issues/11045) by @jesseleite
- Include store in field action payload [#11161](https://github.com/statamic/cms/issues/11161) by @jacksleight

### What's fixed
- Slight adjustments to the search index table [#11171](https://github.com/statamic/cms/issues/11171) by @daun
- Fix translations of Statamic [#11175](https://github.com/statamic/cms/issues/11175) by @jasonvarga
- Dutch translations [#11174](https://github.com/statamic/cms/issues/11174) by @Pluuk
- Filter out empty values in keyed array field [#11138](https://github.com/statamic/cms/issues/11138) by @duncanmcclean
- Fix field actions dark mode [#11167](https://github.com/statamic/cms/issues/11167) by @jacksleight
- Fix field action nested alignment [#11165](https://github.com/statamic/cms/issues/11165) by @jacksleight
- Fix inline bard expand/collapse actions [#11166](https://github.com/statamic/cms/issues/11166) by @jacksleight
- Fix field actions when display is hidden [#11158](https://github.com/statamic/cms/issues/11158) by @jacksleight



## 5.39.0 (2024-11-22)

### What's new
- Field actions [#10352](https://github.com/statamic/cms/issues/10352) by @jacksleight
- Field action modals [#11129](https://github.com/statamic/cms/issues/11129) by @jacksleight
- Add ArrayableString to Blade value helper [#11041](https://github.com/statamic/cms/issues/11041) by @ajnsn
- Add `always_augment_to_query` option [#11086](https://github.com/statamic/cms/issues/11086) by @jacksleight

### What's fixed
- Fix multisite command not moving directories [#11105](https://github.com/statamic/cms/issues/11105) by @duncanmcclean
- Fix asset folder sort across pages [#11130](https://github.com/statamic/cms/issues/11130) by @daun
- Avoid creation of duplicate terms in typeahead input [#11060](https://github.com/statamic/cms/issues/11060) by @daun
- Fix error when using user ID as folder name for avatar asset field in non-admin context [#11141](https://github.com/statamic/cms/issues/11141) by @jonasemde
- Fix incorrect namespaces in tests [#11149](https://github.com/statamic/cms/issues/11149) by @duncanmcclean
- Prevent `str_replace` warning when using Debugbar [#11148](https://github.com/statamic/cms/issues/11148) by @duncanmcclean



## 5.38.1 (2024-11-19)

### What's fixed
- Fix issue when preprocessing dictionary config [#11133](https://github.com/statamic/cms/issues/11133) by @duncanmcclean
- Prevent unnecessary requests to the Outpost when PHP version is different [#11137](https://github.com/statamic/cms/issues/11137) by @duncanmcclean
- Fix bard text trimming when CP is on root URL [#11127](https://github.com/statamic/cms/issues/11127) by @jacksleight
- Hide "Localizable" button in asset blueprints [#11118](https://github.com/statamic/cms/issues/11118) by @duncanmcclean
- Add upload path traversal tests [#11139](https://github.com/statamic/cms/issues/11139) by @jasonvarga
- Prevent asset folder path traversal [#11136](https://github.com/statamic/cms/issues/11136) by @jasonvarga
- More path traversal fixes [#11140](https://github.com/statamic/cms/issues/11140) by @jasonvarga
- Italian translations [#11145](https://github.com/statamic/cms/issues/11145) by @gioppy



## 5.38.0 (2024-11-12)

### What's new
- Extra values for nav field conditions, including depth [#11106](https://github.com/statamic/cms/issues/11106) by @duncanmcclean
- Make button groups clearable [#11110](https://github.com/statamic/cms/issues/11110) by @caseydwyer
- Allow transformResults to be called separately from getBaseItems [#11115](https://github.com/statamic/cms/issues/11115) by @ryanmitchell
- Allow customizing term create label [#11103](https://github.com/statamic/cms/issues/11103) by @daun
- Accept collections in ampersand list modifier [#11102](https://github.com/statamic/cms/issues/11102) by @daun
- Add "Container" option to Asset Folders fieldtype [#11099](https://github.com/statamic/cms/issues/11099) by @duncanmcclean
- Add `fullyQualifiedHandle` method to `Blueprint` [#11096](https://github.com/statamic/cms/issues/11096) by @duncanmcclean
- Create edit/{id} route for control panel access [#11092](https://github.com/statamic/cms/issues/11092) by @aaronbushnell

### What's fixed
- Fix wrong blueprint parent after revision publish [#11116](https://github.com/statamic/cms/issues/11116) by @jacksleight
- Prevent duplicate nocache regions in session [#11109](https://github.com/statamic/cms/issues/11109) by @duncanmcclean
- Hide "Localizable" button on non-localizable blueprints [#11107](https://github.com/statamic/cms/issues/11107) by @duncanmcclean
- Fix error after deleting role when storing users in the database [#11069](https://github.com/statamic/cms/issues/11069) by @duncanmcclean
- Render HTML for dictionary fields in listings [#11088](https://github.com/statamic/cms/issues/11088) by @jasonvarga
- Use layout config in errors too [#11083](https://github.com/statamic/cms/issues/11083) by @ryanmitchell
- French translations [#11093](https://github.com/statamic/cms/issues/11093) by @ebeauchamps
- French translations [#11100](https://github.com/statamic/cms/issues/11100) by @ebeauchamps
- French translations [#11108](https://github.com/statamic/cms/issues/11108) by @jasonvarga
- German translations [#11098](https://github.com/statamic/cms/issues/11098) by @helloDanuk



## 5.37.0 (2024-11-06)

### What's new
- Improved fieldtype search using keywords [#11053](https://github.com/statamic/cms/issues/11053) by @jasonvarga
- Offer to enable Pro during make user command [#11071](https://github.com/statamic/cms/issues/11071) by @jasonvarga
- Add `--clear` option for `starter-kit:export` [#11079](https://github.com/statamic/cms/issues/11079) by @jesseleite
- Extra values for entry field conditions, including depth [#11080](https://github.com/statamic/cms/issues/11080) by @jasonvarga
- Add a config for specifying the default layout [#11025](https://github.com/statamic/cms/issues/11025) by @ryanmitchell

### What's fixed
- Integer fields should render with `type="number"` [#11065](https://github.com/statamic/cms/issues/11065) by @duncanmcclean
- Update addon `.gitignore` stub [#11068](https://github.com/statamic/cms/issues/11068) by @duncanmcclean
- Adjust legacy ignition classes [#11073](https://github.com/statamic/cms/issues/11073) by @jasonvarga
- Fix Ignition Runnable Error Solutions [#11072](https://github.com/statamic/cms/issues/11072) by @jasonvarga
- Fix typeahead relationship input corrupting data [#11059](https://github.com/statamic/cms/issues/11059) by @daun
- Files Fieldtype: Don't truncate existing filename [#11055](https://github.com/statamic/cms/issues/11055) by @duncanmcclean
- Fix addon events dispatched twice if registered manually [#11049](https://github.com/statamic/cms/issues/11049) by @morhi
- Fix query parameters in external script URLs being wrongly encoded [#11052](https://github.com/statamic/cms/issues/11052) by @duncanmcclean
- Fix inline Bard with leading line break [#11038](https://github.com/statamic/cms/issues/11038) by @jacksleight
- Update Translations and fill in blanks with Google Translate [#11050](https://github.com/statamic/cms/issues/11050) by @jasonvarga



## 5.36.0 (2024-10-31)

### What's new
- New Blade syntax for using Tags [#10967](https://github.com/statamic/cms/issues/10967) by @JohnathonKoster
- Add support for avif image format [#11016](https://github.com/statamic/cms/issues/11016) by @daun
- Allow setting tag pair content from fluent tags [#11018](https://github.com/statamic/cms/issues/11018) by @daun

### What's fixed
- Fix Filesystem AbstractAdapter put method return [#11032](https://github.com/statamic/cms/issues/11032) by @godismyjudge95
- Fix color fieldtype's collapsed state showing plain HTML [#11031](https://github.com/statamic/cms/issues/11031) by @jackmcdade
- Fix helpBlock error with custom js validation rule [#11023](https://github.com/statamic/cms/issues/11023) by @irfandumanx



## 5.35.0 (2024-10-28)

### What's new
- Drag and drop folders into the asset browser [#10583](https://github.com/statamic/cms/issues/10583) by @daun
- Include Algolia highlights and snippets [#11008](https://github.com/statamic/cms/issues/11008) by @jacksleight
- Introduce additional form tag hooks [#11010](https://github.com/statamic/cms/issues/11010) by @leewillis77
- Add `allowed_extensions` option to Files fieldtype [#10998](https://github.com/statamic/cms/issues/10998) by @duncanmcclean

### What's fixed
- Support unions in addon event listener discovery [#11015](https://github.com/statamic/cms/issues/11015) by @jasonvarga
- Support auto-registering of listeners using __invoke() not handle [#11009](https://github.com/statamic/cms/issues/11009) by @leewillis77
- French translations [#10995](https://github.com/statamic/cms/issues/10995) by @ebeauchamps



## 5.34.0 (2024-10-24)

### What's new
- Add `password` option to `make:user` command [#11005](https://github.com/statamic/cms/issues/11005) by @joshuablum

### What's fixed
- Fix issues with the Files fieldtype in Dark Mode [#10999](https://github.com/statamic/cms/issues/10999) by @duncanmcclean



## 5.33.1 (2024-10-22)

### What's fixed
- Avoid error when marketplace client returns null [#10996](https://github.com/statamic/cms/issues/10996) by @jasonvarga



## 5.33.0 (2024-10-22)

### What's new
- Improve handling of scheduled entries [#10966](https://github.com/statamic/cms/issues/10966) by @jasonvarga
- Field conditions can be based on other data. Assets can use extension, dimensions, etc. [#10588](https://github.com/statamic/cms/issues/10588) by @daun
- Make email config data accessible in email templates [#10949](https://github.com/statamic/cms/issues/10949) by @Jade-GG
- Autoload event listeners and subscribers [#10911](https://github.com/statamic/cms/issues/10911) by @duncanmcclean
- Make sort modifier work with query builders [#10924](https://github.com/statamic/cms/issues/10924) by @aerni
- Allow sorting folders in asset browser [#10935](https://github.com/statamic/cms/issues/10935) by @duncanmcclean
- Vietnamese translations [#10989](https://github.com/statamic/cms/issues/10989) by @diepdo1810

### What's fixed
- Optimize display of long titles in edit forms [#10988](https://github.com/statamic/cms/issues/10988) by @daun
- Improve the dynamic upload folder help text [#10903](https://github.com/statamic/cms/issues/10903) by @jackmcdade
- Fix z-index issue when configuring Replicator fields [#10937](https://github.com/statamic/cms/issues/10937) by @duncanmcclean
- Avoid showing asset upload fixes when inappropriate [#10986](https://github.com/statamic/cms/issues/10986) by @jasonvarga
- Translate dictionaries [#10982](https://github.com/statamic/cms/issues/10982) by @andjsch
- Duplicate form data when duplicating the form [#10985](https://github.com/statamic/cms/issues/10985) by @ryanmitchell
- Prevent protected pages being cached [#10929](https://github.com/statamic/cms/issues/10929) by @duncanmcclean
- Remove mention of installing addons via CP from addon stub [#10975](https://github.com/statamic/cms/issues/10975) by @duncanmcclean
- French translations [#10977](https://github.com/statamic/cms/issues/10977) by @ebeauchamps



## 5.32.0 (2024-10-18)

### What's new
- Improve duplicate asset upload handling [#10959](https://github.com/statamic/cms/issues/10959) by @jasonvarga
- Add `parent` keyword to field conditions [#9385](https://github.com/statamic/cms/issues/9385) by @florianbrinkmann
- Add `filter_empty` modifier [#10962](https://github.com/statamic/cms/issues/10962) by @marcorieser
- Add `invalid_token` variable for password-protected page [#10956](https://github.com/statamic/cms/issues/10956) by @aerni

### What's fixed
- Fix error when editing Bard field with set and no fields [#10971](https://github.com/statamic/cms/issues/10971) by @duncanmcclean
- Fix issue where editing an asset loads the `/edit` url in the browser [#10964](https://github.com/statamic/cms/issues/10964) by @daun
- Remove deprecated options from PHPUnit stub [#10963](https://github.com/statamic/cms/issues/10963) by @duncanmcclean
- German translations [#10968](https://github.com/statamic/cms/issues/10968) by @helloDanuk



## 5.31.0 (2024-10-14)

### What's new
- Dictionary tag [#10885](https://github.com/statamic/cms/issues/10885) by @ryanmitchell
- Make data of password-protected available in the view [#10946](https://github.com/statamic/cms/issues/10946) by @aerni
- Prompt for license when installing starter kit [#10951](https://github.com/statamic/cms/issues/10951) by @duncanmcclean
- Add `taxonomy:count` tag [#10923](https://github.com/statamic/cms/issues/10923) by @aerni

### What's fixed
- Improve UX of rename asset action [#10941](https://github.com/statamic/cms/issues/10941) by @jasonvarga
- Improve UX of rename asset folder action [#10950](https://github.com/statamic/cms/issues/10950) by @duncanmcclean
- Addon `make` commands no longer add to service providers since they are autoloaded [#10942](https://github.com/statamic/cms/issues/10942) by @duncanmcclean
- Tweak `make` command descriptions [#10952](https://github.com/statamic/cms/issues/10952) by @duncanmcclean
- Fix error if submitted password is null [#10945](https://github.com/statamic/cms/issues/10945) by @aerni
- Prevent timeout during `install:eloquent-driver` command [#10955](https://github.com/statamic/cms/issues/10955) by @duncanmcclean
- Fix asset browser history navigation [#10948](https://github.com/statamic/cms/issues/10948) by @daun
- Fix errors in upload queue [#10944](https://github.com/statamic/cms/issues/10944) by @jasonvarga
- Fix error when deleting collections [#10908](https://github.com/statamic/cms/issues/10908) by @duncanmcclean
- Fix ordering search results by date [#10939](https://github.com/statamic/cms/issues/10939) by @duncanmcclean
- Only show the sync/de-synced state for syncable nav fields [#10933](https://github.com/statamic/cms/issues/10933) by @duncanmcclean
- Ensure default values for globals are available in templates [#10909](https://github.com/statamic/cms/issues/10909) by @duncanmcclean
- Handle empty nodes in `bard_text` modifier [#10913](https://github.com/statamic/cms/issues/10913) by @ryanmitchell
- Use directory paths from stache config instead of static paths [#10914](https://github.com/statamic/cms/issues/10914) by @Alpenverein
- Improvements to the `install:eloquent-driver` command [#10910](https://github.com/statamic/cms/issues/10910) by @duncanmcclean
- Check site requested when using global route binding on api routes [#10894](https://github.com/statamic/cms/issues/10894) by @ryanmitchell
- Update "Bug Report" issue template [#10918](https://github.com/statamic/cms/issues/10918) by @duncanmcclean



## 5.30.0 (2024-10-03)

### What's new
- Support scopes as query methods [#5927](https://github.com/statamic/cms/issues/5927) by @aerni

### What's fixed
- Move nocache js back to end of body but make configurable [#10898](https://github.com/statamic/cms/issues/10898) by @jasonvarga
- Fix static cache locking [#10887](https://github.com/statamic/cms/issues/10887) by @duncanmcclean
- Prevent autoloading non-PHP files [#10886](https://github.com/statamic/cms/issues/10886) by @duncanmcclean
- Don't show updates badge count for local/dev installations [#10884](https://github.com/statamic/cms/issues/10884) by @jesseleite
- Update tiptap npm dependencies [#10883](https://github.com/statamic/cms/issues/10883) by @jasonvarga



## 5.29.0 (2024-10-01)

### What's new
- Add reorder() query builder method [#10871](https://github.com/statamic/cms/issues/10871) by @ryanmitchell
- Show toggle UI in conditions builder for revealer fields [#10867](https://github.com/statamic/cms/issues/10867) by @jesseleite
- Autoload addon routes [#10880](https://github.com/statamic/cms/issues/10880) by @duncanmcclean
- Autoload addon dictionaries [#10878](https://github.com/statamic/cms/issues/10878) by @duncanmcclean
- Allow searching by labels in Dictionary fieldtype [#10877](https://github.com/statamic/cms/issues/10877) by @duncanmcclean

### What's fixed
- Improve `AssetFolderPolicy` performance [#10868](https://github.com/statamic/cms/issues/10868) by @jesseleite
- Prevent autoloading addon files causing exception when called early [#10875](https://github.com/statamic/cms/issues/10875) by @ryanmitchell
- Prevent autoloading of abstract classes and interfaces [#10882](https://github.com/statamic/cms/issues/10882) by @duncanmcclean
- Run query scopes after all other query methods so the query can be changed [#10872](https://github.com/statamic/cms/issues/10872) by @ryanmitchell
- Only get relationship createables if can create [#10870](https://github.com/statamic/cms/issues/10870) by @ryanmitchell
- Blink cache Algolia search API calls [#10879](https://github.com/statamic/cms/issues/10879) by @jacksleight
- Increase `trackDirtyStateTimeout` [#10876](https://github.com/statamic/cms/issues/10876) by @jacksleight
- Better timezone dictionary test [#10881](https://github.com/statamic/cms/issues/10881) by @jasonvarga
- Fix duplicate IDs icon [#10864](https://github.com/statamic/cms/issues/10864) by @jesseleite
- French translations [#10861](https://github.com/statamic/cms/issues/10861) by @ebeauchamps
- Dutch translations [#10874](https://github.com/statamic/cms/issues/10874) by @ceesvanegmond
- Dutch translations [#10866](https://github.com/statamic/cms/issues/10866) by @ceesvanegmond



## 5.28.0 (2024-09-30)

### What's new
- Autoload add-on tags, widgets, modifiers etc from folder [#9270](https://github.com/statamic/cms/issues/9270) by @ryanmitchell
- Allow filtering/sorting/paginating with the `form:submissions` tag [#10826](https://github.com/statamic/cms/issues/10826) by @duncanmcclean

### What's fixed
- Fix assets not being uploadable when not using dynamic folders [#10865](https://github.com/statamic/cms/issues/10865) by @jasonvarga
- Fix "Undefined variable $key" error with Marketplace API Client [#10854](https://github.com/statamic/cms/issues/10854) by @duncanmcclean
- Fix false-positive on publish form Action exceptions [#10855](https://github.com/statamic/cms/issues/10855) by @caseydwyer
- Allow make:addon to work for any minimum-stability [#10814](https://github.com/statamic/cms/issues/10814) by @duncanmcclean



## 5.27.0 (2024-09-26)

### What's new
- Dynamic asset folders [#10808](https://github.com/statamic/cms/issues/10808) by @jasonvarga
- Add Nav & Collection Tree Saving events [#10625](https://github.com/statamic/cms/issues/10625) by @ryanmitchell

### What's fixed
- Fix User Accessor in Password Reset [#10848](https://github.com/statamic/cms/issues/10848) by @samharvey44
- Allow for large field configs in filters [#10822](https://github.com/statamic/cms/issues/10822) by @duncanmcclean
- Fix textarea UI bug [#10850](https://github.com/statamic/cms/issues/10850) by @aerni
- Use existing getUrlsCacheKey method instead of duplicating the creation logic [#10836](https://github.com/statamic/cms/issues/10836) by @dadaxr
- Fix issue when using Livewire with full measure static caching [#10306](https://github.com/statamic/cms/issues/10306) by @aerni
- German translations [#10849](https://github.com/statamic/cms/issues/10849) by @helloDanuk
- French translations [#10839](https://github.com/statamic/cms/issues/10839) by @ebeauchamps
- Bump rollup from 3.29.4 to 3.29.5 [#10851](https://github.com/statamic/cms/issues/10851) by @dependabot



## 5.26.0 (2024-09-24)

### What's new
- Improve feedback when action fails [#10264](https://github.com/statamic/cms/issues/10264) by @simonerd
- Add option to exclude flag emojis from countries dictionary [#10817](https://github.com/statamic/cms/issues/10817) by @jasonvarga
- Add entry password protection [#10800](https://github.com/statamic/cms/issues/10800) by @aerni
- Add submitting state for confirmation modal to better visualise a running action [#10699](https://github.com/statamic/cms/issues/10699) by @morhi

### What's fixed
- Fix CP nav ordering for when preferences are stored in JSON SQL columns [#10809](https://github.com/statamic/cms/issues/10809) by @jesseleite
- Fix toasts in actions not being shown [#10828](https://github.com/statamic/cms/issues/10828) by @jasonvarga
- Fix small typo [#10824](https://github.com/statamic/cms/issues/10824) by @1stevengrant
- Improve addons listing [#10812](https://github.com/statamic/cms/issues/10812) by @duncanmcclean
- Prevent concurrent requests to the Marketplace API [#10815](https://github.com/statamic/cms/issues/10815) by @duncanmcclean
- Make limit modifier work with query builders [#10818](https://github.com/statamic/cms/issues/10818) by @aerni
- Hide Visit URL and Live Preview if term has no template [#10789](https://github.com/statamic/cms/issues/10789) by @edalzell
- Set path on asset folder when moving [#10813](https://github.com/statamic/cms/issues/10813) by @jasonvarga
- Reset previous filters when you finish reordering [#10797](https://github.com/statamic/cms/issues/10797) by @duncanmcclean
- Update CSRF token when session expiry login modal is closed [#10794](https://github.com/statamic/cms/issues/10794) by @jasonvarga
- Fix broken state of "Parent" field when saving Home entry with Revisions [#10726](https://github.com/statamic/cms/issues/10726) by @duncanmcclean
- Improve ImageGenerator Exception handling [#10786](https://github.com/statamic/cms/issues/10786) by @indykoning
- When augmenting terms, `entries_count` should only consider published entries [#10727](https://github.com/statamic/cms/issues/10727) by @duncanmcclean
- Prevent saving value of `parent` field to entry data [#10725](https://github.com/statamic/cms/issues/10725) by @duncanmcclean
- Bump vite from 4.5.3 to 4.5.5 [#10810](https://github.com/statamic/cms/issues/10810) by @dependabot



## 5.25.0 (2024-09-10)

### What's new
- Prevent query parameters bloating the static cache [#10701](https://github.com/statamic/cms/issues/10701) by @duncanmcclean
- Add data-type attribute to replicator and bard set divs [#10692](https://github.com/statamic/cms/issues/10692) by @BobWez98

### What's fixed
- Fix enter key not submitting confirmation modal sometimes [#10721](https://github.com/statamic/cms/issues/10721) by @duncanmcclean
- Prevent user from being logged out when ending impersonation [#10780](https://github.com/statamic/cms/issues/10780) by @duncanmcclean
- Only index entries with published status [#10778](https://github.com/statamic/cms/issues/10778) by @jasonvarga
- Remove legacy code that likely caused issues typing 'f' in asset file rename field [#10777](https://github.com/statamic/cms/issues/10777) by @steveparks
- Allow bind of ImageGenerator by removing last `new` call [#10775](https://github.com/statamic/cms/issues/10775) by @wuifdesign
- Private asset container url method should return null [#10769](https://github.com/statamic/cms/issues/10769) by @jasonvarga
- Improve error messaging in `eloquent:import-users` command [#10767](https://github.com/statamic/cms/issues/10767) by @jesseleite
- Prevent error when `cascadeContent`  is an Eloquent Model [#10759](https://github.com/statamic/cms/issues/10759) by @duncanmcclean
- Fix Radio Fieldtype with numeric keys [#10764](https://github.com/statamic/cms/issues/10764) by @duncanmcclean
- Fix url in translations [#10766](https://github.com/statamic/cms/issues/10766) by @ttrig
- Fix Ignition Views [#10765](https://github.com/statamic/cms/issues/10765) by @jasonvarga
- French translations [#10768](https://github.com/statamic/cms/issues/10768) by @ebeauchamps



## 5.24.0 (2024-09-03)

### What's new
- Support querying by any status [#10752](https://github.com/statamic/cms/issues/10752) by @jasonvarga
- Show field handle on hover for fields in Bard & Replicator sets [#10718](https://github.com/statamic/cms/issues/10718) by @duncanmcclean
- Improve the output of the `search:update` command [#10693](https://github.com/statamic/cms/issues/10693) by @duncanmcclean
- Add `LocalizedTermSaved` & `LocalizedTermDeleted` events [#10670](https://github.com/statamic/cms/issues/10670) by @duncanmcclean

### What's fixed
- When a user changes their password, delete any password reset tokens [#10694](https://github.com/statamic/cms/issues/10694) by @duncanmcclean
- Filter out Spacer fields from form emails [#10710](https://github.com/statamic/cms/issues/10710) by @duncanmcclean
- Fix search index race condition [#10695](https://github.com/statamic/cms/issues/10695) by @jasonvarga
- Prevent error when writing to Comb index file [#10712](https://github.com/statamic/cms/issues/10712) by @duncanmcclean
- Prevent button group fieldtype from submitting actions [#10755](https://github.com/statamic/cms/issues/10755) by @duncanmcclean
- Fix augmentation of select options [#10720](https://github.com/statamic/cms/issues/10720) by @duncanmcclean
- Fix translations on password protected pages [#10711](https://github.com/statamic/cms/issues/10711) by @duncanmcclean
- Fix CP Asset Sort After Search issue [#10709](https://github.com/statamic/cms/issues/10709) by @danielml01
- Allow using `entry` as a field handle in navigation blueprints [#10732](https://github.com/statamic/cms/issues/10732) by @duncanmcclean
- Fix relationship fieldtypes showing ID instead of item title [#10737](https://github.com/statamic/cms/issues/10737) by @duncanmcclean
- Twirldown should be shown even if the user doesn't have edit collection permissions [#10750](https://github.com/statamic/cms/issues/10750) by @duncanmcclean
- Update docblock in action class stub [#10751](https://github.com/statamic/cms/issues/10751) by @duncanmcclean
- Update facade docblocks [#10739](https://github.com/statamic/cms/issues/10739) by @duncanmcclean
- Link to "Reserved Words" docs page from field settings. [#10728](https://github.com/statamic/cms/issues/10728) by @duncanmcclean
- Dictionary Fixes [#10719](https://github.com/statamic/cms/issues/10719) by @duncanmcclean
- Fix twirldown on navigation show page [#10731](https://github.com/statamic/cms/issues/10731) by @duncanmcclean
- Fix `preg_replace` error when uploading assets [#10687](https://github.com/statamic/cms/issues/10687) by @duncanmcclean
- Hide "Create Term" button when all taxonomy blueprints are hidden [#10682](https://github.com/statamic/cms/issues/10682) by @duncanmcclean
- Improves "where" modifier value check [#10681](https://github.com/statamic/cms/issues/10681) by @JohnathonKoster
- Antlers: Improves logging behavior when using the `ray` modifier [#10680](https://github.com/statamic/cms/issues/10680) by @JohnathonKoster
- Update the PR template for v5 [#10733](https://github.com/statamic/cms/issues/10733) by @duncanmcclean
- French translations [#10690](https://github.com/statamic/cms/issues/10690) by @ebeauchamps
- Turkish translations [#10742](https://github.com/statamic/cms/issues/10742) [#10685](https://github.com/statamic/cms/issues/10685) by @peimn
- Azerbaijani translations [#10741](https://github.com/statamic/cms/issues/10741) [#10684](https://github.com/statamic/cms/issues/10684) by @peimn
- Persian translations [#10743](https://github.com/statamic/cms/issues/10743) [#10683](https://github.com/statamic/cms/issues/10683) by @peimn



## 5.23.0 (2024-08-21)

### What's new
- Add blade `@cascade` directive [#10674](https://github.com/statamic/cms/issues/10674) by @jacksleight
- Nocache database driver [#10671](https://github.com/statamic/cms/issues/10671) by @jasonvarga
- Add ability to reset namespaced fieldsets [#9166](https://github.com/statamic/cms/issues/9166) by @aerni
- Add ability to reset namespaced blueprints [#9327](https://github.com/statamic/cms/issues/9327) by @ryanmitchell
- Bard: When email address is selected, assume link is a mailto [#10660](https://github.com/statamic/cms/issues/10660) by @duncanmcclean

### What's fixed
- Stop hiding `hidden` field in namespaced blueprints [#10617](https://github.com/statamic/cms/issues/10617) by @ryanmitchell
- Fix fatal windows cache key error [#10667](https://github.com/statamic/cms/issues/10667) by @godismyjudge95
- Fix blueprint override logic [#10661](https://github.com/statamic/cms/issues/10661) by @jasonvarga
- Fix blueprint override logic, pt 2 [#10668](https://github.com/statamic/cms/issues/10668) by @jasonvarga
- Translate confirm modal title [#10659](https://github.com/statamic/cms/issues/10659) by @peimn
- Translate set groups in set previews [#10658](https://github.com/statamic/cms/issues/10658) by @duncanmcclean
- Turkish translation [#10664](https://github.com/statamic/cms/issues/10664) by @peimn
- Azerbaijani translation [#10665](https://github.com/statamic/cms/issues/10665) by @peimn
- Persian translation [#10663](https://github.com/statamic/cms/issues/10663) by @peimn
- Require spatie/laravel-ray in dev [#10662](https://github.com/statamic/cms/issues/10662) by @jasonvarga
- Fix github workflow for changes in JS tests [#10677](https://github.com/statamic/cms/issues/10677) by @jesseleite



## 5.22.1 (2024-08-19)

### What's fixed
- Add more sanitization to control panel [#10656](https://github.com/statamic/cms/issues/10656) by @duncanmcclean
- Copy to clipboard feature falls back to a modal instead of browser dialog [#10654](https://github.com/statamic/cms/issues/10654) by @duncanmcclean



## 5.22.0 (2024-08-16)

### What's new
- Make config values available in form emails [#10649](https://github.com/statamic/cms/issues/10649) by @duncanmcclean
- Display special install commands for first-party addons [#10640](https://github.com/statamic/cms/issues/10640) by @duncanmcclean
- Add ability to set site on the `mount_url` tag [#9561](https://github.com/statamic/cms/issues/9561) by @aerni
- Ability to hide bard/replicator set types [#10349](https://github.com/statamic/cms/issues/10349) by @jacksleight
- Add custom icon selection to CP Nav Preferences [#8023](https://github.com/statamic/cms/issues/8023) by @jesseleite
- Logout user from other devices when changing password [#10548](https://github.com/statamic/cms/issues/10548) by @duncanmcclean

### What's fixed
- Fix suggested options in Field Conditions builder [#10650](https://github.com/statamic/cms/issues/10650) by @duncanmcclean
- Form fields should continue to output a key/value array [#10648](https://github.com/statamic/cms/issues/10648) by @duncanmcclean
- Support arrays in unique value rules [#10646](https://github.com/statamic/cms/issues/10646) by @duncanmcclean
- Fix nocache race condition [#10642](https://github.com/statamic/cms/issues/10642) by @jasonvarga
- Fix save button options not showing [#10633](https://github.com/statamic/cms/issues/10633) by @duncanmcclean
- Hide "Sortable" config option for Computed fields [#10629](https://github.com/statamic/cms/issues/10629) by @duncanmcclean
- Cast to Array to Resolve Issues with Filters Returning `EntryCollection` [#10627](https://github.com/statamic/cms/issues/10627) by @SylvesterDamgaard
- Azerbaijani Translation [#10638](https://github.com/statamic/cms/issues/10638) by @peimn
- Persian Translation [#10637](https://github.com/statamic/cms/issues/10637) by @peimn
- Turkish Translation [#10635](https://github.com/statamic/cms/issues/10635) by @peimn
- Bump axios from 1.6.4 to 1.7.4 [#10628](https://github.com/statamic/cms/issues/10628) by @dependabot



## 5.21.0 (2024-08-13)

### What's new
- Starter kit modules and other misc improvements [#10559](https://github.com/statamic/cms/issues/10559) by @jesseleite
- Pass any appended form config to antlers [#10616](https://github.com/statamic/cms/issues/10616) by @ryanmitchell
- Collection Actions [#10471](https://github.com/statamic/cms/issues/10471) by @edalzell
- Implement NavCreating / NavSaving / NavCreated events [#10604](https://github.com/statamic/cms/issues/10604) by @duncanmcclean

### What's fixed
- Fix rounded corners in asset fields [#10624](https://github.com/statamic/cms/issues/10624) by @daun
- Fix asset tile buttons in read-only mode [#10622](https://github.com/statamic/cms/issues/10622) by @daun
- Adjust dark mode readonly label [#10623](https://github.com/statamic/cms/issues/10623) by @daun
- Add padding to session expiry modal [#10620](https://github.com/statamic/cms/issues/10620) by @jasonvarga
- Prevent published toast when save failed [#10263](https://github.com/statamic/cms/issues/10263) by @simonerd
- Change character_limit to integer on textarea [#10608](https://github.com/statamic/cms/issues/10608) by @jasonvarga
- Adjust behavior of array fields [#10467](https://github.com/statamic/cms/issues/10467) by @duncanmcclean
- Allow options without labels in the select etc fieldtypes [#10336](https://github.com/statamic/cms/issues/10336) by @duncanmcclean
- Handle lock timeout in cache middleware [#10607](https://github.com/statamic/cms/issues/10607) by @jasonvarga
- Render attributes inside single quotes when value contains double quotes [#10600](https://github.com/statamic/cms/issues/10600) by @ryanmitchell
- Warn when using legacy broadcasting env variable when installing the Collaboration addon [#10597](https://github.com/statamic/cms/issues/10597) by @duncanmcclean
- Optimize hover titles of asset edit buttons [#10603](https://github.com/statamic/cms/issues/10603) by @daun
- Update tiptap-php [#10611](https://github.com/statamic/cms/issues/10611) by @arcs-
- French translations [#10609](https://github.com/statamic/cms/issues/10609) by @ebeauchamps



## 5.20.0 (2024-08-08)

### What's new
- Add Boolable interface and fix ArrayableStrings in Antlers conditions [#10595](https://github.com/statamic/cms/issues/10595) by @JohnathonKoster
- License banner is snoozable [#10590](https://github.com/statamic/cms/issues/10590) by @duncanmcclean
- Add Sites to `install:eloquent-driver` command [#10582](https://github.com/statamic/cms/issues/10582) by @duncanmcclean
- Add placeholder option to integer fieldtype [#10566](https://github.com/statamic/cms/issues/10566) by @daun
- Allow hiding border around group fieldtype [#10570](https://github.com/statamic/cms/issues/10570) by @daun
- Allow entry of hex colors without leading hash [#10568](https://github.com/statamic/cms/issues/10568) by @daun
- Add duplicate stacked row option to Grid fieldtype [#10556](https://github.com/statamic/cms/issues/10556) by @PatrickJunod
- Azerbaijani translation [#10561](https://github.com/statamic/cms/issues/10561) by @peimn

### What's fixed
- Always detach localizations when user is missing permissions to delete in other sites [#10587](https://github.com/statamic/cms/issues/10587) by @duncanmcclean
- Sanitize asset folder name on creation [#10577](https://github.com/statamic/cms/issues/10577) by @daun
- Fix error with Files Fieldtype in Forms when no files are uploaded [#10585](https://github.com/statamic/cms/issues/10585) by @duncanmcclean
- Blink augmentation of asset fields with `max_items: 1` [#10580](https://github.com/statamic/cms/issues/10580) by @ryanmitchell
- Fix broken `RelationshipInput` after removing filter [#10584](https://github.com/statamic/cms/issues/10584) by @duncanmcclean
- Allow uppercase characters in field handles [#10591](https://github.com/statamic/cms/issues/10591) by @duncanmcclean
- Prevent error when user is serialized before its created [#10586](https://github.com/statamic/cms/issues/10586) by @duncanmcclean
- Avoid collections in dictionary fields fieldtype [#10579](https://github.com/statamic/cms/issues/10579) by @jasonvarga
- Fix deprecated: third parameter of preg_replace() must not be null [#10576](https://github.com/statamic/cms/issues/10576) by @miloslavkostir
- Adjust confirmation modal behavior [#10537](https://github.com/statamic/cms/issues/10537) by @jasonvarga
- Fix FieldDisplay input eye color for dark mode  [#10565](https://github.com/statamic/cms/issues/10565) by @PatrickJunod
- Clean up duplicate tailwind classes [#10562](https://github.com/statamic/cms/issues/10562) by @heidkaemper
- Fix NullLockStore (disabling of Stache locking) [#10560](https://github.com/statamic/cms/issues/10560) by @925dk
- Display replicator preview of link fields [#10569](https://github.com/statamic/cms/issues/10569) by @daun
- Arabic translations [#10563](https://github.com/statamic/cms/issues/10563) by @rezbouchabou
- Fix invisible links in Bard headings [#10567](https://github.com/statamic/cms/issues/10567) by @daun
- Fix hyphens in JS slugs [#10541](https://github.com/statamic/cms/issues/10541) by @duncanmcclean
- Asset tag: Fail silently when no URL has been provided [#10553](https://github.com/statamic/cms/issues/10553) by @duncanmcclean
- Adding page title to forgot your password page [#10555](https://github.com/statamic/cms/issues/10555) by @tommulroy
- Fix the Video fieldtype with Vimeo file URLs [#10552](https://github.com/statamic/cms/issues/10552) by @duncanmcclean
- Fallback to `nocache` content when request is missing the `Cache` middleware [#9406](https://github.com/statamic/cms/issues/9406) by @duncanmcclean
- Fix error toast when logging in. [#10308](https://github.com/statamic/cms/issues/10308) by @jelleroorda



## 5.19.0 (2024-08-01)

### What's new
- Ability to set default table fieldtype data [#10540](https://github.com/statamic/cms/issues/10540) by @jackmcdade
- Bring back select param on nav tag [#10226](https://github.com/statamic/cms/issues/10226) by @jasonvarga
- Bring back select modifier [#10219](https://github.com/statamic/cms/issues/10219) by @jasonvarga
- Add where_in modifier [#10529](https://github.com/statamic/cms/issues/10529) by @andjsch

### What's fixed
- Fix template & layout fields in Live Preview [#10542](https://github.com/statamic/cms/issues/10542) by @duncanmcclean
- Minor dark mode adjustments [#10544](https://github.com/statamic/cms/issues/10544) by @heidkaemper
- Copy moment file in translate generate [#10547](https://github.com/statamic/cms/issues/10547) by @peimn
- Prevent parentheses and currencies in js slug [#10538](https://github.com/statamic/cms/issues/10538) by @jasonvarga
- Use form submission query count instead of collection count [#10534](https://github.com/statamic/cms/issues/10534) by @dnwjn
- Dutch translations [#10550](https://github.com/statamic/cms/issues/10550) by @FrankGREV
- Persian translation [#10545](https://github.com/statamic/cms/issues/10545) by @peimn
- Turkish translation [#10543](https://github.com/statamic/cms/issues/10543) by @peimn
- French translations [#10539](https://github.com/statamic/cms/issues/10539) by @ebeauchamps



## 5.18.0 (2024-07-30)

### What's new
- Dictionaries [#10380](https://github.com/statamic/cms/issues/10380) by @duncanmcclean
- Make it possible to add to form configuration screen [#8491](https://github.com/statamic/cms/issues/8491) by @ryanmitchell

### What's fixed
- Avoid extending already-extended file cache store [#10526](https://github.com/statamic/cms/issues/10526) by @jasonvarga
- Prevent error when redirecting to first asset container [#10521](https://github.com/statamic/cms/issues/10521) by @duncanmcclean
- Percentage symbols get replaced with dashes in asset filenames [#10523](https://github.com/statamic/cms/issues/10523) by @vluijkx



## 5.17.1 (2024-07-29)

### What's fixed
- BulkAugmentor handles iterables that don't have sequential numeric keys [#10512](https://github.com/statamic/cms/issues/10512) by @kingsven
- Correct issue where search result supplemental data is not available [#10386](https://github.com/statamic/cms/issues/10386) by @JohnathonKoster
- Prevent using `type` as a handle for fields in sets [#10507](https://github.com/statamic/cms/issues/10507) by @duncanmcclean
- Fix button group and radio previews [#10501](https://github.com/statamic/cms/issues/10501) by @jacksleight
- Add frontMatter method to docblock for Parse facade [#10509](https://github.com/statamic/cms/issues/10509) by @godismyjudge95
- Don't enforce a query length on comb searches [#10496](https://github.com/statamic/cms/issues/10496) by @ryanmitchell
- Fix the "Learn More" translation and link [#10497](https://github.com/statamic/cms/issues/10497) by @peimn
- Remove metadata in EntriesTest [#10491](https://github.com/statamic/cms/issues/10491) by @ryanmitchell
- Fix Date Picker dark mode bg color [#10499](https://github.com/statamic/cms/issues/10499) by @jackmcdade
- Sync datetime dark mode with control panel [#10488](https://github.com/statamic/cms/issues/10488) by @peimn
- Turkish translations [#10518](https://github.com/statamic/cms/issues/10518) by @peimn



## 5.17.0 (2024-07-22)

### What's new
- Add hook to query on entries listing [#10479](https://github.com/statamic/cms/issues/10479) by @duncanmcclean
- Ability to select entries from other sites [#9229](https://github.com/statamic/cms/issues/9229) by @aerni

### What's fixed
- Fix taxonomy routes on multi-site [#10398](https://github.com/statamic/cms/issues/10398) by @aerni
- Only output terms in the current locale [#10433](https://github.com/statamic/cms/issues/10433) by @aerni
- Replace characters in asset filename to ensure they are valid on Windows [#10423](https://github.com/statamic/cms/issues/10423) by @pc-pdx
- Fix Table Fieldtype in dark mode [#10484](https://github.com/statamic/cms/issues/10484) by @duncanmcclean
- Handle `required` fields when adding entries to nav [#10468](https://github.com/statamic/cms/issues/10468) by @duncanmcclean
- Add dark mode to saving overlay on ChangePassword component [#10473](https://github.com/statamic/cms/issues/10473) by @martyf
- Prevent showing selected items in relationship field dropdown mode with max items 1 [#10477](https://github.com/statamic/cms/issues/10477) by @jasonvarga
- Persian translation [#10486](https://github.com/statamic/cms/issues/10486) by @peimn
- German translations [#10480](https://github.com/statamic/cms/issues/10480) by @helloDanuk



## 5.16.0 (2024-07-17)

### What's new
- Ability for relationship/entries fieldtype to add "hints" [#10447](https://github.com/statamic/cms/issues/10447) by @jasonvarga

### What's fixed
- Improve multisite fresh-run detection logic [#10469](https://github.com/statamic/cms/issues/10469) by @jesseleite
- Clarify difference between `default()` and `getFallbackConfig()` site methods [#10470](https://github.com/statamic/cms/issues/10470) by @jesseleite



## 5.15.0 (2024-07-17)

### What's new
- Ability to specify the queue connection for the static:warm command [#8634](https://github.com/statamic/cms/issues/8634) by @grantholle
- Show "after saving" actions when revisions are enabled [#9357](https://github.com/statamic/cms/issues/9357) by @duncanmcclean
- Add site events [#10460](https://github.com/statamic/cms/issues/10460) by @jesseleite
- Add a bunch of various events [#10459](https://github.com/statamic/cms/issues/10459) by @duncanmcclean
- Track sites.yaml path in git integration config [#10463](https://github.com/statamic/cms/issues/10463) by @jesseleite
- Display custom logo as plain text [#10350](https://github.com/statamic/cms/issues/10350) by @daun
- Make `config` available to live preview targets [#10443](https://github.com/statamic/cms/issues/10443) by @ryanmitchell
- Make the `<?xml` tag allowed when using PHP short open tags [#10389](https://github.com/statamic/cms/issues/10389) by @JohnathonKoster
- Radio Fieldtype gets custom button icons [#10453](https://github.com/statamic/cms/issues/10453) by @jackmcdade

### What's fixed
- Prevent double login causing 419 CSRF token mismatch [#10465](https://github.com/statamic/cms/issues/10465) by @jasonvarga
- Refactor sites to allow eloquent storage [#10461](https://github.com/statamic/cms/issues/10461) by @ryanmitchell
- Prevent redundant static:warm queued jobs [#10405](https://github.com/statamic/cms/issues/10405) by @robdekort
- Prevent updating time fieldtype value if it hasn't changed [#10409](https://github.com/statamic/cms/issues/10409) by @duncanmcclean
- Fix styling issues with the `save-button-options` component [#10464](https://github.com/statamic/cms/issues/10464) by @duncanmcclean
- Allow using `value` as a field handle [#10462](https://github.com/statamic/cms/issues/10462) by @duncanmcclean
- Use StaticCache facade in Cache Manager utility [#10456](https://github.com/statamic/cms/issues/10456) by @duncanmcclean
- Add missing dark mode styles for license request failed warning [#10448](https://github.com/statamic/cms/issues/10448) by @heidkaemper
- Hide data list pagination page links on collection widget [#10458](https://github.com/statamic/cms/issues/10458) by @jackmcdade
- Fix select fieldtype disabled cursor [#10457](https://github.com/statamic/cms/issues/10457) by @jackmcdade
- Fix Replicator Preview images being too tall [#10455](https://github.com/statamic/cms/issues/10455) by @jackmcdade
- Make Blueprint Picker scrollable [#10454](https://github.com/statamic/cms/issues/10454) by @jackmcdade
- Clean up reference updater localization mapping logic [#10446](https://github.com/statamic/cms/issues/10446) by @jesseleite
- Reverse spaces in RTL [#10184](https://github.com/statamic/cms/issues/10184) by @peimn
- Merge additional params after SVG sanitization [#10400](https://github.com/statamic/cms/issues/10400) by @heidkaemper
- Fix files fieldtype [#10441](https://github.com/statamic/cms/issues/10441) by @duncanmcclean
- Warm structure trees during static warm [#10412](https://github.com/statamic/cms/issues/10412) by @jasonvarga
- Prevent dark mode gradient affecting custom logos [#10444](https://github.com/statamic/cms/issues/10444) by @duncanmcclean



## 5.14.0 (2024-07-10)

### What's new
- Folders are now included in asset browser pagination rather than all being dumped at the top of the first page [#10419](https://github.com/statamic/cms/issues/10419) by @duncanmcclean
- The Assets area of the CP no longer uses tabs. Each container gets their own page. [#10392](https://github.com/statamic/cms/issues/10392) by @duncanmcclean
- Prevent incompatible pagination parameter combinations [#10415](https://github.com/statamic/cms/issues/10415) by @jesseleite
- The static cache lock now uses actual cache locks so they can be customized [#10370](https://github.com/statamic/cms/issues/10370) by @jasonvarga

### What's fixed
- Tweak collection & form widgets border style in light mode [#10426](https://github.com/statamic/cms/issues/10426) by @rezbouchabou
- Use `Create Revision` instead of `Publish` in form when missing publish permission [#10424](https://github.com/statamic/cms/issues/10424) by @edalzell
- Fix toggle fields in data list filters [#10393](https://github.com/statamic/cms/issues/10393) by @duncanmcclean
- Fix augmentable not resolved in transient values [#10417](https://github.com/statamic/cms/issues/10417) by @aerni
- Open modal instead of immediate deletion on click [#10425](https://github.com/statamic/cms/issues/10425) by @justkidding96
- Move updating from collection repo to entry repo [#10383](https://github.com/statamic/cms/issues/10383) by @jasonvarga
- Change method visibility in `AbstractAugmented` class [#10414](https://github.com/statamic/cms/issues/10414) by @aerni
- Allow for undefined file type in ContainerAssetsStore [#10374](https://github.com/statamic/cms/issues/10374) by @aerni
- Fix updater widget icon in dark mode [#10394](https://github.com/statamic/cms/issues/10394) by @rezbouchabou
- French translations [#10406](https://github.com/statamic/cms/issues/10406) by @ebeauchamps
- Arabic translations [#10408](https://github.com/statamic/cms/issues/10408) by @rezbouchabou



## 5.13.0 (2024-07-05)

### What's new
- Arabic translations [#10391](https://github.com/statamic/cms/issues/10391) by @rezbouchabou
- Support operators in where modifier [#10377](https://github.com/statamic/cms/issues/10377) by @jacksleight

### What's fixed
- Fix list fieldtype rendering in listings [#10379](https://github.com/statamic/cms/issues/10379) by @ryanmitchell
- Fix dirty state after entry action or revision publish [#10381](https://github.com/statamic/cms/issues/10381) by @jacksleight
- Fix static:warm not working when using queues and auth together [#10395](https://github.com/statamic/cms/issues/10395) by @duncanmcclean
- Fix CSS for dark mode CP login background [#10399](https://github.com/statamic/cms/issues/10399) by @heidkaemper
- Add negative bottom margin to Textarea's character counter [#10390](https://github.com/statamic/cms/issues/10390) by @jackmcdade
- Improve Asset Fieldtype Dark Mode UI [#10388](https://github.com/statamic/cms/issues/10388) by @jackmcdade
- Rename "Delete" to "Remove" for clarity. [#10387](https://github.com/statamic/cms/issues/10387) by @jackmcdade
- Refactor frontend formFailure to handle precognitive and fetch exceptions [#10376](https://github.com/statamic/cms/issues/10376) by @ryanmitchell
- Fix unauthorized page logout redirect [#10378](https://github.com/statamic/cms/issues/10378) by @bensherred
- Fix user filtering for role or group when using eloquent and a custom table name [#10358](https://github.com/statamic/cms/issues/10358) by @faltjo



## 5.12.0 (2024-06-28)

### What's new
- Ability to disable CP authentication [#8960](https://github.com/statamic/cms/issues/8960) by @duncanmcclean
- Add UI for listing field sortable config [#10259](https://github.com/statamic/cms/issues/10259) by @ryanmitchell

### What's fixed
- Localized entry dates fall back to the origin [#10282](https://github.com/statamic/cms/issues/10282) by @arthurperton
- Support strings in `bard_text` & `bard_html` modifiers [#10369](https://github.com/statamic/cms/issues/10369) by @edalzell
- Allow accessing drafts via the REST API with Live Preview [#10229](https://github.com/statamic/cms/issues/10229) by @duncanmcclean
- Make Asset::clearCaches protected [#10342](https://github.com/statamic/cms/issues/10342) by @ryanmitchell
- Fix HTML fieldtype [#10364](https://github.com/statamic/cms/issues/10364) by @jacksleight
- French translations [#10366](https://github.com/statamic/cms/issues/10366) by @ebeauchamps
- Clarify Statamic repository differences in README [#10368](https://github.com/statamic/cms/issues/10368) by @steveparks



## 5.11.0 (2024-06-24)

### What's new
- Auto stache watcher setting [#10354](https://github.com/statamic/cms/issues/10354) by @jasonvarga
- Allow configuring the Stache's Cache Store [#10303](https://github.com/statamic/cms/issues/10303) by @riasvdv

### What's fixed
- Custom file cache store adjustments [#10362](https://github.com/statamic/cms/issues/10362) by @jasonvarga
- Hide "Restore Revision" button when user is missing relevant permissions [#10314](https://github.com/statamic/cms/issues/10314) by @duncanmcclean
- Remove old logic from support details command [#10360](https://github.com/statamic/cms/issues/10360) by @jasonvarga
- Resolve some deprecatation warnings [#10346](https://github.com/statamic/cms/issues/10346) by @martinoak
- Move test suite from metadata to attributes [#10351](https://github.com/statamic/cms/issues/10351) by @ryanmitchell
- Hide "Rename" and "Delete" options for default filter presets [#10320](https://github.com/statamic/cms/issues/10320) by @duncanmcclean



## 5.10.0 (2024-06-20)

### What's new
- Add site `attribute` method [#10327](https://github.com/statamic/cms/issues/10327) by @ajnsn

### What's fixed
- Fix performance regression by caching fieldtype configs [#10325](https://github.com/statamic/cms/issues/10325) by @jasonvarga
- CSS improvements [#10284](https://github.com/statamic/cms/issues/10284) by @martinoak
- Fix broken revision links on unpublished entries [#10330](https://github.com/statamic/cms/issues/10330) by @faltjo
- Update some translations [#10343](https://github.com/statamic/cms/issues/10343) by @jasonvarga
- Fix linking to addon fields [#10324](https://github.com/statamic/cms/issues/10324) by @edalzell
- Fix nocache tags on shared error pages [#10340](https://github.com/statamic/cms/issues/10340) by @jacksleight
- Migrate from vue-countable [#10287](https://github.com/statamic/cms/issues/10287) by @Cannonb4ll
- Prevent entries being selected when collapsing/expending entries in Tree View [#10322](https://github.com/statamic/cms/issues/10322) by @duncanmcclean
- Ensure `install:broadcasting` is run when installing into Laravel 11 application [#10335](https://github.com/statamic/cms/issues/10335) by @duncanmcclean
- French translations [#10337](https://github.com/statamic/cms/issues/10337) by @ebeauchamps



## 5.9.0 (2024-06-19)

### What's new
- Static cache response statuses [#10334](https://github.com/statamic/cms/issues/10334) by @jasonvarga
- Allow defining a store in the cache tag [#10318](https://github.com/statamic/cms/issues/10318) by @riasvdv

### What's fixed
- Prevent logging "remote: Processed 1 references in total" Git errors [#10332](https://github.com/statamic/cms/issues/10332) by @duncanmcclean
- Fix "Reupload Asset" action [#10333](https://github.com/statamic/cms/issues/10333) by @duncanmcclean
- Lock pint version [#10326](https://github.com/statamic/cms/issues/10326) by @jasonvarga
- Split fieldsets out in install eloquent command [#10310](https://github.com/statamic/cms/issues/10310) by @ryanmitchell
- Fix using `hook` as field name [#10319](https://github.com/statamic/cms/issues/10319) by @duncanmcclean
- Bump braces from 3.0.2 to 3.0.3 [#10315](https://github.com/statamic/cms/issues/10315) by @dependabot
- Bump ws from 8.13.0 to 8.17.1 [#10316](https://github.com/statamic/cms/issues/10316) by @dependabot



## 5.8.0 (2024-06-17)

### What's new
- 404s get included in the full measure static cache [#10294](https://github.com/statamic/cms/issues/10294) by @jasonvarga
- More Flat Camp quotes [#10307](https://github.com/statamic/cms/issues/10307) by @jackmcdade
- Even more [#10288](https://github.com/statamic/cms/issues/10288) by @robdekort
- And more still [#10300](https://github.com/statamic/cms/issues/10300) by @edalzell

### What's fixed
- Prevent additional augmented search result data from being lost [#10301](https://github.com/statamic/cms/issues/10301) by @JohnathonKoster
- Fix nested field path prefixes [#10313](https://github.com/statamic/cms/issues/10313) by @jacksleight



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
