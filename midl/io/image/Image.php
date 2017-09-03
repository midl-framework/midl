<?php
namespace midl\core\file\image;

use midl\log\Logger;

/**
 * Image class is a utility class to do some simple image operations, e.g.
 * resize, crop etc. Multiple images can be resized with one call, on failure
 * a safe rollback will be called which will delete all saved images.
 *
 * @author Abdulhalim Kara
 */
class Image
{

    /**
     * Fit image options when resizing
     * Whether or not to apply the given width and height values as boundary values
     * when scaling image proportionally.
     * e.g. resize 300x300 image to 100x150
     * FIT_USING_FILL -> results 100x150 (original size is 100x100,
     * 50px height will be filled with white background
     * (25px added to both up and down))
     *
     * @var int
     */
    const FIT_USING_FILL = 1;

    /**
     * Results 100x150, original size is 150x150, 50px width will be cropped
     * (25px from both left and right)
     *
     * @var int
     */
    const FIT_USING_CROP = 2;

    /**
     * Results 100x150 (no resizing will be used, just copy specified pixels)
     *
     * @var int
     */
    const FIT_NONE = 0;

    /**
     * Max allowed dimension for width/height in pixel
     *
     * @var int
     */
    const MAX_DIMENSION = 6000;

    /**
     * Max allowed memory usage in bytes (256MB)
     *
     * @var int
     */
    const MAX_MEMORY_USAGE = 536870912;

    /**
     * Supported images
     *
     * @var array
     */
    private $supportedImages = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

    /**
     * Supported extensions
     *
     * @var array
     */
    private $supportedExtensions = array("gif", "jpeg", "jpg", "png");

    /**
     * Set image path dimensions
     * avatar.jpg -> avatar50x50.jpg
     *
     * @param string $path Image path
     * @param string $dimensions Image dimensions
     * @return string New path with dimensions added
     */
    public function setPathSize($path, $dimensions)
    {
        $dotPos = strrpos($path, ".");
        $path = substr($path, 0, $dotPos) . $dimensions . substr($path, $dotPos);
        
        return $path;
    }

