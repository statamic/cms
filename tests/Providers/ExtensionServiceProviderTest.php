<?php

namespace Tests\Providers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\Action;
use Statamic\Dictionaries\Dictionary;
use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Scope;
use Statamic\Tags\Tags;
use Statamic\Widgets\Widget;
use Tests\TestCase;

class ExtensionServiceProviderTest extends TestCase
{
    #[Test]
    #[DataProvider('extensionProvider')]
    public function core_extensions_are_registered_under_their_handles_and_aliases($type)
    {
        $registered = $this->app['statamic.extensions'][$type];

        $expected = [];

        foreach ($registered->unique() as $class) {
            foreach ([$class::handle(), ...$this->aliasesOf($class)] as $handle) {
                $expected[$handle] = $class;
            }
        }

        $this->assertNotEmpty($registered);
        $this->assertEquals($expected, $registered->all());
    }

    private function aliasesOf(string $class): array
    {
        return method_exists($class, 'aliases') ? $class::aliases() : [];
    }

    public static function extensionProvider()
    {
        return [
            'actions' => [Action::class],
            'dictionaries' => [Dictionary::class],
            'fieldtypes' => [Fieldtype::class],
            'scopes' => [Scope::class],
            'tags' => [Tags::class],
            'widgets' => [Widget::class],
        ];
    }

    #[Test]
    public function core_form_js_drivers_are_registered_under_their_handles()
    {
        $registered = $this->app['statamic.form-js-drivers'];

        $expected = $registered->mapWithKeys(fn ($class) => [$class::handle() => $class]);

        $this->assertNotEmpty($registered);
        $this->assertEquals($expected->all(), $registered->all());
    }
}
