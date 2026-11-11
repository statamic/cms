<?php

namespace Tests\Tags\Form;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;

class FormCaptchaTest extends FormTestCase
{
    const WIDGET = '<div class="cf-turnstile" data-captcha data-sitekey="site-key"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.forms.captcha' => [
            'driver' => 'turnstile',
            'site_key' => 'site-key',
            'secret_key' => 'secret-key',
        ]]);

        Form::find('contact')->data(['captcha' => true]);
    }

    #[Test]
    public function it_adds_the_widget_to_the_end_of_the_form()
    {
        $output = $this->tag('{{ form:contact }}<button>Send</button>{{ /form:contact }}');

        $this->assertStringContainsString('<button>Send</button>'.self::WIDGET.'</form>', $output);
    }

    #[Test]
    public function it_doesnt_add_the_widget_when_the_template_places_it()
    {
        $output = $this->tag('{{ form:contact }}{{ captcha }}<button>Send</button>{{ /form:contact }}');

        $this->assertEquals(1, substr_count($output, 'data-captcha'));
        $this->assertStringContainsString(self::WIDGET.'<button>Send</button></form>', $output);
    }

    #[Test]
    public function it_places_the_widget_with_the_captcha_tag()
    {
        $output = $this->tag('{{ form:contact }}<div class="wrap">{{ form:captcha }}</div><button>Send</button>{{ /form:contact }}');

        $this->assertEquals(1, substr_count($output, 'data-captcha'));
        $this->assertStringContainsString('<div class="wrap">'.self::WIDGET.'</div>', $output);
    }

    #[Test]
    public function the_captcha_tag_can_be_used_with_a_form_handle()
    {
        $this->assertEquals(self::WIDGET, $this->tag('{{ form:captcha in="contact" }}'));
    }

    #[Test]
    public function it_doesnt_render_anything_when_the_form_doesnt_use_a_captcha()
    {
        Form::find('contact')->data(['captcha' => false]);

        $output = $this->tag('{{ form:contact }}{{ captcha }}{{ form:captcha }}<button>Send</button>{{ /form:contact }}');

        $this->assertStringNotContainsString('data-captcha', $output);
    }

    #[Test]
    public function it_doesnt_render_anything_when_captchas_arent_set_up()
    {
        config(['statamic.forms.captcha.site_key' => null]);

        $output = $this->tag('{{ form:contact }}<button>Send</button>{{ /form:contact }}');

        $this->assertStringNotContainsString('data-captcha', $output);
    }

    #[Test]
    public function it_only_renders_the_widget_on_the_last_page()
    {
        $this->createMultiPageForm();
        Form::find('survey')->data(['captcha' => true]);

        $this->assertStringNotContainsString('data-captcha', $this->tag('{{ form:survey }}<button>Next</button>{{ /form:survey }}'));

        request()->query->set('page', 'page_two');

        $this->assertStringContainsString(self::WIDGET.'</form>', $this->tag('{{ form:survey }}<button>Send</button>{{ /form:survey }}'));
    }

    #[Test]
    public function it_includes_the_widget_in_blade_component_data()
    {
        $output = $this->blade('<s:form:contact>{!! $captcha !!}</s:form:contact>');

        $this->assertEquals(1, substr_count($output, 'data-captcha'));
    }

    #[Test]
    public function it_shows_an_error_when_the_captcha_fails()
    {
        Http::fake(['https://challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this
            ->post('/!/forms/contact', ['email' => 'san@holo.com', 'cf-turnstile-response' => 'bad'])
            ->assertSessionHasErrors(['captcha'], null, 'form.contact')
            ->assertLocation('/');

        $this->assertCount(0, Form::find('contact')->submissions());

        $output = $this->tag('{{ form:contact }}{{ error:captcha }}{{ /form:contact }}');

        $this->assertStringContainsString('The captcha could not be verified. Please try again.', $output);
    }

    #[Test]
    public function it_submits_when_the_captcha_passes()
    {
        Http::fake(['https://challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this
            ->post('/!/forms/contact', ['email' => 'san@holo.com', 'cf-turnstile-response' => 'good'])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertCount(1, Form::find('contact')->submissions());
    }
}
