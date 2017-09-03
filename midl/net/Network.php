<?php
namespace midl\core\Net;

/**
 *
 * @author Abdulhalim Kara
 */
class Network
{
    // Constants
    /**
     * CURL options array
     *
     * @var array
     */
    private static $CURL_OPTS = array(CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_TIMEOUT => 60, CURLOPT_USERAGENT => "AHK Framework v1.0");
    
    // Public variables
    
    // Private variables
    
    /**
     * Makes an HTTP request.
     *
     * @param string $url The URL to make the request to
     * @param array $params [Optional] POST array params
     * @param array $options [Optional] Curl options
     * @param CurlHandler $ch [Optional] Initialized curl handle
     * @return bool|string Returns the result on success, false on failure.
     */
    public static function makeRequest($url, $params = null, $options = [], $ch = null)
    {
        if (!$ch)
            $ch = curl_init();
        
        $opts = self::$CURL_OPTS;
        
        if ($options)
            foreach ($options as $key => $option)
                $opts[$key] = $option;
        
        if ($params)
            $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
        
        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        
        if ($curlErrno = curl_errno($ch))
            Logger::log("CURL error: '" . curl_error($ch) . "' error no: '$curlErrno'");
        
        curl_close($ch);
        
        return $result;
    }
}