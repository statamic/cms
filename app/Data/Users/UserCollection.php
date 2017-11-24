<?php

namespace Statamic\Data\Users;

use Statamic\Data\DataCollection;

/**
 * A collection of Users
 */
class UserCollection extends DataCollection
{
    /**
     * Get the collection as an array
     *
     * @param bool $supplement
     * @return array
     */
    public function extract($supplement = false)
    {
        return UserService::transform($this->items, $supplement);
    }
}
