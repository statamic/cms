<?php

return [
    'activate_account_notification_body' => 'Azért kapta ezt az e-mailt, mert jelszó-visszaállítási kérést kaptunk fiókjához.',
    'activate_account_notification_subject' => 'Aktiválja fiókját',
    'addon_has_more_releases_beyond_license_body' => 'Lehet frissíteni, de a rendszert is frissítenie kell vagy új licencet kell vásárolnia.',
    'addon_has_more_releases_beyond_license_heading' => 'Ennek az addonnak több kiadása is van az Ön licence által korlátozotton túl.',
    'addon_install_command' => 'A kiegészítő telepítéséhez futtassa a következő parancsot',
    'addon_list_loading_error' => 'Valami hiba történt az addonok betöltése során. Próbálja újra később.',
    'addon_uninstall_command' => 'A kiegészítő eltávolításához futtassa a következő parancsot',
    'asset_container_allow_uploads_instructions' => 'Ha engedélyezve van, a felhasználók fájlokat tölthetnek fel ebbe a Médiatárolóba.',
    'asset_container_blueprint_instructions' => 'A Blueprintek további egyéni mezőket határoznak meg a Médiák szerkesztésekor.',
    'asset_container_create_folder_instructions' => 'Ha engedélyezve van, a felhasználók mappákat hozhatnak létre ebben a Médiatárolóban.',
    'asset_container_disk_instructions' => 'A Meghajtók (disk) határozzák meg, hogy a fájlok hol tárolódnak: lokálisan a szerveren vagy egy távoli helyen (pl. Amazon S3). A beállításai a `config/filesystems.php` fájlban találhatóak.',
    'asset_container_handle_instructions' => 'Erre a Médiatárolóra való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'asset_container_intro' => 'A média- és dokumentumfájlok a szerveren vagy más fájltároló-szolgáltatásokon található mappákban vannak. Ezen helyeket nevezzük Médiatárolóknak.',
    'asset_container_move_instructions' => 'Ha engedélyezve van, akkor a felhasználók mozgathatják a fájlokat ebben a Médiatárolóban.',
    'asset_container_quick_download_instructions' => 'Ha engedélyezve van, a rendszer hozzáad egy gyorsletöltés gombot az Eszközkezelőhöz.',
    'asset_container_rename_instructions' => 'Ha engedélyezve van, a felhasználók átnevezhetik a Médiatárolóban lévő fájlokat.',
    'asset_container_source_preset_instructions' => 'A feltöltött képeket a rendszer véglegesen feldolgozza ezzel az előbeállítással.',
    'asset_container_title_instructions' => 'Általában többes számú főnév, például Images (képek) vagy Documents (dokumentumok).',
    'asset_container_validation_rules_instructions' => 'Ezek a szabályok a feltöltött fájlokra vonatkoznak.',
    'asset_container_warm_intelligent_instructions' => 'Feltöltéskor állítsa elő a megfelelő előbeállításokat.',
    'asset_container_warm_presets_instructions' => 'Adja meg, hogy mely előre beállított értékeket generálja a feltöltéskor.',
    'asset_folders_directory_instructions' => 'Javasoljuk, hogy az URL-ek „tisztán tartása” érdekében kerülje a szóközöket és a speciális karaktereket.',
    'asset_replace_confirmation' => 'A tartalomban erre az elemre vonatkozó hivatkozások az alábbiakban kiválasztott tartalomra frissülnek.',
    'asset_reupload_confirmation' => 'Biztosan újra fel szeretné tölteni ezt az elemet?',
    'asset_reupload_warning' => 'Böngésző- vagy szerverszintű gyorsítótárazási problémák léphetnek fel. Inkább cserélje le az eszközt.',
    'blueprints_hidden_instructions' => 'Elrejti a Blueprintet a létrehozó gomboknál',
    'blueprints_intro' => 'A Blueprintek beviteli mezők segítségével írják le a Gyűjtemények, Űrlapok és egyéb adattípusok adatmodelljeit.',
    'blueprints_title_instructions' => 'Általában egyes számú főnév, például Article (cikk) vagy Product (termék).',
    'cache_utility_application_cache_description' => 'A Laravel egyesített gyorsítótára _(unified cache)_, amelyet a Statamic és az addonok mellett a composer csomagok is használnak.',
    'cache_utility_description' => 'Kezelje és tekintse meg a Statamic különféle gyorsítótárazási rétegeivel _(caching layers)_ kapcsolatos fontos információkat.',
    'cache_utility_image_cache_description' => 'A képgyorsítótár az összes átalakított és átméretezett kép másolatát tárolja.',
    'cache_utility_stache_description' => 'A Stache a Statamic „tartalom-gyorsítótára” _(content store)_, amely egy adatbázishoz hasonlóan működik. A rendszer automatikusan generálja a tartalomfájlokból.',
    'cache_utility_static_cache_description' => 'A statikus oldalak teljesen megkerülik a Statamic-ot, és közvetlenül a szerverről kerülnek kiszolgálásra, ezzel maximalizálva a teljesítményt.',
    'choose_entry_localization_deletion_behavior' => 'Válassza ki a lokalizált bejegyzéseken végrehajtani kívánt műveletet.',
    'collection_configure_date_behavior_private' => 'Privát – a listák elől elrejtve, az URL-ek 404-es hibára futnak',
    'collection_configure_date_behavior_public' => 'Nyilvános – Mindig látható',
    'collection_configure_date_behavior_unlisted' => 'Nem listázott – a listák elől elrejtve, az URL-ek láthatók',
    'collection_configure_dated_instructions' => 'A közzétételi dátumok felhasználhatók a tartalom ütemezésére és lejáratára.',
    'collection_configure_handle_instructions' => 'Erre a gyűjteményre való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'collection_configure_intro' => 'A gyűjtemény olyan kapcsolódó bejegyzések csoportja, amelyek ugyanolyan attribútumokkal és beállításokkal rendelkeznek.',
    'collection_configure_layout_instructions' => 'Állítsa be a gyűjtemény alapértelmezett elrendezését. A bejegyzések felülírhatják ezt a beállítást egy `template` típusú, `layout` nevű mezővel. (Szokatlan ennek a beállításnak a megváltoztatása.)',
    'collection_configure_origin_behavior_instructions' => 'Egy bejegyzés nyelvesítésekor melyik oldalt tekintsük alapértelmezettnek?',
    'collection_configure_origin_behavior_option_active' => 'Használja az aktív oldalt, ahol a bejegyzés szerkesztve lett.',
    'collection_configure_origin_behavior_option_root' => 'Használja azt a oldalt, ahol a bejegyzést eredetileg létrehozták',
    'collection_configure_origin_behavior_option_select' => 'Hagyja, hogy a felhasználó válassza ki az alapértelmezettet',
    'collection_configure_propagate_instructions' => 'Az új bejegyzések automatikus másolása az összes konfigurált oldalra.',
    'collection_configure_require_slugs_instructions' => 'Kötelező-e a bejegyzéseknek "slug"-ot megadni.',
    'collection_configure_template_instructions' => 'Állítsa be a gyűjtemény alapértelmezett sablonját. A bejegyzések felülírhatják ezt a beállítást egy `template` mezővel.',
    'collection_configure_title_format_instructions' => 'Állítsa be, hogy a gyűjtemény bejegyzései automatikusan generálják a címüket. Tudjon meg többet a [documentation](https://statamic.dev/collections#titles).',
    'collection_configure_title_instructions' => 'Javasoljuk a többes számú főnév használatát, például „Articles” (Cikkek) vagy „Products” (Termékek).',
    'collection_next_steps_blueprints_description' => 'Kezelje a gyűjteményhez rendelkezésre álló tervrajzokat és mezőket.',
    'collection_next_steps_configure_description' => 'Konfigurálja az URL-eket és útvonalakat (routes), Blueprinteket, sorrendezést és egyéb beállításokat!',
    'collection_next_steps_create_entry_description' => 'Hozza létre az első bejegyzést!',
    'collection_next_steps_scaffold_description' => 'Gyorsan generálhat gyűjtő- (index) és részletnézeteket (details) a gyűjtemény nevéből.',
    'collection_revisions_instructions' => 'Verziók engedélyezése ehhez a gyűjteményhez.',
    'collection_scaffold_instructions' => 'Válassza ki, hogy mely üres nézeteket kívánja létrehozni. A meglévő fájlok nem kerülnek felülírásra.',
    'collections_blueprint_instructions' => 'A gyűjtemény bejegyzései használhatják ezen Blueprintek bármelyikét.',
    'collections_default_publish_state_instructions' => 'Amikor új bejegyzéseket hoz létre ebben a gyűjteményben, a `published` kapcsoló alapértelmezés szerint **true** lesz (**false**, azaz piszkozat helyett).',
    'collections_future_date_behavior_instructions' => 'Hogyan viselkedjenek a jövőben keltezett bejegyzések.',
    'collections_links_instructions' => 'A gyűjtemény bejegyzései tartalmazhatnak hivatkozásokat (átirányításokat) más bejegyzésekre vagy URL-ekre.',
    'collections_mount_instructions' => 'Válasszon egy bejegyzést, amelyre ezt a gyűjteményt csatolni (mount) szeretné. További információ a [dokumentációban](https://statamic.dev/collections-and-entries#mounting).',
    'collections_orderable_instructions' => 'Kézi rendezés engedélyezése drag &amp; drop segítségével.',
    'collections_past_date_behavior_instructions' => 'Hogyan viselkedjenek a múltban keltezett bejegyzések.',
    'collections_preview_target_refresh_instructions' => 'Az előnézet automatikus frissítése szerkesztés közben. Ennek letiltása a postMessage szolgáltatást használja.',
    'collections_preview_targets_instructions' => 'Az élő előnézetben megtekinthető URL-ek. További információ a [dokumentációban](https://statamic.dev/live-preview#preview-targets).',
    'collections_route_instructions' => 'Az útvonal vezérli a bejegyzések URL-jeit.',
    'collections_sort_direction_instructions' => 'Az alapértelmezett sorrendezési irány.',
    'collections_taxonomies_instructions' => 'Kapcsolja össze a gyűjtemény bejegyzéseit taxonómiákkal. A mezők automatikusan hozzáadódnak az űrlapokhoz.',
    'dictionaries_countries_emojis_instructions' => 'Meg kell-e tüntetni a megjelölés hangulatjeleit a címkéken.',
    'dictionaries_countries_region_instructions' => 'Opcionálisan szűrheti az országokat régió szerint.',
    'duplicate_action_localizations_confirmation' => 'Biztosan végrehajtja ezt a műveletet? A lokalizációk is megkettőződnek.',
    'duplicate_action_warning_localization' => 'Ez a bejegyzés egy lokalizáció. A származási bejegyzés megkettőződik.',
    'duplicate_action_warning_localizations' => 'Egy vagy több kiválasztott bejegyzés lokalizáció. Ilyen esetekben a származási bejegyzés megkettőződik.',
    'email_utility_configuration_description' => 'A levelezés beállításai itt találhatóak: <code>:path</code>',
    'email_utility_description' => 'Ellenőrizze az e-mail beállításokat, és küldjön teszt e-maileket.',
    'entry_origin_instructions' => 'Az új lokalizáció a kiválasztott oldalon lévő bejegyzés értékeit örökli.',
    'expect_root_instructions' => 'A fastruktúra első elemét tekintsük Főoldalnak („root” / „home”).',
    'field_conditions_always_save_instructions' => 'Mindig mentse a mező értékét, függetlenül attól, hogy a mezők hogyan kerülnek kiértékelésre.',
    'field_conditions_field_instructions' => 'Bármilyen mezőfogót megadhat. Nem korlátozódik a legördülő menüben található lehetőségekre.',
    'field_conditions_instructions' => 'Mikor kell megjeleníteni vagy elrejteni ezt a mezőt.',
    'field_desynced_from_origin' => 'A forrástól deszinkronizálva. Kattintson a szinkronizáláshoz és az eredeti értékéhez való visszatéréshez.',
    'field_synced_with_origin' => 'Szinkronizálva a forrással. Kattintson vagy szerkessze a mezőt a szinkronizálás megszüntetéséhez.',
    'field_validation_advanced_instructions' => 'Adjon ehhez a mezőhöz további validációs szabályt.',
    'field_validation_required_instructions' => 'Adja meg, hogy ez a mező kötelező-e vagy sem.',
    'field_validation_sometimes_instructions' => 'Csak akkor érvényesítse, ha ez a mező látható vagy elküldve.',
    'fields_blueprints_description' => 'A Blueprintek határozzák meg a Gyűjtemények, Taxonómiák, Felhasználók és Űrlapok mezőit és tartalmi felépítésüket.',
    'fields_default_instructions' => 'Állítsa be az alapértelmezett értéket.',
    'fields_display_instructions' => 'A mező neve, ahogy az a Vezérlőpulton jelenik meg.',
    'fields_duplicate_instructions' => 'Annak meghatározása, hogy ezt a mezőt szerepeltetni kell-e az elem másolásakor.',
    'fields_fieldsets_description' => 'A Fieldsetek egyszerű, flexibilis, de teljesen opcionális mezőcsoportok, amelyek Blueprintekbe, de egyéb mezőkbe is importálhatóak, újrafelhasználhatóak.',
    'fields_handle_instructions' => 'A mező sablonváltozója.',
    'fields_instructions_instructions' => 'A mező címkéje (neve) alatt látható, épp mint ez a szöveg. A Markdown támogatott.',
    'fields_instructions_position_instructions' => 'Mutassa az utasításokat a mező felett vagy alatt.',
    'fields_listable_instructions' => 'Szabályozza, hogy ez a mező megjelenhet-e (oszlop formájában) a bejegyzések listájában.',
    'fields_replicator_preview_instructions' => 'Az előnézet láthatóságának szabályozása a replikátor/sávkészletekben.',
    'fields_sortable_instructions' => 'Adja meg, hogy a mező rendezhető legyen-e a listanézetekben.',
    'fields_visibility_instructions' => 'A mező láthatóságának szabályozása a közzétételi űrlapokon.',
    'fieldset_import_fieldset_instructions' => 'Az importálandó Fieldset.',
    'fieldset_import_prefix_instructions' => 'Az az előtag, amelyet minden egyes mező megkap az importálás során, pl. `hero_`',
    'fieldset_intro' => 'A Fieldsetek a Blueprintek opcionális kísérői, újrafelhasználható részekként működnek, amelyek felhasználhatók a Blueprinteken belül is.',
    'fieldset_link_fields_prefix_instructions' => 'A hivatkozott Fieldset minden mezőjének ez lesz az előtagja. Hasznos, ha ugyanazokat a mezőket többször szeretné importálni.',
    'fieldsets_handle_instructions' => 'Erre a Fieldsetre való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'fieldsets_title_instructions' => 'Általában azt írja le, hogy mely mezők lesznek benne, például „Image Block” vagy „Meta Data”',
    'filters_view_already_exists' => 'Már létezik ilyen nevű nézet. A nézet létrehozása felülírja a meglévő nézetet ezzel a névvel.',
    'focal_point_instructions' => 'A fókuszpont beállítása lehetővé teszi a dinamikus fényképvágást, hogy a kép alanya mindig a látszódjon a képen, akármilyen arányúra is legyen az vágva.',
    'focal_point_previews_are_examples' => 'A vágási előnézetek csak példaként szolgálnak',
    'forgot_password_enter_email' => 'Adja meg e-mail címét, hogy el tudjuk küldeni a jelszó-visszaállítási linket.',
    'form_configure_blueprint_instructions' => 'Válasszon a meglévő Blueprintek közül, vagy hozzon létre újat.',
    'form_configure_email_attachments_instructions' => 'A feltöltött elemek csatolása ehhez az e-mailhez.',
    'form_configure_email_bcc_instructions' => 'A BCC-címzett(ek) e-mail címe – vesszővel elválasztva.',
    'form_configure_email_cc_instructions' => 'A CC címzett(ek) e-mail címe – vesszővel elválasztva.',
    'form_configure_email_from_instructions' => 'Hagyja üresen a webhely alapértelmezettjének használatához',
    'form_configure_email_html_instructions' => 'Az e-mail HTML változatának nézete.',
    'form_configure_email_instructions' => 'Állítsa be az új űrlap beérkezésekor elküldendő e-maileket.',
    'form_configure_email_markdown_instructions' => 'Markdown használata ennek az e-mailnek a HTML változatának létrehozásához.',
    'form_configure_email_reply_to_instructions' => 'Hagyja üresen a „Feladó” használatához.',
    'form_configure_email_subject_instructions' => 'E-mail tárgya.',
    'form_configure_email_text_instructions' => 'Az e-mail szöveges változatának nézete.',
    'form_configure_email_to_instructions' => 'A címzett e-mail címe.',
    'form_configure_handle_instructions' => 'Erre az űrlapra való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'form_configure_honeypot_instructions' => 'Honeypotként használandó mezőnév. A Honeypotok speciális mezők, amelyek a SPAM csökkentésére használnak.',
    'form_configure_intro' => 'Az űrlapok információk gyűjtésére szolgálnak a látogatóktól. Beküldések beérkezésekor értesítések küldésére és belső események küldésére _(event dispatch)_ is lehetőség nyílik.',
    'form_configure_mailer_instructions' => 'Válassza ki az e-mail küldésére szolgáló levelezőt. Hagyja üresen az alapértelmezett levelezőhöz való visszatéréshez.',
    'form_configure_store_instructions' => 'Kapcsolja ki, ha nem szeretné, hogy a beküldéseket tároljuk. Az eseményeket _(events)_ és az e-mailes értesítéseket továbbra is elküldjük.',
    'form_configure_title_instructions' => 'Általában cselekvésre ösztönzés, például „Kapcsolat felvétele”.',
    'getting_started_widget_blueprints' => 'A Blueprintekben határozhatjuk meg a tartalom létrehozásához és tárolásához használt mezőket.',
    'getting_started_widget_collections' => 'A gyűjtemények a webhely különböző típusú tartalmait tartalmazzák.',
    'getting_started_widget_docs' => 'Ismerje meg a Statamic képességeit a leghitelesebb forrásból.',
    'getting_started_widget_header' => 'A Statamic használatának megkezdése',
    'getting_started_widget_intro' => 'Az új Statamic webhely felépítésének megkezdéséhez javasoljuk, hogy kezdje ezekkel a lépésekkel.',
    'getting_started_widget_navigation' => 'Hozzon létre többszintű hivatkozáslistákat, amelyek navigációs sávok, láblécek és hasonlók megjelenítésére használhatóak.',
    'getting_started_widget_pro' => 'A Statamic Pro korlátlan számú felhasználói fiókot, szerepet, engedélyeket, Git-integrációt, revíziókat, több webhely kezelésének lehetőségét, és sok más hasznos funkciót tartalmaz!',
    'git_disabled' => 'A Git integráció jelenleg le van tiltva.',
    'git_nothing_to_commit' => 'Nincs mit commitolni.',
    'git_utility_description' => 'A Git által nyomon követett tartalom kezelése.',
    'global_search_open_using_slash' => 'A kereső használatához üsse le a <kbd>/</kbd> billentyűt',
    'global_set_config_intro' => 'A Globális Szettek kezelik a teljes webhelyen elérhető tartalmat, például a céges infókat, kapcsolatfelvételi adatokat vagy a kezelőfelület beállításait.',
    'global_set_no_fields_description' => 'Hozzáadhat mezőket a Blueprinthez, vagy manuálisan is hozzáadhat változókat a Szetthez.',
    'globals_blueprint_instructions' => 'Szabályozza a változók szerkesztésekor megjelenítendő mezőket.',
    'globals_configure_handle_instructions' => 'Erre a Globálisokra való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'globals_configure_intro' => 'A Globálisok a webhely egészén, mindenhol elérhető változók csoportja.',
    'globals_configure_title_instructions' => 'Javasoljuk a Globálisok tartalmát képviselő főnevet, például „Brand” (márka) vagy „Company” (vállalat)',
    'impersonate_action_confirmation' => 'Ezzel a felhasználóval lesz bejelentkezve. Az avatar menü segítségével visszatérhet fiókjába.',
    'licensing_config_cached_warning' => 'Az .env vagy egyéb konfigurációs fájlokban végzett módosításokat a rendszer nem észleli, amíg ki nem üríti a gyorsítótárat. Ha váratlan engedélyezési (license) eredményeket lát, ennek lehet ez is az oka. A gyorsítótár újragenerálásához használhatja a <code>php artisan config:cache</code> parancsot.',
    'licensing_error_invalid_domain' => 'Érvénytelen domén',
    'licensing_error_invalid_edition' => 'A licenc a(z) :edition kiadáshoz szól',
    'licensing_error_no_domains' => 'Nincsenek meghatározott domének',
    'licensing_error_no_site_key' => 'Nincs webhely licenckulcsa',
    'licensing_error_outside_license_range' => 'A licenc a :start és :end közti verziókra szól',
    'licensing_error_unknown_site' => 'Ismeretlen oldal',
    'licensing_error_unlicensed' => 'Nem licenszelt',
    'licensing_incorrect_key_format_body' => 'Úgy tűnik, hogy a webhelykulcs nem a megfelelő formátumban van megadva. Kérjük, ellenőrizze a kulcsot, és próbálja újra. A webhelykulcsot a statamic.com webhelyen található fiókterületről kaphatja meg. Alfanumerikus és 16 karakter hosszú. Ügyeljen arra, hogy ne használja a régi licenckulcsot, amely UUID.',
    'licensing_incorrect_key_format_heading' => 'Helytelen webhelykulcs-formátum',
    'licensing_production_alert' => 'Kérjük, vásároljon licencet, vagy adjon meg egy érvényes licenckulcsot ehhez a webhelyhez, hogy megfeleljen a Licencszerződésnek.',
    'licensing_production_alert_addons' => 'Ez az oldal kereskedelmi kiegészítőket használ. Kérjük, vásároljon megfelelő licenceket.',
    'licensing_production_alert_renew_statamic' => 'A Statamic Pro ezen verziójának használatához licenc megújítása szükséges.',
    'licensing_production_alert_statamic' => 'Ez az oldal Statamic Pro-t használ. Kérjük, vásároljon licencet.',
    'licensing_sync_instructions' => 'A statamic.com-ról származó adatok óránként egyszer frissülnek. Szinkronizáljon manuálisan az elvégzett módosítások megtekintéséhez.',
    'licensing_trial_mode_alert' => 'Olyan fizetős funkciókat vagy addonokat használ, amelyeket licencelni kell a webhely éles telepítése előtt.',
    'licensing_trial_mode_alert_addons' => 'Ez az oldal kereskedelmi kiegészítőket használ. Indítás előtt feltétlenül vásároljon licencet. Köszi!',
    'licensing_trial_mode_alert_statamic' => 'Ez az oldal Statamic Pro-t használ. Indítás előtt feltétlenül vásároljon licencet. Köszi!',
    'licensing_utility_description' => 'Az engedélyezési részletek megtekintése és megoldása.',
    'max_depth_instructions' => 'Állítsa be, hogy maximum hány szint mélységig lehessen létrehozni oldalakat. Hagyja üresen, ha nincsen korlátozva.',
    'max_items_instructions' => 'Állítsa be a kiválasztható elemek maximális számát.',
    'navigation_configure_blueprint_instructions' => 'Válasszon a meglévő Blueprintek közül, vagy hozzon létre újat.',
    'navigation_configure_collections_instructions' => 'Ezen gyűjtemények bejegyzéseihez való hivatkozás engedélyezése.',
    'navigation_configure_handle_instructions' => 'Erre a navigációra való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'navigation_configure_intro' => 'A navigációk többszintű hivatkozások listái, amelyek segítségével navigációs sávokat, láblécet, sitemapeket és egyéb kezelőfelületi navigációkat hozhatunk létre.',
    'navigation_configure_select_across_sites' => 'Engedélyezze a bejegyzések kiválasztását más webhelyekről.',
    'navigation_configure_settings_intro' => 'Engedélyezze a gyűjteményekhez való hivatkozásokat, állítsa be a maximális mélységet és egyebeket.',
    'navigation_configure_title_instructions' => 'Olyan nevet javasolunk, amely megegyezik a használat helyével, például „Main Nav” vagy „Footer Nav”.',
    'navigation_documentation_instructions' => 'További információ a navigációk létrehozásáról, konfigurálásáról és megjelenítéséről.',
    'navigation_link_to_entry_instructions' => 'Hivatkozás hozzáadása egy bejegyzéshez. További gyűjteményekhez való hivatkozás engedélyezése a konfigurációs területen.',
    'navigation_link_to_url_instructions' => 'Adjon hozzá egy hivatkozást bármely belső vagy külső URL-hez. További gyűjteményekhez való hivatkozás engedélyezése a konfigurációs területen.',
    'outpost_error_422' => 'Hiba a statamic.com webhelyen való kommunikáció során.',
    'outpost_error_429' => 'Túl sok kérés a statamic.com felé.',
    'outpost_issue_try_later' => 'Hiba történt a statamic.com webhellyel való kommunikáció során. Kérjük, próbálja újra később.',
    'outpost_license_key_error' => 'A Statamic nem tudta visszafejteni a megadott licenckulcs fájlt. Kérjük, töltse le újra a statamic.com webhelyről.',
    'password_protect_enter_password' => 'A feloldáshoz írja be a jelszót',
    'password_protect_incorrect_password' => 'Helytelen jelszó.',
    'password_protect_token_invalid' => 'Érvénytelen vagy lejárt token.',
    'password_protect_token_missing' => 'Hiányzik a biztonságos token. Erre a képernyőre az eredeti, védett URL-ről kell érkeznie.',
    'phpinfo_utility_description' => 'Ellenőrizze a PHP konfigurációs beállításait és a telepített modulokat.',
    'preference_favorites_instructions' => 'Parancsikonok, amelyek a globális keresősáv megnyitásakor jelennek meg. Alternatív megoldásként felkeresheti az oldalt, és a tetején található gombostű ikon segítségével hozzáadhatja a listához.',
    'preference_locale_instructions' => 'A vezérlőpult preferált nyelve.',
    'preference_start_page_instructions' => 'A vezérlőpultba való bejelentkezéskor megjelenítendő oldal.',
    'publish_actions_create_revision' => 'A munkapéldány alapján egy revízió jön létre. A jelenlegi revízió nem változik.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Mivel az aktuális revízió közzétételre került, és Ön jövőbeli dátumot választott, a beküldés után ez a változat vázlatként fog viselkedni a kiválasztott dátumig.',
    'publish_actions_publish' => 'A munkapéldány módosításai a bejegyzésre alkalmazódnak, és azonnal közzétételre kerülnek.',
    'publish_actions_schedule' => 'A munkapéldány módosításai a bejegyzésre alkalmazódnak, és a kiválasztott napon megjelennek.',
    'publish_actions_unpublish' => 'A jelenlegi revízió közzététele visszavonásra kerül.',
    'reset_password_notification_body' => 'Azért kapta ezt az e-mailt, mert jelszó-visszaállítási kérést kaptunk fiókjához.',
    'reset_password_notification_no_action' => 'Ha nem kérte a jelszó visszaállítását, nincs szükség további teendőkre.',
    'reset_password_notification_subject' => 'Jelszó-visszaállítási kérés',
    'role_change_handle_warning' => 'A rövid név (handle) megváltoztatása nem frissíti a rá vonatkozó hivatkozásokat a felhasználókban és csoportokban.',
    'role_handle_instructions' => 'A rövid név (handle) erre a szerepre való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'role_intro' => 'A szerepek hozzáférési és műveleti engedélyek csoportjai, amelyek hozzárendelhetők felhasználókhoz és felhasználói csoportokhoz.',
    'role_title_instructions' => 'Általában egyes számú főnév, például „Editor” vagy „Admin”.',
    'search_utility_description' => 'Kezelje és tekintse meg a Statamic keresési indexeivel kapcsolatos fontos információkat.',
    'session_expiry_enter_password' => 'Adja meg jelszavát, hogy ott folytassa, ahol abbahagyta',
    'session_expiry_logged_out_for_inactivity' => 'Automatikusan ki lett léptetve, mert egy ideje inaktív volt.',
    'session_expiry_logging_out_in_seconds' => 'Egy ideje inaktív, ezért :seconds másodperc múlva automatikusan kijelentkeztetjük. Ennek elkerüléséhez kattintson ide!',
    'session_expiry_new_window' => 'Új ablakban nyílik meg. Jöjjön vissza, miután bejelentkezett.',
    'show_slugs_instructions' => 'Megjelenjenek-e a "slug"-ok a fa nézetben.',
    'site_configure_attributes_instructions' => 'Adjon hozzá tetszőleges attribútumokat a webhely konfigurációjához, amelyek a sablonokban érhetők el. [További információ](https://statamic.dev/multi-site#additional-attributes).',
    'site_configure_handle_instructions' => 'Egyedülálló hivatkozás erre az oldalra. Nem triviális később változtatni.',
    'site_configure_lang_instructions' => 'További információ a [Languages]-ról (https://statamic.dev/multi-site#language).',
    'site_configure_locale_instructions' => 'További információ a [Locales]-ról (https://statamic.dev/multi-site#locale).',
    'site_configure_name_instructions' => 'A felhasználói név a vezérlőpulton látható.',
    'site_configure_url_instructions' => 'További információ a [webhelyek URL-jeiről](https://statamic.dev/multi-site#url).',
    'status_expired_with_date' => 'Lejárt :date',
    'status_published_with_date' => 'Közzététel dátuma :date',
    'status_scheduled_with_date' => 'Megjelenés időpontja :date',
    'taxonomies_blueprints_instructions' => 'A taxonómia kifejezései ezen Blueprintek bármelyikét felhasználhatják.',
    'taxonomies_collections_instructions' => 'Azok a gyűjtemények, amelyek ezt a taxonómiát használják.',
    'taxonomies_preview_target_refresh_instructions' => 'Az előnézet automatikus frissítése szerkesztés közben. Ennek letiltása a postMessage szolgáltatást használja.',
    'taxonomies_preview_targets_instructions' => 'Az élő előnézetben megtekinthető URL-ek. További információ a [dokumentációban](https://statamic.dev/live-preview#preview-targets).',
    'taxonomy_configure_handle_instructions' => 'Erre a taxonómiára való hivatkozásra szolgál. Nem olyan egyszerű később módosítani!',
    'taxonomy_configure_intro' => 'A taxonómia egy olyan rendszer, amely az adatokat egyedi jellemzők (például kategória vagy szín) alapján csoportosítja, osztályozza.',
    'taxonomy_configure_layout_instructions' => 'Állítsa be a taxonómia alapértelmezett elrendezését. A feltételek felülírhatják ezt a beállítást egy „elrendezés” mezővel.',
    'taxonomy_configure_template_instructions' => 'Állítsa be a taxonómia alapértelmezett sablonját.',
    'taxonomy_configure_term_template_instructions' => 'Állítsa be a taxonómia alapértelmezett sablonját. A feltételek felülírhatják ezt a beállítást egy &quot;sablon&quot; mezővel.',
    'taxonomy_configure_title_instructions' => 'Javasoljuk, hogy többes számú főnevet használjon, például „Categories” vagy „Tags”.',
    'taxonomy_next_steps_blueprints_description' => 'Kezelje az ehhez a taxonómiához rendelkezésre álló tervrajzokat és mezőket.',
    'taxonomy_next_steps_configure_description' => 'Nevezze el a taxonómiákat, társítson hozzájuk gyűjteményeket, definiáljon Blueprinteket stb.',
    'taxonomy_next_steps_create_term_description' => 'Hozza létre az első kifejezést!',
    'try_again_in_seconds' => '{0,1}Próbálja újra most.|Próbálja újra :count másodperc múlva.',
    'units.B' => ':count B',
    'units.GB' => ':count GB',
    'units.KB' => ':count KB',
    'units.MB' => ':count MB',
    'units.ms' => ':countms',
    'units.s' => ':counts',
    'updater_require_version_command' => 'Egy adott verzió igényléséhez futtassa a következő parancsot',
    'updater_update_to_latest_command' => 'A legújabb verzióra való frissítéshez futtassa a következő parancsot',
    'uploader_append_timestamp' => 'Időbélyegző hozzáfűzése',
    'uploader_choose_new_filename' => 'Válasszon új fájlnevet',
    'uploader_discard_use_existing' => 'Dobja el és használja a meglévő fájlt',
    'uploader_overwrite_existing' => 'Meglévő fájl felülírása',
    'user_activation_email_not_sent_error' => 'Az aktiváló e-mailt nem sikerült elküldeni. Kérjük, ellenőrizze az e-mail konfigurációját, és próbálja újra.',
    'user_groups_intro' => 'A felhasználói csoportok lehetővé teszik a felhasználók rendszerezését és az engedélyalapú szerepek összesített alkalmazását.',
    'user_groups_role_instructions' => 'Szerepek hozzárendelésével egyszerűen megadhatja a csoport összes felhasználójának a megfelelő engedélyeket.',
    'user_groups_title_instructions' => 'Általában többes számú főnév, például „Editors” vagy „Photographers”',
    'user_wizard_account_created' => 'A felhasználói fiók létrejött.',
    'user_wizard_intro' => 'A felhasználók szerepekhez rendelhetők. Így könnyen személyre szabhatjuk a felhasználók hozzáféréseit a Vezérlőpult részeihez.',
    'user_wizard_invitation_body' => 'A webhely kezelésének megkezdéséhez aktiválja új :site fiókját. Az Ön biztonsága érdekében az alábbi link :expiry óra után lejár. Ezt követően új jelszóért forduljon a webhely rendszergazdájához.',
    'user_wizard_invitation_intro' => 'Küldjön üdvözlő e-mailt a fiók aktiválásának részleteivel az új felhasználónak.',
    'user_wizard_invitation_share' => 'Másolja ki ezeket a hitelesítő adatokat, és ossza meg őket <code>:email</code> felhasználóval.',
    'user_wizard_invitation_share_before' => 'A felhasználó létrehozása után a rendszer megjeleníti a fiók részleteit, amelyeket megoszthat <code>:email</code> felhasználóval.',
    'user_wizard_invitation_subject' => 'Aktiválja új :site fiókját.',
    'user_wizard_roles_groups_intro' => 'A felhasználók szerepekhez rendelhetők. Így könnyen személyre szabhatjuk a felhasználók hozzáféréseit a Vezérlőpult részeihez.',
    'user_wizard_super_admin_instructions' => 'A Szuper Adminisztrátorok teljes körű hozzáféréssel rendelkeznek mindenhez a Vezérlőpulton. Ügyeljen, kinek adja ezt a szerepet!',
    'view_more_count' => 'Nézd meg :count többet',
    'width_x_height' => ':width x :height',
];