    /**
     * Resize and save image either to a new location or overwrite same image
     * If source image width or height is greater than 800px then
     * only downsizing is available because of cpu and memory limitations.
     * Multiple image resize is supported. To resize an image to multiple sizes send
     * paramater values in array.
     *
     * For $width and $height parameters:
     * 0 means the parameter will be calculated automatically to have the same aspect ratio with source image.
     * They can be an array that has min and/or max values to limit the automatically calculated value.
     * Also can be an array which contains multiple dimensions to save multiple images with single function call.
     *
     * @param string $srcPath Source image path
     * @param int|array $width Destination image width
     * @param int|array $height Destination image height
     * @param string $dstPath [optional] Destination image path, defaults to "".
     *        If this is a directory, file will be saved with same name as source image in the given directory.
     *        If this is null then source image's resource(type of GD) will be returned.
     *        If this is "" then source image will be resized and overwritten.
     * @param array $options [optional]
     *        options[fit] Fit destination image dimensions to given width and height values
     *        by using one of FIT_USING_FILL, FIT_USING_CROP, or FIT_NONE algorithm.
     *        To skip this parameter use null, defaults to null (identical to FIT_USING_CROP)
     *       
     *        options[deleteSource] Whether or not to remove source image, defaults to false
     *       
     *        options[quality] JPG quality when image saved, defaults to 100
     *       
     * @return bool|\resource True on success, resource(type of GD) when $dstPath is null, false on failure
     */
    public function resize($srcPath, $width, $height, $dstPath = "", $options = null)
    {
        if (!is_file($srcPath)) {
            Logger::log("There is no such source image.", Logger::MT_SYSTEM, 1);
            return false;
        }
        
        $exif = @exif_read_data($srcPath);
        
        if ($exif !== false) {
            $orientation = (int)$exif['Orientation'];
            $imgInfo = getimagesize($srcPath);
            
            if (($orientation === 3 || $orientation === 6 || $orientation === 8) &&
                 ((int)$imgInfo[2]) === IMAGETYPE_JPEG) {
                $srcImage = imagecreatefromjpeg($srcPath);
                
                if ($orientation === 3)
                    $srcImage = imagerotate($srcImage, 180, 0);
                elseif ($orientation === 6)
                    $srcImage = imagerotate($srcImage, 270, 0);
                else
                    $srcImage = imagerotate($srcImage, 90, 0);
                
                imagejpeg($srcImage, $srcPath);
                imagedestroy($srcImage);
            }
        }
        
        $imgInfo = getimagesize($srcPath);
        $srcType = (int)$imgInfo[2];
        $srcWidth = $imgInfo[0];
        $srcHeight = $imgInfo[1];
        
        if (!in_array($srcType, $this->supportedImages, true)) {
            $ext = image_type_to_extension($srcType);
            Logger::log("Source image extension($ext) is not allowed.", Logger::MT_USER, 1);
            
            return false;
        } elseif ($srcWidth == 0 || $srcHeight == 0) {
            Logger::log("Could not be able to determine source image size.", Logger::MT_USER, 1);
            return false;
        }
        
        $options = $options ?: [];
        $options["fit"] = $options["fit"] === null ? self::FIT_USING_CROP : $options["fit"];
        $options["deleteSource"] = $options["deleteSource"] === true;
        $options["quality"] = $options["quality"] > 0 && $options["quality"] <= 100 ? $options["quality"] : 100;
        
        $dstPath = $dstPath === "" ? $srcPath : $dstPath;
        $fit = $options["fit"];
        
        if ($srcWidth == $width && $srcHeight == $height && is_string($dstPath) &&
             pathinfo($srcPath) === pathinfo($dstPath))
            return true;
        
        try {
            $memoryLimitChanged = $this->setMemoryLimit($imgInfo, $srcPath);
        } catch (Exception $e) {
            return false;
        }
        
        if ($srcType == 1)
            $srcImage = imagecreatefromgif($srcPath);
        elseif ($srcType == 3)
            $srcImage = imagecreatefrompng($srcPath);
        else
            $srcImage = imagecreatefromjpeg($srcPath);
        
        if (!$srcImage) {
            Logger::log("Failed to generate image: $srcPath", Logger::MT_SYSTEM, 1);
            return false;
        }
        
        if (!is_array($width) || isset($width['min']) || isset($width['max']))
            $width = array($width);
        
        if (!is_array($height) || isset($height['min']) || isset($height['max']))
            $height = array($height);
        
        if (!is_array($dstPath))
            $dstPath = array($dstPath);
        
        if (!is_array($fit))
            $fit = array($fit);
        
        $savedFiles = [];
        $length = count($dstPaths = $dstPath);
        
        for ($i = 0; $i < $length; $i++) {
            if ($dstPath = (string)$dstPaths[$i]) {
                if (is_dir($dstPath)) {
                    $dstPath .= pathinfo($srcPath, PATHINFO_BASENAME);
                    $dstType = $srcType;
                } else {
                    $pos = strrpos($dstPath, ".");
                    
                    $dstExt = $pos !== false ? strtolower(substr($dstPath, $pos + 1)) : null;
                    
                    if (!in_array($dstExt, $this->supportedExtensions, true)) {
                        
                        Logger::log("Destination image extension(.$dstExt) is not allowed.", Logger::MT_USER, 
                            1);
                        
                        $this->rollbackResizing($srcImage, $dstImage, $savedFiles);
                        
                        return false;
                    }
                    
                    $dstType = $dstExt == "gif" ? 1 : ($dstExt == "png" ? 3 : 2);
                }
            } else {
                $dstType = null;
            }
            
            $w = isset($width[$i]) ? $width[$i] : $width[0];
            $h = isset($height[$i]) ? $height[$i] : $height[0];
            $f = isset($fit[$i]) ? $fit[$i] : $fit[0];
            
            $dimensions = $this->calculateDimensions($srcWidth, $srcHeight, $w, $h, $f);
            
            if ($dimensions === false) {
                $this->rollbackResizing($srcImage, $dstImage, $savedFiles);
                return false;
            }
            
            $srcWidth = $dimensions['srcW'];
            $srcHeight = $dimensions['srcH'];
            $dstWidth = $dimensions['dstW'];
            $dstHeight = $dimensions['dstH'];
            $resampleW = $dimensions['resW'];
            $resampleH = $dimensions['resH'];
            $srcX = $dimensions['srcX'];
            $srcY = $dimensions['srcY'];
            $resampleX = $dimensions['resX'];
            $resampleY = $dimensions['resY'];
            
            if ($srcWidth == $dstWidth && $srcHeight == $dstHeight && $dstType &&
                 pathinfo($srcPath) !== pathinfo($dstPath)) {
                if ($result = copy($srcPath, $dstPath)) {
                    continue;
                } else {
                    $this->rollbackResizing($srcImage, $dstImage, $savedFiles);
                    return false;
                }
            }
            
            if ($dstWidth / $srcWidth > 5 || $dstHeight / $srcHeight > 5 || (($srcWidth > 800 ||
                 $srcHeight > 800) && ($dstWidth > $srcWidth || $dstHeight > $srcHeight))) {
                Logger::log("Only downsizing is allowed.", Logger::MT_USER, 1);
                $this->rollbackResizing($srcImage, $dstImage, $savedFiles);
                
                return false;
            }
            
            if ($dstImage)
                imagedestroy($dstImage);
            
            $dstImage = imagecreatetruecolor($dstWidth, $dstHeight);
            $bgColor = imagecolorallocate($dstImage, 255, 255, 255);
            imagefill($dstImage, 0, 0, $bgColor);
            
            imagecopyresampled($dstImage, $srcImage, $resampleX, $resampleY, $srcX, $srcY, $resampleW, 
                $resampleH, $srcWidth, $srcHeight);
            
            if ($dstType) {
                if ($dstType == 1)
                    $result = imagegif($dstImage, $dstPath);
                elseif ($dstType == 3)
                    $result = imagepng($dstImage, $dstPath);
                else
                    $result = imagejpeg($dstImage, $dstPath, $options["quality"]);
                
                $savedFiles[] = $dstPath;
            } else {
                $result = $dstImage;
            }
            
            if (!$result)
                break;
        }
        
        if ($result && $options["deleteSource"])
            unlink($srcPath);
        
        if ($srcImage)
            imagedestroy($srcImage);
        
        if ($dstImage && $dstImage !== $result)
            imagedestroy($dstImage);
        
        if ($memoryLimitChanged)
            ini_restore('memory_limit');
        
        return $result;
    }

