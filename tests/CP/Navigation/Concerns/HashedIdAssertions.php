<?php

namespace Tests\CP\Navigation\Concerns;

use Statamic\CP\Navigation\NavTransformer;

trait HashedIdAssertions
{
    protected function assertIsHashedIdFor($id, $hashedId)
    {
        $this->assertEquals($id, NavTransformer::removeUniqueIdHash($id));

        $this->assertTrue((bool) preg_match('/.*[^\:]:[^\:]{6}$/', $hashedId));

        return $hashedId;
    }

    protected function assertHasHashedIdFor($id, $array)
    {
        $hashedId = collect($array)
            ->mapWithKeys(fn ($config, $id) => [NavTransformer::removeUniqueIdHash($id) => $id])
            ->get($id);

        $this->assertNotNull($hashedId);

        $this->assertIsHashedIdFor($id, $hashedId);

        return $hashedId;
    }
}
