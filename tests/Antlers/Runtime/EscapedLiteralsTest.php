<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class EscapedLiteralsTest extends ParserTestCase
{
    public function test_escaped_opening_brace_works()
    {
        // These should remain untouched since they are __NOT__
        // conflicting with a valid Antlers start range, and
        // are not ambiguous Antlers regions. This behavior
        // would only apply outside of an Antlers region.
        $this->assertSame('@{', $this->renderString('@{'));
        $this->assertSame('@@{', $this->renderString('@@{'));

        $this->assertSame('{{{{ hello world', $this->renderString('@{{{{ hello world'));
        $this->assertSame('hello{', $this->renderString('{{ "hello@{" }}'));
        $this->assertSame('hello@{', $this->renderString('{{ "hello@@{" }}'));
        $this->assertSame('helloworld', $this->renderString('{{ "hello{"world"}" }}'));
        $this->assertSame('helloworld}', $this->renderString('{{ "hello{"world@}"}" }}'));
        $this->assertSame('hello{world}', $this->renderString('{{ "hello{"@{world@}"}" }}'));
        $this->assertSame('hello@{world@}', $this->renderString('{{ "hello{"@@{world@@}"}" }}'));
    }
}
