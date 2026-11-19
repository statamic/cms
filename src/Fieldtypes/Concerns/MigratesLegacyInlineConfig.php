<?php

namespace Statamic\Fieldtypes\Concerns;

trait MigratesLegacyInlineConfig
{
    public function migrateConfig(array $values): array
    {
        if (($values['inline'] ?? false) && ! array_key_exists('appearance', $values)) {
            $values['appearance'] = 'inline';
        }

        unset($values['inline']);

        return $values;
    }
}
