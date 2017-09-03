<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\i18n;

class Translator extends Translate
{

    /**
     * Translate string
     *
     * @return string Translated string
     */
    public function t($message, $domain = null)
    {
        return $this->translate($message, $domain);
    }

    /**
     * Translate plural string
     *
     * @param string $singular
     * @param string $plural
     * @param int $n
     * @param string $domain
     * @return string Translated plural string
     */
    public function tn($singular, $plural, $n, $domain = null)
    {
        return $this->translatePlural($singular, $plural, $n, $domain);
    }

    /**
     *
     * @return string Translated string
     */
    public function tSpf($message)
    {
        $args = func_get_args();
        array_splice($args, 0, 1, $this->t($message));
        
        return call_user_func_array("sprintf", $args);
    }

    /**
     *
     * @return string Translated plural string
     */
    public function tSpfN($singular, $plural, $n)
    {
        $args = func_get_args();
        array_splice($args, 0, 2, $this->tn($singular, $plural, $n));
        
        return call_user_func_array("sprintf", $args);
    }

    /**
     *
     * @return void
     */
    public function tPf($message)
    {
        $args = func_get_args();
        array_splice($args, 0, 1, $this->t($message));
        
        call_user_func_array("printf", $args);
    }

    /**
     *
     * @return void
     */
    public function tPfN($singular, $plural, $n)
    {
        $args = func_get_args();
        array_splice($args, 0, 2, $this->tn($singular, $plural, $n));
        
        call_user_func_array("printf", $args);
    }

    /**
     *
     * @return void
     */
    public function e($message, $domain = null)
    {
        echo $this->t($message, $domain);
    }

    /**
     *
     * @return void
     */
    public function eN($singular, $plural, $n, $domain = null)
    {
        echo $this->tn($singular, $plural, $n, $domain);
    }
}
