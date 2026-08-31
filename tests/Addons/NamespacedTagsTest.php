<?php

namespace Tests\Addons;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Tags\Tags;
use Tests\TestCase;

#[Group('addons')]
class NamespacedTagsTest extends TestCase
{
    #[Test]
    public function it_registers_namespaced_tags_when_a_tag_namespace_is_set()
    {
        $this->makeProvider('acme')->callBootTags();

        $tags = $this->app['statamic.tags'];

        $this->assertNull($tags->get('namespaced_test'));
        $this->assertNull($tags->get('namespaced_test_alias'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('acme::namespaced_test'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('acme::namespaced_test_alias'));
    }

    #[Test]
    public function it_does_not_register_namespaced_tags_without_a_tag_namespace()
    {
        $this->makeProvider(null)->callBootTags();

        $tags = $this->app['statamic.tags'];

        $this->assertSame(NamespacedTestTag::class, $tags->get('namespaced_test'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('namespaced_test_alias'));
        $this->assertNull($tags->get('acme::namespaced_test'));
        $this->assertNull($tags->get('acme::namespaced_test_alias'));
    }

    private function makeProvider(?string $tagNamespace): AddonServiceProvider
    {
        return new class($this->app, $tagNamespace) extends AddonServiceProvider
        {
            protected $tags = [NamespacedTestTag::class];

            public function __construct($app, $tagNamespace)
            {
                parent::__construct($app);

                $this->tagNamespace = $tagNamespace;
            }

            protected function autoloadFilesFromFolder($folder, $requiredClass = null): array
            {
                return [];
            }

            public function callBootTags()
            {
                return $this->bootTags();
            }
        };
    }
}

class NamespacedTestTag extends Tags
{
    protected static $handle = 'namespaced_test';

    protected static $aliases = ['namespaced_test_alias'];

    public function index(): string
    {
        return 'hello';
    }
}
