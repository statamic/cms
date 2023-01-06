<?php

return [
    'activate_account_notification_body' => 'You are receiving this email because we received a password reset request for your account.',
    'activate_account_notification_subject' => 'Activate Your Account',
    'addon_has_more_releases_beyond_license_body' => 'You may update, but will need to upgrade or purchase a new license.',
    'addon_has_more_releases_beyond_license_heading' => 'This addon has more releases beyond your licensed limit.',
    'addon_list_loading_error' => 'Something went wrong while loading addons. Try again later.',
    'asset_container_allow_uploads_instructions' => 'When enabled will give users the ability upload files into this container.',
    'asset_container_blueprint_instructions' => 'Blueprints define additional custom fields available when editing assets.',
    'asset_container_create_folder_instructions' => 'When enabled will give users the ability to create folders in this container.',
    'asset_container_disk_instructions' => 'Filesystem disks specify where files are stored — either locally or in a remote location like Amazon S3. They can be configured in `config/filesystems.php`',
    'asset_container_handle_instructions' => 'Used to reference this container on the frontend. It\'s non-trivial to change later.',
    'asset_container_intro' => 'Media and document files live in folders on the server or other file storage services. Each of these locations is called a container.',
    'asset_container_move_instructions' => 'When enabled will allow users to move files around inside this container.',
    'asset_container_quick_download_instructions' => 'When enabled will add a quick download button in the Asset Manager.',
    'asset_container_rename_instructions' => 'When enabled will allow users to rename the files in this container.',
    'asset_container_title_instructions' => 'Usually a plural noun, like Images or Documents',
    'asset_container_source_preset_instructions' => 'When enabled, source images will be permanently processed with this image manipulation preset on upload.',
    'asset_container_warm_intelligent_instructions' => 'Generate appropriate presets on upload.',
    'asset_container_warm_presets_instructions' => 'Specify which presets to generate on upload.',
    'asset_folders_directory_instructions' => 'We recommend avoiding spaces and special characters to keep URLs clean.',
    'asset_replace_confirmation' => 'References to this asset within content will be updated to the asset you select below.',
    'asset_reupload_confirmation' => 'Are you sure you want to reupload this asset?',
    'asset_reupload_warning' => 'You may encounter browser or server-level caching issues. You may prefer to replace the asset instead.',
    'blueprints_intro' => 'Blueprints define and organize fields to create the content models for collections, forms, and other data types.',
    'blueprints_hidden_instructions' => 'Hides the blueprint from the create buttons in the CP',
    'blueprints_title_instructions' => 'Usually a singular noun, like Article or Product',
    'cache_utility_application_cache_description' => 'Laravel\'s unified cache used by Statamic, third party addons, and composer packages.',
    'cache_utility_description' => 'Manage and view important information about Statamic\'s various caching layers.',
    'cache_utility_image_cache_description' => 'The image cache stores copies of all transformed and resized images.',
    'cache_utility_stache_description' => 'The Stache is Statamic\'s content store that functions much like a database. It is generated automatically from the content files.',
    'cache_utility_static_cache_description' => 'Static pages bypass Statamic completely and are rendered directly from the server for maximum performance.',
    'choose_entry_localization_deletion_behavior' => 'Choose the action you wish to perform on the localized entries.',
    'collection_configure_date_behavior_private' => 'Private - Hidden from listings, URLs 404',
    'collection_configure_date_behavior_public' => 'Public - Always visible',
    'collection_configure_date_behavior_unlisted' => 'Unlisted - Hidden from listings, URLs visible',
    'collection_configure_dated_instructions' => 'Publish dates can be used to schedule and expire content.',
    'collection_configure_handle_instructions' => 'Used to reference this collection on the frontend. It\'s non-trivial to change later.',
    'collection_configure_intro' => 'A collection is a group of related entries that share behavior, attributes, and settings.',
    'collection_configure_layout_instructions' => 'Set this collection\'s default layout. Entries can override this setting with a `template` field named `layout`. It is unusual to change this setting.',
    'collection_configure_origin_behavior_instructions' => 'When localizing an entry, which site should be used as the origin?',
    'collection_configure_origin_behavior_option_active' => 'Use the active site of the entry being edited',
    'collection_configure_origin_behavior_option_root' => 'Use the site the entry was originally created in',
    'collection_configure_origin_behavior_option_select' => 'Let the user select the origin',
    'collection_configure_propagate_instructions' => 'Automatically propagate new entries to all configured sites.',
    'collection_configure_require_slugs_instructions' => 'Whether entries are required to have slugs.',
    'collection_configure_template_instructions' => 'Set this collection\'s default template. Entries can override this setting with a `template` field.',
    'collection_configure_title_instructions' => 'We recommend a plural noun, like "Articles" or "Products".',
    'collection_configure_title_format_instructions' => 'Set this to have the entries in this collection generate their titles automatically. Learn more in the [documentation](https://statamic.dev/collections#titles).',
    'collection_next_steps_configure_description' => 'Configure URLs and routes, define blueprints, date behaviors, ordering and other options.',
    'collection_next_steps_create_entry_description' => 'Create the first entry or stub out a handful of placeholder entries, it\'s up to you.',
    'collection_next_steps_blueprints_description' => 'Manage the blueprints and fields available for this collection.',
    'collection_next_steps_scaffold_description' => 'Quickly generate index and detail views from the name of the collection.',
    'collection_revisions_instructions' => 'Enable revisions for this Collection.',
    'collection_scaffold_instructions' => 'Choose which empty views to generate. Existing files will not be overwritten.',
    'collections_amp_instructions' => 'Enable Accelerated Mobile Pages (AMP). Automatically adds routes and URL for entries in this collection. Learn more in the [documentation](https://statamic.dev/amp)',
    'collections_blueprint_instructions' => 'Entries in this collection may use any of these blueprints.',
    'collections_default_publish_state_instructions' => 'While creating new entries in this collection the published toggle will default to **true** instead of **false** (draft).',
    'collections_future_date_behavior_instructions' => 'How future dated entries should behave.',
    'collections_links_instructions' => 'Entries in this collection may contain links (redirects) to other entries or URLs.',
    'collections_mount_instructions' => 'Choose an entry on which this collection should be mounted. Learn more in the [documentation](https://statamic.dev/collections-and-entries#mounting).',
    'collections_orderable_instructions' => 'Enable manual ordering via drag & drop.',
    'collections_past_date_behavior_instructions' => 'How past dated entries should behave.',
    'collections_preview_targets_instructions' => 'The URLs to be viewable within Live Preview. Learn more in the [documentation](https://statamic.dev/live-preview#preview-targets).',
    'collections_route_instructions' => 'The route controls entries URL pattern. Learn more in the [documentation](https://statamic.dev/collections#routing).',
    'collections_sort_direction_instructions' => 'The default sort direction.',
    'collections_taxonomies_instructions' => 'Connect entries in this collection to taxonomies. Fields will be automatically added to publish forms.',
    'email_utility_configuration_description' => 'Mail settings are configured in <code>:path</code>',
    'email_utility_description' => 'Check email configuration settings and send test emails.',
    'entry_origin_instructions' => 'The new localization will inherit values from the entry in the selected site.',
    'expect_root_instructions' => 'Consider the first page in the tree a "root" or "home" page.',
    'field_conditions_instructions' => 'When to show or hide this field.',
    'field_desynced_from_origin' => 'Desynced from origin. Click to sync and revert to the origin\'s value.',
    'field_synced_with_origin' => 'Synced with origin. Click or edit the field to desync.',
    'field_validation_advanced_instructions' => 'Add more advanced validation to this field.',
    'field_validation_required_instructions' => 'Control whether or not this field is required.',
    'fields_always_save_instructions' => 'Always save field value, regardless of how field conditions are evaluated.',
    'fields_blueprints_description' => 'Blueprints define the fields for content structures like collections, taxonomies, users, and forms.',
    'fields_default_instructions' => 'Set the default value.',
    'fields_display_instructions' => 'The field\'s label shown in the Control Panel.',
    'fields_duplicate_instructions' => 'Whether this field should be included when duplicating the item.',
    'fields_fieldsets_description' => 'Fieldsets are simple, flexible, and completely optional groupings of fields that help to organize reusable, pre-configured fields.',
    'fields_handle_instructions' => 'The field\'s template variable.',
    'fields_instructions_instructions' => 'Shown under the field\'s display label, like this very text. Markdown is supported.',
    'fields_instructions_position_instructions' => 'Show instructions above or below the field.',
    'fields_listable_instructions' => 'Control the listing column visibility.',
    'fields_visibility_instructions' => 'Control field visibility on publish forms.',
    'fieldset_import_fieldset_instructions' => 'The fieldset to be imported.',
    'fieldset_import_prefix_instructions' => 'The prefix that should be applied to each field when they are imported. eg. hero_',
    'fieldset_intro' => 'Fieldsets are an optional companion to blueprints, acting as reusable partials that can be used within blueprints.',
    'fieldset_link_fields_prefix_instructions' => 'Every field in the linked fieldset will be prefixed with this. Useful if you want to import the same fields multiple times.',
    'fieldsets_handle_instructions' => 'Used to reference this fieldset elsewhere. It\'s non-trivial to change later.',
    'fieldsets_title_instructions' => 'Usually describes what fields will be inside, like Image Block or Meta Data',
    'focal_point_instructions' => 'Setting a focal point allows dynamic photo cropping with a subject that stays in frame.',
    'focal_point_previews_are_examples' => 'Crop previews are for example only',
    'forgot_password_enter_email' => 'Enter your email address so we can send a reset password link.',
    'form_configure_blueprint_instructions' => 'Choose from existing Blueprints or create a new one.',
    'form_configure_email_from_instructions' => 'Leave blank to fall back to the site default',
    'form_configure_email_html_instructions' => 'The view for the html version of this email.',
    'form_configure_email_markdown_instructions' => 'Render the HTML version of this email using markdown.',
    'form_configure_email_attachments_instructions' => 'Attach uploaded assets to this email.',
    'form_configure_email_instructions' => 'Configure emails to be sent when new form submission are received.',
    'form_configure_email_reply_to_instructions' => 'Leave blank to fall back to sender.',
    'form_configure_email_subject_instructions' => 'Email subject line.',
    'form_configure_email_text_instructions' => 'The view for the text version of this email.',
    'form_configure_email_to_instructions' => 'Email address of the recipient.',
    'form_configure_handle_instructions' => 'Used to reference this form on the frontend. It\'s non-trivial to change later.',
    'form_configure_honeypot_instructions' => 'Field name to use as a honeypot. Honeypots are special fields used to reduce botspam.',
    'form_configure_intro' => 'Forms are used to collect information from visitors and dispatch events and notifications when there are new submissions.',
    'form_configure_store_instructions' => 'Disable to stop storing submissions. Events and email notifications will still be sent.',
    'form_configure_title_instructions' => 'Usually a call to action, like "Contact Us".',
    'getting_started_widget_blueprints' => 'Blueprints define the custom fields used to create and store content.',
    'getting_started_widget_collections' => 'Collections contain the different types of content in the site.',
    'getting_started_widget_docs' => 'Get to know Statamic by understanding its capabilities the right way.',
    'getting_started_widget_header' => 'Getting Started with Statamic',
    'getting_started_widget_intro' => 'To begin building your new Statamic site, we recommend starting with these steps.',
    'getting_started_widget_navigation' => 'Create multi-level lists of links that can be used to render navbars, footers, and so on.',
    'getting_started_widget_pro' => 'Statamic Pro adds unlimited user accounts, roles, permissions, git-integration, revisions, multi-site, and more!',
    'git_disabled' => 'Statamic Git integration is currently disabled.',
    'git_nothing_to_commit' => 'Nothing to commit, content paths clean!',
    'git_utility_description' => 'Manage Git tracked content.',
    'global_search_open_using_slash' => 'Focus global search using the <kbd>/</kbd> key',
    'global_set_config_intro' => 'Global Sets manage content available across the entire site, like company details, contact information, or front-end settings.',
    'global_set_no_fields_description' => 'You can add fields to the Blueprint, or you can manually add variables to the set itself.',
    'globals_blueprint_instructions' => 'Controls the fields to be displayed when editing the variables.',
    'globals_configure_handle_instructions' => 'Used to reference this global set on the frontend. It\'s non-trivial to change later.',
    'globals_configure_intro' => 'A global set is a group of variables available across all front-end pages.',
    'globals_configure_title_instructions' => 'We recommend a noun representing the set\'s contents. eg. "Brand" or "Company"',
    'licensing_config_cached_warning' => 'Any changes you make to your .env or config files will not be detected until you clear the cache. If you are seeing unexpected licensing results here, it may be because of this. You can use the <code>php artisan config:cache</code> command to regenerate the cache.',
    'licensing_error_invalid_domain' => 'Invalid domain',
    'licensing_error_invalid_edition' => 'License is for :edition edition',
    'licensing_error_no_domains' => 'No domains defined',
    'licensing_error_no_site_key' => 'No site license key',
    'licensing_error_outside_license_range' => 'License valid for versions :start and :end',
    'licensing_error_unknown_site' => 'Unknown site',
    'licensing_error_unlicensed' => 'Unlicensed',
    'licensing_incorrect_key_format_heading' => 'Incorrect site key format',
    'licensing_incorrect_key_format_body' => 'It appears that your site key is not in the correct format. Please check the key and try again. You can grab your site key from your account area on statamic.com. It is alphanumeric and 16 characters long. Make sure to not use the legacy license key which is a UUID.',
    'licensing_production_alert' => 'This site is using Statamic Pro and commercial addons. Please purchase appropriate licenses.',
    'licensing_production_alert_addons' => 'This site is using commercial addons. Please purchase appropriate licenses.',
    'licensing_production_alert_renew_statamic' => 'Using this version of Statamic Pro requires a license renewal.',
    'licensing_production_alert_statamic' => 'This site is using Statamic Pro. Please purchase a license.',
    'licensing_sync_instructions' => 'Data from statamic.com is synced once per hour. Force a sync to see any changes you\'ve made.',
    'licensing_trial_mode_alert' => 'This site is using Statamic Pro and commercial addons. Make sure to buy licenses before launching. Thanks!',
    'licensing_trial_mode_alert_addons' => 'This site is using commercial addons. Make sure to buy licenses before launching. Thanks!',
    'licensing_trial_mode_alert_statamic' => 'This site is using Statamic Pro. Make sure to buy a license before launching. Thanks!',
    'licensing_utility_description' => 'View and resolve licensing details.',
    'max_depth_instructions' => 'Set a maximum number of levels page may be nested. Leave blank for no limit.',
    'max_items_instructions' => 'Set a maximum number of selectable items.',
    'navigation_configure_blueprint_instructions' => 'Choose from existing Blueprints or create a new one.',
    'navigation_configure_collections_instructions' => 'Enable linking to entries in these collections.',
    'navigation_configure_handle_instructions' => 'Used to reference this navigation on the frontend. It\'s non-trivial to change later.',
    'navigation_configure_intro' => 'Navigations are multi-level lists of links that can be used to build navbars, footers, sitemaps, and other forms of frontend navigation.',
    'navigation_configure_settings_intro' => 'Enable linking to collections, set a max depth, and other behaviors.',
    'navigation_configure_title_instructions' => 'We recommend a name that matches where it will be used, like "Main Nav" or "Footer Nav".',
    'navigation_documentation_instructions' => 'Learn more about building, configuring, and rendering Navigations.',
    'navigation_link_to_entry_instructions' => 'Add a link to an entry. Enable linking to additional collections in the config area.',
    'navigation_link_to_url_instructions' => 'Add a link to any internal or external URL. Enable linking to entries in the config area.',
    'outpost_error_422' => 'Error communicating with statamic.com.',
    'outpost_error_429' => 'Too many requests to statamic.com.',
    'outpost_issue_try_later' => 'There was an issue communicating with statamic.com. Please try again later.',
    'password_protect_enter_password' => 'Enter password to unlock',
    'password_protect_incorrect_password' => 'Incorrect password.',
    'password_protect_token_invalid' => 'Invalid or expired token.',
    'password_protect_token_missing' => 'Secure token is missing. You must arrive at this screen from the original, protected URL.',
    'phpinfo_utility_description' => 'Check PHP configuration settings and installed modules.',
    'publish_actions_create_revision' => 'A revision will be created based off the working copy. The current revision will not change.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Since the current revision is published and you\'ve selected a date in the future, once you submit, the revision will act like a draft until the selected date.',
    'publish_actions_publish' => 'Changes to the working copy will applied to the entry and it will be published immediately.',
    'publish_actions_schedule' => 'Changes to the working copy will applied to the entry and it will be appear published on the selected date.',
    'publish_actions_unpublish' => 'The current revision will be unpublished.',
    'reset_password_notification_body' => 'You are receiving this email because we received a password reset request for your account.',
    'reset_password_notification_no_action' => 'If you did not request a password reset, no further action is required.',
    'reset_password_notification_subject' => 'Reset Password Notification',
    'role_change_handle_warning' => 'Changing the handle will not update references to it in users and groups.',
    'role_handle_instructions' => 'Handles are used to reference this role on the frontend. Cannot be easily changed.',
    'role_intro' => 'Roles are groups of access and action permissions that can be assigned to users and user groups.',
    'role_title_instructions' => 'Usually a singular noun, like Editor or Admin.',
    'search_utility_description' => 'Manage and view important information about Statamic\'s search indexes.',
    'session_expiry_enter_password' => 'Enter your password to continue where you left off',
    'session_expiry_logged_out_for_inactivity' => 'You have been logged out because you\'ve been inactive for a while.',
    'session_expiry_logging_out_in_seconds' => 'You have been inactive for a while and will be logged out in :seconds seconds. Click to extend your session.',
    'session_expiry_new_window' => 'Opens in a new window. Come back once you\'ve logged in.',
    'show_slugs_instructions' => 'Whether to display slugs in the tree view.',
    'tab_sections_instructions' => 'The fields in each section will be grouped together into tabs. Create new fields, reuse existing fields, or import entire groups of fields from existing fieldsets.',
    'taxonomies_blueprints_instructions' => 'Terms in this taxonomy may use any of these blueprints.',
    'taxonomies_collections_instructions' => 'The collections that use this taxonomy.',
    'taxonomies_preview_targets_instructions' => 'The URLs to be viewable within Live Preview. Learn more in the [documentation](https://statamic.dev/live-preview#preview-targets).',
    'taxonomy_configure_handle_instructions' => 'Used to reference this taxonomy on the frontend. It\'s non-trivial to change later.',
    'taxonomy_configure_intro' => 'A taxonomy is a system of classifying data around a set of unique characteristics, such as category or color.',
    'taxonomy_configure_title_instructions' => 'We recommend using a plural noun, like "Categories" or "Tags".',
    'taxonomy_next_steps_configure_description' => 'Configure names, associate collections, define blueprints, and more.',
    'taxonomy_next_steps_create_term_description' => 'Create the first term or stub out a handful of placeholder terms, it\'s up to you.',
    'taxonomy_next_steps_documentation_description' => 'Learn more about taxonomies, how they work, and how to configure them.',
    'try_again_in_seconds' => '{0,1}Try again now.|Try again in :count seconds.',
    'updates_available' => 'Updates are available!',
    'user_groups_handle_instructions' => 'Used to reference this user group on the frontend. It\'s non-trivial to change later.',
    'user_groups_intro' => 'User groups allow you to organize users and apply permission-based roles in aggregate.',
    'user_groups_role_instructions' => 'Assign roles to give users in this group all of their corresponding permissions.',
    'user_groups_title_instructions' => 'Usually a plural noun, like Editors or Photographers',
    'user_wizard_account_created' => 'The user account has been created.',
    'user_wizard_email_instructions' => 'The email address also serves as a username and must be unique.',
    'user_wizard_intro' => 'Users can be assigned to roles that customize their permissions, access, and abilities throughout the Control Panel.',
    'user_wizard_invitation_body' => 'Activate your new Statamic account on :site to begin managing this website. For your security, the link below expires after :expiry hour. After that, please contact the site administrator for a new password.',
    'user_wizard_invitation_intro' => 'Send a welcome email with account activation details to the new user.',
    'user_wizard_invitation_share' => 'Copy these credentials and share them with <code>:email</code> via your preferred method.',
    'user_wizard_invitation_share_before' => 'After creating a user, details will be provided to share with <code>:email</code> via your preferred method.',
    'user_wizard_invitation_subject' => 'Activate your new Statamic account on :site',
    'user_wizard_name_instructions' => 'Leave the name blank to let the user fill it in.',
    'user_wizard_roles_groups_intro' => 'Users can be assigned to roles that customize their permissions, access, and abilities throughout the Control Panel.',
    'user_wizard_super_admin_instructions' => 'Super admins have complete control and access to everything in the control panel. Grant this role wisely.',
];
