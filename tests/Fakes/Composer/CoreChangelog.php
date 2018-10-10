<?php

namespace Tests\Fakes\Composer;

use Statamic\Composer\CoreChangelog as RealChangelog;

class CoreChangelog extends RealChangelog
{
    public function get()
    {
        return collect([
            '1.1.2' => (object) [
                $this->new('Even more crazy new things!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '1.1.1' => (object) [
                $this->new('More crazy new things!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '1.1.0' => (object) [
                $this->new('Crazy new things!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '1.0.1' => (object) [
                $this->new('Such new!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
            '1.0.0' => (object) [
                $this->new('Much wow!'),
                $this->fix('Fixed this.'),
                $this->fix('Fixed that.'),
            ],
        ]);
    }
}
