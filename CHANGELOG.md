# Release Notes

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
