# Release Notes

## 3.4.4 (2023-02-15)

### What's new
- Added a `vite:asset` tag. [#7477](https://github.com/statamic/cms/issues/7477) by @mwagena

### What's improved
- French translations [#7503](https://github.com/statamic/cms/issues/7503) by @ebeauchamps

### What's fixed
- Fix pagination in the `taxonomy` tag. [#7531](https://github.com/statamic/cms/issues/7531) by @ryanmitchell
- Updated Tiptap in order to fix tables, code blocks, etc. [#7514](https://github.com/statamic/cms/issues/7514) by @jasonvarga
- Fix issue where you couldn't select text in nested Bard fields in Firefox. [#7525](https://github.com/statamic/cms/issues/7525) by @o1y
- Fix inability to position cursor after a link in Bard. [#7528](https://github.com/statamic/cms/issues/7528) by @o1y
- Fix missing dropcursor in Bard. [#7518](https://github.com/statamic/cms/issues/7518) by @o1y
- Fix Bard set popover flicker. [#7535](https://github.com/statamic/cms/issues/7535) by @o1y
- Fix issue where pasting links into Bard would strip out attributes. [#7517](https://github.com/statamic/cms/issues/7517) by @o1y
- Fix typo in preference start page instructions. [#7538](https://github.com/statamic/cms/issues/7538) by @ryanmitchell
- Fix docs URL on Updater page. [#7539](https://github.com/statamic/cms/issues/7539) by @joshuablum
- Prevent deprecation warnings in translate commands. [#7537](https://github.com/statamic/cms/issues/7537) by @ryanmitchell
- Fix issue where you couldn't position the cursor between Bard sets. [#7516](https://github.com/statamic/cms/issues/7516) by @o1y
- Fix augmentation issue in Antlers when using the scope parameter. [#7436](https://github.com/statamic/cms/issues/7436) by @JohnathonKoster



## 3.4.3 (2023-02-08)

### What's new
- Contextual keyword snippets for `local` search driver. [#7144](https://github.com/statamic/cms/issues/7144) by @jacksleight
- Support adding computed values to multiple collections at once. [#7165](https://github.com/statamic/cms/issues/7165) by @jacksleight
- Custom Bard button active and visible callbacks. [#7479](https://github.com/statamic/cms/issues/7479) by @jacksleight

### What's improved
- Antlers logs will include the file and line numbers. [#7438](https://github.com/statamic/cms/issues/7438) by @JohnathonKoster
- Add view and visit url options to collection listing dropdowns. [#7469](https://github.com/statamic/cms/issues/7469) by @arthurperton
- Cache result of schema call to avoid duplication. [#7466](https://github.com/statamic/cms/issues/7466) by @ryanmitchell
- Norwegian translations. [#7472](https://github.com/statamic/cms/issues/7472) by @espenlg
- French translations. [#7463](https://github.com/statamic/cms/issues/7463) by @ebeauchamps

### What's fixed
- Fix `nocache` tag when using URLs with multiple query parameters. [#7486](https://github.com/statamic/cms/issues/7486) by @arthurperton
- Fix `nocache` tag behavior when using the `ignore_query_string` setting. [#7488](https://github.com/statamic/cms/issues/7488) by @arthurperton
- Fix `orWhereJsonLength()` method in the query builders. [#7462](https://github.com/statamic/cms/issues/7462) by @arthurperton
- Fix bard toolbar button active states. [#7470](https://github.com/statamic/cms/issues/7470) by @jacksleight
- Fix queue worker state issues around assets and stache indexes. [#7467](https://github.com/statamic/cms/issues/7467) by @jesseleite
- Fix underline in Bard not outputting any HTML tags. [#7494](https://github.com/statamic/cms/issues/7494) by @mauricewijnia
- Fix Bard and Replicator dropdown positioning when in secondary sections. [#7478](https://github.com/statamic/cms/issues/7478) by @arthurperton
- Fix a few search issues. [#7481](https://github.com/statamic/cms/issues/7481) by @jasonvarga
- Fix warning when a search index is empty. [#7492](https://github.com/statamic/cms/issues/7492) by @simonworkhouse
- Workaround for GitHub Actions network issue. [#7482](https://github.com/statamic/cms/issues/7482) by @jasonvarga



## 3.4.2 (2023-02-02)

### What's improved
- Move `always_save` field setting toggle to conditions tab. [#7417](https://github.com/statamic/cms/issues/7417) by @jesseleite
- Add toggle for `sometimes` rule in validation builder [#7416](https://github.com/statamic/cms/issues/7416) by @jesseleite
- Auto-focus search input when opening selectors in Bard link or standalone link fieldtype. [#7425](https://github.com/statamic/cms/issues/7425) by @duncanmcclean
- French translations. [#7453](https://github.com/statamic/cms/issues/7453) by @ebeauchamps
- Dutch translations. [#7442](https://github.com/statamic/cms/issues/7442) by @stephanbouman

### What's fixed
- Fix GraphQL nested fields. [#7431](https://github.com/statamic/cms/issues/7431) by @jasonvarga
- Fix custom Bard extension type attributes. [#7450](https://github.com/statamic/cms/issues/7450) by @jacksleight
- Fix mismatched search results. [#7445](https://github.com/statamic/cms/issues/7445) by @jasonvarga
- Fix create entry form not redirecting on save. [#7444](https://github.com/statamic/cms/issues/7444) by @jasonvarga



## 3.4.1 (2023-01-30)

### What's improved
- Dutch translations. [#7418](https://github.com/statamic/cms/issues/7418) by @oakeddev
- German translations. [#7422](https://github.com/statamic/cms/issues/7422) by @helloDanuk

### What's fixed
- Fix Bard backwards compatibility. [#7433](https://github.com/statamic/cms/issues/7433) by @jasonvarga
- Fix preferences listing errors when using Eloquent users. [#7430](https://github.com/statamic/cms/issues/7430) by @jesseleite
- Prevent Vite error when missing alias in app config file. [#7429](https://github.com/statamic/cms/issues/7429) by @jacksleight
- Revert "Easier Dynamic Antlers Tags and Variables (#7164)" [#7435](https://github.com/statamic/cms/issues/7435) by @jasonvarga



## 3.4.0 (2023-01-27)

### What's new
- Preference management UI in the Control Panel. [#7226](https://github.com/statamic/cms/issues/7226) by @jasonvarga
- Ability to customize the Control Panel Nav. [#6678](https://github.com/statamic/cms/issues/6678) by @jesseleite
- A bunch of search features, improvements, and fixes. [#6318](https://github.com/statamic/cms/issues/6318) by @jasonvarga
- Ability to process/resize image assets on upload. [#6040](https://github.com/statamic/cms/issues/6040) by @jesseleite
- Bard: Upgraded Tiptap to version 2. [#6043](https://github.com/statamic/cms/issues/6043) by @wiebkevogel
- Bard: Smart Typography. [#7326](https://github.com/statamic/cms/issues/7326) by @jackmcdade
- Bard: Inline mode. [#6973](https://github.com/statamic/cms/issues/6973) by @jacksleight
- Bard: Text align support to Bard [#6450](https://github.com/statamic/cms/issues/6450) by @jacksleight
- Bard: Placeholder and character limit support. [#6449](https://github.com/statamic/cms/issues/6449) by @jacksleight
- Bard: Ability to use a custom Tiptap Editor class. [#6422](https://github.com/statamic/cms/issues/6422) by @jacksleight
- Experimental: Autosave for entries. [#6198](https://github.com/statamic/cms/issues/6198) by @wiebkevogel

### What's improved
- The Runtime Antlers parser is now the default. [#6414](https://github.com/statamic/cms/issues/6414) by @jasonvarga
- Action classes have more access to selected items. [#7261](https://github.com/statamic/cms/issues/7261) by @jasonvarga
- Bard: Improve PHP extension syntax. [#6421](https://github.com/statamic/cms/issues/6421) by @jacksleight

### What's fixed
- Ability to defer permission and utility registration, which fixes incorrect translations. [#7343](https://github.com/statamic/cms/issues/7343) by @jasonvarga
- Replicator, Bard, and Grid items `id` fields are available in templates, GraphQL and REST API. [#7279](https://github.com/statamic/cms/issues/7279) by @arthurperton
- Respect ascii replace option in JavaScript slugify function. [#6338](https://github.com/statamic/cms/issues/6338) by @jasonvarga

### What's changed
- A number of these items may contain breaking changes. Consult the [3.4 upgrade guide](https://statamic.dev/upgrade-guide/3-3-to-3-4).



## 3.3.68 (2023-02-02)

### What's fixed
- Fix create entry form not redirecting on save. [#7443](https://github.com/statamic/cms/issues/7443) by @jasonvarga



## 3.3.67 (2023-01-30)

### What's fixed
- Reverted "Dynamic Antlers Tags and Variables" [#7420](https://github.com/statamic/cms/issues/7420) by @edalzell



## 3.3.66 (2023-01-26)

### What's new
- Support Vite assets in the Control Panel. [#6869](https://github.com/statamic/cms/issues/6869) by @jacksleight
- ~~Dynamic Antlers Tags and Variables.~~ Reverted in 3.3.67/3.4.1 [#7164](https://github.com/statamic/cms/issues/7164) by @JohnathonKoster
- SVG tag allows adding `title` and `desc` elements. [#7348](https://github.com/statamic/cms/issues/7348) by @jackmcdade

### What's improved
- Custom rules are displayed in the validation builder dropdown. [#7363](https://github.com/statamic/cms/issues/7363) by @jacksleight
- Dutch translations. [#7404](https://github.com/statamic/cms/issues/7404) by @oakeddev

### What's fixed
- Fix issue where some asset directories wouldn't appear. [#7205](https://github.com/statamic/cms/issues/7205) by @jasonvarga
- Fix error when using taxonomy terms in navs. [#7407](https://github.com/statamic/cms/issues/7407) by @FrittenKeeZ
- Fix modifier exceptions losing their stack trace. [#7409](https://github.com/statamic/cms/issues/7409) by @jasonvarga
- Fix missing form values in submission emails. [#7369](https://github.com/statamic/cms/issues/7369) by @jacksleight
- Fix Eloquent user login timestamps not being set. [#7402](https://github.com/statamic/cms/issues/7402) by @ryanmitchell
- Fix Antlers issuess where variables could leak into other places. [#7353](https://github.com/statamic/cms/issues/7353) [#7392](https://github.com/statamic/cms/issues/7392) by @JohnathonKoster
- Fix Antlers issue where variables were being overzealously cached. [#7390](https://github.com/statamic/cms/issues/7390) by @JohnathonKoster
- Fix revealer fields disappearing on save. [#7388](https://github.com/statamic/cms/issues/7388) by @jesseleite



## 3.3.65 (2023-01-18)

### What's new
- PHP 8.2 support. [#7299](https://github.com/statamic/cms/issues/7299) by @jasonvarga
- Ability to pass an array of roles into the `user_roles` tag. [#7328](https://github.com/statamic/cms/issues/7328) by @edalzell

### What's improved
- When using revisions, the publish action button takes into consideration whether you can manage publish state. [#7168](https://github.com/statamic/cms/issues/7168) by @arthurperton
- Pasting multiple lines into the `list` fieldtype will create multiple list items. [#7340](https://github.com/statamic/cms/issues/7340) by @jacksleight

### What's fixed
- Fix how user blueprint fields are handled in a few cases. [#7368](https://github.com/statamic/cms/issues/7368) by @jesseleite
- Fix misspelling [#7354](https://github.com/statamic/cms/issues/7354) by @stefanbauer
- Improve memory leak and garbage collection in Antlers. [#7361](https://github.com/statamic/cms/issues/7361) [#7367](https://github.com/statamic/cms/issues/7367) by @JohnathonKoster
- Fix `revealer` fields disappearing on save. [#7329](https://github.com/statamic/cms/issues/7329) by @jacksleight
- Use customizable create label on the empty collection listing view. [#7323](https://github.com/statamic/cms/issues/7323) by @arthurperton



## 3.3.64 (2023-01-06)

### What's new
- The searchable item is now passed into search transformers. [#7305](https://github.com/statamic/cms/issues/7305) by @okaufmann
- You are now required to enter your existing password in order to change it, in the CP. [#7287](https://github.com/statamic/cms/issues/7287) by @jasonvarga

### What's improved
- Danish translations. [#7314](https://github.com/statamic/cms/issues/7314) by @rabol
- French translations. [#7275](https://github.com/statamic/cms/issues/7275) by @ebeauchamps
- Switch operator parsing in Antlers. [#7283](https://github.com/statamic/cms/issues/7283) by @JohnathonKoster

### What's fixed
- Fix asset search in nested subdirectories. [#7313](https://github.com/statamic/cms/issues/7313) by @edalzell
- Sanitize form tokens. [#7309](https://github.com/statamic/cms/issues/7309) by @jackmcdade
- Rename filename label to path in the Asset editor. [#7303](https://github.com/statamic/cms/issues/7303) by @jasonvarga
- Fix YAML facade casing. [#7298](https://github.com/statamic/cms/issues/7298) by @beatwiz
- Add property to translator to prevent deprecation message. [#7289](https://github.com/statamic/cms/issues/7289) by @jasonvarga



## 3.3.63 (2022-12-21)

### What's new
- Ability to use separate queue for in the `static:warm` command. [#7184](https://github.com/statamic/cms/issues/7184) by @robbanl

### What's improved
- Order `collection` and `taxonomy` fieldtype options by title. [#7246](https://github.com/statamic/cms/issues/7246) by @duncanmcclean
- Order `roles`, `groups`, and `sites` fieldtype options by title. [#7259](https://github.com/statamic/cms/issues/7259) by @j3ll3yfi5h
- German translations. [#7252](https://github.com/statamic/cms/issues/7252) by @j3ll3yfi5h, [#7260](https://github.com/statamic/cms/issues/7260) by @helloDanuk

### What's fixed
- Fix duplicate action not respecting entries without slugs. [#7243](https://github.com/statamic/cms/issues/7243) by @duncanmcclean
- Support `Carbon` values in conditions. [#6931](https://github.com/statamic/cms/issues/6931) by @edalzell
- Fix focus related JS error when there are no inputs. [#7257](https://github.com/statamic/cms/issues/7257) by @martyf
- Remove sound effects on tree drag & drop interactions. [#7255](https://github.com/statamic/cms/issues/7255) by @jackmcdade



## 3.3.62 (2022-12-16)

### What's new
- Support default manipulations for Glide. Support using aliases in presets. [#7239](https://github.com/statamic/cms/issues/7239) by @jackmcdade
- Support prepend and append options on the `integer` fieldtype. [#7241](https://github.com/statamic/cms/issues/7241) by @jackmcdade
- Support for search transformers to be classes. [#7177](https://github.com/statamic/cms/issues/7177) by @ryanmitchell

### What's improved
- Improve Control Panel's UX when using an invalid site key format. [#7110](https://github.com/statamic/cms/issues/7110) by @joshuablum
- Links in field instructions will in new windows. [#7223](https://github.com/statamic/cms/issues/7223) by @jackmcdade

### What's fixed
- Fix asset thumbnails and file icons in entry listings and `assets` field. [#7195](https://github.com/statamic/cms/issues/7195) by @jacksleight
- Fix autofocusing of the first field in Control Panel forms. [#7242](https://github.com/statamic/cms/issues/7242) by @jackmcdade
- Fix default preferences not being tracked with the Git integration. [#7230](https://github.com/statamic/cms/issues/7230) by @jesseleite
- The `color` fieldtype's picker closes when clicking save. [#7219](https://github.com/statamic/cms/issues/7219) by @jacksleight
- The `collection` widget's "create entry" button works with multiple blueprints. [#7217](https://github.com/statamic/cms/issues/7217) by @jackmcdade



## 3.3.61 (2022-12-13)

### What's new
- Add `user:profile_form` and `user:password_form` tags. [#6400](https://github.com/statamic/cms/issues/6400) by @jacksleight
- Add info about Stache watcher and Static Caching to the `about` and `support:details` commands. [#7213](https://github.com/statamic/cms/issues/7213) by @joshuablum

### What's improved
- French translations. [#7196](https://github.com/statamic/cms/issues/7196) by @ebeauchamps
- Hungarian translations. [#7162](https://github.com/statamic/cms/issues/7162) by @zsoltjanes

### What's fixed
- Handle edits within front-end fields. [#7178](https://github.com/statamic/cms/issues/7178) by @jasonvarga
- Handle empty checkboxes fieldtype on front-end. [#7180](https://github.com/statamic/cms/issues/7180) by @jasonvarga
- Fix term slugs not using appropriate language when creating through fieldtype. [#7208](https://github.com/statamic/cms/issues/7208) by @FrittenKeeZ
- Fix deprecation notices in `link` fieldtype when empty. [#7201](https://github.com/statamic/cms/issues/7201) by @linaspasv
- Fix Statamic's file cache driver not honoring custom permission setting. [#7189](https://github.com/statamic/cms/issues/7189) by @tomgillett
- Fix utility route authorization. [#7214](https://github.com/statamic/cms/issues/7214) by @jasonvarga
- Fix Javascript error on publish form in Chrome. [#7170](https://github.com/statamic/cms/issues/7170) by @arthurperton
- Fix focal point not saving when editing other asset fields. [#7171](https://github.com/statamic/cms/issues/7171) by @arthurperton
- Hook up REST API pagination size config. [#7161](https://github.com/statamic/cms/issues/7161) by @duncanmcclean
- Allow version 6 of `symfony/var-exporter`. [#7191](https://github.com/statamic/cms/issues/7191) by @tomschlick
- Bump express from 4.17.1 to 4.18.2 [#7187](https://github.com/statamic/cms/issues/7187) by @dependabot
- Bump minimatch from 3.0.4 to 3.1.2 [#7167](https://github.com/statamic/cms/issues/7167) by @dependabot
- Bump qs from 6.9.4 to 6.9.7 [#7176](https://github.com/statamic/cms/issues/7176) by @dependabot
- Fix npm build issues. [#7190](https://github.com/statamic/cms/issues/7190) by @jasonvarga



## 3.3.60 (2022-12-02)

### What's new
- Add `when` and `unless` to `partial` tag. [#7054](https://github.com/statamic/cms/issues/7054) by @edalzell
- Ability to override greeting in activation mail. [#7154](https://github.com/statamic/cms/issues/7154) by @ruslansteiger

### What's improved
- Replicator and Bard sets can be toggled with a single click. [#7037](https://github.com/statamic/cms/issues/7037) by @jacksleight
- Improve accessibility for tabs. [#6704](https://github.com/statamic/cms/issues/6704) by @arthurperton
- Show YouTube Shorts in `video` fieldtype preview. [#7153](https://github.com/statamic/cms/issues/7153) by @duncanmcclean
- German translations. [#7157](https://github.com/statamic/cms/issues/7157) by @helloDanuk
- Dutch translations. [#7152](https://github.com/statamic/cms/issues/7152) by @oakeddev

### What's fixed
- Maintain `sort` parameter in REST API links. [#7158](https://github.com/statamic/cms/issues/7158) by @edalzell
- Make Antlers value resolution "lazy", and make pluck operator work with nulls. [#7151](https://github.com/statamic/cms/issues/7151) by @JohnathonKoster
- Prevent deprecation warning when using `yield` tag with no matching section and no fallback. [#7149](https://github.com/statamic/cms/issues/7149) by @xuneXTW
- Add authorization to "duplicate" actions. [#7150](https://github.com/statamic/cms/issues/7150) by @jasonvarga
- Avoid wrapping of date fields in listings. [#7146](https://github.com/statamic/cms/issues/7146) by @jackmcdade
- Allows localization variable to be saved as false. [#7087](https://github.com/statamic/cms/issues/7087) by @tao



## 3.3.59 (2022-11-29)

### What's new
- Ability to duplicate entries, terms, assets, and forms. [#6307](https://github.com/statamic/cms/issues/6307) by @duncanmcclean
- Ability to use HTTP auth support to the `static:warm` command. [#7115](https://github.com/statamic/cms/issues/7115) by @moritzlang
- Ability for addons to set their fieldset namespace. [#7105](https://github.com/statamic/cms/issues/7105) by @edalzell

### What's improved
- Added a typehint to the `schedule` method in addon service providers. [#7081](https://github.com/statamic/cms/issues/7081) by @robbanl
- Improve the speed of the updater page. [#7140](https://github.com/statamic/cms/issues/7140) by @arthurperton

### What's fixed
- Fix invalid slug validation when single-depth orderable collections. [#7134](https://github.com/statamic/cms/issues/7134) by @arthurperton
- Prevent error when creating entry while using multisite [#7143](https://github.com/statamic/cms/issues/7143) by @jasonvarga
- The link fieldtype will localize appropriately. [#7093](https://github.com/statamic/cms/issues/7093) by @arthurperton
- Fix search on users listing when storing users in the database and using separate first/last name fields. [#7138](https://github.com/statamic/cms/issues/7138) by @duncanmcclean
- Fix static caching error when using exclusion URLs without leading slashes. [#7130](https://github.com/statamic/cms/issues/7130) by @arthurperton
- Fix issue where it looked like asset fields would disappear. [#7131](https://github.com/statamic/cms/issues/7131) by @arthurperton
- Fix case insensitive entry URLs. [#7103](https://github.com/statamic/cms/issues/7103) by @jasonvarga
- Fix super user handling within JavaScript based permission checks. [#7101](https://github.com/statamic/cms/issues/7101) by @jasonvarga
- Add `psr_autoloading` rule to `pint.json`. [#7142](https://github.com/statamic/cms/issues/7142) by @jasonvarga



## 3.3.58 (2022-11-21)

### What's new
- Ability to disable generating asset preset manipulations on upload. [#7076](https://github.com/statamic/cms/issues/7076) by @ryanmitchell

### What's improved
- Norwegian translations. [#7092](https://github.com/statamic/cms/issues/7092) by @espenlg

### What's fixed
- Prevent error in Static Caching middleware when using JSON responses. [#7075](https://github.com/statamic/cms/issues/7075) by @FrittenKeeZ
- Prevent dates being added to localized entries in non-dated collections. [#7086](https://github.com/statamic/cms/issues/7086) by @tao
- Support for JsonResource::withoutWrapping. [#7072](https://github.com/statamic/cms/issues/7072) by @jhhazelaar
- Prevent error in form route binding when customizing action route. [#7083](https://github.com/statamic/cms/issues/7083) by @julesjanssen
- Fix incorrect home icon and slug in entry listings. [#7095](https://github.com/statamic/cms/issues/7095) by @jasonvarga
- Prevent entire `assets:generate-presets` command failing when encountering invalid images. [#7091](https://github.com/statamic/cms/issues/7091) by @ryatkins



## 3.3.57 (2022-11-18)

### What's new
- Ability to add namespaced fieldsets. [#6588](https://github.com/statamic/cms/issues/6588) by @jasonvarga

### What's improved
- German translations. [#7074](https://github.com/statamic/cms/issues/7074) by @helloDanuk
- French translations. [#7084](https://github.com/statamic/cms/issues/7084) by @ebeauchamps
- Dutch translations. [#7073](https://github.com/statamic/cms/issues/7073) by @oakeddev

### What's fixed
- ~~Fix root page for max depth 1.~~ Reverted in 3.3.58 [#4895](https://github.com/statamic/cms/issues/4895) by @jelleroorda



## 3.3.56 (2022-11-14)

### What's new
- Split permissions for assigning roles/groups and editing them. [#6614](https://github.com/statamic/cms/issues/6614) by @ryanmitchell
- User creation wizard can have the email step disabled. [#7062](https://github.com/statamic/cms/issues/7062) by @jasonvarga
- Allow filtering by blueprints in `entries` fieldtype. [#7047](https://github.com/statamic/cms/issues/7047) by @FrittenKeeZ

### What's improved
- Dutch Translations [#7053](https://github.com/statamic/cms/issues/7053) by @oakeddev

### What's fixed
- Fix grid table header position inside stacks. [#7061](https://github.com/statamic/cms/issues/7061) by @jackmcdade
- Fix empty template fieldtype dropdown style. [#7060](https://github.com/statamic/cms/issues/7060) by @jackmcdade
- Fix lowercasing of asset folder names to match user's configuration. [#7055](https://github.com/statamic/cms/issues/7055) by @jesseleite
- Make sure SVGs are rendered in collection listing. [#7059](https://github.com/statamic/cms/issues/7059) by @jackmcdade



## 3.3.55 (2022-11-14)

### What's new
- Support custom date formats in route definitions. [#7043](https://github.com/statamic/cms/issues/7043) by @bencarr

### What's improved
- Better widget card wrapping on small screens. [#7036](https://github.com/statamic/cms/issues/7036) by @jackmcdade
- Add an icon to the form widget. [#7034](https://github.com/statamic/cms/issues/7034) by @jackmcdade

### What's fixed
- Fix namespaced view paths not being reset correctly. [#7051](https://github.com/statamic/cms/issues/7051) by @jasonvarga
- Use translation for 'All'. [#7040](https://github.com/statamic/cms/issues/7040) by @jasonvarga



## 3.3.54 (2022-11-09)

### What's improved
- Support Query Builder values in reverse modifier [#7006](https://github.com/statamic/cms/issues/7006) by @stvnthomas

### What's fixed
- Fix root page validation error logic [#7028](https://github.com/statamic/cms/issues/7028) by @jasonvarga
- Fix handling of last_login when being cast to a Carbon instance on the User model. [#7009](https://github.com/statamic/cms/issues/7009) by @dannydinges
- Reset additional view paths after request. [#7030](https://github.com/statamic/cms/issues/7030) by @jasonvarga



## 3.3.53 (2022-11-08)

### What's improved
- German translations. [#7013](https://github.com/statamic/cms/issues/7013) by @helloDanuk
- French translations [#7008](https://github.com/statamic/cms/issues/7008) by @ebeauchamps

### What's fixed
- Fix `parent` and `updated_by` fields not being shallow augmented in API, sometimes causing recursion. [#7025](https://github.com/statamic/cms/issues/7025) by @jasonvarga
- Fix a couple of "root page cannot have children" issues. [#7007](https://github.com/statamic/cms/issues/7007) by @jasonvarga
- Fix failing test due to orchestra/testbench update. [#7017](https://github.com/statamic/cms/issues/7017) by @jasonvarga



## 3.3.52 (2022-11-04)

### What's fixed
- Add missing validation for `id` being a reserved field handle. [#7005](https://github.com/statamic/cms/issues/7005) by @jasonvarga
- Fix Replicator/Grid/Bard not displaying newly added items after saving. [#7000](https://github.com/statamic/cms/issues/7000) by @jasonvarga
- Reset asset pagination when moving to another folder or container. [#6997](https://github.com/statamic/cms/issues/6997) by @jasonvarga



## 3.3.51 (2022-11-02)

### What's new
- Ability to bypass entry localization confirmation modal. [#6983](https://github.com/statamic/cms/issues/6983) by @jasonvarga

### What's fixed
- Prevent Antlers logging non-loopable variable warnings for certain tags. [#6989](https://github.com/statamic/cms/issues/6989) by @JohnathonKoster



## 3.3.50 (2022-11-01)

### What's new
- Added a confirmation modal for selecting the origin site when creating an entry localization. [#6943](https://github.com/statamic/cms/issues/6943) by @arthurperton
- Add Turkish translation. [#6963](https://github.com/statamic/cms/issues/6963) by @sineld
- The delimiter can be configured for Form CSV exports. [#6964](https://github.com/statamic/cms/issues/6964) by @theLeroy

### What's improved
- Improve configuration options for the `slug` fieldtype. [#6978](https://github.com/statamic/cms/issues/6978) by @jackmcdade
- The `first` modifier now supports associative arrays. [#6977](https://github.com/statamic/cms/issues/6977) by @royvanv

### What's fixed
- Clicking the "toggle all" checkbox will select the proper amount of items if max selections have been restricted. [#6816](https://github.com/statamic/cms/issues/6816) by @ncla
- Prevent the `assets:meta` command from wiping data. [#6854](https://github.com/statamic/cms/issues/6854) by @ncla
- The `@nocache` Blade directive properly handles additional data passed to it. [#6934](https://github.com/statamic/cms/issues/6934) by @jacksleight
- Fix compatibility with Laravel's `Str::slug()` method. [#6981](https://github.com/statamic/cms/issues/6981) by @jasonvarga
- Fix Runtime Antlers issue when using recursion in the `nav` tag. [#6968](https://github.com/statamic/cms/issues/6968) by @JohnathonKoster
- Fix the `relative` modifier's "extra words" parameter. [#6976](https://github.com/statamic/cms/issues/6976) by @jacksleight
- Fix subtraction in Antlers. [#6970](https://github.com/statamic/cms/issues/6970) by @JohnathonKoster
- The entry publish form will be updated with server-side values on save. [#6842](https://github.com/statamic/cms/issues/6842) by @arthurperton
- Replace deprecated `utf8_encode` method usage. [#6823](https://github.com/statamic/cms/issues/6823) by @krzysztofrewak



## 3.3.49 (2022-10-26)

### What's improved
- Dutch translations. [#6953](https://github.com/statamic/cms/issues/6953) by @robdekort
- German translations [#6944](https://github.com/statamic/cms/issues/6944) by @helloDanuk

### What's fixed
- Fix Replicator and Bard collapsed set state issues. [#6902](https://github.com/statamic/cms/issues/6902) by @jesseleite
- Fix Antlers issue when using nested tag loops in some situations. [#6894](https://github.com/statamic/cms/issues/6894) by @JohnathonKoster
- Fix shorthand array syntax in Antlers. [#6939](https://github.com/statamic/cms/issues/6939) by @JohnathonKoster
- Fix leaking of locale and carbon string format outside of requests. [#6945](https://github.com/statamic/cms/issues/6945) by @jasonvarga
- Added methods to `GlobalSet` contract. [#6938](https://github.com/statamic/cms/issues/6938) by @Z3d0X
- Fix `@nocache` Blade directive serialization errors. [#6935](https://github.com/statamic/cms/issues/6935) by @jacksleight
- Fix error when using custom primary key for `users` database table. [#6919](https://github.com/statamic/cms/issues/6919) by @jhhazelaar
- Swapped forum for GitHub Discussions in the readme. [#6946](https://github.com/statamic/cms/issues/6946) by @robdekort
- Adjusted grammar in assets config file. [#6936](https://github.com/statamic/cms/issues/6936) by @JohnathonKoster



## 3.3.48 (2022-10-20)

### What's new
- Add support for computed values. [#6179](https://github.com/statamic/cms/issues/6179) by @jesseleite

### What's fixed
- Fix overly aggressive static cache invalidation. [#6927](https://github.com/statamic/cms/issues/6927) by @jasonvarga



## 3.3.47 (2022-10-19)

### What's fixed
- Fix id being used for slug when creating entries. [#6921](https://github.com/statamic/cms/issues/6921) by @jasonvarga



## 3.3.46 (2022-10-19)

### What's new
- Support binding a custom `LocalizedTerm` class. [#6910](https://github.com/statamic/cms/issues/6910) by @jacksleight

### What's improved
- Improve performance of relationship fields. [#6909](https://github.com/statamic/cms/issues/6909) by @wiebkevogel
- Improve performance of textarea fields. [#6907](https://github.com/statamic/cms/issues/6907) by @wiebkevogel

### What's fixed
- Disable password protection when in live preview. [#6856](https://github.com/statamic/cms/issues/6856) by @duncanmcclean
- Prevent recursion when generating slug from autogenerated title. [#6903](https://github.com/statamic/cms/issues/6903) by @jasonvarga
- Order user fields by name. [#6896](https://github.com/statamic/cms/issues/6896) by @ryanmitchell
- Fix taxonomy term field filters not working. [#6900](https://github.com/statamic/cms/issues/6900) by @jacksleight
- Fix asset grid listing in narrow containers. [#6888](https://github.com/statamic/cms/issues/6888) by @jacksleight
- Fix variable collision on full measure static cache nocache JS code. [#6895](https://github.com/statamic/cms/issues/6895) by @ryanmitchell



## 3.3.45 (2022-10-14)

### What's new
- Use a `users` search index in the control panel if one exists. [#6886](https://github.com/statamic/cms/issues/6886) by @jasonvarga

### What's improved
- French translations [#6881](https://github.com/statamic/cms/issues/6881) by @ebeauchamps

### What's fixed
- Avoid caching tokenized REST API requests. [#6806](https://github.com/statamic/cms/issues/6806) by @notnek
- Asset title could use a field named title if one exists. [#6884](https://github.com/statamic/cms/issues/6884) by @jasonvarga
- Fix error when updating asset references in empty bard fields. [#6825](https://github.com/statamic/cms/issues/6825) by @AndrewHaine
- Fix nocache placeholder appearing in responses. [#6838](https://github.com/statamic/cms/issues/6838) by @arthurperton
- Invalidate statically cached urls with query strings. [#6866](https://github.com/statamic/cms/issues/6866) by @jasonvarga
- Fix dirty state when switching sites in entries. [#6861](https://github.com/statamic/cms/issues/6861) by @arthurperton
- Fix issue where things wouldn't get updated appropriately when running in a queue. [#6726](https://github.com/statamic/cms/issues/6726) by @jesseleite



## 3.3.44 (2022-10-13)

### What's new
- Added JavaScript event on nocache replacement. [#6828](https://github.com/statamic/cms/issues/6828) by @DanielDarrenJones
- Added `input_label` field to revealer config. [#6850](https://github.com/statamic/cms/issues/6850) by @jacksleight
- Added `flip` parameter to `glide` tag. [#6852](https://github.com/statamic/cms/issues/6852) by @jacksleight
- Added Edit Blueprint option to term publish form. [#6851](https://github.com/statamic/cms/issues/6851) by @jacksleight

### What's improved
- Auto-focus the field when opening a rename action modal. [#6858](https://github.com/statamic/cms/issues/6858) by @ncla
- Using `status` for a field handle will now validate as a reserved word. [#6857](https://github.com/statamic/cms/issues/6857) by @jasonvarga

### What's fixed
- Fixed an asset performance issue (especially when using S3) by waiting to load metadata until necessary. [#6871](https://github.com/statamic/cms/issues/6871) by @jasonvarga
- Fixed JS error when using nocache tags on full measure static caching and with a CSRF token starting with a number. [#6855](https://github.com/statamic/cms/issues/6855) by @jasonvarga
- Fix `date` fieldtype format handling in listings. [#6845](https://github.com/statamic/cms/issues/6845) by @granitibrahimi
- Fix Bard's "Save as HTML" setting label. [#6849](https://github.com/statamic/cms/issues/6849) by @jackmcdade



## 3.3.43 (2022-10-05)

### What's improved
- Command registration in starter kit post-install hooks. [#6807](http://github.com/statamic/cms/issues/6807) by @jesseleite

### What's fixed
- Fix starter kit post-install for statamic/cli users in Windows. [#6830](http://github.com/statamic/cms/issues/6830) by @jesseleite
- Fix `stache:warm` and `stache:refresh` performance regression when using S3 assets. [#6835](http://github.com/statamic/cms/issues/6835) by @jesseleite



## 3.3.42 (2022-10-03)

### What's new
- Added a filter for the `users` fieldtype. [#6654](https://github.com/statamic/cms/issues/6654) by @jacksleight
- Add `width` to GraphQL form field type. [#6815](https://github.com/statamic/cms/issues/6815) by @duncanmcclean

### What's improved
- An exception is thrown if composer.lock is missing when finding the Statamic version. [#6808](https://github.com/statamic/cms/issues/6808) by @flolanger
- Resolve redirects through fieldtype. [#6562](https://github.com/statamic/cms/issues/6562) by @jacksleight
- German translations. [#6809](https://github.com/statamic/cms/issues/6809) by @dominikradl

### What's fixed
- Fix focal point not saving when asset blueprint has no fields [#6814](https://github.com/statamic/cms/issues/6814) by @ncla
- Fix asset meta bottleneck. [#6822](https://github.com/statamic/cms/issues/6822) by @jasonvarga
- Avoid prompt to make user when installing starter kit via statamic/cli. [#6810](https://github.com/statamic/cms/issues/6810) by @jesseleite



## 3.3.41 (2022-09-29)

### What's new
- Starter kit post-install hooks. [#6792](https://github.com/statamic/cms/issues/6792) by @jesseleite

### What's improved
- Asset's glide cache is busted when `focus` changes. [#6769](https://github.com/statamic/cms/issues/6769) by @edalzell
- Dutch translations. [#6805](https://github.com/statamic/cms/issues/6805) by @robdekort

### What's fixed
- Uppercase acronyms are left alone in the `title` modifier. [#6783](https://github.com/statamic/cms/issues/6783) by @joshuablum
- Fix `assets` fieldtype error. [#6799](https://github.com/statamic/cms/issues/6799) by @jacksleight
- Fix GitHub Action workflow to test on multiple Laravel versions. [#6801](https://github.com/statamic/cms/issues/6801) by @crynobone



## 3.3.40 (2022-09-27)

### What's improved
- French translations [#6780](https://github.com/statamic/cms/issues/6780) by @ebeauchamps

### What's fixed
- Fix asset selector grid columns. [#6790](https://github.com/statamic/cms/issues/6790) by @jacksleight
- Fix asset browse button inside link fieldtype. [#6788](https://github.com/statamic/cms/issues/6788) by @jacksleight
- Fix Bard set menu options not working. [#6779](https://github.com/statamic/cms/issues/6779) by @jacksleight



## 3.3.39 (2022-09-23)

### What's new
- Ability to replace assets by either changing references to another asset, or reuploading a file. [#4832](https://github.com/statamic/cms/issues/4832) by @jesseleite

### What's fixed
- Improve handling of invalid Replicator and Bard field data [#6708](https://github.com/statamic/cms/issues/6708) by @jacksleight
- Fix shallow Bard set drag and drop ghost in Chrome [#6776](https://github.com/statamic/cms/issues/6776) by @jacksleight



## 3.3.38 (2022-09-22)

### What's new
- Add single asset/folder actions to grid view. [#6677](https://github.com/statamic/cms/issues/6677) by @jacksleight
- Add `key_by` modifier. [#6763](https://github.com/statamic/cms/issues/6763) by @jasonvarga
- Add `glide:data_url` tag to generate data URLs. [#6753](https://github.com/statamic/cms/issues/6753) by @jacksleight
- Add `collapse` option to Bard fieldtype. [#6734](https://github.com/statamic/cms/issues/6734) by @jacksleight
- Add `cookie` tag [#6748](https://github.com/statamic/cms/issues/6748) by @ryanmitchell
- Add custom build directory and hot file to `vite` tag. [#6752](https://github.com/statamic/cms/issues/6752) by @joshuablum
- Add `files` fieldtype. [#6736](https://github.com/statamic/cms/issues/6736) by @jasonvarga

### What's improved
- Asset Browser thumbnail style now matches the fieldtype. [#6715](https://github.com/statamic/cms/issues/6715) by @jackmcdade
- Improve display of Grid and Replicator replicator preview. [#6733](https://github.com/statamic/cms/issues/6733) by @jacksleight
- SVGs get a better replicator preview, and ability to set alt text. [#6765](https://github.com/statamic/cms/issues/6765) by @jacksleight
- Spanish Translations. [#6761](https://github.com/statamic/cms/issues/6761) by @cesaramirez

### What's fixed
- Fix asset replicator preview. [#6732](https://github.com/statamic/cms/issues/6732) by @jacksleight
- Fix using prefixed variables in conditions. [#6760](https://github.com/statamic/cms/issues/6760) by @JohnathonKoster
- Fix action value processing. [#6754](https://github.com/statamic/cms/issues/6754) by @jasonvarga



## 3.3.37 (2022-09-20)

### What's new
- Add Portuguese Brazilian translation. [#6739](https://github.com/statamic/cms/issues/6739) by @rodrigomantoan

### What's improved
- German translations. [#6716](https://github.com/statamic/cms/issues/6716) by @helloDanuk

### What's fixed
- Fix live preview scroll position handling when url contains a port number. [#6742](https://github.com/statamic/cms/issues/6742) by @o1y
- Fix plus buttons spacing in Replicator. [#6722](https://github.com/statamic/cms/issues/6722) by @arthurperton
- Fix static cache invalidation. [#6720](https://github.com/statamic/cms/issues/6720) by @jasonvarga



## 3.3.36 (2022-09-13)

### What's new
- Initial support for default preferences. [#6642](https://github.com/statamic/cms/issues/6642) by @jesseleite

### What's improved
- Norwegian translations. [#6709](https://github.com/statamic/cms/issues/6709) by @hgrimelid
- Dutch translations. [#6699](https://github.com/statamic/cms/issues/6699) by @robdekort
- French translations. [#6690](https://github.com/statamic/cms/issues/6690) by @ebeauchamps

### What's fixed
- Existing user data gets merged when logging in using OAuth for the first time. [#6692](https://github.com/statamic/cms/issues/6692) by @arthurperton
- Fix multisite and queue related static caching issues. [#6621](https://github.com/statamic/cms/issues/6621) by @arthurperton
- Fix publish form tab not being set properly on load. [#6710](https://github.com/statamic/cms/issues/6710) by @jasonvarga
- Fix date fieldtype in range mode. [#6703](https://github.com/statamic/cms/issues/6703) by @jasonvarga
- Fix asset folder permissions. [#6698](https://github.com/statamic/cms/issues/6698) by @jasonvarga
- Don't show the asset picker when squished or inside a Grid. [#6701](https://github.com/statamic/cms/issues/6701) by @jackmcdade
- The cache tag will properly scope per site. [#6702](https://github.com/statamic/cms/issues/6702) by @arthurperton
- Fix sidebar on publish form being inaccessible. [#6694](https://github.com/statamic/cms/issues/6694) by @arthurperton
- Fix negative test assertion counts. [#6689](https://github.com/statamic/cms/issues/6689) by @jasonvarga
- Fix risky test warnings. [#6691](https://github.com/statamic/cms/issues/6691) by @jesseleite



## 3.3.35 (2022-09-09)

### What's new
- Ability to specify `search:results` query value rather than reading from the URL. [#6684](https://github.com/statamic/cms/issues/6684) by @jacksleight

### What's improved
- Improve performance and readability of publish components. [#6680](https://github.com/statamic/cms/issues/6680) by @arthurperton
- Grid row controls are hidden when there are no available actions. [#6647](https://github.com/statamic/cms/issues/6647) by @arthurperton

### What's fixed
- Fix styling of lists inside tables and blockquotes in Bard. [#6685](https://github.com/statamic/cms/issues/6685) by @jacksleight
- Fix empty live preview fields falling back to original values, by distinguishing supplemented nulls from missing values.  [#6666](https://github.com/statamic/cms/issues/6666) by @arthurperton
- Fix deleting an entry with children from the collection tree view. [#6644](https://github.com/statamic/cms/issues/6644) by @arthurperton
- Fix incorrect localized term collection URLs. [#6659](https://github.com/statamic/cms/issues/6659) by @AndrewHaine
- Fix date field default format not including time when `mode` isn't explicitly set. [#6657](https://github.com/statamic/cms/issues/6657) by @FrittenKeeZ
- Fix search within the asset browser not restricting to a folder where necessary. [#6673](https://github.com/statamic/cms/issues/6673) by @arthurperton
- Fix missing view nav authorization. [#6663](https://github.com/statamic/cms/issues/6663) by @ryanmitchell
- Fix blank URLs being flagged as external. [#6668](https://github.com/statamic/cms/issues/6668) by @arthurperton
- Fix JS error in Popover when resizing. [#6679](https://github.com/statamic/cms/issues/6679) by @arthurperton
- Fix JS error when downloading assets. [#6669](https://github.com/statamic/cms/issues/6669) by @jacksleight
- Fix JS error when resizing edit pages. [#6660](https://github.com/statamic/cms/issues/6660) by @arthurperton
- Fix 'Add child link to entry' appearing in nav builder when no collection is set. [#6672](https://github.com/statamic/cms/issues/6672) by @jacksleight
- Fix marking external CP navigation items as current. [#6655](https://github.com/statamic/cms/issues/6655) by @arthurperton
- Fix asset fieldtype drag mirror. [#6671](https://github.com/statamic/cms/issues/6671) by @jackmcdade
- Fix asset tile controls position. [#6656](https://github.com/statamic/cms/issues/6656) by @jackmcdade
- Fix date not showing in Safari. [#6651](https://github.com/statamic/cms/issues/6651) by @arthurperton
- Fix date picker value format. [#6688](https://github.com/statamic/cms/issues/6688) by @jasonvarga
- Fix augmentation test. [#6676](https://github.com/statamic/cms/issues/6676) by @jasonvarga



## 3.3.34 (2022-09-05)

### What's new
- Add support for downloading multiple assets as a zip. [#6606](https://github.com/statamic/cms/issues/6606) [#6626](https://github.com/statamic/cms/issues/6626) by @jacksleight
- Add support for filtering entries by taxonomy terms in the REST API. [#6615](https://github.com/statamic/cms/issues/6615) by @arthurperton

### What's improved
- Asset fieldtype UI/UX improvements such as ability to set missing alt attributes, and better thumbnails. [#6638](https://github.com/statamic/cms/issues/6638) by @jackmcdade
- Static caching: When saving a collection (or its tree), the configured collection urls will be invalidated. [#6636](https://github.com/statamic/cms/issues/6636) by @arthurperton
- Static caching excluded URLs treat trailing slashes as optional. [#6633](https://github.com/statamic/cms/issues/6633) by @arthurperton

### What's fixed
- Exclude `published` from data when saving entries. [#6641](https://github.com/statamic/cms/issues/6641) by @jasonvarga
- Show placeholder in form select fields. [#6637](https://github.com/statamic/cms/issues/6637) by @fjahn
- Avoid fieldtypes getting mounted twice. [#6632](https://github.com/statamic/cms/issues/6632) by @arthurperton
- Fix popper causing overflow. [#6628](https://github.com/statamic/cms/issues/6628) by @jasonvarga
- Fix missing permission translation keys being shown. [#6624](https://github.com/statamic/cms/issues/6624) by @jasonvarga
- Fix numeric separators JS error. [#6625](https://github.com/statamic/cms/issues/6625) by @jesseleite
- Fix asset data handling. [#6591](https://github.com/statamic/cms/issues/6591) by @jesseleite



## 3.3.33 (2022-08-31)

### What's new
- Add `page_name` parameter to support customizing the paginator in the `collection` tag. [#6593](https://github.com/statamic/cms/issues/6593) by @jacksleight
- Add `mark` modifiers for highlighting words in text. [#6574](https://github.com/statamic/cms/issues/6574) by @jacksleight
- Add "Download" action to Assets. [#6594](https://github.com/statamic/cms/issues/6594) by @ahinkle
- Add "Add Set" button at the top of the Replicator fieldtype. [#6586](https://github.com/statamic/cms/issues/6586) by @wiebkevogel
- Add ability to temporarily disable and re-enable an event subscriber. [#6577](https://github.com/statamic/cms/issues/6577) by @jesseleite

### What's improved
- Updating asset or term references will generate a single commit. [#6535](https://github.com/statamic/cms/issues/6535) by @jesseleite
- German translations [#6590](https://github.com/statamic/cms/issues/6590) by @helloDanuk
- French translations [#6578](https://github.com/statamic/cms/issues/6578) by @ebeauchamps

### What's fixed
- Fix form emails not using the appropriate site language. [#6607](https://github.com/statamic/cms/issues/6607) by @jasonvarga
- Fix asset renaming when selecting more than 2 assets. [#6585](https://github.com/statamic/cms/issues/6585) by @jesseleite
- Clone default blueprint before modifying them. [#6597](https://github.com/statamic/cms/issues/6597) by @arthurperton
- Fix memory leak regarding popper components. [#6601](https://github.com/statamic/cms/issues/6601) by @wiebkevogel
- Make Eloquent `paginate` `$columns` argument default to asterisk. [#6598](https://github.com/statamic/cms/issues/6598) by @jasonvarga
- Remove redundant user path config. [#6580](https://github.com/statamic/cms/issues/6580) by @ryanmitchell
- Bring back env value parsing in form email configs. [#6575](https://github.com/statamic/cms/issues/6575) by @jasonvarga
- Fix icon vertical alignment on collection view. [#6576](https://github.com/statamic/cms/issues/6576) by @jackmcdade
- Fix extra whitespace appearing in new check icon [#6571](https://github.com/statamic/cms/issues/6571) by @jacksleight



## 3.3.32 (2022-08-25)

### What's new
- Ability to invalidate urls when editing forms and their blueprints. [#6546](https://github.com/statamic/cms/issues/6546) by @arthurperton
- Ability to provide custom icon SVGs to Utilities. [#6556](https://github.com/statamic/cms/issues/6556) by @duncanmcclean
- Added a "View GraphQL" permission. [#6550](https://github.com/statamic/cms/issues/6550) by @ryanmitchell

### What's improved
- Added collection/taxonomy names to stack selector to more easily differentiate between similar entries. [#6518](https://github.com/statamic/cms/issues/6518) by @jacksleight
- Improve UX of filtering listing by toggle fields. [#6545](https://github.com/statamic/cms/issues/6545) by @jacksleight
- Improve display of toggle fields in listings. [#6541](https://github.com/statamic/cms/issues/6541) by @jacksleight
- Support setting entry blueprint using instance. [#6547](https://github.com/statamic/cms/issues/6547) by @jesseleite
- Support passing a form instance into tags. [#6544](https://github.com/statamic/cms/issues/6544) by @jasonvarga

### What's fixed
- Destroy key bindings from popover after closing. [#6566](https://github.com/statamic/cms/issues/6566) by @jonassiewertsen
- Destroy key bindings from revision history after closing. [#6565](https://github.com/statamic/cms/issues/6565) by @jonassiewertsen
- Destroy key bindings from modals after closing. [#6564](https://github.com/statamic/cms/issues/6564) by @jonassiewertsen
- Destroy key bindings in Bard fields. [#6555](https://github.com/statamic/cms/issues/6555) by @jonassiewertsen
- Destroy sortable instances in relationship fields. [#6563](https://github.com/statamic/cms/issues/6563) by @wiebkevogel
- Adjust debounce in date fields. [#6560](https://github.com/statamic/cms/issues/6560) by @zsoltjanes
- Fix modals and stacks not playing nicely together in some situations. [#6558](https://github.com/statamic/cms/issues/6558) by @jasonvarga
- Fix incorrect meta when using certain fieldtypes as config fields. [#6549](https://github.com/statamic/cms/issues/6549) by @arthurperton
- Fix multi-site namespaced view overrides. [#6539](https://github.com/statamic/cms/issues/6539) by @edalzell
- Collect eloquent roles on sync to preserve passing them as an array. [#6540](https://github.com/statamic/cms/issues/6540) by @ryanmitchell



## 3.3.31 (2022-08-19)

### What's new
- Add `user_roles` tag. [#6517](https://github.com/statamic/cms/issues/6517) by @ryanmitchell
- Add `user_groups` tag. [#6505](https://github.com/statamic/cms/issues/6505) by @ryanmitchell
- Add `user` scope on `cache` tag. [#6515](https://github.com/statamic/cms/issues/6515) by @ryanmitchell

### What's fixed
- Compatibility for EloquentQueryBuilder. [#5844](https://github.com/statamic/cms/issues/5844) by @ryanmitchell
- Fix double `SubmissionCreated` event. [#6532](https://github.com/statamic/cms/issues/6532) by @ryanmitchell
- Fix `user:reset_password_form` tag action. [#6527](https://github.com/statamic/cms/issues/6527) by @jasonvarga
- Fix type in root page field instructions. [#6512](https://github.com/statamic/cms/issues/6512) by @sjclark



## 3.3.30 (2022-08-17)

### What's new
- Add `always_save` field config to allow overriding of conditional field data flow. [#6387](https://github.com/statamic/cms/issues/6387) by @jesseleite

### What's improved
- Deleting assets and terms will update their references, and added an option to disable the feature. [#6504](https://github.com/statamic/cms/issues/6504) by @jesseleite

### What's fixed
- Fix saving of `statamic://` links in Bard when saving as html. [#6511](https://github.com/statamic/cms/issues/6511) by @jesseleite
- Fix `revealer` handling when multiple field conditions are being evaluated. [#6443](https://github.com/statamic/cms/issues/6443) by @jesseleite
- Prevent Glide routes adding unnecessary cookies. [#6502](https://github.com/statamic/cms/issues/6502) by @schwartzmj



## 3.3.29 (2022-08-15)

### What's new
- Added `bard_text`, `bard_html`, and `bard_items` modifiers. [#6226](https://github.com/statamic/cms/issues/6226) by @jacksleight
- Added `antlers` modifier. [#6489](https://github.com/statamic/cms/issues/6489) by @jasonvarga
- Added `blueprint` to augmented entry data. [#6015](https://github.com/statamic/cms/issues/6015) by @jacksleight
- Ability to add Form JS drivers to an addon's service provider. [#6499](https://github.com/statamic/cms/issues/6499) by @jacksleight

### What's improved
- Use singular nouns in default blueprint handles and titles. [#5941](https://github.com/statamic/cms/issues/5941) by @jacksleight

### What's fixed
- Fix stretched SVG thumbnails. [#6500](https://github.com/statamic/cms/issues/6500) by @jacksleight
- Fix depth issues when using recursive children in Runtime Antlers. [#6490](https://github.com/statamic/cms/issues/6490) by @JohnathonKoster
- Fix `raw` modifier on fields with `antlers: true` in Runtime Antlers. [#6484](https://github.com/statamic/cms/issues/6484) by @JohnathonKoster
- Fix extra margin on narrow date only fields [#6435](https://github.com/statamic/cms/issues/6435) by @jacksleight
- Fix error when filtering by collection in CP entry listing with multiple collections. [#5915](https://github.com/statamic/cms/issues/5915) by @psyao



## 3.3.28 (2022-08-11)

### What's new
- Provide full form data when using form tags in Blade. [#5892](https://github.com/statamic/cms/issues/5892) by @jacksleight
- Form emails are sent in separate jobs, and you can override the class in order to customize retries, backoff, etc. [#6481](https://github.com/statamic/cms/issues/6481) by @okaufmann

### What's fixed
- Fix asset versioning path conflicts. [#6444](https://github.com/statamic/cms/issues/6444) by @jonassiewertsen



## 3.3.27 (2022-08-09)

### What's improved
- Auto generated entry titles get trimmed. [#6473](https://github.com/statamic/cms/issues/6473) by @aerni
- Reorderable select field options are styled with grab handles. [#6451](https://github.com/statamic/cms/issues/6451) by @jacksleight
- Added phpdoc type information to AddonServiceProvider. [#6465](https://github.com/statamic/cms/issues/6465) by @j6s

### What's fixed
- Fix terms not resolving query builders (e.g. nested entries or terms) in GraphQL. [#6379](https://github.com/statamic/cms/issues/6379) by @boydseltenrijch
- Fix `locales` tag not outputting collection specific term URLs. [#6466](https://github.com/statamic/cms/issues/6466) by @jasonvarga
- Fix methods getting called more than expected in Antlers. [#6458](https://github.com/statamic/cms/issues/6458) by @JohnathonKoster
- Prevent `theme:output` tag from rendering files outside of the `resources` directory. [#6456](https://github.com/statamic/cms/issues/6456) by @jasonvarga
- Remove redundant `orWhere` methods in entry and term query builders. [#6460](https://github.com/statamic/cms/issues/6460) by @ryanmitchell



## 3.3.26 (2022-08-08)

### What's new
- Add option to remove empty Bard nodes. [#6438](https://github.com/statamic/cms/issues/6438), [#6447](https://github.com/statamic/cms/issues/6447) by @aerni
- Add `saveQuietly` method and `afterSave` callbacks to `Taxonomy` and `Submission` classes. [#6427](https://github.com/statamic/cms/issues/6427) by @duncanmcclean
- Add revisions option to Collection configuration form. [#6426](https://github.com/statamic/cms/issues/6426) by @jackmcdade

### What's fixed
- Revert "Fix parameters not being available within partial slots" added in 3.3.25 as it was discovered to be a breaking change. [#6463](https://github.com/statamic/cms/issues/6463) by @jasonvarga
- Fix a couple of docs URLs. [#6461](https://github.com/statamic/cms/issues/6461) by @jackmcdade
- Augment submission data in email config. [#6424](https://github.com/statamic/cms/issues/6424) by @aerni
- Fix incorrect nav depth when using a partial multiple times. [#6440](https://github.com/statamic/cms/issues/6440) by @JohnathonKoster
- Fix modifiers not being applied to method invocation without parenthesis. [#6416](https://github.com/statamic/cms/issues/6416) by @JohnathonKoster
- Fix creating new empty arrays in Antlers. [#6437](https://github.com/statamic/cms/issues/6437) by @JohnathonKoster
- Forms: Fix double parsing in email configs, some other small fixes, and add test coverage. [#6464](https://github.com/statamic/cms/issues/6464) by @jasonvarga
- Make empty grid/replicator/bard fields be indexed as null instead of empty arrays. [#6428](https://github.com/statamic/cms/issues/6428) by @jasonvarga
- Add vscode directory to gitignore [#6430](https://github.com/statamic/cms/issues/6430) by @MarvelousMartin



## 3.3.25 (2022-08-01)

### What's fixed
- Fix assets not being attached to, or listed in form submission emails. [#6408](https://github.com/statamic/cms/issues/6408) by @jacksleight
- The `embed_url` modifier handles unlisted Vimeo URLs. [#6413](https://github.com/statamic/cms/issues/6413) by @ryanmitchell
- The `toggle` field's `inline_label` option uses markdown. [#6412](https://github.com/statamic/cms/issues/6412) by @jesseleite
- ~~Fix parameters not being available within partial slots.~~ Reverted in 3.3.26 [#6405](https://github.com/statamic/cms/issues/6405) by @JohnathonKoster
- Fix aggressiveness of html minification when rendering form fields. [#6394](https://github.com/statamic/cms/issues/6394) by @jesseleite
- Fix password reset for unactivated user accounts. [#6406](https://github.com/statamic/cms/issues/6406) by @jasonvarga
- Bump `moment` from 2.29.2 to 2.29.4 [#6410](https://github.com/statamic/cms/issues/6410) by @dependabot
- Composer steps in the GitHub test workflow will retry on a failure. [#6409](https://github.com/statamic/cms/issues/6409) by @jasonvarga



## 3.3.24 (2022-07-26)

### What's improved
- Always include `no_results` in tags that output items. [#6368](http://github.com/statamic/cms/issues/6368) by @jacksleight
- Dutch translations. [#6364](http://github.com/statamic/cms/issues/6364) by @erwinromkes

### What's fixed
- Fix `rememberWithExpiration` cache macro not found error. [#6380](http://github.com/statamic/cms/issues/6380) by @JohnathonKoster
- Fix word count in `CoreModifiers` treating punctuation as a word. [#6367](http://github.com/statamic/cms/issues/6367) by @johnnoel
- Fix reordering entries in a collection named literally `collection`. [#6371](http://github.com/statamic/cms/issues/6371) by @jasonvarga
- Deprecate `format_localized` modifier. [#6370](http://github.com/statamic/cms/issues/6370) by @jasonvarga



## 3.3.23 (2022-07-21)

### What's new
- Added Statamic information to the new "about" command. [#6351](https://github.com/statamic/cms/issues/6351) by @jasonvarga
- Custom fields can be added to the Collection GraphQL type. [#6362](https://github.com/statamic/cms/issues/6362) by @jasonvarga

### What's fixed
- Fix toggle field casting to boolean when field is hidden. [#6348](https://github.com/statamic/cms/issues/6348) by @jesseleite
- Allow validation against `published` value on entry publish form. [#6353](https://github.com/statamic/cms/issues/6353) by @jesseleite
- Runtime: Fix error when using modulo in a condition. [#6363](https://github.com/statamic/cms/issues/6363) by @JohnathonKoster
- Fix versioned asset path having a double extension. [#6346](https://github.com/statamic/cms/issues/6346) by @jonassiewertsen
- Fix infinite loop when user file doesn't have id. [#6361](https://github.com/statamic/cms/issues/6361) by @jasonvarga
- Fix assets fieldtype being sortable when read-only. [#6358](https://github.com/statamic/cms/issues/6358) by @jesseleite
- Clean up leftover files in global set test. [#6350](https://github.com/statamic/cms/issues/6350) by @jasonvarga



## 3.3.22 (2022-07-18)

### What's improved
- An error is logged when contact with the Outpost fails. [#6341](https://github.com/statamic/cms/issues/6341) by @jasonvarga



## 3.3.21 (2022-07-18)

### What's new
- Add `saveQuietly` methods and save-related events to a number of classes. [#3379](https://github.com/statamic/cms/issues/3379) [#6339](https://github.com/statamic/cms/issues/6339) by @duncanmcclean @jesseleite
- Ability to replace extra ascii characters in slugs etc. [#5496](https://github.com/statamic/cms/issues/5496) by @FrittenKeeZ
- The `templates` fieldtype can add a "map to blueprint" option. It's used on the collection configuration form. [#6337](https://github.com/statamic/cms/issues/6337) by @FrittenKeeZ

### What's improved
- Improve vendor asset cache-busting. [#6312](https://github.com/statamic/cms/issues/6312) by @jonassiewertsen



## 3.3.20 (2022-07-13)

### What's new
- Ability to keep sections dynamic while using static caching. Adds a `nocache` tag. [#6231](https://github.com/statamic/cms/issues/6231) by @jasonvarga

### What's fixed
- Fix breadcrumb translation issue. [#6331](https://github.com/statamic/cms/issues/6331) by @jasonvarga



## 3.3.19 (2022-07-12)

### What's new
- Ability to use a class to determine static cache url exclusions. [#5469](https://github.com/statamic/cms/issues/5469) by @FrittenKeeZ
- Ability to hide Replicator/Bard set field previews. [#6022](https://github.com/statamic/cms/issues/6022) by @edalzell
- Added Czech translation. [#6320](https://github.com/statamic/cms/issues/6320) by @MarvelousMartin

### What's improved
- French translations. [#6311](https://github.com/statamic/cms/issues/6311) by @ebeauchamps
- Dutch translations [#6305](https://github.com/statamic/cms/issues/6305) by @robdekort
- Use Laravel Pint for code formatting. [#6298](https://github.com/statamic/cms/issues/6298) by @jesseleite

### What's fixed
- Runtime: Fix issue when using `recursive children` multiple times. [#6321](https://github.com/statamic/cms/issues/6321) by @JohnathonKoster
- Runtime: Fix dynamic binding and query builders. [#6324](https://github.com/statamic/cms/issues/6324) by @JohnathonKoster
- Fix long lines/words in code mirror breaking the layout width. [#6316](https://github.com/statamic/cms/issues/6316) by @jacksleight
- Fix bard nested table styling. [#6315](https://github.com/statamic/cms/issues/6315) by @jacksleight
- Fix finding data by request url when theres no root-based site. [#6306](https://github.com/statamic/cms/issues/6306) by @jasonvarga
- Tag state gets reset between subsequent requests. [#6193](https://github.com/statamic/cms/issues/6193) by @JohnathonKoster



## 3.3.18 (2022-07-05)

### What's new
- Added `vite` tag. [#6271](https://github.com/statamic/cms/issues/6271) by @lokmanm
- Add column selector to users listing. [#6185](https://github.com/statamic/cms/issues/6185) by @jesseleite
- Allow assets to trigger static caching invalidation. [#5489](https://github.com/statamic/cms/issues/5489) by @FrittenKeeZ
- Allow `templates` fieldtype to be scoped to a folder. [#6222](https://github.com/statamic/cms/issues/6222) by @jacksleight
- Pagination size options are configurable. [#6215](https://github.com/statamic/cms/issues/6215) by @jonassiewertsen
- Add `visibility` field config with new `hidden` option. [#5958](https://github.com/statamic/cms/issues/5958) by @jesseleite
- Add `truncate` method to collections and taxonomies. [#6220](https://github.com/statamic/cms/issues/6220) by @jacksleight
- Add open and download buttons for readonly assets. [#6299](https://github.com/statamic/cms/issues/6299) by @RafaelKr

### What's improved
- Improved Live preview scroll position consistency. [#6295](https://github.com/statamic/cms/issues/6295) by @ryanmitchell
- Runtime: Allows parameters to begin with numbers. [#6288](https://github.com/statamic/cms/issues/6288) by @JohnathonKoster
- Add file size to `assets` fieldtype row tooltip. [#6294](https://github.com/statamic/cms/issues/6294) by @ExpDev07
- View class is more easily extendable. [#6272](https://github.com/statamic/cms/issues/6272) by @dimitri-koenig
- German translations. [#6297](https://github.com/statamic/cms/issues/6297) by @helloDanuk

### What's fixed
- Fix status on newly propagated entries. [#5684](https://github.com/statamic/cms/issues/5684) by @j3ll3yfi5h
- Don't show hidden blueprints on the `entries` fieldtype. [#6285](https://github.com/statamic/cms/issues/6285) by @duncanmcclean
- Fix blueprint parent not being set. [#5999](https://github.com/statamic/cms/issues/5999) by @aerni
- Do not remove dots from URLs. [#5468](https://github.com/statamic/cms/issues/5468) by @Konafets
- Only attempt to scroll live preview when on the same origin. [#6282](https://github.com/statamic/cms/issues/6282) by @jasonvarga
- Remove pagination totals in widgets and selectors. [#6279](https://github.com/statamic/cms/issues/6279) by @jacksleight
- Update search indexes for assets, terms, and users. [#6044](https://github.com/statamic/cms/issues/6044) by @okaufmann
- Use `implode()` instead of `join()` alias to appease style fixers. [#6300](https://github.com/statamic/cms/issues/6300) by @jesseleite



## 3.3.17 (2022-06-30)

### What's new
- Add item totals to CP listing pagination. [#6244](https://github.com/statamic/cms/issues/6244) by @jacksleight
- Allow addons to add root-only Bard node extensions. [#6202](https://github.com/statamic/cms/issues/6202) by @jacksleight

### What's improved
- Maintain iframe scroll position in live preview. [#6206](https://github.com/statamic/cms/issues/6206) by @ryanmitchell
- Augment date fields to Illuminate\Support\Carbon. [#6218](https://github.com/statamic/cms/issues/6218) by @ryanmitchell
- Moved data finding controller logic into repository. [#6235](https://github.com/statamic/cms/issues/6235) by @jasonvarga
- French translations. [#6204](https://github.com/statamic/cms/issues/6204) by @ebeauchamps

### What's fixed
- Add context to all bulk action listings. [#6247](https://github.com/statamic/cms/issues/6247) by @jacksleight
- Fix bard JSON error when reordering replicator sets. [#6265](https://github.com/statamic/cms/issues/6265) by @jacksleight
- Fix revealer tracking when re-ordering bard/replicator sets. [#6261](https://github.com/statamic/cms/issues/6261) by @jesseleite
- Runtime: Fix scope reset bug with conditions under very specific circumstances. [#6258](https://github.com/statamic/cms/issues/6258) by @JohnathonKoster
- Fix publish date with times. [#5870](https://github.com/statamic/cms/issues/5870) [#6236](https://github.com/statamic/cms/issues/6236) by @jasonvarga, @edalzell
- Support QueryBuilder values in shuffle modifier. [#6219](https://github.com/statamic/cms/issues/6219) by @ryanmitchell
- Fix publish path of addon translations. [#6214](https://github.com/statamic/cms/issues/6214) by @aerni
- Using config type fallback. [#6207](https://github.com/statamic/cms/issues/6207) by @sliesensei
- Replace reference to FlysystemFileNotFoundException. [#6054](https://github.com/statamic/cms/issues/6054) by @michaelr0
- Fix `locales` tag when using Live Preview. [#6263](https://github.com/statamic/cms/issues/6263) by @edalzell



## 3.3.16 (2022-06-13)

### What's new
- Actions can be added to the form listing. [#5845](https://github.com/statamic/cms/issues/5845) by @duncanmcclean
- Ability to specify default sort order for asset containers. [#6171](https://github.com/statamic/cms/issues/6171) by @jacksleight

### What's improved
- Updated Norwegian translation. [#6195](https://github.com/statamic/cms/issues/6195) by @hgrimelid

### What's fixed
- Fix JS error when a field has a number. [#6187](https://github.com/statamic/cms/issues/6187) by @jasonvarga
- Fix `get_error` tag outputting something when there are other errors. [#6201](https://github.com/statamic/cms/issues/6201) by @jasonvarga
- Fix AMP not working on home page. [#6184](https://github.com/statamic/cms/issues/6184) by @jasonvarga
- Fix issue where site specific views aren't used for taxonomies. [#6146](https://github.com/statamic/cms/issues/6146) by @edalzell
- Fix awkward validation error message. [#6188](https://github.com/statamic/cms/issues/6188) by @jasonvarga



## 3.3.15 (2022-06-09)

### What's new
- Add `to_bool` modifier. [#6159](https://github.com/statamic/cms/issues/6159) by @edalzell

### What's fixed
- Rename `mount` tag to `mount_url` so it doesn't clash with variable. [#6181](https://github.com/statamic/cms/issues/6181) by @jacksleight
- Fix user recursion issue in Control Panel. [#6163](https://github.com/statamic/cms/issues/6163) by @jasonvarga



## 3.3.14 (2022-06-08)

### What's new
- Added "single" mode for the `array` fieldtype. [#6141](https://github.com/statamic/cms/issues/6141) by @jackmcdade
- Added `mount` tag. (Renamed to `mount_url` in 3.3.15) [#6038](https://github.com/statamic/cms/issues/6038) by @jacksleight

### What's improved
- Asset filenames are now lowercased by default. [#6031](https://github.com/statamic/cms/issues/6031) by @jesseleite
- The CP text direction is based on the selected site instead of the default. [#6154](https://github.com/statamic/cms/issues/6154) by @jasonvarga

### What's fixed
- Fix `users` fieldtype search and add pagination. [#6096](https://github.com/statamic/cms/issues/6096) by @ryanmitchell
- Fix route binding for broadcasting [#6169](https://github.com/statamic/cms/issues/6169) by @jasonvarga
- Fix parser error when using Blade [#6106](https://github.com/statamic/cms/issues/6106) by @jacksleight
- Fix issue where calling `saveQuietly` on an entry stops events firing. [#6086](https://github.com/statamic/cms/issues/6086) by @ryanmitchell
- Fix route bindings when CP route is empty. [#6149](https://github.com/statamic/cms/issues/6149) by @ryanmitchell
- Fix static caching invalidation for collections. [#6145](https://github.com/statamic/cms/issues/6145) by @edalzell
- Runtime: Prevent taxonomies from overwriting the current scope. [#6153](https://github.com/statamic/cms/issues/6153) by @JohnathonKoster
- Runtime: Fix grid fields not being pluckable. [#6174](https://github.com/statamic/cms/issues/6174) by @JohnathonKoster
- Runtime: Fix variables created in PHP not staying in scope. [#6155](https://github.com/statamic/cms/issues/6155) by @JohnathonKoster
- Runtime: Fix parameter escaping. [#6158](https://github.com/statamic/cms/issues/6158) by @JohnathonKoster



## 3.3.13 (2022-06-02)

### What's new
- Ability to duplicate Replicator and Bard sets. [#6111](https://github.com/statamic/cms/issues/6111) by @jacksleight
- Add `pathinfo` and `parse_url` modifiers. [#6078](https://github.com/statamic/cms/issues/6078) by @jacksleight
- Add `actionUrl` method to `Form` class. [#6113](https://github.com/statamic/cms/issues/6113) by @michaelr0
- Ability to disable pretty Debugbar variables for performance. [#6094](https://github.com/statamic/cms/issues/6094) by @simonhamp
- Runtime: Ability to control stack content whitespace, and access stack contents as an array. [#6110](https://github.com/statamic/cms/issues/6110) by @JohnathonKoster

### What's improved
- Improve Bard replicator preview. [#6098](https://github.com/statamic/cms/issues/6098) by @jacksleight
- Runtime: improves interpolated variable dynamic access [#6109](https://github.com/statamic/cms/issues/6109) by @JohnathonKoster
- Improve blueprint performance. [#6108](https://github.com/statamic/cms/issues/6108) by @jasonvarga
- Alphabetize the `dd` & `dump` modifier output. [#6121](https://github.com/statamic/cms/issues/6121) by @edalzell
- Improve active toggle field state when it's read-only.  [#6079](https://github.com/statamic/cms/issues/6079) by @jacksleight
- Update French translations. [#6120](https://github.com/statamic/cms/issues/6120) by @ebeauchamps
- Update Norwegian translations. [#6066](https://github.com/statamic/cms/issues/6066) by @hgrimelid

### What's fixed
- Support query strings in `Path::extension()`. [#6132](https://github.com/statamic/cms/issues/6132) by @ryanmitchell
- Fix static caching invalidation when using separate domains. [#6138](https://github.com/statamic/cms/issues/6138) by @jasonvarga
- Fix error when using taxonomy terms fieldtype with multiple taxonomies. [#6103](https://github.com/statamic/cms/issues/6103) by @ryanmitchell
- Support query builder in `chunk` modifier. [#6084](https://github.com/statamic/cms/issues/6084) by @ryanmitchell
- Ensure route bindings only apply to Statamic routes. [#5775](https://github.com/statamic/cms/issues/5775) by @ryanmitchell
- Runtime: Fix variable scope across parser boundaries. [#6139](https://github.com/statamic/cms/issues/6139) by @JohnathonKoster
- Runtime: Reset stack state when rendering final view. [#6140](https://github.com/statamic/cms/issues/6140) by @JohnathonKoster
- Runtime: Remove literal/stack replacements on 404/error. [#6073](https://github.com/statamic/cms/issues/6073) by @JohnathonKoster
- Runtime: Corrects assignments across query builder scopes. [#6136](https://github.com/statamic/cms/issues/6136) by @JohnathonKoster
- Runtime: improve variable parsing and Builder array plucking [#5902](https://github.com/statamic/cms/issues/5902) by @JohnathonKoster
- Bump eventsource from 1.1.0 to 1.1.1 [#6128](dhttps://github.com/statamic/cms/issues/6128) by @dependabot
- Remove unnecessary code from the Glide tag. [#6087](https://github.com/statamic/cms/issues/6087) by @ryanmitchell
- Tidy up some more Blade echo statements. [#6126](https://github.com/statamic/cms/issues/6126) by @michaelr0



## 3.3.12 (2022-05-18)

### What's improved
- Add `toggle` mode to revealer fieldtype. [#6052](http://github.com/statamic/cms/issues/6052) by @jesseleite
- Allow eloquent query builder to accept page parameters. [#6056](http://github.com/statamic/cms/issues/6056) by @ryanmitchell
- Clean up deprecations. [#6014](http://github.com/statamic/cms/issues/6014) by @jasonvarga
- Expect the `RevisionContract` inside WorkingCopy. [#6017](http://github.com/statamic/cms/issues/6017) by @jonassiewertsen
- Disable SSL certificate verification locally for `static:warm` command. [#6028](http://github.com/statamic/cms/issues/6028) by @FrittenKeeZ

### What's fixed
- Fix for revealers nested within grids with `mode: stacked` enabled. [#6047](http://github.com/statamic/cms/issues/6047) by @jesseleite
- Fix file uploads in front end form submissions. [#6061](http://github.com/statamic/cms/issues/6061) by @jesseleite


## 3.3.11 (2022-05-10)

### What's improved
- Swedish translations. [#5975](http://github.com/statamic/cms/issues/5975) by @adevade
- Remove unused assets (mostly `.png`s). [#6006](http://github.com/statamic/cms/issues/6006) by @adevade
- Update default url to use `https` in markdown fields. [#5971](http://github.com/statamic/cms/issues/5971) by @adevade
- Enable spellcheck in markdown fields, improve CodeMirror field accessibility. [#6016](http://github.com/statamic/cms/issues/6016) by @jacksleight
- Allow overwriting the `getAddon` method. [#5935](http://github.com/statamic/cms/issues/5935) by @jonassiewertsen

### What's fixed
- Fix hidden data when replicator sets are collapsed by default. [#6021](http://github.com/statamic/cms/issues/6021) by @jesseleite
- Fix adding new s3 folder to asset listing cache with stache watcher disabled. [#5996](http://github.com/statamic/cms/issues/5996) by @jesseleite
- Fix overzealous deleting from asset listing cache with stache watcher disabled. [#5998](http://github.com/statamic/cms/issues/5998) by @jesseleite
- Move route to fix live preview pop out. [#5968](http://github.com/statamic/cms/issues/5968) by @jasonvarga
- Fix limit and offset on ordered query builders. [#5932](http://github.com/statamic/cms/issues/5932) by @jasonvarga
- Fix indexing of query builder results. [#5961](http://github.com/statamic/cms/issues/5961) by @jasonvarga
- Runtime: Fix numeric variables inside parameters and shares cascade data. [#5995](http://github.com/statamic/cms/issues/5995) by @JohnathonKoster
- Runtime: Evaluate Antlers within `code` fieldtype when `antlers: true` is set. [#5966](http://github.com/statamic/cms/issues/5966) by @JohnathonKoster


## 3.3.10 (2022-04-29)

### What's new
- Add `str_pad` modifiers. [#5920](https://github.com/statamic/cms/issues/5920) by @FrittenKeeZ
- Add 'Copy URL' action to assets. [#5901](https://github.com/statamic/cms/issues/5901) by @duncanmcclean

### What's improved
- Swedish translations. [#5948](https://github.com/statamic/cms/issues/5948) by @adevade
- French translations. [#5903](https://github.com/statamic/cms/issues/5903) by @ebeauchamps
- Runtime: Better line and char numbers when reporting interpolation errors. [#5930](https://github.com/statamic/cms/issues/5930) by @JohnathonKoster
- Move the HTML fieldtype into the proper "special" category. [#5959](https://github.com/statamic/cms/issues/5959) by @jackmcdade
- Enable Glide presets to use watermarks. [#5925](https://github.com/statamic/cms/issues/5925) by @wesort
- Asset browser uses HasActions mixin. [#5912](https://github.com/statamic/cms/issues/5912) by @jasonvarga

### What's fixed
- Runtime: Corrects parameter style modifier scope overwriting issue. [#5885](https://github.com/statamic/cms/issues/5885) by @JohnathonKoster
- Runtime: Prevent variables from leaking out of tags. [#5884](https://github.com/statamic/cms/issues/5884) by @JohnathonKoster
- Runtime: Adds support for uppercase logical keywords. [#5911](https://github.com/statamic/cms/issues/5911) by @JohnathonKoster
- Add backwards compatibility to entry edit url. [#5924](https://github.com/statamic/cms/issues/5924) by @jonassiewertsen
- Fix hidden code field. [#5923](https://github.com/statamic/cms/issues/5923) by @jasonvarga
- Hook up CodeMirror's `direction` config property. [#5957](https://github.com/statamic/cms/issues/5957) by @jackmcdade
- Update data when replicator set is toggled. [#5898](https://github.com/statamic/cms/issues/5898) by @edalzell
- Bump composer/composer dependency. [#5921](https://github.com/statamic/cms/issues/5921) by @jasonvarga
- Bump async from 2.6.3 to 2.6.4. [#5922](https://github.com/statamic/cms/issues/5922) by @dependabot


## 3.3.9 (2022-04-21)

### What's new
- Add “small” Bard button. [#5876](https://github.com/statamic/cms/pull/5876) by @edalzell

### What's improved
- Updated Russian translations. [#5879](https://github.com/statamic/cms/pull/5879) by @dragomano

### What's fixed
- Runtime: Fix conditions checking the Cascade. [#5875](https://github.com/statamic/cms/pull/5875) by @JohnathonKoster
- Fix submitted data when fields are hidden by revealer fieldtype. [#5878](https://github.com/statamic/cms/pull/5878) by @jesseleite
- Fix hidden field tracking in stacked grid rows. [#5877](https://github.com/statamic/cms/issues/5877) by @jesseleite


## 3.3.8 (2022-04-20)

### What's new
- Glide generated images can be stored on any filesystem. [#5725](https://github.com/statamic/cms/issues/5725) by @jasonvarga
- Glide supports watermarks. [#5725](https://github.com/statamic/cms/issues/5725) by @jasonvarga
- Actions can run JavaScript. [#5854](https://github.com/statamic/cms/issues/5854) by @duncanmcclean
- Ability to copy a password reset link from the user listing dropdown menus. [#5854](https://github.com/statamic/cms/issues/5854) by @duncanmcclean
- Ability to provide additional preview targets. [#5549](https://github.com/statamic/cms/issues/5549) by @aerni
- Simplified Chinese translations. [#5847](https://github.com/statamic/cms/issues/5847) by @grandpalacko

### What's improved
- Starter kits will require multiple dependencies at once. [#5859](https://github.com/statamic/cms/issues/5859) by @jesseleite
- Updated Dutch translations. [#5867](https://github.com/statamic/cms/issues/5867) by @robdekort
- Runtime: Optimized `noparse` parsing. [#5848](https://github.com/statamic/cms/issues/5848) by @fdabek1

### What's fixed
- Fix issue where adding an image to Bard will make an needless, erroring AJAX request. [#5864](https://github.com/statamic/cms/issues/5864) by @fdabek1
- Fix issue where the `terms` fieldtype filters out too much. [#5865](https://github.com/statamic/cms/issues/5865) by @jasonvarga
- Fix `group_by` modifier not working with grids. [#5858](https://github.com/statamic/cms/issues/5858) by @jasonvarga
- Runtime: Preserve builder instances when being supplied in dynamic bindings and modifiers, or when they've been scoped/nested. [#5807](https://github.com/statamic/cms/issues/5807) by @fdabek1
- Runtime: Fix single letter variables without spaces skipping HTML content. [#5843](https://github.com/statamic/cms/issues/5843) by @fdabek1
- Fix validation error when attempting to create a taxonomy that has the same handle as a collection. [#5850](https://github.com/statamic/cms/issues/5850) by @grandpalacko



## 3.3.7 (2022-04-14)

### What's new
- The `toggle` fieldtype is available in frontend forms. [#5789](https://github.com/statamic/cms/issues/5789) by @jesseleite
- Added an `inline_label` option to the `toggle` fieldtype. [#5789](https://github.com/statamic/cms/issues/5789) by @jesseleite

### What's improved
- User avatars in the CP header are cropped appropriately. [#5731](https://github.com/statamic/cms/issues/5731) by @ncla

### What's fixed
- Fix handling of hidden nested fields in publish forms. [#5805](https://github.com/statamic/cms/issues/5805) by @jesseleite
- Runtime: Prevent modifiers on tag pairs being evaluated twice. [#5828](https://github.com/statamic/cms/issues/5828) by @JohnathonKoster
- Runtime: Prevent a log when looping over a null. [#5832](https://github.com/statamic/cms/issues/5832) by @JohnathonKoster
- Adjusted docs link in French translation. [#5835](https://github.com/statamic/cms/issues/5835) by @ebeauchamps
- Another ReturnTypeWillChange annotation. [#5839](https://github.com/statamic/cms/issues/5839) by @jasonvarga



## 3.3.6 (2022-04-11)

### What's new
- The Markdown `Parser` class is macroable. [#5797](https://github.com/statamic/cms/issues/5797) by @jacksleight

### What's improved
- Dutch translations. [#5801](https://github.com/statamic/cms/issues/5801) by @robdekort
- Norwegian translations. [#5798](https://github.com/statamic/cms/issues/5798) by @espenlg

### What's fixed
- The `nav:breadcrumbs` tag can now be used in Blade. [#5599](https://github.com/statamic/cms/issues/5599) by @jasonvarga
- Collections are counted more efficiently in the `length` modifier. [#5802](https://github.com/statamic/cms/issues/5802) by @jasonvarga
- Fix issue when using a single `assets` field named `asset`. [#5799](https://github.com/statamic/cms/issues/5799) by @jasonvarga
- Fix `entries` fieldtype in select mode pushing the content area wider. [#5787](https://github.com/statamic/cms/issues/5787) by @jackmcdade
- Runtime: Support for multiple interpolations in tag method names. [#5800](https://github.com/statamic/cms/issues/5800) by @JohnathonKoster
- Runtime: Isolate scope when resolving values to prevent overriding page data. [#5668](https://github.com/statamic/cms/issues/5668) by @JohnathonKoster
- Runtime: Correct self closing tags. [#5781](https://github.com/statamic/cms/issues/5781) by @JohnathonKoster
- Runtime: Fix issue when using recursion and conditions. [#5779](https://github.com/statamic/cms/issues/5779) by @JohnathonKoster
- Runtime: Fix `noparse` behavior. [#5778](https://github.com/statamic/cms/issues/5778) by @JohnathonKoster
- Runtime: Fix modifier chains and null values. [#5780](https://github.com/statamic/cms/issues/5780) by @JohnathonKoster
- Runtime: Prevent literal from being removed when using multiple nested double braces in a parameter. [#5777](https://github.com/statamic/cms/issues/5777) by @JohnathonKoster
- Runtime: Removes ctype_space null deprecation warning. [#5776](https://github.com/statamic/cms/issues/5776) by @JohnathonKoster
- Bump `minimist` from 1.2.5 to 1.2.6 [#5808](https://github.com/statamic/cms/issues/5808) by @dependabot
- Bump `moment` from 2.27.0 to 2.29.2 [#5806](https://github.com/statamic/cms/issues/5806) by @dependabot



## 3.3.5 (2022-04-06)

### What's new
- Norwegian translation. [#5722](https://github.com/statamic/cms/issues/5722) by @espenlg
- When there's a syntax error inside an Antlers-enabled field, the exception will show the contents. [#5659](https://github.com/statamic/cms/issues/5659) by @JohnathonKoster

### What's improved
- German translations. [#5715](https://github.com/statamic/cms/issues/5715) by @helloDanuk
- French translations. [#5705](https://github.com/statamic/cms/issues/5705) by @ebeauchamps

### What's fixed
- Fix runtime parser not maintaining query builder results accurately in subsequent tags. [#5716](https://github.com/statamic/cms/issues/5716) by @JohnathonKoster
- Fix the `groupby` operator not supporting modifiers in the runtime parser. [#5716](https://github.com/statamic/cms/issues/5716) by @JohnathonKoster
- Fix runtime parser outputting a replacement string when a stack is used with nothing pushed into it. [#5716](https://github.com/statamic/cms/issues/5716) by @JohnathonKoster
- Fix runtime parser support for multibyte characters. [#5704](https://github.com/statamic/cms/issues/5704) by @JohnathonKoster
- Fix runtime parser support for tags with parameters in Antlers-enabled fields. [#5659](https://github.com/statamic/cms/issues/5659) by @JohnathonKoster
- Fix Glide cache disk permissions. [#5724](https://github.com/statamic/cms/issues/5724) by @jasonvarga
- Fix URL typo in Dutch translation. [#5772](https://github.com/statamic/cms/issues/5772) by @MarcelWeidum
- Fix slugification not using appropriate language on the create term form. [#5738](https://github.com/statamic/cms/issues/5738) by @arthurperton
- Fix issue where Flysystem could return pathless FileAttributes response. [#5726](https://github.com/statamic/cms/issues/5726) by @jesseleite
- Fix runtime parser support for PHP assignments inside loops. [#5734](https://github.com/statamic/cms/issues/5734) by @JohnathonKoster
- Add attribute to suppress deprecation notice [#5719](https://github.com/statamic/cms/issues/5719) by @marcorieser
- Add ReturnTypeWillChange attribute [#5701](https://github.com/statamic/cms/issues/5701) by @jasonvarga



## 3.3.4 (2022-03-30)

### What's improved
- The `site` filter's badge will display the name instead of the handle. [#5683](https://github.com/statamic/cms/issues/5683) by @j3ll3yfi5h
- Show a more graceful error when hitting a rate limit on the licensing page. [#5678](https://github.com/statamic/cms/issues/5678) by @jesseleite
- The `users` fieldtype will show the first/last name in listings when applicable. [#5677](https://github.com/statamic/cms/issues/5677) by @j3ll3yfi5h
- The "Site selected." toast message is localizable. [#5687](https://github.com/statamic/cms/issues/5687) by @j3ll3yfi5h

### What's fixed
- Fix issue where the `date` fieldtype could not be cleared. [#5682](https://github.com/statamic/cms/issues/5682) by @potentweb
- The cache is bypassed for tokenized GraphQL requests. [#5693](https://github.com/statamic/cms/issues/5693) by @jasonvarga
- Fix 404 handling for entries/terms in the REST API. [#5690](https://github.com/statamic/cms/issues/5690) by @notnek
- Fix an error when you have a comment at the start of a `partial` slot. [#5651](https://github.com/statamic/cms/issues/5651) by @JohnathonKoster
- Fix issue where the updates badge shows the wrong count in some cases. [#5678](https://github.com/statamic/cms/issues/5678) by @jesseleite
- Fix asset editor overflow issues. [#5689](https://github.com/statamic/cms/issues/5689) by @jackmcdade
- Fix issue where collections were unnecessarily converted to arrays in modifiers. [#5642](https://github.com/statamic/cms/issues/5642) by @JohnathonKoster
- A handful more Antlers bug fxies. [#5571](https://github.com/statamic/cms/issues/5571) by @JohnathonKoster
- Tidy up some Blade echo statements. [#5645](https://github.com/statamic/cms/issues/5645) by @michaelr0
- Fix the initial height of textareas. [#5649](https://github.com/statamic/cms/issues/5649) by @wiebkevogel
- Prevent slug error when used on forms without containers. [#5657](https://github.com/statamic/cms/issues/5657) by @jasonvarga
- Fix Global Variables in GraphQL not resolving query builders [#5640](https://github.com/statamic/cms/issues/5640) by @jasonvarga
- Upgraded to PHP CS Fixer v3. [#5541](https://github.com/statamic/cms/issues/5541) by @jesseleite



## 3.3.3 (2022-03-24)

### What's new
- Live Preview is supported in the REST API. [#5623](https://github.com/statamic/cms/issues/5623) by @jasonvarga
- Query builders can accept page name and number. [#5602](https://github.com/statamic/cms/issues/5602) by @mattmurtaugh
- Added `csrf` parameter to `form` tag. [#5626](https://github.com/statamic/cms/issues/5626) by @robdekort
- Added `has_focus` variable to assets. [#5638](https://github.com/statamic/cms/issues/5638) by @jasonvarga
- Added `playtime` variable to assets. [#5586](https://github.com/statamic/cms/issues/5586) by @jackmcdade

### What's improved
- Redesigned Search Index Utility. [#5625](https://github.com/statamic/cms/issues/5625) by @jackmcdade
- Removed primary style on the Asset browser's "Create Container" button. [#5629](https://github.com/statamic/cms/issues/5629) by @jackmcdade
- Expanded Windows testing. [#5613](https://github.com/statamic/cms/issues/5613) by @jesseleite
- Updated Swedish translations. [#5615](https://github.com/statamic/cms/issues/5615) [#5620](https://github.com/statamic/cms/issues/5620) [#5617](https://github.com/statamic/cms/issues/5617) [#5616](https://github.com/statamic/cms/issues/5616) by @jannejava
- Made asset editor full width. [#5608](https://github.com/statamic/cms/issues/5608) by @jackmcdade
- Asset fields will auto-select the container when only one exists. [#5498](https://github.com/statamic/cms/issues/5498) by @duncanmcclean

### What's fixed
- The `to_json` modifier can handle query builders. [#5635](https://github.com/statamic/cms/issues/5635) by @jasonvarga
- Fix `is_external` check for anchors. [#5631](https://github.com/statamic/cms/issues/5631) by @grandpalacko
- Fix Asset Editor not showing HTML5 players. [#5607](https://github.com/statamic/cms/issues/5607) by @jackmcdade



## 3.3.2 (2022-03-18)

### What's improved
- Update French translations. [#5540](https://github.com/statamic/cms/issues/5540) by @ebeauchamps
- Link to automatic titles section in the docs. [#5553](https://github.com/statamic/cms/issues/5553) by @jackmcdade

### What's fixed
- Prevent filtering users by password hashes in the APIs. [#5568](https://github.com/statamic/cms/issues/5568) by @jasonvarga
- Prevent usage of `null` as filesystem paths. [#5562](https://github.com/statamic/cms/issues/5562) by @jasonvarga
- The `glide` tag pair can handle query builders. [#5552](https://github.com/statamic/cms/issues/5552) by @jasonvarga
- Fix Flysystem 3 support in Glide. [#5551](https://github.com/statamic/cms/issues/5551) by @jasonvarga
- Make `isset` on properties work. [#5530](https://github.com/statamic/cms/issues/5530) by @jasonvarga
- Range fields can now use `0` as a value or default. [#5538](https://github.com/statamic/cms/issues/5538) by @jasonvarga



## 3.3.1 (2022-03-16)

### What's new
- Ability to make fields read only through the Blueprint builder. [#5379](https://github.com/statamic/cms/issues/5379) by @jackmcdade

### What's improved
- Update German translations. [#5511](https://github.com/statamic/cms/issues/5511) by @helloDanuk

### What's fixed
- Fix issue in Runtime parser where augmenting within an expression could alter the scope. [#5525](https://github.com/statamic/cms/issues/5525) by @JohnathonKoster
- Fix Grid fields not converting to JSON by making `Values` implement `JsonSerializable`. [#5524](https://github.com/statamic/cms/issues/5524) by @jasonvarga
- Fix error when determining slug language on non-entry forms. [#5523](https://github.com/statamic/cms/issues/5523) by @jasonvarga
- Fix nested query builder access in Regex parser. [#5521](https://github.com/statamic/cms/issues/5521) by @jasonvarga
- Fix Runtime parser not supporting dashes in variable names. [#5516](https://github.com/statamic/cms/issues/5516) by @JohnathonKoster
- Fix Flysystem error on Cache Manager utility page. [#5509](https://github.com/statamic/cms/issues/5509) by @jesseleite



## 3.3.0 (2022-03-15)

### What's new
- Official 3.3 release! 🎉

### What's changed
- Removed unused `toCacheableArray` methods from `Entry`, `AssetContainer`, and `User`. [#5506](https://github.com/statamic/cms/issues/5506) by @jasonvarga



## 3.3.0-beta.7 (2022-03-14)

### What's fixed
- Another handful of Antlers Runtime Bugfixes. [#5477](https://github.com/statamic/cms/issues/5477) by @JohnathonKoster
- Fix drafts not being visible in Live Preview. [#5493](https://github.com/statamic/cms/issues/5493) by @jasonvarga
- Use tokens for determining if a request is Live Preview. [#5495](https://github.com/statamic/cms/issues/5495) by @jasonvarga
- Fix terms being filtered out unintentionally. [#5474](https://github.com/statamic/cms/issues/5474) by @jasonvarga
- Fix `ExtractInfo` size call between Laravel 8 and 9. [#5470](https://github.com/statamic/cms/issues/5470) by @jesseleite
- Modifiers convert `Values` objects to arrays [#5464](https://github.com/statamic/cms/issues/5464) by @jasonvarga
- The `pluck` modifier supports `ArrayAccess` types [#5462](https://github.com/statamic/cms/issues/5462) by @jasonvarga
- Regex parser can handle when tags return query builders [#5461](https://github.com/statamic/cms/issues/5461) by @jasonvarga
- Explicitly support colon syntax in `where` modifier to avoid a breaking change. [#5499](https://github.com/statamic/cms/issues/5499) by @jasonvarga

### What's changed
- Modifiers in parameter syntax have pipe-delimited arguments. [#5477](https://github.com/statamic/cms/issues/5477) by @JohnathonKoster



## 3.3.0-beta.6 (2022-03-09)

### What's new
- Add `Values` wrapper and use it in Grid, Replicator, and Bard. Allows for a nicer syntax in Blade. [#5436](https://github.com/statamic/cms/issues/5436) by @jasonvarga

### What's fixed
- Fix error when entries etc are used in conditions. [#5443](https://github.com/statamic/cms/issues/5443) by @jasonvarga
- Two handfuls of Antlers Runtime parser bugs. [#5438](https://github.com/statamic/cms/issues/5438) [#5428](https://github.com/statamic/cms/issues/5428) by @JohnathonKoster
- Allow a `terms` fieldtype value to be passed into the `collection` tag's taxonomy parameter. [#5445](https://github.com/statamic/cms/issues/5445) by @jasonvarga
- Suppress PHP 8.1 deprecation warnings. [#5444](https://github.com/statamic/cms/issues/5444) by @jasonvarga
- Bump minimum `league/commonmark` version. [#5434](https://github.com/statamic/cms/issues/5434) by @jesseleite
- Bump `ajthinking/archetype` to v1. [#5440](https://github.com/statamic/cms/issues/5440) by @ajthinking
- Bring over pending changes from 3.2

### What's changed
- Grid/Replicator/Bard now augment to `Values` instance rather than arrays. [#5436](https://github.com/statamic/cms/issues/5436) by @jasonvarga



## 3.3.0-beta.5 (2022-03-07)

### What's new
- More control over how values should be stored for querying. e.g. `date` fieldtypes store `Carbon` instances. [#5156](https://github.com/statamic/cms/issues/5156) by @jasonvarga
- Support date where clauses in the query builder. [#4753](https://github.com/statamic/cms/issues/4753) by @ryanmitchell

### What improved
- Improved test coverage for modifiers. [#5282](https://github.com/statamic/cms/issues/5282) [#5341](https://github.com/statamic/cms/issues/5341) by @Konafets

### What's fixed
- A handful of Antlers Runtime parser bugs. [#5408](https://github.com/statamic/cms/issues/5408) by @JohnathonKoster
- Brought over changes from 3.2

### What's changed
- Custom `date` fields are stored in the Stache as `Carbon` instances, not strings. [#5156](https://github.com/statamic/cms/issues/5156) by @jasonvarga



## 3.3.0-beta.4 (2022-03-04)

### What's new
- Allow escaped braces inside tag parameters and string literals. [#5394](https://github.com/statamic/cms/issues/5394) by @JohnathonKoster

### What's fixed
- Update the Live Preview content area size when you change the device dropdown. [#5390](https://github.com/statamic/cms/issues/5390) by @jasonvarga
- Fix error when opening inline publish form. [#5392](https://github.com/statamic/cms/issues/5392) by @jasonvarga
- Brought over changes from 3.2

### What's changed
- Assets, Terms, and Users fieldtypes augment to query builders. [#5388](https://github.com/statamic/cms/issues/5388) by @jasonvarga
- Change references of `Statamic 3` to just `Statamic`. [#5404](https://github.com/statamic/cms/issues/5404) by @jackmcdade



## 3.3.0-beta.3 (2022-03-02)

### What's new
- Added an "Ordered" Query Builder decorator that will apply a predefined order to the results. [#5381](https://github.com/statamic/cms/issues/5381) by @jasonvarga
- The `static:warm` command may leverage the queue. [#5272](https://github.com/statamic/cms/issues/5272) by @arthurperton
- Add hidden blueprint indicators. [#5342](https://github.com/statamic/cms/issues/5342) by @jackmcdade
- Added `next` and `prev` loop variables in the Antlers runtime parser. [#5351](https://github.com/statamic/cms/issues/5351) by @JohnathonKoster

### What's improved
- Dutch translations. [#5380](https://github.com/statamic/cms/issues/5380) by @robdekort
- Align entries/terms count columns to the right. [#5347](https://github.com/statamic/cms/issues/5347) by @jackmcdade

### What's fixed
- Fix a handful of things in the Antlers runtime parser. [#5351](https://github.com/statamic/cms/issues/5351) by @JohnathonKoster
- Fix email fields not being loopable. [#5375](https://github.com/statamic/cms/issues/5375) by @jasonvarga
- Fix typo in preview target help text. [#5373](https://github.com/statamic/cms/issues/5373) by @notnek
- Fix error when viewing form submission. [#5369](https://github.com/statamic/cms/issues/5369) by @jasonvarga
- Let `count`, `length`, and `pluck` modifiers handle query builders. [#5368](https://github.com/statamic/cms/issues/5368) by @jasonvarga
- Fix querying nested entries fields in GraphQL. [#5367](https://github.com/statamic/cms/issues/5367) by @jasonvarga
- Fix live preview when creating a term. [#5354](https://github.com/statamic/cms/issues/5354) by @jasonvarga
- Brought over changes from 3.2



## 3.3.0-beta.2 (2022-02-25)

### What's fixed
- Fixed issue where the resources directory was deleted during a composer install. [#5350](https://github.com/statamic/cms/issues/5350) by @jasonvarga



## 3.3.0-beta.1 (2022-02-25)

### What's new
- Brand new Antlers parser. [#4257](https://github.com/statamic/cms/issues/4257) by @JohnathonKoster
- Laravel 9 support. [#5188](https://github.com/statamic/cms/issues/5188) by @jesseleite
- Headless Live Preview. [#5109](https://github.com/statamic/cms/issues/5109) by @jasonvarga
- Frontend form field conditions. [#4949](https://github.com/statamic/cms/issues/4949) by @jesseleite
- Antlers wrapper for Blade. [#5058](https://github.com/statamic/cms/issues/5058) by @jesseleite
- Support Blade section yields in Antlers layouts. [#5056](https://github.com/statamic/cms/issues/5056) by @jasonvarga
- Added `Statamic::query()` aliases. [#5285](https://github.com/statamic/cms/issues/5285) by @jasonvarga
- Augmentables (Entry, Term, etc) can retrieve their augmented values via property access. [#5297](https://github.com/statamic/cms/issues/5297) by @jasonvarga
- Augmentables can retrieve their augmented values via array access. [#5327](https://github.com/statamic/cms/issues/5327) by @jasonvarga
- Augmentables implement `Arrayable`, and will return all their augmented values via the `toArray` method. [#5186](https://github.com/statamic/cms/issues/5186) by @jasonvarga

### What's fixed
- CP forms only submit visible fields, in order to fix sometimes/required_if/etc validation rules. [#5101](https://github.com/statamic/cms/issues/5101) by @jesseleite

### What's changed
- PHP 7.2 and 7.3 are no longer supported.
- Laravel 6 and 7 are no longer supported.
- Entries fieldtypes augment to query builders instead of collections. [#5238](https://github.com/statamic/cms/issues/5238) by @jasonvarga
- The `page` and each global in the cascade are now instances of the page/global. [#5334](https://github.com/statamic/cms/issues/5334) by @jasonvarga
- The `Augmented` interface's `get` method now has a return typehint of `Value`. [#5302](https://github.com/statamic/cms/issues/5302) by @jasonvarga
- `$item->augmentedValue()`, `toAugmentedCollection()`, and `toAugmentedArray()` will always return `Value` instances. [#5302](https://github.com/statamic/cms/issues/5302) by @jasonvarga
- Form submission data will always be an unfiltered Collection. [#5230](https://github.com/statamic/cms/issues/5230) by @jasonvarga
- AssetContainer, Collection, Form, and Taxonomy's `toArray` methods return different data. [#5186](https://github.com/statamic/cms/issues/5186) by @jasonvarga



## 3.2.38 (2022-03-14)

### What's new
- Support `whereJsonContains`, `whereJsonDoesntContain`, and `whereJsonLength` in the query builder. [#4707](https://github.com/statamic/cms/issues/4707) by @ryanmitchell
- Add ability to select assets in the `link` fieldtype. [#4647](https://github.com/statamic/cms/issues/4647) by @jacksleight
- Add `origin_id` to augmented entry data. [#5455](https://github.com/statamic/cms/issues/5455) by @jasonvarga

### What's improved
- Updated French translations. [#5465](https://github.com/statamic/cms/issues/5465) by @ebeauchamps
- Slugification is language-aware. [#5494](https://github.com/statamic/cms/issues/5494) by @jasonvarga

### What's fixed
- Fix division by zero error when calculating asset ratio. [#5488](https://github.com/statamic/cms/issues/5488) by @arthurperton
- Register Variables debugbar collector once. [#5448](https://github.com/statamic/cms/issues/5448) by @michaelr0
- Fix `link` fieldtype not updating when switching sites. [#5447](https://github.com/statamic/cms/issues/5447) by @jasonvarga
- Fix a few asset issues. [#5433](https://github.com/statamic/cms/issues/5433) by @jasonvarga
- Fix `nav` tag's `is_current` and `is_parent` variables. [#5292](https://github.com/statamic/cms/issues/5292) by @arthurperton



## 3.2.37 (2022-03-07)

### What's improved
- Updated Swedish translations. [#5421](https://github.com/statamic/cms/issues/5421) by @jannejava

### What's fixed
- Fix validation related error in Grid fields. [#5424](https://github.com/statamic/cms/issues/5424) by @jasonvarga
- Fix user wizard icon size. [#5423](https://github.com/statamic/cms/issues/5423) by @duncanmcclean
- Fix rare GitHub auth error output in the `starter-kit:install` command. [#5410](https://github.com/statamic/cms/issues/5410) by @jesseleite



## 3.2.36 (2022-03-04)

### What's new
- The `route` tag can be used with a `name` parameter. [#5407](https://github.com/statamic/cms/issues/5407) by @ryanmitchell
- The CP updater will prevent you upgrading to a version that could require manual changes. [#5401](https://github.com/statamic/cms/issues/5401) by @jasonvarga

### What's fixed
- Namespaced translation methods [#5144](https://github.com/statamic/cms/issues/5144) by @jasonvarga
- The `range` fieldtype will save integers. [#5391](https://github.com/statamic/cms/issues/5391) by @jackmcdade



## 3.2.35 (2022-03-01)

### What's fixed
- Fixed casing of `pdfobject` import.



## 3.2.34 (2022-03-01)

### What's new
- New PDF viewer that doesn't rely on Google Docs and allows previewing of PDFs in private containers. [#5349](https://github.com/statamic/cms/issues/5349) by @edalzell
- Add duration to asset augmentation. [#5265](https://github.com/statamic/cms/issues/5265) by @schmidex
- Form validation rules are queryable in GraphQL. [#5344](https://github.com/statamic/cms/issues/5344) by @arthurperton
- Support `when`, `unless`, and `tap` in the query builder. [#5090](https://github.com/statamic/cms/issues/5090) by @ryanmitchell
- Support `taxonomy:term:not="something"` syntax. [#5206](https://github.com/statamic/cms/issues/5206) by @jackmcdade
- Add ability to filter navigation trees. [#5085](https://github.com/statamic/cms/issues/5085) by @arthurperton
- The `entry` GraphQL query now supports the `filter` argument. [#5119](https://github.com/statamic/cms/issues/5119) by @strebl
- Add Malaysian translation. [#5326](https://github.com/statamic/cms/issues/5326) by @zunnurs01

### What's improved
- Improve messaging around GitHub auth errors when installing Starter Kits. [#5374](https://github.com/statamic/cms/issues/5374) by @jesseleite
- Added a notification about an updated CLI tool when installing Starter Kits. [#5338](https://github.com/statamic/cms/issues/5338) by @jesseleite
- Provide a helpful exception if a site doesn't exist when viewing a collection. [#5336](https://github.com/statamic/cms/issues/5336) by @jackmcdade
- Update Swedish translations. [#5330](https://github.com/statamic/cms/issues/5330) [#5329](https://github.com/statamic/cms/issues/5329) [#5328](https://github.com/statamic/cms/issues/5328) by @jannejava
- Update German translations. [#5321](https://github.com/statamic/cms/issues/5321) by @helloDanuk
- Update French translations. [#5261](https://github.com/statamic/cms/issues/5261) by @ebeauchamps

### What's fixed
- Fix issue where items would always be placed at the end of the tree when using numeric IDs (i.e. the Eloquent driver). [#5283](https://github.com/statamic/cms/issues/5283) by @ryanmitchell
- The `reverse` modifier will preserve keys when modifying a collection. [#5340](https://github.com/statamic/cms/issues/5340) by @Konafets
- Prevent replacements interfering with in `regex` validation rules. [#5345](https://github.com/statamic/cms/issues/5345) by @arthurperton
- Fix Grid UI when using date fields with time. [#5364](https://github.com/statamic/cms/issues/5364) by @arthurperton
- The `entry` GraphQL query will filter out drafts by default. [#5119](https://github.com/statamic/cms/issues/5119) by @strebl
- Fix incorrect query parameter replacement in the `video` fieldtype. [#5317](https://github.com/statamic/cms/issues/5317) by @arthurperton
- Remove null values from Grid. [#5284](https://github.com/statamic/cms/issues/5284) by @duncanmcclean
- The `collection` tag will respect the custom sort field and direction in the config.  [#5071](https://github.com/statamic/cms/issues/5071) by @ryanmitchell
- Remove unused parameters from call to `HTML::email()` [#5235](https://github.com/statamic/cms/issues/5235) by @Konafets
- Remove unused replicator method. [#5281](https://github.com/statamic/cms/issues/5281) by @jasonvarga
- Bump ajv from 6.12.2 to 6.12.6 [#5258](https://github.com/statamic/cms/issues/5258) by @dependabot
- Bump url-parse from 1.5.6 to 1.5.10 [#5359](https://github.com/statamic/cms/issues/5359) by @dependabot
- Bump color-string from 1.5.3 to 1.9.0 [#5276](https://github.com/statamic/cms/issues/5276) by @dependabot
- Bump url-parse from 1.5.1 to 1.5.6 [#5275](https://github.com/statamic/cms/issues/5275) by @dependabot
- Bump ws from 5.2.2 to 5.2.3 [#5274](https://github.com/statamic/cms/issues/5274) by @dependabot
- Bump tmpl from 1.0.4 to 1.0.5 [#5273](https://github.com/statamic/cms/issues/5273) by @dependabot
- Bump follow-redirects from 1.14.1 to 1.14.8 [#5263](https://github.com/statamic/cms/issues/5263) by @dependabot



## 3.2.33 (2022-02-11)

### What's new
- Support `whereBetween` and `whereNotBetween` in the query builder. [#4752](https://github.com/statamic/cms/issues/4752) by @ryanmitchell
- Ability to add config fields to existing fieldtypes. [#5077](https://github.com/statamic/cms/issues/5077) by @aerni
- Add Polish translation. [#5146](https://github.com/statamic/cms/issues/5146) by @damianchojnacki
- Replicator/Bard/Grid fields can be validated against fields in the same set/row. [#5047](https://github.com/statamic/cms/issues/5047) by @arthurperton
- Add audio and video attributes to assets. [#5045](https://github.com/statamic/cms/issues/5045) by @edalzell

### What's improved
- Improve performance of fetching user roles. [#5120](https://github.com/statamic/cms/issues/5120) by @jonassiewertsen
- The password protection page is translatable. [#4894](https://github.com/statamic/cms/issues/4894) by @jelleroorda

### What's fixed
- Fix toggle fieldtype alignment issues. [#5251](https://github.com/statamic/cms/issues/5251) by @arthurperton
- Fix the `list` modifier not working with Collections. [#5255](https://github.com/statamic/cms/issues/5255) by @ryanmitchell
- Fix incorrect exception usage in Comb. [#5223](https://github.com/statamic/cms/issues/5223) by @Konafets
- Fix incorrect exception usage in the Agolia index. [#5224](https://github.com/statamic/cms/issues/5224) by @Konafets
- Fix the `structures` icon not inheriting color. [#5230](https://github.com/statamic/cms/issues/5230) by @aerni
- Removed duplicated logic in `nav` tag. [#5210](https://github.com/statamic/cms/issues/5210) by @arthurperton
- Revert `type` being a reserved word for field handles. [#5184](https://github.com/statamic/cms/issues/5184) by @jasonvarga
- Fix static caching not invalidating when a collection tree is deleted. [#5170](https://github.com/statamic/cms/issues/5170) by @Konafets
- Fix HTML snippet in Bard fieldtype related messages. [#5145](https://github.com/statamic/cms/issues/5145) by @Konafets
- Set the `color` fieldtype's `color_modes` default value correctly. [#5137](https://github.com/statamic/cms/issues/5137) by @Konafets
- Prevent additional data in blueprint YAML files being removed when saving. [#5129](https://github.com/statamic/cms/issues/5129) by @jasonvarga
- A bunch of code formatting fixes. [#5218](https://github.com/statamic/cms/issues/5218) [#5214](https://github.com/statamic/cms/issues/5214) [#5219](https://github.com/statamic/cms/issues/5219) [#5215](https://github.com/statamic/cms/issues/5215) [#5189](https://github.com/statamic/cms/issues/5189) [#5158](https://github.com/statamic/cms/issues/5158) [#5134](https://github.com/statamic/cms/issues/5134) [#5142](https://github.com/statamic/cms/issues/5142) by @Konafets



## 3.2.32 (2022-01-26)

### What's improved
- Improve performance of the `nav` tag. [#4925](https://github.com/statamic/cms/issues/4925) by @FrittenKeeZ
- Prevent entering negative values into `max_items`/`max_files` configs. [#5044](https://github.com/statamic/cms/issues/5044) by @edalzell
- Prevent using `type` as a field handle. [#5088](https://github.com/statamic/cms/issues/5088) by @arthurperton
- Default field names are suffixed to help prevent tag/variable collisions. [#5084](https://github.com/statamic/cms/issues/5084) by @arthurperton

### What's fixed
- Fix infinite loop in UI when removing assets from `asset` fieldtype. [#5070](https://github.com/statamic/cms/issues/5070) by @arthurperton
- Fix `widont` inserting spaces into nested lists. [#5115](https://github.com/statamic/cms/issues/5115) by @benfreke
- Fix Replicator preview text generation. [#5096](https://github.com/statamic/cms/issues/5096) by @arthurperton
- Fix Eloquent query builder column selects. [#5125](https://github.com/statamic/cms/issues/5125) by @jasonvarga
- Fix "passed value cannot be an array" error. [#5127](https://github.com/statamic/cms/issues/5127) by @jasonvarga
- Fix unintentionally added new abstract query builder method. [#5123](https://github.com/statamic/cms/issues/5123) by @jasonvarga
- Fix `nav` tag's `is_parent` logic so it works better for Navs. [#4969](https://github.com/statamic/cms/issues/4969) by @arthurperton
- Throw a 404 instead of 500 on Glide asset URLs when there's an invalid container. [#5094](https://github.com/statamic/cms/issues/5094) by @edalzell
- Fix Replicator's "add set" button not inserting at the right position. [#5107](https://github.com/statamic/cms/issues/5107) by @arthurperton
- Fix the "delete" action confirmation translations. [#5074](https://github.com/statamic/cms/issues/5074) by @zsoltjanes
- Bump `marked` from 0.7.0 to 4.0.10 [#5075](https://github.com/statamic/cms/issues/5075) by @dependabot



## 3.2.31 (2022-01-14)

### What's new
- Ability to exclude certain fields from displaying in the REST API. [#5041](https://github.com/statamic/cms/issues/5041) by @arthurperton
- Ability to `select` fields in `nav`, `locales`, and `collection` tags. [#5068](https://github.com/statamic/cms/issues/5068) by @jasonvarga
- Add search to the user listing in the CP. [#4084](https://github.com/statamic/cms/issues/4084) by @arthurperton
- Support `whereColumn` in the query builder. [#4754](https://github.com/statamic/cms/issues/4754) by @ryanmitchell

### What's fixed
- The `static:warm` command visits taxonomy URLs, excludes taxonomy URLs with no views, includes multisite taxonomy URLs, displays the URL count, and excludes excluded URLs. [#5065](https://github.com/statamic/cms/issues/5065) by @arthurperton
- Fix how localized terms and globals save their data and fall back to origin values. [#4884](https://github.com/statamic/cms/issues/4884) by @aerni
- Prevent nested Bard fields from going into fullscreen mode. [#5059](https://github.com/statamic/cms/issues/5059) by @jonassiewertsen
- Fix infinite loop when using nested Replicator fields. [#5055](https://github.com/statamic/cms/issues/5055) by @jasonvarga
- Replaced a hardcoded `lang` directory reference. [#5054](https://github.com/statamic/cms/issues/5054) by @Konafets
- Fix incorrect redirect when saving a taxonomy term. [#5053](https://github.com/statamic/cms/issues/5053) by @arthurperton
- Fix SVGs not displaying in private asset containers. [#4991](https://github.com/statamic/cms/issues/4991) by @arthurperton
- Fix saving not using the latest state when you hit save too quickly after typing. [#5039](https://github.com/statamic/cms/issues/5039) by @jackmcdade
- Fix some PHP 8.1 deprecation messages. [#5063](https://github.com/statamic/cms/issues/5063) by @edalzell
- Add allowed composer plugins. [#5069](https://github.com/statamic/cms/issues/5069) by @jasonvarga



## 3.2.30 (2022-01-07)

### What's new
- Support sub-field querying (JSON) in the query builder. [#4758](https://github.com/statamic/cms/issues/4758) by @ryanmitchell
- The `collection` tag (and others) can `sort` by sub-fields. [#5030](https://github.com/statamic/cms/issues/5030) by @jasonvarga
- Uploaded assets may be attached to form submission emails. [#4726](https://github.com/statamic/cms/issues/4726) by @jacksleight
- Provide errors indexed by fields for the `user:reset_password_form` tag. [#4822](https://github.com/statamic/cms/issues/4822) by @marcorieser
- `DataCollection` can use the first value of an array for sorting. [#4967](https://github.com/statamic/cms/issues/4967) by @arthurperton
- Add `min_` and `max_filesize` validation rules for `assets` fieldtypes. [#4980](https://github.com/statamic/cms/issues/4980) by @arthurperton

### What's improved
- The `display` field is auto-focused in the blueprint builder. [#5026](https://github.com/statamic/cms/issues/5026) by @jackmcdade

### What's fixed
- Fix state mutations in Replicator, causing the page to freeze in some situations. [#5031](https://github.com/statamic/cms/issues/5031) by @arthurperton
- Fix the Asset's `remove` method not removing anything. [#5038](https://github.com/statamic/cms/issues/5038) by @arthurperton
- Fix inappropriate state mutations in Grid and Assets fieldtypes. [#5005](https://github.com/statamic/cms/issues/5005) by @arthurperton
- Fix terms not being sorted appropriately in other sites. [#4982](https://github.com/statamic/cms/issues/4982) by @arthurperton
- Fix the range field's derpy layout of uneven widths [#5027](https://github.com/statamic/cms/issues/5027) by @jackmcdade
- Fix `View` method chainability. [#5020](https://github.com/statamic/cms/issues/5020) by @jasonvarga
- Fix unnecessary dirty state when changing sites. [#5013](https://github.com/statamic/cms/issues/5013) by @jasonvarga
- Fix readonly sub-fields in localizable Grid and Bard fields. [#4962](https://github.com/statamic/cms/issues/4962) by @arthurperton



## 3.2.29 (2022-01-04)

### What's new
- Support `where([...])` syntax in the query builder. [#4899](https://github.com/statamic/cms/issues/4899) by @ryanmitchell
- Added login throttling to `user:login_form`. [#4971](https://github.com/statamic/cms/issues/4971) by @arthurperton
- Add `is_homepage` variable to cascade. [#4995](https://github.com/statamic/cms/issues/4995) by @edalzell
- The pagination slider window can be adjusted. [#5001](https://github.com/statamic/cms/issues/5001) by @ryanmitchell

### What's improved
- Updated French translations. [#4976](https://github.com/statamic/cms/issues/4976) by @ebeauchamps
- Updated German translations. [#4998](https://github.com/statamic/cms/issues/4998) by @helloDanuk

### What's fixed
- Fix red error states when dealing with nested fields (Replicator, Grid, Bard). [#5002](https://github.com/statamic/cms/issues/5002) by @arthurperton
- Prevent error when when uploading assets in forms when there's no container configured. [#4974](https://github.com/statamic/cms/issues/4974) by @arthurperton
- Fix intersection error in iterator query builder, which search uses. [#5006](https://github.com/statamic/cms/issues/5006) by @jasonvarga
- In the fieldtype selector, hide empty sections, and hide slug for forms. [#4948](https://github.com/statamic/cms/issues/4948) by @jesseleite
- Link to docs in route config field. [#4986](https://github.com/statamic/cms/issues/4986) by @Konafets
- Fix reordering in a collection ending with the word "collection". [#4978](https://github.com/statamic/cms/issues/4978) by @arthurperton
- Fix changelog dates. [#4973](https://github.com/statamic/cms/issues/4973) by @markguleno



## 3.2.28 (2021-12-22)

### What's new
- Entries in a multi-level structured collection can be sorted by depth-first order. [#4883](https://github.com/statamic/cms/issues/4883) by @FrittenKeeZ
- Asset folder can be renamed/moved in the Control Panel. [#4028](https://github.com/statamic/cms/issues/4028) by @arthurperton
- The pool concurrency can be configured for the Static Cache warming command. [#4931](https://github.com/statamic/cms/issues/4931) by @jelleroorda
- Add `compact` modifier. [#4933](https://github.com/statamic/cms/issues/4933) by @JohnathonKoster
- Add `increment:reset` tag. [#4932](https://github.com/statamic/cms/issues/4932) by @JohnathonKoster

### What's improved
- Improve augmentation performance by caching blueprint fields. [#4923](https://github.com/statamic/cms/issues/4923) by @FrittenKeeZ
- Updated Markdown icon. [#4956](https://github.com/statamic/cms/issues/4956) by @jackmcdade

### What's fixed
- Allow `group_by` modifier to support stringable objects. [#4955](https://github.com/statamic/cms/issues/4955) by @jasonvarga
- Hide the handle field on the asset container edit screen. [#4954](https://github.com/statamic/cms/issues/4954) by @jasonvarga
- Alphabetically order asset container tabs, and fix the redirect. [#4947](https://github.com/statamic/cms/issues/4947) by @duncanmcclean
- Fix params not working on `assets` tag when it's an assets fieldtype. [#4731](https://github.com/statamic/cms/issues/4731) by @jelleroorda
- Fix error when attempting to view a revision. [#4945](https://github.com/statamic/cms/issues/4945) by @jasonvarga
- Fix noparse extractions with non-Antlers layouts. [#4934](https://github.com/statamic/cms/issues/4934) by @JohnathonKoster
- Adjust Replicator update logic, which fixes nested `table` fields losing data. [#4903](https://github.com/statamic/cms/issues/4903) by @jesseleite
- Fix `site` query param on tree API endpoints. [#4907](https://github.com/statamic/cms/issues/4907) by @jesseleite
- Replace JS lazyloading with native. [#4943](https://github.com/statamic/cms/issues/4943) by @jackmcdade
- Add `ends_with` validation rule to the autocompletion list. [#4940](https://github.com/statamic/cms/issues/4940) by @dmgawel
- Fix pasting into Bard image alt field. [#4950](https://github.com/statamic/cms/issues/4950) by @dmgawel
- Fix checkbox position. [#4957](https://github.com/statamic/cms/issues/4957) by @jackmcdade



## 3.2.27 (2021-12-17)

### What's new
- Entries may have autogenerated titles and/or optional slugs. [#4667](https://github.com/statamic/cms/issues/4667) by @jasonvarga
- Query builder can perform closure based nested wheres. [#4555](https://github.com/statamic/cms/issues/4555) by @ryanmitchell
- Added a `current_user` variable to views. [#4888](https://github.com/statamic/cms/issues/4888) by @jacksleight

### What's improved
- Refreshed fieldtype selector design. [#4929](https://github.com/statamic/cms/issues/4929) by @jackmcdade

### What's fixed
- Fieldtypes no longer get assigned to the `text` category by default. [447c27343](https://github.com/statamic/cms/commit/447c27343) by @jackmcdade
- Links in instruction text are no longer red. [#4905](https://github.com/statamic/cms/issues/4905) by @jackmcdade
- Fix issue where the wrong entry's values could appear on the front-end. [#4918](https://github.com/statamic/cms/issues/4918) by @jasonvarga
- Bumped TailwindCSS to 1.9.6. [#4929](https://github.com/statamic/cms/issues/4929) by @jackmcdade



## 3.2.26 (2021-12-10)

### What's new
- Add create and link button to Terms fieldtype. [#4073](https://github.com/statamic/cms/issues/4073) by @arthurperton
- Swedish translation. [#4880](https://github.com/statamic/cms/issues/4880) by @adevade
- Add `parent` method to Blueprint class. [#4885](https://github.com/statamic/cms/issues/4885) by @aerni

### What's fixed
- Fix `form:errors` not showing errors. [#4095](https://github.com/statamic/cms/issues/4095) by @arthurperton



## 3.2.25 (2021-12-07)

### What's new
- Add `please license:set` command. [#4840](https://github.com/statamic/cms/issues/4840) by @jesseleite
- Add `trackable_embed_url` modifier. [#4856](https://github.com/statamic/cms/issues/4856) by @edalzell

### What's fixed

- Fix Starter Kits not being installable on Windows. [#4843](https://github.com/statamic/cms/issues/4843) by @jesseleite
- Adjusted some licensing messaging. [#4870](https://github.com/statamic/cms/issues/4870) by @jasonvarga
- Fix double scheduling. [#4855](https://github.com/statamic/cms/issues/4855) by @edalzell
- Fix action file downloads not being completed. [#4851](https://github.com/statamic/cms/issues/4851) by @wanze
- Fix jumpy publish tabs on page load. [#4866](https://github.com/statamic/cms/issues/4866) by @jackmcdade
- Fix jumpy origin sync button. [#4819](https://github.com/statamic/cms/issues/4819) by @aerni
- Fix read-only mode on grid solo assets. [#4865](https://github.com/statamic/cms/issues/4865) by @jackmcdade
- Emails will use the site's `lang` for translations if provided. [#4842](https://github.com/statamic/cms/issues/4842) by @okaufmann
- Fix paths to git documentation in config files. [#4824](https://github.com/statamic/cms/issues/4824) by @McGo
- Added PHP 8.1 tests. [#4724](https://github.com/statamic/cms/issues/4724) by @jasonvarga



## 3.2.24 (2021-11-24)

### What's new
- Native Bard / TipTap extensions can be replaced by custom ones. [#4314](https://github.com/statamic/cms/issues/4314) by @jacksleight
- Added an `is_tomorrow` modifier. [#4802](https://github.com/statamic/cms/issues/4802) by @joseph-d
- Added a Hungarian translation. [#4804](https://github.com/statamic/cms/issues/4804) by @matkovsky

### What's improved
- Custom Bard buttons can be added conditionally. [#4106](https://github.com/statamic/cms/issues/4106) by @morhi
- French translations [#4809](https://github.com/statamic/cms/issues/4809) by @ebauchamps



## 3.2.23 (2021-11-19)

### What's new
- You can specify `export_as` paths in your starter kit config. [#4733](https://github.com/statamic/cms/issues/4733) by @jesseleite
- Add `get_errors` tag. [#4192](https://github.com/statamic/cms/issues/4192) by @edalzell
- Add `whereNull` support to query builders. [#4740](https://github.com/statamic/cms/issues/4740) by @ryanmitchell
- Add Forms support to GraphQL. [#4115](https://github.com/statamic/cms/issues/4115) by @arthurperton

### What's fixed
- Added missing `antlers` config to the UI for Bard fields. [#4782](https://github.com/statamic/cms/issues/4782) by @jackmcdade
- Fixed error when submitting forms from other domains. [#4745](https://github.com/statamic/cms/issues/4745) by @SteJW


## 3.2.22 (2021-11-15)

### What's new
- Publish form tabs are pushed into the URL so you can link to specific tabs or stay where you are when refreshing. [#4660](https://github.com/statamic/cms/issues/4660) by @jackmcdade
- Option to hide email login button when using OAuth. [#4625](https://github.com/statamic/cms/issues/4625) by @duncanmcclean
- Include cascade data (like globals) on the password protection page. [#4706](https://github.com/statamic/cms/issues/4706) by @ryanmitchell
- Add `download` method to the Asset class. [#4712](https://github.com/statamic/cms/issues/4712) by @edalzell
- Using an invalid nav or collection on the `nav` tag will throw an exception. [#4624](https://github.com/statamic/cms/issues/4624) by @jelleroorda
- Add `bootAddon()` method to `AddonServiceProvider`. [#4696](https://github.com/statamic/cms/issues/4696) by @ryanmitchell
- Ability to infer template from blueprint. [#4668](https://github.com/statamic/cms/issues/4668) by @jesseleite

### What's improved
- Chinese translations. [#4734](https://github.com/statamic/cms/issues/4734) by @binotaliu
- Russian translations. [#4695](https://github.com/statamic/cms/issues/4695) by @dragomano

### What's fixed
- Store `parent` as ID in the Stache to make it queryable. [#4728](https://github.com/statamic/cms/issues/4728) by @jasonvarga
- Fix `range` field not updating when changing sites. [#4713](https://github.com/statamic/cms/issues/4713) by @edalzell
- Fix spacing on the `entries` fieldtype. [#4714](https://github.com/statamic/cms/issues/4714) by @edalzell
- The app translator locale is set to the site's `lang`. [#4715](https://github.com/statamic/cms/issues/4715) by @marcorieser
- Fix error on `select` (and similar) field when there are no options. [#4689](https://github.com/statamic/cms/issues/4689) by @jasonvarga
- Handle numeric keys on `select` (and similar) fields. [#4688](https://github.com/statamic/cms/issues/4688) by @jasonvarga



## 3.2.21 (2021-11-08)

### What's new
- The `locales` tag can output data for all sites even when the entry isn't localized. It can also exclude its own locale. [#4665](https://github.com/statamic/cms/issues/4665) by @aerni
- Utility for warming the Stache. [#4659](https://github.com/statamic/cms/issues/4659) by @jackmcdade

### What's improved
- Show a more helpful error if someone hides all blueprints. [#4607](https://github.com/statamic/cms/issues/4607) by @jelleroorda
- French translations. [#4627](https://github.com/statamic/cms/issues/4627) by @ebeauchamps
- Dutch translations. [#4664](https://github.com/statamic/cms/issues/4664) by @royvanv
- German translations. [#4642](https://github.com/statamic/cms/issues/4642) by @helloDanuk

### What's fixed
- Fix nav root pages not showing extra data. [#4650](https://github.com/statamic/cms/issues/4650) by @jelleroorda
- Fix asset field validation when using Amazon S3. [#4116](https://github.com/statamic/cms/issues/4116) by @arthurperton
- Fix null and empty string field condition handling. [#4661](https://github.com/statamic/cms/issues/4661) by @jesseleite
- Style links inside bard tables [3880e39c3](https://github.com/statamic/cms/commit/3880e39c3) by @jackmcdade
- Add a duplicate translation for backwards compatibility. [#4648](https://github.com/statamic/cms/issues/4648) by @jasonvarga
- Bump axios from 0.21.1 to 0.21.2 [#4679](https://github.com/statamic/cms/issues/4679) by @dependabot
- Bump validator from 10.11.0 to 13.7.0 [#4649](https://github.com/statamic/cms/issues/4649) by @dependabot



## 3.2.20 (2021-11-01)

### What's improved
- Update Dutch translations [#4635](https://github.com/statamic/cms/issues/4635) by @robdekort

### What's fixed
- Fix nested field validation inside Grids. [#4639](https://github.com/statamic/cms/issues/4639) by @jesseleite
- Fix nested field validation inside Replicators (specifically Bards). [#4633](https://github.com/statamic/cms/issues/4633) by @jesseleite
- Fix password reset notification translation. [#4630](https://github.com/statamic/cms/issues/4630) by @rrelmy



## 3.2.19 (2021-10-29)

### What's new
- Added `lang` to sites to explicitly define the language for translations. [#4612](https://github.com/statamic/cms/issues/4612) by @jelleroorda
- The mode in the `code` fieldtype may optionally be selectable by the user. [#4586](https://github.com/statamic/cms/issues/4586) by @jackmcdade
- Added `RevisionSaved` and `RevisionDeleted` events [#4587](https://github.com/statamic/cms/issues/4587) by @jesseleite
- Added `latest_date` to the `date` fieldtype (and fixed `earliest_date`). [#4623](https://github.com/statamic/cms/issues/4623) by @jackmcdade

### What's improved
- Passing asset instances to the `glide` tag results in a little performance boost. [#4585](https://github.com/statamic/cms/issues/4585) by @jasonvarga
- Make textareas more visually in sync with text inputs. [#4622](https://github.com/statamic/cms/issues/4622) by @jackmcdade
- Improved the collection listing's empty state. [#4616](https://github.com/statamic/cms/issues/4616) by @jackmcdade
- Hide the twirldown from the collection view page when it's empty. [#4613](https://github.com/statamic/cms/issues/4613) by @jelleroorda
- Update Dutch translations. [#4580](https://github.com/statamic/cms/issues/4580) by @robdekort

### What's fixed
- Fix multisite support on the individual Global API endpoint. [#4594](https://github.com/statamic/cms/issues/4594) by @notnek
- Fix missing variables and redirect on the `user:reset_password_form` tag. [#4618](https://github.com/statamic/cms/issues/4618) by @jelleroorda
- Fix the `table` fieldtype's dirty state. [#4620](https://github.com/statamic/cms/issues/4620) by @jackmcdade
- Fix the `color` fieldtype's dirty state. [#4621](https://github.com/statamic/cms/issues/4621) by @jackmcdade
- Fix how the `structures` fieldtype saves collections. [#4615](https://github.com/statamic/cms/issues/4615) by @jasonvarga
- Fix front-end form submissions not showing validation messages in the right language in some cases. [#4612](https://github.com/statamic/cms/issues/4612) by @jelleroorda
- Prevent filtering out `@` from search queries using the local Comb driver. [#4602](https://github.com/statamic/cms/issues/4602) by @jelleroorda
- Hide `form` widgets when a user is not allowed to view it. [#4608](https://github.com/statamic/cms/issues/4608) by @jelleroorda
- Don't show "View" link in entry actions if the entry doesn't have a dedicated URL. [#4606](https://github.com/statamic/cms/issues/4606) by @jelleroorda
- Support floating point numbers in the `sum` modifier. [#4611](https://github.com/statamic/cms/issues/4611) by @jelleroorda
- Add augmentation to the `Structure` class. [a950ef47d](https://github.com/statamic/cms/commit/a950ef47d) by @jackmcdade
- Add augmentation to the `UserGroup` and `Role` classes. [b9f5c4fc8](https://github.com/statamic/cms/commit/b9f5c4fc8) by @jackmcdade



## 3.2.18 (2021-10-25)

### What's new
- Add support for `orWhere()`, `orWhereIn()`, and `orWhereNotIn()` in query builders. [#4356](https://github.com/statamic/cms/issues/4356) by @ryanmitchell

### What's improved
- The `ResponseCreated` event has access to the data. [#4569](https://github.com/statamic/cms/issues/4569) by @jbreuer95
- Updated French translations. [#4548](https://github.com/statamic/cms/issues/4548) by @ebeauchamps

### What's fixed
- Fix custom protector class hydration. [#4550](https://github.com/statamic/cms/issues/4550) by @ChristianPavilonis
- Use configured CP guard for the Eloquent user driver. [#4225](https://github.com/statamic/cms/issues/4225) by @jbreuer95
- Fix hard session driver requirement. [#4571](https://github.com/statamic/cms/issues/4571) by @jbreuer95
- Fix SVG compatibility & consistency in Thumbnail.vue. [#4547](https://github.com/statamic/cms/issues/4547) by @caseydwyer
- Add check for permissions before showing create link. [#4556](https://github.com/statamic/cms/issues/4556) by @jackmcdade



## 3.2.17 (2021-10-20)

### What's new
- Ability to add additional toast notifications from PHP. [#4449](https://github.com/statamic/cms/issues/4449) by @fjahn
- Add ability to assign user groups when registering. [#4529](https://github.com/statamic/cms/issues/4529) by @jacksleight
- The "remember me" feature is configurable for OAuth. [#4415](https://github.com/statamic/cms/issues/4415) by @samspinoy

### What's improved
- Update French translations. [#4531](https://github.com/statamic/cms/issues/4531) by @ebeauchamps
- Improve UI for selecting user groups/roles, and for containers in Markdown and Bard fields. [#4539](https://github.com/statamic/cms/issues/4539) by @jasonvarga

### What's fixed
- Fix a number of issues with select (and similar) fieldtypes. [#4483](https://github.com/statamic/cms/issues/4483) by @jasonvarga
- Fix reversing of entries when re-ordering a `desc` ordered collection. [#4532](https://github.com/statamic/cms/issues/4532) by @jesseleite
- Speed up the recently added fieldtype input debouncing. [#4470](https://github.com/statamic/cms/issues/4470) by @jasonvarga
- Fix entry taxonomization indexing for existing terms. [#4530](https://github.com/statamic/cms/issues/4530) by @jesseleite
- Fix a couple of issues regarding the `User::fromUser()` method when using Eloquent. [#4500](https://github.com/statamic/cms/issues/4500) by @jesseleite



## 3.2.16 (2021-10-14)

### What's improved
- Improve IDE Autocompletion of `Blink` facade. [#4466](https://github.com/statamic/cms/issues/4466) by @duncanmcclean

### What's fixed
- Fix relationship fieldtype request query length limit. [#4484](https://github.com/statamic/cms/issues/4484) by @jesseleite
- Fix issue with Glide signature and Laravel Octane. [#4473](https://github.com/statamic/cms/issues/4473) by @riasvdv
- Fix multisite entry blueprint logic. [#4465](https://github.com/statamic/cms/issues/4465) by @jasonvarga
- Fix fieldtype key and label translations. [#4458](https://github.com/statamic/cms/issues/4458) by @ebeauchamps
- Fix styles of lists in Markdown Preview. [#4480](https://github.com/statamic/cms/issues/4480) by @duncanmcclean
- Fix Bard codeblock margin. [#4482](https://github.com/statamic/cms/issues/4482) by @stvnthomas



## 3.2.15 (2021-10-12)

### What's new
- Entries may be propagated to other sites automatically on creation. [#3304](https://github.com/statamic/cms/issues/3304) by @duncanmcclean
- Slugs may be shown on a collection's tree view. [#4444](https://github.com/statamic/cms/issues/4444) by @tobiasholst
- You can query entries' `blueprint` fields in GraphQL. [#4416](https://github.com/statamic/cms/issues/4416) by @dmgawel

### What's improved
- When creating a new localized entry, the published toggle will now match the origin entry's status. [#4432](https://github.com/statamic/cms/issues/4432) by @jesseleite

### What's fixed
- Fix incompatibility with latest version of Laravel. [#4456](https://github.com/statamic/cms/issues/4456) by @jasonvarga
- Fix Bard reactivity issue [#4438](https://github.com/statamic/cms/issues/4438) by @tobiasholst



## 3.2.14 (2021-10-08)

### What's improved
- Updated German translations. [#4429](https://github.com/statamic/cms/issues/4429) by @helloDanuk

### What's fixed
- Fieldtype titles are translated separately to prevent conflicts with common words. [#4423](https://github.com/statamic/cms/issues/4423) by @jasonvarga
- Collection entry counts are site specific. [#4424](https://github.com/statamic/cms/issues/4424) by @jasonvarga
- Fixed issue where IDs are shown instead of titles in relationship fieldtypes when using Eloquent.  [#4422](https://github.com/statamic/cms/issues/4422) by @tobiasholst



## 3.2.13 (2021-10-07)

### What's improved
- Update Dutch translations. [#4413](https://github.com/statamic/cms/issues/4413) by @robdekort
- Update French translations. [#4411](https://github.com/statamic/cms/issues/4411) by @ebeauchamps

### What's fixed
- Fix lost asset meta on move / rename. [#4412](https://github.com/statamic/cms/issues/4412) by @jesseleite



## 3.2.12 (2021-10-06)

### What's improved
- Added debouncing to a number of fieldtypes to prevent slowdowns in some situations. [#4393](https://github.com/statamic/cms/issues/4393)
- Updated French translations [#4382](https://github.com/statamic/cms/issues/4382)

### What's fixed
- Fixed Bard's floating toolbar button styles leaking outside of the toolbar. [#4383](https://github.com/statamic/cms/issues/4383)
- Use separate first/last name fields in the user listing and wizard when applicable. [#4408](https://github.com/statamic/cms/issues/4408) [#4399](https://github.com/statamic/cms/issues/4399)
- Fix issue where enabling a site on a taxonomy would not show the terms until the cache is cleared. [#4400](https://github.com/statamic/cms/issues/4400)
- Add missing dimensions icon dimensions. [#4396](https://github.com/statamic/cms/issues/4396)
- Bump `composer/composer` in test suite. [#4401](https://github.com/statamic/cms/issues/4401)



## 3.2.11 (2021-10-04)

### What's improved
- Updated German translations. [#4373](https://github.com/statamic/cms/issues/4373)

### What's fixed
- Added `Cascade::hydrated()` callback method so you can manipulate its data after being hydrated. [#4359](https://github.com/statamic/cms/issues/4359)
- Fix extra live preview data not being in view. [#4359](https://github.com/statamic/cms/issues/4103)
- Make `pluck` modifier work with arrays. [#4374](https://github.com/statamic/cms/issues/4374)
- Fix `parent` tag not finding the parent in some cases. [#4345](https://github.com/statamic/cms/issues/4345)
- `Search::indexExists()` returns `false` rather than throwing an exception. [#4244](https://github.com/statamic/cms/issues/4244)



## 3.2.10 (2021-09-30)

### What's new
- Add `ensureFieldsInSection` method to add multiple fields at the same time. [#4333](https://github.com/statamic/cms/issues/4333)

### What's fixed
- Fix taxonomy terms not returning accurate entries or counts when using certain combinations of collections and multisite. [#4335](https://github.com/statamic/cms/issues/4335)



## 3.2.9 (2021-09-28)

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



## 3.2.8 (2021-09-24)

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
