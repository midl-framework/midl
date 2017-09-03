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

/**
 * CSS, JavaScript and Image manager
 */
class Assets
{

    /**
     *
     * @var string
     */
    protected $rootPath;

    /**
     *
     * @var AssetMinifier
     */
    protected $minifier;

    /**
     * Added files (CSS, JavaScript) for current page
     *
     * @var array
     */
    protected $addedAssets = [];

    /**
     * CSS and JavaScript files:
     * [
     * "css": ["basePath" => "", "minPath" => "", "files" => [["path" => "", "deps" => ""], ...]],
     * "js": ["basePath" => "", "minPath" => "", "files" => [["path" => "", "deps" => ""], ...]]
     * ]
     *
     * @var array
     */
    protected $assets = [];

    /**
     *
     * @var array
     */
    protected $imgs = [];

    /**
     *
     * @var string
     */
    protected $imgBasePath;

    /**
     *
     * @var array
     */
    protected $vars;

    /**
     *
     * @param string $rootPath Application root path
     * @param string $configFile Configuration file path relative to application root path
     * @param array $vars [optional] Custom variables to apply assets path, useful to load
     *        localised assets
     * @param AssetMinifier $minifier [optional]
     * @throws \InvalidArgumentException
     */
    public function __construct($rootPath, $configFile, array $vars = [], AssetMinifier $minifier = null)
    {
        if (!is_dir($rootPath))
            throw new \InvalidArgumentException("Application root path '$rootPath' doesn't exist.");
        
        $this->rootPath = rtrim($rootPath, "/\\") . DIRECTORY_SEPARATOR;
        $configFile = $this->rootPath . ltrim($configFile, "/\\");
        
        if (!is_file($configFile))
            throw new \InvalidArgumentException("Assets configuration file '$configFile' doesn't exist.");
        
        $this->vars = $vars;
        $this->minifier = $minifier;
        
        $this->parseConfig($configFile);
    }

    /**
     *
     * @return AssetMinifier
     */
    public function getMinifier()
    {
        return $this->minifier;
    }

    /**
     *
     * @param AssetMinifier $minifier
     * @return AssetMinifier
     */
    public function setMinifier(AssetMinifier $minifier = null)
    {
        $this->minifier = $minifier;
    }

    /**
     * Add CSS file to be added to the current page
     *
     * @param string $alias Alias for CSS file, only alphanumeric, - (dash) and . (dot) characters
     * @return void
     */
    public function addCSS($alias)
    {
        $this->addedAssets["css"][] = $alias;
    }

    /**
     *
     * @return array
     */
    public function getCSSPaths()
    {
        if (!isset($this->addedAssets["css"]))
            return [];
        
        $this->addedAssets["css"] = array_unique($this->addedAssets["css"]);
        
        return $this->getPaths("css", $this->addedAssets["css"]);
    }

    /**
     * Gets minified and cached CSS file path for added files
     *
     * @return string
     */
    public function getMinifiedCSSPath()
    {
        if (!isset($this->addedAssets["css"]) || !$this->minifier)
            return "";
        
        $this->addedAssets["css"] = array_unique($this->addedAssets["css"]);
        
        return $this->getMinifiedPath("css", $this->addedAssets["css"]);
    }

    /**
     * Add JavaScript file to be added to the current page
     *
     * @param string $alias Alias for JavaScript file, only alphanumeric, - (dash) and . (dot) characters
     * @return void
     */
    public function addJS($alias)
    {
        $this->addedAssets["js"][] = $alias;
    }

    /**
     *
     * @return array
     */
    public function getJSPaths()
    {
        if (!isset($this->addedAssets["js"]))
            return [];
        
        $this->addedAssets["js"] = array_unique($this->addedAssets["js"]);
        
        return $this->getPaths("js", $this->addedAssets["js"]);
    }

    /**
     * Gets minified and cached JavaScript file path for added files
     *
     * @return string
     */
    public function getMinifiedJSPath()
    {
        if (!isset($this->addedAssets["js"]) || !$this->minifier)
            return "";
        
        $this->addedAssets["js"] = array_unique($this->addedAssets["js"]);
        
        return $this->getMinifiedPath("js", $this->addedAssets["js"]);
    }

    /**
     * Gets relative image path
     *
     * @param string $img Image alias or path
     * @return string
     */
    public function getImgPath($img)
    {
        if (!is_string($img))
            return "";
        
        if (isset($this->imgs[$img]))
            $img = $this->imgs[$img];
        
        return $this->applyVars($this->imgBasePath . ltrim($img, "/\\"));
    }