    /**
     * Delete all previously saved files and destroy resources(type of GD)
     *
     * @param Resource $srcImage Source image resource(type of GD)
     * @param Resource $dstImage Destination image resource(type of GD)
     * @param array $savedFiles Resized and saved image files
     * @return void
     */
    private function rollbackResizing($srcImage, $dstImage, $savedFiles)
    {
        imagedestroy($srcImage);
        
        if ($dstImage)
            imagedestroy($dstImage);
        
        foreach ($savedFiles as $filename)
            if (is_file($filename))
                unlink($filename);
    }

    /**
     * Calculate dimensions for resizing.
     *
     * @param int $srcWidth Source image width
     * @param int $srcHeightSource image height
     * @param int|array $dstWidth Destination image width
     * @param int|array $dstHeight Destination image width
     * @param int $fit Fit destination image dimensions to given width and height values
     * @return array|bool Calculated dimensions associative array, false if both with and height is zero
     */
    private function calculateDimensions($srcWidth, $srcHeight, $width, $height, $fit)
    {
        $dstWidth = $resampleW = (!is_array($width) ? (int)$width : 0);
        $dstHeight = $resampleH = (!is_array($height) ? (int)$height : 0);
        $srcX = $srcY = $resampleX = $resampleY = 0;
        
        if ($dstWidth == 0 && $dstHeight == 0) {
            if (!$width['min'] || !$width['max']) {
                if ($width['min'])
                    $dstWidth = $resampleW = $srcWidth < $width['min'] ? $width['min'] : $srcWidth;
                
                elseif ($width['max'])
                    $dstWidth = $resampleW = $srcWidth > $width['max'] ? $width['max'] : $srcWidth;
            } elseif (!$height['min'] || !$height['max']) {
                if ($height['min'])
                    $dstHeight = $resampleH = $srcHeight < $height['min'] ? $height['min'] : $srcHeight;
                
                elseif ($height['max'])
                    $dstHeight = $resampleH = $srcHeight > $height['max'] ? $height['max'] : $srcHeight;
            }
        }
        
        if ($dstWidth == 0 || $dstHeight == 0) {
            if ($dstHeight > 0) {
                $dstWidth = $resampleW = min(
                    array($srcWidth * ($dstHeight / $srcHeight), self::MAX_DIMENSION));
                
                if ($width['min'] && $dstWidth < ($int = (int)$width['min'])) {
                    $resampleX = ($int - $dstWidth) / 2;
                    $dstWidth = $int;
                } elseif ($width['max'] && $dstWidth > ($int = (int)$width['max'])) {
                    $srcX = ($crop = (1 - $int / $dstWidth) * $srcWidth) / 2;
                    $srcWidth -= $crop;
                    $dstWidth = $int;
                }
            } elseif ($dstWidth > 0) {
                $dstHeight = $resampleH = min(
                    array($srcHeight * ($dstWidth / $srcWidth), self::MAX_DIMENSION));
                
                if ($height['min'] && $dstHeight < ($int = (int)$height['min'])) {
                    $resampleY = ($int - $dstHeight) / 2;
                    $dstHeight = $int;
                } elseif ($height['max'] && $dstHeight > ($int = (int)$height['max'])) {
                    $srcY = ($crop = (1 - $int / $dstHeight) * $srcHeight) / 2;
                    $srcHeight -= $crop;
                    $dstHeight = $int;
                }
            } else // if ($dstWidth == 0 && $dstHeight == 0)
{
                Logger::log("Both image width and height can't be zero.", Logger::MT_SYSTEM, 2);
                return false;
            }
        } elseif ($fit) {
            $wRatio = $dstWidth / $srcWidth;
            $hRatio = $dstHeight / $srcHeight;
            
            if ($fit == self::FIT_USING_FILL) {
                $ratio = $wRatio < $hRatio ? $wRatio : $hRatio;
                $resampleW = $srcWidth * $ratio;
                $resampleH = $srcHeight * $ratio;
                $resampleX = ($dstWidth - $resampleW) / 2;
                $resampleY = ($dstHeight - $resampleH) / 2;
            } elseif ($fit == self::FIT_USING_CROP) {
                $ratio = $wRatio > $hRatio ? $wRatio : $hRatio;
                $srcX = ($srcWidth - $dstWidth / $ratio) / 2;
                $srcY = ($srcHeight - $dstHeight / $ratio) / 2;
                $srcWidth = $dstWidth / $ratio;
                $srcHeight = $dstHeight / $ratio;
            }
        }
        
        $dstWidth = $dstWidth < 1 ? 1 : $dstWidth;
        $dstHeight = $dstHeight < 1 ? 1 : $dstHeight;
        
        return array('srcW' => $srcWidth, 'srcH' => $srcHeight, 'dstW' => $dstWidth, 'dstH' => $dstHeight, 
            'resW' => $resampleW, 'resH' => $resampleH, 'srcX' => $srcX, 'srcY' => $srcY, 'resX' => $resampleX, 
            'resY' => $resampleY);
    }

