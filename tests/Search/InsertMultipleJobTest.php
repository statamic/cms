<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\InsertMultipleJob;
use Tests\TestCase;

class InsertMultipleJobTest extends TestCase
{
    #[Test, DataProvider('timeoutProvider')]
    public function it_sets_the_timeout_from_config($configured, $expected)
    {
        config(['statamic.search.queue_timeout' => $configured]);

        $job = new InsertMultipleJob('test', null, collect(['entry::123']));

        $this->assertSame($expected, $job->timeout);
    }

    public static function timeoutProvider()
    {
        return [
            'not configured' => [null, null],
            'integer' => [300, 300],
            'string from env' => ['300', 300],
        ];
    }

    #[Test, DataProvider('queueConnectionsProvider')]
    public function it_uses_the_configured_queue_and_connection(
        $configuredQueue,
        $configuredConnection,
        $defaultConnection,
        $expectedJobQueue,
        $expectedJobConnection
    ) {
        config([
            'statamic.search.queue' => $configuredQueue,
            'statamic.search.queue_connection' => $configuredConnection,
            'queue.default' => $defaultConnection,
        ]);

        $job = new InsertMultipleJob('test', null, collect(['entry::123']));

        $this->assertSame($expectedJobQueue, $job->queue);
        $this->assertSame($expectedJobConnection, $job->connection);
    }

    /**
     * When the config keys are null the job leaves connection/queue null rather than
     * resolving them to the framework defaults, since config() only falls back to its
     * second argument when a key is absent, and these keys always ship in the config
     * file. A null connection/queue already means "use the default", so the outcome is
     * the same - but the properties stay null.
     */
    public static function queueConnectionsProvider()
    {
        return [
            [null, null, 'redis', null, null],
            ['indexing', null, 'redis', 'indexing', null],
            [null, 'sqs', 'redis', null, 'sqs'],
            ['indexing', 'sqs', 'redis', 'indexing', 'sqs'],
        ];
    }
}
