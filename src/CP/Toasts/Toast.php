<?php

namespace Statamic\CP\Toasts;

/**
 * Holds information about a toast message to show to the user.
 */
class Toast
{
    /**
     * @var string
     */
    public $message;

    /**
     * @param  string  $message  The message to display when showing the toast.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
