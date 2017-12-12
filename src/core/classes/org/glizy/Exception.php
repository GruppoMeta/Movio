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
define('GLZ_E_403', 403);
define('GLZ_E_500', 500);

/**
 * Class org_glizy_Exception
 */
class org_glizy_Exception
{
    static public $applicationName = 'GLIZY framework';
    static public $debugMode = false;

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


        if ($errono == GLZ_E_403) {
            self::show403($message);
        } else if ($errono == GLZ_E_404) {
            self::show404($message);
        } else {
            self::show($errono, '', '', '', $message);
        }
    }

    /**
     * @param int $errno
     * @param string $message
     */
    static public function show404($message)
    {
        self::loadErrorPage(404, 'Not Found', $message);
    }

    /**
     * @param int $errno
     * @param string $message
     */
    static public function show403($message)
    {
        self::loadErrorPage(403, 'Forbidden', $message);
    }

    /**
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param string $message
     * @param int    $headerCode
     */
    static public function show($errno, $errstr, $errfile, $errline, $message = '', $headerCode = 500, $trace=null)
    {
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
        $e['file']        = $errfile;
        $e['line']        = $errline;
        $e['description'] = $errstr;
        $e['message']     = $message;
        $e['trace']       = $trace;

        self::loadErrorPage(500, 'Internal Server Error', $e);
    }

    /**
     * Load the error page
     * Check if the developer have defined a custome page
     * @param  int $code            Error code
     * @param  string $codeDescription Error code description
     * @param  string|array $message         Error message
     */
    private static function loadErrorPage($code, $codeDescription, $message)
    {
        @header('HTTP/1.0 '.$code.' '.$codeDescription);
        $html = true;
        foreach (headers_list() as $header) {
            if ((stripos($header, 'content-type:') === 0) && (stripos($header, 'html') === false)) {
                $html = false;
            }
        }

        if (!is_array($message)) {
            $e                = array();
            $e['code']        = $code;
            $e['description'] = $message;
            $e['message']     = '';
        } else {
            $e = $message;
        }
        $e['title'] = $e['title'] ? $e['title'] : self::$applicationName;

        if (self::$debugMode && isset($e['line'])) {
            $e['trace'] = self::formatTrace( $e['line'], $e['trace'] ? $e['trace'] : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        } else {
            unset($e['trace']);
            unset($e['file']);
        }

        if (!$html) {
            echo json_encode($e);
        } else if (self::$debugMode) {
            include_once(dirname(__FILE__) . '/../../../pages/errors/debug.php');
        } else if (file_exists('error-'.$code.'.html')) {
            include_once('error-'.$code.'.html');
        } else if (file_exists('error-'.$code.'.php')) {
            include_once('error-'.$code.'.php');
        } else if (file_exists(dirname(__FILE__) . '/../../../pages/errors/'.$code.'.php')) {
            include_once(dirname(__FILE__) . '/../../../pages/errors/'.$code.'.php');
        } else {
            include_once(dirname(__FILE__) . '/../../../pages/errors/general.php');
        }
        exit;
    }

    /**
     * @param  string $firstErrorLine
     * @param  array  $trace
     * @return array
     */
    private static function formatTrace($firstErrorLine, $trace)
    {
        $formattedTrace = array();
        $errors = $e['trace'];
        for ( $i = 0; $i < count($trace); $i++ )
        {
            $formattedTrace[] = sprintf('%s:%s(%s) #%d',
                    $trace[$i]['class'],
                    $trace[$i]['function'],
                    self::formatTraceArgs($trace[$i]['args']),
                    ($i ? $trace[$i-1]['line'] : $firstErrorLine)
                );
        }

        return $formattedTrace;
    }

    /**
     * @param  mixed $args
     * @return string
     */
    private static function formatTraceArgs($args)
    {
        if (!$args) return '';

        $output = array();
        if ($args) {
            foreach ($args as $value) {
                if (is_array($value)) {
                    $output[] = sprintf('Array(%d)', count($value));
                } else if (is_object($value)) {
                    $output[] = get_class($value);
                } else if (is_null($value)) {
                    $output[] = 'null';
                } else {
                    $output[] = $value;
                }
            }
        }
        return $output ? implode(', ', $output) : '';
    }
}
