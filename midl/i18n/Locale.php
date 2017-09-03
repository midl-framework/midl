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

class Locale
{

    /**
     * Language code, e.g.: "en"
     *
     * @var string
     */
    protected $language;

    /**
     * Language name to display to user, e.g.: "English"
     *
     * @var string
     */
    protected $displayLanguage;

    /**
     * Country code, e.g.: "US"
     *
     * @var string
     */
    protected $country;

    /**
     * Country name to display to user, e.g.: "United States"
     *
     * @var string
     */
    protected $displayCountry;

    /**
     * Data array structure:
     * ["lang" => "", "displayLang" => "", "country" => "", "displayCountry" => ""]
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data["lang"]))
            $this->language = $data["lang"];
        
        if (isset($data["displayLang"]))
            $this->displayLanguage = $data["displayLang"];
        
        if (isset($data["country"]))
            $this->country = $data["country"];
        
        if (isset($data["displayCountry"]))
            $this->displayCountry = $data["displayCountry"];
    }

    /**
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     *
     * @return string
     */
    public function getDisplayLanguage()
    {
        return $this->displayLanguage;
    }

    /**
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     *
     * @return string
     */
    public function getDisplayCountry()
    {
        return $this->displayCountry;
    }

    /**
     * Gets locale value in format of "language-country", e.g.: "en-US"
     *
     * @return string
     */
    public function getValue()
    {
        return $this->language . "-" . $this->country;
    }
}
