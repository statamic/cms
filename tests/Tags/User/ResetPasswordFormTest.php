<?php

namespace Tests\Tags\User;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResetPasswordFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    #[Test]
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:reset_password_form }}{{ /user:reset_password_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/password/reset">', $output);
    }
}
