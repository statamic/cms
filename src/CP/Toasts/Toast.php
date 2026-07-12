<?php

namespace Statamic\CP\Toasts;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Holds information about a toast message to show to the user.
 */
class Toast implements Arrayable
{
    private const TYPES = ['error', 'success', 'info'];
    private $message;
    private $type;
    private $duration;

    /**
     * @param  string  $message  The message to display when showing the toast.
     * @param  string  $type  The type of toast. See Toast::TYPES for valid values.
     *
     * @throws Exception if the specified toast type is invalid.
     */
    public function __construct(string $message, string $type = 'info')
    {
        $this->message = $message;
        $this->type = $this->validateType($type);
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
    private function validateType(string $type): string
    {
        if (! in_array($type, self::TYPES)) {
            $validTypesString = implode(', ', self::TYPES);
            throw new Exception("Invalid toast type. Must be one of: $validTypesString");
        }

        return $type;
    }
}
