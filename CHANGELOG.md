# Release Notes

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
