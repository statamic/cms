<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class InstalledTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PackToTheFuture::generateComposerLock('hasselhoff/baywatch-embeds', '1.0.0', base_path('composer.lock'));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    #[Test]
    public function it_can_check_if_package_is_installed_using_if_conditional()
    {
        $this->assertEquals('yes', $this->tag('{{ if {installed:hasselhoff/baywatch-embeds} }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->tag('{{ if {installed:hasselhoff/lotr-embeds} }}yes{{ else }}no{{ /if }}'));
    }

    #[Test]
    public function it_can_check_if_package_is_installed_using_tag_pair()
    {
        $this->assertEquals('yes', $this->tag('{{ installed:hasselhoff/baywatch-embeds }}yes{{ /installed:hasselhoff/baywatch-embeds }}'));
        $this->assertEquals('', $this->tag('{{ installed:hasselhoff/lotr-embeds }}yes{{ /installed:hasselhoff/lotr-embeds }}'));
    }
}
