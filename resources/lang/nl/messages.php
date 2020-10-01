<?php

return [
    'activate_account_notification_body' => 'Je ontvangt deze e-mail, omdat we een verzoek ontvangen hebben om je wachtwoord te resetten.',
    'activate_account_notification_subject' => 'Activeer je account',
    'addon_has_more_releases_beyond_license_body' => 'Je kunt bijwerken, maar moet een nieuwe licentie aanschaffen of een bestaande upgraden.',
    'addon_has_more_releases_beyond_license_heading' => 'Deze add-on heeft meer releases dan je licentie toe staat.',
    'addon_list_loading_error' => 'Er is iets misgegaan bij het laden van add-ons. Probeer het later opnieuw.',
    'asset_container_allow_uploads_instructions' => 'Indien ingeschakeld, kunnen gebruikers bestanden uploaden naar deze container.',
    'asset_container_blueprint_instructions' => 'Blueprints definiëren aanvullende aangepaste velden die te gebruiken zijn als je assets bewerkt.',
    'asset_container_create_folder_instructions' => 'Indien ingeschakeld kunnen gebruikers mappen aanmaken in deze container.',
    'asset_container_disk_instructions' => 'Filesystem disk specificeren waar bestanden moeten worden opgeslagen. Danwel lokaal of op een remote location, zoals Amazon S3. Ze kunnen geconfigureerd worden in `config/filesystems.php`.',
    'asset_container_handle_instructions' => 'Wordt gebruikt om aan de voorkant van de website aan deze container te refereren. Het is niet persé eenvoudig om nadien te wijzigen.',
    'asset_container_intro' => 'Media en documenten worden opgeslagen in mappen op de server op op andere file-storage-diensten. Elke van deze locaties noemt men een container.',
    'asset_container_move_instructions' => 'Indien ingeschakeld, kunnen gebruikers bestanden verplaatsen binnenin deze container.',
    'asset_container_quick_download_instructions' => 'Indien ingeschakeld, wordt er een downloadknop weergegeven in de asset-manager.',
    'asset_container_rename_instructions' => 'Indien ingeschakeld, kunnen gebruikers files hernoemen in deze container.',
    'asset_container_title_instructions' => 'Meestal een zelfstandig naamwoord in meervoud, zoals  Afbeeldingen of Documenten.',
    'asset_folders_directory_instructions' => 'We adviseren om spaties en speciale karakters niet te gebruiken om URL\'s netjes te houden.',
    'blueprints_intro' => 'Blueprints definiëren en organiseren velden zodat je blauwdrukken kunt maken voor de inhoud van collecties, formulieren en andere datatypes.',
    'blueprints_title_instructions' => 'Meestal een zelfstandig naamwoord in enkelvoud, zoals Artikel of Product.',
    'cache_utility_application_cache_description' => 'Laravel\'s samengevoegde cache die wordt gebruikt door Statamic, add-ons van derden en composer packages.',
    'cache_utility_description' => 'Beheer en bekijk belangrijke informatie over Statamic\'s verschillende caching--lagen.',
    'cache_utility_image_cache_description' => 'De afbeeldingencache slaat kopieën van alle aangepaste en herschaalde afbeeldingen.',
    'cache_utility_stache_description' => 'De Stache is de content-cache van Statamic. Het functioneert als een soort database. Het wordt automatisch gegenereerd op basis van de inhoudsbestanden van de website.',
    'cache_utility_static_cache_description' => 'Statische pagina\'s passeren Statamic volledige en  en worden rechtstreeks vanaf de server getoond. Dit zorgt voor maximale performance.',
    'collection_configure_date_behavior_private' => 'Privé - Verborgen in lijsten, URL\'s geven een 404-error.',
    'collection_configure_date_behavior_public' => 'Openbaar - Altijd zichtbaar',
    'collection_configure_date_behavior_unlisted' => 'Verborgen - Verborgen in lijsten, URL\'s functioneren',
    'collection_configure_dated_instructions' => 'Publiceerdatums kunnen gebruikt worden om content in te plannen of te laten verlopen.',
    'collection_configure_handle_instructions' => 'Wordt gebruikt om aan deze collectie te refereren vanaf de voorkant van de website. Het is niet persé eenvoudig om nadien te wijzigen.',
    'collection_configure_intro' => 'Een collectie is een groep van gerelateerde entries die hetzelfde gedrag, eigenschappen en instellingen delen.',
    'collection_configure_layout_instructions' => 'Configureer de standaard layout van deze collectie. Entries kunnen deze overschrijven door een `template` field te gebruiken die `layout` heet. Het is ongebruikelijk om deze instellingen te wijzigen.',
    'collection_configure_template_instructions' => 'Configureer het standard template van deze collectie. Entries kunnen deze overschrijven door een `template` field te gebruiken.',
    'collection_configure_title_instructions' => 'We adviseren een zelfstandig naamwoord in meervoud, zoals "Artikelen" of "Producten".',
    'collection_next_steps_configure_description' => 'Configureer URL\'s en routes, definieer blueprints, datumgedrag, rangschikking en andere opties.',
    'collection_next_steps_create_entry_description' => 'Maak een eerste entry aan of een aantal tijdelijke placeholders, het is aan jou.',
    'collection_next_steps_documentation_description' => 'Leer meer over collecties, hoe ze werken en hoe je ze moet configureren.',
    'collection_next_steps_scaffold_description' => 'Genereer snel een aantal lege blauwdrukken en frontend-templates gebaseerd op de naam van deze collectie.',
    'collection_scaffold_instructions' => 'Kies welke lege resources er gegenereerd moeten worden. Bestaande bestanden worden niet overschreven.',
    'collections_amp_instructions' => 'Activeer Accelerated Mobile Pages (AMP). Voeg automatisch routes en URL\'s toe voor entries in deze collectie. Lees meer in de [documentatie](https://statamic.dev/amp)',
    'collections_blueprint_instructions' => 'Entries in deze collectie mogen elke van deze blueprints gebruiken.',
    'collections_default_publish_state_instructions' => 'Tijdens het aanmaken van entries in deze collectie wordt de publiceerknop standaard op **aan** gezet in plaats van **uit** (concept).',
    'collections_future_date_behavior_instructions' => 'Hoe entries met een datum in de toekomst zich moeten gedragen.',
    'collections_links_instructions' => 'Entries in deze collectie mogen links (doorverwijzingen) naar andere entries of URL\'s bevatten.',
    'collections_mount_instructions' => 'Kies een entry waarop deze collectie gemonteerd moet worden. Lees meer in de [documentatie](https://statamic.dev/colletions-and-entrie#mounting).',
    'collections_orderable_instructions' => 'Activeer handmatig ordenen met drag & drop.',
    'collections_past_date_behavior_instructions' => 'Hoe entries met een datum in het verleden zich moeten gedragen.',
    'collections_route_instructions' => 'De route definieert het URL-patroon.',
    'collections_sort_direction_instructions' => 'De standaard sorteervolgorde.',
    'collections_taxonomies_instructions' => 'Koppel entries in deze collectie aan taxonomieën. Velden worden automatisch toegevoegd aan publiceerformulieren.',
    'email_utility_configuration_description' => 'E-mailsettings worden geconfigureerd in <code>:path</code>',
    'email_utility_description' => 'Bekijk e-mailconfiguratiesettings and verstuur testmails.',
    'expect_root_instructions' => 'Beschouwd de eerste pagina in een boomstructuur als "root" of "home" -pagina.',
    'field_conditions_instructions' => 'Wanneer dit veld getoond of verborgen moet worden.',
    'field_desynced_from_origin' => 'Gedesynchroniseerd van de oorsprong. CKlik om te synchroniseren en de oorspronkelijke waarde te herstellen.',
    'field_synced_with_origin' => 'Gesynchroniseerd met de oorsprong. Klik of wijzig het veld om te desynchroniseren.',
    'field_validation_advanced_instructions' => 'Voeg meer geavanceerde validatie toe aan dit veld.',
    'field_validation_required_instructions' => 'Bepaal wanneer dit veld verplicht is.',
    'fields_blueprints_description' => 'Blueprints definieëren de velden voor inhoudsstructuren, zoals collecties, taxonomieën, gebruikers en formulieren.',
    'fields_display_instructions' => 'Het label van dit veld zoals getoond in het Control Panel.',
    'fields_fieldsets_description' => 'Fieldsets zijn simpele, flexibele, en optionele grouperingen van velden die het mogelijk maken om velden te organiseren en herbruiken.',
    'fields_handle_instructions' => 'De variabelenaam van dit veld te gebruiken in het template.',
    'fields_instructions_instructions' => 'Wordt getoond onder het velds weergavelabel, net zoals deze tekst. Markdown is toegestaan.',
    'fields_listable_instructions' => 'Bepaal of dit veld getoond moet worden als kolom in overzichtstabellen.',
    'fieldset_import_fieldset_instructions' => 'De fieldset die geïmporteerd moet worden.',
    'fieldset_import_prefix_instructions' => 'Het voorvoegsel dat op ieder veld toegepast moet worden als ze worden geïmporteerd. Bijv: hero_',
    'fieldset_intro' => 'Fieldsets zijn een optionele toevoeging aan blueprints, het zijn herbruikbare partials die in blueprints gebruikt kunnen worden.',
    'fieldset_link_fields_prefix_instructions' => 'Aan elk veld in het gelinkte fieldset wordt deze waarde voorgevoegd. Handig als je dezelfde velden meerdere malen wilt importeren.',
    'fieldsets_handle_instructions' => 'Wordt gebruikt om ergens anders naar deze veldset te verwijzen. Het is niet triviaal om later te veranderen.',
    'fieldsets_title_instructions' => 'Omschrijft normaal gesproken welke velden hierin zitten, zoals Afbeeldingsblok.',
    'focal_point_instructions' => 'Het instellen van een focuspunt maakt het mogelijk om foto\'s dynamisch te croppen waarbij het onderwerp altijd in het frame blijft.',
    'focal_point_previews_are_examples' => 'De previews van het croppen zijn slechts ter illustratie.',
    'forgot_password_enter_email' => 'Voer je e-mailadres in, zodat we je een wachtwoordresetlink kunnen sturen.',
    'form_configure_blueprint_instructions' => 'Kies uit bestaande blueprints of maak een nieuwe aan.',
    'form_configure_email_from_instructions' => 'Laat leeg om terug te vallen op de sitestandaard.',
    'form_configure_email_html_instructions' => 'De view voor de html-versie van deze e-mail.',
    'form_configure_email_instructions' => 'Configureer e-mails die verzonden worden als er nieuwe formulierinzendingen worden ontvangen.',
    'form_configure_email_reply_to_instructions' => 'Laat leeg om terug te vallen op de zender.',
    'form_configure_email_subject_instructions' => 'E-mailonderwerp',
    'form_configure_email_text_instructions' => 'De view voor de tekstversie van deze e-mail.',
    'form_configure_email_to_instructions' => 'Het e-mailadres van de ontvanger.',
    'form_configure_handle_instructions' => 'Wordt gebruikt om aan dit formulier te refereren aan de voorkant van de website. Het is niet persé eenvoudig om dit nadien te wijzigen.',
    'form_configure_honeypot_instructions' => 'De veldnaam die gebruikt moet worden als honeypot. Honeypot\'s zijn speciale velden die gebruikt worden om spam te verminderen.',
    'form_configure_intro' => 'Formulieren worden gebruikt om informatie van bezoekers te verzamelen en events en notificaties te triggeren als er een formulier wordt ingevuld.',
    'form_configure_store_instructions' => 'Schakel uit om formulierinzendingen niet meer op te slaan. Events en e-mailnotificaties worden nog steeds verzonden.',
    'form_configure_title_instructions' => 'Meestal een call-to-action, zoals "Neem contact op".',
    'getting_started_widget_blueprints' => 'Blueprints definiëren de custom velden die gebruikt worden om content te maken en op te slaan.',
    'getting_started_widget_collections' => 'Collecties bevatten verschillende typen content van de website.',
    'getting_started_widget_docs' => 'Leer Statamic kennen door zijn mogelijkheden op de juiste manier aan te leren.',
    'getting_started_widget_header' => 'Starten met Statamic 3',
    'getting_started_widget_intro' => 'Om te beginnen met het bouwen van je nieuwe Statamic 3 site, raden we je aan om met deze stappen te beginnen.',
    'getting_started_widget_navigation' => 'Creëer multi-level lijsten van links die gebruik kunnen worden om navigaties, footers etc. te genereren.',
    'getting_started_widget_pro' => 'Statamic Pro voegt onbeperkte gebruikersaccounts, rollen, machtigingen, git-integratie, revisies, multi-site en meer toe!',
    'git_disabled' => 'Statamic Git-integratie is momenteel uitgeschakeld.',
    'git_nothing_to_commit' => 'Niets te doen, inhoudspaden schoon!',
    'git_utility_description' => 'Beheer door Git bijgehouden inhoud.',
    'global_search_open_using_slash' => 'Focus zoekveld met de <kbd>/</kbd> toets',
    'global_set_config_intro' => 'Met globalsets kun je content beheren die sitebreed beschikbaar moet zijn, zoals bedrijfsgegevens, contactinformatie of frontend-settings.',
    'globals_blueprint_instructions' => 'Welke velden weergegeven moeten worden als de variabelen worden bewerkt.',
    'globals_configure_handle_instructions' => 'Wordt gebruikt om deze globalset aan de voorkant aan te roepen. Het is niet persé eenvoudig om dit nadien te wijzigen.',
    'globals_configure_intro' => 'Een globalset is een groep met variabelen die over de gehele frontend beschikbaar is.',
    'globals_configure_title_instructions' => 'We adviseren een zelfstandig naamwoord die de inhoud van de set omschrijft. Bijv: "Merk" of "Bedrijf"',
    'licensing_error_invalid_domain' => 'Ongeldig domein',
    'licensing_error_invalid_edition' => 'Licentie is voor :edition editie',
    'licensing_error_no_domains' => 'Geen domeinen gedefinieerd',
    'licensing_error_no_site_key' => 'Geen site-licentiesleutel',
    'licensing_error_outside_license_range' => 'Licentie geldig voor versies :start en :end',
    'licensing_error_unknown_site' => 'Onbekende site',
    'licensing_error_unlicensed' => 'Zonder licentie',
    'licensing_production_alert' => 'Koop of voer een geldige licentiesleutel in voor deze site om te voldoen aan de licentieovereenkomst.',
    'licensing_sync_instructions' => 'Gegevens van statamic.com worden eenmaal per uur gesynchroniseerd. Forceer een synchronisatie om alle wijzigingen te zien die je hebt aangebracht.',
    'licensing_trial_mode_alert' => 'U geniet van betaalde functies of add-ons waarvoor een licentie nodig is voordat u deze site implementeert. Veel plezier!',
    'licensing_utility_description' => 'Bekijk en los licentiegegevens op.',
    'max_depth_instructions' => 'Stel een maximum aan het aantal pagina\'s dat genest mag worden. Laat leeg voor geen limiet.',
    'max_items_instructions' => 'Stel een maximum aan het aantal items dat geselecteerd mag worden.',
    'navigation_configure_collections_instructions' => 'Sta toe dat er naar entries in deze collectie gelinkt mogen worden.',
    'navigation_configure_handle_instructions' => 'Wordt gebruikt om aan deze navigatie te refereren aan in de frontend. Het is niet persé eenvoudig om dit nadien te wijzigen.',
    'navigation_configure_intro' => 'Navigaties zijn multi-level lijsten met links die gebruikt kunnen worden om navigatiemenu\'s, footers, sitemaps en andere frontendnavigaties te maken.',
    'navigation_configure_settings_intro' => 'Sta toe dat er naar collecties gelinkt mag worden en stel een maximum diepte en andere instellingen in.',
    'navigation_configure_title_instructions' => 'We raden je aan een naam te kiezen die omschrijft waar dit gebruikt wordt, zoals: "Hoofdmenu" of "Footermenu".',
    'navigation_documentation_instructions' => 'Leer meer over het bouwen, configureren en renderen van navigaties.',
    'navigation_link_to_entry_instructions' => 'Voeg een link naar een entry toe. Sta toe dat er naar andere collecties gelinkt mag worden in de configuratie.',
    'navigation_link_to_url_instructions' => 'Voeg een link toe naar een interne of externe URL. Sta toe dat er naar entries gelinkt mag worden in de configuratie.',
    'outpost_error_422' => 'Fout bij het communiceren met statamic.com.',
    'outpost_error_429' => 'Te veel verzoeken aan statamic.com.',
    'outpost_issue_try_later' => 'Er is een probleem opgetreden bij de communicatie met statamic.com. Probeer het later opnieuw.',
    'phpinfo_utility_description' => 'Controleer de PHP-configuratie-instellingen en geïnstalleerde modules.',
    'publish_actions_create_revision' => 'Een revisie wordt gemaakt op basis van de werkkopie. De huidige revisie zal niet wijzigen.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Aangezien de huidige revisie gepubliceerd is en je een datum in de toekomst hebt gekozen, zal na opslaan de revisie in concept blijven tot de geselecteerde datum.',
    'publish_actions_publish' => 'Aanpassingen aan de werkkopie worden toegepast op de entry en onmiddelijk gepubliceerd.',
    'publish_actions_schedule' => 'Aanpassingen aan de werkkopie worden toegepast op de entry en gepubliceerd op de publicatiedatum.',
    'publish_actions_unpublish' => 'De huidige revisie wordt gedepubliceerd.',
    'rename_asset_warning' => 'Het hernoemen van een item werkt geen verwijzingen ernaar bij, wat kan resulteren in verbroken links op je site.',
    'reset_password_notification_body' => 'Je ontvangt deze e-mail, omdat we een verzoek hebben gekregen om je wachtwoord te resetten.',
    'reset_password_notification_no_action' => 'Als je geen verzoek hebt gedaan om je wachtwoord te resetten dan hoef je verder niets te doen.',
    'reset_password_notification_subject' => 'Wachtwoordresetnotificatie',
    'role_change_handle_warning' => 'Het aanpassen van de handle zal niet worden gereflecteerd in eventuele referenties in gebruikers en groepen.',
    'role_handle_instructions' => 'Handles worden gebruikt om deze rol aan te refereren in de frontend. Dit is niet eenvoudig te wijzigen.',
    'role_intro' => 'Rollen zijn groepen van toegangs- en actiepermissies die je kunt toewijzen aan gebruikers en groepen.',
    'role_title_instructions' => 'Doorgaans een zelfstandig naamwoord in enkelvoud, zoals Redacteur of Administrator.',
    'search_utility_description' => 'Beheer en bekijk belangrijke informatie over de zoekindex van Statamic.',
    'session_expiry_enter_password' => 'Voer je wachtwoord in om verder te gaan waar je gebleven was',
    'session_expiry_logged_out_for_inactivity' => 'Je bent uitlogd, omdat je een tijd inactief bent geweest.',
    'session_expiry_logging_out_in_seconds' => 'Je bent een tijd inactief geweest en wordt uitgelogd over :seconds seconden. Klik hier om je sessie te verlengen.',
    'session_expiry_new_window' => 'Opent in een nieuw venster. Kom terug als je ingelogd bent.',
    'tab_sections_instructions' => 'De velden in iedere sectie worden gegroepeerd in tabs. Voeg nieuwe velden toe, herbruik bestaande velden of importeer groepen van velden uit reeds bestaande fieldsets.',
    'taxonomies_blueprints_instructions' => 'Termen in deze taxonomie mogen in al deze blueprints gebruikt worden.',
    'taxonomies_collections_instructions' => 'De collectie die deze taxonomie gebruikt.',
    'taxonomy_configure_handle_instructions' => 'Wordt gebruikt om in de frontend aan deze taxonomie te refereren. Het is niet persé eenvoudig om dit nadien te wijzigen.',
    'taxonomy_configure_intro' => 'Een taxonomie is een systeem waarmee je data kunt classificeren op basis van unieke eigenschappen, zoals categorie of kleur.',
    'taxonomy_configure_title_instructions' => 'We adviseren een meervoudig zeflstandig naamwoord, zoals "Categorieën" of "Tags".',
    'taxonomy_next_steps_configure_description' => 'Configureer naamgeving, associeer collecties, definieer blueprints en meer.',
    'taxonomy_next_steps_create_term_description' => 'Maak de eerste term of een aantal tijdelijke termen, het is aan jou.',
    'taxonomy_next_steps_documentation_description' => 'Leer meer over taxonomieën, hoe ze werken en hoe je ze kunt configureren.',
    'try_again_in_seconds' => '{0,1} Probeer het nu opnieuw. Probeer het over :count seconden.',
    'updates_available' => 'Updates beschikbaar!',
    'user_groups_handle_instructions' => 'Wordt gebruikt om in de frontend aan een gebruiker in deze groep te refereren. Het is niet persé eenvoudig om dit nadien te wijzigen.',
    'user_groups_intro' => 'Met gebruikersgroepen kun je gebruikers groeperen en bevoegdheden in één keer toepassen.',
    'user_groups_role_instructions' => 'Wijs rollen toe om gebruikers in deze groep alle bijbehorende bevoegdheden te geven.',
    'user_groups_title_instructions' => 'Meestal een meervoudig zelfstandig naamwoord, zoals Redacteuren of Fotografen.',
    'user_wizard_account_created' => 'Het gebruikersaccount is aangemaakt.',
    'user_wizard_email_instructions' => 'Het e-mailadres is tevens gebruikersnaam en moet uniek zijn.',
    'user_wizard_intro' => 'Gebruikers kunnen rollen toegewezen krijgen om hun eigen bevoegdheden, toegang en mogelijkheden in het Control Panel aan te passen.',
    'user_wizard_invitation_body' => 'Activeer je nieuwe Statamic-account voor :site om te beginnen met het beheren van deze website. Voor je eigen veiligheid, onderstaande link verloopt na :expiry uur. Hierna zul je contact op moeten nemen met de administrator van deze site voor een nieuw wachtwoord.',
    'user_wizard_invitation_intro' => 'Stuur een welkomstmail naar deze gebruiker met instructies over hoe het account geactiveerd kan worden.',
    'user_wizard_invitation_share' => 'Kopieer deze inloggegevens en deel ze met <code>:email</code> via een communicatiekanaal naar keuze.',
    'user_wizard_invitation_share_before' => 'Nadat je een gebruiker hebt aangemaakt, komen er instructies beschikbaar die je via een communicatiekanaal naar keuze kunt delen met <code>:email</code>.',
    'user_wizard_invitation_subject' => 'Activeer je nieuwe Statamic-account voor :site',
    'user_wizard_name_instructions' => 'Laat de naam leeg om de gebruiker deze te laten invullen.',
    'user_wizard_roles_groups_intro' => 'Aan gebruikers kunnen rollen toegewezen worden die hun bevoegdheden, toegang en mogelijkheden in het Control Panel aanpassen.',
    'user_wizard_super_admin_instructions' => 'Super admins hebben volledige controle over het Control Panel en kunnen alles wijzigen. Ga hier verstandig mee om.',
];
