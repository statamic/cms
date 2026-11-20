<?php

namespace Tests\Feature\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldActionModalControllerTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function process(array $fields, array $values)
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        return $this
            ->actingAs($user)
            ->postJson('/cp/field-action-modal/process', [
                'fields' => $fields,
                'values' => $values,
            ]);
    }

    #[Test]
    public function it_validates_using_the_submitted_rules()
    {
        $this->process(
            ['x' => ['type' => 'text', 'validate' => ['required']]],
            ['x' => null],
        )->assertUnprocessable()->assertJsonValidationErrors('x');
    }

    #[Test]
    public function it_does_not_instantiate_classes_that_are_not_validation_rules()
    {
        FieldActionModalNotARule::$constructed = false;

        $this->withoutExceptionHandling();

        try {
            $this->process(
                ['x' => ['type' => 'text', 'validate' => ['new \\'.FieldActionModalNotARule::class.'("foo")']]],
                ['x' => 'y'],
            );
            $this->fail('Expected an InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            //
        }

        $this->assertFalse(FieldActionModalNotARule::$constructed);
    }
}

class FieldActionModalNotARule
{
    public static $constructed = false;

    public function __construct(...$args)
    {
        static::$constructed = true;
    }
}
