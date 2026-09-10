<?php

namespace Tests\Forms\Fieldtypes;

use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Assets\Assets;
use Statamic\Fieldtypes\Code;
use Statamic\Fieldtypes\Files;
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
    public function it_falls_back_to_the_equivalent_form_fieldtype_view()
    {
        // The Textarea fieldtype is wrapped by the LongAnswer form fieldtype.
        // Since there's no textarea view, it falls back to long_answer.
        $fallback = (new Fallback)->wrapping(new Textarea);

        $this->assertEquals('statamic::forms.antlers.fields.long_answer', $fallback->view());
    }

    #[Test]
    public function it_uses_icon_from_the_wrapped_fieldtype()
    {
        $fallback = (new Fallback)->wrapping(new Code);

        $this->assertEquals('fieldtype-code', $fallback->icon());
    }

    #[Test]
    public function it_uses_the_equivalent_form_fieldtype_icon_when_one_exists()
    {
        $fallback = (new Fallback)->wrapping(new Textarea);

        $this->assertEquals('text-long', $fallback->icon());
    }

    #[Test]
    public function it_falls_back_to_the_upload_view_when_wrapping_assets()
    {
        $fallback = (new Fallback)->wrapping(new Assets);

        $this->assertEquals('statamic::forms.antlers.fields.upload', $fallback->view());
    }

    #[Test]
    public function it_falls_back_to_the_upload_view_when_wrapping_files()
    {
        $fallback = (new Fallback)->wrapping(new Files);

        $this->assertEquals('statamic::forms.antlers.fields.upload', $fallback->view());
    }

    #[Test]
    public function it_prefers_a_published_assets_view_over_the_upload_fallback()
    {
        View::addNamespace('statamic', __DIR__.'/__fixtures__/views');

        $fallback = (new Fallback)->wrapping(new Assets);

        $this->assertEquals('statamic::forms.antlers.fields.assets', $fallback->view());
    }
}
