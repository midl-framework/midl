<?php
namespace midl\html;

class HTML
{

    /**
     * Gets encoded HTML string,
     * - HMTL characters will be converted to the equivalent entities
     * - Optionally new lines will be converted to <br /> tag
     *
     * @param string $input User input
     * @param bool $convertNl2br [optional] Whether to convert new lines to <br /> tag or not, defaults to false
     * @return string Safe to print HTML string
     */
    public static function esc($input, $convertNl2br = false)
    {
        if ($convertNl2br)
            $value = nl2br(htmlentities($input, ENT_QUOTES, "UTF-8"));
        else
            $value = htmlentities($input, ENT_QUOTES, "UTF-8");
        
        return $value;
    }

    /**
     * Escapes string to be used in html attributes
     *
     * @param string $value Attribute value
     * @return string Escaped attribute string
     */
    public static function escAttr($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
    }

    /**
     * Escape attributes for being used in javascript specific attributes like onclick.
     * Replacing double quotes at the begining and end of string values with single quotes.
     *
     * @param string $value Attribute value
     * @return string Escaped attribute string
     */
    public static function escJSAttr($value)
    {
        return htmlspecialchars(json_encode($value, JSON_HEX_APOS), ENT_QUOTES, "UTF-8");
    }
}
