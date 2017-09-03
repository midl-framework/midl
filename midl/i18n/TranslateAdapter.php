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

interface TranslateAdapter
{

    /**
     * Loads translation data from translations source e.g., .mo file or database
     * Sample translation data:
     * [ msg => translated_msg, msg_plural => [translated_singular_msg, translated_plural_msg], ...]
     *
     * @param string $domain Text domain
     * @param string $locale Locale identifier, e.g. "en-US"
     * @return array Array of translation data
     * @throws \RuntimeException If fails to load translation data
     */
    public function loadTranslationData($domain, $locale);
}
