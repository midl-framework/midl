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

/**
 * I18n class is responsible to manage Locale and Translator class.
 * This class always assures to have a default and current locale
 * at any time. If user doesn't provide localization data
 * then it will fall back to default values which is locale "en-US".
 */
class I18n
{

    /**
     * Available locales
     *
     * @var Locale[]
     */
    protected $availableLocales = [];

    /**
     *
     * @var Translator
     */
    protected $translator;

    /**
     *
     * @var Locale
     */
    protected $defaultLocale;

    /**
     *
     * @var Locale
     */
    protected $currentLocale;

    /**
     * availableLocales: [["lang" => "en", "displayLang" => "English"
     * , "country" => "US", "displayCountry" => "United States"
     * , "default" => true
     * ], ...]
     *
     * @param array|string $availableLocales [optional]
     */
    public function __construct(array $availableLocales = [], Translator $translator = null)
    {
        $this->setAvailableLocales($availableLocales);
        
        if ($translator)
            $this->translator = $translator;
    }

    /**
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     *
     * @return void
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     *
     * @return Locale
     */
    public function getLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Sets current locale by language and country code.
     * At least one of language and country code must be specified.
     *
     * @param string $language [optional]
     * @param string $country [optional]
     * @return void
     */
    public function changeLocaleBy($language = null, $country = null)
    {
        if (!$language && !$country)
            return false;
        
        foreach ($this->availableLocales as $locale) {
            $langComp = !$language || $language === $locale->getLanguage();
            $countryComp = !$country || $country === $locale->getCountry();
            
            if ($langComp && $countryComp) {
                $this->setLocale($locale);
                
                if ($this->translator)
                    $this->translator->changeLocale($locale->getValue());
                
                return true;
            }
        }
        
        return false;
    }

    /**
     *
     * @return Locale
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     *
     * @return Locale[] Array of Locale objects
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }

    /**
     *
     * @param array $locales
     * @return void
     */
    protected function setAvailableLocales($locales)
    {
        if (!is_array($locales) || count($locales) < 1)
            $locales = null;
        else {
            $validLocales = [];
            
            foreach ($locales as $locale)
                if (!empty($locale["lang"]) && !empty($locale["country"]))
                    $validLocales[] = $locale;
            
            $locales = $validLocales;
        }
        
        if (!$locales)
            $locales = [["lang" => "en", "country" => "US", "default" => true]];
        
        foreach ($locales as $locale) {
            $localeInstance = new Locale($locale);
            $this->availableLocales[] = $localeInstance;
            
            if (!empty($locale["default"]))
                $this->defaultLocale = $localeInstance;
        }
        
        if (!$this->defaultLocale)
            $this->defaultLocale = $this->availableLocales[0];
        
        $this->setLocale($this->defaultLocale);
    }

    /**
     *
     * @param Locale $locale
     * @return void
     */
    protected function setLocale(Locale $locale)
    {
        $this->currentLocale = $locale;
    }
}
