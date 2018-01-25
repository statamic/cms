<?php

namespace Statamic\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\AggregateServiceProvider;

class StatamicServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        ViewServiceProvider::class,
        AppServiceProvider::class,
        CollectionsServiceProvider::class,
        DataServiceProvider::class,
        FilesystemServiceProvider::class,
        ExtensionServiceProvider::class,
        EventServiceProvider::class,
        StacheServiceProvider::class,
        AuthServiceProvider::class,
        GlideServiceProvider::class,
        \Statamic\StaticCaching\ServiceProvider::class,
        CpServiceProvider::class,
        ValidationServiceProvider::class,

        // AuthServiceProvider::class,
        // BroadcastServiceProvider::class,
        // EventServiceProvider::class,
    ];

    public function register()
    {
        parent::register();

        $aliases = [
            'Addon', 'Arr', 'Asset', 'AssetContainer', 'Assets', 'Auth', 'Cache', 'Collection', 'Config', 'Content',
            'Cookie', 'Crypt', 'Data', 'Email', 'Entries', 'Entry', 'Event', 'Fieldset', 'File', 'Folder', 'Form',
            'Globals', 'GlobalSet', 'Hash', 'Helper', 'Image', 'Nav', 'OAuth', 'Page', 'PageFolder', 'Parse',
            'Path', 'Pattern', 'Permission', 'Permissions', 'Please', 'Request', 'Role', 'Roles', 'Search',
            'Stache', 'Storage', 'Str', 'Taxonomy', 'TaxonomyTerm', 'TaxonomyTerms', 'Term', 'Theme', 'URL',
            'User', 'UserGroup', 'UserGroups', 'YAML', 'Zip',
        ];

        foreach ($aliases as $alias) {
            AliasLoader::getInstance()->load('Statamic\\API\\'.$alias);
        }
    }
}
