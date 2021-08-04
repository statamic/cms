<?php

namespace Tests\Events;

use Statamic\Events\EntrySavingMessageStore;
use Tests\TestCase;

class EntrySavingMessageStoreTest extends TestCase
{
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new EntrySavingMessageStore();
    }

    /** @test */
    public function it_provides_a_generic_message_if_no_custom_message_is_specified()
    {
        $genericMessage = __('Saved');

        $this->assertMessageIs($genericMessage);
    }

    /** @test */
    public function it_provides_an_empty_message_if_an_empty_message_is_specified()
    {
        $empty = '';

        $this->store->addSuccessMessage($empty);

        $this->assertMessageIs($empty);
    }

    /** @test */
    public function it_provides_a_single_specified_message()
    {
        $customMessage = 'Changes applied!';

        $this->store->addSuccessMessage($customMessage);

        $this->assertMessageIs($customMessage);
    }

    /** @test */
    public function it_provides_multiple_specified_messages()
    {
        $firstMessage = 'Changes applied';
        $secondMessage = 'Notification sent';

        $this->store->addSuccessMessage($firstMessage);
        $this->store->addSuccessMessage($secondMessage);

        $eol = PHP_EOL;
        $this->assertMessageIs("$firstMessage$eol$secondMessage");
    }

    private function assertMessageIs($expected): void
    {
        $this->assertEquals(
            $expected,
            $this->store->getMessage()
        );
    }
}
