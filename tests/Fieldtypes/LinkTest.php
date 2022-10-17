<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Statamic\Entries\Entry;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Tests\TestCase;

class LinkTest extends TestCase
{
    /** @test */
    public function it_augments_to_url()
    {
        ResolveRedirect::shouldReceive('resolve')
            ->with('entry::test', $parent = new Entry)
            ->once()
            ->andReturn('/the-redirect');

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertEquals('/the-redirect', $fieldtype->augment('entry::test'));
    }

    /** @test */
    public function it_augments_invalid_entry_to_null()
    {
        // invalid entries come back from the ResolveRedirect class as a 404 integer

        ResolveRedirect::shouldReceive('resolve')
            ->with('entry::test', $parent = new Entry)
            ->once()
            ->andReturn(404);

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->augment('entry::test'));
    }

    /** @test */
    public function it_augments_null_to_null()
    {
        // null could technically be passed to the ResolveRedirect class, where it would
        // just return null, but we'll just avoid calling it for a little less overhead.
        ResolveRedirect::shouldReceive('resolve')->never();

        $field = new Field('test', ['type' => 'link']);
        $field->setParent(new Entry);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->augment(null));
    }
}
