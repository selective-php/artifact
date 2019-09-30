<?php

namespace Selective\Artifact\Utility;

/**
 * Text Formatter
 */
final class TextFormatter
{
    /**
     * Returns the given string as an underscored_string.
     *
     * Also replaces dashes with underscores.
     *
     * @param string $string CamelCasedString to be "underscorized"
     *
     * @return string The underscore_version of the input string
     */
    public static function underscore(string $string): string
    {
        $delimiter = '_';
        $string = str_replace('-', $delimiter, $string);

        return mb_strtolower((string)preg_replace('/(?<=\\w)([A-Z])/', $delimiter . '\\1', $string));
    }
}