    /**
     * Increase memory usage limit if necessary
     *
     * @param array $imgInfo Image info retrieved from getimagesize() function
     * @param string $path Image path
     * @throws Exception If required emory limit exceeds max allowed limit for an image
     * @return bool True on success memory limit increasing, false for no changes
     */
    private function setMemoryLimit($imgInfo, $path)
    {
        if ($imgInfo['channels']) // for jpeg and gif
            $memoryNeeded = memory_get_usage() + round(
                ($imgInfo[0] * $imgInfo[1] * $imgInfo['bits'] * $imgInfo['channels'] / 8 + 65536) * 1.65);
        else // for png
            $memoryNeeded = memory_get_usage() + round($imgInfo[0] * $imgInfo[1] * $imgInfo['bits']);
        
        if ($memoryNeeded > self::MAX_MEMORY_USAGE)
            throw new Exception(
                "Required memory limit ($memoryNeeded) exceeds max allowed limit. Image: $path");
        
        $memoryLimit = (int)ini_get('memory_limit');
        
        if ($memoryNeeded > $memoryLimit * 1048576) // 1048576 = 1024*1024
{
            $newLimit = ($memoryLimit + ceil(($memoryNeeded - $memoryLimit * 1048576) / 1048576)) . "M";
            ini_set('memory_limit', $newLimit);
            
            Logger::log("Memory limit increased to: $newLimit, for image: $path", Logger::MT_SYSTEM, 2);
            
            return true;
        }
        
        return false;
    }

