<?php

namespace Tests\Git;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Processes\Git;
use Statamic\Console\Processes\Process;
use Statamic\Facades\Path;
use Tests\TestCase;

class GitProcessTest extends TestCase
{
    use Concerns\PreparesTempRepos;

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

    /**
     * @group integration
     * @test
     */
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

    /**
     * @group integration
     * @test
     */
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

    /**
     * @group integration
     * @test
     */
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

    /**
     * @group integration
     * @test
     */
    public function it_can_git_add_all_files()
    {
        $this->files->put($this->basePath('temp/content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put($this->basePath('temp/content/collections/new.yaml'), 'title: New Collection');
        $this->files->put($this->basePath('temp/content/taxonomies/topics.yaml'), 'title: Topics Title Changed');
        $this->files->put($this->basePath('temp/content/unrelated.yaml'), 'title: Unrelated Content');
        $this->files->put($this->basePath('temp/assets/statement.md'), 'Unrelated Content');

        Git::create($this->basePath('temp/content'))->add('--all');

        $expectedContentStatus = <<<'EOT'
A  collections/new.yaml
M  collections/pages.yaml
M  taxonomies/topics.yaml
A  unrelated.yaml
EOT;

        $this->assertEquals($expectedContentStatus, Git::create($this->basePath('temp/content'))->status());
    }

    /**
     * @group integration
     * @test
     */
    public function it_can_git_add_specific_paths()
    {
        $this->files->put($this->basePath('temp/content/collections/pages.yaml'), 'title: Pages Title Changed');
        $this->files->put($this->basePath('temp/content/collections/new.yaml'), 'title: New Collection');
        $this->files->put($this->basePath('temp/content/taxonomies/topics.yaml'), 'title: Topics Title Changed');
        $this->files->put($this->basePath('temp/content/unrelated.yaml'), 'title: Unrelated Content');

        Git::create($this->basePath('temp/content'))->add('collections');

        $expectedContentStatus = <<<'EOT'
A  collections/new.yaml
M  collections/pages.yaml
 M taxonomies/topics.yaml
?? unrelated.yaml
EOT;

        $this->assertEquals($expectedContentStatus, Git::create($this->basePath('temp/content'))->status());
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
