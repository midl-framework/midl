<?php
namespace midl\core\File;

use midl\app\App;
use midl\core\Log\Logger;
use midl\core\String;

/**
 * Upload operations are done via this utiliy class
 *
 * @author Abdulhalim Kara
 */
class Upload
{
    // Constants
    /**
     * Max file size
     *
     * @var int
     */
    const MAX_SIZE = 104857600;
    // 100 * 1024 * 1024 bytes = 100MB
    
    // Public variables
    
    // Private variables
    /**
     * Allowed extensions regular expression for admin
     *
     * @var RegEx
     */
    private static $adminAllowedExtensionsRegExp = "/\.(gif|jpe?g|png|7z|gtar|gz|sitx?|tar|tgz|zip|css|html?|asc?|fla|swf|txt|xml|pdf|rtf|docx?|pptx?|csv|xlc|xlsx?|avi|flv|mov)$/i";

    /**
     * Allowed extensions regular expression for other users
     *
     * @var RegEx
     */
    private static $allowedExtensionsRegExp = "/\.(gif|jpe?g|png|zip|css|html?|txt|xml|pdf|rtf|docx?|pptx?|xlsx?)$/i";

    /**
     * Allowed extensions, on update also update Flash file
     *
     * @var array
     */
    private static $extensions = array("gif", "jpeg", "jpg", "png", "7z", "gtar", "gz", "sit", "sitx", "tar", 
        "tgz", "zip", "css", "htm", "html", "as", "asc", "fla", "swf", "txt", "xml", "pdf", "rtf", "doc", 
        "docx", "ppt", "pptx", "csv", "xlc", "xls", "xlsx", "avi", "flv", "mov");

    /**
     * Angular File Upload
     *
     * @return array|bool Associative array for get and post, boolean value for delete operations
     */
    public static function ngFileUpload()
    {
        App::$loader->loadVendorClass("jQuery-File-Upload/upload.class");
        
        $allowedExtensions = App::$user->isAdmin ? self::$adminAllowedExtensionsRegExp : self::$allowedExtensionsRegExp;
        
        $uploadHandler = new \UploadHandler(
            array("script_url" => SITE_URL . "ajax/upload/", "upload_dir" => TMP_DIR, 
                "upload_url" => ROOT_URL . "f/up/tmp/", "max_file_size" => self::MAX_SIZE, 
                "accept_file_types" => $allowedExtensions));
        
        header("Pragma: no-cache");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header('Content-Disposition: inline; filename="files.json"');
        header("X-Content-Type-Options: nosniff");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size");
        
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "OPTIONS":
                break;
            case "HEAD":
            case "GET":
                $result = $uploadHandler->get();
                break;
            case "POST":
                if ($_REQUEST["_method"] === "DELETE") {
                    $result = $uploadHandler->delete();
                } else {
                    $result = $uploadHandler->post();
                }
                break;
            case "DELETE":
                $result = $uploadHandler->delete();
                break;
            default:
                header("HTTP/1.1 405 Method Not Allowed");
        }
        
        if (is_array($result)) {
            if ($result) {
                foreach ($result as &$value) {
                    if (!$value->error)
                        continue;
                    
                    elseif ($value->error == "acceptFileTypes")
                        $value->error = t__("Sorry, uploaded file type is not allowed.");
                    
                    elseif ($value->error == "maxFileSize")
                        $value->error = t_spf('Uploaded file size is too big. Max allowed file size is %1$s', 
                            File::getFormattedFileSize(self::MAX_SIZE));
                    
                    elseif ($value->error == "minFileSize")
                        $value->error = t__("Uploaded file size is too small.");
                    
                    else
                        $value->error = t__(
                            "Some error encountered during uploading your file(s), please try again.");
                    
                    Logger::log($value->error);
                }
                
                //delete reference
                unset($value);
            } else {
                $result[] = array(
                    "error" => t_spf('Uploaded file size is too big. Max allowed file size is %1$s', 
                        File::getFormattedFileSize(self::MAX_SIZE)));
                Logger::log($result[0]["error"]);
            }
        }
        
