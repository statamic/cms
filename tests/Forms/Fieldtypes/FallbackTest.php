<?php

namespace Tests\Forms\Fieldtypes;

use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Text;
use Statamic\Fieldtypes\Textarea;
use Statamic\Forms\Fieldtypes\Fallback;
use Tests\TestCase;

class FallbackTest extends TestCase
{
    #[Test]
    public function it_resolves_view_using_wrapped_fieldtype_handle()
    {
        View::addNamespace('statamic', __DIR__.'/__fixtures__/views');

        $fallback = (new Fallback)->wrapping(new Text);

        $this->assertEquals('statamic::forms.antlers.fields.text', $fallback->view());
    }

    #[Test]
    public function it_finds_form_fieldtype_view_for_wrapped_fieldtype()
    {
        // The Textarea fieldtype is wrapped by the LongAnswer form fieldtype.
        $fallback = (new Fallback)->wrapping(new Textarea);

        $this->assertEquals('statamic::forms.antlers.fields.long_answer', $fallback->view());
    }
}