    /**
     *
     * @param string $alias
     * @param string $filePath
     * @param array|string $dependencies
     * @return void
     */
    public function addCSSAlias($alias, $filePath, $dependencies = null)
    {
        $this->addAlias("css", $alias, $filePath, $dependencies);
    }

    /**
     *
     * @param string $alias
     * @param string $filePath
     * @param array|string $dependencies
     * @return void
     */
    public function addJSAlias($alias, $filePath, $dependencies = null)
    {
        $this->addAlias("js", $alias, $filePath, $dependencies);
    }

    /**
     *
     * @param string $type
     * @param array $aliases
     * @throws \RuntimeException
     * @return array
     */
    protected function getPaths($type, $aliases)
    {
        if (!isset($this->assets[$type]["basePath"]) || !is_dir($this->assets[$type]["basePath"]))
            return "";
        
        $paths = [];
        
        foreach ($aliases as $alias) {
            
            if (!empty($this->assets[$type]["files"][$alias]["deps"]))
                $this->resolveDeps($type, $alias, $paths);
            
            if (!empty($this->assets[$type]["files"][$alias]["path"])) {
                
                $path = $this->assets[$type]["basePath"] . $this->assets[$type]["files"][$alias]["path"];
                $path = $this->applyVars($path);
                
                $paths[$alias] = $path;
            }
        }
        
        return $paths;
    }

    /**
     *
     * @param string $type
     * @param array $aliases
     * @throws \RuntimeException
     * @return string
     */
    protected function getMinifiedPath($type, $aliases)
    {
        if (!isset($this->assets[$type]["basePath"]) || !is_dir($this->assets[$type]["basePath"]))
            return "";
        
        if (!isset($this->assets[$type]["minPath"]) || !is_dir($this->assets[$type]["minPath"]))
            return "";
        
        $files = [];
        $maxMtime = 0;
        
        foreach ($aliases as $alias) {
            
            if (!empty($this->assets[$type]["files"][$alias]["deps"]))
                $this->resolveDeps($type, $alias, $files, $maxMtime);
            
            if (!empty($this->assets[$type]["files"][$alias]["path"])) {
                
                $path = $this->assets[$type]["basePath"] . $this->assets[$type]["files"][$alias]["path"];
                $path = $this->applyVars($path);
                
                if (!is_file($this->rootPath . $path))
                    throw new \RuntimeException("Asset file '$path' doesn't exists.");
                
                $maxMtime = max($maxMtime, filemtime($this->rootPath . $path));
                $files[$alias] = $path;
            }
        }
        
        if (!$files)
            return "";
        
        $cacheKey = md5(json_encode(["mtime" => $maxMtime, "files" => $files]));
        $cachePath = $this->assets[$type]["minPath"] . "$cacheKey.$type";
        
        if (!file_exists($this->rootPath . $cachePath)) {
            if (!is_writable(dirname($this->rootPath . $cachePath)))
                throw new \RuntimeException("Asset cache file '$cachePath' is not writable");
            
            if (file_put_contents($this->rootPath . $cachePath, $this->minify($type, $files)) === false)
                throw new \RuntimeException("Unable to write asset cache file '$cachePath'");
        }
        
        return $cachePath;
    }

    /**
     *
     * @param string $type
     * @param string $alias
     * @param array &$files
     * @param int &$maxMtime
     * @param array $depsQueue
     * @return void
     * @throws \RuntimeException
     */
    protected function resolveDeps($type, $alias, &$files, &$maxMtime = null, array $depsQueue = [])
    {
        $depsQueue[$alias] = true;
        
        foreach ($this->assets[$type]["files"][$alias]["deps"] as $depAlias) {
            
            // Check dependency cycle
            if ($depAlias !== $alias && !isset($depsQueue[$depAlias])) {
                
                if (!empty($this->assets[$type]["files"][$depAlias]["deps"]))
                    $this->resolveDeps($type, $depAlias, $files, $maxMtime, $depsQueue);
                
                if (!empty($this->assets[$type]["files"][$depAlias]["path"])) {
                    
                    $path = $this->assets[$type]["basePath"] . $this->assets[$type]["files"][$depAlias]["path"];
                    $path = $this->applyVars($path);
                    
                    if (!is_file($this->rootPath . $path))
                        throw new \RuntimeException("Asset file '$path' doesn't exists.");
                    
                    $maxMtime = max($maxMtime, filemtime($this->rootPath . $path));
                    $files[$depAlias] = $path;
                }
            }
        }
    }

