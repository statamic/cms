# Release Notes

## 6.0.0-alpha.6 (2025-09-08)

### What's fixed
- Bring back entry_count translation [#12364](https://github.com/statamic/cms/issues/12364) by @jasonvarga
- Prevent PSR-4 warnings [#12348](https://github.com/statamic/cms/issues/12348) by @duncanmcclean
- Differentiate dangerous actions in bulk actions toolbar [#12300](https://github.com/statamic/cms/issues/12300) by @duncanmcclean
- Selected assets shouldn't be cleared when editing assets [#12301](https://github.com/statamic/cms/issues/12301) by @duncanmcclean
- Separate the string 'Expand' for better translations [#12357](https://github.com/statamic/cms/issues/12357) by @helloDanuk
- Make active buttons in a group field type look pressed rather than a primary button [#12305](https://github.com/statamic/cms/issues/12305) by @JayGeorge
- Asset field responsiveness [#12303](https://github.com/statamic/cms/issues/12303) by @JayGeorge
- Fix too many backticks in markdown cheatsheet [#12358](https://github.com/statamic/cms/issues/12358) by @duncanmcclean
- Replicator improvements including Bard replicators [#12361](https://github.com/statamic/cms/issues/12361) by @JayGeorge
- Apply Inter more selectively in Bard [#12363](https://github.com/statamic/cms/issues/12363) by @JayGeorge
- Separate the string 'Link' for better translations [#12359](https://github.com/statamic/cms/issues/12359) by @helloDanuk
- Fix missing fieldtype icons [#12304](https://github.com/statamic/cms/issues/12304) by @duncanmcclean
- Dutch translations [#12356](https://github.com/statamic/cms/issues/12356) by @Frank-L93
- Focal Point Editor tweaks [#12302](https://github.com/statamic/cms/issues/12302) by @duncanmcclean
- Remove some unused translations [#12306](https://github.com/statamic/cms/issues/12306) by @helloDanuk
- Fix translation in docs callout [#12311](https://github.com/statamic/cms/issues/12311) by @duncanmcclean
- Fix errors not showing on two factor setup modal [#12313](https://github.com/statamic/cms/issues/12313) by @duncanmcclean
- Adjust the sidebar position for RTL mode [#12335](https://github.com/statamic/cms/issues/12335) by @tao
- Add translations in command palette [#12320](https://github.com/statamic/cms/issues/12320) by @helloDanuk
- Invert text colour of Markdown Cheatsheet in dark mode [#12322](https://github.com/statamic/cms/issues/12322) by @duncanmcclean
- Fix missing translation in Assets & Files fieldtypes [#12325](https://github.com/statamic/cms/issues/12325) by @duncanmcclean
- Allow localization to be selected from ComboBox [#12337](https://github.com/statamic/cms/issues/12337) by @tao
- Take `initialPerPage` into account when rendering widget skeleton [#12326](https://github.com/statamic/cms/issues/12326) by @duncanmcclean
- Border radius fixes for the main containers [#12344](https://github.com/statamic/cms/issues/12344) by @JayGeorge
- Fix translation in Code Fieldtype [#12345](https://github.com/statamic/cms/issues/12345) by @duncanmcclean
- Pass placeholder to search input as a string, not a variable [#12323](https://github.com/statamic/cms/issues/12323) by @duncanmcclean
- Left align table heads e.g. Checkbox field type key/value headings [#12346](https://github.com/statamic/cms/issues/12346) by @JayGeorge
- Make sure field actions do not inherit bottom margin [#12349](https://github.com/statamic/cms/issues/12349) by @JayGeorge
- Checkboxes fieldtype: Option value should be a "label" [#12350](https://github.com/statamic/cms/issues/12350) by @duncanmcclean
- Integer fieldtype: Adjust config field widths [#12351](https://github.com/statamic/cms/issues/12351) by @duncanmcclean
- Popovers shouldn't use fixed widths, to account for localization [#12352](https://github.com/statamic/cms/issues/12352) by @duncanmcclean
- Add translation for "Search..." in German [#12317](https://github.com/statamic/cms/issues/12317) by @duncanmcclean
- Add missing fieldtype titles to translator [#12343](https://github.com/statamic/cms/issues/12343) by @helloDanuk
- Make Base & Large translatable in width field type [#12340](https://github.com/statamic/cms/issues/12340) by @helloDanuk
- Make 'Clear' translatable in Filters.vue [#12331](https://github.com/statamic/cms/issues/12331) by @helloDanuk
- Radio items can now be disabled [#12319](https://github.com/statamic/cms/issues/12319) by @jackmcdade
- Fix German entry count translation [#12296](https://github.com/statamic/cms/issues/12296) by @daun
- Fix Select/Combobox icon [ae5b544fd](https://github.com/statamic/cms/commit/ae5b544fd) by @jackmcdade



## 6.0.0-alpha.5 (2025-09-04)

### What's new
- REST API Authentication [#12051](https://github.com/statamic/cms/issues/12051) by @duncanmcclean
- Bard adjustments [#12240](https://github.com/statamic/cms/issues/12240) by @jasonvarga
- Make `container` available in custom field conditions [#12277](https://github.com/statamic/cms/issues/12277) by @duncanmcclean
- Updated asset and folder permissions [#12281](https://github.com/statamic/cms/issues/12281) by @jasonvarga
- Addon setting helper [#12244](https://github.com/statamic/cms/issues/12244) by @edalzell
- Themeable global header [#12214](https://github.com/statamic/cms/issues/12214) by @jackmcdade
- Redesign trial mode banner [#12239](https://github.com/statamic/cms/issues/12239) by @jackmcdade
- Fleshing out command palette actions [#12140](https://github.com/statamic/cms/issues/12140) by @jesseleite

### What's fixed

- Replicator - change the set picker to be in the center when in between blocks, but left when under the "Add Block" button [4de68783d](https://github.com/statamic/cms/commit/4de68783d) by @JayGeorge
- Respect configured sort column & direction on collection index [#12234](https://github.com/statamic/cms/issues/12234) by @duncanmcclean
- Table fixes for Safari and Firefox [#12233](https://github.com/statamic/cms/issues/12233) by @JayGeorge
- Third-party fieldtypes should be able to use first-party icons [#12291](https://github.com/statamic/cms/issues/12291) by @duncanmcclean
- German translations [#12252](https://github.com/statamic/cms/issues/12252) by @helloDanuk
- Add `cursor-pointer` to replicator set headers & set picker [#12236](https://github.com/statamic/cms/issues/12236) by @duncanmcclean
- Prepend append consistency [#12272](https://github.com/statamic/cms/issues/12272) by @JayGeorge
- Fix errors from BardFieldtype when closing Live Preview [#12275](https://github.com/statamic/cms/issues/12275) by @duncanmcclean
- Replicator legibility improvements [#12278](https://github.com/statamic/cms/issues/12278) by @JayGeorge
- Combobox item separation [#12292](https://github.com/statamic/cms/issues/12292) by @JayGeorge
- Make collapsable sections buttery smooth [#12285](https://github.com/statamic/cms/issues/12285) by @JayGeorge
- Combobox - add a bit of horizontal padding, based on when they say "no options currently" available [eca16ffca](https://github.com/statamic/cms/commit/eca16ffca) by @JayGeorge
- Only render publish section headers when display is set or collapsible is enabled [#12274](https://github.com/statamic/cms/issues/12274) by @JayGeorge
- Add display text to fallback blueprint section [#12280](https://github.com/statamic/cms/issues/12280) by @jasonvarga
- Fix field filter combobox [#12273](https://github.com/statamic/cms/issues/12273) by @duncanmcclean
- Combobox hover contrast [#12287](https://github.com/statamic/cms/issues/12287) by @JayGeorge
- Bard - fix background and border-radius, including for dark mode [cfe217464](https://github.com/statamic/cms/commit/cfe217464) by @JayGeorge
- Cache flattened permissions [#12258](https://github.com/statamic/cms/issues/12258) by @duncanmcclean
- Hide search cancel button in combobox input [#12224](https://github.com/statamic/cms/issues/12224) by @duncanmcclean
- Fix the toast notification position from sometimes breaking [#12271](https://github.com/statamic/cms/issues/12271) by @JayGeorge
- Add a chevron to collapsible sections [#12260](https://github.com/statamic/cms/issues/12260) by @duncanmcclean
- Login custom logo width [#12262](https://github.com/statamic/cms/issues/12262) by @JayGeorge
- Avoid saving theme preference when unauthenticated [#12264](https://github.com/statamic/cms/issues/12264) by @duncanmcclean
- Prevent Live Preview panels from blending into the background [#12269](https://github.com/statamic/cms/issues/12269) by @JayGeorge
- Fix focal point editor [#12259](https://github.com/statamic/cms/issues/12259) by @duncanmcclean
- Tweak distance between field instructions and fields an itsy bitsy amount [9ff62636e](https://github.com/statamic/cms/commit/9ff62636e) by @JayGeorge
- Relationship selector tweaks [#12265](https://github.com/statamic/cms/issues/12265) by @duncanmcclean
- Fix border color in dark mode empty states [#12266](https://github.com/statamic/cms/issues/12266) by @duncanmcclean
- Fix listing translations [#12263](https://github.com/statamic/cms/issues/12263) by @duncanmcclean
- Fix date column in collection widget [#12267](https://github.com/statamic/cms/issues/12267) by @duncanmcclean
- Field type / text - change the way append/prepend are laid out so they're next to each other [4280afc1b](https://github.com/statamic/cms/commit/4280afc1b) by @duncanmcclean
- More UI tweaks [#12248](https://github.com/statamic/cms/issues/12248) by @jackmcdade
- Fix timezone modifier altering original value [#12218](https://github.com/statamic/cms/issues/12218) by @marcorieser
- Grid field type - remove text-gray class because it was affecting things like link buttons, making the button text light [a0874c72d](https://github.com/statamic/cms/commit/a0874c72d) by @JayGeorge
- Grid field type - fix some alignment issues [aaeb4020c](https://github.com/statamic/cms/commit/aaeb4020c) by @JayGeorge
- Grid field type - tidy CSS more [b4cffe8fd](https://github.com/statamic/cms/commit/b4cffe8fd) by @JayGeorge
- Add `resources/dist-package` to `.gitignore` [#12246](https://github.com/statamic/cms/issues/12246) by @duncanmcclean
- Grid field -- a couple of dark mode adjustments [824916a71](https://github.com/statamic/cms/commit/824916a71) by @JayGeorge
- Radio field - improve border legibility [16df2bb5f](https://github.com/statamic/cms/commit/16df2bb5f) by @JayGeorge
- Dictionary field - improve text legibility [842848f93](https://github.com/statamic/cms/commit/842848f93) by @JayGeorge
- Checkboxes - improve border legibility [6d2320e32](https://github.com/statamic/cms/commit/6d2320e32) by @JayGeorge
- Grid field -- improve legibility [ee23bb6e4](https://github.com/statamic/cms/commit/ee23bb6e4) by @JayGeorge
- Remove some container focus states [51b90435f](https://github.com/statamic/cms/commit/51b90435f) by @JayGeorge
- Add fluid publish fields to the top of the Edit Blueprint screen to help layout [a07251738](https://github.com/statamic/cms/commit/a07251738) by @JayGeorge
- Add a modifier class to fluid publish fields that prevents the fields from being fluid on smaller screens to help legibility [c04551e11](https://github.com/statamic/cms/commit/c04551e11) by @JayGeorge
- Minor tweak to UI accent color for code comments to make them more legible [3b7998674](https://github.com/statamic/cms/commit/3b7998674) by @JayGeorge
- Improve `code` presentation for field configuration [1e7ebce33](https://github.com/statamic/cms/commit/1e7ebce33) by @JayGeorge
- Improve code presentation for validation rule lists [4068d526c](https://github.com/statamic/cms/commit/4068d526c) by @JayGeorge
- Remove unnecessary margin from globals publish form [#12235](https://github.com/statamic/cms/issues/12235) by @duncanmcclean
- Date Fieldtype: Prevent dashed border showing in non-read-only state [#12237](https://github.com/statamic/cms/issues/12237) by @duncanmcclean
- Add `:dismissible="false"` to other modal in `SessionExpiry.vue` [#12231](https://github.com/statamic/cms/issues/12231) by @duncanmcclean
- Add/manipulate the UI accent color for code comments [9daa324bb](https://github.com/statamic/cms/commit/9daa324bb) by @JayGeorge
- Prevent session expiry modal from being dismissed [#12223](https://github.com/statamic/cms/issues/12223) by @duncanmcclean
- Clearer WCAG 2.2 Messaging [#12225](https://github.com/statamic/cms/issues/12225) by @
- Improve the gap between text and toggles so that long text (e.g. instructions) don't get too close to the toggle [deea7660e](https://github.com/statamic/cms/commit/deea7660e) by @JayGeorge
- Tweak the switch color values a shade so they're less intense [f89cbd0c0](https://github.com/statamic/cms/commit/f89cbd0c0) by @JayGeorge



## 6.0.0-alpha.4 (2025-08-28)

### What's new
- Configurable progress bar color [#12202](https://github.com/statamic/cms/issues/12202) by @jackmcdade
- UI accent color theme config [#12201](https://github.com/statamic/cms/issues/12201) by @jackmcdade

### What's fixed
- Fix spacer field from collapsing [#12211](https://github.com/statamic/cms/issues/12211) by @JayGeorge
- Lowercase Inter font directory [#12213](https://github.com/statamic/cms/issues/12213) by @jasonvarga
- Remove unnecessary whitespace in bard link toolbar [#12207](https://github.com/statamic/cms/issues/12207) by @duncanmcclean
- Make border colors more consistent [#12209](https://github.com/statamic/cms/issues/12209) by @JayGeorge
- Tidy Vite [#12204](https://github.com/statamic/cms/issues/12204) by @jasonvarga
- Reduce negative letter-spacing for the st-text-legibility legibility (e.g. Bard fields) based [d479c45](https://github.com/statamic/cms/commit/d479c45) by @JayGeorge
- Vite Tailwind Exclusions [#12200](https://github.com/statamic/cms/issues/12200) by @jasonvarga
- UI improvements & fixes [#12203](https://github.com/statamic/cms/issues/12203) by @jackmcdade
- Make panel background slightly darker to improve legibility [2413ae7](https://github.com/statamic/cms/commit/2413ae7) by @JayGeorge
- Fix dark content bg [#12198](https://github.com/statamic/cms/issues/12198) by @jackmcdade
- Switch (Toggle) accents [#12210](https://github.com/statamic/cms/issues/12210) by @jasonvarga
- Automatically run vite build watch [#12193](https://github.com/statamic/cms/issues/12193) by @jasonvarga



## 6.0.0-alpha.3 (2025-08-26)

### What's fixed
- Fix dist tar download error [#12188](https://github.com/statamic/cms/issues/12188) by @jasonvarga
- Fix modal open prop usage [#12187](https://github.com/statamic/cms/issues/12187) by @jasonvarga
- Global header should use chosen gray family [#12185](https://github.com/statamic/cms/issues/12185) by @jackmcdade
- Unlock laravel/framework [#12183](https://github.com/statamic/cms/issues/12183) by @jasonvarga



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
