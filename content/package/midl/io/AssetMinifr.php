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

use midl\io\AssetMinifier;

class AssetMinifr implements AssetMinifier
{

    /**
     *
     * @see \midl\io\AssetMinifier::minifyCSS()
     */
    public function minifyCSS($files, $rootPath = null)
    {
        if (is_string($files))
            $files = [$files];
        
        elseif (!is_array($files))
            throw new \InvalidArgumentException("Files must be string or array of strings.");
        
        if ($rootPath !== null) {
            if (!is_dir($rootPath))
                throw new \InvalidArgumentException("Root path of CSS files '$rootPath' doesn't exist.");
            
            $rootPath = rtrim($rootPath, "/\\") . DIRECTORY_SEPARATOR;
        }
        
        $contents = [];
        $options = ["docRoot" => "", "bubbleCssImports" => true];
        
        foreach ($files as $file) {
            
            if (!is_file($rootPath . $file))
                throw new \InvalidArgumentException("CSS file '$file' doesn't exist.");
            
            $content = file_get_contents($rootPath . $file);
            $options["currentDir"] = dirname($file);
            
            if (preg_match("/[\-\.]min\.css$/", $file)) {
                $contents[] = \Minify_CSS_UriRewriter::rewrite($content, $options['currentDir'], 
                    $options['docRoot']);
            } else
                $contents[] = \Minify_CSS::minify($content, $options);
        }
        
        $contents = implode(PHP_EOL, $contents);
        $imports = [];
        
        // @link https://github.com/mrclay/minify
        if (preg_match_all("/@import.*?;/", $contents, $imports))
            $contents = implode("", $imports[0]) . preg_replace("/@import.*?;/", "", $contents);
        
        return $contents;
    }

    /**
     *
     * @see \midl\io\AssetMinifier::minifyJS()
     */
    public function minifyJS($files, $rootPath = null)
    {
        if (is_string($files))
            $files = [$files];
        
        elseif (!is_array($files))
            throw new \InvalidArgumentException("Files must be string or array of strings.");
        
        if ($rootPath !== null) {
            if (!is_dir($rootPath))
                throw new \InvalidArgumentException("Root path of JavaScript files '$rootPath' doesn't exist.");
            
            $rootPath = rtrim($rootPath, "/\\") . DIRECTORY_SEPARATOR;
        }
        
        $JSqueeze = new \Patchwork\JSqueeze();
        $contents = [];
        
        foreach ($files as $file) {
            
            if (!is_file($rootPath . $file))
                throw new \InvalidArgumentException("JavaScript file '$file' doesn't exist.");
            
            $content = file_get_contents($rootPath . $file);
            
            if (preg_match("/[\-\.]min\.js$/", $file))
                $contents[] = $content;
            else
                $contents[] = $JSqueeze->squeeze($content);
        }
        
        return implode(PHP_EOL, $contents);
    }
}