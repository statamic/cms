<?php

namespace Tests\Git;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\ReplacementFile;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events;
use Statamic\Facades;
use Statamic\Facades\Config;
use Statamic\Facades\Git;
use Statamic\Facades\User;
use Statamic\Git\Subscriber as GitSubscriber;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GitEventTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $defaultConfig = include __DIR__.'/../../config/git.php';

        Config::set('statamic.git', $defaultConfig);
        Config::set('statamic.git.enabled', true);

        $this->actingAs(
            $user = User::make()
                ->id('chewbacca')
                ->email('chew@bacca.com')
                ->data(['name' => 'Chewbacca'])
                ->makeSuper()
        );

        Config::set('filesystems.disks.test', [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]);

        Storage::fake('test');

        Git::shouldReceive('statuses');
        Git::shouldReceive('as')->with($user)->andReturnSelf();
    }

    #[Test]
    public function it_doesnt_commit_when_git_is_disabled()
    {
        Git::shouldReceive('as')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection saved')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted')->never();

        Config::set('statamic.git.enabled', false);

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    #[Test]
    public function it_doesnt_commit_when_automatic_is_disabled()
    {
        Git::shouldReceive('as')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection saved')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted')->never();

        Config::set('statamic.git.automatic', false);

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    #[Test]
    public function it_doesnt_commit_ignored_events()
    {
        Git::shouldReceive('as')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection saved')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted')->once();

        Config::set('statamic.git.ignored_events', [
            \Statamic\Events\CollectionSaved::class,
        ]);

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    #[Test]
    public function it_doesnt_commit_when_event_subscriber_is_disabled()
    {
        Git::shouldReceive('as')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection saved')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted')->once();

        $collection = Facades\Collection::make('pages');

        GitSubscriber::withoutListeners(function () use ($collection) {
            $collection->save();
        });

        $collection->delete();
    }

    #[Test]
    public function it_commits_when_custom_addon_events_are_registered()
    {
        Git::shouldReceive('dispatchCommit')->with('Pun saved')->once();
        Git::makePartial();

        Git::listen(PunSaved::class);

        try {
            PunSaved::dispatch(new \stdClass);
        } catch (\Exception $exception) {
            // Not worried about other errors for the purpose of this test.
        }
    }

    #[Test]
    public function it_commits_when_blueprint_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Blueprint saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Blueprint deleted')->once();

        $blueprint = Facades\Blueprint::make('post');

        $blueprint->save();
        $blueprint->delete();
    }

    #[Test]
    public function it_commits_when_fieldset_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Fieldset saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Fieldset deleted')->once();

        $fieldset = Facades\Fieldset::make('address');

        $fieldset->save();
        $fieldset->delete();
    }

    #[Test]
    public function it_commits_when_collection_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Collection saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted')->once();

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    #[Test]
    public function it_commits_when_entry_is_saved_and_deleted()
    {
        Config::set('statamic.git.ignored_events', [
            Events\CollectionSaved::class,
        ]);

        Git::shouldReceive('dispatchCommit')->with('Entry saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Entry deleted')->once();

        $collection = Facades\Collection::make('pages');
        $collection->save();

        $entry = Facades\Entry::make()
            ->collection($collection)
            ->locale(Facades\Site::default()->handle());

        $entry->save();
        $entry->delete();
    }

    #[Test]
    public function it_commits_when_tracked_revisions_are_saved_and_deleted()
    {
        Config::set('statamic.git.ignored_events', [
            Events\CollectionSaved::class,
        ]);

        Config::set('statamic.revisions.path', base_path('content/revisions'));

        Git::shouldReceive('dispatchCommit')->with('Revision saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Revision deleted')->once();

        $collection = Facades\Collection::make('pages');
        $collection->save();

        $entry = Facades\Entry::make()
            ->collection($collection)
            ->locale(Facades\Site::default()->handle());

        $entry->createRevision();
        $entry->latestRevision()->delete();
    }

    #[Test]
    public function it_commits_when_navigation_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Navigation saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Navigation deleted')->once();

        $nav = Facades\Nav::make()->handle('footer');

        $nav->save();
        $nav->delete();
    }

    #[Test]
    public function it_commits_when_a_navigation_tree_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Navigation tree saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Navigation tree deleted')->once();

        $nav = Facades\Nav::make()->handle('footer');
        $tree = $nav->makeTree('en');

        $tree->save();
        $tree->delete();
    }

    #[Test]
    public function it_commits_when_a_collection_tree_is_saved_and_deleted()
    {
        Config::set('statamic.git.ignored_events', [
            Events\CollectionSaved::class,
        ]);

        Git::shouldReceive('dispatchCommit')->with('Collection tree saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Collection tree deleted')->once();

        $collection = Facades\Collection::make('pages')->structureContents(['max_depth' => 10])->save();
        $tree = $collection->structure()->makeTree('en');

        $tree->save();
        $tree->delete();
    }

    #[Test]
    public function it_commits_when_taxonomy_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Taxonomy saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Taxonomy deleted')->once();

        $taxonomy = Facades\Taxonomy::make('topics');

        $taxonomy->save();
        $taxonomy->delete();
    }

    #[Test]
    public function it_commits_when_term_is_saved_and_deleted()
    {
        Config::set('statamic.git.ignored_events', [
            Events\TaxonomySaved::class,
        ]);

        Git::shouldReceive('dispatchCommit')->with('Term saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Term deleted')->once();

        $taxonomy = Facades\Taxonomy::make('topics');
        $taxonomy->save();

        $term = Facades\Term::make()
            ->taxonomy($taxonomy)
            ->in(Facades\Site::default()->handle())
            ->data([]);

        $term->save();
        $term->delete();
    }

    #[Test]
    public function it_commits_when_global_set_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Global Set saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Global Set deleted')->once();

        $set = Facades\GlobalSet::make('main');

        $set->save();
        $set->delete();
    }

    // todo: additional test for global variables

    #[Test]
    public function it_commits_when_form_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Form saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Form deleted')->once();

        $form = Facades\Form::make('main');

        $form->save();
        $form->delete();
    }

    #[Test]
    public function it_commits_when_form_submission_is_saved_and_deleted()
    {
        Config::set('statamic.git.ignored_events', [
            Events\FormSaved::class,
        ]);

        Git::shouldReceive('dispatchCommit')->with('Submission saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Submission deleted')->once();

        $form = Facades\Form::make('contact');

        $form->save();

        $submission = $form->makeSubmission()->data([]);

        $submission->save();
        $submission->delete();
    }

    #[Test]
    public function it_commits_when_user_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('User saved')->once();
        Git::shouldReceive('dispatchCommit')->with('User deleted')->once();

        $user = Facades\User::make();

        $user->save();
        $user->delete();
    }

    #[Test]
    public function it_commits_when_user_role_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Role saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Role deleted')->once();

        $role = Facades\Role::make()->handle('author');

        $role->save();
        $role->delete();
    }

    #[Test]
    public function it_commits_when_user_group_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('User group saved')->once();
        Git::shouldReceive('dispatchCommit')->with('User group deleted')->once();

        $group = Facades\UserGroup::make()->handle('admin');

        $group->save();
        $group->delete();
    }

    #[Test]
    public function it_commits_when_default_user_preferences_are_saved()
    {
        Git::shouldReceive('dispatchCommit')->with('Default preferences saved')->once();

        Facades\Preference::default()->set('foo', 'bar')->save();

        Facades\File::delete(resource_path('preferences.yaml'));
    }

    #[Test]
    public function it_commits_when_site_is_saved_and_deleted()
    {
        // Ensure we have one `en` site to start
        Facades\File::put(resource_path('sites.yaml'), Facades\YAML::dump([
            'en' => [
                'name' => 'English',
                'url' => 'http://localhost/',
                'locale' => 'en_US',
            ],
        ]));

        Git::shouldReceive('dispatchCommit')->with('Site saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Site deleted')->once();

        // Delete the `en` site and save a new `fr` site
        Facades\Site::setSites([
            'fr' => [
                'name' => 'French',
                'url' => 'http://localhost/',
                'locale' => 'fr_FR',
            ],
        ])->save();
    }

    #[Test]
    public function it_commits_when_asset_container_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset container saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Asset container deleted')->once();

        $container = Facades\AssetContainer::make()->handle('assets');

        $container->save();
        $container->delete();
    }

    #[Test]
    public function it_commits_when_asset_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Asset deleted')->once();

        $asset = $this->makeAsset()->data(['bar' => 'baz']);

        $asset->save();
        $asset->delete();
    }

    #[Test]
    public function it_commits_when_asset_is_uploaded()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset saved')->once();

        $this->makeAsset()->upload(
            UploadedFile::fake()->create('asset.txt')
        );
    }

    #[Test]
    public function it_commits_when_asset_is_reuploaded()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset reuploaded')->once();

        $file = Mockery::mock(ReplacementFile::class);
        $file->shouldReceive('extension')->andReturn('txt');
        $file->shouldReceive('writeTo');

        $this->makeAsset()->reupload($file);
    }

    #[Test]
    public function it_commits_when_asset_is_moved()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset saved')->once();

        $asset = tap($this->makeAsset()->data(['bar' => 'baz']))->saveQuietly();

        $asset->move('new-location');
    }

    #[Test]
    public function it_commits_when_asset_is_renamed()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset saved')->once();

        $asset = tap($this->makeAsset()->data(['bar' => 'baz']))->saveQuietly();

        $asset->rename('new-name');
    }

    #[Test]
    public function it_commits_only_once_when_asset_is_replaced()
    {
        $originalAsset = tap($this->makeAsset())->saveQuietly();

        Git::shouldReceive('dispatchCommit')->with('Asset saved')->once();

        $newAsset = $this->makeAsset()->upload(
            UploadedFile::fake()->create('asset.txt')
        );

        $newAsset->replace($originalAsset);
    }

    #[Test]
    public function it_commits_when_replaced_asset_is_deleted()
    {
        $originalAsset = tap($this->makeAsset())->saveQuietly();

        Git::shouldReceive('dispatchCommit')->with('Asset saved')->once();

        $newAsset = $this->makeAsset()->upload(
            UploadedFile::fake()->create('asset.txt')
        );

        Git::shouldReceive('dispatchCommit')->with('Asset deleted')->once();

        // Replace with `$deleteOriginal = true`
        $newAsset->replace($originalAsset, true);
    }

    #[Test]
    public function it_commits_when_asset_folder_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset folder saved')->once();
        Git::shouldReceive('dispatchCommit')->with('Asset folder deleted')->once();

        $folder = $this
            ->makeAsset()
            ->container()
            ->assetFolder('somewhere');

        $folder->save();
        $folder->delete();
    }

    #[Test]
    public function it_batches_term_references_changes_into_one_commit()
    {
        Config::set('statamic.git.ignored_events', [
            Events\TaxonomySaved::class,
            Events\CollectionSaved::class,
            Events\TermSaved::class,
        ]);

        $taxonomy = tap(Facades\Taxonomy::make('topics'))->save();
        $taxonomyBlueprint = Facades\Blueprint::make('default');

        BlueprintRepository::shouldReceive('in')->with('taxonomies/topics')->andReturn(collect([$taxonomyBlueprint]));
        BlueprintRepository::makePartial();

        $term = tap(Facades\Term::make('leia')->taxonomy($taxonomy)->inDefaultLocale()->data([]))->save();

        $collection = tap(Facades\Collection::make('pages'))->save();

        $blueprint = Facades\Blueprint::make('article')
            ->setNamespace('collections.pages')
            ->setContents([
                'fields' => [
                    [
                        'handle' => 'topic',
                        'field' => [
                            'type' => 'terms',
                            'taxonomies' => [$taxonomy->handle()],
                            'max_items' => 1,
                        ],
                    ],
                ],
            ]);

        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect([$blueprint]));

        foreach (range(1, 3) as $i) {
            Facades\Entry::make()
                ->collection($collection)
                ->blueprint($blueprint)
                ->locale(Facades\Site::default()->handle())
                ->data([
                    'title' => $i,
                    'topic' => 'leia',
                ])
                ->saveQuietly();
        }

        Git::shouldReceive('dispatchCommit')->with('Term references updated')->once(); // Ensure references updated event gets fired
        Git::shouldReceive('dispatchCommit')->with('Entry saved')->never(); // Ensure individual entry saved events do not get fired

        $term->slug('leia-updated')->save();
    }

    #[Test]
    public function it_batches_asset_references_changes_into_one_commit()
    {
        Config::set('statamic.git.ignored_events', [
            Events\CollectionSaved::class,
            Events\AssetSaved::class,
        ]);

        $originalAsset = tap($this->makeAsset('leia.jpg'))->save();
        $newAsset = tap($this->makeAsset('leia-2.jpg'))->save();

        $collection = tap(Facades\Collection::make('pages'))->save();

        $blueprint = Facades\Blueprint::make('article')
            ->setNamespace('collections.pages')
            ->setContents([
                'fields' => [
                    [
                        'handle' => 'avatar',
                        'field' => [
                            'type' => 'assets',
                            'container' => $originalAsset->container()->handle(),
                            'max_files' => 1,
                        ],
                    ],
                ],
            ]);

        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect([$blueprint]));

        foreach (range(1, 3) as $i) {
            Facades\Entry::make()
                ->collection($collection)
                ->blueprint($blueprint)
                ->locale(Facades\Site::default()->handle())
                ->data([
                    'title' => $i,
                    'avatar' => 'leia.jpg',
                ])
                ->saveQuietly();
        }

        Git::shouldReceive('dispatchCommit')->with('Asset references updated')->once(); // Ensure references updated event gets fired
        Git::shouldReceive('dispatchCommit')->with('Entry saved')->never(); // Ensure individual entry saved events do not get fired

        $newAsset->replace($originalAsset);
    }

    private function makeAsset($path = 'asset.txt')
    {
        Git::shouldReceive('dispatchCommit')->with('Asset container saved')->once();

        $container = Facades\AssetContainer::make()->handle('assets')->disk('test');
        $container->save();

        return (new Asset)
            ->container($container->handle())
            ->path($path)
            ->data(['foo' => 'bar']);
    }
}

class PunSaved extends Events\Event implements ProvidesCommitMessage
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Pun saved');
    }
}
