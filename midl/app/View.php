<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\app;

class View
{

    /**
     *
     * @var Controller
     */
    protected $controller;

    /**
     *
     * @var string
     */
    protected $rootPath;

    /**
     * HTML content that will be added to head tag
     *
     * @var array
     */
    protected $headContent = [];

    /**
     * JavaScript codes to be added to body of html
     *
     * @var array
     */
    protected $scriptContent = [];

    /**
     *
     * @var bool
     */
    protected $scriptBufferStatus = false;

    /**
     *
     * @var string
     */
    protected $themeFile;

    /**
     * Main view file for this page
     *
     * @var string
     */
    protected $viewFile;

    /**
     *
     * @param Controller $controller
     * @param string $rootPath Root directory for view files
     * @throws \RuntimeException
     */
    public function __construct(Controller $controller, $rootPath)
    {
        if (!is_dir($rootPath))
            throw new \RuntimeException("Views root directory '$rootPath' doesn't exist.");
        
        $this->controller = $controller;
        $this->rootPath = rtrim($rootPath, "/\\") . DIRECTORY_SEPARATOR;
    }

    /**
     *
     * @return string|null
     */
    public function getContent()
    {
        if ($this->themeFile)
            return $this->getViewContent($this->themeFile);
        
        if ($this->viewFile)
            return $this->getViewContent($this->viewFile);
        
        return null;
    }

    /**
     *
     * @return void
     */
    public function cleanContent()
    {
        ob_end_clean();
        
        $this->themeFile = null;
        $this->viewFile = null;
    }

    /**
     *
     * @return void
     */
    public function includeBody()
    {
        if ($this->viewFile)
            $this->includeView($this->viewFile);
    }

    /**
     *
     * @param string $viewFile
     * @param bool $relativeToCaller [optional] Whether view file path is relative to caller
     *        file's directory or relative to root directory of view files, defaults to false
     *        which emans relative to root directory of views
     * @return mixed
     */
    public function includeView($viewFile, $relativeToCaller = false)
    {
        if ($relativeToCaller) {
            $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            
            if (isset($backTrace[0]["file"]))
                $filePath = dirname($backTrace[0]["file"]) . DIRECTORY_SEPARATOR . $viewFile;
        } else
            $filePath = $this->rootPath . $viewFile;
        
        if (!is_file($filePath))
            throw new \RuntimeException("View file '$viewFile' doesn't exist.");
        
        return include $filePath;
    }

    /**
     *
     * @return string
     */
    public function getThemeFile()
    {
        return $this->themeFile;
    }

    /**
     *
     * @param string $themeFile
     * @return void
     */
    public function setThemeFile($themeFile)
    {
        $this->themeFile = $themeFile;
    }

    /**
     *
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    /**
     *
     * @param string $viewFile
     * @return void
     */
    public function setViewFile($viewFile)
    {
        $this->viewFile = $viewFile;
    }

    /**
     *
     * @return string
     */
    public function getHead()
    {
        return implode(PHP_EOL, $this->headContent);
    }

    /**
     *
     * @param string $content
     * @return void
     */
    public function addHead($content)
    {
        if (is_string($content))
            $this->headContent[] = $content;
    }

    /**
     *
     * @return string
     */
    public function getScript()
    {
        return implode(PHP_EOL, $this->scriptContent);
    }

    /**
     *
     * @param string $script
     * @return void
     */
    public function addScript($script)
    {
        if (is_string($script))
            $this->scriptContent[] = $script;
    }

    /**
     * TODO try catch errors
     *
     * @return bool
     */
    public function startScriptBuffer()
    {
        $this->scriptBufferStatus = ob_start();
        
        return $this->scriptBufferStatus;
    }

    /**
     *
     * @return void
     */
    public function endScriptBuffer()
    {
        if ($this->scriptBufferStatus)
            $this->scriptContent[] = ob_get_clean();
        
        $this->scriptBufferStatus = false;
    }

    /**
     *
     * @param string $viewFile View file path relative to view files root path
     * @return string
     * @throws \RuntimeException
     */
    protected function getViewContent($viewFile)
    {
        $filePath = $this->rootPath . $viewFile;
        
        if (!is_file($filePath))
            throw new \RuntimeException("View file '$viewFile' doesn't exist.");
        
        try {
            ob_start();
            
            include $filePath;
            
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
}