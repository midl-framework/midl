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

use midl\package\Pactory;

class TranslateAdapterGettext implements TranslateAdapter
{

    /**
     * Absolute path to translation data directory to read.
     * Translations data will be loaded from domain directories in following pattern:
     * /path/to/translation/dir/{domain}/{locale}.mo
     *
     * @var string
     */
    protected $translationPath;

    /**
     *
     * @var \Zend_Translate_Adapter_Gettext
     */
    private $zendAdapter;

    // TODO TranslateAdapterGettext coupled to zend package, problem?
    /**
     *
     * @param string $transPath Full path of ranslation directory
     * @throws \InvalidArgumentException
     */
    public function __construct($transPath)
    {
        $transPath = rtrim((string)$transPath, "/\\") . DIRECTORY_SEPARATOR;
        
        if (!is_dir($transPath))
            throw new \InvalidArgumentException("Translation directory '$transPath' doesn't exist.");
        
        $this->translationPath = $transPath;
        
        $this->zendAdapter = Pactory::newSingleton("zend/Zend_Translate_Adapter_Gettext.php");
    }

    /**
     * Load translation data from mo file
     *
     * @see TranslateAdapter::loadTranslationData()
     */
    public function loadTranslationData($domain, $locale)
    {
        $filePath = "{$this->translationPath}$domain/$locale.mo";
        
        if (!is_file($filePath))
            throw new \RuntimeException("Translation data file '$filePath' doesn't exists.");
        
        $data = $this->zendAdapter->loadTranslationData($filePath, $locale);
        
        if (is_array($data) && isset($data[$locale]))
            return $data[$locale];
        
        throw new \RuntimeException("Failed to load translation data, error: '$data'");
    }
}