        return $result;
    }

    /**
     * jQuery File Upload
     *
     * @return array|bool Associative array for get and post, boolean value for delete operations
     */
    public static function jQueryFileUpload()
    {
        App::$loader->loadVendorClass("jQuery-File-Upload/upload.class");
        
        $allowedExtensions = App::$user->isAdmin ? self::$adminAllowedExtensionsRegExp : self::$allowedExtensionsRegExp;
        
        $uploadHandler = new \UploadHandler(
            array("script_url" => SITE_URL . "ajax/upload/", "upload_dir" => TMP_DIR, 
                "upload_url" => ROOT_URL . "f/up/tmp/", "max_file_size" => self::MAX_SIZE, 
                "accept_file_types" => $allowedExtensions));
        
        header("Pragma: no-cache");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header('Content-Disposition: inline; filename="files.json"');
        header("X-Content-Type-Options: nosniff");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size");
        
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "OPTIONS":
                break;
            case "HEAD":
            case "GET":
                $result = $uploadHandler->get();
                break;
            case "POST":
                if ($_REQUEST["_method"] === "DELETE") {
                    $result = $uploadHandler->delete();
                } else {
                    $result = $uploadHandler->post();
                }
                break;
            case "DELETE":
                $result = $uploadHandler->delete();
                break;
            default:
                header("HTTP/1.1 405 Method Not Allowed");
        }
        
        if (is_array($result)) {
            if ($result) {
                foreach ($result as &$value) {
                    if (!$value->error)
                        continue;
                    
                    elseif ($value->error == "acceptFileTypes")
                        $value->error = t__("Sorry, uploaded file type is not allowed.");
                    
                    elseif ($value->error == "maxFileSize")
                        $value->error = t_spf('Uploaded file size is too big. Max allowed file size is %1$s', 
                            File::getFormattedFileSize(self::MAX_SIZE));
                    
                    elseif ($value->error == "minFileSize")
                        $value->error = t__("Uploaded file size is too small.");
                    
                    else
                        $value->error = t__(
                            "Some error encountered during uploading your file(s), please try again.");
                    
                    Logger::log($value->error);
                }
                
                //delete reference
                unset($value);
            } else {
                $result[] = array(
                    "error" => t_spf('Uploaded file size is too big. Max allowed file size is %1$s', 
                        File::getFormattedFileSize(self::MAX_SIZE)));
                Logger::log($result[0]["error"]);
            }
        }
        
        return $result;
    }

    /**
     * Save uploaded file to specified directory.
     * Only one file accepted per call. For multiple file saving use saveFiles function.
     *
     * @param array $uploadedFile Uploaded files array, $_FILES["Filedata"] "Filedata" is set by Flash
     * @param string $dir [optional] Directory to save uploaded file, defaults to TMP_DIR
     * @return array Associative array containing file path on success, or error message on failure
     */
    public static function saveFile($uploadedFile, $dir = TMP_DIR)
    {
        if (($error = self::validateFile($uploadedFile, $dir)) === true) {
            $path = $dir . File::getUniqueFileName(
                strtolower(substr($uploadedFile["name"], strrpos($uploadedFile["name"], "."))), $dir);
            
            if (move_uploaded_file($uploadedFile["tmp_name"], $path))
                return array("path" => $path);
            else
                return array("error" => "UNKNOWN_ERROR");
        }
        
        Logger::log($error, Logger::MT_SYSTEM, 1);
        
        return array("error" => $error);
    }

    /**
     * Validate uploaded file data.
     *
     * @param array $uploadedFile Uploaded file array
     * @param string $dir Directory to save uploaded file
     * @return bool|string True on success, error message on failure
     */
    private static function validateFile($uploadedFile, $dir)
    {
        if (($error = $uploadedFile["error"]) === UPLOAD_ERR_NO_FILE || !is_array($uploadedFile))
            return t__("No file was uploaded.");
        
        if (!is_string($name = $uploadedFile["name"]) || strpos($name, "\0") !== false ||
             ($pos = strrpos($name, ".")) === 0)
            return t__("Invalid file name.");
        
        if (!is_dir($dir))
            return t__("The destination directory is not available.");
        
        if ($error !== UPLOAD_ERR_OK)
            return t_spf('Error occurred during uploading the file, error no: %1$s', $error);
        
        $extension = $pos !== false ? (string)substr($name, $pos + 1) : null;
        
        if (!in_array(strtolower($extension), self::$extensions, true))
            return t_spf('Uploaded file extension(.%1$s) is not allowed.', $extension);
        
        if (($size = (int)$uploadedFile["size"]) <= 0)
            return t_spf('Uploaded file size(%1$d) is too small.', $size);
        
        if ($size > self::MAX_SIZE)
            return t_spf('Uploaded file size(%1$s) exceeds max file size(%2$s).', 
                File::getFormattedFileSize($size), File::getFormattedFileSize(self::MAX_SIZE));
        
        return true;
    }
}