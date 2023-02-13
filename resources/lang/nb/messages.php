<?php

return [
    'activate_account_notification_body' => 'Du får denne e-posten fordi vi har mottatt en forespørsel om tilbakestilling av passordet som er tilknyttet kontoen din.',
    'activate_account_notification_subject' => 'Aktiver kontoen din',
    'addon_has_more_releases_beyond_license_body' => 'Du kan oppdatere, men da må du oppgradere eller kjøpe en ny lisens.',
    'addon_has_more_releases_beyond_license_heading' => 'Denne utvidelsen har flere versjoner utover din lisensgrense.',
    'addon_list_loading_error' => 'Noe gikk galt under lasting av utvidelser. Prøv igjen senere.',
    'asset_container_allow_uploads_instructions' => 'Når det er aktivert, kan brukere laste opp filer til denne beholderen.',
    'asset_container_blueprint_instructions' => 'Blueprint definerer ytterligere tilpassede felt som er tilgjengelige ved redigering av innhold.',
    'asset_container_create_folder_instructions' => 'Når det er aktivert, kan brukere opprette mapper i denne beholderen.',
    'asset_container_disk_instructions' => 'Filsystemdisker spesifiserer hvor filer lagres – enten lokalt eller på en ekstern lagringsenhet, for eksempel på Amazon S3. De kan konfigureres i `config/filesystems.php`',
    'asset_container_handle_instructions' => 'Brukes for å henvise til denne beholderen på frontend. Det er vanskelig å endre senere.',
    'asset_container_intro' => 'Medie- og dokumentfiler ligger i mapper på serveren eller andre fillagringstjenester. Hver av disse plasseringene kalles en beholder.',
    'asset_container_move_instructions' => 'Når dette er aktivert, kan brukere flytte rundt på filer inne i beholderen.',
    'asset_container_quick_download_instructions' => 'Når dette er aktivert, blir en knapp for hurtignedlasting lagt til i Filer.',
    'asset_container_rename_instructions' => 'Når dette er aktivert, kan brukere endre navn på filer i denne beholderen.',
    'asset_container_source_preset_instructions' => 'Opplastede bilder vil bli prosessert ved hjelp av denne forhåndsinnstillingen.',
    'asset_container_title_instructions' => 'Vanligvis et substantiv i flertall, som bilder eller dokumenter.',
    'asset_container_warm_intelligent_instructions' => 'Genererer passende bildederivater ved opplasting.',
    'asset_container_warm_presets_instructions' => 'Velg hvilke bildederivater du ønsker å generere ved opplasting.',
    'asset_folders_directory_instructions' => 'For å holde URL-ene rene anbefaler vi at du unngår bruk av mellomrom og spesialtegn.',
    'asset_replace_confirmation' => 'Referanser til denne filen i innholdet vil bli oppdatert til filen du velger nedenfor.',
    'asset_reupload_confirmation' => 'Er du sikker på at du vil laste opp på nytt?',
    'asset_reupload_warning' => 'Du kan oppleve problemer med mellomlagring i nettleser eller på server. Du kan vurdere å erstatte filen i stedet.',
    'blueprints_hidden_instructions' => 'Skjuler knapper for opprettelse av nytt innhold med denne blueprinten i kontrollpanelet',
    'blueprints_intro' => 'Blueprint definerer og organiserer felt for å opprette innholdsmodellene for samlinger, skjemaer og andre datatyper.',
    'blueprints_title_instructions' => 'Vanligvis et substantiv i entall, for eksempel artikkel eller produkt.',
    'cache_utility_application_cache_description' => 'Laravels samlede cache som brukes av Statamic, tredjeparts tillegg og Composer-pakker.',
    'cache_utility_description' => 'Administrer og vis viktig informasjon om Statamics ulike cachinglag.',
    'cache_utility_image_cache_description' => 'Bildecachen lagrer kopier av alle bilder som er transformert eller har fått endret størrelse.',
    'cache_utility_stache_description' => 'Stache er Statamics innholdslager og fungerer veldig likt som en database. Det genereres automatisk fra innholdsfilene.',
    'cache_utility_static_cache_description' => 'For å sikre maksimal ytelse omgår statiske sider Statamic fullstendig og gjengis direkte fra serveren.',
    'choose_entry_localization_deletion_behavior' => 'Velg handlingen du vil utføre på de lokaliserte oppføringene.',
    'collection_configure_date_behavior_private' => 'Privat - Skjult fra lister, URL-er 404',
    'collection_configure_date_behavior_public' => 'Offentlig - Alltid synlig',
    'collection_configure_date_behavior_unlisted' => 'Ulistet - Skjult fra lister, synlige URL-er',
    'collection_configure_dated_instructions' => 'Publiseringsdatoer kan brukes til å planlegge utgivelse og utløp av innhold.',
    'collection_configure_handle_instructions' => 'Brukes for å henvise til denne samlingen på frontend. Det er vanskelig å endre senere.',
    'collection_configure_intro' => 'En samling er en gruppe relaterte oppføringer som deler atferd, attributter og innstillinger.',
    'collection_configure_layout_instructions' => 'Angi standardlayout for denne samlingen. Oppføringer kan overstyre denne innstillingen med et `mal`-felt med navnet `layout`. Det er ikke vanlig å endre denne innstillingen.',
    'collection_configure_origin_behavior_instructions' => 'Ved oversettelse av en oppføring, hvilken kilde skal brukes som original?',
    'collection_configure_origin_behavior_option_active' => 'Bruk det aktive området hvor oppføringen blir redigert',
    'collection_configure_origin_behavior_option_root' => 'Bruk området oppføringen original ble opprettet.',
    'collection_configure_origin_behavior_option_select' => 'La brukeren velge kilde',
    'collection_configure_propagate_instructions' => 'Overfør automatisk nye oppføringer til alle konfigurerte nettsteder.',
    'collection_configure_require_slugs_instructions' => 'Om oppføringer må ha permalenker eller ikke.',
    'collection_configure_template_instructions' => 'Angi standardmal for denne samlingen. Oppføringer kan overstyre denne innstillingen med et `mal`-felt.',
    'collection_configure_title_format_instructions' => 'Angi dette for at oppføringene i denne samlingen skal generere titlene automatisk. Les mer i [dokumentasjon](https://statamic.dev/collections#titles).',
    'collection_configure_title_instructions' => 'Vi anbefaler et substantiv i flertall, for eksempel "Artikler" eller "Produkter".',
    'collection_next_steps_blueprints_description' => 'Administrer blueprintene og feltene som er tilgjengelige for denne samlingen.',
    'collection_next_steps_configure_description' => 'Konfigurer URL-er og ruter, definer blueprint, datoatferd, sortering og andre alternativer.',
    'collection_next_steps_create_entry_description' => 'Opprett den første oppføringen eller lag en håndfull plassholderoppføringer, det er opp til deg.',
    'collection_next_steps_scaffold_description' => 'Generer raskt indeks- og detaljoversikter fra navnet på samlingen.',
    'collection_revisions_instructions' => 'Aktiver revisjoner for denne samlingen.',
    'collection_scaffold_instructions' => 'Velg hvilke tomme visninger som skal genereres. Eksisterende filer overskrives ikke.',
    'collections_blueprint_instructions' => 'Oppføringer i denne samlingen kan bruke en hvilken som helst av disse blueprintene.',
    'collections_default_publish_state_instructions' => 'Under opprettelse av nye oppføringer i denne samlingen vil den publiserte veksleknappen som standard være **true** i stedet for **false** (kladd).',
    'collections_future_date_behavior_instructions' => 'Hvordan framtidig daterte oppføringer skal oppføre seg.',
    'collections_links_instructions' => 'Oppføringer i denne samlingen kan inneholde lenker (omdirigeringer) til andre oppføringer eller URL-er.',
    'collections_mount_instructions' => 'Velg en oppføring som denne samlingen skal monteres på. Les mer i [dokumentasjon](https://statamic.dev/collections-and-entries#mounting).',
    'collections_orderable_instructions' => 'Aktiver manuell sortering via dra og slipp.',
    'collections_past_date_behavior_instructions' => 'Hvordan tidligere daterte oppføringer skal oppføre seg.',
    'collections_preview_targets_instructions' => 'URL-ene som skal kunne vises i forhåndsvisning. Les mer i [dokumentasjon](https://statamic.dev/live-preview#preview-targets).',
    'collections_route_instructions' => 'Ruten styrer oppføringenes URL-mønster. Les mer i [dokumentasjon](https://statamic.dev/collections#routing).',
    'collections_sort_direction_instructions' => 'Standard sorteringsretning.',
    'collections_taxonomies_instructions' => 'Koble oppføringer i denne samlingen til taksonomier. Felter legges automatisk til for å publisere skjemaer.',
    'email_utility_configuration_description' => 'E-postinnstillinger er konfigurert i <code>:path</code>',
    'email_utility_description' => 'Sjekk konfigurasjonsinnstillinger for e-post, og test utsending av e-poster.',
    'entry_origin_instructions' => 'Den nye oversettelsen vil arve innhold fra oppføringen i det valgte området.',
    'expect_root_instructions' => 'Se på den første siden i treet som en "rot" eller "startside".',
    'field_conditions_always_save_instructions' => 'Alltid lagre innhold i felt, uavhengig av om vilkårene er gyldig.',
    'field_conditions_instructions' => 'Når dette feltet skal vises eller skjules.',
    'field_desynced_from_origin' => 'Avsynkroniser fra opprinnelse. Klikk for å synkronisere og gå tilbake til opprinnelsesverdien.',
    'field_synced_with_origin' => 'Synkronisert med opprinnelse. Klikk eller rediger feltet for å avsynkronisere.',
    'field_validation_advanced_instructions' => 'Legg til mer avansert validering i dette feltet.',
    'field_validation_required_instructions' => 'Kontroller om dette feltet er obligatorisk eller ikke.',
    'field_validation_sometimes_instructions' => 'Valider dette feltet kun når det er synlig eller blir lagret.',
    'fields_blueprints_description' => 'Blueprint definerer feltene for innholdsstrukturer som samlinger, taksonomier, brukere og skjemaer.',
    'fields_default_instructions' => 'Angi standardverdien.',
    'fields_display_instructions' => 'Feltets etikett vises i kontrollpanelet.',
    'fields_duplicate_instructions' => 'Angi om dette feltet skal inkluderes ved duplisering.',
    'fields_fieldsets_description' => 'Feltsett er enkle, fleksible og helt valgfri grupperinger av felter, som gjør det mulig å organisere gjenbrukbare, forhåndskonfigurerte felter.',
    'fields_handle_instructions' => 'Feltets malvariabel.',
    'fields_instructions_instructions' => 'Vises under feltets visningsetikett, slik som denne teksten. Markdown støttes.',
    'fields_instructions_position_instructions' => 'Vis instruksjoner over eller under feltet.',
    'fields_listable_instructions' => 'Styrer om feltet skjules eller vises i lister.',
    'fields_visibility_instructions' => 'Styrer om feltet vises i publiseringsskjema.',
    'fieldset_import_fieldset_instructions' => 'Feltsettet som skal importeres.',
    'fieldset_import_prefix_instructions' => 'Prefikset som skal brukes på hvert felt når de importeres. For eksempel hero_',
    'fieldset_intro' => 'Feltsett er en valgfri ledsager til blueprint og fungerer som gjenbrukbare delreplikaer som kan brukes i blueprint.',
    'fieldset_link_fields_prefix_instructions' => 'Hvert felt i det tilkoblede feltsettet vil ha dette som prefiks. Nyttig hvis du vil importere de samme feltene flere ganger.',
    'fieldsets_handle_instructions' => 'Brukes for å henvise til dette feltet andre steder. Det er vanskelig å endre senere.',
    'fieldsets_title_instructions' => 'Beskriver vanligvis hvilke felter som vil være inni, for eksempel Image Block eller Meta Data',
    'focal_point_instructions' => 'Innstilling av et fokuspunkt tillater dynamisk beskjæring av bilder med et motiv som forblir i rammen.',
    'focal_point_previews_are_examples' => 'Forhåndsvisninger av beskjæringer er kun eksempler',
    'forgot_password_enter_email' => 'Oppgi e-postadressen din, så sender vi deg en lenke for tilbakestilling av passord.',
    'form_configure_blueprint_instructions' => 'Velg blant eksisterende blueprinter, eller opprett en ny.',
    'form_configure_email_attachments_instructions' => 'Knytt opplastede vedlegg til denne e-posten.',
    'form_configure_email_from_instructions' => 'La stå tomt for å gå tilbake til standarden for nettstedet',
    'form_configure_email_html_instructions' => 'Visning av HTML-versjonen av denne e-posten.',
    'form_configure_email_instructions' => 'Konfigurer e-postene som skal sendes når den nye skjemainnsendelsen mottas.',
    'form_configure_email_markdown_instructions' => 'Gjengi HTML-versjonen av e-posten ved hjelp av Markdown.',
    'form_configure_email_reply_to_instructions' => 'La stå tomt for å gå tilbake til avsenderen.',
    'form_configure_email_subject_instructions' => 'E-postens emnelinje.',
    'form_configure_email_text_instructions' => 'Visningen til tekstversjonen av denne e-posten.',
    'form_configure_email_to_instructions' => 'Mottakerens e-postadresse.',
    'form_configure_handle_instructions' => 'Brukes for å henvise til dette skjemaet på frontend. Det er vanskelig å endre senere.',
    'form_configure_honeypot_instructions' => 'Feltnavn som skal brukes som en honeypot. En honeypot er et spesielt felt som brukes for å redusere botspam.',
    'form_configure_intro' => 'Skjemaer brukes for å samle inn informasjon om besøkende og sende varsler og meldinger når det er nye innsendelser.',
    'form_configure_store_instructions' => 'Deaktiver for å stanse lagring av innsendelser. Varsler og e-post-meldinger vil fortsatt bli sendt.',
    'form_configure_title_instructions' => 'Vanligvis en oppfordring til handling, f.eks. "Kontakt oss".',
    'getting_started_widget_blueprints' => 'Blueprint definerer de brukerdefinerte feltene som anvendes for å opprette og lagre innhold.',
    'getting_started_widget_collections' => 'Samlinger inneholder de ulike typene innhold på et nettsted.',
    'getting_started_widget_docs' => 'Lær om Statamic ved å forstå løsningens muligheter på riktig måte.',
    'getting_started_widget_header' => 'Komme i gang med Statamic',
    'getting_started_widget_intro' => 'Når du skal bygge opp ditt nye Statamic-nettsted, anbefaler vi at du starter med disse trinnene.',
    'getting_started_widget_navigation' => 'Opprett flernivålister med lenker som kan brukes for å gjengi navigeringsfelt, bunntekster og så videre.',
    'getting_started_widget_pro' => 'Statamic Pro byr på ubegrenset antall brukerkontoer, roller, tillatelser, Git-integrering, revisjoner, multi-site og mer!',
    'git_disabled' => 'Statamic Git-integrering er for øyeblikket deaktivert.',
    'git_nothing_to_commit' => 'Ingenting å oppdatere, innholdsbanene er rene!',
    'git_utility_description' => 'Administrer Git-sporet innhold.',
    'global_search_open_using_slash' => 'Fokuser globalt søk med <kbd>/</kbd>-tasten',
    'global_set_config_intro' => 'Globale sett håndterer tilgjengelig innhold på tvers av hele nettstedet, inkludert bedriftsinformasjon, kontaktopplysninger eller frontend-innstillinger.',
    'global_set_no_fields_description' => 'Du kan legge til felter i blueprint eller manuelt legge til variabler til selve settet.',
    'globals_blueprint_instructions' => 'Kontrollerer feltene som skal vises når variablene redigeres.',
    'globals_configure_handle_instructions' => 'Brukes for å henvise til dette globale settet på frontend. Det er vanskelig å endre senere.',
    'globals_configure_intro' => 'Et globalt sett er en gruppe med variabler som er tilgjengelig på tvers av alle frontend-sider.',
    'globals_configure_title_instructions' => 'Vi anbefaler bruk av et substantiv som beskriver settet innhold, for eksempel "Merke" eller "Bedrift".',
    'licensing_config_cached_warning' => 'Eventuelle endringer du foretar i dine .env- eller konfigurasjonsfiler, registreres ikke før du tømmer cachen. Hvis du ser uventede lisensresultater her, kan dette være grunnen. Du kan bruke <code>php artisan config:cache</code> for å generere cachen på nytt.',
    'licensing_error_invalid_domain' => 'Ugyldig domene',
    'licensing_error_invalid_edition' => 'Lisensen gjelder for :edition utgave',
    'licensing_error_no_domains' => 'Ingen domener er definert',
    'licensing_error_no_site_key' => 'Ingen lisensnøkkel for nettsted',
    'licensing_error_outside_license_range' => 'Lisensen er gyldig for versjoner :start og :end',
    'licensing_error_unknown_site' => 'Ukjent nettsted',
    'licensing_error_unlicensed' => 'Ikke lisensiert',
    'licensing_incorrect_key_format_body' => 'Det ser ut til at nettstednøkkelen din ikke er i riktig format. Vennligst sjekk nøkkelen og prøv igjen. Du kan hente nettstednøkkelen din fra kontoområdet ditt på statamic.com. Den er alfanumerisk og 16 tegn lang. Pass på å ikke bruke den gamle lisensnøkkelen som er en UUID.',
    'licensing_incorrect_key_format_heading' => 'Ugyldig nettstednøkkel',
    'licensing_production_alert' => 'Dette nettstedet bruker Statamic Pro og kommersielle tillegg. Du må kjøpe de nødvendige lisensene.',
    'licensing_production_alert_addons' => 'Dette nettstedet bruker kommersielle tillegg. Du må kjøpe de nødvendige lisensene.',
    'licensing_production_alert_renew_statamic' => 'Bruk av denne versjonen av Statamic Pro krever fornyelse av lisensen.',
    'licensing_production_alert_statamic' => 'Dette nettstedet bruker Statamic Pro. Du må kjøpe en lisens.',
    'licensing_sync_instructions' => 'Data fra statamic.com synkroniseres en gang i timen. Tving en synkronisering for å se eventuelle endringer du har gjort.',
    'licensing_trial_mode_alert' => 'Dette nettstedet bruker Statamic Pro og kommersielle tillegg. Du må kjøpe lisenser før du starter løsningen. Takk!',
    'licensing_trial_mode_alert_addons' => 'Dette nettstedet bruker kommersielle tillegg. Du må kjøpe lisenser før du starter løsningen. Takk!',
    'licensing_trial_mode_alert_statamic' => 'Dette nettstedet bruker Statamic Pro. Du må kjøpe en lisens før du starter løsningen. Takk!',
    'licensing_utility_description' => 'Vis og løs lisensopplysninger.',
    'max_depth_instructions' => 'Angi maksimalt antall nivåer siden kan nestes. La stå tomt hvis det ikke skal være noen grense.',
    'max_items_instructions' => 'Angi maksimalt antall valgbare objekter.',
    'navigation_configure_blueprint_instructions' => 'Velg blant eksisterende blueprinter, eller opprett en ny.',
    'navigation_configure_collections_instructions' => 'Aktiver lenking til oppføringer i disse samlingene.',
    'navigation_configure_handle_instructions' => 'Brukes for å henvise til denne navigasjonen på frontend. Det er vanskelig å endre senere.',
    'navigation_configure_intro' => 'Navigasjoner er flernivålister med lenker som kan brukes for å bygge navigeringsfelt, bunntekster, nettstedskart og andre former for frontend-navigering.',
    'navigation_configure_settings_intro' => 'Aktiver lenking til samlinger, angi en maksimal dybde og annen atferd.',
    'navigation_configure_title_instructions' => 'Vi anbefaler bruk av et navn som beskriver hvor det skal brukes, f.eks. "Hovednav" eller "Bunntekstnav".',
    'navigation_documentation_instructions' => 'Lær mer om bygging, konfigurering og gjengivelse av navigasjoner.',
    'navigation_link_to_entry_instructions' => 'Legger en lenke til en oppføring. Aktiver lenking til flere samlinger i konfigurasjonsområdet.',
    'navigation_link_to_url_instructions' => 'Legg til en lenke til eventuelle interne eller eksterne URL-er. Aktiver lenking til oppføringer i konfigurasjonsområdet.',
    'outpost_error_422' => 'Det oppstod en feil ved kommunikasjon med statamic.com.',
    'outpost_error_429' => 'For mange forespørsler til statamic.com.',
    'outpost_issue_try_later' => 'Det oppstod et problem ved kommunikasjon med statamic.com. Prøv igjen senere.',
    'password_protect_enter_password' => 'Oppgi passord for å låse opp',
    'password_protect_incorrect_password' => 'Feil passord.',
    'password_protect_token_invalid' => 'Ugyldig eller utløpt token.',
    'password_protect_token_missing' => 'Sikkerhetsnøkkel mangler. Du må komme til denne skjermen via den opprinnelige, beskyttede URL-en.',
    'phpinfo_utility_description' => 'Kontroller PHP-konfigurasjonsinnstillinger og installerte moduler.',
    'preference_favorites_instructions' => 'Snarveier som vil bli vist når du åpner den globale søkelinjen. Du kan alternativt besøke siden og bruke pin-ikonet øverst for å legge den til i denne listen.',
    'preference_locale_instructions' => 'Foretrukket språk i kontrollpanelet.',
    'preference_start_page_instructions' => 'Startside ved innlogging i kontrollpanelet.',
    'publish_actions_create_revision' => 'En revisjon opprettes basert på arbeidskopien. Den gjeldende revisjonen endres ikke.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Siden den gjeldende revisjonen er publisert og du har valgt en dato i fremtiden, vil revisjonen fungere som en kladd frem til valgt dato etter innsendelse.',
    'publish_actions_publish' => 'Endringer i arbeidskopien brukes, og den publiseres umiddelbart.',
    'publish_actions_schedule' => 'Endringer i arbeidskopien brukes, og den publiseres på den valgte datoen.',
    'publish_actions_unpublish' => 'Den gjeldende revisjonen vil ikke bli publisert.',
    'reset_password_notification_body' => 'Du får denne e-posten fordi vi har mottatt en forespørsel om tilbakestilling av passordet som er tilknyttet kontoen din.',
    'reset_password_notification_no_action' => 'Hvis du ikke har sendt inn en forespørsel om tilbakestilling av passord, trenger du ikke å foreta deg noe.',
    'reset_password_notification_subject' => 'Varsel om tilbakestilling av passord',
    'role_change_handle_warning' => 'Endring av håndtaket oppdaterer ikke referansene til det i brukere og grupper.',
    'role_handle_instructions' => 'Håndtak brukes for å referere til denne rollen på frontend. Kan ikke endres på en enkel måte.',
    'role_intro' => 'Roller er grupper med tilgangs- og handlingstillatelser som kan tilordnes brukere og brukergrupper.',
    'role_title_instructions' => 'Vanligvis et substantiv i entall, f.eks. Reaktør eller Administrator.',
    'search_utility_description' => 'Administrer og vis viktig informasjon om Statamics søkeindekser.',
    'session_expiry_enter_password' => 'Oppgi passordet ditt for å fortsette der du slapp',
    'session_expiry_logged_out_for_inactivity' => 'Du har blitt logget ut fordi du har vært inaktiv for lenge.',
    'session_expiry_logging_out_in_seconds' => 'Du har vært inaktiv en stund og vil bli logget ut om :seconds sekunder. Klikk for å forlenge økten.',
    'session_expiry_new_window' => 'Åpner et nytt vindu. Kom tilbake når du har logget deg inn.',
    'show_slugs_instructions' => 'Om permalenker skal vises i trevisningen eller ikke.',
    'tabs_instructions' => 'Feltene i hver inndeling grupperes i faner. Opprett nye felter, bruk eksisterende felter om igjen eller importer hele grupper med felter fra eksisterende feltsett.',
    'taxonomies_blueprints_instructions' => 'Betingelser i denne taksonomien kan bruke en hvilken som helst av disse blueprintene.',
    'taxonomies_collections_instructions' => 'Samlingene som bruker denne taksonomien.',
    'taxonomies_preview_targets_instructions' => 'URL-ene som skal kunne vises i forhåndsvisning. Les mer i [dokumentasjon](https://statamic.dev/live-preview#preview-targets).',
    'taxonomy_configure_handle_instructions' => 'Brukes for å henvise til denne taksonomien på frontend. Det er vanskelig å endre senere.',
    'taxonomy_configure_intro' => 'En taksonomi er en måte å klassifisere data på, basert på et unikt sett med egenskaper, for eksempel kategori eller farge.',
    'taxonomy_configure_title_instructions' => 'Vi anbefaler et substantiv i flertall, for eksempel "Kategorier" eller "Merker".',
    'taxonomy_next_steps_configure_description' => 'Konfigurer navn, tilknytt samlinger, definer blueprinter og mer.',
    'taxonomy_next_steps_create_term_description' => 'Opprett den første termen eller lag en håndfull plassholdertermer, det er opp til deg.',
    'taxonomy_next_steps_documentation_description' => 'Lær mer om taksonomier, hvordan de fungerer og hvordan du konfigurerer dem.',
    'try_again_in_seconds' => '{0,1}Prøv igjen nå.|Prøv igjen om :count sekunder.',
    'user_groups_handle_instructions' => 'Brukes for å henvise til denne brukergruppen på frontend. Det er vanskelig å endre senere.',
    'user_groups_intro' => 'Med brukergrupper kan du organisere brukere og tildele tillatelsesbaserte roller samlet.',
    'user_groups_role_instructions' => 'Tildel roller for å gi brukerne i denne gruppen alle aktuelle tillatelser.',
    'user_groups_title_instructions' => 'Vanligvis et substantiv i flertall, som redaktører eller fotografer.',
    'user_wizard_account_created' => 'Brukerkontoen er opprettet.',
    'user_wizard_email_instructions' => 'E-postadressen fungerer også som brukernavn og må være unik.',
    'user_wizard_intro' => 'Brukere kan tildeles roller som definerer hva de skal ha av tillatelser, tilganger og muligheter overalt i kontrollpanelet.',
    'user_wizard_invitation_body' => 'Aktiver din nye Statamic-konto på :site for å begynne å administrere dette nettstedet. Lenken nedenfor utløper etter :expiry time(r). Etter det må du be administratoren om å få tilsendt en ny.',
    'user_wizard_invitation_intro' => 'Send en velkomste-post med informasjon om kontoaktivering til den nye brukeren.',
    'user_wizard_invitation_share' => 'Kopier disse opplysningene, og del dem med <code>:email</code> via din foretrukne kanal.',
    'user_wizard_invitation_share_before' => 'Når du har opprettet en bruker, får du informasjon som du skal dele med <code>:email</code> via din foretrukne kanal.',
    'user_wizard_invitation_subject' => 'Aktiver din nye Statamic-konto på :site',
    'user_wizard_name_instructions' => 'La navnet stå tomt, så brukeren kan skrive det inn.',
    'user_wizard_roles_groups_intro' => 'Brukere kan tildeles roller som definerer hva de skal ha av tillatelser, tilganger og muligheter overalt i kontrollpanelet.',
    'user_wizard_super_admin_instructions' => 'Superadministratorer får full kontroll og tilgang til alt i kontrollpanelet. Tenk derfor nøye gjennom hvem du vil gi denne rollen til.',
];
