<?php

namespace Statamic\Contracts\Git;

interface ProvidesCommitMessage
{
    /**
     * Get the Git commit message.
     *
     * @return string
     */
    public function commitMessage();
}
