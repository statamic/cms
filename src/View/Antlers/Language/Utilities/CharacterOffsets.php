<?php

namespace Statamic\View\Antlers\Language\Utilities;

class CharacterOffsets
{
    const MAX_ADVANCE_STEP = 32768;

    /**
     * @param  int[]  $offsets
     * @param  int  $length
     * @return array<int, int>
     */
    protected static function identity(array $offsets, $length)
    {
        $mapped = [];

        foreach ($offsets as $offset) {
            $mapped[$offset] = min($offset, $length);
        }

        return $mapped;
    }

    /**
     * @param  string  $source
     * @param  bool|null  $known
     * @return bool
     */
    protected static function isMultibyte($source, $known)
    {
        return $known ?? strlen($source) !== mb_strlen($source);
    }

    /**
     * @param  string  $source
     * @param  int[]  $characterOffsets
     * @param  bool|null  $sourceIsMultibyte
     * @return array<int, int>
     */
    public static function toBytes($source, array $characterOffsets, $sourceIsMultibyte = null)
    {
        if ($characterOffsets === []) {
            return [];
        }

        $byteLength = strlen($source);

        if (! self::isMultibyte($source, $sourceIsMultibyte)) {
            return self::identity($characterOffsets, $byteLength);
        }

        if (! preg_match('//u', $source)) {
            return self::toBytesByteLoop($source, $characterOffsets);
        }

        $sorted = array_values(array_unique($characterOffsets));
        sort($sorted, SORT_NUMERIC);

        $offsets = [];
        $byte = 0;
        $character = 0;
        $reachedEnd = false;

        foreach ($sorted as $target) {
            if ($target <= 0) {
                $offsets[$target] = 0;

                continue;
            }

            if (! $reachedEnd && $target > $character) {
                $advanced = self::advanceCharacters($source, $byte, $target - $character);

                if ($advanced === null) {
                    $reachedEnd = true;
                } else {
                    $byte = $advanced;
                    $character = $target;
                }
            }

            $offsets[$target] = $reachedEnd ? $byteLength : $byte;
        }

        return $offsets;
    }

    /**
     * @param  string  $source
     * @param  int  $byte
     * @param  int  $characters
     * @return int|null
     */
    protected static function advanceCharacters($source, $byte, $characters)
    {
        $step = self::MAX_ADVANCE_STEP;

        while ($characters > 0) {
            if ($step > $characters) {
                $step >>= 1;

                continue;
            }

            if (preg_match('/\G.{'.$step.'}/us', $source, $match, 0, $byte) !== 1) {
                return null;
            }

            $byte += strlen($match[0]);
            $characters -= $step;
        }

        return $byte;
    }

    /**
     * @param  string  $source
     * @param  int[]  $characterOffsets
     * @return array<int, int>
     */
    protected static function toBytesByteLoop($source, array $characterOffsets)
    {
        $byteLength = strlen($source);
        $needed = array_fill_keys($characterOffsets, true);
        $offsets = [];
        $character = 0;

        for ($byte = 0; $byte < $byteLength; $byte++) {
            if ((ord($source[$byte]) & 0xC0) === 0x80) {
                continue;
            }

            if (isset($needed[$character])) {
                $offsets[$character] = $byte;
            }

            $character++;
        }

        foreach ($needed as $neededCharacter => $unused) {
            if (! isset($offsets[$neededCharacter])) {
                $offsets[$neededCharacter] = $neededCharacter <= 0 ? 0 : $byteLength;
            }
        }

        return $offsets;
    }

    /**
     * Map offsets in LF-normalized text back to the original UTF-8 source.
     * CRLF counts as one normalized character and two authored characters.
     *
     * The first map is normalized character offset => source byte offset.
     * The second map is source byte offset => source character offset.
     *
     * @param  string  $source
     * @param  int[]  $characterOffsets
     * @return array{0: array<int, int>, 1: array<int, int>}
     */
    public static function normalizedToBytesAndCharacters($source, array $characterOffsets)
    {
        if ($characterOffsets === []) {
            return [[], []];
        }

        $byteLength = strlen($source);

        if ($byteLength === mb_strlen($source) && strpos($source, "\r\n") === false) {
            $bytes = self::identity($characterOffsets, $byteLength);
            $characters = [];

            foreach ($bytes as $byte) {
                $characters[$byte] = $byte;
            }

            return [$bytes, $characters];
        }

        $needed = array_fill_keys($characterOffsets, true);
        $bytes = [];
        $characters = [];
        $normalizedCharacter = 0;
        $sourceCharacter = 0;

        for ($byte = 0; $byte < $byteLength; $byte++) {
            if ((ord($source[$byte]) & 0xC0) === 0x80) {
                // UTF-8 continuation bytes do not begin a new character.
                continue;
            }

            if (isset($needed[$normalizedCharacter]) && ! isset($bytes[$normalizedCharacter])) {
                $bytes[$normalizedCharacter] = $byte;
                $characters[$byte] = $sourceCharacter;
            }

            if ($source[$byte] === "\r" && $byte + 1 < $byteLength && $source[$byte + 1] === "\n") {
                $byte++;
                $sourceCharacter++;
            }

            $normalizedCharacter++;
            $sourceCharacter++;
        }

        foreach ($needed as $neededCharacter => $unused) {
            if (! isset($bytes[$neededCharacter])) {
                $bytes[$neededCharacter] = $byteLength;
                $characters[$byteLength] = $sourceCharacter;
            }
        }

        return [$bytes, $characters];
    }

    /**
     * @param  string  $source
     * @param  int[]  $byteOffsets
     * @param  bool|null  $sourceIsMultibyte
     * @param  int  $anchorByte
     * @param  int  $anchorCharacter
     * @return array<int, int>
     */
    public static function toCharacters($source, array $byteOffsets, $sourceIsMultibyte = null, $anchorByte = 0, $anchorCharacter = 0)
    {
        if ($byteOffsets === []) {
            return [];
        }

        $byteLength = strlen($source);

        if (! self::isMultibyte($source, $sourceIsMultibyte)) {
            return self::identity($byteOffsets, $byteLength);
        }

        $sorted = array_values(array_unique($byteOffsets));
        sort($sorted, SORT_NUMERIC);

        $offsets = [];
        $character = 0;
        $previousByte = 0;

        foreach ($sorted as $byte) {
            if ($byte >= $anchorByte && $previousByte < $anchorByte) {
                $previousByte = $anchorByte;
                $character = $anchorCharacter;
            }

            $lead = min($byte, $byteLength);

            while ($lead > 0 && $lead < $byteLength && (ord($source[$lead]) & 0xC0) === 0x80) {
                $lead--;
            }

            if ($lead > $previousByte) {
                $character += self::countCharacters($source, $previousByte, $lead);
                $previousByte = $lead;
            }

            $offsets[$byte] = $character;
        }

        return $offsets;
    }

    /**
     * @param  string  $source
     * @param  int  $start
     * @param  int  $end
     * @return int
     */
    protected static function countCharacters($source, $start, $end)
    {
        $length = $end - $start;

        return $length - preg_match_all('/[\x80-\xBF]/', substr($source, $start, $length));
    }
}
