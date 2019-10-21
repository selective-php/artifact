<?php

namespace Selective\Artifact\Utility;

/**
 * Text Formatter.
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

    /**
     * Returns the given string as an underscored_string.
     *
     * Also replaces dashes with underscores.
     *
     * @param string $version The version
     *
     * @return string The underscore_version of the input string
     */
    public static function flatVersion(string $version): string
    {
        $numbers = '';
        foreach (explode('.', $version) as $number) {
            $numbers .= str_pad($number, 2, '0', STR_PAD_LEFT);
        }

        return $numbers;
    }
}
