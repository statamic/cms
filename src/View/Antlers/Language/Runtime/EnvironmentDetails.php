<?php

namespace Statamic\View\Antlers\Language\Runtime;

class EnvironmentDetails
{
    private static $alwaysLikeTag = [
        'if' => 1,
        'endif' => 1,
        'elseif' => 1,
        'else' => 1,
        'unless' => 1,
        'elseunless' => 1,
    ];

    protected $modifierNames = [];

    protected $tagNames = [];

    public function getTagNames()
    {
        return $this->tagNames;
    }

    public function setTagNames($tagNames)
    {
        $this->tagNames = array_flip($tagNames);
    }

    public function setModifierNames($modifierNames)
    {
        $this->modifierNames = array_flip($modifierNames);
    }

    public function isTag($value)
    {
        if (array_key_exists($value, self::$alwaysLikeTag)) {
            return true;
        }

        return array_key_exists($value, $this->tagNames);
    }

    public function isModifier($value)
    {
        return array_key_exists($value, $this->modifierNames);
    }
}
