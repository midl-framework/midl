<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\io;

interface AssetMinifier
{

    /**
     *
     * @param array|string $files List of files to be minified and combined, or single file path
     * @param string $rootPath [optional] Base path for files
     * @return string Minified content
     * @throws \InvalidArgumentException
     */
    public function minifyCSS($files, $rootPath = null);

    /**
     *
     * @param array|string $files List of files to be minified and combined, or single file path
     * @param string $rootPath [optional] Base path for files
     * @return string Minified content
     * @throws \InvalidArgumentException
     */
    public function minifyJS($files, $rootPath = null);
}