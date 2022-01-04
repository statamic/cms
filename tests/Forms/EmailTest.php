<?php

namespace Tests\Forms;

use Mockery as m;
use Statamic\Facades\Antlers;
use Statamic\Facades\Site;
use Statamic\Forms\Email;
use Statamic\Forms\Submission;
use Tests\TestCase;

class EmailTest extends TestCase
{
    /** @test */
    public function it_sets_html_view_as_string()
    {
        $email = $this->makeEmail();

        $email->build();

        $this->assertTrue(is_string($email->view), 'View is not a string.');
        $this->assertEquals('emails/test', $email->view);
    }

    /** @test */
    public function the_sites_locale_gets_used_on_the_mailable()
    {
        Site::setConfig(['sites' => [
            'one' => ['locale' => 'en_US', 'url' => '/one'],
            'two' => ['locale' => 'fr_Fr', 'url' => '/two'],
            'three' => ['locale' => 'de_CH', 'lang' => 'de_CH', 'url' => '/three'],
        ]]);

        $this->assertEquals('en', $this->makeEmail(Site::get('one'))->locale);
        $this->assertEquals('fr', $this->makeEmail(Site::get('two'))->locale);
        $this->assertEquals('de_CH', $this->makeEmail(Site::get('three'))->locale);
    }

    private function makeEmail($site = null)
    {
        /** @var Submission */
        $submission = m::mock(Submission::class);
        $submission->shouldReceive('toArray')->andReturn([]);
        $submission->shouldReceive('toAugmentedArray')->andReturn([]);

        return new Email($submission, [
            'to' => Antlers::parse('test@example.com'),
            'html' => Antlers::parse('emails/test'),
        ], $site ?? Site::default());
    }
}
