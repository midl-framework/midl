<?php
use midl\core\file\image\Image;
use midl\loader\Autoloader;

/**
 * This tiny service will resize images and save for given width and height.
 * Width and height can be set at the end of filename before extension e.g.
 * image-100x100.jpg, another-image-180x200.png
 *
 * It will work only for available sizes.
 * If requested image size is not available then it will check for original image,
 * if original image exists it will create a new resized image and send it to the client.
 * So next time when same image size is requested it will be served from server
 * without coming to this service.
 *
 * Example:
 * If requested image is /path/to/image-WxH.jpg then it will check for /path/to/image.jpg
 * If image exists then it will resize, save it and send the image to the client.
 *
 * @author Abdulhalim Kara
 */

error_reporting(0);

$availableSizes = array("-100x100", "-180x200");

$ROOT_DIR = substr(__FILE__, 0, -strlen($_SERVER["SCRIPT_NAME"]));

$uri = $_SERVER["REQUEST_URI"];
if (($qmPos = strpos($uri, "?")) !== false)
    $uri = substr($uri, 0, $qmPos);

$pathInfo = pathinfo(rawurldecode($uri));

$imgDir = $ROOT_DIR . rtrim($pathInfo["dirname"], "./") . "/";
$fileName = $pathInfo["filename"];

$size = strrchr($fileName, "-");
$origImage = $imgDir . substr($fileName, 0, -strlen($size)) . ".{$pathInfo["extension"]}";

if (in_array($size, $availableSizes, true) && @is_file($origImage)) {
    
    $size = explode("x", substr($size, 1));
    $width = (int)$size[0];
    $height = (int)$size[1];
    $destPath = $imgDir . $pathInfo["basename"];
    
    require_once dirname(dirname(__DIR__)) . "/midl/loader/Autoloader.php";
    
    new Autoloader();
    $img = new Image();
    
    $result = $img->resize($origImage, $width, $height, $destPath);
    
    if ($result) {
        
        header("Content-Type: image/{$pathInfo["extension"]}");
        readfile($destPath);
        exit();
    }
}

header("HTTP/1.0 404 Not Found");
