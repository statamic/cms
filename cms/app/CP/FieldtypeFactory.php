<?php

namespace Statamic\CP;

use Statamic\Exceptions\FatalException;
use Statamic\Exceptions\ResourceNotFoundException;
use Statamic\Extend\Management\FieldtypeLoader;

/**
 * Creates a Fieldtype instance
 */
class FieldtypeFactory
{
    /**
     * @var FieldtypeLoader
     */
    private $loader;

    public function __construct(FieldtypeLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Create a new fieldtype
     *
     * @param string $type
     * @param array  $config
     * @return \Statamic\Extend\Fieldtype
     * @throws \Statamic\Exceptions\FatalException
     */
    public static function create($type, array $config = [])
    {
        $instance = app(static::class);

        try {
            $fieldtype = $instance->loader->load($type, $config);
        } catch (ResourceNotFoundException $e) {
            $message = "Fieldtype [$type] does not exist.";

            if ($suggestion = $instance->getSuggestion($type)) {
                $message .= " Try [$suggestion].";
            }

            throw new FatalException($message);
        }

        return $fieldtype;
    }

    /**
     * Get a suggestion for the unknown fieldtype, if they've used a deprecated one from v1.
     *
     * @param  string $type
     * @return null|string
     */
    private function getSuggestion($type)
    {
        switch ($type) {
            case 'file':
                return 'assets';
            case 'markitup':
                return 'markdown';
        }
    }
}