    /**
     * Add watermark image
     *
     * @param string $imagePath Path to image
     * @param string $watermarkPath [Optional] Path to watermark image
     * @param bool $jpgQuality [Optional] Output jpg quality
     * @return bool True on success, false on failure
     */
    public function watermarkImage($imagePath, $watermarkPath = "f/def/img/watermark.png", $jpgQuality = 100)
    {
        if (!is_file($imagePath)) {
            Logger::log("There is no such source image.", Logger::MT_SYSTEM, 1);
            return false;
        }
        
        $imgInfo = getimagesize($imagePath);
        $width = $imgInfo[0];
        $height = $imgInfo[1];
        $imgType = (int)$imgInfo[2];
        
        if (!in_array($imgType, $this->supportedImages, true)) {
            Logger::log("Source image extension(" . image_type_to_extension($imgType) . ") is not allowed.", 
                Logger::MT_USER, 1);
            return false;
        } elseif ($width == 0 || $height == 0) {
            Logger::log("Could not be able to properly determine source image size.", Logger::MT_USER, 1);
            return false;
        }
        
        try {
            $memoryLimitChanged = $this->setMemoryLimit($imgInfo, $imagePath);
        } catch (Exception $e) {
            return false;
        }
        
        if ($imgType == 1)
            $image = imagecreatefromgif($imagePath);
        elseif ($imgType == 3)
            $image = imagecreatefrompng($imagePath);
        else
            $image = imagecreatefromjpeg($imagePath);
        
        if (!$image) {
            Logger::log("Failed to generate image: $imagePath", Logger::MT_SYSTEM, 1);
            return false;
        }
        
        $wmrkInfo = getimagesize($watermarkPath);
        $wmrkWidth = $wmrkInfo[0];
        $wmrkHeight = $wmrkInfo[1];
        $watermark = imagecreatefrompng($watermarkPath);
        
        $rows = (int)($height / $wmrkHeight) + 1;
        $cols = (int)($width / $wmrkWidth) + 1;
        
        for ($i = 0; $i < $rows; $i++)
            for ($j = 0; $j < $cols; $j++)
                imagecopy($image, $watermark, $j * $wmrkWidth, $i * $wmrkHeight, 0, 0, $wmrkWidth, 
                    $wmrkHeight);
        
        if ($imgType == 1)
            $result = imagegif($image, $imagePath);
        elseif ($imgType == 3)
            $result = imagepng($image, $imagePath);
        else
            $result = imagejpeg($image, $imagePath, $jpgQuality);
        
        if ($image)
            imagedestroy($image);
        if ($watermark)
            imagedestroy($watermark);
        
        if ($memoryLimitChanged)
            ini_restore('memory_limit');
        
        return $result;
    }

    /**
     * Check whether image type is supported or not
     *
     * @param string $imagePath Path to image
     * @return bool True on success, false on failure
     */
    public function isSupportedImage($imagePath)
    {
        if (!is_file($imagePath))
            return false;
        
        $imageInfo = getimagesize($imagePath);
        
        if (!in_array((int)$imageInfo[2], $this->supportedImages, true))
            return false;
        
        return true;
    }
}
