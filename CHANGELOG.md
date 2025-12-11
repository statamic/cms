# Release Notes

## 6.0.0-alpha.18 (2025-12-03)

### What's new
- Support limit argument in explode modifier [#13089](https://github.com/statamic/cms/issues/13089) by @jacksleight
- Add Actions to Navigations and Global Sets [#13137](https://github.com/statamic/cms/issues/13137) by @o1y
- Storybook [#13116](https://github.com/statamic/cms/issues/13116) by @jasonvarga

### What's fixed
- Underline publish section instruction links [#13065](https://github.com/statamic/cms/issues/13065) by @daun
- Remove collapsed publish section contents from tab order [#13066](https://github.com/statamic/cms/issues/13066) by @daun
- Register `statamic.web` middleware group before booted callbacks [#13074](https://github.com/statamic/cms/issues/13074) by @jacksleight
- Define styles for large icon-only button variant [#13075](https://github.com/statamic/cms/issues/13075) by @daun
- French translations [#13073](https://github.com/statamic/cms/issues/13073) by @ebeauchamps
- German translations [#13070](https://github.com/statamic/cms/issues/13070) by @ebeauchamps
- Increase toggle area of collapsible publish sections [#13068](https://github.com/statamic/cms/issues/13068) by @daun
- Delete duplicate table border radius rule [#13077](https://github.com/statamic/cms/issues/13077) by @JayGeorge
- Asset fixes for the Grid fieldtype [#13058](https://github.com/statamic/cms/issues/13058) by @JayGeorge
- Bump description contrast up one level [#13090](https://github.com/statamic/cms/issues/13090) by @JayGeorge
- Date range improvements [#13088](https://github.com/statamic/cms/issues/13088) by @JayGeorge
- Text wrap balance error messages [#13087](https://github.com/statamic/cms/issues/13087) by @JayGeorge
- Fix Live Preview Pop out [#13083](https://github.com/statamic/cms/issues/13083) by @duncanmcclean
- Fix edit link to additional blueprints [#13086](https://github.com/statamic/cms/issues/13086) by @duncanmcclean
- Description contrast half way house [#13094](https://github.com/statamic/cms/issues/13094) by @JayGeorge
- Increase contrast between pressed and unpressed button variants [#13078](https://github.com/statamic/cms/issues/13078) by @daun
- Fix display of taxonomy preview targets [#13095](https://github.com/statamic/cms/issues/13095) by @duncanmcclean
- Fix missing "Create Folder" button in asset fieldtype selector [#13104](https://github.com/statamic/cms/issues/13104) by @duncanmcclean
- Sort global sets by title [#13103](https://github.com/statamic/cms/issues/13103) by @duncanmcclean
- Asset Replicator preview tweaks [#13107](https://github.com/statamic/cms/issues/13107) by @JayGeorge
- Drop support for Laravel 11 & PHP 8.2 [#13081](https://github.com/statamic/cms/issues/13081) by @duncanmcclean
- Smooth out dashboard widget transitions [#13106](https://github.com/statamic/cms/issues/13106) by @JayGeorge
- Narrow stack aesthetics [#13105](https://github.com/statamic/cms/issues/13105) by @JayGeorge
- Revert standalone UI package [#13118](https://github.com/statamic/cms/issues/13118) by @jasonvarga
- Vitest 4 [#13120](https://github.com/statamic/cms/issues/13120) by @jasonvarga
- JS Browser Tests [#13121](https://github.com/statamic/cms/issues/13121) by @jasonvarga
- Make the Structure fieldtype consistent with other relationship fieldtypes [#13119](https://github.com/statamic/cms/issues/13119) by @JayGeorge
- Fix dark switch border [#13127](https://github.com/statamic/cms/issues/13127) by @JayGeorge
- Make the Time fieldtype icons clearer [#13125](https://github.com/statamic/cms/issues/13125) by @JayGeorge
- tsconfig [#13130](https://github.com/statamic/cms/issues/13130) by @jasonvarga
- Various combobox fixes [#13053](https://github.com/statamic/cms/issues/13053) by @duncanmcclean
- Fix append prepend border in dark mode [#13129](https://github.com/statamic/cms/issues/13129) by @JayGeorge
- Fix separator on video fieldtype [#13128](https://github.com/statamic/cms/issues/13128) by @JayGeorge
- Improve dark mode heading icon color [#13115](https://github.com/statamic/cms/issues/13115) by @JayGeorge
- Vertically center combobox tags [#13132](https://github.com/statamic/cms/issues/13132) by @JayGeorge
- Prevent console warning about prop type in listing search [#13149](https://github.com/statamic/cms/issues/13149) by @duncanmcclean
- Prevent redirect when creating term via fieldtype [#13150](https://github.com/statamic/cms/issues/13150) by @duncanmcclean
- Starting & ending impersonation should trigger full page refreshes [#13148](https://github.com/statamic/cms/issues/13148) by @duncanmcclean
- Refactor how field settings are saved [#13147](https://github.com/statamic/cms/issues/13147) by @duncanmcclean
- Update replicator previews after reordering sets [#13113](https://github.com/statamic/cms/issues/13113) by @duncanmcclean
- Fix field dropdown in field conditions builder [#13143](https://github.com/statamic/cms/issues/13143) by @duncanmcclean
- Remove redundant `authorize` call when deleting a Blueprint [#13164](https://github.com/statamic/cms/issues/13164) by @martyf
- Bring back `$reveal` [#13156](https://github.com/statamic/cms/issues/13156) by @duncanmcclean
- Add file icon extensions [#13175](https://github.com/statamic/cms/issues/13175) by @daun
- Fix clear static cache string [#13193](https://github.com/statamic/cms/issues/13193) by @duncanmcclean
- Show sidebar when `actions` tab is provided [#13180](https://github.com/statamic/cms/issues/13180) by @duncanmcclean
- Only add "Browse the Marketplace" action to command palette for public addons [#13188](https://github.com/statamic/cms/issues/13188) by @duncanmcclean
- Avoid setting active nav item when click event is cancelled [#13185](https://github.com/statamic/cms/issues/13185) by @duncanmcclean
- Fix typo in svg icons [#13161](https://github.com/statamic/cms/issues/13161) by @mikemartin
- Show proper 404 pages instead of plain text [#13192](https://github.com/statamic/cms/issues/13192) by @duncanmcclean
- Fix "Create Entry" button on collection widget [#13082](https://github.com/statamic/cms/issues/13082) by @duncanmcclean
- Enforce 4 digit years [#13189](https://github.com/statamic/cms/issues/13189) by @duncanmcclean
- Improve Time Fieldtype UX [#13209](https://github.com/statamic/cms/issues/13209) by @duncanmcclean
- Allow custom widgets without min-height [#13179](https://github.com/statamic/cms/issues/13179) by @daun
- Remove background from main header icon [#13210](https://github.com/statamic/cms/issues/13210) by @daun
- Make dots consistent [#13203](https://github.com/statamic/cms/issues/13203) by @JayGeorge
- Ensure `savingRef` and `errorsRef` are returned as refs [#13208](https://github.com/statamic/cms/issues/13208) by @duncanmcclean
- Collection widget fixes [#13200](https://github.com/statamic/cms/issues/13200) by @JayGeorge
- Fix globals localization [#13191](https://github.com/statamic/cms/issues/13191) by @duncanmcclean
- Delete Blade `docs-callout` component [#13190](https://github.com/statamic/cms/issues/13190) by @duncanmcclean
- Export `architectural-background` for addons [#13182](https://github.com/statamic/cms/issues/13182) by @duncanmcclean
- Show media dimensions for all filetypes [#13218](https://github.com/statamic/cms/issues/13218) by @daun
- Add build verification step in test workflow [#13220](https://github.com/statamic/cms/issues/13220) by @jasonvarga
- Filter should focus on field after its been selected [#13181](https://github.com/statamic/cms/issues/13181) by @duncanmcclean
- Apply collection widget badge styling fixes to panel footer only [#13222](https://github.com/statamic/cms/issues/13222) by @jasonvarga
- Export Stack as a UI component [#13184](https://github.com/statamic/cms/issues/13184) by @duncanmcclean
- Add width and height columns to asset browser [#13219](https://github.com/statamic/cms/issues/13219) by @daun
- Bard: Avoid debouncing new or deleted nodes [#13167](https://github.com/statamic/cms/issues/13167) by @duncanmcclean
- Throw exception when trying to filter by `status` [#13153](https://github.com/statamic/cms/issues/13153) by @duncanmcclean
- Make array and table fieldtypes more similar [#13174](https://github.com/statamic/cms/issues/13174) by @JayGeorge
- Improve search indexing performance [#13126](https://github.com/statamic/cms/issues/13126) by @duncanmcclean
- Improve search indexing performance pt 2 [#13228](https://github.com/statamic/cms/issues/13228) by @jasonvarga
- Search Updates [#13108](https://github.com/statamic/cms/issues/13108) by @duncanmcclean
- Skip Laravel Herd test failure [#13229](https://github.com/statamic/cms/issues/13229) by @jasonvarga
- Cmd s to close a narrow section [#13100](https://github.com/statamic/cms/issues/13100) by @JayGeorge
- Add collapsible support to Group fieldtype [#13157](https://github.com/statamic/cms/issues/13157) by @martyf
- Fix vite dev for addons [#13241](https://github.com/statamic/cms/issues/13241) by @jasonvarga



## 6.0.0-alpha.17 (2025-11-14)

### What's new
- Add Alpine Precognition form driver [#12995](https://github.com/statamic/cms/issues/12995) by @jacksleight
- Prevent dumping when debug off [#13003](https://github.com/statamic/cms/issues/13003) by @ryanmitchell
- Add methods to Entry/Term Repos [#9815](https://github.com/statamic/cms/issues/9815) by @godismyjudge95
- Make the focus outline color themeable [#13048](https://github.com/statamic/cms/issues/13048) by @JayGeorge

### What's fixed
- Encapsulate button styles in the component itself for portability. [#12966](https://github.com/statamic/cms/issues/12966) by @jackmcdade
- Fix create folder color [#12994](https://github.com/statamic/cms/issues/12994) by @JayGeorge
- Increase combobox text size [#12991](https://github.com/statamic/cms/issues/12991) by @JayGeorge
- Fix live preview device select [#12987](https://github.com/statamic/cms/issues/12987) by @JayGeorge
- Remove nav sidebar shadow when closed [#12983](https://github.com/statamic/cms/issues/12983) by @JayGeorge
- Update pull request template for v6 [#12981](https://github.com/statamic/cms/issues/12981) by @duncanmcclean
- Ensure custom icons with fixed width/height are sized correctly [#12985](https://github.com/statamic/cms/issues/12985) by @duncanmcclean
- Various revision fixes [#12996](https://github.com/statamic/cms/issues/12996) by @duncanmcclean
- Add "Done" button to listing filters stack [#12976](https://github.com/statamic/cms/issues/12976) by @duncanmcclean
- French translations [#12989](https://github.com/statamic/cms/issues/12989) by @ebeauchamps
- Use correct first day of the week in week view [#12988](https://github.com/statamic/cms/issues/12988) by @duncanmcclean
- Tweak text in remove page confirmation modal [#12998](https://github.com/statamic/cms/issues/12998) by @duncanmcclean
- Fix bg architectural lines for dark mode [#12977](https://github.com/statamic/cms/issues/12977) by @JayGeorge
- Add hook for asset warm presets [#12971](https://github.com/statamic/cms/issues/12971) by @godismyjudge95
- Recalculate stack offset when the window is resized [#12999](https://github.com/statamic/cms/issues/12999) by @duncanmcclean
- Fix Bard's floating toolbar in fullscreen mode [#12990](https://github.com/statamic/cms/issues/12990) by @duncanmcclean
- Hide "Your Session is Expiring" modal when 2FA modal is open [#12997](https://github.com/statamic/cms/issues/12997) by @duncanmcclean
- Fix error when creating nav items [#13000](https://github.com/statamic/cms/issues/13000) by @duncanmcclean
- Ensure `hiddenFields` state is correct [#12980](https://github.com/statamic/cms/issues/12980) by @duncanmcclean
- Avoid refreshing Codemirror [#13001](https://github.com/statamic/cms/issues/13001) by @duncanmcclean
- Wrap widget header in a slot [#12973](https://github.com/statamic/cms/issues/12973) by @daun
- Live preview improvements [#12984](https://github.com/statamic/cms/issues/12984) by @JayGeorge
- Prevent divide y borders where child is hidden [#13005](https://github.com/statamic/cms/issues/13005) by @JayGeorge
- Fix permission label translations [#12992](https://github.com/statamic/cms/issues/12992) by @duncanmcclean
- Use correct mathematical symbol for display of dimensions [#13009](https://github.com/statamic/cms/issues/13009) by @daun
- Array fieldtype table fixes [#13011](https://github.com/statamic/cms/issues/13011) by @JayGeorge
- Adjust asset preview border radius [#13010](https://github.com/statamic/cms/issues/13010) by @daun
- Display asset modification date in tooltip [#13012](https://github.com/statamic/cms/issues/13012) by @daun
- Ability to have a custom Entry class per collection [#11203](https://github.com/statamic/cms/issues/11203) by @edalzell
- Fix `Invalid Date` error on required date fields [#12798](https://github.com/statamic/cms/issues/12798) by @duncanmcclean
- Bard focus ring adjustment [#13015](https://github.com/statamic/cms/issues/13015) by @JayGeorge
- Fix color fieldtype popover position [#13017](https://github.com/statamic/cms/issues/13017) by @JayGeorge
- Remove double shadow from toggle items [#13016](https://github.com/statamic/cms/issues/13016) by @JayGeorge
- Fix missing translation for user group blueprint [#12649](https://github.com/statamic/cms/issues/12649) by @duncanmcclean
- Tweak blueprint section placeholder text [#13020](https://github.com/statamic/cms/issues/13020) by @duncanmcclean
- Avoid duplicate dirty state warnings [#13018](https://github.com/statamic/cms/issues/13018) by @duncanmcclean
- Left align date fieldtype popover [#13019](https://github.com/statamic/cms/issues/13019) by @JayGeorge
- Bring sizes of custom logo and avatar closer together [#13021](https://github.com/statamic/cms/issues/13021) by @daun
- Support text-only custom logos [#13023](https://github.com/statamic/cms/issues/13023) by @daun
- Publish container text direction [#13024](https://github.com/statamic/cms/issues/13024) by @jasonvarga
- Adjust 950 color value for other hues [#13030](https://github.com/statamic/cms/issues/13030) by @JayGeorge
- Indent color vars nicerer [#13026](https://github.com/statamic/cms/issues/13026) by @JayGeorge
- Dark mode fix assets text contrast [#13025](https://github.com/statamic/cms/issues/13025) by @JayGeorge
- Tweak calendar fieldtype [#13034](https://github.com/statamic/cms/issues/13034) by @JayGeorge
- Bump lowest composer constraints [#13038](https://github.com/statamic/cms/issues/13038) by @jasonvarga
- Improve blueprint breadcrumbs [#13045](https://github.com/statamic/cms/issues/13045) by @duncanmcclean
- Address near-identical translation strings [#13028](https://github.com/statamic/cms/issues/13028) by @duncanmcclean
- Trigger full-page refresh after saving sites [#13032](https://github.com/statamic/cms/issues/13032) by @duncanmcclean
- Show Statamic's 404 page when nav doesn't exist [#13041](https://github.com/statamic/cms/issues/13041) by @duncanmcclean
- Various navigation fixes [#13039](https://github.com/statamic/cms/issues/13039) by @duncanmcclean
- Add `config:app:locale` option to sites locale dropdown [#13033](https://github.com/statamic/cms/issues/13033) by @duncanmcclean
- Fix double page tree panel in selector [#13046](https://github.com/statamic/cms/issues/13046) by @jasonvarga
- Refactor CSS color variables [#13008](https://github.com/statamic/cms/issues/13008) by @andjsch
- Focus the "Display" input when opening section edit stack [#13061](https://github.com/statamic/cms/issues/13061) by @duncanmcclean
- Add max widths to pages [#13062](https://github.com/statamic/cms/issues/13062) by @duncanmcclean
- Clear command palette actions when navigating between pages [#13059](https://github.com/statamic/cms/issues/13059) by @duncanmcclean
- Move blueprint tab edit fields into a stack [#13050](https://github.com/statamic/cms/issues/13050) by @duncanmcclean
- Add layout actions to Command Palette on collection show page [#13057](https://github.com/statamic/cms/issues/13057) by @duncanmcclean
- Remove unnecessary padding from Dictionary Fields fieldtype [#13060](https://github.com/statamic/cms/issues/13060) by @duncanmcclean
- Add IDs to create form fields [#13055](https://github.com/statamic/cms/issues/13055) by @duncanmcclean
- Add `gray-150` to replace `bg-gray-200/55` [#13040](https://github.com/statamic/cms/issues/13040) by @JayGeorge
- Prevent opening nav item in new tab from updating active state [#13056](https://github.com/statamic/cms/issues/13056) by @duncanmcclean
- Apply correct icon classes to appended input icons [#13051](https://github.com/statamic/cms/issues/13051) by @daun
- Make listing search input clearable via button [#13052](https://github.com/statamic/cms/issues/13052) by @daun



## 6.0.0-alpha.16 (2025-11-06)

### What's new
- Enable background re-caching of static cache [#9396](https://github.com/statamic/cms/issues/9396) by @ryanmitchell
- Template Scaffolding Improvements [#12872](https://github.com/statamic/cms/issues/12872) by @JohnathonKoster
- Add login passkey autocomplete [#12880](https://github.com/statamic/cms/issues/12880) by @ryanmitchell
- Support fieldsets in subdirectories [#9341](https://github.com/statamic/cms/issues/9341) by @duncanmcclean
- Revision Query builder [#10437](https://github.com/statamic/cms/issues/10437) by @ryanmitchell
- Add `min`, `max`, and `step` attributes to Integer and Float fieldtypes [#12395](https://github.com/statamic/cms/issues/12395) by @ELowry
- Generate Vue components from `make:widget`  [#12886](https://github.com/statamic/cms/issues/12886) by @duncanmcclean

### What's fixed
- Avoid rendering `DynamicHtmlRenderer` for Vue widgets [#12871](https://github.com/statamic/cms/issues/12871) by @duncanmcclean
- Add a period to avoid a new translation string [#12881](https://github.com/statamic/cms/issues/12881) by @ryanmitchell
- Indigo 700 is now the default primary/accent color [#12887](https://github.com/statamic/cms/issues/12887) by @jackmcdade
- Right-align toggles in config publish forms [#12889](https://github.com/statamic/cms/issues/12889) by @duncanmcclean
- Use a modal instead of a prompt for create passkey [#12879](https://github.com/statamic/cms/issues/12879) by @ryanmitchell
- Only send input attributes to the input [#12892](https://github.com/statamic/cms/issues/12892) by @jasonvarga
- French translations [#12883](https://github.com/statamic/cms/issues/12883) by @ebeauchamps
- Fix types in `Settings` interface [#12867](https://github.com/statamic/cms/issues/12867) by @JayGeorge
- Delete background aa [#12900](https://github.com/statamic/cms/issues/12900) by @JayGeorge
- Revert right-aligning toggles [#12902](https://github.com/statamic/cms/issues/12902) by @duncanmcclean
- Add dark mode variant to addon `tailwind.css` [#12901](https://github.com/statamic/cms/issues/12901) by @duncanmcclean
- Update Algolia [#12903](https://github.com/statamic/cms/issues/12903) by @jasonvarga
- 2fa design tweaks [#12899](https://github.com/statamic/cms/issues/12899) by @JayGeorge
- Make blue the accent UI color [#12906](https://github.com/statamic/cms/issues/12906) by @JayGeorge
- Make prop example consistent between Vue & Blade widget stubs  [#12912](https://github.com/statamic/cms/issues/12912) by @duncanmcclean
- Passkey aa gray fix [#12904](https://github.com/statamic/cms/issues/12904) by @JayGeorge
- Pull publish tabs up [#12911](https://github.com/statamic/cms/issues/12911) by @JayGeorge
- More nuanced light and dark vars [#12917](https://github.com/statamic/cms/issues/12917) by @JayGeorge
- npm audit fix [#12920](https://github.com/statamic/cms/issues/12920) by @jasonvarga
- Fix `container` config option on asset folder fieldtype [#12919](https://github.com/statamic/cms/issues/12919) by @duncanmcclean
- Only apply config layout to top-level fields [#12882](https://github.com/statamic/cms/issues/12882) by @duncanmcclean
- Fake process in tests [#12922](https://github.com/statamic/cms/issues/12922) by @jasonvarga
- German translations [#12921](https://github.com/statamic/cms/issues/12921) by @helloDanuk
- Remove `validate` key from publish array [#12918](https://github.com/statamic/cms/issues/12918) by @duncanmcclean
- Make the focus style more consistent [#12916](https://github.com/statamic/cms/issues/12916) by @JayGeorge
- Filters close button position fix [#12924](https://github.com/statamic/cms/issues/12924) by @JayGeorge
- Ignore test changes in Vite [#12928](https://github.com/statamic/cms/issues/12928) by @jasonvarga
- Add singular translation for entry count on collection grid [#12931](https://github.com/statamic/cms/issues/12931) by @duncanmcclean
- Optically size fieldtype icons [#12936](https://github.com/statamic/cms/issues/12936) by @JayGeorge
- Address input regressions [#12940](https://github.com/statamic/cms/issues/12940) by @jasonvarga
- Add a `creating-entry` hook to allow default values to be set [#8643](https://github.com/statamic/cms/issues/8643) by @ryanmitchell
- Fix re-used state in publish forms [#12937](https://github.com/statamic/cms/issues/12937) by @duncanmcclean
- Publish migration for `webauthn` table during upgrade process [#12938](https://github.com/statamic/cms/issues/12938) by @duncanmcclean
- Remove `header` widget [#12946](https://github.com/statamic/cms/issues/12946) by @duncanmcclean
- Prevent publish form jumping to top as sidebar shows/hides [#12948](https://github.com/statamic/cms/issues/12948) by @duncanmcclean
- Prevent addon settings blueprints from being edited  [#12866](https://github.com/statamic/cms/issues/12866) by @duncanmcclean
- Prevent fieldtype components mounting twice  [#12950](https://github.com/statamic/cms/issues/12950) by @duncanmcclean
- Inertia-ify more things [#12942](https://github.com/statamic/cms/issues/12942) by @duncanmcclean
- Revision history improvements [#12949](https://github.com/statamic/cms/issues/12949) by @JayGeorge
- Tidy up stack headers [#12941](https://github.com/statamic/cms/issues/12941) by @JayGeorge
- Revision history tweaks [#12955](https://github.com/statamic/cms/issues/12955) by @JayGeorge
- Blue text fix [#12956](https://github.com/statamic/cms/issues/12956) by @JayGeorge
- Add missing `arrow-up` icon [#12954](https://github.com/statamic/cms/issues/12954) by @duncanmcclean
- Fix error on navigation edit page [#12952](https://github.com/statamic/cms/issues/12952) by @duncanmcclean
- Delete Tooltip UI component in favour of `v-tooltip` [#12953](https://github.com/statamic/cms/issues/12953) by @duncanmcclean
- Link Fieldtype API changes [#11375](https://github.com/statamic/cms/issues/11375) by @duncanmcclean
- Fix pro badge tooltip [#12958](https://github.com/statamic/cms/issues/12958) by @duncanmcclean
- Fix avatar squishing [#12957](https://github.com/statamic/cms/issues/12957) by @JayGeorge
- Fix page tree links [#12968](https://github.com/statamic/cms/issues/12968) by @duncanmcclean
- Fix locale select text contrast [#12964](https://github.com/statamic/cms/issues/12964) by @JayGeorge
- Adjust empty state empty screens on mobile [#12965](https://github.com/statamic/cms/issues/12965) by @JayGeorge
- Add alternative keyboard shortcut for toggling the nav [#12967](https://github.com/statamic/cms/issues/12967) by @duncanmcclean
- Various Bard fixes [#12963](https://github.com/statamic/cms/issues/12963) by @duncanmcclean
- Fix the Markdown fieldtype from being pulled above the sidebar nav [#12961](https://github.com/statamic/cms/issues/12961) by @JayGeorge
- Show hidden fieldtype in form submission [#12970](https://github.com/statamic/cms/issues/12970) by @jasonvarga



## 6.0.0-alpha.15 (2025-10-24)

### What's new
- Passkeys [#9239](https://github.com/statamic/cms/issues/9239) by @ryanmitchell
- Bladeless widgets [#12801](https://github.com/statamic/cms/issues/12801) by @duncanmcclean

### What's fixed
- Flatten badges by default [#12834](https://github.com/statamic/cms/issues/12834) by @jackmcdade
- Fix badge button style when used as link or button [9566dcec4](https://github.com/statamic/cms/commit/9566dcec4) by @jackmcdade
- Fix week hour format [#12844](https://github.com/statamic/cms/issues/12844) by @jasonvarga
- Improve replicator preview [#12841](https://github.com/statamic/cms/issues/12841) by @JayGeorge
- Remove `getting_started` widget from `cp` config [#12852](https://github.com/statamic/cms/issues/12852) by @duncanmcclean
- Add `HandleInertiaRequests` middleware to frontend auth routes [#12854](https://github.com/statamic/cms/issues/12854) by @duncanmcclean
- Fix empty dashboard [#12851](https://github.com/statamic/cms/issues/12851) by @duncanmcclean
- Convert collection create page to Inertia [#12853](https://github.com/statamic/cms/issues/12853) by @duncanmcclean
- Bard tweaks [#12850](https://github.com/statamic/cms/issues/12850) by @JayGeorge
- Fix text bottom margin in table cell [#12849](https://github.com/statamic/cms/issues/12849) by @JayGeorge
- Add `show_mode_label` option to code fieldtype [#12848](https://github.com/statamic/cms/issues/12848) by @duncanmcclean
- Dark mode tweaks [#12843](https://github.com/statamic/cms/issues/12843) by @JayGeorge
- Move JS initialization earlier [#12856](https://github.com/statamic/cms/issues/12856) by @jasonvarga
- Combobox: Only render `selected-option` slot when there's a selected option [#12847](https://github.com/statamic/cms/issues/12847) by @duncanmcclean
- Set the default dated collections view to list [#12839](https://github.com/statamic/cms/issues/12839) by @JayGeorge
- Add replicator set above first set [#12838](https://github.com/statamic/cms/issues/12838) by @JayGeorge
- Bard sets fix inset pseudo content from blocking pointer events [#12837](https://github.com/statamic/cms/issues/12837) by @JayGeorge
- Publish Container should watch changes to `modifiedFields` prop [#12830](https://github.com/statamic/cms/issues/12830) by @duncanmcclean
- Fix combobox z-index in modals [#12829](https://github.com/statamic/cms/issues/12829) by @JayGeorge
- Bard reading time [#12836](https://github.com/statamic/cms/issues/12836) by @JayGeorge
- Fix css compilation issues [#12833](https://github.com/statamic/cms/issues/12833) by @JayGeorge
- Persistent sidebar collapse icon [#12846](https://github.com/statamic/cms/issues/12846) by @JayGeorge
- Fix css border-image compilation clash [3257369f3](https://github.com/statamic/cms/commit/3257369f3) by @jackmcdade
- Convert collection scaffold page to Inertia [#12826](https://github.com/statamic/cms/issues/12826) by @duncanmcclean



## 6.0.0-alpha.14 (2025-10-21)

### What's fixed
- Default content bg [#12769](https://github.com/statamic/cms/issues/12769) by @JayGeorge
- Fix failing test [#12802](https://github.com/statamic/cms/issues/12802) by @duncanmcclean
- "Visit URL" link should open in a new tab [#12797](https://github.com/statamic/cms/issues/12797) by @duncanmcclean
- Replace `<a>` tags with Inertia's `<Link>` component [#12796](https://github.com/statamic/cms/issues/12796) by @duncanmcclean
- Inertia-fy empty dashboard [#12793](https://github.com/statamic/cms/issues/12793) by @jasonvarga
- Fix missing props on entry create form [#12794](https://github.com/statamic/cms/issues/12794) by @duncanmcclean
- Make widget titles linkable [#12799](https://github.com/statamic/cms/issues/12799) by @duncanmcclean
- Handle dirty state in the user wizard [#12792](https://github.com/statamic/cms/issues/12792) by @duncanmcclean
- Configuration screen fixes [#12800](https://github.com/statamic/cms/issues/12800) by @JayGeorge
- Contain overscroll in calendar posts [#12786](https://github.com/statamic/cms/issues/12786) by @JayGeorge
- Stack fixes [#12783](https://github.com/statamic/cms/issues/12783) by @JayGeorge
- Fix duplicate impersonating badges in user dropdown [#12789](https://github.com/statamic/cms/issues/12789) by @duncanmcclean
- Fix inactive tab text color legibility [#12784](https://github.com/statamic/cms/issues/12784) by @JayGeorge
- Fix calendar cell aspect ratio in safari [#12787](https://github.com/statamic/cms/issues/12787) by @JayGeorge
- Fix missing validation errors & redirects in production [#12795](https://github.com/statamic/cms/issues/12795) by @duncanmcclean
- Delete unused Blade views [#12785](https://github.com/statamic/cms/issues/12785) by @duncanmcclean
- Calendar narrow rows only for higher viewports [#12788](https://github.com/statamic/cms/issues/12788) by @JayGeorge
- French translations [#12807](https://github.com/statamic/cms/issues/12807) by @ebeauchamps
- Fix stack inset [#12812](https://github.com/statamic/cms/issues/12812) by @JayGeorge
- Fix x position [#12814](https://github.com/statamic/cms/issues/12814) by @JayGeorge
- `AddonTestCase` should load Inertia's `ServiceProvider` [#12815](https://github.com/statamic/cms/issues/12815) by @duncanmcclean
- Improve sidebar nav performance [#12816](https://github.com/statamic/cms/issues/12816) by @JayGeorge
- CP Nav Customisation - Add missing show item icon [#12818](https://github.com/statamic/cms/issues/12818) by @JayGeorge
- List fieldtype border radius [#12817](https://github.com/statamic/cms/issues/12817) by @JayGeorge
- Calendar entries dark mode [#12811](https://github.com/statamic/cms/issues/12811) by @JayGeorge
- Fix column layout for publish fields [#12813](https://github.com/statamic/cms/issues/12813) by @JayGeorge
- Decouple CSRF token from nocache script [#11014](https://github.com/statamic/cms/issues/11014) by @aerni



## 6.0.0-alpha.13 (2025-10-16)

### What's new
- Collection Calendar mode [#12597](https://github.com/statamic/cms/issues/12597) by @jackmcdade
- Inertia fixes and additions [#12747](https://github.com/statamic/cms/issues/12747) by @jasonvarga
- Allow extra config on ALL fieldtypes [#12722](https://github.com/statamic/cms/issues/12722) by @ryanmitchell

### What's fixed
- Fix button tag/target logic [#12715](https://github.com/statamic/cms/issues/12715) by @jasonvarga
- Fix user in email utility [#12726](https://github.com/statamic/cms/issues/12726) by @jasonvarga
- Fix error from `RelationshipInput` when items aren't displayed [#12745](https://github.com/statamic/cms/issues/12745) by @duncanmcclean
- Pluralize "Draft" on collection listings [#12740](https://github.com/statamic/cms/issues/12740) by @duncanmcclean
- Inertia-fy Duplicate IDs page [#12742](https://github.com/statamic/cms/issues/12742) by @duncanmcclean
- Prevent combobox dropdown opening when disabled [#12746](https://github.com/statamic/cms/issues/12746) by @duncanmcclean
- Fix error when augmenting icon field [#12741](https://github.com/statamic/cms/issues/12741) by @duncanmcclean
- Fix invisible audio player in asset editor [#12735](https://github.com/statamic/cms/issues/12735) by @daun
- Match Markdown footer border color with header [#12728](https://github.com/statamic/cms/issues/12728) by @helloDanuk
- Container shade [#12749](https://github.com/statamic/cms/issues/12749) by @JayGeorge
- Improve Nav icon ux [#12752](https://github.com/statamic/cms/issues/12752) by @JayGeorge
- Consistent stacks [#12754](https://github.com/statamic/cms/issues/12754) by @JayGeorge
- Use object-contain instead of object-cover in the thumbnail view [#12755](https://github.com/statamic/cms/issues/12755) by @JayGeorge
- Improve live preview sidebar [#12756](https://github.com/statamic/cms/issues/12756) by @JayGeorge
- Maps z-index values across the CP to variables [#12617](https://github.com/statamic/cms/issues/12617) by @JayGeorge
- Improve collection listing badges [#12753](https://github.com/statamic/cms/issues/12753) by @JayGeorge
- Display available preview in asset editor [#12734](https://github.com/statamic/cms/issues/12734) by @daun
- Publish field component changes [#12743](https://github.com/statamic/cms/issues/12743) by @duncanmcclean
- Stacks facelift [#12761](https://github.com/statamic/cms/issues/12761) by @jackmcdade
- Translate "Saved" string when saving user [#12762](https://github.com/statamic/cms/issues/12762) by @duncanmcclean
- Export Inertia's `usePoll` composable [#12764](https://github.com/statamic/cms/issues/12764) by @duncanmcclean
- Remove Drawer Component [#12766](https://github.com/statamic/cms/issues/12766) by @jasonvarga
- Bard sticky stuck toolbar [#12767](https://github.com/statamic/cms/issues/12767) by @JayGeorge
- Field toolbar fixes [#12768](https://github.com/statamic/cms/issues/12768) by @JayGeorge
- Improve scrolling cut-off point on the page editor [#12770](https://github.com/statamic/cms/issues/12770) by @JayGeorge
- German translations [#12733](https://github.com/statamic/cms/issues/12733) by @helloDanuk



## 6.0.0-alpha.12 (2025-10-10)

### What's fixed
- Go back to v5 style config fields [#12700](https://github.com/statamic/cms/issues/12700) by @jackmcdade
- Fix inconsistent spacing in cp config [#12677](https://github.com/statamic/cms/issues/12677) by @duncanmcclean
- Inertia-fy Forms [#12676](https://github.com/statamic/cms/issues/12676) by @jasonvarga
- Nav items with `target="_blank"` shouldn't be Inertia Links [#12683](https://github.com/statamic/cms/issues/12683) by @duncanmcclean
- Fix Reupload Asset action [#12689](https://github.com/statamic/cms/issues/12689) by @duncanmcclean
- Change wording of "Submit" button in nav page editor [#12688](https://github.com/statamic/cms/issues/12688) by @duncanmcclean
- Fix deletion modals on structure trees [#12687](https://github.com/statamic/cms/issues/12687) by @duncanmcclean
- Tweak config of set preview asset field [#12684](https://github.com/statamic/cms/issues/12684) by @duncanmcclean
- Mobile - fix a tiny bit of overflow on the dashboard widget view [125224fd5](https://github.com/statamic/cms/commit/125224fd5) by @JayGeorge 
- Fix missing page titles [#12682](https://github.com/statamic/cms/issues/12682) by @duncanmcclean
- Fix inertia pro logic [#12692](https://github.com/statamic/cms/issues/12692) by @jasonvarga
- Date Picker: Avoid passing current time when time is disabled [#12686](https://github.com/statamic/cms/issues/12686) by @duncanmcclean
- Fix field / date filtering [#12696](https://github.com/statamic/cms/issues/12696) by @jasonvarga
- Responsive improvements [#12697](https://github.com/statamic/cms/issues/12697) by @JayGeorge
- Allow initializing values into entry create form [#12699](https://github.com/statamic/cms/issues/12699) by @jasonvarga
- Markdown fieldtype - Remove/Comment out the purple-ish selection colo… [#12693](https://github.com/statamic/cms/issues/12693) by @JayGeorge
- Target group buttons _only_ in the floating toolbar [#12707](https://github.com/statamic/cms/issues/12707) by @JayGeorge
- Pass existing data into asset thumbnail hook [#12702](https://github.com/statamic/cms/issues/12702) by @daun
- Fix front-end 2FA pages [#12711](https://github.com/statamic/cms/issues/12711) by @jasonvarga
- Utility tweaks [#12712](https://github.com/statamic/cms/issues/12712) by @jasonvarga
- French translations [#12706](https://github.com/statamic/cms/issues/12706) by @ebeauchamps
- Dark mode improvements [#12691](https://github.com/statamic/cms/issues/12691) by @JayGeorge



## 6.0.0-alpha.11 (2025-10-07)

### What's new
- JS API fixes and adjustments [#12665](https://github.com/statamic/cms/issues/12665) by @jasonvarga
- Inertia-fy Utilities [#12674](https://github.com/statamic/cms/issues/12674) by @jasonvarga
- Inertia-fy the blueprints and fieldsets areas [#12656](https://github.com/statamic/cms/issues/12656) by @jasonvarga

### What's fixed
- Inertia toast handling [#12670](https://github.com/statamic/cms/issues/12670) by @jasonvarga
- Bard sticky floating toolbar fixes [#12661](https://github.com/statamic/cms/issues/12661) by @JayGeorge
- If you're focused on a checkbox, Enter now submits the nearest form.  [#12671](https://github.com/statamic/cms/issues/12671) by @jackmcdade
- Fix missing site name in header [#12673](https://github.com/statamic/cms/issues/12673) by @jasonvarga
- Fix translations [#12666](https://github.com/statamic/cms/issues/12666) by @jasonvarga
- Move blade badge to header [#12669](https://github.com/statamic/cms/issues/12669) by @jasonvarga
- Global site selector should only be visible when you have > 1 site [#12655](https://github.com/statamic/cms/issues/12655) by @duncanmcclean
- Make the Globals index look like the other indexes [aceb87731](https://github.com/statamic/cms/commit/aceb87731)
- Fix select/combobox chevron overflow [247d4d143](https://github.com/statamic/cms/commit/247d4d143)
- Fix drop-to-upload ui [7fd35d50c](https://github.com/statamic/cms/commit/7fd35d50c)
- French translations [#12672](https://github.com/statamic/cms/issues/12672) by @ebeauchamps



## 6.0.0-alpha.10 (2025-10-03)

### What's new
- Inertia [#12610](https://github.com/statamic/cms/issues/12610) by @jasonvarga
- Replicator and Bard Set Preview Images [#12532](https://github.com/statamic/cms/issues/12532) by @jasonvarga
- Improve stack saving ux [#12606](https://github.com/statamic/cms/issues/12606) by @JayGeorge
- Ability to use custom components & hide "Run action" button in action modals [#12612](https://github.com/statamic/cms/issues/12612) by @duncanmcclean

### What's fixed
- Improve send password reset modal [#12577](https://github.com/statamic/cms/issues/12577) by @JayGeorge
- Asset table fixes for Safari [#12575](https://github.com/statamic/cms/issues/12575) by @JayGeorge
- Use new Avatar component in User Listing [#12576](https://github.com/statamic/cms/issues/12576) by @JayGeorge
- Make the Avatar gradient mesh more readable / AA compliant [#12579](https://github.com/statamic/cms/issues/12579) by @JayGeorge
- Scrollable modals [#12580](https://github.com/statamic/cms/issues/12580) by @jackmcdade
- Fix table container rounded corners [#12581](https://github.com/statamic/cms/issues/12581) by @jackmcdade
- Improve relationship field padding [#12604](https://github.com/statamic/cms/issues/12604) by @JayGeorge
- Fix breadcrumb dropdown with plurals including apostrophes. [#12602](https://github.com/statamic/cms/issues/12602) by @Frank-L93
- Remove stacked rounded corners [#12599](https://github.com/statamic/cms/issues/12599) by @JayGeorge
- Make radio and checkbox options consistent [#12596](https://github.com/statamic/cms/issues/12596) by @JayGeorge
- Fix inability to move a page with children to another top-level position [#12582](https://github.com/statamic/cms/issues/12582) by @jackmcdade
- Fix the custom "create entry" button in card view [#12598](https://github.com/statamic/cms/issues/12598) by @JayGeorge
- Text color on disabled buttons [#12573](https://github.com/statamic/cms/issues/12573) by @JayGeorge
- Fix global site selector popping in [#12578](https://github.com/statamic/cms/issues/12578) by @JayGeorge
- Bard slash command for single set [#12595](https://github.com/statamic/cms/issues/12595) by @JayGeorge
- Sticky blueprint heading [#12608](https://github.com/statamic/cms/issues/12608) by @JayGeorge
- Rearrange collections index modes [#12616](https://github.com/statamic/cms/issues/12616) by @jackmcdade
- Improve Assets loading with skeleton [#12615](https://github.com/statamic/cms/issues/12615) by @JayGeorge
- Prevent panel transparency [#12637](https://github.com/statamic/cms/issues/12637) by @JayGeorge
- Sticky toolbars [#12632](https://github.com/statamic/cms/issues/12632) by @JayGeorge
- Bard icon consistency [#12623](https://github.com/statamic/cms/issues/12623) by @JayGeorge
- New folder placeholder length [#12621](https://github.com/statamic/cms/issues/12621) by @JayGeorge
- Relationship fields fix space after colon [#12614](https://github.com/statamic/cms/issues/12614) by @JayGeorge
- Toast shouldnt fire on row actions when message === false [#12611](https://github.com/statamic/cms/issues/12611) by @ryanmitchell
- Sticky table fixes [#12618](https://github.com/statamic/cms/issues/12618) by @JayGeorge
- Fix inconsistent gray border widths [#12620](https://github.com/statamic/cms/issues/12620) by @JayGeorge
- Wire up more Inertia pages [#12646](https://github.com/statamic/cms/issues/12646) by @jasonvarga
- pass action and values to component [#12641](https://github.com/statamic/cms/issues/12641) by @ryanmitchell
- Fix set picker regressions [#12645](https://github.com/statamic/cms/issues/12645) by @jackmcdade
- Page tree node transitions [#12640](https://github.com/statamic/cms/issues/12640) by @JayGeorge
- Container queries for publish fields [#12644](https://github.com/statamic/cms/issues/12644) by @JayGeorge
- Remove header starting style [#12639](https://github.com/statamic/cms/issues/12639) by @JayGeorge
- Bard floating toolbar mode fix container focus state [#12594](https://github.com/statamic/cms/issues/12594) by @JayGeorge
- Fix PSR4 warning from `SetupCpViteTest` [#12647](https://github.com/statamic/cms/issues/12647) by @duncanmcclean
- Fix "Create Entry" button in collection grid view [#12652](https://github.com/statamic/cms/issues/12652) by @duncanmcclean
- Make `Save` toast message translatable [#12648](https://github.com/statamic/cms/issues/12648) by @duncanmcclean
- Track dirty state when editing fieldsets [#12651](https://github.com/statamic/cms/issues/12651) by @duncanmcclean
- Wire up `PublishForm::make()` as an Inertia page [#12650](https://github.com/statamic/cms/issues/12650) by @duncanmcclean
- Wire up architectural line backgrounds in Inertia [#12653](https://github.com/statamic/cms/issues/12653) by @jasonvarga
- Fix reordering in asset fieldtype's grid view [#12654](https://github.com/statamic/cms/issues/12654) by @duncanmcclean
- German translations [#12587](https://github.com/statamic/cms/issues/12587) by @helloDanuk
- French translations [#12583](https://github.com/statamic/cms/issues/12583) by @ebeauchamps
- French translations [#12633](https://github.com/statamic/cms/issues/12633) by @ebeauchamps



## 6.0.0-alpha.9 (2025-09-24)

### What's new
- Blade Component Support in Antlers [#12424](https://github.com/statamic/cms/issues/12424) by @JohnathonKoster
- Avatar component [#12194](https://github.com/statamic/cms/issues/12194) by @martyf
- Update `make:fieldtype` stubs [#12533](https://github.com/statamic/cms/issues/12533) by @duncanmcclean
- Update `make:widget` stub [#12521](https://github.com/statamic/cms/issues/12521) by @duncanmcclean

### What's fixed

- Border radius inception [#12571](https://github.com/statamic/cms/issues/12571) by @JayGeorge
- Fix JS package tests [#12572](https://github.com/statamic/cms/issues/12572) by @jasonvarga
- Fix "Contained data table" styling [#12556](https://github.com/statamic/cms/issues/12556) by @JayGeorge
- Smoother page loading, mostly for Firefox [#12568](https://github.com/statamic/cms/issues/12568) by @JayGeorge
- Fix license rows [#12569](https://github.com/statamic/cms/issues/12569) by @JayGeorge
- Remove create child entry separator [#12570](https://github.com/statamic/cms/issues/12570) by @JayGeorge
- Bard toolbar fixes [#12543](https://github.com/statamic/cms/issues/12543) by @JayGeorge
- Make "Cancel" in ConfirmationModal translatable [#12539](https://github.com/statamic/cms/issues/12539) by @duncanmcclean
- Fix JS errors caused by single quotes in translations [#12541](https://github.com/statamic/cms/issues/12541) by @duncanmcclean
- Fix button text on password reset page [#12540](https://github.com/statamic/cms/issues/12540) by @duncanmcclean
- Make Origin dropdown clearable on Global Set settings [#12542](https://github.com/statamic/cms/issues/12542) by @duncanmcclean
- Fake breadcrumb keyboard focus [#12545](https://github.com/statamic/cms/issues/12545) by @JayGeorge
- Fix a few focus states in the global header [#12546](https://github.com/statamic/cms/issues/12546) by @JayGeorge
- Assets upload icon spacing [#12550](https://github.com/statamic/cms/issues/12550) by @JayGeorge
- Improve combobox legibility to pass AA [#12544](https://github.com/statamic/cms/issues/12544) by @JayGeorge
- Fix relationship input size edge cases [#12549](https://github.com/statamic/cms/issues/12549) by @jackmcdade
- Checkbox item component shouldnt have required value prop [#12534](https://github.com/statamic/cms/issues/12534) by @jasonvarga
- Match whereJsonContains to how Laravel handles it [#11117](https://github.com/statamic/cms/issues/11117) by @ryanmitchell
- Hot-reload contents of Live Preview embed [#11982](https://github.com/statamic/cms/issues/11982) by @duncanmcclean
- Add `@vitejs/plugin-vue` dependency to package [#12531](https://github.com/statamic/cms/issues/12531) by @duncanmcclean
- Replace tailwind/typography [#12516](https://github.com/statamic/cms/issues/12516) by @JayGeorge
- Improve Combobox performance [#12499](https://github.com/statamic/cms/issues/12499) by @duncanmcclean
- Add dependencies to UI package [#12512](https://github.com/statamic/cms/issues/12512) by @duncanmcclean
- Ensure field names are always left-aligned in blueprint builder [#12501](https://github.com/statamic/cms/issues/12501) by @duncanmcclean
- Fix publish form actions showing at bottom when sidebar is visible [#12509](https://github.com/statamic/cms/issues/12509) by @duncanmcclean
- Fix global site selector [#12497](https://github.com/statamic/cms/issues/12497) by @duncanmcclean
- Improve List fieldtype in dark mode [#12495](https://github.com/statamic/cms/issues/12495) by @duncanmcclean
- Fix translations in relationship selector [#12498](https://github.com/statamic/cms/issues/12498) by @duncanmcclean
- Add `clearable` option to Dictionary fieldtype [#12506](https://github.com/statamic/cms/issues/12506) by @duncanmcclean
- Make template & layout dropdowns clearable [#12505](https://github.com/statamic/cms/issues/12505) by @duncanmcclean
- Ensure publish form actions are visible on smaller screens [#12500](https://github.com/statamic/cms/issues/12500) by @duncanmcclean
- Improve term template instructions [#12507](https://github.com/statamic/cms/issues/12507) by @duncanmcclean
- Correct select icon and spacing [#12486](https://github.com/statamic/cms/issues/12486) by @martyf
- Fix typo in navigation blueprint breadcrumb [#12494](https://github.com/statamic/cms/issues/12494) by @duncanmcclean
- Correct Grid fieldtype deleting row condition [#12488](https://github.com/statamic/cms/issues/12488) by @martyf
- Replace page tree ui/tooltips with regular directive tooltips. [9859261fc](https://github.com/statamic/cms/commit/9859261fc) by @jackmcdade
- Section toggle is now a button for a11y. [4b1fb74b3](https://github.com/statamic/cms/commit/4b1fb74b3) by @jackmcdade
- German translations [#12529](https://github.com/statamic/cms/issues/12529) by @helloDanuk
- French translations [#12493](https://github.com/statamic/cms/issues/12493) by @ebeauchamps



## 6.0.0-alpha.8 (2025-09-17)

### What's new
- Smoother page loads using animation [#12454](https://github.com/statamic/cms/issues/12454) by @JayGeorge
- Add `setup-cp-vite` command [#12056](https://github.com/statamic/cms/issues/12056) by @duncanmcclean
- Add `prepend` and `append` options to Float fieldtype [#12476](https://github.com/statamic/cms/issues/12476) by @duncanmcclean

### What's fixed
- Add missing architectural lines to navigation > getting started screen [#12468](https://github.com/statamic/cms/issues/12468) by @JayGeorge
- Bard focus states [#12360](https://github.com/statamic/cms/issues/12360) by @JayGeorge
- Collection grid missing v-for key [#12477](https://github.com/statamic/cms/issues/12477) by @martyf
- Ensure "Stop Impersonating" string is translated correctly [#12456](https://github.com/statamic/cms/issues/12456) by @duncanmcclean
- Combobox shouldn't be disabled when max selections limit has been reached [#12472](https://github.com/statamic/cms/issues/12472) by @duncanmcclean
- Make bard buttons setting responsive [#12474](https://github.com/statamic/cms/issues/12474) by @jacksleight
- Combobox: Fix search input focus [#12471](https://github.com/statamic/cms/issues/12471) by @duncanmcclean
- Fix permission labels not being localized correctly [#12316](https://github.com/statamic/cms/issues/12316) by @duncanmcclean
- Add tests for the `Combobox` component [#12174](https://github.com/statamic/cms/issues/12174) by @duncanmcclean
- Add `cursor-pointer` to Create Form links [#12453](https://github.com/statamic/cms/issues/12453) by @duncanmcclean
- Fix licensing table [#12429](https://github.com/statamic/cms/issues/12429) by @duncanmcclean
- Textareas should autosize [#12435](https://github.com/statamic/cms/issues/12435) by @duncanmcclean
- Hide "Customize Columns" button in Grid mode [#12448](https://github.com/statamic/cms/issues/12448) by @duncanmcclean
- Optimize PageTree performance issues with larger collections [#12434](https://github.com/statamic/cms/issues/12434) by @o1y
- French translations [#12430](https://github.com/statamic/cms/issues/12430) by @ebeauchamps
- Validation error shouldn't be thrown when asset container has value [#12437](https://github.com/statamic/cms/issues/12437) by @duncanmcclean
- Fix string on terms empty state [#12447](https://github.com/statamic/cms/issues/12447) by @duncanmcclean
- Fix icon in field settings stack [#12436](https://github.com/statamic/cms/issues/12436) by @duncanmcclean
- Fix validation errors not showing for relationship fieldtypes [#12438](https://github.com/statamic/cms/issues/12438) by @duncanmcclean
- Improve folder empty states in asset browser [#12452](https://github.com/statamic/cms/issues/12452) by @duncanmcclean
- Add `target` and `variant` props to Context Item component [#12451](https://github.com/statamic/cms/issues/12451) by @duncanmcclean
- Fix adding nav items from empty state [#12463](https://github.com/statamic/cms/issues/12463) by @duncanmcclean
- Ensure nav customizations apply to breadcrumbs [#12460](https://github.com/statamic/cms/issues/12460) by @duncanmcclean
- Fix Grid fieldtype's table mode [#12439](https://github.com/statamic/cms/issues/12439) by @duncanmcclean
- "Imported from: ..." translation should include variable [#12455](https://github.com/statamic/cms/issues/12455) by @duncanmcclean
- Make translator tool look in the ui package [#12423](https://github.com/statamic/cms/issues/12423) by @jasonvarga



## 6.0.0-alpha.7 (2025-09-12)

### What's new
- Standalone UI package etc [#12329](https://github.com/statamic/cms/issues/12329) by @jasonvarga
- Command palette `keys` API and display [#12241](https://github.com/statamic/cms/issues/12241) by @jesseleite
- URL trailing slash normalizations and support for enforcing them [#11840](https://github.com/statamic/cms/issues/11840) by @jesseleite
- Drawer UI component [#12215](https://github.com/statamic/cms/issues/12215) by @jackmcdade

### What's fixed
- Require Bard container when image button is enabled [#12238](https://github.com/statamic/cms/issues/12238) by @godismyjudge95
- Fix site selectors [#12411](https://github.com/statamic/cms/issues/12411) by @duncanmcclean
- Page Tree now remembers collapseAll and expandAll [ddda9f48d](https://github.com/statamic/cms/commit/ddda9f48d) by @jackmcdade
- ColorFieldtype now stores updated values when you click away. [b97ed4fb5](https://github.com/statamic/cms/commit/b97ed4fb5) by @jackmcdade
- Improve collection grid listing in multisite [#12324](https://github.com/statamic/cms/issues/12324) by @duncanmcclean
- Fix locales dictionary on Windows [#12407](https://github.com/statamic/cms/issues/12407) by @duncanmcclean
- Fix combobox dropdown not showing in modals [#12158](https://github.com/statamic/cms/issues/12158) by @duncanmcclean
- Fix "ESC" key to dismiss the command palette when Bard is on the page [#12394](https://github.com/statamic/cms/issues/12394) by @duncanmcclean
- Restore breadcrumb hover padding [b5431a009](https://github.com/statamic/cms/commit/b5431a009) by @JayGeorge
- Pressing meta + enter on command palette should open new window [#12389](https://github.com/statamic/cms/issues/12389) by @ryanmitchell
- Fix Live Preview popout [#12366](https://github.com/statamic/cms/issues/12366) by @duncanmcclean
- Simplify Button groups [#12327](https://github.com/statamic/cms/issues/12327) by @JayGeorge
- Global header - switch out grey text colors in favour of faded white colors so that theming looks better [#12405](https://github.com/statamic/cms/issues/12405) by @JayGeorge
- Tone down global search background opacity so it works better with different colors [#12414](https://github.com/statamic/cms/issues/12414) by @JayGeorge
- Change from text-balance to text-pretty [#12403](https://github.com/statamic/cms/issues/12403) by @helloDanuk
- Breadcrumb hover padding [#12408](https://github.com/statamic/cms/issues/12408) by @JayGeorge
- Fix position of revisions "View History" button [#12406](https://github.com/statamic/cms/issues/12406) by @duncanmcclean
- Switch hover state from blue-500 to blue-600 to hit AA for accessibility [#12409](https://github.com/statamic/cms/issues/12409) by @JayGeorge
- Fix field label tooltip [#12410](https://github.com/statamic/cms/issues/12410) by @duncanmcclean
- Increase contrast for localizable toggle [#12413](https://github.com/statamic/cms/issues/12413) by @JayGeorge
- Assets Fieldtype: Clicking on filename should select asset [#12383](https://github.com/statamic/cms/issues/12383) by @duncanmcclean
- Add code accent for dark mode and tweak light mode values [#12398](https://github.com/statamic/cms/issues/12398) by @JayGeorge
- Fix folder svg [#12415](https://github.com/statamic/cms/issues/12415) by @jasonvarga
- Fix double svg in icon component [#12412](https://github.com/statamic/cms/issues/12412) by @jasonvarga
- Make 'Enter text...' translatable [#12401](https://github.com/statamic/cms/issues/12401) by @helloDanuk
- Fix header dark mode when subheading is present [12c0fee15](https://github.com/statamic/cms/commit/12c0fee15) by @jackmcdade
- Improve the way that the Asset Field type keeps important information visible [#12396](https://github.com/statamic/cms/issues/12396) by @JayGeorge
- Improve asset fieldtype tiles and rows [#12397](https://github.com/statamic/cms/issues/12397) by @jackmcdade
- Icon changes, bring back custom set icons [#12286](https://github.com/statamic/cms/issues/12286) by @duncanmcclean
- Fix discarding nav items [#12390](https://github.com/statamic/cms/issues/12390) by @duncanmcclean
- Table styling [#12367](https://github.com/statamic/cms/issues/12367) by @JayGeorge
- German translations [#12370](https://github.com/statamic/cms/issues/12370) by @helloDanuk
- Make "Save" translatable on publish forms [#12375](https://github.com/statamic/cms/issues/12375) by @duncanmcclean
- Fix uploading via the asset fieldtype [#12379](https://github.com/statamic/cms/issues/12379) by @duncanmcclean
- Improve licensing layout and fix little dot [#12381](https://github.com/statamic/cms/issues/12381) by @JayGeorge
- Asset button text alignment [#12382](https://github.com/statamic/cms/issues/12382) by @JayGeorge
- Asset UI tweaks [#12384](https://github.com/statamic/cms/issues/12384) by @JayGeorge
- Make 'Select...' translatable [#12369](https://github.com/statamic/cms/issues/12369) by @helloDanuk
- Add some gap between Bard icons, for when they are "pressed" [#12368](https://github.com/statamic/cms/issues/12368) by @JayGeorge
- Improve the difference between the `data-ui-subheading` and the heading [#12268](https://github.com/statamic/cms/issues/12268) by @JayGeorge



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
