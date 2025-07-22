<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Utility;
use Statamic\Http\Controllers\CP\AddonEditionsController;
use Statamic\Http\Controllers\CP\AddonsController;
use Statamic\Http\Controllers\CP\API\AddonsController as AddonsApiController;
use Statamic\Http\Controllers\CP\API\TemplatesController;
use Statamic\Http\Controllers\CP\Assets\ActionController as AssetActionController;
use Statamic\Http\Controllers\CP\Assets\AssetContainerBlueprintController;
use Statamic\Http\Controllers\CP\Assets\AssetContainersController;
use Statamic\Http\Controllers\CP\Assets\AssetsController;
use Statamic\Http\Controllers\CP\Assets\BrowserController;
use Statamic\Http\Controllers\CP\Assets\FieldtypeController;
use Statamic\Http\Controllers\CP\Assets\FolderActionController;
use Statamic\Http\Controllers\CP\Assets\FoldersController;
use Statamic\Http\Controllers\CP\Assets\PdfController;
use Statamic\Http\Controllers\CP\Assets\SvgController;
use Statamic\Http\Controllers\CP\Assets\ThumbnailController;
use Statamic\Http\Controllers\CP\Auth\CsrfTokenController;
use Statamic\Http\Controllers\CP\Auth\ElevatedSessionController;
use Statamic\Http\Controllers\CP\Auth\ExtendSessionController;
use Statamic\Http\Controllers\CP\Auth\ForgotPasswordController;
use Statamic\Http\Controllers\CP\Auth\ImpersonationController;
use Statamic\Http\Controllers\CP\Auth\LoginController;
use Statamic\Http\Controllers\CP\Auth\ResetPasswordController;
use Statamic\Http\Controllers\CP\Auth\TwoFactorChallengeController;
use Statamic\Http\Controllers\CP\Auth\TwoFactorSetupController;
use Statamic\Http\Controllers\CP\Auth\UnauthorizedController;
use Statamic\Http\Controllers\CP\Collections\CollectionActionController;
use Statamic\Http\Controllers\CP\Collections\CollectionBlueprintsController;
use Statamic\Http\Controllers\CP\Collections\CollectionsController;
use Statamic\Http\Controllers\CP\Collections\CollectionTreeController;
use Statamic\Http\Controllers\CP\Collections\EditRedirectController;
use Statamic\Http\Controllers\CP\Collections\EntriesController;
use Statamic\Http\Controllers\CP\Collections\EntryActionController;
use Statamic\Http\Controllers\CP\Collections\EntryPreviewController;
use Statamic\Http\Controllers\CP\Collections\EntryRevisionsController;
use Statamic\Http\Controllers\CP\Collections\LocalizeEntryController;
use Statamic\Http\Controllers\CP\Collections\PublishedEntriesController;
use Statamic\Http\Controllers\CP\Collections\ReorderCollectionBlueprintsController;
use Statamic\Http\Controllers\CP\Collections\ReorderEntriesController;
use Statamic\Http\Controllers\CP\Collections\RestoreEntryRevisionController;
use Statamic\Http\Controllers\CP\Collections\ScaffoldCollectionController;
use Statamic\Http\Controllers\CP\CommandPaletteController;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\DashboardController;
use Statamic\Http\Controllers\CP\DuplicatesController;
use Statamic\Http\Controllers\CP\FieldActionModalController;
use Statamic\Http\Controllers\CP\Fields\BlueprintController;
use Statamic\Http\Controllers\CP\Fields\FieldsController;
use Statamic\Http\Controllers\CP\Fields\FieldsetController;
use Statamic\Http\Controllers\CP\Fields\FieldtypesController;
use Statamic\Http\Controllers\CP\Fields\MetaController;
use Statamic\Http\Controllers\CP\Fieldtypes\DictionaryFieldtypeController;
use Statamic\Http\Controllers\CP\Fieldtypes\FilesFieldtypeController;
use Statamic\Http\Controllers\CP\Fieldtypes\IconFieldtypeController;
use Statamic\Http\Controllers\CP\Fieldtypes\MarkdownFieldtypeController;
use Statamic\Http\Controllers\CP\Fieldtypes\RelationshipFieldtypeController;
use Statamic\Http\Controllers\CP\Forms\ActionController as FormActionController;
use Statamic\Http\Controllers\CP\Forms\FormBlueprintController;
use Statamic\Http\Controllers\CP\Forms\FormExportController;
use Statamic\Http\Controllers\CP\Forms\FormsController;
use Statamic\Http\Controllers\CP\Forms\FormSubmissionsController;
use Statamic\Http\Controllers\CP\Forms\SubmissionActionController;
use Statamic\Http\Controllers\CP\Globals\GlobalsBlueprintController;
use Statamic\Http\Controllers\CP\Globals\GlobalsController;
use Statamic\Http\Controllers\CP\Globals\GlobalVariablesController;
use Statamic\Http\Controllers\CP\GraphQLController;
use Statamic\Http\Controllers\CP\Navigation\NavigationBlueprintController;
use Statamic\Http\Controllers\CP\Navigation\NavigationController;
use Statamic\Http\Controllers\CP\Navigation\NavigationPagesController;
use Statamic\Http\Controllers\CP\Navigation\NavigationTreeController;
use Statamic\Http\Controllers\CP\Preferences\DefaultPreferenceController;
use Statamic\Http\Controllers\CP\Preferences\Nav\DefaultNavController;
use Statamic\Http\Controllers\CP\Preferences\Nav\NavController;
use Statamic\Http\Controllers\CP\Preferences\Nav\RoleNavController;
use Statamic\Http\Controllers\CP\Preferences\Nav\UserNavController;
use Statamic\Http\Controllers\CP\Preferences\PreferenceController;
use Statamic\Http\Controllers\CP\Preferences\RolePreferenceController;
use Statamic\Http\Controllers\CP\Preferences\UserPreferenceController;
use Statamic\Http\Controllers\CP\SelectSiteController;
use Statamic\Http\Controllers\CP\SessionTimeoutController;
use Statamic\Http\Controllers\CP\Sites\SitesController;
use Statamic\Http\Controllers\CP\SlugController;
use Statamic\Http\Controllers\CP\StartPageController;
use Statamic\Http\Controllers\CP\Taxonomies\PublishedTermsController;
use Statamic\Http\Controllers\CP\Taxonomies\ReorderTaxonomyBlueprintsController;
use Statamic\Http\Controllers\CP\Taxonomies\RestoreTermRevisionController;
use Statamic\Http\Controllers\CP\Taxonomies\TaxonomiesController;
use Statamic\Http\Controllers\CP\Taxonomies\TaxonomyBlueprintsController;
use Statamic\Http\Controllers\CP\Taxonomies\TermActionController;
use Statamic\Http\Controllers\CP\Taxonomies\TermPreviewController;
use Statamic\Http\Controllers\CP\Taxonomies\TermRevisionsController;
use Statamic\Http\Controllers\CP\Taxonomies\TermsController;
use Statamic\Http\Controllers\CP\Updater\UpdateProductController;
use Statamic\Http\Controllers\CP\Updater\UpdaterController;
use Statamic\Http\Controllers\CP\Users\AccountController;
use Statamic\Http\Controllers\CP\Users\PasswordController;
use Statamic\Http\Controllers\CP\Users\RolesController;
use Statamic\Http\Controllers\CP\Users\TwoFactorAuthenticationController;
use Statamic\Http\Controllers\CP\Users\UserActionController;
use Statamic\Http\Controllers\CP\Users\UserBlueprintController;
use Statamic\Http\Controllers\CP\Users\UserGroupBlueprintController;
use Statamic\Http\Controllers\CP\Users\UserGroupsController;
use Statamic\Http\Controllers\CP\Users\UsersController;
use Statamic\Http\Controllers\CP\Users\UserWizardController;
use Statamic\Http\Controllers\CP\Utilities\UtilitiesController;
use Statamic\Http\Controllers\User\TwoFactorRecoveryCodesController;
use Statamic\Http\Middleware\CP\RedirectIfTwoFactorSetupIncomplete;
use Statamic\Http\Middleware\CP\RequireElevatedSession;
use Statamic\Http\Middleware\RequireStatamicPro;
use Statamic\Statamic;

