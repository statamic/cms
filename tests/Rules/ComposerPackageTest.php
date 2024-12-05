<?php

namespace Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Rules\ComposerPackage;
use Tests\TestCase;

class ComposerPackageTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = ComposerPackage::class;

    #[Test]
    public function it_validates_handles()
    {
        $this->assertPasses('the-hasselhoff/kung-fury');
        $this->assertPasses('blastoff12345/chocolate-ship67890');

        $this->assertFails('not a package');
        $this->assertFails('not-a-package');
        $this->assertFails(' vendor/not-a-package');
        $this->assertFails('vendor/not-a-package ');
        $this->assertFails('vendor /not-a-package');
        $this->assertFails('vendor/ not-a-package');
    }

    #[Test]
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput(trans('statamic::validation.composer_package'), 'not-a-package');
    }
}
