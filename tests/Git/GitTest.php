<?php

namespace Tests\Git;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
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

    private $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkippedInWindows(); // TODO: Figure out why GitTest is breaking suite in Windows.

        $this->files = app(Filesystem::class);

        $this->createTempDirectory($this->basePath('temp'));
        $this->files->copyDirectory($this->basePath('content'), base_path('content'));
        $this->files->copyDirectory($this->basePath('assets'), $this->basePath('temp/assets'));
        $this->createTempRepo(base_path('content'));
        $this->createTempRepo($this->basePath('temp/assets'));
        $this->files->copyDirectory($this->basePath('assets'), base_path('../assets-external'));

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
        $this->deleteTempDirectory(base_path('../assets-external'));
        $this->deleteTempDirectory(base_path('content/assets-linked'));

        parent::tearDown();
    }

    #[Test]
    public function it_wont_run_if_git_integration_is_not_enabled()
    {
        Config::set('statamic.git.enabled', false);

        $this->expectExceptionMessage('Statamic Git integration is currently disabled.');

        Git::anything();
    }

    #[Test]
    public function it_gets_tracked_statuses()
    {
        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put(base_path('content/taxonomies/tags.yaml'), 'title: Added Tags');
        $this->files->put(base_path('content/untracked.yaml'), 'title: Untracked File');
        $this->files->put($this->basePath('temp/assets/statement.txt'), 'Change statement.');

        $statuses = Git::statuses();
        $contentStatus = $statuses->get(Path::resolve(base_path('content')));
        $assetsStatus = $statuses->get(Path::resolve($this->basePath('temp/assets')));

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

    #[Test]
    public function it_returns_null_when_statuses_are_clean()
    {
        $this->assertNull(Git::statuses());
    }

    #[Test]
    public function it_filters_out_external_paths_that_are_not_separate_repos()
    {
        $notARepoPath = Path::resolve(base_path('../../../../..'));

        Config::set('statamic.git.paths', [
            'content/collections',
            'content/taxonomies',
            $notARepoPath,
        ]);

        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put(base_path('content/taxonomies/tags.yaml'), 'title: Added Tags');
        $this->files->put(base_path('content/untracked.yaml'), 'title: Untracked File');

        $statuses = Git::statuses();
        $contentStatus = $statuses->get(Path::resolve(base_path('content')));

        $expectedContentStatus = <<<'EOT'
 M collections/pages.yaml
?? taxonomies/tags.yaml
EOT;

        $this->assertEquals($expectedContentStatus, $contentStatus->status);

        $this->assertEquals(2, $contentStatus->totalCount);
        $this->assertEquals(1, $contentStatus->addedCount);
        $this->assertEquals(1, $contentStatus->modifiedCount);
        $this->assertEquals(0, $contentStatus->deletedCount);
    }

    #[Test]
    public function it_can_handle_configured_paths_that_are_symlinks()
    {
        $externalPath = Path::resolve(base_path('../assets-external'));
        $symlinkPath = Path::resolve(base_path('content/assets-linked'));

        $this->markTestSkippedInWindows(); // TODO: Figure out why calling `symlink()` results in permissions error in Windows
        @symlink($externalPath, $symlinkPath);

        $this->files->put($externalPath.'/statement.txt', 'Change statement.');

        Config::set('statamic.git.paths', [
            $symlinkPath,
        ]);

        $status = Git::statuses()->get(Path::resolve(base_path('content')));

        $expectedStatus = <<<'EOT'
?? assets-linked
EOT;

        $this->assertEquals($expectedStatus, $status->status);

        $this->assertEquals(1, $status->totalCount);
        $this->assertEquals(1, $status->addedCount);
        $this->assertEquals(0, $status->modifiedCount);
        $this->assertEquals(0, $status->deletedCount);
    }

    #[Test]
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

        $han = User::make()
            ->email('han@solo.com')
            ->data(['name' => 'Han Solo'])
            ->makeSuper();

        $this->assertEquals('Han Solo', Git::as($han)->gitUserName());
        $this->assertEquals('han@solo.com', Git::as($han)->gitUserEmail());
        $this->assertEquals('Chewy', Git::gitUserName());
        $this->assertEquals('chew@bacca.com', Git::gitUserEmail());

        Config::set('statamic.git.use_authenticated', false);

        $this->assertEquals('Spock', Git::gitUserName());
        $this->assertEquals('spock@example.com', Git::gitUserEmail());
    }

    #[Test]
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

    #[Test]
    public function it_shell_escapes_git_user_name_and_email()
    {
        Config::set('statamic.git.user.name', 'Jimmy"; echo "deleting all your files now"; #');
        Config::set('statamic.git.user.email', 'jimmy@haxor.org"; echo "deleting all your files now"; #');

        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');

        $expectedContentStatus = <<<'EOT'
 M collections/pages.yaml
EOT;

        $this->assertEquals($expectedContentStatus, GitProcess::create(Path::resolve(base_path('content')))->status());

        $this->assertStringContainsString('Initial commit.', $this->showLastCommit(base_path('content')));

        Git::commit('Message"; echo "deleting all your files now"; #');

        $expectedUser = 'Jimmy\; echo deleting all your files now\; \# <jimmy@haxor.org\; echo deleting all your files now\; \#>';
        $expectedMessage = 'Message\; echo deleting all your files now\; \#';

        if (static::isRunningWindows()) {
            $expectedUser = str_replace('\\', '^', $expectedUser);
            $expectedMessage = str_replace('\\', '^', $expectedMessage);
        }

        $lastCommit = $this->showLastCommit(base_path('content'));

        $this->assertStringContainsString($expectedUser, $lastCommit);
        $this->assertStringContainsString($expectedMessage, $lastCommit);
    }

    #[Test]
    public function it_commits_with_spaces_in_paths()
    {
        $this->files->put(base_path('content/collections/file with spaces.yaml'), 'title: File with spaces in path!');
        $this->files->makeDirectory(base_path('content/collections/folder with spaces'));
        $this->files->put(base_path('content/collections/folder with spaces/file.yaml'), 'title: Folder with spaces in path!');

        $expectedContentStatus = <<<'EOT'
?? "collections/file with spaces.yaml"
?? "collections/folder with spaces/"
EOT;

        $this->assertEquals($expectedContentStatus, GitProcess::create(Path::resolve(base_path('content')))->status());

        $this->assertStringContainsString('Initial commit.', $this->showLastCommit(base_path('content')));

        Git::commit();

        $this->assertStringContainsString('Content saved', $commit = $this->showLastCommit(base_path('content')));
        $this->assertStringContainsString('Spock <spock@example.com>', $commit);
        $this->assertStringContainsString('collections/file with spaces.yaml', $commit);
        $this->assertStringContainsString('title: File with spaces in path!', $commit);
        $this->assertStringContainsString('collections/folder with spaces/file.yaml', $commit);
        $this->assertStringContainsString('title: Folder with spaces in path!', $commit);
    }

    #[Test]
    public function it_commits_with_spaces_in_explicitly_configured_paths()
    {
        Config::set('statamic.git.paths', [
            'content/path with spaces',
        ]);

        $this->files->makeDirectory(base_path('content/path with spaces'));
        $this->files->put(base_path('content/path with spaces/file.yaml'), 'title: File with spaces in path!');
        $this->files->put(base_path('content/path with spaces/nested file with spaces.yaml'), 'title: Nested file with spaces in path!');
        $this->files->makeDirectory(base_path('content/path with spaces/nested folder with spaces'));
        $this->files->put(base_path('content/path with spaces/nested folder with spaces/file.yaml'), 'title: Nested folder with spaces in path!');

        $expectedStatus = <<<'EOT'
?? "path with spaces/"
EOT;

        $this->assertEquals($expectedStatus, GitProcess::create(Path::resolve(base_path('content/path with spaces')))->status());

        $this->assertStringContainsString('Initial commit.', $this->showLastCommit(base_path('content/path with spaces')));

        Git::commit();

        $this->assertStringContainsString('Content saved', $commit = $this->showLastCommit(base_path('content/path with spaces')));
        $this->assertStringContainsString('Spock <spock@example.com>', $commit);
        $this->assertStringContainsString('path with spaces/file.yaml', $commit);
        $this->assertStringContainsString('title: File with spaces in path!', $commit);
        $this->assertStringContainsString('path with spaces/nested file with spaces.yaml', $commit);
        $this->assertStringContainsString('title: Nested file with spaces in path!', $commit);
        $this->assertStringContainsString('path with spaces/nested folder with spaces/file.yaml', $commit);
        $this->assertStringContainsString('title: Nested folder with spaces in path!', $commit);
    }

    #[Test]
    public function it_can_commit_with_custom_commit_message()
    {
        $this->files->put(base_path('content/collections/pages.yaml'), 'title: Pages Title Changed');

        Git::commit('Pages changed.');

        $this->assertStringContainsString('Pages changed.', $commit = $this->showLastCommit(base_path('content')));
        $this->assertStringContainsString('collections/pages.yaml', $commit);
    }

    #[Test]
    public function it_can_run_custom_commands()
    {
        $this->markTestSkippedInWindows();

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

    #[Test]
    public function it_can_run_custom_commands_with_custom_git_binary()
    {
        $this->markTestSkippedInWindows();

        $this->files->put($logFile = $this->basePath('temp/log.txt'), '');

        Config::set('statamic.git.binary', 'the custom git binary');

        Config::set('statamic.git.commands', [
            'echo "{{ name }} committed using {{ git }}." >> '.$logFile,
        ]);

        Git::partialMock()
            ->shouldReceive('groupTrackedContentPathsByRepo')
            ->andReturn(collect([base_path('content') => collect(['foo'])]));

        Git::commit();

        $expectedLog = <<<'EOT'
Spock committed using the custom git binary.

EOT;

        $this->assertEquals($expectedLog, $this->files->get($logFile));
    }

    #[Test]
    public function it_dispatches_commit_job()
    {
        Queue::fake();

        Git::dispatchCommit();

        Queue::assertPushed(\Statamic\Git\CommitJob::class, 1);
    }

    #[Test]
    public function it_doesnt_push_by_default()
    {
        Git::shouldReceive('push')->never();
        Git::makePartial();

        Git::commit();
    }

    #[Test]
    public function it_doesnt_push_when_there_was_nothing_to_commit()
    {
        Git::shouldReceive('push')->never();
        Git::makePartial();

        Config::set('statamic.git.push', true);

        Git::commit();
    }

    #[Test]
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
