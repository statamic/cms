<?php

return [

    'super' => 'Super User',
    'super_desc' => 'Super admins have complete control and access to everything in the control panel. Grant this role wisely.',

    'group_cp' => 'Control Panel',
    'access_cp' => 'Access the Control Panel',
    'access_cp_desc' => 'Allows access into control panel, but doesn\'t guarantee anything can be done once inside.',
    'configure_fields' => 'Configure Fields',
    'configure_fields_desc' => 'Ability to edit blueprints, fieldsets, and their fields.',
    'configure_addons' => 'Configure Addons',
    'configure_addons_desc' => 'Ability to access the addon area to install and uninstall addons.',

    'group_collections' => 'Collections',
    'configure_collections' => 'Configure Collections',
    'configure_collections_desc' => 'Grants access to all collection related permissions',
    'view_{collection}_entries' => 'View :collection entries',
    'edit_{collection}_entries' => 'Edit entries',
    'create_{collection}_entries' => 'Create new entries',
    'delete_{collection}_entries' => 'Delete entries',
    'publish_{collection}_entries' => 'Manage publish state',
    'publish_{collection}_entries_desc' => 'Ability to change from draft to published and vice versa',
    'reorder_{collection}_entries' => 'Reorder entries',
    'reorder_{collection}_entries_desc' => 'Enables drag and drop reordering',
    'edit_other_authors_{collection}_entries' => "Edit other authors' entries",
    'publish_other_authors_{collection}_entries' => "Manage publish state of other authors' entries",
    'delete_other_authors_{collection}_entries' => "Delete other authors' entries",

    'group_taxonomies' => 'Taxonomies',
    'configure_taxonomies' => 'Configure Taxonomies',
    'configure_taxonomies_desc' => 'Grants access to all taxonomy related permissions',
    'view_{taxonomy}_terms' => 'View :taxonomy terms',
    'edit_{taxonomy}_terms' => 'Edit terms',
    'create_{taxonomy}_terms' => 'Create new terms',
    'delete_{taxonomy}_terms' => 'Delete terms',
    'publish_{taxonomy}_terms' => 'Manage publish state',
    'reorder_{taxonomy}_terms' => 'Reorder terms',

    'group_navigation' => 'Navigation',
    'configure_navs' => 'Configure Navigation',
    'configure_navs_desc' => 'Grants access to all navigation related permissions',
    'view_{nav}_nav' => 'View :nav navigation',
    'edit_{nav}_nav' => 'Edit navigation',

    'group_globals' => 'Globals',
    'configure_globals' => 'Configure Globals',
    'configure_globals_desc' => 'Grants access to all global related permissions',
    'edit_{global}_globals' => 'Edit :global globals',

    'group_assets' => 'Assets',
    'configure_asset_containers' => 'Configure Asset Containers',
    'configure_asset_containers_desc' => 'Grants access to all asset related permissions',
    'view_{container}_assets' => 'View :container assets',
    'upload_{container}_assets' => 'Upload new assets',
    'edit_{container}_assets' => 'Edit assets',
    'move_{container}_assets' => 'Move assets',
    'rename_{container}_assets' => 'Rename assets',
    'delete_{container}_assets' => 'Delete assets',

    'group_forms' => 'Forms',
    'configure_forms' => 'Configure forms',
    'configure_forms_desc' => 'Grants access to all form related permissions',
    'view_{form}_form_submissions' => 'View :form submissions',
    'delete_{form}_form_submissions' => 'Delete :form submissions',

    'group_users' => 'Users',
    'view_users' => 'View users',
    'edit_users' => 'Edit users',
    'create_users' => 'Create users',
    'delete_users' => 'Delete users',
    'change_passwords' => 'Change passwords',
    'edit_user_groups' => 'Edit groups',
    'edit_roles' => 'Edit roles',

    'group_updates' => 'Updates',
    'view_updates' => 'View updates',
    'perform_updates' => 'Perform updates',

    'group_utilities' => 'Utilities',
    'access_utility' => ':title',
    'access_utility_desc' => 'Grants access to the :title utility',

    'group_misc' => 'Miscellaneous',
    'resolve_duplicate_ids' => 'Resolve Duplicate IDs',
    'resolve_duplicate_ids_desc' => 'Grants ability to see and resolve duplicate IDs.',

];
