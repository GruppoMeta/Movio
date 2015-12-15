<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_log_ElasticSearch
 */
class org_glizy_log_ElasticSearch extends org_glizy_log_LogBase
{
    private $_message = array();
    private $_index = 'glizy';
    private $_type = 'log';
    private $_host = '';

    /**
     * @param array      $options
     * @param int|string $level
     * @param string     $group
     */
    function __construct($options=array(), $level=GLZ_LOG_DEBUG, $group='')
    {
        parent::__construct($options, $level, $group);
        if (isset($options['index']))
        {
            $this->_index = $options['index'];
        }
        if (isset($options['type']))
        {
            $this->_type = $options['type'];
        }
        if (isset($options['host'])) {
            $this->_host = $options['host'];
        } else {
            $this->_host = __Config::get ( 'glizy.exception.log.elasticsearch.url' );
        }
        $this->_message['index'] = $this->_index;
        $this->_message['type'] = $this->_type;
    }

    /**
     * @return string
     */
    private function getCallingName() {
        $trace  = debug_backtrace();
        $caller = $trace[5];

        if (isset($caller['class'])) {
            $result = $caller['class'] . '::' . $caller['function'];
        } else {
            $result = $caller['function'];
        }

        return $result;
    }

    /**
     * @param        $msg
     * @param int    $level
     * @param string $group
     *
     * @return bool|mixed
     * @throws Exception
     */
    function log($msg, $level=GLZ_LOG_DEBUG, $group='')
	{
		if (!$this->_check($level, $group))
		{
			return false;
		}

        $t = explode ( " ", microtime ( ) );
        $params = array(
            '@timestamp' => date("Y-m-d\TH:i:s",$t[1]).substr((string)$t[0],1,4).date("P"),
            'host' => !isset( $_SERVER["SERVER_ADDR"] ) ? 'console' : $_SERVER["SERVER_ADDR"],
            'group' => $group,
            'level' => $level,
            'caller' => $this->getCallingName(),
            'pid' => getmypid(),
            'appName' => __Config::get ( 'APP_NAME' )
        );

        $message = array_merge($this->_message, $params);

        if (is_array( $msg ))
        {
            $message = array_merge($message, $msg);
        }
        else
        {
            $message = array_merge($message, array('message' => $msg));
        }

        // set_time_limit(0);

        $data 		= json_encode ( $message );
        $restUrl 	= $this->_host . $this->_index . '-' . date ( "Y.m.d" ) . '/' . $this->_type;
        $ch 		= curl_init ( $restUrl );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen ( $data ) )
        );

        if( ! $result = curl_exec($ch))
        {
            if (class_exists('org_glizy_Config') && org_glizy_Config::get('DEBUG') === true) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
        }

        curl_close($ch);

        return $result;
	}
}