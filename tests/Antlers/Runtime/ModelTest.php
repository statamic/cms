<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Tests\Antlers\ParserTestCase;

class ModelTest extends ParserTestCase
{
    public function test_model_attributes_are_returned()
    {
        $model = FakeModel::make();
        $model->title = 'Title';

        $data = [
            'model' => $model,
        ];

        $template = <<<'EOT'
{{ model:title }}{{ model:foo_bar }}
EOT;

        $this->assertSame('TitleFooBar', $this->renderString($template, $data));
    }
}

class FakeModel extends \Illuminate\Database\Eloquent\Model
{
    public function fooBar(): Attribute
    {
        return Attribute::make(
            get: fn () => 'FooBar',
        );
    }
}
