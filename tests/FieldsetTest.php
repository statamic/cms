<?php namespace Tests;

use Statamic\Fields\Fieldset;
use Statamic\Extend\Fieldtype;

class FieldsetTest extends TestCase
{
    /** @var \Statamic\Fields\Fieldset */
    private $fieldset;

    private $contents;

    public function setUp()
    {
        parent::setUp();

        $this->contents = [
            'fields' => [
                'foo' => ['type' => 'text', 'validate' => 'required'],
                'bar' => ['type' => 'textarea', 'localizable' => true],
            ]
        ];

        $this->fieldset = new Fieldset;
        $this->fieldset->name('fieldset');
        $this->fieldset->contents($this->contents);
    }

    public function testGetsAndSetsType()
    {
        $this->assertEquals('default', $this->fieldset->type());

        $this->fieldset->type('settings');

        $this->assertEquals('settings', $this->fieldset->type());
    }

    public function testThrowsExceptionOnInvalidType()
    {
        $this->expectException('Exception');

        $this->fieldset->type('something');
    }

    public function testGetsAndSetsLocale()
    {
        $this->assertEquals('en', $this->fieldset->locale());

        $this->fieldset->locale('fr');

        $this->assertEquals('fr', $this->fieldset->locale());
    }

    public function testGetsAndSetsName()
    {
        $this->assertEquals('fieldset', $this->fieldset->name());

        $this->fieldset->name('foo');

        $this->assertEquals('foo', $this->fieldset->name());
    }

    public function testGetsPath()
    {
        $this->assertEquals(
            'resources/fieldsets/fieldset.yaml',
            $this->fieldset->path()
        );
    }

    public function testGetFields()
    {
        $fields = ['foo' => ['type' => 'text', 'validate' => 'required'],
                   'bar' => ['type' => 'textarea', 'localizable' => true]];

        $this->assertEquals($fields, $this->fieldset->fields());
    }

    public function testGetContents()
    {
        $this->assertEquals($this->contents, $this->fieldset->contents());
    }

    public function testTitleNotExplicitlySetUsesUppercasedFilename()
    {
        $this->assertEquals('Fieldset', $this->fieldset->title());
    }

    public function testTitleIsExplicitlySet()
    {
        $contents = $this->contents + ['title' => 'My Fieldset'];
        $this->fieldset->contents($contents);

        $this->assertEquals('My Fieldset', $this->fieldset->title());
    }

    public function testGetFieldtypes()
    {
        $this->markTestSkipped(); // Until taxonomies are reimplemented.

        $fieldtypes = $this->fieldset->fieldtypes();

        $this->assertTrue($fieldtypes[0] instanceof Fieldtype);
    }

    public function testConvertsToArray()
    {
        $expected = [
            'fields' => [
                [
                    'name' => 'foo',
                    'type' => 'text',
                    'display' => null,
                    'instructions' => null,
                    'validate' => 'required',
                    'required' => true,
                    'localizable' => false
                ],
                [
                    'name' => 'bar',
                    'type' => 'textarea',
                    'display' => null,
                    'instructions' => null,
                    'required' => false,
                    'localizable' => true
                ],
            ]
        ];

        $this->assertEquals($expected, $this->fieldset->toArray());
    }
}
