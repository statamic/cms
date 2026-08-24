<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Data\ContainsData;
use Statamic\Data\HasOrigin;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Tests\TestCase;

class HasOriginTest extends TestCase
{
    #[Test]
    public function it_does_not_hang_when_resolving_values_with_a_circular_origin()
    {
        $a = new OriginStub('a', ['title' => 'A']);
        $b = new OriginStub('b', ['title' => 'B'], $a);
        $a->setOrigin($b);

        $this->assertTrue($a->hasOriginCycle());
        $this->assertTrue($b->hasOriginCycle());
        $this->assertEquals('A', $a->value('title'));
        $this->assertEquals('B', $b->value('title'));
        $this->assertTrue($a->keys()->contains('title'));
        $this->assertEquals('a', $a->root()->id());
    }

    #[Test]
    public function it_resolves_values_through_a_normal_origin_chain()
    {
        $root = new OriginStub('root', ['title' => 'Root', 'shared' => 'from-root']);
        $child = new OriginStub('child', ['title' => 'Child'], $root);

        $this->assertFalse($child->hasOriginCycle());
        $this->assertEquals('Child', $child->value('title'));
        $this->assertEquals('from-root', $child->value('shared'));
        $this->assertEquals('root', $child->root()->id());
    }
}

class OriginStub
{
    use ContainsData, FluentlyGetsAndSets, HasOrigin;

    private ?self $originObject;

    public function __construct(private string $stubId, array $data, ?self $origin = null)
    {
        $this->data($data);
        $this->originObject = $origin;
        $this->origin = $origin?->id();
    }

    public function id()
    {
        return $this->stubId;
    }

    public function setOrigin(self $origin): self
    {
        $this->originObject = $origin;
        $this->origin = $origin->id();

        return $this;
    }

    public function getOriginByString($origin)
    {
        return $this->originObject && $this->originObject->id() === $origin
            ? $this->originObject
            : null;
    }
}
