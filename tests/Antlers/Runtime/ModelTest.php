<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Database\Eloquent\Casts\Attribute;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class ModelTest extends ParserTestCase
{
    #[Test, DataProvider('modelProvider')]
    public function attributes_are_returned($attribute, $expected)
    {
        $model = new FakeModel;
        $model->title = 'foo';

        $this->assertSame($expected, $this->renderString("{{ model:$attribute }}", ['model' => $model]));
    }

    #[Test, DataProvider('modelProvider')]
    public function attributes_are_returned_in_tag_pair($attribute, $expected)
    {
        $model = new FakeModel;
        $model->title = 'foo';

        $this->assertSame($expected, $this->renderString("{{ model }}{{ $attribute }}{{ /model }}", ['model' => $model]));
    }

    public static function modelProvider()
    {
        return [
            'column' => ['title', 'foo'],
            'accessor' => ['alfa_bravo', 'charlie'],
            'old accessor' => ['delta_echo', 'foxtrot'],
        ];
    }

    #[Test]
    public function variable_references_receive_models()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                $src = $this->params->get('value');

                return $src instanceof FakeModel ? 'Yes' : 'No';
            }
        })::register();

        $this->assertSame('Yes', $this->renderString('{{ %tag :value="model" }}', ['model' => new FakeModel]));
    }
}

class FakeModel extends \Illuminate\Database\Eloquent\Model
{
    public function alfaBravo(): Attribute
    {
        return Attribute::make(
            get: fn () => 'charlie',
        );
    }

    public function getDeltaEchoAttribute()
    {
        return 'foxtrot';
    }
}
