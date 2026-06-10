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
        $provider = new class($this->app) extends AddonServiceProvider
        {
            protected $tags = [NamespacedTestTag::class];

            protected $tagNamespace = 'acme';

            protected function autoloadFilesFromFolder($folder, $requiredClass = null)
            {
                return [];
            }

            public function callBootTags()
            {
                return $this->bootTags();
            }
        };

        $provider->callBootTags();

        $tags = $this->app['statamic.tags'];

        $this->assertSame(NamespacedTestTag::class, $tags->get('namespaced_test'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('namespaced_test_alias'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('acme::namespaced_test'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('acme::namespaced_test_alias'));
    }

    #[Test]
    public function it_does_not_register_namespaced_tags_without_a_tag_namespace()
    {
        $provider = new class($this->app) extends AddonServiceProvider
        {
            protected $tags = [NamespacedTestTag::class];

            protected function autoloadFilesFromFolder($folder, $requiredClass = null)
            {
                return [];
            }

            public function callBootTags()
            {
                return $this->bootTags();
            }
        };

        $provider->callBootTags();

        $tags = $this->app['statamic.tags'];

        $this->assertSame(NamespacedTestTag::class, $tags->get('namespaced_test'));
        $this->assertSame(NamespacedTestTag::class, $tags->get('namespaced_test_alias'));
        $this->assertNull($tags->get('acme::namespaced_test'));
        $this->assertNull($tags->get('acme::namespaced_test_alias'));
    }
}

class NamespacedTestTag extends Tags
{
    protected static $handle = 'namespaced_test';

    protected static $aliases = ['namespaced_test_alias'];

    public function index()
    {
        return 'hello';
    }
}
