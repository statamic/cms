<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Sites;
use Tests\TestCase;

class SitesTest extends TestCase
{
    #[Test]
    public function it_preprocesses_index_values_with_site_groups()
    {
        $this->setSites([
            'en' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
                'group' => 'UK',
                'group_handle' => 'uk',
            ],
            'fr' => [
                'name' => 'French',
                'locale' => 'fr_FR',
                'url' => '/fr/',
                'group' => 'EU',
                'group_handle' => 'eu',
            ],
        ]);

        $this->assertEquals([
            [
                'title' => 'English',
                'group' => 'UK',
                'group_handle' => 'uk',
            ],
        ], $this->fieldtype(['max_items' => 1])->preProcessIndex('en'));

        $this->assertEquals([
            [
                'title' => 'English',
                'group' => 'UK',
                'group_handle' => 'uk',
            ],
            [
                'title' => 'French',
                'group' => 'EU',
                'group_handle' => 'eu',
            ],
        ], $this->fieldtype()->preProcessIndex(['en', 'fr']));
    }

    #[Test]
    public function it_preprocesses_index_values_without_site_groups()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => '/fr/'],
        ]);

        $this->assertEquals([
            [
                'title' => 'English',
                'group' => null,
                'group_handle' => null,
            ],
        ], $this->fieldtype(['max_items' => 1])->preProcessIndex('en'));
    }

    private function fieldtype($config = [])
    {
        $field = new Field('test', array_merge([
            'type' => 'sites',
        ], $config));

        return (new Sites)->setField($field);
    }
}
