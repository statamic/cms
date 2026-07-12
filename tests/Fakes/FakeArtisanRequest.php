<?php

namespace Tests\Fakes;

class FakeArtisanRequest extends \Illuminate\Http\Request
{
    protected $artisanCommand;

    public function __construct($artisanCommand)
    {
        $this->artisanCommand = $artisanCommand;
    }

    public function server($key = null, $default = null)
    {
        return [
            'argv' => [
                'artisan',
                $this->artisanCommand,
            ],
        ];
    }
}
