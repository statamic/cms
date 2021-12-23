<?php

return [
    'activate_account_notification_body' => 'Du får det här e-postmeddelandet eftersom vi har fått en begäran om lösenordsåterställning för ditt konto.',
    'activate_account_notification_subject' => 'Aktivera ditt konto',
    'addon_has_more_releases_beyond_license_body' => 'Du kan uppdatera, men kommer att behöva uppgradera eller köpa en ny licens.',
    'addon_has_more_releases_beyond_license_heading' => 'Det här tillägget har fler versioner utöver din licensierade gräns.',
    'addon_list_loading_error' => 'Något gick fel när tillägg laddades. Försök igen senare.',
    'asset_container_allow_uploads_instructions' => 'När det är aktiverat kommer användarna att kunna ladda upp filer till den här behållaren.',
    'asset_container_blueprint_instructions' => 'Ritningar definierar ytterligare anpassade fält som är tillgängliga vid redigering av tillgångar.',
    'asset_container_create_folder_instructions' => 'När det är aktiverat kommer användarna att kunna skapa mappar i den här behållaren.',
    'asset_container_disk_instructions' => 'Filsystemsdiskar anger var filerna lagras - antingen lokalt eller på en avlägsen plats som Amazon S3. De kan konfigureras i `config/filesystems.php`',
    'asset_container_handle_instructions' => 'Används för att referera till denna behållare på frontend. Det är inte trivialt att ändra senare.',
    'asset_container_intro' => 'Media- och dokumentfiler finns i mappar på servern eller andra fillagringstjänster. Var och en av dessa platser kallas en container.',
    'asset_container_move_instructions' => 'När det är aktiverat kan användare flytta runt filer i den här behållaren.',
    'asset_container_quick_download_instructions' => 'När den är aktiverad kommer en snabbnedladdningsknapp att läggas till i Asset Manager.',
    'asset_container_rename_instructions' => 'När det är aktiverat kan användare byta namn på filerna i den här behållaren.',
    'asset_container_title_instructions' => 'Vanligtvis ett substantiv i plural, som bilder eller dokument',
    'asset_folders_directory_instructions' => 'Vi rekommenderar att du undviker mellanslag och specialtecken för att hålla webbadresserna rena.',
    'blueprints_hidden_instructions' => 'Döljer ritningen från skapa-knapparna i CP',
    'blueprints_intro' => 'Ritningar definierar och organiserar fält för att skapa innehållsmodeller för samlingar, formulär och andra datatyper.',
    'blueprints_title_instructions' => 'Vanligtvis ett singular substantiv, som Artikel eller Produkt',
    'cache_utility_application_cache_description' => 'Laravels enhetliga cache som används av Statamic, tredjepartstillägg och kompositörspaket.',
    'cache_utility_description' => 'Hantera och visa viktig information om Statamics olika cachinglager.',
    'cache_utility_image_cache_description' => 'Bildcachen lagrar kopior av alla transformerade och ändrade bilder.',
    'cache_utility_stache_description' => 'The Stache är Statamics innehållsbutik som fungerar ungefär som en databas. Den genereras automatiskt från innehållsfilerna.',
    'cache_utility_static_cache_description' => 'Statiska sidor går helt förbi Statamic och renderas direkt från servern för maximal prestanda.',
    'choose_entry_localization_deletion_behavior' => 'Välj den åtgärd du vill utföra på de lokaliserade posterna.',
    'collection_configure_date_behavior_private' => 'Privat – Dold från listor, URL:er 404',
    'collection_configure_date_behavior_public' => 'Offentlig - Alltid synlig',
    'collection_configure_date_behavior_unlisted' => 'Olistad - Dold från listor, webbadresser synliga',
    'collection_configure_dated_instructions' => 'Publiceringsdatum kan användas för att schemalägga och förfalla innehåll.',
    'collection_configure_handle_instructions' => 'Används för att referera till denna samling på frontend. Det är inte trivialt att ändra senare.',
    'collection_configure_intro' => 'En samling är en grupp relaterade poster som delar beteende, attribut och inställningar.',
    'collection_configure_layout_instructions' => 'Ställ in den här samlingens standardlayout. Poster kan åsidosätta denna inställning med ett `mall`-fält som heter `layout`. Det är ovanligt att ändra denna inställning.',
    'collection_configure_propagate_instructions' => 'Sprid automatiskt nya poster till alla konfigurerade webbplatser.',
    'collection_configure_template_instructions' => 'Ställ in den här samlingens standardmall. Poster kan åsidosätta denna inställning med ett &quot;mall&quot;-fält.',
    'collection_configure_title_instructions' => 'Vi rekommenderar ett substantiv i plural, som &quot;Artiklar&quot; eller &quot;Produkter&quot;.',
    'collection_next_steps_blueprints_description' => 'Hantera ritningarna och fälten som är tillgängliga för den här samlingen.',
    'collection_next_steps_configure_description' => 'Konfigurera webbadresser och rutter, definiera ritningar, datumbeteende, beställning och andra alternativ.',
    'collection_next_steps_create_entry_description' => 'Skapa den första posten eller släng ut en handfull platshållarposter, det är upp till dig.',
    'collection_next_steps_scaffold_description' => 'Generera snabbt index- och detaljvyer från namnet på samlingen.',
    'collection_scaffold_instructions' => 'Välj vilka tomma vyer som ska genereras. Befintliga filer kommer inte att skrivas över.',
    'collections_amp_instructions' => 'Aktivera Accelerated Mobile Pages (AMP). Lägger automatiskt till rutter och URL för poster i den här samlingen. Läs mer i [dokumentationen](https://statamic.dev/amp)',
    'collections_blueprint_instructions' => 'Inlägg i denna samling kan använda vilken som helst av dessa ritningar.',
    'collections_default_publish_state_instructions' => 'När du skapar nya poster i den här samlingen kommer den publicerade växlingen som standard till **true** istället för **false** (utkast).',
    'collections_future_date_behavior_instructions' => 'Hur framtida daterade poster ska bete sig.',
    'collections_links_instructions' => 'Inlägg i denna samling kan innehålla länkar (omdirigeringar) till andra poster eller webbadresser.',
    'collections_mount_instructions' => 'Välj en post som denna samling ska monteras på. Läs mer i [dokumentationen](https://statamic.dev/collections-and-entries#mounting).',
    'collections_orderable_instructions' => 'Aktivera manuell beställning via dra och släpp.',
    'collections_past_date_behavior_instructions' => 'Hur tidigare daterade poster ska bete sig.',
    'collections_route_instructions' => 'Rutten styr posternas URL-mönster.',
    'collections_sort_direction_instructions' => 'Standard sorteringsriktning.',
    'collections_taxonomies_instructions' => 'Koppla poster i den här samlingen till taxonomier. Fält kommer automatiskt att läggas till för att publicera formulär.',
    'email_utility_configuration_description' => 'E-postinställningar konfigureras i <code>:path</code>',
    'email_utility_description' => 'Kontrollera inställningarna för e-postkonfiguration och skicka testmeddelanden.',
    'expect_root_instructions' => 'Ansåg att den första sidan i trädet var en &quot;root&quot;- eller &quot;hemsida&quot;.',
    'field_conditions_instructions' => 'När ska det här fältet visas eller döljas.',
    'field_desynced_from_origin' => 'Avsynkroniserad från ursprung. Klicka för att synkronisera och återgå till ursprungsvärdet.',
    'field_synced_with_origin' => 'Synkas med ursprung. Klicka eller redigera fältet för att avsynkronisera.',
    'field_validation_advanced_instructions' => 'Lägg till mer avancerad validering i det här fältet.',
    'field_validation_required_instructions' => 'Kontrollera om detta fält är obligatoriskt eller inte.',
    'fields_blueprints_description' => 'Ritningar definierar fälten för innehållsstrukturer som samlingar, taxonomier, användare och formulär.',
    'fields_default_instructions' => 'Ställ in standardvärdet.',
    'fields_display_instructions' => 'Fältets etikett visas i kontrollpanelen.',
    'fields_fieldsets_description' => 'Fältuppsättningar är enkla, flexibla och helt valfria grupperingar av fält som hjälper till att organisera återanvändbara, förkonfigurerade fält.',
    'fields_handle_instructions' => 'Fältets mallvariabel.',
    'fields_instructions_instructions' => 'Visas under fältets visningsetikett, som just denna text. Markdown stöds.',
    'fields_instructions_position_instructions' => 'Där instruktionerna ska placeras i förhållande till fältet.',
    'fields_listable_instructions' => 'Kontrollera kolumnsynligheten för detta fält.',
    'fieldset_import_fieldset_instructions' => 'Fältuppsättningen som ska importeras.',
    'fieldset_import_prefix_instructions' => 'Prefixet som ska tillämpas på varje fält när de importeras. t.ex. hjälte_',
    'fieldset_intro' => 'Fältuppsättningar är ett valfritt komplement till ritningar, och fungerar som återanvändbara delar som kan användas i ritningar.',
    'fieldset_link_fields_prefix_instructions' => 'Varje fält i den länkade fältuppsättningen kommer att ha prefix. Användbart om du vill importera samma fält flera gånger.',
    'fieldsets_handle_instructions' => 'Används för att referera till denna fältuppsättning någon annanstans. Det är inte trivialt att ändra senare.',
    'fieldsets_title_instructions' => 'Beskriver vanligtvis vilka fält som kommer att finnas inuti, som bildblock eller metadata',
    'focal_point_instructions' => 'Att ställa in en brännpunkt möjliggör dynamisk beskärning av foton med ett motiv som stannar i ram.',
    'focal_point_previews_are_examples' => 'Beskärningsförhandsvisningar är endast till exempel',
    'forgot_password_enter_email' => 'Ange din e-postadress så att vi kan skicka en länk för återställning av lösenord.',
    'form_configure_blueprint_instructions' => 'Välj bland befintliga ritningar eller skapa en ny.',
    'form_configure_email_from_instructions' => 'Lämna tomt för att gå tillbaka till webbplatsens standard',
    'form_configure_email_html_instructions' => 'Vyn för html-versionen av detta e-postmeddelande.',
    'form_configure_email_instructions' => 'Konfigurera e-postmeddelanden som ska skickas när ett nytt formulär tas emot.',
    'form_configure_email_markdown_instructions' => 'Återge HTML-versionen av detta e-postmeddelande med markdown.',
    'form_configure_email_reply_to_instructions' => 'Lämna tomt för att gå tillbaka till avsändaren.',
    'form_configure_email_subject_instructions' => 'E-post ämnesrad.',
    'form_configure_email_text_instructions' => 'Vyn för textversionen av detta e-postmeddelande.',
    'form_configure_email_to_instructions' => 'E-postadress till mottagaren.',
    'form_configure_handle_instructions' => 'Används för att referera till detta formulär på frontend. Det är inte trivialt att ändra senare.',
    'form_configure_honeypot_instructions' => 'Fältnamn att använda som honungskruka. Honeypots är specialfält som används för att minska botspam.',
    'form_configure_intro' => 'Formulär används för att samla in information från besökare och skicka händelser och aviseringar när det finns nya bidrag.',
    'form_configure_store_instructions' => 'Inaktivera för att sluta lagra bidrag. Händelser och e-postmeddelanden kommer fortfarande att skickas.',
    'form_configure_title_instructions' => 'Vanligtvis en uppmaning, som &quot;Kontakta oss&quot;.',
    'getting_started_widget_blueprints' => 'Ritningar definierar de anpassade fält som används för att skapa och lagra innehåll.',
    'getting_started_widget_collections' => 'Samlingar innehåller de olika typerna av innehåll på webbplatsen.',
    'getting_started_widget_docs' => 'Lär känna Statamic genom att förstå dess kapacitet på rätt sätt.',
    'getting_started_widget_header' => 'Komma igång med Statamic 3',
    'getting_started_widget_intro' => 'För att börja bygga din nya Statamic 3-webbplats rekommenderar vi att du börjar med dessa steg.',
    'getting_started_widget_navigation' => 'Skapa listor på flera nivåer med länkar som kan användas för att rendera navigeringsfält, sidfötter och så vidare.',
    'getting_started_widget_pro' => 'Statamic Pro lägger till obegränsade användarkonton, roller, behörigheter, git-integration, revisioner, multi-site och mer!',
    'git_disabled' => 'Statamic Git-integrering är för närvarande inaktiverad.',
    'git_nothing_to_commit' => 'Inget att begå, innehållsvägar rena!',
    'git_utility_description' => 'Hantera Git-spårat innehåll.',
    'global_search_open_using_slash' => 'Fokusera global sökning med <kbd>/</kbd> -tangenten',
    'global_set_config_intro' => 'Globala uppsättningar hanterar innehåll som är tillgängligt på hela webbplatsen, som företagsinformation, kontaktinformation eller front-end-inställningar.',
    'global_set_no_fields_description' => 'Du kan lägga till fält i Blueprint, eller så kan du manuellt lägga till variabler till själva uppsättningen.',
    'globals_blueprint_instructions' => 'Styr fälten som ska visas när variablerna redigeras.',
    'globals_configure_handle_instructions' => 'Används för att referera till denna globala uppsättning på frontend. Det är inte trivialt att ändra senare.',
    'globals_configure_intro' => 'En global uppsättning är en grupp av variabler som är tillgängliga på alla front-end-sidor.',
    'globals_configure_title_instructions' => 'Vi rekommenderar ett substantiv som representerar uppsättningens innehåll. t.ex. &quot;Varumärke&quot; eller &quot;Företag&quot;',
    'licensing_config_cached_warning' => 'Eventuella ändringar du gör i dina .env- eller config-filer kommer inte att upptäckas förrän du rensar cacheminnet. Om du ser oväntade licensresultat här kan det bero på detta. Du kan använda <code>php artisan config :cache</code> att återskapa cachen.',
    'licensing_error_invalid_domain' => 'Ogiltig domän',
    'licensing_error_invalid_edition' => 'Licensen är för :edition edition',
    'licensing_error_no_domains' => 'Inga domäner definierade',
    'licensing_error_no_site_key' => 'Ingen webbplatslicensnyckel',
    'licensing_error_outside_license_range' => 'Licens giltig för versionerna :start och :end',
    'licensing_error_unknown_site' => 'Okänd webbplats',
    'licensing_error_unlicensed' => 'Olicensierad',
    'licensing_production_alert' => 'Den här webbplatsen använder Statamic Pro och kommersiella tillägg. Köp lämpliga licenser.',
    'licensing_production_alert_addons' => 'Den här webbplatsen använder kommersiella tillägg. Köp lämpliga licenser.',
    'licensing_production_alert_renew_statamic' => 'Att använda den här versionen av Statamic Pro kräver en licensförnyelse.',
    'licensing_production_alert_statamic' => 'Den här webbplatsen använder Statamic Pro. Köp en licens.',
    'licensing_sync_instructions' => 'Data från statamic.com synkroniseras en gång i timmen. Tvinga en synkronisering för att se alla ändringar du har gjort.',
    'licensing_trial_mode_alert' => 'Den här webbplatsen använder Statamic Pro och kommersiella tillägg. Se till att köpa licenser innan du startar. Tack!',
    'licensing_trial_mode_alert_addons' => 'Den här webbplatsen använder kommersiella tillägg. Se till att köpa licenser innan du startar. Tack!',
    'licensing_trial_mode_alert_statamic' => 'Den här webbplatsen använder Statamic Pro. Se till att köpa en licens innan du startar. Tack!',
    'licensing_utility_description' => 'Visa och lös licensinformation.',
    'max_depth_instructions' => 'Ange ett maximalt antal nivåer sidan kan kapslas. Lämna tomt utan gräns.',
    'max_items_instructions' => 'Ställ in ett maximalt antal valbara objekt.',
    'navigation_configure_blueprint_instructions' => 'Välj bland befintliga ritningar eller skapa en ny.',
    'navigation_configure_collections_instructions' => 'Aktivera länkning till poster i dessa samlingar.',
    'navigation_configure_handle_instructions' => 'Används för att referera till denna navigering på frontend. Det är inte trivialt att ändra senare.',
    'navigation_configure_intro' => 'Navigationer är listor på flera nivåer med länkar som kan användas för att bygga navigeringsfält, sidfötter, webbplatskartor och andra former av frontend-navigering.',
    'navigation_configure_settings_intro' => 'Aktivera länkning till samlingar, ställ in ett maximalt djup och andra beteenden.',
    'navigation_configure_title_instructions' => 'Vi rekommenderar ett namn som stämmer överens med var det kommer att användas, som &quot;Main Nav&quot; eller &quot;Footer Nav&quot;.',
    'navigation_documentation_instructions' => 'Läs mer om att bygga, konfigurera och återge navigering.',
    'navigation_link_to_entry_instructions' => 'Lägg till en länk till en post. Aktivera länkning till ytterligare samlingar i konfigurationsområdet.',
    'navigation_link_to_url_instructions' => 'Lägg till en länk till valfri intern eller extern URL. Aktivera länkning till poster i konfigurationsområdet.',
    'outpost_error_422' => 'Fel vid kommunikation med statamic.com.',
    'outpost_error_429' => 'För många förfrågningar till statamic.com.',
    'outpost_issue_try_later' => 'Det uppstod ett problem med kommunikationen med statamic.com. Vänligen försök igen senare.',
    'phpinfo_utility_description' => 'Kontrollera PHP-konfigurationsinställningar och installerade moduler.',
    'publish_actions_create_revision' => 'En revision kommer att skapas baserat på arbetskopian. Den aktuella revideringen kommer inte att ändras.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Eftersom den aktuella versionen är publicerad och du har valt ett datum i framtiden, kommer revisionen att fungera som ett utkast till det valda datumet när du har skickat in.',
    'publish_actions_publish' => 'Ändringar av arbetsexemplaret kommer att tillämpas på inlägget och det kommer att publiceras omedelbart.',
    'publish_actions_schedule' => 'Ändringar av arbetskopian kommer att tillämpas på posten och den kommer att visas publicerad på det valda datumet.',
    'publish_actions_unpublish' => 'Den aktuella versionen kommer att avpubliceras.',
    'reset_password_notification_body' => 'Du får det här e-postmeddelandet eftersom vi har fått en begäran om lösenordsåterställning för ditt konto.',
    'reset_password_notification_no_action' => 'Om du inte begärde en lösenordsåterställning krävs ingen ytterligare åtgärd.',
    'reset_password_notification_subject' => 'Återställ lösenordsmeddelande',
    'role_change_handle_warning' => 'Att ändra handtaget kommer inte att uppdatera referenser till det i användare och grupper.',
    'role_handle_instructions' => 'Handtag används för att referera till denna roll på frontend. Kan inte enkelt ändras.',
    'role_intro' => 'Roller är grupper av åtkomst- och handlingsbehörigheter som kan tilldelas användare och användargrupper.',
    'role_title_instructions' => 'Vanligtvis ett singular substantiv, som Editor eller Admin.',
    'search_utility_description' => 'Hantera och visa viktig information om Statamics sökindex.',
    'session_expiry_enter_password' => 'Ange ditt lösenord för att fortsätta där du slutade',
    'session_expiry_logged_out_for_inactivity' => 'Du har blivit utloggad eftersom du har varit inaktiv ett tag.',
    'session_expiry_logging_out_in_seconds' => 'Du har varit inaktiv ett tag och kommer att loggas ut om :seconds sekunder. Klicka för att förlänga din session.',
    'session_expiry_new_window' => 'Öppnas i ett nytt fönster. Kom tillbaka när du har loggat in.',
    'show_slugs_instructions' => 'Om sniglar ska visas i trädvyn.',
    'tab_sections_instructions' => 'Fälten i varje avsnitt kommer att grupperas i flikar. Skapa nya fält, återanvänd befintliga fält eller importera hela grupper av fält från befintliga fältuppsättningar.',
    'taxonomies_blueprints_instructions' => 'Termer i denna taxonomi kan använda vilken som helst av dessa ritningar.',
    'taxonomies_collections_instructions' => 'Samlingarna som använder denna taxonomi.',
    'taxonomy_configure_handle_instructions' => 'Används för att referera till denna taxonomi på frontend. Det är inte trivialt att ändra senare.',
    'taxonomy_configure_intro' => 'En taxonomi är ett system för att klassificera data kring en uppsättning unika egenskaper, såsom kategori eller färg.',
    'taxonomy_configure_title_instructions' => 'Vi rekommenderar att du använder ett substantiv i plural, som &quot;Kategorier&quot; eller &quot;Taggar&quot;.',
    'taxonomy_next_steps_configure_description' => 'Konfigurera namn, associera samlingar, definiera ritningar och mer.',
    'taxonomy_next_steps_create_term_description' => 'Skapa den första termen eller släng ut en handfull platshållartermer, det är upp till dig.',
    'taxonomy_next_steps_documentation_description' => 'Läs mer om taxonomier, hur de fungerar och hur du konfigurerar dem.',
    'try_again_in_seconds' => '{0,1}Försök igen nu.|Försök igen om :count sekunder.',
    'user_groups_handle_instructions' => 'Används för att referera till denna användargrupp på frontend. Det är inte trivialt att ändra senare.',
    'user_groups_intro' => 'Användargrupper låter dig organisera användare och tillämpa behörighetsbaserade roller sammantaget.',
    'user_groups_role_instructions' => 'Tilldela roller för att ge användare i den här gruppen alla deras motsvarande behörigheter.',
    'user_groups_title_instructions' => 'Vanligtvis ett substantiv i plural, som Redaktörer eller Fotografer',
    'user_wizard_account_created' => 'Användarkontot har skapats.',
    'user_wizard_email_instructions' => 'E-postadressen fungerar också som ett användarnamn och måste vara unik.',
    'user_wizard_intro' => 'Användare kan tilldelas roller som anpassar deras behörigheter, åtkomst och förmågor i hela kontrollpanelen.',
    'user_wizard_invitation_body' => 'Aktivera ditt nya Statamic-konto på :site att börja hantera denna webbplats. För din säkerhet upphör länken nedan att gälla efter :expiry hour. Efter det, kontakta webbplatsens administratör för ett nytt lösenord.',
    'user_wizard_invitation_intro' => 'Skicka ett välkomstmail med kontoaktiveringsdetaljer till den nya användaren.',
    'user_wizard_invitation_share' => 'Kopiera dessa referenser och dela dem med <code>:email</code> via din föredragna metod.',
    'user_wizard_invitation_share_before' => 'När du har skapat en användare kommer detaljer att tillhandahållas att dela med <code>:email</code> via din föredragna metod.',
    'user_wizard_invitation_subject' => 'Aktivera ditt nya Statamic-konto på :site',
    'user_wizard_name_instructions' => 'Lämna namnet tomt för att låta användaren fylla i det.',
    'user_wizard_roles_groups_intro' => 'Användare kan tilldelas roller som anpassar deras behörigheter, åtkomst och förmågor i hela kontrollpanelen.',
    'user_wizard_super_admin_instructions' => 'Superadmins har fullständig kontroll och tillgång till allt i kontrollpanelen. Ge denna roll klokt.',
];
