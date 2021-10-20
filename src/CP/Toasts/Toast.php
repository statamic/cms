<?php

namespace Statamic\CP\Toasts;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Holds information about a toast message to show to the user.
 */
class Toast implements Arrayable
{
    private static $VALID_TYPES = ['error', 'success', 'info'];
    private $message;
    private $type;
    private $duration;

    /**
     * @param  string  $message  The message to display when showing the toast.
     * @param  string  $type  The type of toast. See Toast::$VALID_TYPES for valid values.
     *
     * @throws Exception if the specified toast type is invalid.
     */
    public function __construct(string $message, string $type = 'info')
    {
        $this->message = $message;
        $this->validateType($type);
        $this->type = $type;
    }

    public function duration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'duration' => $this->duration,
        ];
    }

    /**
     * @throws Exception
     */
    private function validateType(string $type)
    {
        if (! in_array($type, self::$VALID_TYPES)) {
            $validTypesString = implode(', ', self::$VALID_TYPES);
            throw new Exception("Invalid toast type. Must be one of: $validTypesString");
        }
    }
}
