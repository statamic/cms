<?php

namespace Tests\Git;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Facades;
use Statamic\Facades\Config;
use Statamic\Facades\Git;
use Statamic\Facades\User;
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
            User::make()
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
    }

    /** @test */
    public function it_doesnt_commit_when_git_is_disabled()
    {
        Git::shouldReceive('dispatchCommit')->with('Collection saved.')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted.')->never();

        Config::set('statamic.git.enabled', false);

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    /** @test */
    public function it_doesnt_commit_when_automatic_is_disabled()
    {
        Git::shouldReceive('dispatchCommit')->with('Collection saved.')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted.')->never();

        Config::set('statamic.git.automatic', false);

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    /** @test */
    public function it_doesnt_commit_ignored_events()
    {
        Git::shouldReceive('dispatchCommit')->with('Collection saved.')->never();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted.')->once();

        Config::set('statamic.git.ignored_events', [
            \Statamic\Events\Data\CollectionSaved::class,
        ]);

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    /** @test */
    public function it_commits_when_custom_addon_events_are_registered()
    {
        Git::shouldReceive('dispatchCommit')->with(null)->once(); // JokeSaved doesn't define `toSentence()`.
        Git::shouldReceive('dispatchCommit')->with('Pun saved.')->once(); // PunSaved extends our event with `toSentence()`.
        Git::makePartial();

        Git::listen(JokeSaved::class);
        Git::listen(PunSaved::class);

        try {
            JokeSaved::dispatch(new \stdClass);
            PunSaved::dispatch(new \stdClass);
        } catch (\Exception $exception) {
            // Not worried about other errors for the purpose of this test.
        }
    }

    /** @test */
    public function it_commits_when_blueprint_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Blueprint saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Blueprint deleted.')->once();

        $blueprint = Facades\Blueprint::make('post');

        $blueprint->save();
        $blueprint->delete();
    }

    /** @test */
    public function it_commits_when_fieldset_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Fieldset saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Fieldset deleted.')->once();

        $fieldset = Facades\Fieldset::make('address');

        $fieldset->save();
        $fieldset->delete();
    }

    /** @test */
    public function it_commits_when_collection_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Collection saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Collection deleted.')->once();

        $collection = Facades\Collection::make('pages');

        $collection->save();
        $collection->delete();
    }

    /** @test */
    public function it_commits_when_entry_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Collection saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Entry saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Entry deleted.')->once();

        $collection = Facades\Collection::make('pages');
        $collection->save();

        $entry = Facades\Entry::make()
            ->collection($collection)
            ->locale(Facades\Site::default()->handle());

        $entry->save();
        $entry->delete();
    }

    /** @test */
    public function it_commits_when_navigation_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Navigation saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Navigation deleted.')->once();

        $nav = Facades\Nav::make()->handle('footer');
        $nav->addTree($nav->makeTree(Facades\Site::default()->handle()));

        $nav->save();
        $nav->delete();
    }

    /** @test */
    public function it_commits_when_taxonomy_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Taxonomy saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Taxonomy deleted.')->once();

        $taxonomy = Facades\Taxonomy::make('topics');

        $taxonomy->save();
        $taxonomy->delete();
    }

    /** @test */
    public function it_commits_when_term_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Taxonomy saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Term saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Term deleted.')->once();

        $taxonomy = Facades\Taxonomy::make('topics');
        $taxonomy->save();

        $term = Facades\Term::make()
            ->taxonomy($taxonomy)
            ->in(Facades\Site::default()->handle())
            ->data([]);

        $term->save();
        $term->delete();
    }

    /** @test */
    public function it_commits_when_global_set_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Global set saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Global set deleted.')->once();

        $set = Facades\GlobalSet::make('main');
        $set->addLocalization($set->makeLocalization(Facades\Site::default()->handle()));

        $set->save();
        $set->delete();
    }

    /** @test */
    public function it_commits_when_form_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Form saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Form deleted.')->once();

        $form = Facades\Form::make('main');

        $form->save();
        $form->delete();
    }

    /** @test */
    public function it_commits_when_form_submission_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Blueprint saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Form saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Submission saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Submission deleted.')->once();

        $blueprint = Facades\Blueprint::make('post');
        $blueprint->save();

        $form = Facades\Form::make('contact')->blueprint($blueprint);

        $form->save();

        $submission = $form->createSubmission()->data([]);

        $submission->save();
        $submission->delete();
    }

    /** @test */
    public function it_commits_when_user_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('User saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('User deleted.')->once();

        $user = Facades\User::make();

        $user->save();
        $user->delete();
    }

    /** @test */
    public function it_commits_when_user_role_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Role saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Role deleted.')->once();

        $role = Facades\Role::make()->handle('author');

        $role->save();
        $role->delete();
    }

    /** @test */
    public function it_commits_when_user_group_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('User group saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('User group deleted.')->once();

        $group = Facades\UserGroup::make()->handle('admin');

        $group->save();
        $group->delete();
    }

    /** @test */
    public function it_commits_when_asset_container_is_saved_and_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset container saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Asset container deleted.')->once();

        $container = Facades\AssetContainer::make()->handle('assets');

        $container->save();
        $container->delete();
    }

    /** @test */
    public function it_commits_when_asset_is_uploaded()
    {
        // The assertion happens within `uploadAsset()`.
        $this->uploadAsset();
    }

    /** @test */
    public function it_commits_when_asset_is_saved()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset saved.')->once();

        $this->uploadAsset()->data(['bar' => 'baz'])->save();
    }

    /** @test */
    public function it_commits_when_asset_is_moved()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset moved.')->once();

        $this->uploadAsset()->move('somewhere', 'asset');
    }

    /** @test */
    public function it_commits_when_asset_is_replaced()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset replaced.')->once();

        $this->uploadAsset()->replace('new');
    }

    /** @test */
    public function it_commits_when_asset_is_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset deleted.')->once();

        $this->uploadAsset()->delete();
    }

    /** @test */
    public function it_commits_when_asset_folder_is_saved()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset folder saved.')->once();

        $this
            ->uploadAsset()
            ->container()
            ->assetFolder('somewhere')
            ->save();
    }

    /** @test */
    public function it_commits_when_asset_folder_is_deleted()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset folder saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Asset folder deleted.')->once();

        $folder = $this
            ->uploadAsset()
            ->container()
            ->assetFolder('somewhere');

        $folder->save();
        $folder->delete();
    }

    private function uploadAsset()
    {
        Git::shouldReceive('dispatchCommit')->with('Asset container saved.')->once();
        Git::shouldReceive('dispatchCommit')->with('Asset uploaded.')->once();

        $container = Facades\AssetContainer::make()->handle('assets')->disk('test');
        $container->save();

        return (new Asset)
            ->container($container->handle())
            ->path('asset.txt')
            ->data(['foo' => 'bar'])
            ->upload(UploadedFile::fake()->create('asset.txt'));
    }
}

class JokeSaved extends \Statamic\Events\Event
{
    //
}

class PunSaved extends \Statamic\Events\Data\Saved
{
    //
}