Route::group(['prefix' => 'auth'], function () {
    if (config('statamic.cp.auth.enabled', true)) {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);

        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.action');

        Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'index'])->name('two-factor-challenge');
        Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store']);

        Route::get('two-factor-setup', TwoFactorSetupController::class)
            ->withoutMiddleware(RedirectIfTwoFactorSetupIncomplete::class)
            ->name('two-factor-setup');
    }

    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('token', CsrfTokenController::class)->name('token');
    Route::get('extend', ExtendSessionController::class)->name('extend');

    Route::get('unauthorized', UnauthorizedController::class)->name('unauthorized');

    Route::get('stop-impersonating', [ImpersonationController::class, 'stop'])->name('impersonation.stop');
});

Route::middleware('statamic.cp.authenticated')->group(function () {
    Statamic::additionalCpRoutes();

    Route::get('/', StartPageController::class)->name('index');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('select-site/{handle}', [SelectSiteController::class, 'select']);

    Route::resource('navigation', NavigationController::class);
    Route::get('navigation/{navigation}/tree', [NavigationTreeController::class, 'index'])->name('navigation.tree.index');
    Route::patch('navigation/{navigation}/tree', [NavigationTreeController::class, 'update'])->name('navigation.tree.update');
    Route::post('navigation/{navigation}/pages', [NavigationPagesController::class, 'update'])->name('navigation.pages.update');
    Route::get('navigation/{navigation}/pages/create', [NavigationPagesController::class, 'create'])->name('navigation.pages.create');
    Route::get('navigation/{navigation}/pages/{edit}/edit', [NavigationPagesController::class, 'edit'])->name('navigation.pages.edit');

    Route::resource('collections', CollectionsController::class);
    Route::post('collections/actions', [CollectionActionController::class, 'run'])->name('collections.actions.run');
    Route::post('collections/actions/list', [CollectionActionController::class, 'bulkActions'])->name('collections.actions.bulk');
    Route::get('collections/{collection}/scaffold', [ScaffoldCollectionController::class, 'index'])->name('collections.scaffold');
    Route::post('collections/{collection}/scaffold', [ScaffoldCollectionController::class, 'create'])->name('collections.scaffold.create');

    Route::get('collections/{collection}/tree', [CollectionTreeController::class, 'index'])->name('collections.tree.index');
    Route::patch('collections/{collection}/tree', [CollectionTreeController::class, 'update'])->name('collections.tree.update');

    Route::group(['prefix' => 'collections/{collection}/entries'], function () {
        Route::get('/', [EntriesController::class, 'index'])->name('collections.entries.index');
        Route::post('actions', [EntryActionController::class, 'run'])->name('collections.entries.actions.run');
        Route::post('actions/list', [EntryActionController::class, 'bulkActions'])->name('collections.entries.actions.bulk');
        Route::get('create/{site}', [EntriesController::class, 'create'])->name('collections.entries.create');
        Route::post('create/{site}/preview', [EntryPreviewController::class, 'create'])->name('collections.entries.preview.create');
        Route::post('reorder', ReorderEntriesController::class)->name('collections.entries.reorder');
        Route::post('{site}', [EntriesController::class, 'store'])->name('collections.entries.store');

        Route::group(['prefix' => '{entry}'], function () {
            Route::get('/', [EntriesController::class, 'edit'])->name('collections.entries.edit');
            Route::post('publish', [PublishedEntriesController::class, 'store'])->name('collections.entries.published.store');
            Route::post('unpublish', [PublishedEntriesController::class, 'destroy'])->name('collections.entries.published.destroy');
            Route::post('localize', LocalizeEntryController::class)->name('collections.entries.localize');

            Route::resource('revisions', EntryRevisionsController::class, [
                'as' => 'collections.entries',
                'only' => ['index', 'store', 'show'],
            ])->names([
                'index' => 'collections.entries.revisions.index',
                'store' => 'collections.entries.revisions.store',
                'show' => 'collections.entries.revisions.show',
            ]);

            Route::post('restore-revision', RestoreEntryRevisionController::class)->name('collections.entries.restore-revision');
            Route::post('preview', [EntryPreviewController::class, 'edit'])->name('collections.entries.preview.edit');
            Route::get('preview', [EntryPreviewController::class, 'show'])->name('collections.entries.preview.popout');
            Route::patch('/', [EntriesController::class, 'update'])->name('collections.entries.update');
            Route::get('{slug}', fn ($collection, $entry, $slug) => redirect($entry->editUrl()));
        });
    });

    Route::resource('taxonomies', TaxonomiesController::class);

    Route::group(['prefix' => 'taxonomies/{taxonomy}/terms'], function () {
        Route::get('/', [TermsController::class, 'index'])->name('taxonomies.terms.index');
        Route::post('actions', [TermActionController::class, 'run'])->name('taxonomies.terms.actions.run');
        Route::post('actions/list', [TermActionController::class, 'bulkActions'])->name('taxonomies.terms.actions.bulk');
        Route::get('create/{site}', [TermsController::class, 'create'])->name('taxonomies.terms.create');
        Route::post('create/{site}/preview', [TermPreviewController::class, 'create'])->name('taxonomies.terms.preview.create');
        Route::post('{site}', [TermsController::class, 'store'])->name('taxonomies.terms.store');

        Route::group(['prefix' => '{term}/{site?}'], function () {
            Route::get('/', [TermsController::class, 'edit'])->name('taxonomies.terms.edit');
            Route::post('/', [PublishedTermsController::class, 'store'])->name('taxonomies.terms.published.store');
            Route::delete('/', [PublishedTermsController::class, 'destroy'])->name('taxonomies.terms.published.destroy');

            Route::resource('revisions', TermRevisionsController::class, [
                'as' => 'taxonomies.terms',
                'only' => ['index', 'store', 'show'],
            ]);

            Route::post('restore-revision', RestoreTermRevisionController::class)->name('taxonomies.terms.restore-revision');
            Route::post('preview', [TermPreviewController::class, 'edit'])->name('taxonomies.terms.preview.edit');
            Route::get('preview', [TermPreviewController::class, 'show'])->name('taxonomies.terms.preview.popout');
            Route::patch('/', [TermsController::class, 'update'])->name('taxonomies.terms.update');
        });
    });

    Route::get('globals', [GlobalsController::class, 'index'])->name('globals.index');
    Route::get('globals/create', [GlobalsController::class, 'create'])->name('globals.create');
    Route::post('globals', [GlobalsController::class, 'store'])->name('globals.store');
    Route::get('globals/{global_set}/edit', [GlobalsController::class, 'edit'])->name('globals.edit');
    Route::patch('globals/{global_set}', [GlobalsController::class, 'update'])->name('globals.update');
    Route::delete('globals/{global_set}', [GlobalsController::class, 'destroy'])->name('globals.destroy');

    Route::get('globals/{global_set}', [GlobalVariablesController::class, 'edit'])->name('globals.variables.edit');
    Route::patch('globals/{global_set}/variables', [GlobalVariablesController::class, 'update'])->name('globals.variables.update');

    Route::resource('asset-containers', AssetContainersController::class);
    Route::post('asset-containers/{asset_container}/folders', [FoldersController::class, 'store']);
    Route::patch('asset-containers/{asset_container}/folders/{path}', [FoldersController::class, 'update'])->where('path', '.*');
    Route::post('assets/actions', [AssetActionController::class, 'run'])->name('assets.actions.run');
    Route::post('assets/actions/list', [AssetActionController::class, 'bulkActions'])->name('assets.actions.bulk');
    Route::get('assets/browse', [BrowserController::class, 'index'])->name('assets.browse.index');
    Route::get('assets/browse/search/{asset_container}/{path?}', [BrowserController::class, 'search'])->where('path', '.*');
    Route::post('assets/browse/folders/{asset_container}/actions', [FolderActionController::class, 'run'])->name('assets.folders.actions.run');
    Route::get('assets/browse/folders/{asset_container}/{path?}', [BrowserController::class, 'folder'])->where('path', '.*');
    Route::get('assets/browse/{asset_container}/{path?}/edit', [BrowserController::class, 'edit'])->where('path', '.*')->name('assets.browse.edit');
    Route::get('assets/browse/{asset_container}/{path?}', [BrowserController::class, 'show'])->where('path', '.*')->name('assets.browse.show');
    Route::post('assets-fieldtype', [FieldtypeController::class, 'index']);
    Route::resource('assets', AssetsController::class)->parameters(['assets' => 'encoded_asset'])->except('destroy');
    Route::get('assets/{encoded_asset}/download', [AssetsController::class, 'download'])->name('assets.download');
    Route::get('thumbnails/{encoded_asset}/{size?}/{orientation?}', [ThumbnailController::class, 'show'])->name('assets.thumbnails.show');
    Route::get('svgs/{encoded_asset}', [SvgController::class, 'show'])->name('assets.svgs.show');
    Route::get('pdfs/{encoded_asset}', [PdfController::class, 'show'])->name('assets.pdfs.show');

    Route::get('sites', [SitesController::class, 'edit'])->name('sites.edit');
    Route::patch('sites', [SitesController::class, 'update'])->name('sites.update');

    Route::group(['prefix' => 'fields'], function () {
        Route::get('/', [FieldsController::class, 'index'])->name('fields.index');
        Route::post('edit', [FieldsController::class, 'edit'])->name('fields.edit');
        Route::post('update', [FieldsController::class, 'update'])->name('fields.update');
        Route::post('field-meta', [MetaController::class, 'show']);
        Route::delete('fieldsets/{fieldset}/reset', [FieldsetController::class, 'reset'])->name('fieldsets.reset');
        Route::resource('fieldsets', FieldsetController::class)->except(['show']);
        Route::get('fieldtypes', [FieldtypesController::class, 'index']);

        Route::group(['prefix' => 'blueprints', 'as' => 'blueprints.'], function () {
            Route::get('/', [BlueprintController::class, 'index'])->name('index');

            Route::group(['prefix' => 'collections/{collection}'], function () {
                Route::get('/', [CollectionBlueprintsController::class, 'index'])->name('collections.index');
                Route::get('create', [CollectionBlueprintsController::class, 'create'])->name('collections.create');
                Route::post('/', [CollectionBlueprintsController::class, 'store'])->name('collections.store');
                Route::get('{blueprint}', [CollectionBlueprintsController::class, 'show'])->name('collections.show');
                Route::get('{blueprint}/edit', [CollectionBlueprintsController::class, 'edit'])->name('collections.edit');
                Route::patch('{blueprint}', [CollectionBlueprintsController::class, 'update'])->name('collections.update');
                Route::delete('{blueprint}', [CollectionBlueprintsController::class, 'destroy'])->name('collections.destroy');
                Route::post('reorder', ReorderCollectionBlueprintsController::class)->name('collections.reorder');
            });

            Route::group(['prefix' => 'taxonomies/{taxonomy}'], function () {
                Route::get('/', [TaxonomyBlueprintsController::class, 'index'])->name('taxonomies.index');
                Route::get('create', [TaxonomyBlueprintsController::class, 'create'])->name('taxonomies.create');
                Route::post('/', [TaxonomyBlueprintsController::class, 'store'])->name('taxonomies.store');
                Route::get('{blueprint}', [TaxonomyBlueprintsController::class, 'show'])->name('taxonomies.show');
                Route::get('{blueprint}/edit', [TaxonomyBlueprintsController::class, 'edit'])->name('taxonomies.edit');
                Route::patch('{blueprint}', [TaxonomyBlueprintsController::class, 'update'])->name('taxonomies.update');
                Route::delete('{blueprint}', [TaxonomyBlueprintsController::class, 'destroy'])->name('taxonomies.destroy');
                Route::post('reorder', ReorderTaxonomyBlueprintsController::class)->name('taxonomies.reorder');
            });

            Route::get('asset-containers/{asset_container}/edit', [AssetContainerBlueprintController::class, 'edit'])->name('asset-containers.edit');
            Route::patch('asset-containers/{asset_container}', [AssetContainerBlueprintController::class, 'update'])->name('asset-containers.update');

            Route::get('forms/{form}/edit', [FormBlueprintController::class, 'edit'])->name('forms.edit');
            Route::patch('forms/{form}', [FormBlueprintController::class, 'update'])->name('forms.update');

            Route::get('globals/{global_set}/edit', [GlobalsBlueprintController::class, 'edit'])->name('globals.edit');
            Route::patch('globals/{global_set}', [GlobalsBlueprintController::class, 'update'])->name('globals.update');

            Route::get('navigation/{navigation}/edit', [NavigationBlueprintController::class, 'edit'])->name('navigation.edit');
            Route::patch('navigation/{navigation}', [NavigationBlueprintController::class, 'update'])->name('navigation.update');

            Route::get('users/edit', [UserBlueprintController::class, 'edit'])->name('users.edit');
            Route::patch('users', [UserBlueprintController::class, 'update'])->name('users.update');

            Route::get('user-groups/edit', [UserGroupBlueprintController::class, 'edit'])->name('user-groups.edit');
            Route::patch('user-groups', [UserGroupBlueprintController::class, 'update'])->name('user-groups.update');

            Route::get('{namespace}/{handle}/edit', [BlueprintController::class, 'edit'])->name('edit');
            Route::patch('{namespace}/{handle}', [BlueprintController::class, 'update'])->name('update');
            Route::delete('{namespace}/{handle}/reset', [BlueprintController::class, 'reset'])->name('reset');
        });
    });

    Route::get('updater', [UpdaterController::class, 'index'])->name('updater');
    Route::get('updater/count', [UpdaterController::class, 'count']);
    Route::get('updater/{marketplaceProductSlug}', [UpdateProductController::class, 'show'])->name('updater.product');
    Route::get('updater/{marketplaceProductSlug}/changelog', [UpdateProductController::class, 'changelog']);

    Route::group(['prefix' => 'duplicates'], function () {
        Route::get('/', [DuplicatesController::class, 'index'])->name('duplicates');
        Route::post('regenerate', [DuplicatesController::class, 'regenerate'])->name('duplicates.regenerate');
    });

    Route::get('addons', [AddonsController::class, 'index'])->name('addons.index');
    Route::post('addons/editions', AddonEditionsController::class);

    Route::post('forms/actions', [FormActionController::class, 'run'])->name('forms.actions.run');
    Route::post('forms/actions/list', [FormActionController::class, 'bulkActions'])->name('forms.actions.bulk');
    Route::post('forms/{form}/submissions/actions', [SubmissionActionController::class, 'run'])->name('forms.submissions.actions.run');
    Route::post('forms/{form}/submissions/actions/list', [SubmissionActionController::class, 'bulkActions'])->name('forms.submissions.actions.bulk');
    Route::resource('forms', FormsController::class);
    Route::resource('forms.submissions', FormSubmissionsController::class);
    Route::get('forms/{form}/export/{type}', [FormExportController::class, 'export'])->name('forms.export');

    Route::post('users/actions', [UserActionController::class, 'run'])->name('users.actions.run');
    Route::post('users/actions/list', [UserActionController::class, 'bulkActions'])->name('users.actions.bulk');
    Route::resource('users', UsersController::class)->except('destroy');
    Route::patch('users/{user}/password', [PasswordController::class, 'update'])->name('users.password.update');
    Route::withoutMiddleware(RedirectIfTwoFactorSetupIncomplete::class)->middleware(RequireElevatedSession::class)->group(function () {
        Route::get('two-factor/enable', [TwoFactorAuthenticationController::class, 'enable'])->name('users.two-factor.enable');
        Route::delete('two-factor', [TwoFactorAuthenticationController::class, 'disable'])->name('users.two-factor.disable');
        Route::post('two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirm'])->name('users.two-factor.confirm');
        Route::get('two-factor/recovery-codes', [TwoFactorRecoveryCodesController::class, 'show'])->name('users.two-factor.recovery-codes.show');
        Route::post('two-factor/recovery-codes', [TwoFactorRecoveryCodesController::class, 'store'])->name('users.two-factor.recovery-codes.generate');
        Route::get('two-factor/recovery-codes/download', [TwoFactorRecoveryCodesController::class, 'download'])->name('users.two-factor.recovery-codes.download');
    });
    Route::get('account', AccountController::class)->name('account');
    Route::resource('user-groups', UserGroupsController::class);
    Route::resource('roles', RolesController::class);

    Route::post('user-exists', UserWizardController::class)->name('user.exists');

    Route::get('command-palette', [CommandPaletteController::class, 'index'])->name('command-palette.index');
    Route::get('command-palette/search', [CommandPaletteController::class, 'search'])->name('command-palette.search');

    Route::get('utilities', [UtilitiesController::class, 'index'])->name('utilities.index');
    Utility::routes();

    if (config('statamic.graphql.enabled')) {
        Route::get('graphql', [GraphQLController::class, 'index'])->name('graphql.index');
        Route::get('graphiql', [GraphQLController::class, 'graphiql'])->name('graphql.graphiql');
    }

    Route::group(['prefix' => 'fieldtypes'], function () {
        Route::get('relationship', [RelationshipFieldtypeController::class, 'index'])->name('relationship.index');
        Route::post('relationship/data', [RelationshipFieldtypeController::class, 'data'])->name('relationship.data');
        Route::get('relationship/filters', [RelationshipFieldtypeController::class, 'filters'])->name('relationship.filters');
        Route::post('markdown', [MarkdownFieldtypeController::class, 'preview'])->name('markdown.preview');
        Route::post('files/upload', [FilesFieldtypeController::class, 'upload'])->name('files.upload');
        Route::get('dictionaries/{dictionary}', DictionaryFieldtypeController::class)->name('dictionary-fieldtype');
        Route::post('icons', IconFieldtypeController::class)->name('icon-fieldtype');
    });

    Route::group(['prefix' => 'field-action-modal'], function () {
        Route::post('resolve', [FieldActionModalController::class, 'resolve'])->name('resolve');
        Route::post('process', [FieldActionModalController::class, 'process'])->name('process');
    });

    Route::group(['prefix' => 'api', 'as' => 'api.'], function () {
        Route::resource('addons', AddonsApiController::class)->only('index');
        Route::resource('templates', TemplatesController::class)->only('index');
    });

    Route::group(['prefix' => 'preferences', 'as' => 'preferences.'], function () {
        Route::get('/', [PreferenceController::class, 'index'])->name('index');
        Route::get('edit', [UserPreferenceController::class, 'edit'])->name('user.edit');
        Route::patch('/', [UserPreferenceController::class, 'update'])->name('user.update');

        Route::middleware([RequireStatamicPro::class, 'can:manage preferences'])->group(function () {
            Route::get('roles/{role}/edit', [RolePreferenceController::class, 'edit'])->name('role.edit');
            Route::patch('roles/{role}', [RolePreferenceController::class, 'update'])->name('role.update');
            Route::get('default/edit', [DefaultPreferenceController::class, 'edit'])->name('default.edit');
            Route::patch('default', [DefaultPreferenceController::class, 'update'])->name('default.update');
        });

        Route::post('js', [PreferenceController::class, 'store'])->name('store');
        Route::delete('js/{key}', [PreferenceController::class, 'destroy'])->name('destroy');

    });

    Route::group(['prefix' => 'nav', 'as' => 'preferences.nav.'], function () {
        Route::get('/', [NavController::class, 'index'])->name('index');
        Route::get('edit', [UserNavController::class, 'edit'])->name('user.edit');
        Route::patch('/', [UserNavController::class, 'update'])->name('user.update');
        Route::delete('/', [UserNavController::class, 'destroy'])->name('user.destroy');

        Route::middleware([RequireStatamicPro::class, 'can:manage preferences'])->group(function () {
            Route::get('roles/{role}/edit', [RoleNavController::class, 'edit'])->name('role.edit');
            Route::patch('roles/{role}', [RoleNavController::class, 'update'])->name('role.update');
            Route::delete('roles/{role}', [RoleNavController::class, 'destroy'])->name('role.destroy');
            Route::get('default/edit', [DefaultNavController::class, 'edit'])->name('default.edit');
            Route::patch('default', [DefaultNavController::class, 'update'])->name('default.update');
            Route::delete('default', [DefaultNavController::class, 'destroy'])->name('default.destroy');
        });
    });

    Route::post('slug', SlugController::class);
    Route::get('session-timeout', SessionTimeoutController::class)->name('session.timeout');

    Route::get('auth/confirm-password', [ElevatedSessionController::class, 'showForm'])->name('confirm-password');
    Route::get('elevated-session', [ElevatedSessionController::class, 'status'])->name('elevated-session.status');
    Route::post('elevated-session', [ElevatedSessionController::class, 'confirm'])->name('elevated-session.confirm');
    Route::get('elevated-session/resend-code', [ElevatedSessionController::class, 'resendCode'])->name('elevated-session.resend-code')->middleware('throttle:send-elevated-session-code');

    Route::view('/playground', 'statamic::playground')->name('playground');

    Route::get('edit/{id}', EditRedirectController::class);

    Route::get('{segments}', [CpController::class, 'pageNotFound'])->where('segments', '.*')->name('404');
});
