# Release Notes

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
