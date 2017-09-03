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

class Translate
{

    /**
     * Current locale identifier, e.g.: en-US
     *
     * @var string
     */
    protected $locale;

    /**
     * Current text domain
     *
     * @var string
     */
    protected $domain = "default";

    /**
     * Translate adapter to load translation data
     *
     * @var TranslateAdapter
     */
    protected $adapter;

    /**
     * Translations data array, structure:
     * [
     * domain1 => [ locale => [ msg => translated_msg, ...], ...],
     * domain2 => [ locale => [ msg => translated_msg, ...], ...],
     * domain3 => [ locale => [ msg_plural => [singular_txt, plural_txt], ...], ...],
     * ...
     * ]
     *
     * @var array
     */
    protected $translations = [];

    /**
     *
     * @param TranslateAdapter $adapter A valid translate adapter to load translation data
     * @param string $locale [optional]
     * @param string $domain [optional]
     * @throws \Exception If failed to load translation data
     */
    public function __construct(TranslateAdapter $adapter, $locale = null, $domain = null)
    {
        $this->adapter = $adapter;
        
        if ($locale)
            $this->setLocale($locale);
        
        if ($domain)
            $this->setDomain($domain);
        
        if ($this->domain && $this->locale)
            $this->loadTranslationData();
    }

    /**
     * Translate message
     *
     * @return string Translated string
     */
    public function translate($message, $domain = null, $locale = null)
    {
        $domain = ((string)$domain) ?: $this->domain;
        $locale = ((string)$locale) ?: $this->locale;
        
        if (isset($this->translations[$domain][$locale][$message])) {
            $translations = $this->translations[$domain][$locale][$message];
            
            if (is_array($translations) && isset($translations[0]))
                return $translations[0];
            else
                return $translations ?: $message;
        }
        
        return $message;
    }

    /**
     * Translate plural message
     *
     * @return string Translated string
     */
    public function translatePlural($singular, $plural, $n, $domain = null, $locale = null)
    {
        $domain = ((string)$domain) ?: $this->domain;
        $locale = ((string)$locale) ?: $this->locale;
        
        if (isset($this->translations[$domain][$locale][$singular])) {
            $translations = $this->translations[$domain][$locale][$singular];
            $index = $n == 1 ? 0 : 1;
            
            if (is_array($translations) && isset($translations[$index]))
                return $translations[$index];
        }
        
        return $n == 1 ? $singular : $plural;
    }

    /**
     * Gets current text domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets text domain
     *
     * @param string $domain Text domain
     * @return void
     */
    public function setDomain($domain)
    {
        if ($domain && is_string($domain))
            $this->domain = $domain;
    }

    /**
     * Sets text domain and loads translation data for new domain
     *
     * @param string $domain Text domain
     * @return void
     */
    public function changeDomain($domain)
    {
        if ($domain && is_string($domain)) {
            $this->domain = $domain;
            
            if ($this->locale)
                $this->loadTranslationData();
        }
    }

    /**
     * Sets locale identifier
     *
     * @param string $locale Locale identifier
     * @return void
     */
    public function setLocale($locale)
    {
        if ($locale && is_string($locale))
            $this->locale = $locale;
    }

    /**
     * Sets locale identifier and loads translation data for new locale
     *
     * @param string $locale Locale identifier
     * @return void
     */
    public function changeLocale($locale)
    {
        if ($locale && is_string($locale)) {
            $this->locale = $locale;
            
            if ($this->domain)
                $this->loadTranslationData();
        }
    }

    /**
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Loads and parse translation data for current domain and current locale.
     *
     * @see TranslateAdapter::loadTranslationData()
     */
    public function loadTranslationData()
    {
        $data = $this->adapter->loadTranslationData($this->domain, $this->locale);
        
        if (is_array($data))
            $this->addTranslationData($data);
    }

    /**
     * Adds translation data, sample data:
     * [
     * text => translated_text,
     * text_plural => [singular_txt, plural_txt],
     * ...
     * ]
     *
     * @param array $data
     * @param string $domain [optional] Defaults to current domain
     * @param string $locale [optional] Defaults to current locale
     * @return void
     */
    public function addTranslationData(array $data, $domain = null, $locale = null)
    {
        $domain = ((string)$domain) ?: $this->domain;
        $locale = ((string)$locale) ?: $this->locale;
        
        if (!isset($this->translations[$domain]))
            $this->translations[$domain] = [$locale => []];
        
        elseif (!isset($this->translations[$domain][$locale]))
            $this->translations[$domain][$locale] = [];
        
        $this->translations[$domain][$locale] = array_merge($this->translations[$domain][$locale], $data);
    }
}

