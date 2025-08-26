# Release Notes

## 6.0.0-alpha.2 (2025-08-26)

### What's new
- CP Theme Config [#12170](https://github.com/statamic/cms/issues/12170) by @jackmcdade
- Add preference for dirty navigation confirmation [#12095](https://github.com/statamic/cms/issues/12095) by @jasonvarga
- Add more icons along with backwards compatibility [#12165](https://github.com/statamic/cms/issues/12165) by @jackmcdade

### What's fixed
- Lock laravel/framework temporarily [#12179](https://github.com/statamic/cms/issues/12179) by @jasonvarga
- Change marketplace link to open in new tab as per icon [#12180](https://github.com/statamic/cms/issues/12180) by @JayGeorge
- Marketplace link should be external [#12182](https://github.com/statamic/cms/issues/12182) by @JayGeorge
- Don't hard code `/cp` in command palette URL [#12178](https://github.com/statamic/cms/issues/12178) by @duncanmcclean
- Match the text color of Bard fields with markdown fields [#12177](https://github.com/statamic/cms/issues/12177) by @JayGeorge
- Bring back asset editor button labels [#12171](https://github.com/statamic/cms/issues/12171) by @jackmcdade
- Fix error when publishing localization [#12155](https://github.com/statamic/cms/issues/12155) by @duncanmcclean
- Fix custom labels on replicator's add set button [#12145](https://github.com/statamic/cms/issues/12145) by @duncanmcclean
- Fix error on listings when query parse  [#12161](https://github.com/statamic/cms/issues/12161) by @duncanmcclean
- Improve read-only/disabled state of Date Fieldtype [#12127](https://github.com/statamic/cms/issues/12127) by @duncanmcclean
- Combobox: Hide chevron icon unless there are options to pick from [#12126](https://github.com/statamic/cms/issues/12126) by @duncanmcclean
- Correctly translate instructions for a ui-field [#12124](https://github.com/statamic/cms/issues/12124) by @martyf
- Fix bard toolbar style regression [#12141](https://github.com/statamic/cms/issues/12141) by @o1y
- Fix set overflow [#12168](https://github.com/statamic/cms/issues/12168) by @jackmcdade
- Fix section overflow [#12166](https://github.com/statamic/cms/issues/12166) by @jackmcdade
- Entries fieldtype: ensure `site` parameter is passed to request [#12160](https://github.com/statamic/cms/issues/12160) by @duncanmcclean
- Fix infinite loop on collection index [#12101](https://github.com/statamic/cms/issues/12101) by @duncanmcclean
- Fix default published state of new nav items [#12159](https://github.com/statamic/cms/issues/12159) by @duncanmcclean
- Add version to `@statamic/cms` package [#12164](https://github.com/statamic/cms/issues/12164) by @duncanmcclean
- Fix badges in bard set handles [#12163](https://github.com/statamic/cms/issues/12163) by @duncanmcclean
- Fix revealer fieldtype's toggle mode [#12128](https://github.com/statamic/cms/issues/12128) by @duncanmcclean
- Fix badges in replicator set headers [#12148](https://github.com/statamic/cms/issues/12148) by @duncanmcclean
- Fix carbon deprecation warning [#12119](https://github.com/statamic/cms/issues/12119) by @jasonvarga
- Fix broken blueprint builder after validation error [#12152](https://github.com/statamic/cms/issues/12152) by @duncanmcclean
- Fix alignment of tab name & chevron in blueprint builder [#12146](https://github.com/statamic/cms/issues/12146) by @duncanmcclean
- Fix error when section is collapsible, but not collapsed by default [#12147](https://github.com/statamic/cms/issues/12147) by @duncanmcclean
- Configurable app shell colors [#12134](https://github.com/statamic/cms/issues/12134) by @jackmcdade
- Fix 2fa recovery codes dark mode [#12133](https://github.com/statamic/cms/issues/12133) by @jackmcdade
- Add nav hover contrast [#12132](https://github.com/statamic/cms/issues/12132) by @jackmcdade
- Fix array fieldtype UI [#12123](https://github.com/statamic/cms/issues/12123) by @jackmcdade
- Relax field configs so they max out at 50% width [#12122](https://github.com/statamic/cms/issues/12122) by @jackmcdade
- Fix bard floating toolbar [#12121](https://github.com/statamic/cms/issues/12121) by @jackmcdade
- Fix dist-package tar location [#12120](https://github.com/statamic/cms/issues/12120) by @jasonvarga
- Selecting an asset when `max_files: 1` should close the stack [#12112](https://github.com/statamic/cms/issues/12112) by @duncanmcclean
- Modal: Fix open state not being emitted to parent component [#12106](https://github.com/statamic/cms/issues/12106) by @duncanmcclean
- Fix "Create" button in relationship fieldtypes [#12113](https://github.com/statamic/cms/issues/12113) by @duncanmcclean
- Allow profile dropdown to grow. [#12118](https://github.com/statamic/cms/issues/12118) by @jackmcdade
- Fix site switcher line wrap. [#12117](https://github.com/statamic/cms/issues/12117) by @jackmcdade
- Make collection cards a little prettier [#12115](https://github.com/statamic/cms/issues/12115) by @jackmcdade
- Fix bard set tooltips [#12111](https://github.com/statamic/cms/issues/12111) by @jackmcdade
- Fix read-only toggles. [#12110](https://github.com/statamic/cms/issues/12110) by @jackmcdade
- Fix the List fieldtype by @jackmcdade
- The sidebar width should be just wide enough to accommodate badges without wrapping [#12102](https://github.com/statamic/cms/issues/12102) by @JayGeorge
- Fix the assets toolbar-padding [#12105](https://github.com/statamic/cms/issues/12105) by @JayGeorge
- Improve accessibility of red color [#12094](https://github.com/statamic/cms/issues/12094) by @JayGeorge
- Input only shows copy button if supported [#12100](https://github.com/statamic/cms/issues/12100) by @jasonvarga
- Fix destructive buttons [#12086](https://github.com/statamic/cms/issues/12086) by @JayGeorge
- Update Duplicate IDs page [#12084](https://github.com/statamic/cms/issues/12084) by @duncanmcclean
- Fix duplicate mobile nav toggles [#12088](https://github.com/statamic/cms/issues/12088) by @duncanmcclean
- Fix missing logo on auth pages [#12087](https://github.com/statamic/cms/issues/12087) by @duncanmcclean
- Prevent combobox input from being autofilled [#12081](https://github.com/statamic/cms/issues/12081) by @duncanmcclean
- Fix Marketplace link on Addons page [#12064](https://github.com/statamic/cms/issues/12064) by @duncanmcclean



## 6.0.0-alpha.1 (2025-08-21)

### What's new
- Redesigned Control Panel
- UI Component library
- Vue 3 [#11339](https://github.com/statamic/cms/pull/11339)
- Command Palette [#11699](https://github.com/statamic/cms/pull/11699)
- Elevated Sessions [#11688](https://github.com/statamic/cms/pull/11688)
- Two Factor Authentication [#11664](https://github.com/statamic/cms/pull/11664)
- Antlers Component Tag syntax [#11799](https://github.com/statamic/cms/pull/11799)
- Addon settings [#11929](https://github.com/statamic/cms/pull/11929)
- Tiptap 3 [#12030](https://github.com/statamic/cms/pull/12030)
- Glide 3 [#11626](https://github.com/statamic/cms/pull/11626)
- Video thumbnails [#11841](https://github.com/statamic/cms/pull/11841)
- Enhanced Asset Browser [#11807](https://github.com/statamic/cms/pull/11807)
- Recursive form:fields tag, group field support, and misc form improvements [#10976](https://github.com/statamic/cms/pull/10976) 

### What's fixed
- Better Timezone support [#11409](https://github.com/statamic/cms/pull/11409)
- Better Date formatting [#11566](https://github.com/statamic/cms/pull/11566)
- Globals content is separate from the config [#11585](https://github.com/statamic/cms/pull/11585)
- Blueprint routing improved to fix nav and breadcrumbs [#11980](https://github.com/statamic/cms/pull/11980)
- Super user authorization check now only applies to Statamic permissions [#11516](https://github.com/statamic/cms/pull/11516)
- Fix ensure field has config on blueprints [#11898](https://github.com/statamic/cms/pull/11898) 
- `logged_in` variable now uses guard from users.php config [#11666](https://github.com/statamic/cms/pull/11666)
- Dropped Carbon 2 support [#11500](https://github.com/statamic/cms/pull/11500)
- Dropped Laravel 10 and PHP 8.1 support [#11440](https://github.com/statamic/cms/pull/11440)
- Dropped underscore in favor of lodash [#11529](https://github.com/statamic/cms/pull/11529) 
- Dropped fuse.js in favor of fuzzysort  [#11713](https://github.com/statamic/cms/pull/11713) 
- Dropped moment.js in favor of native JS date formatting [#11573](https://github.com/statamic/cms/pull/11573)
- Dropped “parent” field from entries publish sidebar [#11506](https://github.com/statamic/cms/pull/11506)
- Dropped Vuex for Pinia [#11446](https://github.com/statamic/cms/pull/11446)