    /**
     *
     * @param string $type
     * @param array $assets
     * @return string
     */
    protected function minify($type, $assets)
    {
        if ($type === "css")
            return $this->minifier->minifyCSS($assets, $this->rootPath);
        elseif ($type === "js")
            return $this->minifier->minifyJS($assets, $this->rootPath);
    }

    /**
     *
     * @param string $configFile
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function parseConfig($configFile)
    {
        $options = include $configFile;
        
        if (!is_array($options))
            throw new \InvalidArgumentException("Assets configuration file '$configFile' is malformed.");
        
        if (isset($options["css"]) && is_array($options["css"]))
            $this->parseAsset("css", $options["css"]);
        
        if (isset($options["js"]) && is_array($options["js"]))
            $this->parseAsset("js", $options["js"]);
        
        if (isset($options["img"]) && is_array($options["img"])) {
            
            $img = $options["img"];
            
            if (isset($img["basePath"]))
                $this->imgBasePath = rtrim($img["basePath"], "/\\") . DIRECTORY_SEPARATOR;
            
            if (isset($img["files"]) && is_array($img["files"])) {
                
                foreach ($img["files"] as $alias => $imgPath)
                    $this->addImgAlias($alias, $imgPath);
            }
        }
    }

    /**
     *
     * @param string $type
     * @param array $asset
     * @return void
     */
    protected function parseAsset($type, $asset)
    {
        if (isset($asset["basePath"]))
            $this->assets[$type]["basePath"] = rtrim($asset["basePath"], "/\\") . DIRECTORY_SEPARATOR;
        
        if (isset($asset["minPath"]))
            $this->assets[$type]["minPath"] = rtrim($asset["minPath"], "/\\") . DIRECTORY_SEPARATOR;
        
        if (isset($asset["files"]) && is_array($asset["files"])) {
            foreach ($asset["files"] as $alias => $filePath) {
                
                if (isset($asset["dependencies"][$alias]))
                    $this->addAlias($type, $alias, $filePath, $asset["dependencies"][$alias]);
                else
                    $this->addAlias($type, $alias, $filePath);
            }
        }
    }

    /**
     *
     * @param string $type
     * @param string $alias
     * @param string $filePath
     * @param array|string $dependencies
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function addAlias($type, $alias, $filePath, $dependencies = null)
    {
        if (!ctype_alnum(str_replace(["-", "."], "", $alias)))
            throw new \InvalidArgumentException("Alias '$alias' must includes only alphanumeric characters.");
        
        if (is_string($dependencies))
            $dependencies = [$dependencies];
        
        elseif ($dependencies && !is_array($dependencies))
            throw new \InvalidArgumentException("Dependencies must be string or array of strings.");
        
        $this->assets[$type]["files"][$alias] = ["path" => $filePath, "deps" => $dependencies];
    }

    /**
     *
     * @param string $alias
     * @param string $imgPath
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function addImgAlias($alias, $imgPath)
    {
        if (!ctype_alnum(str_replace(["-", "."], "", $alias)))
            throw new \InvalidArgumentException("Alias '$alias' must includes only alphanumeric characters.");
        
        $this->imgs[$alias] = $imgPath;
    }

    /**
     *
     * @param string $path
     * @return string
     */
    protected function applyVars($path)
    {
        if (!$path)
            return $path;
        
        $vars = [];
        preg_match_all('/{([^}\[]+)(\[.+\])?}/', $path, $vars, PREG_OFFSET_CAPTURE);
        
        if (!$vars || !$vars[1])
            return $path;
        
        $formattedPath = $path;
        $offset = 0;
        
        foreach ($vars[1] as $index => $var) {
            
            $varName = $var[0];
            $varValue = "";
            
            if (isset($this->vars[$varName])) {
                
                if (is_callable($this->vars[$varName]))
                    $varValue = (string)call_user_func($this->vars[$varName], $this, $varName);
                
                elseif (is_scalar($this->vars[$varName]))
                    $varValue = (string)$this->vars[$varName];
            }
            
            $varNameLength = strlen($vars[0][$index][0]);
            $start = $offset + $vars[0][$index][1];
            
            $formattedPath = substr_replace($formattedPath, $varValue, $start, $varNameLength);
            
            $offset += strlen($varValue) - $varNameLength;
        }
        
        return $formattedPath;
    }
}