<?php

namespace Ogone;

class Encoding
{
    /**
     * Convert character encoding.
     *
     * @param string|array $data
     * @param string $in_charset
     * @param string $out_charset
     * @return string|array
     */
    public static function convert($data, $in_charset, $out_charset)
    {
        if (is_string($data)) {
            $data = mb_convert_encoding($data, $out_charset, $in_charset);
        } elseif (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $key = mb_convert_encoding($data, $out_charset, $key);
                if (is_string($value)) {
                    $value = mb_convert_encoding($data, $out_charset, $value);
                    $result[$key] = $value;
                } elseif (is_array($value)) {
                    $result[$key] = convert($data, $in_charset, $out_charset);
                }
            }

            return $result;
        }

        return $data;
    }

    /**
     * Convert data from UTF-8 to Western European.
     *
     * @param string|array $data
     * @return string|array
     */
    public static function convertToLatin($data) {
        return self::convert($data, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Convert data from Western European to UTF-8.
     *
     * @param string|array $data
     * @return string|array
     */
    public static function convertToUtf8($data) {
        return self::convert($data, 'ISO-8859-1', 'UTF-8');
    }
}
