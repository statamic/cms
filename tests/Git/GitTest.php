<?php

namespace Tests\Git;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Processes\Git as GitProcess;
use Statamic\Console\Processes\Process;
use Statamic\Facades\Config;
use Statamic\Facades\Git;
use Statamic\Facades\Path;
use Statamic\Facades\User;
use Tests\TestCase;

class GitTest extends TestCase
{
    use Concerns\PreparesTempRepos;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->createTempDirectory($this->basePath('temp'));
        $this->files->copyDirectory($this->basePath('content'), base_path('content'));
        $this->files->copyDirectory($this->basePath('assets'), $this->basePath('temp/assets'));
        $this->createTempRepo(base_path('content'));
        $this->createTempRepo($this->basePath('temp/assets'));

        $defaultConfig = include __DIR__.'/../../config/git.php';

        Config::set('statamic.git', $defaultConfig);
        Config::set('statamic.git.enabled', true);
        Config::set('statamic.git.paths', [
            'content/collections', // Relative path.
            'content/taxonomies', // Relative path.
            $this->basePath('temp/assets'), // Absolute path.
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteTempDirectory(base_path('content'));
        $this->deleteTempDirectory($this->basePath('temp'));

        parent::tearDown();
    }

    /** @test */
    public function it_wont_run_if_git_integration_is_not_enabled()
    {
        Config::set('statamic.git.enabled', false);

        $this->expectExceptionMessage('Statamic Git integration is currently disabled.');

        Git::anything();
    }

    /** @test */
    public function it_gets_tracked_statuses()
    {
        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put(base_path('content/taxonomies/tags.yaml'), 'title: Added Tags');
        $this->files->put(base_path('content/untracked.yaml'), 'title: Untracked File');
        $this->files->put($this->basePath('temp/assets/statement.txt'), 'Change statement.');

        $statuses = Git::statuses();
        $contentStatus = $statuses->get(Path::resolve(base_path('content')));
        $assetsStatus = $statuses->get($this->basePath('temp/assets'));

        $expectedContentStatus = <<<'EOT'
 M collections/pages.yaml
?? taxonomies/tags.yaml
EOT;

        $expectedAssetsStatus = <<<'EOT'
 M statement.txt
EOT;

        $this->assertEquals($expectedContentStatus, $contentStatus->status);
        $this->assertEquals($expectedAssetsStatus, $assetsStatus->status);

        $this->assertEquals(2, $contentStatus->totalCount);
        $this->assertEquals(1, $contentStatus->addedCount);
        $this->assertEquals(1, $contentStatus->modifiedCount);
        $this->assertEquals(0, $contentStatus->deletedCount);

        $this->assertEquals(1, $assetsStatus->totalCount);
        $this->assertEquals(0, $assetsStatus->addedCount);
        $this->assertEquals(1, $assetsStatus->modifiedCount);
        $this->assertEquals(0, $assetsStatus->deletedCount);
    }

    /** @test */
    public function it_returns_null_when_statuses_are_clean()
    {
        $this->assertNull(Git::statuses());
    }

    /** @test */
    public function it_gets_git_user_info()
    {
        $this->assertEquals('Spock', Git::gitUserName());
        $this->assertEquals('spock@example.com', Git::gitUserEmail());

        $this->actingAs(
            User::make()
                ->email('chew@bacca.com')
                ->data(['name' => 'Chewy'])
                ->makeSuper()
        );

        $this->assertEquals('Chewy', Git::gitUserName());
        $this->assertEquals('chew@bacca.com', Git::gitUserEmail());

        Config::set('statamic.git.use_authenticated', false);

        $this->assertEquals('Spock', Git::gitUserName());
        $this->assertEquals('spock@example.com', Git::gitUserEmail());
    }

    /** @test */
    public function it_commits_tracked_content()
    {
        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put(base_path('content/taxonomies/tags.yaml'), 'title: Added Tags');
        $this->files->put(base_path('content/untracked.yaml'), 'title: Untracked File');
        $this->files->put($this->basePath('temp/assets/statement.txt'), 'Change statement.');

        $expectedContentStatus = <<<'EOT'
 M collections/pages.yaml
?? taxonomies/tags.yaml
?? untracked.yaml
EOT;

        $expectedAssetsStatus = <<<'EOT'
 M statement.txt
EOT;

        $this->assertEquals($expectedContentStatus, GitProcess::create(Path::resolve(base_path('content')))->status());
        $this->assertEquals($expectedAssetsStatus, GitProcess::create($this->basePath('temp/assets'))->status());

        $this->assertStringContainsString('Initial commit.', $this->showLastCommit(base_path('content')));
        $this->assertStringContainsString('Initial commit.', $this->showLastCommit($this->basePath('temp/assets')));

        Git::commit();

        $this->assertStringContainsString('Content saved', $commit = $this->showLastCommit(base_path('content')));
        $this->assertStringContainsString('Spock <spock@example.com>', $commit);
        $this->assertStringContainsString('collections/pages.yaml', $commit);
        $this->assertStringContainsString('taxonomies/tags.yaml', $commit);
        $this->assertStringNotContainsString('untracked.yaml', $commit);

        $this->assertStringContainsString('Content saved', $commit = $this->showLastCommit($this->basePath('temp/assets')));
        $this->assertStringContainsString('statement.txt', $commit);
    }

    /** @test */
    public function it_can_commit_with_custom_commit_message()
    {
        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');

        Git::commit('Pages changed.');

        $this->assertStringContainsString('Pages changed.', $commit = $this->showLastCommit(base_path('content')));
        $this->assertStringContainsString('collections/pages.yaml', $commit);
    }

    /** @test */
    public function it_can_run_custom_commands()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put(base_path('content/taxonomies/tags.yaml'), 'title: Added Tags');
        $this->files->put(base_path('content/untracked.yaml'), 'title: Untracked File');
        $this->files->put($this->basePath('temp/assets/statement.txt'), 'Change statement.');

        $this->files->put($logFile = $this->basePath('temp/log.txt'), '');

        Config::set('statamic.git.commands', [
            'echo "{{ name }} committed." >> '.$logFile,
        ]);

        Git::commit();

        $expectedLog = <<<'EOT'
Spock committed.
Spock committed.

EOT;

        $this->assertEquals($expectedLog, $this->files->get($logFile));
    }

    /** @test */
    public function it_dispatches_commit_job()
    {
        $this->expectsJobs(\Statamic\Git\CommitJob::class);

        Git::dispatchCommit();
    }

    /** @test */
    public function it_doesnt_push_by_default()
    {
        Git::shouldReceive('push')->never();
        Git::makePartial();

        Git::commit();
    }

    /** @test */
    public function it_doesnt_push_when_there_was_nothing_to_commit()
    {
        Git::shouldReceive('push')->never();
        Git::makePartial();

        Config::set('statamic.git.push', true);

        Git::commit();
    }

    /** @test */
    public function it_can_push_after_a_commit()
    {
        Git::shouldReceive('push')->once();
        Git::makePartial();

        Config::set('statamic.git.push', true);

        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');

        Git::commit();
    }

    private function showLastCommit($path)
    {
        return Process::create($path)->run('git show');
    }

    private function basePath($path = null)
    {
        return __DIR__.'/__fixtures__/'.$path;
    }
}
