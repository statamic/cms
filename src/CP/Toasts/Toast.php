<?php

namespace Statamic\CP\Toasts;

use Exception;

/**
 * Holds information about a toast message to show to the user.
 */
class Toast
{
    private static $VALID_TYPES = ['error', 'success', 'info'];

    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $type;

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

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
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
