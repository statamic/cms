# Release Notes

## 3.2.15 (2020-10-12)

### What's new
- Entries may be propagated to other sites automatically on creation. [#3304](https://github.com/statamic/cms/issues/3304) by @duncanmcclean
- Slugs may be shown on a collection's tree view. [#4444](https://github.com/statamic/cms/issues/4444) by @tobiasholst
- You can query entries' `blueprint` fields in GraphQL. [#4416](https://github.com/statamic/cms/issues/4416) by @dmgawel

### What's improved
- When creating a new localized entry, the published toggle will now match the origin entry's status. [#4432](https://github.com/statamic/cms/issues/4432) by @jesseleite

### What's fixed
- Fix incompatibility with latest version of Laravel. [#4456](https://github.com/statamic/cms/issues/4456) by @jasonvarga
- Fix Bard reactivity issue [#4438](https://github.com/statamic/cms/issues/4438) by @tobiasholst



## 3.2.14 (2020-10-08)

### What's improved
- Updated German translations. [#4429](https://github.com/statamic/cms/issues/4429) by @helloDanuk

### What's fixed
- Fieldtype titles are translated separately to prevent conflicts with common words. [#4423](https://github.com/statamic/cms/issues/4423) by @jasonvarga
- Collection entry counts are site specific. [#4424](https://github.com/statamic/cms/issues/4424) by @jasonvarga
- Fixed issue where IDs are shown instead of titles in relationship fieldtypes when using Eloquent.  [#4422](https://github.com/statamic/cms/issues/4422) by @tobiasholst



## 3.2.13 (2020-10-07)

### What's improved
- Update Dutch translations. [#4413](https://github.com/statamic/cms/issues/4413) by @robdekort 
- Update French translations. [#4411](https://github.com/statamic/cms/issues/4411) by @ebeauchamps

### What's fixed
- Fix lost asset meta on move / rename. [#4412](https://github.com/statamic/cms/issues/4412) by @jesseleite



## 3.2.12 (2020-10-06)

### What's improved
- Added debouncing to a number of fieldtypes to prevent slowdowns in some situations. [#4393](https://github.com/statamic/cms/issues/4393)
- Updated French translations [#4382](https://github.com/statamic/cms/issues/4382)

### What's fixed
- Fixed Bard's floating toolbar button styles leaking outside of the toolbar. [#4383](https://github.com/statamic/cms/issues/4383)
- Use separate first/last name fields in the user listing and wizard when applicable. [#4408](https://github.com/statamic/cms/issues/4408) [#4399](https://github.com/statamic/cms/issues/4399)
- Fix issue where enabling a site on a taxonomy would not show the terms until the cache is cleared. [#4400](https://github.com/statamic/cms/issues/4400)
- Add missing dimensions icon dimensions. [#4396](https://github.com/statamic/cms/issues/4396)
- Bump `composer/composer` in test suite. [#4401](https://github.com/statamic/cms/issues/4401)



## 3.2.11 (2020-10-04)

### What's improved
- Updated German translations. [#4373](https://github.com/statamic/cms/issues/4373)

### What's fixed
- Added `Cascade::hydrated()` callback method so you can manipulate its data after being hydrated. [#4359](https://github.com/statamic/cms/issues/4359)
- Fix extra live preview data not being in view. [#4359](https://github.com/statamic/cms/issues/4103)
- Make `pluck` modifier work with arrays. [#4374](https://github.com/statamic/cms/issues/4374)
- Fix `parent` tag not finding the parent in some cases. [#4345](https://github.com/statamic/cms/issues/4345)
- `Search::indexExists()` returns `false` rather than throwing an exception. [#4244](https://github.com/statamic/cms/issues/4244)



## 3.2.10 (2020-09-30)

### What's new
- Add `ensureFieldsInSection` method to add multiple fields at the same time. [#4333](https://github.com/statamic/cms/issues/4333)

### What's fixed
- Fix taxonomy terms not returning accurate entries or counts when using certain combinations of collections and multisite. [#4335](https://github.com/statamic/cms/issues/4335)



## 3.2.9 (2020-09-28)

### What's new
- The `group_by` modifier can now be looped over, use nested values, and handles dates. [#4322](https://github.com/statamic/cms/issues/4322)
- Added a `split` modifier. [#4305](https://github.com/statamic/cms/issues/4305)

### What's improved
- Updated French, German, Swiss, and Russian translations. [#4332](https://github.com/statamic/cms/issues/4332) [#4311](https://github.com/statamic/cms/issues/4311) [#4306](https://github.com/statamic/cms/issues/4306)
- Return queries from `QueriesConditions` trait. [#4312](https://github.com/statamic/cms/issues/4312)
- Improve IDE completion of `GraphQL` facade. [#4307](https://github.com/statamic/cms/issues/4307)

### What's fixed
- Fixed `User` class not being overridable. [#4243](https://github.com/statamic/cms/issues/4243)
- Fixed `users` fieldtype outputting too much data. [#4243](https://github.com/statamic/cms/issues/4243)
- Don't show "toggle all" checkbox in the asset selector if only one file is selectable. [#4309](https://github.com/statamic/cms/issues/4309)
- Fix pages that start with the CP route, but not actually within the CP, being considered a CP route. (e.g. `/cpa`) [#4324](https://github.com/statamic/cms/issues/4324)
- Support default values on all applicable form inputs. [#4323](https://github.com/statamic/cms/issues/4323)



## 3.2.8 (2020-09-24)

### What's new
- Add a `format_translated` modifier to get translated Carbon dates. [#4272](https://github.com/statamic/cms/issues/4272)

### What's fixed
- Localized entries with explicit `null` values will no longer inherit their origin's values. [#4301](https://github.com/statamic/cms/issues/4301)
- Fix slow stack selector listing in the `entries` fieldtype. [#4302](https://github.com/statamic/cms/issues/4302)
- Fix error when editing certain imported fields in the blueprint editor. [#4298](https://github.com/statamic/cms/issues/4298)
- Fix overzealous field blink cache that causes data to remain across entries when using the SSG. [#4303](https://github.com/statamic/cms/issues/4303)
- Fix another giant SVG icon. [488c8aca5](https://github.com/statamic/cms/commit/488c8aca5)



## 3.2.7 (2021-09-23)

### What's new
- Add `partial:exists` and `partial:if_exists` tags. [#4296](https://github.com/statamic/cms/issues/4296)

### What's fixed
- Localize the `parent` tag. [#4294](https://github.com/statamic/cms/issues/4294)
- Fix responsiveness of more SVG icons. [#4295](https://github.com/statamic/cms/issues/4295) [6773a7f7b](https://github.com/statamic/cms/commit/6773a7f7b) [ee498c02d](https://github.com/statamic/cms/commit/ee498c02d)



## 3.2.6 (2021-09-21)

### What's new
- When creating a localization of an entry, the slug becomes reactive to the title field again. [#4292](https://github.com/statamic/cms/issues/4292)
- Add a Site column to entry listings when using multisite. [#4279](https://github.com/statamic/cms/issues/4279)

### What's improved
- German translations. [#4283](https://github.com/statamic/cms/issues/4283)

### What's fixed
- Fix Replicator set picker popover going the wrong direction. [#2966](https://github.com/statamic/cms/issues/2966)
- Fix Globals nav and listing causing errors when using multisite. [#4289](https://github.com/statamic/cms/issues/4289)
- Fixed responsiveness of SVG icons. [#4285](https://github.com/statamic/cms/issues/4285)
- Added missing translation. [#4282](https://github.com/statamic/cms/issues/4282)



## 3.2.5 (2021-09-17)

### What's improved
- A number of licensing UX improvements. [#4262](https://github.com/statamic/cms/issues/4262)
- Added Russian translations. [#4263](https://github.com/statamic/cms/issues/4263)
- Updated French translations. [#4255](https://github.com/statamic/cms/issues/4255)
- The Dashboard item is removed from the nav when there are no widgets. [#4266](https://github.com/statamic/cms/issues/4266)



## 3.2.4 (2021-09-09)

### What's fixed
- Fix issue where Bards inside Replicators wouldn't validate. [#4241](https://github.com/statamic/cms/issues/4241)



## 3.2.3 (2021-09-09)

### What's new
- Add `support:zip-blueprint` command. [#3740](https://github.com/statamic/cms/issues/3740)
- Ability to position instructions below the field. [#4157](https://github.com/statamic/cms/issues/4157)

### What's improved
- The `collection` widget's title is now a link. [#4215](https://github.com/statamic/cms/issues/4215)
- Update Dutch translations. [#4207](https://github.com/statamic/cms/issues/4207)

### What's fixed
- Fix issue where newly created asset folders wouldn't show on reload. [#4176](https://github.com/statamic/cms/issues/4176)
- Make the `site:clear` command clear trees. [#4231](https://github.com/statamic/cms/issues/4231)
- Bring back the HTML field on the HTML fieldtype so you can HTML in your HTML field. [a3f7caabb](https://github.com/statamic/cms/commit/a3f7caabb)  
- Read inline SVGs from Composer vendor directory which improves Vapor support. [#4216](https://github.com/statamic/cms/issues/4216)
- Escape user input on git commands. [#4220](https://github.com/statamic/cms/issues/4220)
- Validate replicator as an array. [#4212](https://github.com/statamic/cms/issues/4212)



## 3.2.2 (2021-09-01)

### What's improved
- Added more loop variables to the `nav` tag. [#4195](https://github.com/statamic/cms/issues/4195)
- Update Dutch translations. [#4185](https://github.com/statamic/cms/issues/4185)

### What's fixed
- Fix Starter Kits not being installable on Windows [#4202](https://github.com/statamic/cms/issues/4202)
- Fix Control Panel updater on Windows.  [#4198](https://github.com/statamic/cms/issues/4198)
- Fix Live Preview and Visit URL icons. [e67c3fc53](https://github.com/statamic/cms/commit/e67c3fc53)
- Fix Control Panel side nav height. [statamic/ideas#313](https://github.com/statamic/ideas/issues/313)
- Fix form submissions responses not being returned as JSON. [#4186](https://github.com/statamic/cms/issues/4186)
- Prevent error on empty bard fields, when they're configured to save HTML. [#4197](https://github.com/statamic/cms/issues/4197)
- Fix avatars overlaying incorrectly. [#4196](https://github.com/statamic/cms/issues/4196)



## 3.2.1 (2021-08-26)

### What's improved
- Updated French and German translations. [#4182](https://github.com/statamic/cms/issues/4182) [#4171](https://github.com/statamic/cms/issues/4171) [#4175](https://github.com/statamic/cms/issues/4175) [#4169](https://github.com/statamic/cms/issues/4169)

### What's fixed
- Fix error using `nav:breadcrumbs` on a taxonomy page. [#4179](https://github.com/statamic/cms/issues/4179)
- Fix missing `is_current` in `nav:breadcrumbs` tag by allowing `Page`s to have supplemental values. [#4178](https://github.com/statamic/cms/issues/4178)
- Removed outdated `Blueprint::all()` IDE hint. [#4172](https://github.com/statamic/cms/issues/4172)



## 3.2.0 (2021-08-24)

### What's new
- Official 3.2 release! 🎉

### What's fixed
- References to assets in links in Bard fields will get updated. [#4152](https://github.com/statamic/cms/issues/4152)
- Adjusted nav item editor instructions. [#4142](https://github.com/statamic/cms/issues/4142)
- Removed the warning when renaming assets. [#4141](https://github.com/statamic/cms/issues/4141)
- Changes from 3.1



## 3.2.0-beta.1 (2021-08-17)

### What's new
- A brand new Starter Kit experience that's objectively better in every possible way (just you wait and see). [#3755](https://github.com/statamic/cms/issues/3755)
- Navs can now have their own blueprints. 🎉 [#3941](https://github.com/statamic/cms/issues/3941)
- Addon/extension `make` commands now do all the boilerplate work for you, including wiring up `webpack` because nobody wants to do that. [#3965](https://github.com/statamic/cms/issues/3965)
- An entire Pringles can full of UI improvements that definitely doesn't have a fake snake coiled up inside. [#3967](https://github.com/statamic/cms/issues/3967)
- Added Alpine.js to the Control Panel. [#3903](https://github.com/statamic/cms/issues/3903)
- You can now select multiple rows in CP tables by clicking one, holding down shift, and clicking another. [#3907](https://github.com/statamic/cms/issues/3907)
- References get automatically updated throughout content when you move or rename assets and terms. [#3850](https://github.com/statamic/cms/issues/3850) [#3912](https://github.com/statamic/cms/issues/3912)
- Nav and collection tree items are now are collapsible. [#3899](https://github.com/statamic/cms/issues/3899)
- Added a pagination size selector to the asset browser. [#3957](https://github.com/statamic/cms/issues/3957)

### What's changing
- `cmd`+`s` now does a _quick_ save (and `cmd`+`return` does the full thing). [#3910](https://github.com/statamic/cms/issues/3910)

### What's fixed
- Fixed git error when using symlinks. [#4062](https://github.com/statamic/cms/issues/4062)
- Prevent an error when users move or rename assets. [#4068](https://github.com/statamic/cms/issues/4068)
- The `site:clear` command will no longer remove the default filesystem disk configs.



## 3.1.35 (2021-08-24)

### What's new
- Taxonomy terms may be live previewed. [#4145](https://github.com/statamic/cms/issues/4145)
- The `foreach` tag supports dynamic variables.  [#4012](https://github.com/statamic/cms/issues/4012)
- The `video` fieldtype's placeholder may be customized. [#4162](https://github.com/statamic/cms/issues/4162)

### What's fixed
- Fix missing environment variables when updating through the CP using Laravel Sail.  [#4027](https://github.com/statamic/cms/issues/4027)
- Fix `crop_focal` usage in Glide presets. [#4041](https://github.com/statamic/cms/issues/4041)
- Fix `nav` and `nav:breadcrumbs` tags showing incorrectly showing redirected items as current. [#4058](https://github.com/statamic/cms/issues/4058)
- Fix date column on form submissions table. [#3969](https://github.com/statamic/cms/issues/3969)
- Make the `as` modifier work with collections. [#4130](https://github.com/statamic/cms/issues/4130)
- In the `static:warm` command, fix missing requests, and show errors for failed requests. [#4128](https://github.com/statamic/cms/issues/4128)
- Fix term `title` not taking the supplemented value into account. [#4153](https://github.com/statamic/cms/issues/4153)
- Fix CP nav items not being marked as active when there's a query string. [#4149](https://github.com/statamic/cms/issues/4149)
- Fix the `link` fieldtype's perpetual dirty state [#4148](https://github.com/statamic/cms/issues/4148)
- When creating a blueprint, the title will get slugified to prevent colons and question marks. [#4143](https://github.com/statamic/cms/issues/4143)



## 3.1.34 (2021-08-17)

### What's new
- Add `add_slashes` modifier. [74208d23e](https://github.com/statamic/cms/commit/74208d23e)
- Add `getKey` method to `User` class. [#4122](https://github.com/statamic/cms/issues/4122)

### What's improved
- Adjusted German translations. [#4126](https://github.com/statamic/cms/issues/4126)
- Align command descriptions. [#4125](https://github.com/statamic/cms/issues/4125)

### What's fixed
- Fix data-table's rounded corners hover state. [1ea06666b](https://github.com/statamic/cms/commit/1ea06666b)



## 3.1.33 (2021-08-13)

### What's new
- Add Laravel Nova and Horizon links to CP. [#4076](https://github.com/statamic/cms/issues/4076) [#4074](https://github.com/statamic/cms/issues/4074)

### What's improved
- Updated French and German translations. [#4057](https://github.com/statamic/cms/issues/4057) [#4078](https://github.com/statamic/cms/issues/4078)

### What's fixed
- Fix GraphQL error when trying to get `parent` entry. [#3971](https://github.com/statamic/cms/issues/3971)
- GraphQL uses floats instead of integers for image dimensions. [#4107](https://github.com/statamic/cms/issues/4107)
- The `video` fieldtype no longer strips the `?` from YouTube URLs. [#4104](https://github.com/statamic/cms/issues/4104)
- Add `password_activations` table to the `auth:migration` command stub. [#4079](https://github.com/statamic/cms/issues/4079)
- Update the config stubs used by the `clear:site` command. [#4060](https://github.com/statamic/cms/issues/4060)
- The `markdown` field's `cmd+left` behavior now works the way you expect it to. [ad0c05bb4](https://github.com/statamic/cms/commit/ad0c05bb4)
- Fix blueprint card corners. [#4103](https://github.com/statamic/cms/issues/4103)
- Fix tab overflow dropdown in LivePreview pane. [a1c3f5bdb](https://github.com/statamic/cms/commit/a1c3f5bdb)
- Hide "Add Set" button when there are no sets. [#4083](https://github.com/statamic/cms/issues/4083)
- Prevent showing the Laravel Telescope link when the user doesn't have permission. [#4075](https://github.com/statamic/cms/issues/4075)
- Fix view scaffolding tpyo. [#4077](https://github.com/statamic/cms/issues/4077)
- Add toggle for "create" config option on `entries` and `terms` fieldtypes. [#4069](https://github.com/statamic/cms/issues/4069)
- Bump `path-parse` from 1.0.6 to 1.0.7 [#4110](https://github.com/statamic/cms/issues/4110)
- Remove unused `tar` dependency. [c963ac8b5](https://github.com/statamic/cms/commit/c963ac8b5)
- Fix QueriesConditionTest [#4113](https://github.com/statamic/cms/issues/4113)
- Add more assertions for ternary conditions inside tag parameters [#4111](https://github.com/statamic/cms/issues/4111)



## 3.1.32 (2021-07-28)

### What's fixed
- When we designed the [tab overflow](https://github.com/statamic/cms/issues/4051) UI, we neglected the other tabs in the control panel. They look like tabs again.

## 3.1.31 (2021-07-28)

### What's new
- Ability to query individual entries in a specific site using GraphQL. [#4055](https://github.com/statamic/cms/issues/4055)

### What's improved
- Publish form tabs now overflow into a dropdown when there's no more room. [#4051](https://github.com/statamic/cms/issues/4051)
- Field handles are shown when hovering over labels for a moment. [statamic/ideas#302](https://github.com/statamic/ideas/issues/302)

### What's fixed
- Fix newly uploaded assets showing incorrect metadata. [#4059](https://github.com/statamic/cms/issues/4059)
- Fix updating of entry's `author` field when user doesn't have permission to edit author. [#4052](https://github.com/statamic/cms/issues/4052)



## 3.1.30 (2021-07-26)

### What's new
- Added a command to warm the static cache. [#4016](https://github.com/statamic/cms/issues/4016)

### What's improved
- A more helpful exception is thrown when editing an entry that has an invalid blueprint. [#3977](https://github.com/statamic/cms/issues/3977)

### What's fixed
- Fix asset editor overflow issues. [#4043](https://github.com/statamic/cms/issues/4043)
- Fix tree path issue on Windows. [#4024](https://github.com/statamic/cms/issues/4024)
- Fix issue where video fieldtypes would prevent asset selection. [#3978](https://github.com/statamic/cms/issues/3978)



## 3.1.29 (2021-07-16)

### What's fixed
- Fix validation on blueprint editor. [#4015](https://github.com/statamic/cms/issues/4015)



## 3.1.28 (2021-07-15)

### What's new
- The `link` fieldtype can have its collections specified, and it defaults to only collections with routes. [#3973](https://github.com/statamic/cms/issues/3973)
- Sites can now have arbitrary attributes. [#3996](https://github.com/statamic/cms/issues/3996)
- Added GraphQL support for the `array` fieldtype. [#3972](https://github.com/statamic/cms/issues/3972)
- Added a `session:has` tag. [#3958](https://github.com/statamic/cms/issues/3958)

### What's improved
- The blueprint builder will prevent you from adding fields with reserved words. [#3989](https://github.com/statamic/cms/issues/3989)
- The blueprint builder will allow you to set default field values.  [#3982](https://github.com/statamic/cms/issues/3982)
- The collection scaffolder now just creates views. [#3997](https://github.com/statamic/cms/issues/3997)
- In listings that only ever require a single selection, you no longer need to unselect before selecting its replacement. [#3950](https://github.com/statamic/cms/issues/3950)

### What's fixed
- Fix "create navigation" button not appearing when you have permission. [#4005](https://github.com/statamic/cms/issues/4005)
- Fix asset meta data disappearing when renaming programmatically. [#3964](https://github.com/statamic/cms/issues/3964)
- Collection trees will be deleted when setting a collection to not orderable. [#3901](https://github.com/statamic/cms/issues/3901)
- Prevent video previews blowing out the UI of the asset editor. [#3975](https://github.com/statamic/cms/issues/3975)
- Support collections in the `reverse` modifier. [#4003](https://github.com/statamic/cms/issues/4003)
- Prevent the Live Preview toolbar covering the Bard toolbar. [#3979](https://github.com/statamic/cms/issues/3979)
- Tree files are ignored if they don't have a matching collection. [#3948](https://github.com/statamic/cms/issues/3948)
- Consolidate the Stache's `getItemFilter` and `getFileFilter` methods. [#4001](https://github.com/statamic/cms/issues/4001)
- Fix `search` tag when the `as` param is used without `paginate`. [#4000](https://github.com/statamic/cms/issues/4000)
- Fix static caching invalidation mismatch when using trusted proxies and SSL. [#3939](https://github.com/statamic/cms/issues/3939)
- Fix removing users from groups when using Eloquent. [#3951](https://github.com/statamic/cms/issues/3951)
- Fix table word wrap weirdness. [#2550](https://github.com/statamic/cms/issues/2550)
- Fix `code` fieldtype not loading, and `textarea` fieldtype not being the correct height when in inactive tabs. [#3955](https://github.com/statamic/cms/issues/3955)
- Fix site specific views not loading. [#3944](https://github.com/statamic/cms/issues/3944)



## 3.1.27 (2021-07-01)

### What's improved
- Structure entry eager loading is conditional, and won't happen when loading front-end pages. [#3540](https://github.com/statamic/cms/issues/3540)

### What's fixed
- Only update Stache indexes when they aren't cached at all yet. [#3936](https://github.com/statamic/cms/issues/3936)
- Fix `is_parent` being `true` for the home page in the `nav` tag when your site is defined with a full URL. [#3900](https://github.com/statamic/cms/issues/3900)
- Fix error when getting the dimensions for a zero byte image. [#3935](https://github.com/statamic/cms/issues/3935)
- That also prevents corrupt images from constantly recalculating their dimensions.
- Fix `dimensions` validation rule on `assets` fields. [#3922](https://github.com/statamic/cms/issues/3922)
- Filter out `import` fields from the "add existing field" pane in the Blueprint builder, which was causing JS errors. [#3924](https://github.com/statamic/cms/issues/3924)
- Make Grid column squeezing rules more specific, which fixes a derpy `date` field. [#3917](https://github.com/statamic/cms/issues/3917)
- Check permissions for the "Create" button on the `entries` fieldtype. [#3906](https://github.com/statamic/cms/issues/3906)
- Fix `integer` fieldtype focus state. [6211855ad](https://github.com/statamic/cms/commit/6211855ad)
- Fix `isLowerCase` method casing. [9f05530bf](https://github.com/statamic/cms/commit/9f05530bf)
- Fix random invalid popper reference errors. [d8a0f52b9](https://github.com/statamic/cms/commit/d8a0f52b9)



## 3.1.26 (2021-06-22)

### What's new
- The `View::make()` method can now accept data.
- Added a `View::first()` method that uses the first view that exists in a given array. [#3880](https://github.com/statamic/cms/issues/3880)


### What's fixed
- Fixed pagination in the `search:results` tag. [#3894](https://github.com/statamic/cms/issues/3894)
- Static caching: Collection-based term URLs get invalidated when saving a term. [#3884](https://github.com/statamic/cms/issues/3884)
- Static caching: Reordering a collection will invalidate the URL where it's mounted. [#3885](https://github.com/statamic/cms/issues/3885)
- Static caching: Saving a nav tree will trigger invalidation. [#3882](https://github.com/statamic/cms/issues/3882)
- Prevent error for entries with `layout: false`, or routes with `layout: false`/`null`. [#3893](https://github.com/statamic/cms/issues/3893)
- Apply field config `classes` to `grid` fields. [#3881](https://github.com/statamic/cms/issues/3881)
- Improve handling of creating terms in a non-default site. [#3441](https://github.com/statamic/cms/issues/3441)
- Fix errors when importing single fields into Blueprints. [#3249](https://github.com/statamic/cms/issues/3249)
- Prevent seeing asset breadcrumbs when navigation is restricted. [#3873](https://github.com/statamic/cms/issues/3873)
- Fixed the "Create Folder" field not gaining focus in some browsers. [#3874](https://github.com/statamic/cms/issues/3874)
- Bump `striptags` from 3.1.1 to 3.2.0 [#3879](https://github.com/statamic/cms/issues/3879)


## 3.1.25 (2021-06-18)

### What's new
- Support for `.antlers.xml` views which automatically set the response type to `text/xml`. [#3855](https://github.com/statamic/cms/issues/3855)
- You can add more fieldtypes to the selector when building Form blueprints. [#3866](https://github.com/statamic/cms/issues/3866)
- You can set the `width` on `grid` sub-fields in table mode. [#3867](https://github.com/statamic/cms/issues/3867)

### What's improved
- When using full-measure static caching, pages with really long query strings will fall back to half-measure caching. [#3864](https://github.com/statamic/cms/issues/3864)

### What's fixed
- Fix filtering of taxonomy terms by collection. [#3870](https://github.com/statamic/cms/issues/3870)
- Term files will always be created when using the `terms` fieldtype. [#3852](https://github.com/statamic/cms/issues/3852)
- Fix wrong asset container with similar URLs being resolved. [#3858](https://github.com/statamic/cms/issues/3858)
- Prevent dispatching invalidation jobs when static caching is disabled. [#3869](https://github.com/statamic/cms/issues/3869)



## 3.1.24 (2021-06-16)

### What's new
- Add support for defining default password validation rules. [#3823](https://github.com/statamic/cms/3823)

### What's fixed
- Markdown fieldtype previews are rendered server side so you can see custom parsers. [#3859](https://github.com/statamic/cms/3859)
- Prevent static caching error when visiting a URL with a really long query string. [#3860](https://github.com/statamic/cms/3860)
- Fix the size of SVGs in the assets fieldtype when in list mode. [#3854](https://github.com/statamic/cms/3854)



## 3.1.23 (2021-06-14)

### What's improved
- Updated German, French, and Dutch translations [#3843](https://github.com/statamic/cms/issues/3843) [#3838](https://github.com/statamic/cms/issues/3838) [#3841](https://github.com/statamic/cms/issues/3841)

### What's fixed
- Fixed validation error when saving entries in a strutured collection without a route. [#3847](https://github.com/statamic/cms/issues/3847)
- Prevent an error when selecting an entry to mount to a collection. [#3846](https://github.com/statamic/cms/issues/3846)
- Assets can now access their `mime_type` in templates. [c5858990f](https://github.com/statamic/cms/commit/c5858990f)



## 3.1.22 (2021-06-11)

### What's new
- Action classes can customize their toast messages. [#3822](https://github.com/statamic/cms/issues/3822)
- Sites may specify text direction. [#3815](https://github.com/statamic/cms/issues/3815)
- Added `form` to Submission. [#3792](https://github.com/statamic/cms/issues/3792)
- Added `width` attribute to DataList's Table component. [#3762](https://github.com/statamic/cms/issues/3762)
- Added `maxlength` to the views of form fields that have a `character_limit` set. [#3797](https://github.com/statamic/cms/issues/3797)

### What's improved
- Updated Dutch translations. [#3834](https://github.com/statamic/cms/issues/3834)

### What's fixed
- Fixed issue where ticking many items in a listing could fail. [#3298](https://github.com/statamic/cms/issues/3298)
- Fixed `fatal: not a git repository` errors when using symlinks. [#3829](https://github.com/statamic/cms/issues/3829)
- Prevented the "Create Fieldset" button disappearing. [#3821](https://github.com/statamic/cms/issues/3821)
- Added a Submission typehint. [370cdc4ea](https://github.com/statamic/cms/commit/370cdc4ea)
- Bump browserslist from 4.12.2 to 4.16.6 [#3769](https://github.com/statamic/cms/issues/3769)



## 3.1.21 (2021-06-09)

### What's new
- The `redirect` tag will pass along route parameters when targeting a named route. [#3801](https://github.com/statamic/cms/issues/3801)

### What's fixed
- Fix entry slug or date changes not being reflected in filename. [#3816](https://github.com/statamic/cms/issues/3816)
- Fix incorrect blueprint being saved to localized entry files. [#3818](https://github.com/statamic/cms/issues/3818)
- Fix error when attempting to parse an `image` validation rule. [#3812](https://github.com/statamic/cms/issues/3812)



## 3.1.20 (2021-06-08)

### What's fixed
- URI uniqueness is validated per site. [#3808](https://github.com/statamic/cms/issues/3808)
- Prevent an infinite loop when you manually create an entry file without an ID. [#3807](https://github.com/statamic/cms/issues/3807)



## 3.1.19 (2021-06-07)

### What's new
- Allow duplicate entry slugs (mainly so you can have entries of the same slug in different positions of a tree.) [#3671](https://github.com/statamic/cms/issues/3671)
- Validation replacements. [#3690](https://github.com/statamic/cms/issues/3690)
- Added an `installed` tag to check for packages within Antlers templates. [#3800](https://github.com/statamic/cms/issues/3800)

### What's fixed
- The `blueprint` is always saved to an entry. [#3786](https://github.com/statamic/cms/issues/3786)
- Fix support for multiline `@{{ }}` noparse tags. [#3785](https://github.com/statamic/cms/issues/3785)
- Bump dns-packet from 1.3.1 to 1.3.4 [#3779](https://github.com/statamic/cms/issues/3779)



## 3.1.18 (2021-05-28)

### What's improved
- Updated Dutch and French translations. [#3781](https://github.com/statamic/cms/issues/3781) [#3777](https://github.com/statamic/cms/issues/3777)

### What's fixed

- Fix `@{{ }}` noparse tags with nested braces. [#3784](https://github.com/statamic/cms/issues/3784)
- Fix an issue where a Grid with `min_rows` inside a Replicator wouldn't work by passing along pre-processed values. [#3782](https://github.com/statamic/cms/issues/3782)



## 3.1.17 (2021-05-26)

### What's improved
- Support underscored partials in a partials directory. [statamic/ideas#305](https://github.com/statamic/ideas/issues/305)

### What's fixed
- A bunch of date related fixes. [#3730](https://github.com/statamic/cms/issues/3730)
- Fix and improve Stache path handling. Fixes a couple of term related errors. [#3768](https://github.com/statamic/cms/issues/3768)
- Fix modifiers not working with dynamic array keys. [#3737](https://github.com/statamic/cms/issues/3737)
- Fix an error when using the `locales` tag on non-content routes. [#3754](https://github.com/statamic/cms/issues/3754)
- Fix an updater error on certain environments. [#3734](https://github.com/statamic/cms/issues/3734)



## 3.1.16 (2021-05-20)

### What's fixed
- Reverted the lodash and underscore upgrades from 3.1.15 temporarily. [#3750](https://github.com/statamic/cms/issues/3750)



## 3.1.15 (2021-05-20)

### What's new
- You can programmatically get and set a user's preferred locale more easily. [#3725](https://github.com/statamic/cms/issues/3725)
- You can customize a Collection's "Create Entry" text. [#3586](https://github.com/statamic/cms/issues/3586)

### What's improved
- The Bard link picker will autofocus the URL input. [#3741](https://github.com/statamic/cms/issues/3741)
- Updated French translations [#3718](https://github.com/statamic/cms/issues/3718) [#3716](https://github.com/statamic/cms/issues/3716)

### What's fixed
- Fix issue where the site URL sometimes would be incorrect, causing incorrect behavior in the `nav:breadcrumbs` tag, and likely other places. [#3695](https://github.com/statamic/cms/issues/3695)
- Fix the `locales` tag only working for entries. [#3689](https://github.com/statamic/cms/issues/3689)
- Fix asset editor not being editable even if you have permission. [#3743](https://github.com/statamic/cms/issues/3743)
- Prevent mounting an entry from the same collection onto itself. [#3731](https://github.com/statamic/cms/issues/3731)
- The `entries` fieldtype filters out unpublished entries when augmenting. [#3544](https://github.com/statamic/cms/issues/3544)
- Typehint the Submission interface in the form email class so custom implementations can be used. [#3596](https://github.com/statamic/cms/issues/3596)
- Bump underscore from 1.9.2 to 1.12.1 [#3662](https://github.com/statamic/cms/issues/3662)
- Bump lodash from 4.17.19 to 4.17.21 [#3672](https://github.com/statamic/cms/issues/3672)



## 3.1.14 (2021-05-14)

### What's new
- Add Bard node extension helper. [#3657](https://github.com/statamic/cms/issues/3657)

### What's improved
- Add HTML fieldtype icon. [247364cbb](https://github.com/statamic/cms/commit/247364cbb)
- Update Spanish, German, Dutch, and French Translations [#3706](https://github.com/statamic/cms/issues/3706) [#3674](https://github.com/statamic/cms/issues/3674) [#3703](https://github.com/statamic/cms/issues/3703) [#3688](https://github.com/statamic/cms/issues/3688)

### What's fixed
- Fix avatar URLs for some situations. [468a55864](https://github.com/statamic/cms/commit/468a55864)
- Fix error when selecting certain collections in a Bard fieldtype. [#3709](https://github.com/statamic/cms/issues/3709)
- Fix array fieldtype always being dirty. [#3704](https://github.com/statamic/cms/issues/3704)
- Fix GraphQL error in globals and terms. [#3711](https://github.com/statamic/cms/issues/3711)
- Fix Bard z-index issue. [#3694](https://github.com/statamic/cms/issues/3694)
- Fix SVG Dimensions. [#3702](https://github.com/statamic/cms/issues/3702)
- Explicitly use Stringy for `Str::replace()`. [#3698](https://github.com/statamic/cms/issues/3698)
- Bump codemirror from 5.55.0 to 5.58.2. [#3691](https://github.com/statamic/cms/issues/3691)
- Bump url-parse from 1.4.7 to 1.5.1. [#3664](https://github.com/statamic/cms/issues/3664)
- Bump hosted-git-info from 2.8.8 to 2.8.9. [#3676](https://github.com/statamic/cms/issues/3676)



## 3.1.13 (2021-05-10)

### What's improved
- In Bard, display the asset container option when using the link or image buttons. [#3665](https://github.com/statamic/cms/issues/3665)
- Make dropdown items links, letting you open them in new tabs. [#3667](https://github.com/statamic/cms/issues/3667)

### What's fixed
- Update tracked keys when saving and deleting [#3684](https://github.com/statamic/cms/issues/3684)
- Bard link picker only show entries for collections with a route. [#3679](https://github.com/statamic/cms/issues/3679)
- Separate the title and optional translation. [#3675](https://github.com/statamic/cms/issues/3675)
- Fix Bard error when linked entries and assets are deleted. [#3678](https://github.com/statamic/cms/issues/3678)
- Fix `date` fieldtype's `time_enabled` option [#3661](https://github.com/statamic/cms/issues/3661)
- Fix `link` fieldtype alignment. [83aededfe](https://github.com/statamic/cms/commit/83aededfe)
- Fix YAML fence when dumping multiline string as last key [#3663](https://github.com/statamic/cms/issues/3663)
- Fix "Create Fieldset" button not displaying. [#3645](https://github.com/statamic/cms/issues/3645)



## 3.1.12 (2021-05-06)

### What's new
- Added Duplicate ID tracking with Control Panel and CLI reviewing options. [#3619](https://github.com/statamic/cms/issues/3619)
- You can replace ProseMirror nodes and marks with custom ones. [#3648](https://github.com/statamic/cms/issues/3648)

### What's improved
- Added `required` attributes to dynamic form field html. [#3592](https://github.com/statamic/cms/issues/3592)
- Updated German translations. [#3607](https://github.com/statamic/cms/issues/3607)

### What's fixed
- Fix a bunch of Stache issues. [#3619](https://github.com/statamic/cms/issues/3619) [#3616](https://github.com/statamic/cms/issues/3616)
- Fix augmentation fallback behavior [#3660](https://github.com/statamic/cms/issues/3660)
- Fix `trans_choice` Tag [#3650](https://github.com/statamic/cms/issues/3650)
- Fix `link` fieldtype not showing the saved value. [#3637](https://github.com/statamic/cms/issues/3637)
- Fix localizable Grid fields in stacked mode being read only. [#3518](https://github.com/statamic/cms/issues/3518)
- Add asset selector to Bard link toolbar. [#3591](https://github.com/statamic/cms/issues/3591)
- Favor authors value in Stache index [#3617](https://github.com/statamic/cms/issues/3617)
- Bump composer requirement, of composer. [#3653](https://github.com/statamic/cms/issues/3653)



## 3.1.11 (2021-04-28)

### What's improved
- Assets uploaded in the selector stack will be automatically selected. [#3604](https://github.com/statamic/cms/issues/3604)
- Improved the UX of the `link` fieldtype. [#3605](https://github.com/statamic/cms/issues/3605)
- Updated French and German translations. [#3583](https://github.com/statamic/cms/issues/3583) [#3589](https://github.com/statamic/cms/issues/3589) [#3601](https://github.com/statamic/cms/issues/3601)

### What's fixed
- Fix relationship fieldtypes sometimes only showing IDs. [#3547](https://github.com/statamic/cms/issues/3547)
- Prevent regenerating asset meta file for non-images. [#3609](https://github.com/statamic/cms/issues/3609)
- Handle custom `authors` field. [#3599](https://github.com/statamic/cms/issues/3599)
- Cascade is reused on subsequent calls rather than rehydrating. [#3595](https://github.com/statamic/cms/issues/3595)
- Old input values are remembered in the `user:register_form`. [#3584](https://github.com/statamic/cms/issues/3584)
- Support collections in the `sentence_list` modifier. [#3593](https://github.com/statamic/cms/issues/3593)
- Support collections in the `option_list` modifier. [#3606](https://github.com/statamic/cms/issues/3606)
- Fix bard formatting inside links. [#3108](https://github.com/statamic/cms/issues/3108)



## 3.1.10 (2021-04-23)

### What's improved
- Improve Laravel Nova compatibility by avoiding conflicting routes. [#3543](https://github.com/statamic/cms/issues/3543)
- A read-only asset editor is now more read-only-er. [#3552](https://github.com/statamic/cms/issues/3552)
- Improved asset upload failure error messages. [#3560](https://github.com/statamic/cms/issues/3560)

### What's fixed
- The `link` and `path` tags use the `id` parameter to output urls for entries, etc. [#3576](https://github.com/statamic/cms/issues/3576)
- They'll use the original item's url if it doesn't exist in the current site. [#3579](https://github.com/statamic/cms/issues/3579)
- Fix the Collection edit screen not showing existing routes, and prevent an incorrect dirty state message. [#3581](https://github.com/statamic/cms/issues/3581)
- Fix issues where the Static Site Generator would sometimes leak data between pages. [#3562](https://github.com/statamic/cms/issues/3562)
- Fix set reordering for Bard and Replicator. [#3574](https://github.com/statamic/cms/issues/3574)
- Fix page not scrolling when dragging Bard sets. [#3571](https://github.com/statamic/cms/issues/3571)
- Fix images not displaying in Bard. [#3570](https://github.com/statamic/cms/issues/3570)
- Add missing red asterisk to required fields in a Bard or Replicator set. [#3572](https://github.com/statamic/cms/issues/3572)
- Fix issue where the first line after an image in Bard was not editable. [#3555](https://github.com/statamic/cms/issues/3555)
- Fix issue where you sometimes couldn't move the cursor in Bard. [#3559](https://github.com/statamic/cms/issues/3559)
- When using a collection widget with pagination, you don't get scrolled to the top of the page. [#3553](https://github.com/statamic/cms/issues/3553)
- Fix the missing delete action for asset folders. [#3582](https://github.com/statamic/cms/issues/3582)
- Bump ssri from 6.0.1 to 6.0.2. [#3549](https://github.com/statamic/cms/issues/3549)



## 3.1.9 (2021-04-19)

### What's improved
- Added header to disable Google's FLoC tracking by default. [#3545](https://github.com/statamic/cms/issues/3545)



## 3.1.8 (2021-04-16)

### What's fixed
- Fix n+1 user group and role queries when storing users in the database. [#3527](https://github.com/statamic/cms/issues/3527)
- Fix taxonomy not loading when your site has been configured with a subdirectory. [#3541](https://github.com/statamic/cms/issues/3541)



## 3.1.7 (2021-04-15)

### What's new
- The `link` and `path` tags can output URLs for entries, terms, etc. [#3530](https://github.com/statamic/cms/issues/3530)
- You can customize the table names for storing users in a database. [#3278](https://github.com/statamic/cms/issues/3278)
- Added a `urlWithoutRedirect` and `absoluteUrlWithoutRedirect` methods to entries and terms. [#3522](https://github.com/statamic/cms/issues/3522)

### What's improved
- Adjusted the UI for the site selector on the entry and term publish forms. [#3519](https://github.com/statamic/cms/issues/3519)

### What's fixed
- Localized entries can save empty values, which fixes not being able to override the values from the original entry. [#3531](https://github.com/statamic/cms/issues/3531)
- Private entries can be viewed in Live Preview. [#3533](https://github.com/statamic/cms/issues/3533)
- Fix the site being used in Live Preview. [#3534](https://github.com/statamic/cms/issues/3534)
- Fix JavaScript modules only loading one time in Live Preview. [#3524](https://github.com/statamic/cms/issues/3524)
- The `text` fieldtype, when using number mode will prevent an empty value being saved as `0`. [#3536](https://github.com/statamic/cms/issues/3536)
- It will also save integers or floats appropriately. [a18d6f639](https://github.com/statamic/cms/commit/a18d6f639)
- Add border to selected non-image thumbnails in the asset browser. [#3525](https://github.com/statamic/cms/issues/3525)
- Fix `is_parent` on nav tags when using first-child redirects. [#2359](https://github.com/statamic/cms/issues/2359)
- The `permalink` variable on nav items with hardcoded URLs will now be converted to absolute URLs. [#3522](https://github.com/statamic/cms/issues/3522)
- Fixed YAML exceptions sometimes showing the wrong file's contents. [#3515](https://github.com/statamic/cms/issues/3515)



## 3.1.6 (2021-04-12)

### What's new
- Added a `pluck` modifier. [#3502](https://github.com/statamic/cms/issues/3502)
- The `multisite` command lets you add more than one additional site. [#3302](https://github.com/statamic/cms/issues/3302)
- Added a `max_depth` parameter to the `nav` tag. [#3513](https://github.com/statamic/cms/issues/3513)

### What's improved
- Updated French translations. [#3497](https://github.com/statamic/cms/issues/3497)
- Gracefully handle incorrect-but-close-enough usage of `custom` field conditions. [73f941c5e](https://github.com/statamic/cms/commit/73f941c5e)

### What's fixed
- Fix taxonomy routing when using localization. [#3505](https://github.com/statamic/cms/issues/3505)
- In the `search:results` tag, include `search_score`, and fix `result_type` when not supplementing data. [#3477](https://github.com/statamic/cms/issues/3477)
- Preserve user defined defaults for new entries. [#3472](https://github.com/statamic/cms/issues/3472)
- Prevent newly added navigation items being greyed out even if they're published. [#3510](https://github.com/statamic/cms/issues/3510)
- Fix trailing slash on URLs which sometimes makes the asset browser not load. [#3504](https://github.com/statamic/cms/issues/3504)
- Fix error in the `assets:generate-presets` command on older versions of Laravel. [#3511](https://github.com/statamic/cms/issues/3511)
- Fix error wen `CarbonImmutable` is used app-wide. [#3499](https://github.com/statamic/cms/issues/3499)
- Fix 404 error when URLs have both ending slash and query parameters. [#3494](https://github.com/statamic/cms/issues/3494)
- Fix NaN and other glitches in the `time` fieldtype. [#3496](https://github.com/statamic/cms/issues/3496)


## 3.1.5 (2021-04-07)

### What's new
- The Bard link toolbar allows you to browse for entries. [#3466](https://github.com/statamic/cms/issues/3466)
- Added a `queue` option to the `assets:generate-presets` command. [#3490](https://github.com/statamic/cms/issues/3490)
- The `cache` tag supports cache tags. (Naming is hard.) [#3357](https://github.com/statamic/cms/issues/3357)
- Add status UI for text nav items. [#3489](https://github.com/statamic/cms/issues/3489)

### What's fixed
- Fix SVG dimensions when not using pixels. [#3482](https://github.com/statamic/cms/issues/3482)
- Prevent the 'read only' label and translation icons on `section` fieldtypes. [#3492](https://github.com/statamic/cms/issues/3492)
- Prevent incorrect nav output when you had a nav named the same as a collection. [#3491](https://github.com/statamic/cms/issues/3491)



## 3.1.4 (2021-04-06)

### What's new
- Ability to push queries and middleware into GraphQL. [#3385](https://github.com/statamic/cms/issues/3385)
- Add breadcrumbs to asset browser. [#3475](https://github.com/statamic/cms/issues/3475)
- Add limit param to foreach tag. [fc034eec1](https://github.com/statamic/cms/commit/fc034eec1)

### What's fixed
- Fix squished sidebar toggle. [#3456](https://github.com/statamic/cms/issues/3456)
- Prevent unintended deletion of assets through editor. [#3474](https://github.com/statamic/cms/issues/3474)
- Fix autofocus issues in Safari and Firefox. [#3471](https://github.com/statamic/cms/issues/3471)
- Handle encoded characters in uploaded asset filenames. [#3473](https://github.com/statamic/cms/issues/3473)
- Fix Glide 404ing for images in the `public` directory. [#3484](https://github.com/statamic/cms/issues/3484)
- Fix assets being incorrect every other request in some cases. [#3485](https://github.com/statamic/cms/issues/3485)
- Use request helper instead of server variables to fix an issue with Laravel Octane. [#3483](https://github.com/statamic/cms/issues/3483)



## 3.1.3 (2021-04-02)

### What's new
- Status icons are shown in collections' tree views. [#3461](https://github.com/statamic/cms/issues/3461)
- Addons can add external stylesheets. [#3464](https://github.com/statamic/cms/issues/3464)
- Added a `honeypot` variable inside forms. [#3462](https://github.com/statamic/cms/issues/3462)

### What's fixed
- Glide routes will return 404s for non-existent images. [#3450](https://github.com/statamic/cms/issues/3450)
- Recognize tag pairs correctly for a collection alias. [#3457](https://github.com/statamic/cms/issues/3457)
- Fix utf8 handling of base64 encoded strings. [#3421](https://github.com/statamic/cms/issues/3421)
- Fix `markdown` modifier not working with the `code` fieldtype. [#3460](https://github.com/statamic/cms/issues/3460)
- Allow `symfony/var-exporter` 5.1. [#3463](https://github.com/statamic/cms/issues/3463)
- Bump y18n from 4.0.0 to 4.0.1. [#3443](https://github.com/statamic/cms/issues/3443)



## 3.1.2 (2021-03-30)

### What's improved
- Prevent the need to hit enter to add a validation rule. [bdf9e03a5](https://github.com/statamic/cms/commit/bdf9e03a5)
- Updated German translations. [#3434](https://github.com/statamic/cms/issues/3434)

### What's fixed
- Fix taxonomies url and data handling which fixes a `nav:breadcrumbs` issue. [#3448](https://github.com/statamic/cms/issues/3448)
- Fix "move asset" action not listing all folders. [#3447](https://github.com/statamic/cms/issues/3447)
- Prevent action and glide routes being disabled by config. [#3446](https://github.com/statamic/cms/issues/3446)
- Prevent error during addon tests. [#3435](https://github.com/statamic/cms/issues/3435)



## 3.1.1 (2021-03-25)

### What's improved
- French translations. [#3429](https://github.com/statamic/cms/issues/3429)

### What's fixed
- Fix widths for certain fieldtypes within Grid tables. [#3426](https://github.com/statamic/cms/issues/3426)
- Fix update issue when a nav doesn't have a tree. [#3430](https://github.com/statamic/cms/issues/3430)
- Fix link color inside updater. [#3423](https://github.com/statamic/cms/issues/3423)
- Fix translation typo [#3428](https://github.com/statamic/cms/issues/3428)
- Fix date fieldtypes not displaying. [#3422](https://github.com/statamic/cms/issues/3422)
- Fix issue where the delete action wouldn't show, or would show twice. [#3420](https://github.com/statamic/cms/issues/3420)
- Prevent error on `/cp/auth` when logged in. [#3425](https://github.com/statamic/cms/issues/3425)
- Don't check for composer scripts during tests. [#3427](https://github.com/statamic/cms/issues/3427)



## 3.1.0 (2021-03-24)

### What's new
- Official 3.1 release. 🎉



## 3.1.0-beta.3 (2021-03-24)

### What's new
- `form:create` action and method params. [#3411](https://github.com/statamic/cms/issues/3411)

### What's fixed
- Redirect to CP after CP-based user activation. [5e2ff7df7](https://github.com/statamic/cms/commit/5e2ff7df7)
- Allow grid tables to dynamically use the most appropriate space. [12529a8bf](https://github.com/statamic/cms/commit/12529a8bf)
- Preprocess default values in Bard, Grid and Replicator preload methods. [#3235](https://github.com/statamic/cms/issues/3235)
- Bumped `laravel/framework` requirement to versions with security patches. [#3416](https://github.com/statamic/cms/issues/3416)
- Changes from 3.0.49



## 3.1.0-beta.2 (2021-03-22)

### What's new
- Added option to set a custom path to git binary. [#3393](https://github.com/statamic/cms/issues/3393)
- Added `ArrayableString` class, and apply to the `code` fieldtype. [#3347](https://github.com/statamic/cms/issues/3347)
- Added support for `date` input type on `text` fieldtype. [39323eab4](https://github.com/statamic/cms/commit/39323eab4)
- Added ability to set HTML attributes on `NavItem`s. [#3386](https://github.com/statamic/cms/issues/3386)

### What's improved
- More asset performance improvements. [#3409](https://github.com/statamic/cms/issues/3409)
- Redesign the updater widget. [3b8538814](https://github.com/statamic/cms/commit/3b8538814)
- Set widget heights to full for a more pleasing experience. [d7b55bd47](https://github.com/statamic/cms/commit/d7b55bd47)
- Display toggle fieldtypes inline when in sidebar. [a521286ea](https://github.com/statamic/cms/commit/a521286ea)
- Don't show error templates in the template fieldtype. [da84894de](https://github.com/statamic/cms/commit/da84894de)
- When a Replicator has a single set, the add button will not show the set selector. [68722c23a](https://github.com/statamic/cms/commit/68722c23a)
- Added an icon to the collection widget. [28e2290a0](https://github.com/statamic/cms/commit/28e2290a0)

### What's fixed
- Fix custom logo when using arrays with null. [#3408](https://github.com/statamic/cms/issues/3408)
- Fix `trans_choice()` pluralization. [#3405](https://github.com/statamic/cms/issues/3405)
- Fix broadcasting error if you have your routes cached. [#3395](https://github.com/statamic/cms/issues/3395)
- Prevent delete action showing outside of core listings. [8a4d84fc](https://github.com/statamic/cms/commit/8a4d84fc)
- Brought over changes from 3.0.48



## 3.1.0-beta.1 (2021-03-15)

### What's new
- You can configure Statamic to use separate authentication from the rest of your app. [#3143](https://github.com/statamic/cms/issues/3143)
- Added support for the `mimetypes` validation rule. [#3290](https://github.com/statamic/cms/issues/3290)

### What's improved
- A whole bunch of Amazon S3 performance optimization. [#3369](https://github.com/statamic/cms/issues/3369) [#3353](https://github.com/statamic/cms/issues/3353) [#3354](https://github.com/statamic/cms/issues/3354) [#3359](https://github.com/statamic/cms/issues/3359) [#3362](https://github.com/statamic/cms/issues/3362)
- The `mimes` and `image` validation rules now use the actual mime type rather than just the extension. [#3290](https://github.com/statamic/cms/issues/3290)
- SVG assets can provide their dimensions. [#2865](https://github.com/statamic/cms/issues/2865)

### What's fixed
- GraphQL will filter out draft entries from the entries query by default. [#3349](https://github.com/statamic/cms/issues/3349)
- Fix an error when there's missing asset metadata. It's now lazily loaded. [#3280](https://github.com/statamic/cms/issues/3280)
- Brought over changes from 3.0.47



## 3.1.0-alpha.4 (2021-03-08)

### What's new
- Collection and Nav Trees are now stored separately from their config. [#2768](https://github.com/statamic/cms/issues/2768)
- Added configuration to make REST API resources opt-in. [#3318](https://github.com/statamic/cms/issues/3318)
- Added a form endpoint to the REST API. [#3271](https://github.com/statamic/cms/issues/3271)
- You can disable paste and input rules on Bard fields. [e23f2103](https://github.com/statamic/cms/commit/e23f2103)
- You can add placeholder text to `textarea` fieldtypes. [dc8fb06f](https://github.com/statamic/cms/commit/dc8fb06f)

### What's fixed
- The REST API will filter out draft entries by default. [#3317](https://github.com/statamic/cms/issues/3317)
- Full measure static caching no longer logs when creating the page. [#3255](https://github.com/statamic/cms/issues/3255)
- Form fieldtypes now show data in the API rather than an empty object. [#3182](https://github.com/statamic/cms/issues/3182)
- Removed the minimum character limit for search queries. [4327e68c](https://github.com/statamic/cms/commit/4327e68c)
- Added the missing jpeg file type icon. [0c019840](https://github.com/statamic/cms/commit/0c019840)
- Update scripts and lock file class will normalize versions even more normalized. [#3335](https://github.com/statamic/cms/issues/3335)
- Brought over changes from 3.0.44-46

### What's changing
- A bunch of structure tree related things outlined in [#2768](https://github.com/statamic/cms/issues/2768)
- A `hasCachedPage` method has been added to the `Statamic\StaticCaching\Cacher` interface.
- GraphQL queries are all disabled by default. [#3289](https://github.com/statamic/cms/issues/3289)
- Global search is now only triggered with a slash. (Not ctrl/alt/shift+f) [cad87068](https://github.com/statamic/cms/commit/cad87068)
- Since REST API resources are now opt-in, everything will 404 until you update your config. [#3318](https://github.com/statamic/cms/issues/3318)



## 3.1.0-alpha.3 (2021-02-11)

### What's new
- Add site and locale to entries. [#3205](https://github.com/statamic/cms/issues/3205)
- Date fields in range mode can be queried in GraphQL. [#3223](https://github.com/statamic/cms/issues/3223)

### What's fixed
- Support separate logos for outside/inside. [cad7451e](https://github.com/statamic/cms/commit/cad7451e)
- Fix date fields not augmenting ranges. [#3223](https://github.com/statamic/cms/issues/3223)
- Brought over changes from 3.0.43

### What's changing
- The `@svg` directive has been renamed to `@cp_svg` to avoid potential conflicts. [#3186](https://github.com/statamic/cms/issues/3186)



## 3.1.0-alpha.2 (2021-02-04)

### What's new
- Ability to query an entry by slug or URI in GraphQL. [#3193](https://github.com/statamic/cms/issues/3193)

### What's fixed
- Fixed GraphQL nested subfield handling for Replicator, Bard, and Grid fields. [#3202](https://github.com/statamic/cms/issues/3202)
- Fixed Safari display issue. [#1999](https://github.com/statamic/cms/issues/1999)
- Brought over changes from 3.0.41-42



## 3.1.0-alpha.1 (2021-02-01)

### What's new
- GraphQL [#2982](https://github.com/statamic/cms/issues/2982)
- White labeling [#3013](https://github.com/statamic/cms/issues/3013)
- Update Scripts [#3024](https://github.com/statamic/cms/issues/3024)
- API Caching [#3168](https://github.com/statamic/cms/issues/3168)
- Nav and Collection structure tree API endpoints [#2999](https://github.com/statamic/cms/issues/2999)
- Entry author permissions [#3053](https://github.com/statamic/cms/issues/3053)

### What's changing
- The `date` fieldtype now augments to Carbon instances. If you use them in Antlers without any modifiers, they will now be output using the default `date_format` (e.g. January 1st, 2020). Previously, the raw value (e.g. 2020-01-02) would have been output. Actual entry dates (i.e. the `date` field) would have behaved this way already. If you were using a modifier (e.g. `format`), there will be no change.



## 3.0.49 (2021-03-24)

### What's new
- Add markdown option to render form emails. [#3414](https://github.com/statamic/cms/issues/3414)

### What's fixed
- Widont adds spaces for all paragraphs, and fixed up the modifier parameter. [#3303](https://github.com/statamic/cms/issues/3303)
- Vertically align fieldtypes in a grid. [#3387](https://github.com/statamic/cms/issues/3387)
- Bump elliptic from 6.5.3 to 6.5.4. [#3352](https://github.com/statamic/cms/issues/3352)



## 3.0.48 (2021-03-22)

### What's new
- The Git integration can use a custom queue connection. [#3305](https://github.com/statamic/cms/issues/3305)

### What's improved
- The Stache watcher now uses an environment variable by default. [#3403](https://github.com/statamic/cms/issues/3403)

### What's fixed
- Fix `markdown` modifier not using custom parser. [#3373](https://github.com/statamic/cms/issues/3373)
- Fix issue where the `nav` tag would incorrect label urls as external. [#3401](https://github.com/statamic/cms/issues/3401)
- Assets default their `focus` and `focus_css` values to `50-50-1`. [#3340](https://github.com/statamic/cms/issues/3340)
- Fix wrong Closure typehint. [#3375](https://github.com/statamic/cms/issues/3375)



## 3.0.47 (2021-03-15)

### What's new
- Added a `route` param to `redirect` tag. [#3308](https://github.com/statamic/cms/issues/3308)
- Added a "double encode" option to the `sanizite` modifier. [#3067](https://github.com/statamic/cms/issues/3067)

### What's fixed
- Fix sorting on aliased entries. [#3363](https://github.com/statamic/cms/issues/3363)
- Fix default entry blueprint when hiding some of them. [#3368](https://github.com/statamic/cms/issues/3368)
- Fix error when using SVGs in Glide tag pairs. [#3366](https://github.com/statamic/cms/issues/3366)
- Fix JS error when field condition would result in an unevaluatable string. [#3366](https://github.com/statamic/cms/issues/3366)
- Fix CP index dates in range-mode. [#3306](https://github.com/statamic/cms/issues/3306)
- Removed unused dependencies in Fieldset and Blueprint repositories. [#3307](https://github.com/statamic/cms/issues/3307)



## 3.0.46 (2021-03-05)

### What's new
- You can get a user's email via a property. [#3331](https://github.com/statamic/cms/issues/3331)

### What's fixed
- Fix range field overflowing issue. [#3292](https://github.com/statamic/cms/issues/3292)
- Show valid data for a Form fields in the content API. [#3270](https://github.com/statamic/cms/issues/3270)
- Enable fixed toolbar on a Bard field inside a set. [#3240](https://github.com/statamic/cms/issues/3240)



## 3.0.45 (2021-02-22)

### What's new
- Add new `chunk` modifier. [849ae0ccb](https://github.com/statamic/cms/commit/849ae0ccb)
- Support `image` and `mimes` validation rules for assets. [#3253](https://github.com/statamic/cms/issues/3253)
- Parameters can now access:nested:variables. [#3267](https://github.com/statamic/cms/issues/3267)
- Added syringe icon. [#3232](https://github.com/statamic/cms/issues/3232)

### What's improved
- Improve Spanish translations. [#3243](https://github.com/statamic/cms/issues/3243)

### What's fixed
- Fix error when attempting to filter a collection by a single taxonomy. [#3244](https://github.com/statamic/cms/issues/3244)
- Prevent deleting `select` field selections when in read only. [#3283](https://github.com/statamic/cms/issues/3283)
- Preserve numeric keys in the `array` fieldtype. [#3284](https://github.com/statamic/cms/issues/3284)
- Localize the taxonomy `terms` field. [#3172](https://github.com/statamic/cms/issues/3172)
- Persist the `parent` when using the create another entry button. [#3285](https://github.com/statamic/cms/issues/3285)
- Fix disabled `select` field styling issues. [#3275](https://github.com/statamic/cms/issues/3275)
- Prevent excessive user database queries. [#3227](https://github.com/statamic/cms/issues/3227)
- Handle null labels correctly in the `array` fieldtype. [#3260](https://github.com/statamic/cms/issues/3260)
- Keep text field width within limits in Firefox. [#3258](https://github.com/statamic/cms/issues/3258)
- Fix type error in `repeat` modifier. [#3261](https://github.com/statamic/cms/issues/3261)
- The `date` fieldtype in a listing uses use the `date_format` setting. [#3264](https://github.com/statamic/cms/issues/3264)
- Use `date_format` in updater changelogs. [#3246](https://github.com/statamic/cms/issues/3246)



## 3.0.44 (2021-02-17)

### What's fixed
- Allow `view` data to be passed into tags parameters. [#3252](https://github.com/statamic/cms/issues/3252)
- Fix error when submitting a form with emails. [#3239](https://github.com/statamic/cms/issues/3239)



## 3.0.43 (2021-02-11)

### What's new
- Added an `EntryCreated` event. [#3078](https://github.com/statamic/cms/issues/3078)
- Ability to save entries without triggering events. [#3208](https://github.com/statamic/cms/issues/3208)
- Add `sort` and `query_scope` parameters to `search:results` tag. [#2383](https://github.com/statamic/cms/issues/2383)
- Ability to disable focal point editor. [#3160](https://github.com/statamic/cms/issues/3160)

### What's improved
- Added Chinese translations. [#3211](https://github.com/statamic/cms/issues/3211)
- Updated French translations. [#3206](https://github.com/statamic/cms/issues/3206)

### What's fixed
- Fix Radio input position. [#3183](https://github.com/statamic/cms/issues/3183)
- Fix Antlers ternary condition escaping. [#3123](https://github.com/statamic/cms/issues/3123)
- Prevent terms being created with existing slugs, which prevents overriding existing terms. [#3114](https://github.com/statamic/cms/issues/3114)
- The "Visit URL" button gets hidden when a collection has no route. [#3080](https://github.com/statamic/cms/issues/3080)
- Fix stroke color of the taxonomy icon. [#3225](https://github.com/statamic/cms/issues/3225)
- Fix issue where date range fields would sometimes be a day behind. [#3221](https://github.com/statamic/cms/issues/3221)
- Prevent error when a user's avatar is deleted. [#3212](https://github.com/statamic/cms/issues/3212)
- Use more data when augmenting a form submission, which prevents the wrong date being shown. [#3204](https://github.com/statamic/cms/issues/3204)



## 3.0.42 (2021-02-04)

### What's fixed
- Fix error in asset listings when one has recently been deleted. [#3201](https://github.com/statamic/cms/issues/3201)
- Fix Taxonomy facade accessor. [#3199](https://github.com/statamic/cms/issues/3199)
- Small clean up of `trans` tag. [#3197](https://github.com/statamic/cms/issues/3197)



## 3.0.41 (2021-02-03)

### What's new
- Added a `ray` modifier. [#3137](https://github.com/statamic/cms/issues/3137)

### What's improved
- Form email subjects can be translated. [#3144](https://github.com/statamic/cms/issues/3144)
- View site button in CP uses the selected site. [#3139](https://github.com/statamic/cms/issues/3139)
- Updated Danish, German, and French translations. [#3161](https://github.com/statamic/cms/issues/3161) [#3134](https://github.com/statamic/cms/issues/3134) [#3129](https://github.com/statamic/cms/issues/3129)

### What's fixed
- Prevent moving pages to end of top level when already there. [#3152](https://github.com/statamic/cms/issues/3152)
- Fix form widget styling. [#3169](https://github.com/statamic/cms/issues/3169)
- Fix Bard line wrapping issue. [#3115](https://github.com/statamic/cms/issues/3115)
- Inject the Symfony Yaml component. [#3164](https://github.com/statamic/cms/issues/3164)
- Adjust Action Facade docblock [#3150](https://github.com/statamic/cms/issues/3150)



## 3.0.40 (2021-01-21)

### What's fixed
- Fix error when saving a root page. [#3132](https://github.com/statamic/cms/issues/3132)



## 3.0.39 (2021-01-19)

### What's improved
- Fixed a handful of translation issues. [#2511](https://github.com/statamic/cms/issues/2511) [#2520](https://github.com/statamic/cms/issues/2520) [#2515](https://github.com/statamic/cms/issues/2515) [#2510](https://github.com/statamic/cms/issues/2510) [#2509](https://github.com/statamic/cms/issues/2509) [#2641](https://github.com/statamic/cms/issues/2641) [#2514](https://github.com/statamic/cms/issues/2514) [#3119](https://github.com/statamic/cms/issues/3119)
- The `multisite` command will enable pro and update your config file for you. [#3125](https://github.com/statamic/cms/issues/3125)

### What's fixed
- Fix error in the `multisite` command. [#3125](https://github.com/statamic/cms/issues/3125)
- Fix table fieldtype duplicating data. [#2470](https://github.com/statamic/cms/issues/2470)
- Fix table fieldtype not showing delete row button. [#2790](https://github.com/statamic/cms/issues/2790)
- Fix entries etc not being removed from search index when deleted. [#3121](https://github.com/statamic/cms/issues/3121)
- Fix API URL related error when using Live Preview while creating an entry. [#3112](https://github.com/statamic/cms/issues/3112)
- Fix time being added to the date fieldtype unnecessarily. [#3118](https://github.com/statamic/cms/issues/3118)
- Prevent null values from being saved in Bard and Replicator fields. [#3126](https://github.com/statamic/cms/issues/3126)
- Prevent a situation where you could move a page into a child of the root, which isn't allowed. [#3104](https://github.com/statamic/cms/issues/3104)
- Prevent orderable collections from having a parent field. [#2012](https://github.com/statamic/cms/issues/2012)
- Removed route model binding for users. [#3088](https://github.com/statamic/cms/issues/3088)
- Fix 404s within the CP rendering as front-end 404s. [#3098](https://github.com/statamic/cms/issues/3098)



## 3.0.38 (2021-01-11)

### What's new
- Added a horizontal rule button to Bard. [#3076](https://github.com/statamic/cms/issues/3076)
- Ability to choose from multiple blueprints on the empty collection screen. [#1985](https://github.com/statamic/cms/issues/1985)
- You can now edit a blueprint section's or Bard set's handle separately from the display text. [#1667](https://github.com/statamic/cms/issues/1667)
- Addons can more easily register actions, scopes, and filters. [#3093](https://github.com/statamic/cms/issues/3093)

### What's improved
- Updated French and Dutch translations. [#3077](https://github.com/statamic/cms/issues/3077) [#3086](https://github.com/statamic/cms/issues/3086)

### What's fixed
- Fix Bard issue where using bold inside a link would split the link up. [#2109](https://github.com/statamic/cms/issues/2109)
- Fix Bard issue where an empty paragraph is added before a newly added set. [#1491](https://github.com/statamic/cms/issues/1491)
- Prevent editing and removing assets from the assets fieldtype when it's read only. [#1826](https://github.com/statamic/cms/issues/1826)
- Half measure static caching uses the correct expiry key as per the docs. [#2744](https://github.com/statamic/cms/issues/2744)
- Fixed an issue where a statically cached page would get unintentionally re-cached. [#3085](https://github.com/statamic/cms/issues/3085)
- Fix date handling when using revisions. [#3094](https://github.com/statamic/cms/issues/3094)



## 3.0.37 (2021-01-06)

### What's new
- Added a `CollectionCreated` event. [#3062](https://github.com/statamic/cms/issues/3062)
- Added a `UserRegistering` event. [#3057](https://github.com/statamic/cms/issues/3057)
- Added a `float` fieldtype. [#3060](https://github.com/statamic/cms/issues/3060)

### What's improved
- You now get a confirmation before updating or downgrading Statamic and addons. [#3038](https://github.com/statamic/cms/issues/3038)

### What's fixed
- Fixed entry publish state management permissions. [#3039](https://github.com/statamic/cms/issues/3039)
- Query strings can be ignored when using static caching. [#3075](https://github.com/statamic/cms/issues/3075)
- Bump `axios` from 0.19.2 to 0.21.1 [#3068](https://github.com/statamic/cms/issues/3068)



## 3.0.36 (2020-12-23)

### What's new
- Added a `mount` variable to entries in templates. [#3046](https://github.com/statamic/cms/issues/3046)
- Added a `locales:count` tag. [#3042](https://github.com/statamic/cms/issues/3042)

### What's improved
- Hide the "Enable Pro" part of the Getting Started widget if it's enabled. [#3051](https://github.com/statamic/cms/issues/3051)
- Updated French and German translations. [#3029](https://github.com/statamic/cms/issues/3029) [#3052](https://github.com/statamic/cms/issues/3052)
- Improved the Asset SVG asset previews. [#2945](https://github.com/statamic/cms/issues/2945)

### What's fixed
- Fix issue where you couldn't drag Bard sets when used inside a Replicator. [#2063](https://github.com/statamic/cms/issues/2063)
- The 'Add Date' button is unavailable in the date fieldtype when it's read only. [#3025](https://github.com/statamic/cms/issues/3025)
- Fix issue where a non existent avatar sometimes caused an error. [#3027](https://github.com/statamic/cms/issues/3027)
- Show a dropdown indicator when there's more than one taxonomy blueprint. [#3010](https://github.com/statamic/cms/issues/3010)
- Fix btn class selector clash. [#3022](https://github.com/statamic/cms/issues/3022)
- Updating through the CP will also update dependencies, fixing an issue where people were stuck on 3.0.12. [#3045](https://github.com/statamic/cms/issues/3045)
- Prevent Replicator sets shrinking when dragging them. [9dedf49b3](https://github.com/statamic/cms/commit/9dedf49b3)
- Fix issue where you couldn't un-hide a blueprint. [#3033](https://github.com/statamic/cms/issues/3033)



## 3.0.35 (2020-12-17)

### What's new
- Blueprints can be hidden from the Create Entry and Create Term buttons. [#3007](https://github.com/statamic/cms/issues/3007)
- Added a `UserBlueprintFound` event. [#2983](https://github.com/statamic/cms/issues/2983)

### What's fixed
- Fixed a circular reference which made Bard freeze the page. [#2959](https://github.com/statamic/cms/issues/2959) [#3005](https://github.com/statamic/cms/issues/3005)
- Register our custom cache driver earlier, which fixes compatibility with Laravel Telescope. [#3023](https://github.com/statamic/cms/issues/3023) [#1721](https://github.com/statamic/cms/issues/1721)
- The Toggle fieldtype gives you a boolean when undefined, rather than null. [1f11c9c89](https://github.com/statamic/cms/commit/1f11c9c89)
[05601e49b](https://github.com/statamic/cms/commit/05601e49b)
- Terms can contain supplemental data, which fixes error within search. [#3008](https://github.com/statamic/cms/issues/3008)
- Fix `isInGroup` for Eloquent user driver. [#2951](https://github.com/statamic/cms/issues/2951)
- Fix issue where only one term would be returned when you have two terms with the same slug in different taxonomies. [c9624a49e](https://github.com/statamic/cms/commit/c9624a49e)
- Hide the "Duplicate Row" button when max grid rows have been reached. [#3006](https://github.com/statamic/cms/issues/3006)
- Removed the zero indexed grid item count. [b657efa28](https://github.com/statamic/cms/commit/b657efa28)
- Fix `join` modifier when value is null. [#3001](https://github.com/statamic/cms/issues/3001)
- Fix Term facade hints. [#3012](https://github.com/statamic/cms/issues/3012)
- Bump `ini` from 1.3.5 to 1.3.8 [#3009](https://github.com/statamic/cms/issues/3009)



## 3.0.34 (2020-12-09)

### What's new
- PHP 8 support. [#2944](https://github.com/statamic/cms/issues/2944)

### What's fixed
- Use the correct password reset url in emails when using the `user:forgot_password_form`. [#2988](https://github.com/statamic/cms/issues/2988)
- Passing an invalid `from` value to a `nav` tag will output from the root, rather than throw an error. [#2963](https://github.com/statamic/cms/issues/2963)



## 3.0.33 (2020-12-08)

### What's improved
- Allow collection specific taxonomy views to work without mounting (when you have a single word collection). [352772eaa](https://github.com/statamic/cms/commit/352772eaa)
- Updated German translation. [#2968](https://github.com/statamic/cms/issues/2968)

### What's fixed
- Fixed an issue where you couldn't re-select an asset after removing one. [844e3710d](https://github.com/statamic/cms/commit/844e3710d)
- Prevent terms being excluded from search results by giving them a published status. [#2950](https://github.com/statamic/cms/issues/2950)
- Fix the "View" dropdown link on the taxonomy term listing page. [e26a1ad5f](https://github.com/statamic/cms/commit/e26a1ad5f)
- Fix terms not having the collection scoped URLs on the collection specific listing page. [175783dc6](https://github.com/statamic/cms/commit/175783dc6)
- Fix a paginator related error when using Laravel 6. [6ade2a61c](https://github.com/statamic/cms/commit/6ade2a61c)
- Fixed an issue where colon delimited strings in Antlers conditions weren't parsed correctly. [#2396](https://github.com/statamic/cms/issues/2396)
- Fix breadcrumbs not rendering properly when not including home [#2976](https://github.com/statamic/cms/issues/2976)
- Fix error for an empty search string [#2974](https://github.com/statamic/cms/issues/2974)
- Fix error when paginating using Eloquent. [7f4fd19ea](https://github.com/statamic/cms/commit/7f4fd19ea)



## 3.0.32 (2020-12-02)

### What's new
- Added `term.saved` and `term.saving` hooks. [016306639](https://github.com/statamic/cms/commit/016306639) [8c3320d20](https://github.com/statamic/cms/commit/8c3320d20)

### What's improved
- Added `hidden` to the `text` fieldtype's `input_type` dropdown. [#2952](https://github.com/statamic/cms/issues/2952)
- Improved visual spacing when adding Replicator blocks. [#2955](https://github.com/statamic/cms/issues/2955)
- Updated French translations. [#2870](https://github.com/statamic/cms/issues/2870)

### What's fixed
- Reverted the `highlight.js` and `tiptap-extensions` upgrades from 3.0.31. Fixes a Prosemirror error. [#2919](https://github.com/statamic/cms/issues/2919)
- Fix users not being able to change their own passwords. [6fec3bace](https://github.com/statamic/cms/commit/6fec3bace)
- Fix users not being able to reset their passwords when using Eloquent. [#2795](https://github.com/statamic/cms/issues/2795)
- Fix an unnecessary alert after saving a term. [#2930](https://github.com/statamic/cms/issues/2930)
- Prevent the `statamic:install` command trying to creating `.gitkeep` files at the wrong place. [#2939](https://github.com/statamic/cms/issues/2939)



## 3.0.31 (2020-11-25)

### What's new
- Added an `assets:generate-presets` command. [2909](https://github.com/statamic/cms/commit/2909)
- CP Nav items can use their own SVGs. [#2890](https://github.com/statamic/cms/issues/2890)

### What's improved
- The Select fieldtype's Replicator preview text uses labels. [#2913](https://github.com/statamic/cms/issues/2913)
- When using Eloquent based users, prevent updating timestamps when logging in. [f7d242e5c](https://github.com/statamic/cms/commit/f7d242e5c)
- Added an Antlers toggle to the config of text fieldtypes. [#2891](https://github.com/statamic/cms/issues/2891)
- The `.gitkeep` files generated by the `install` command use directories defined in the config. [#2888](https://github.com/statamic/cms/issues/2888)
- Updated translations. [#2896](https://github.com/statamic/cms/issues/2896) [2ef2fda9c](https://github.com/statamic/cms/commit/2ef2fda9c)

### What's fixed
- Fixed non-string IDs (like integers, when using Eloquent) within the Entries fieldtype. [#2900](https://github.com/statamic/cms/issues/2900)
- Fixed error when attempting to filter entries by a null taxonomy term. [#2904](https://github.com/statamic/cms/issues/2904) [#2912](https://github.com/statamic/cms/issues/2912)
- Upgraded `highlight.js` and `tiptap-extensions`. [b74c61e05](https://github.com/statamic/cms/commit/b74c61e05)
- Fix error when a `terms` fieldtype is used within a User. [6e04a0878](https://github.com/statamic/cms/commit/6e04a0878) [#2826](https://github.com/statamic/cms/issues/2826)
- Fix cmd+s not saving on a navigation. [#2873](https://github.com/statamic/cms/issues/2873)
- Fix OAuth when using Eloquent users. [#2901](https://github.com/statamic/cms/issues/2901)
- Pass in the current blueprint to the 'Create Another' URL. [#2886](https://github.com/statamic/cms/issues/2886)
- UTF8 encode asset name. [#2892](https://github.com/statamic/cms/issues/2892)
- Fixed the `localize` modifier. [7bf579393](https://github.com/statamic/cms/commit/7bf579393)



## 3.0.30 (2020-11-20)

### What's new
- Added a [sites](https://statamic.dev/variables/sites) variable. [#2513](https://github.com/statamic/cms/issues/2513)
- Added the ability to limit number of sets in a Replicator field. [#2866](https://github.com/statamic/cms/issues/2866)
- The search:results tag supports pagination. [d059bc4eb](https://github.com/statamic/cms/commit/d059bc4eb)

### What's fixed
- Prevent comma in submission filenames when used in some locales. [927890a95](https://github.com/statamic/cms/commit/927890a95)
- Prevent form submissions generating new IDs. [#2822](https://github.com/statamic/cms/issues/2822)
- Don't use the "after save" features when inside a Stack. [#2827](https://github.com/statamic/cms/issues/2827) [#2469](https://github.com/statamic/cms/issues/2469)
- Global CP search results are filtered by permission. [#2848](https://github.com/statamic/cms/issues/2848)
- Prevent error when getting image dimensions from a corrupt file. [#2877](https://github.com/statamic/cms/issues/2877)
- Query string is maintained in pagination links in tags. [d059bc4eb](https://github.com/statamic/cms/commit/d059bc4eb)



## 3.0.29 (2020-11-19)

### What's fixed
- Fix issue where nested imports with prefixes causes compounding prefixes. [#2869](https://github.com/statamic/cms/issues/2869)
- Prevent select fields with max_items set to 1 being unclearable. [d04519d2b](https://github.com/statamic/cms/commit/d04519d2b)
- Select fields are searchable if you allow additions, even if you don't explicitly enable the searchable option. [5cba0bc](https://github.com/statamic/cms/commit/5cba0bc)
- Adjust Bard Set Picker placement. [80ff247b2](https://github.com/statamic/cms/commit/80ff247b2)
- Fix styling of pagination's `...` separator. [f0f1cdef6](https://github.com/statamic/cms/commit/f0f1cdef6)
- Support cmd+s to save on Fieldset and Navagition form pages. [b77a8d227](https://github.com/statamic/cms/commit/b77a8d227)
- Fixed that annoying little gap in the main nav when the trial banner isn't there. [d9396a838](https://github.com/statamic/cms/commit/d9396a838)
- Only turn fieldtype length limiter to red when you exceed the limit. Meeting is fine. [0c939faa3](https://github.com/statamic/cms/commit/0c939faa3) [7ce0200f1](https://github.com/statamic/cms/commit/7ce0200f1)
- Fix the `rtfm` command's URL and text. [3185d65e5](https://github.com/statamic/cms/commit/3185d65e5)
- The `length` modifier works with collections. [#2876](https://github.com/statamic/cms/issues/2876)



## 3.0.28 (2020-11-17)

### What's new
- Added a `UserRegistered` event. [#2838](https://github.com/statamic/cms/issues/2838)
- Add config values to the form email data. [#2847](https://github.com/statamic/cms/issues/2847)

### What's improved
- Improved speed of CP entry, term, and form submission listings by only requesting values for visible columns. [#2857](https://github.com/statamic/cms/issues/2857)
- Made some Blueprint related performance improvements. [#2856](https://github.com/statamic/cms/issues/2856)
- Added unique classes based on the handle to each field wrapper div. [statamic/ideas#388](https://github.com/statamic/ideas/issues/388)
- Replaced fzaninotto/faker with fakerphp/faker. [#2819](https://github.com/statamic/cms/issues/2819)

### What's fixed
- Collection widget shows entries for the current site. [adbeaeba5](https://github.com/statamic/cms/commit/adbeaeba5)
- Prevent situations where a structure could end up with a root page with children. [#2852](https://github.com/statamic/cms/issues/2852)
- Render attributes whose value is false. [#2845](https://github.com/statamic/cms/issues/2845)
- Prevent removing/ordering of options on read-only relationship selects. [#2415](https://github.com/statamic/cms/issues/2415)
- Adjusted trial mode banner visibility. [4b83422b9](https://github.com/statamic/cms/commit/4b83422b9)



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
