<?php

namespace Tests\Console\Commands;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StacheRefresh;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Stache;
use Statamic\Git\Git;
use Statamic\Stache\Stores\ChildStore;
use Tests\TestCase;

class StacheRefreshTest extends TestCase
{
    private string $refFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refFile = storage_path('statamic/.stache-git-ref');

        if (file_exists($this->refFile)) {
            unlink($this->refFile);
        }
    }

    public function tearDown(): void
    {
        if (file_exists($this->refFile)) {
            unlink($this->refFile);
        }

        parent::tearDown();
    }

    private function mockGit(array $expectations): Git
    {
        $git = Mockery::mock(Git::class)->makePartial();

        foreach ($expectations as $method => $return) {
            $git->shouldReceive($method)->andReturn($return);
        }

        app()->instance(Git::class, $git);

        return $git;
    }

    #[Test]
    public function it_doesnt_add_any_exclusion_if_no_parameter()
    {
        Stache::shouldReceive('exclude')->never()
            ->shouldReceive('clear')->once()
            ->shouldReceive('warm')->once();

        $this->artisan(StacheRefresh::class);
    }

    #[Test]
    public function it_adds_exclude()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('clear')->once()->andReturn()
            ->shouldReceive('warm')->once()->andReturn();

        $this->artisan(StacheRefresh::class, ['--exclude' => 'foo']);
    }

    #[Test]
    public function it_adds_multiple_excludes()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('exclude')->once()->with('bar')->andReturn()
            ->shouldReceive('clear')->once()->andReturn()
            ->shouldReceive('warm')->once()->andReturn();

        $this->artisan(StacheRefresh::class, ['--exclude' => 'foo,bar']);
    }

    #[Test]
    public function it_fails_gracefully_when_not_in_a_git_repo()
    {
        $this->mockGit(['isRepo' => false]);

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('Not a git repository')
            ->assertExitCode(1);
    }

    #[Test]
    public function it_performs_full_refresh_and_bootstraps_ref_file_on_first_git_run()
    {
        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => null,
            'currentSha' => 'abc1234',
        ]);
        $git->shouldReceive('setStacheRef')->once()->with('abc1234');

        Stache::shouldReceive('clear')->once();
        Stache::shouldReceive('warm')->once();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('bootstrapped')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_reports_no_changes_when_diff_is_empty()
    {
        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => 'abc1234',
            'stacheDiff' => collect(),
            'currentSha' => 'abc1234',
        ]);
        $git->shouldReceive('setStacheRef')->once()->with('abc1234');

        Stache::shouldReceive('clear')->never();
        Stache::shouldReceive('warm')->never();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('No changes detected')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_falls_back_to_full_refresh_when_an_action_requires_it()
    {
        $actions = collect([
            ['type' => 'full-refresh', 'storeKey' => null, 'absolutePath' => null, 'displayPath' => 'resources/fieldsets/common.yaml'],
        ]);

        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => 'abc1234',
            'stacheDiff' => $actions,
            'currentSha' => 'def5678',
        ]);
        $git->shouldReceive('setStacheRef')->once()->with('def5678');

        Stache::shouldReceive('clear')->once();
        Stache::shouldReceive('warm')->once();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_runs_targeted_refresh_and_saves_updated_ref()
    {
        $actions = collect([
            ['type' => 'warm-store', 'storeKey' => 'entries::blog', 'absolutePath' => null, 'displayPath' => 'resources/blueprints/collections/blog/article.yaml'],
        ]);

        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => 'abc1234',
            'stacheDiff' => $actions,
            'currentSha' => 'def5678',
        ]);
        $git->shouldReceive('setStacheRef')->once()->with('def5678');

        $storeMock = Mockery::mock(ChildStore::class);
        $storeMock->shouldReceive('warm')->once();

        Stache::shouldReceive('store')->with('entries::blog')->andReturn($storeMock);
        Stache::shouldReceive('clear')->never();
        Stache::shouldReceive('warm')->never();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('selectively groomed')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_passes_include_dirty_flag_to_stache_diff()
    {
        $git = Mockery::mock(Git::class)->makePartial();
        $git->shouldReceive('isRepo')->andReturn(true);
        $git->shouldReceive('getStacheRef')->andReturn('abc1234');
        $git->shouldReceive('stacheDiff')->once()->with(true)->andReturn(collect());
        $git->shouldReceive('currentSha')->andReturn('abc1234');
        $git->shouldReceive('setStacheRef')->once();
        app()->instance(Git::class, $git);

        $this->artisan('statamic:stache:refresh', ['--git' => true, '--include-dirty' => true])
            ->expectsOutputToContain('No changes detected')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_fails_when_git_diff_throws_and_does_not_update_the_ref()
    {
        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => 'abc1234',
        ]);
        $git->shouldReceive('stacheDiff')->once()->andThrow(new ProcessException('Git command failed: fatal: bad object'));
        $git->shouldReceive('setStacheRef')->never();
        $git->shouldReceive('currentSha')->never();

        Stache::shouldReceive('clear')->never();
        Stache::shouldReceive('warm')->never();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('Unable to diff git changes')
            ->assertExitCode(1);
    }

    #[Test]
    public function it_updates_an_item_from_path_for_update_item_actions()
    {
        $path = '/var/www/site/content/collections/blog/post.md';
        $actions = collect([
            ['type' => 'update-item', 'storeKey' => 'entries::blog', 'absolutePath' => $path, 'displayPath' => 'content/collections/blog/post.md'],
        ]);

        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => 'abc1234',
            'stacheDiff' => $actions,
            'currentSha' => 'def5678',
        ]);
        $git->shouldReceive('setStacheRef')->once()->with('def5678');

        $storeMock = Mockery::mock(ChildStore::class);
        $storeMock->shouldReceive('updateItemFromPath')->once()->with($path);

        Stache::shouldReceive('store')->with('entries::blog')->andReturn($storeMock);
        Stache::shouldReceive('clear')->never();
        Stache::shouldReceive('warm')->never();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('selectively groomed')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_forgets_an_item_by_path_for_forget_item_actions()
    {
        $path = '/var/www/site/content/collections/blog/post.md';
        $actions = collect([
            ['type' => 'forget-item', 'storeKey' => 'entries::blog', 'absolutePath' => $path, 'displayPath' => 'content/collections/blog/post.md'],
        ]);

        $git = $this->mockGit([
            'isRepo' => true,
            'getStacheRef' => 'abc1234',
            'stacheDiff' => $actions,
            'currentSha' => 'def5678',
        ]);
        $git->shouldReceive('setStacheRef')->once()->with('def5678');

        $storeMock = Mockery::mock(ChildStore::class);
        $storeMock->shouldReceive('forgetItemByPath')->once()->with($path);

        Stache::shouldReceive('store')->with('entries::blog')->andReturn($storeMock);
        Stache::shouldReceive('clear')->never();
        Stache::shouldReceive('warm')->never();

        $this->artisan('statamic:stache:refresh', ['--git' => true])
            ->expectsOutputToContain('selectively groomed')
            ->assertExitCode(0);
    }
}
