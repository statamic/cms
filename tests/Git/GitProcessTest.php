<?php

namespace Tests\Git;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Processes\Git;
use Statamic\Console\Processes\Process;
use Statamic\Facades\Path;
use Tests\TestCase;

class GitProcessTest extends TestCase
{
    use Concerns\PreparesTempRepos;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->createTempDirectory($this->basePath('temp'));
        $this->files->copyDirectory($this->basePath('assets'), $this->basePath('temp/assets'));
        $this->files->copyDirectory($this->basePath('content'), $this->basePath('temp/content'));
        $this->createTempRepo($this->basePath('temp/assets'));
        $this->createTempRepo($this->basePath('temp/content'));
    }

    public function tearDown(): void
    {
        $this->deleteTempDirectory($this->basePath('temp'));

        parent::tearDown();
    }

    #[Group('integration')]
    #[Test]
    public function it_can_get_git_root()
    {
        $this->assertEquals(
            Path::resolve($this->basePath('temp/content')),
            Git::create($this->basePath('temp/content/collections'))->root()
        );

        $this->assertEquals(
            Path::resolve($this->basePath('temp/content')),
            Git::create($this->basePath('temp/content/taxonomies'))->root()
        );

        $this->assertEquals(
            Path::resolve($this->basePath('temp/assets')),
            Git::create($this->basePath('temp/assets'))->root()
        );
    }

    #[Group('integration')]
    #[Test]
    public function it_can_check_if_folder_is_in_git_repo()
    {
        $this->assertTrue(Git::create($this->basePath('temp/content'))->isRepo());
        $this->assertTrue(Git::create($this->basePath('temp/content/collections'))->isRepo());
        $this->assertTrue(Git::create($this->basePath('temp/content/taxonomies'))->isRepo());
        $this->assertTrue(Git::create($this->basePath('temp/assets'))->isRepo());

        $notARepoPath = Path::resolve(base_path('../../../../..'));

        $this->assertFalse(Git::create($notARepoPath)->isRepo());
    }

    #[Group('integration')]
    #[Test]
    public function it_can_get_git_status_of_parent_repo()
    {
        $this->assertNull(Git::create($this->basePath('temp/content'))->status());
        $this->assertNull(Git::create($this->basePath('temp/assets'))->status());

        $this->files->put($this->basePath('temp/content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put($this->basePath('temp/content/collections/new.yaml'), 'title: New Collection');
        $this->files->put($this->basePath('temp/content/taxonomies/topics.yaml'), 'title: Topics Title Changed');

        $expectedContentStatus = <<<'EOT'
 M collections/pages.yaml
 M taxonomies/topics.yaml
?? collections/new.yaml
EOT;

        $this->assertEquals($expectedContentStatus, Git::create($this->basePath('temp/content'))->status());
        $this->assertNull(Git::create($this->basePath('temp/assets'))->status());
    }

    #[Group('integration')]
    #[Test]
    public function it_can_get_git_status_of_specific_sub_paths()
    {
        $this->files->put($this->basePath('temp/content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put($this->basePath('temp/content/collections/new.yaml'), 'title: New Collection');
        $this->files->put($this->basePath('temp/content/taxonomies/topics.yaml'), 'title: Topics Title Changed');

        $expectedCollectionsStatus = <<<'EOT'
 M collections/pages.yaml
?? collections/new.yaml
EOT;

        $expectedTaxonomiesStatus = <<<'EOT'
 M taxonomies/topics.yaml
EOT;

        $expectedCombinedStatus = <<<'EOT'
 M collections/pages.yaml
 M taxonomies/topics.yaml
?? collections/new.yaml
EOT;

        $this->assertEquals($expectedCollectionsStatus, Git::create($this->basePath('temp/content'))->status('collections'));
        $this->assertEquals($expectedTaxonomiesStatus, Git::create($this->basePath('temp/content'))->status('taxonomies'));
        $this->assertEquals($expectedCombinedStatus, Git::create($this->basePath('temp/content'))->status(['collections', 'taxonomies']));
    }

    #[Test]
    public function it_logs_error_output()
    {
        Log::shouldReceive('error')->once();

        $this->simulateLoggableErrorOutput('fatal: The current branch master has no upstream branch.');
    }

    #[Test]
    public function it_doesnt_log_resolving_deltas_as_error_output()
    {
        Log::shouldReceive('error')->never();

        $this->simulateLoggableErrorOutput('remote: Resolving deltas');
        $this->simulateLoggableErrorOutput('remote: Resolving deltas: 0% (0/6)\nremote: Resolving deltas: 16% (1/6)\nremote: Resolving deltas: 33% (2/6)\nremote: Resolving deltas: 50% (3/6)\nremote: Resolving deltas: 66% (4/6)\nremote: Resolving deltas: 83% (5/6)\nremote: Resolving deltas: 100% (6/6)\nremote: Resolving deltas: 100% (6/6), completed with 5 local objects.');
    }

    #[Test]
    public function it_doesnt_log_added_host_key_to_known_hosts_as_error_output()
    {
        Log::shouldReceive('error')->never();

        $this->simulateLoggableErrorOutput("Permanently added the ECDSA host key for IP address '127.0.0.1' to the list of known hosts.");
    }

    #[Test]
    public function it_doesnt_log_processed_references_as_error_output()
    {
        Log::shouldReceive('error')->never();

        $this->simulateLoggableErrorOutput('remote: Processed 1 references in total');
    }

    #[Test]
    public function it_doesnt_log_auto_packing_as_error_output()
    {
        Log::shouldReceive('error')->never();

        $this->simulateLoggableErrorOutput('Error: Auto packing the repository in background for optimum performance.');
    }

    private function showLastCommit($path)
    {
        return Process::create($path)->run('git show');
    }

    private function basePath($path = null)
    {
        return __DIR__.'/__fixtures__/'.$path;
    }

    private function simulateLoggableErrorOutput($output)
    {
        $process = new class($output) extends Git
        {
            private $simulatedOutput;

            public function __construct($output)
            {
                $this->simulatedOutput = $output;
            }

            public function getCommandLine()
            {
                return 'TestGitClass';
            }

            public function run($command, $cacheKey = null)
            {
                $this->prepareErrorOutput('err', $this->simulatedOutput);

                $this->logErrorOutput($this);
            }
        };

        $process->run('test');
    }
}
