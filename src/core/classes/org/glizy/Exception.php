<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

define('GLZ_E_ERROR', E_ERROR);
define('GLZ_E_WARNING', E_WARNING);
define('GLZ_E_NOTICE', E_NOTICE);
define('GLZ_E_404', 404);
define('GLZ_E_500', 500);

/**
 * Class org_glizy_Exception
 */
class org_glizy_Exception
{

    /**
     * @param string $message
     * @param int    $errono
     * @param string $file
     */
    function __construct($message, $errono = GLZ_E_ERROR, $file = '')
    {
        if (is_array($message)) {
            $messageStr = array_shift($message);
            $message    = vsprintf($messageStr, $message);
        }

        if ($errono != GLZ_E_404) {
            self::show($errono, '', '', '', $message);
        } else {
            self::show404($errono, $message);
        }
        exit();
    }

    /**
     * @param int $errno
     * @param string $message
     */
    static public function show404($errno, $message)
    {
        $e                = array();
        $e['code']        = $errno;
        $e['description'] = $message;
        $e['message']     = '';
        header("HTTP/1.0 404 Not Found");
        include_once(dirname(__FILE__) . '/../../../pages/errors/404.php');
        exit();
    }

    /**
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param string $message
     * @param int    $headerCode
     */
    static public function show($errno, $errstr, $errfile, $errline, $message = '', $headerCode = 500)
    {
        $eventInfo = array('type' => 'dumpException', 'data' => array('message' => $message, 'errono' => $errono, 'file' => $errfile, 'errline' => $errline));
        $evt = &org_glizy_ObjectFactory::createObject('org.glizy.events.Event', $this, $eventInfo);
        org_glizy_events_EventDispatcher::dispatchEvent($evt);

        @header('HTTP/1.0 500 Internal Server Error');
        $errors = array(
            1 => 'E_ERROR',
            2 => 'E_WARNING',
            4 => 'E_PARSE',
            8 => 'E_NOTICE',
            16 => 'E_CORE_ERROR',
            32 => 'E_CORE_WARNING',
            64 => 'E_COMPILE_ERROR',
            128 => 'E_COMPILE_WARNING',
            256 => 'E_USER_ERROR',
            512 => 'E_USER_WARNING',
            2047 => 'E_ALL',
            2048 => 'E_STRICT',
            4096 => 'E_RECOVERABLE_ERROR'
        );

        $e                = array();
        $e['code']        = isset($errors[$errno]) ? $errors[$errno] : $errors[1];
        $e['description'] = $errstr;
        $e['message']     = $message;

        if (class_exists('org_glizy_Config') && org_glizy_Config::get('DEBUG') === true) {
            $e['file']       = $errfile;
            $e['line']       = $errline;
            $e['stacktrace'] = array_slice(debug_backtrace(), 2);
            include_once(dirname(__FILE__) . '/../../../pages/errors/debug.php');
        } else {
            include_once(dirname(__FILE__) . '/../../../pages/errors/general.php');
        }
        exit();
    }
}