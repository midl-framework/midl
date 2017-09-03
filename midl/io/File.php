<?php
namespace midl\core\File;

use midl\core\Log\Logger;
use midl\core\String;

/**
 * File operations utility class, e.g.
 * rename,
 * generate a unique name under a folder.
 *
 * @author Abdulhalim Kara
 */
class File
{
    // Constants
    
    // Public variables
    
    // Private variables
    
    /**
     * Get unique file name for the given directory.
     *
     * @param string $extension File extension starting with a dot, e.g. ".jpg"
     * @param string $dir Directory to save uploaded file
     * @param bool $withoutExtension [optional] Defaults to false
     * @return string Unique file name
     */
    public static function getUniqueFileName($extension, $dir, $withoutExtension = false)
    {
        $name = hash("crc32", (string)(microtime(true) . session_id() . $extension));
        
        while (is_file($dir . $name . $extension))
            $name = hash("crc32", (string)(microtime(true) . rand() . $name . $extension));
        
        return $name . ($withoutExtension ? "" : $extension);
    }

    /**
     * Generate unique file name for the given directory.
     *
     * @param string $fileName File name
     * @param string $dir Directory to save uploaded file
     * @param bool $withoutExtension [optional] Defaults to false
     * @return string Unique file name
     */
    public static function generateUniqueFileName($fileName, $dir, $withoutExtension = false)
    {
        $pathInfo = pathinfo($fileName);
        $name = String::slugify($pathInfo["filename"]);
        $ext = "." . $pathInfo["extension"];
        $i = 0;
        
        if (!$name)
            $name = hash("crc32", (string)(microtime(true) . session_id() . $ext));
        
        while (is_file($dir . $name . ($i ? "-$i" : "") . $ext))
            $i++;
        
        return $name . ($i ? "-$i" : "") . ($withoutExtension ? "" : $ext);
    }

    /**
     * Remove directory and its content
     * Original by WordPress ms.php file
     *
     * @param string $dir Directory name
     * @return bool True on success, false on failure
     */
    public static function removeDir($dir)
    {
        if (is_file($dir))
            return unlink($dir);
        
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $stack = array($dir);
        $index = 0;
        
        while ($index < count($stack)) {
            $dir = $stack[$index];
            
            $dh = @opendir($dir);
            
            if ($dh) {
                while (($file = @readdir($dh)) !== false) {
                    if ($file === "." || $file === "..")
                        continue;
                    
                    $path = "$dir/$file";
                    
                    if (@is_dir($path))
                        $stack[] = $path;
                    
                    elseif (@is_file($path))
                        @unlink($path);
                }
                
                @closedir($dh);
            }
            
            $index++;
        }
        
        $stack = array_reverse($stack);
        
        foreach ($stack as $dir)
            @rmdir($dir);
        
        return true;
    }

    /**
     * Extract source file to new destination
     *
     * @param string $source Source file path
     * @param string $destination Location where to extract files
     * @param bool $delete [Optional] Whether or not to delete source file after successful extraction, defaults to false
     * @return bool True on success, false on failure
     */
    public static function extractTo($source, $destination, $delete = false)
    {
        $za = new \ZipArchive();
        
        if ($za->open($source) === true) {
            if ($za->extractTo($destination)) {
                $za->close();
                
                if ($delete)
                    @unlink($source);
                
                return true;
            } else
                Logger::log("Failed to extract, source: $source, destination: $destination");
        } else
            Logger::log("Failed to open file for extraction, source: $source, destination: $destination");
        
        return false;
    }

    /**
     * Get formatted file size
     *
     * @param string $file File path
     * @return string File size
     */
    public static function getFormattedFileSize($file)
    {
        $fileSize = is_int($file) ? $file : filesize($file);
        
        return $fileSize < 1024 ? t_spfN("one byte", '%1$d bytes', $fileSize) : ($fileSize < 1048576 ? round(
            $fileSize / 1024, 1) . " KB" : ($fileSize < 1073741824 ? round($fileSize / 1048576, 1) . " MB" : round(
            $fileSize / 1073741824, 1) . " GB"));
    }

    /**
     * Clear file name
     *
     * @param string $fileName File name
     * @param int $maxLength [optional] Max length of file name, defaults to 0 (no limit)
     * @return string File name
     */
    public static function clearFileName($fileName, $maxLength = 0)
    {
        $fileName = String::replaceTRChars($fileName);
        
        return $maxLength > 0 && strlen($fileName) > $maxLength ? substr($fileName, -$maxLength) : $fileName;
    }
}