<?php

namespace Statamic\Composer;

use Facades\Statamic\Composer\Composer;

class CoreChangelog
{
    /**
     * Get changelog, sorted from newest to oldest.
     */
    public function get()
    {
        // Will actually get changelog from database later.
        return collect([
            '3.1.2' => (object) [
                $this->new('Even more crazy new things!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '3.1.1' => (object) [
                $this->new('More crazy new things!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '3.1.0' => (object) [
                $this->new('Crazy new things!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '3.0.1' => (object) [
                $this->new('Such new!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '3.0.0' => (object) [
                $this->new('Much wow!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
        ]);
    }

    /**
     * New change type.
     *
     * @param \stdClass $change
     */
    protected function new($change)
    {
        return (object) [
            'type' => 'new',
            'change' => $change,
        ];
    }

    /**
     * Fix change type.
     *
     * @param \stdClass $change
     */
    protected function fix($change)
    {
        return (object) [
            'type' => 'fix',
            'change' => $change,
        ];
    }
}
