<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\core\exception;

class NotFoundException extends \Exception
{

    /**
     *
     * @param string $message [optional]
     * @param int $code [optional]
     * @param \Throwable $previous [optional]
     */
    function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
