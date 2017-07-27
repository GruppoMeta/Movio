<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_rest_core_Application
 */

class org_glizy_rest_core_Application extends org_glizy_mvc_core_Application
{
    private $initSiteMap = false;

    /**
     * @param boolean $initSiteMap
     */
    public function setInitSiteMap($initSiteMap)
    {
        $this->initSiteMap = $initSiteMap;
    }

	function run()
	{
		$this->log( "Run Rest application", GLZ_LOG_SYSTEM );
		if (file_exists(org_glizy_Paths::get('APPLICATION_STARTUP')))
		{
			// if the startup folder is defined all files are included
			glz_require_once_dir(org_glizy_Paths::get('APPLICATION_STARTUP'));
		}

		glz_defineBaseHost();
        if ($this->initSiteMap) {
            $this->_initSiteMap();
        }
		$this->_initRequest();

		glz_require_once_dir(org_glizy_Paths::getRealPath('APPLICATION_CLASSES'));

		$this->_startProcess();

		if (file_exists(org_glizy_Paths::get('APPLICATION_SHUTDOWN')))
		{
			// if the shutdown folder is defined all files are included
			glz_require_once_dir(org_glizy_Paths::get('APPLICATION_SHUTDOWN'));
		}
	}

	function _startProcess()
	{
		foreach( $this->proxyMap as $k=>$v )
		{
			$v->onRegister();
		}

		$method = __Request::$method ? __Request::$method : 'get';
		$controller = __Request::get( 'controller', '' );
		$status = 200;
		$directOutput = false;
        $result = array();

        if ( $method!='options' && $controller )
		{
			$actionClass = org_glizy_ObjectFactory::createObject( $controller, $this );

			if ( is_object( $actionClass ) )
			{
				$reflectionClass = new ReflectionClass( $actionClass );
				$callMethod = '';
				if ( $reflectionClass->hasMethod( "execute_".$method ) )
				{
					$callMethod = "execute_".$method;
				}
				else if ( $reflectionClass->hasMethod( "execute" ) )
				{
					$callMethod = "execute";
				}

				if ( $callMethod )
				{
					$reflectionMethod = $reflectionClass->getMethod( $callMethod );
					$methodParams = $reflectionMethod->getParameters();
					$params = array();
					foreach( $methodParams as $v )
					{
						$params[] = __Request::get( $v->name );
					}
					$result = call_user_func_array( array( $actionClass, "execute" ), $params );
					$directOutput = $actionClass->directOutput;

					if (is_array($result)) {
	                    if (isset($result['http-status'])) {
	                        $status = $result['http-status'];
	                        unset($result['http-status']);
	                    } else if (isset($result['httpStatus'])) {
                            $status = $result['httpStatus'];
                            unset($result['httpStatus']);
                        }
                        $keys = array_keys($result);
                        if (count($result)==1 && $keys[0]===0) {
                            $result = $result[0];
                        }
                    } else if (is_object($result)) {
                    	if (property_exists($result, 'httpStatus')) {
                    		$status = $result->httpStatus;
                            unset($result->httpStatus);
                    	}
                    }
				}
				else
				{
					$status = 501;
				}
			}
			else
			{
				$status = 404;
			}
		}
		else if ( $method=='options')
		{
			$status = 200;
		}
		else
		{
			$status = 404;
		}

        if ( $result === false )
        {
            $status = 500;
        }

		if ($status === 404) {
			$report = array();
            $report['Request'] = __Request::getAllAsArray();
            $report['_SERVER'] = $_SERVER;
            $this->log( $report, GLZ_LOG_SYSTEM, 'glizy.404' );
		}
		$httpAccept = (strpos(@$_SERVER['HTTP_ACCEPT'], 'xml')!==false) ? 'xml' : 'json';

		// sent response
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: -1');
		header( $_SERVER['SERVER_PROTOCOL'].' '.$status.' '.org_glizy_helpers_HttpStatus::getStatusCodeMessage( $status ) );
		if ( !is_null($result) )
		{
			if ( $httpAccept == 'json' )
			{
				header("Content-Type: application/json; charset=utf-8");
				if (!$directOutput) {
					// @ serve per evitare waring di conversione nel caso ci siano caratteri non utf8
					echo @json_encode( $result );
				} else {
					echo $result;
				}
			}
			else
			{
				header("Content-Type: text/xml; charset=".GLZ_CHARSET);
				if ( !is_array( $result ) || !isset( $result['result'] ) )
				{
					$result = array( 'result' => $result );
				}
				echo $this->createXml( $result );
			}
		}
	}

	private function createXml( $data )
	{
		$xml = new XmlWriter();
		$xml->openMemory();
        $xml->startDocument('1.0', GLZ_CHARSET);
		$this->createXmlNode($xml, $data);
		return $xml->outputMemory(true);
	}

	private function createXmlNode( XMLWriter $xml, $data )
	{
	    foreach($data as $key => $value){
	    	if ( $key == "_className" || is_null( $value ) ) continue;

	        if( is_string( $key) && is_object( $value ) )
	        {
	            $xml->startElement($key);
	            $this->createXmlNode($xml, $value );
	            $xml->endElement();
	        }
	        else if( is_string( $key) && is_array($value) )
	        {
	        	$arrayKeys = array_keys( $value );
	        	$wrapTag = preg_replace( '/ies$/i', 'y', $key );
	        	$wrapTag = rtrim( $wrapTag, 's' );
	        	foreach( $arrayKeys as $k )
	        	{
	        		if ( is_string( $k ) )
	        		{
	        			$wrapTag = '';
	        			break;
	        		}
	        	}
	            $xml->startElement($key);
				if ( !empty( $wrapTag ) )
				{
		            foreach( $value as $v )
		            {
		            	if (is_string($v)) {
							$xml->writeElement($key, $v);
		            	} else {
				            $xml->startElement( $wrapTag );
				            $this->createXmlNode($xml, $v);
				            $xml->endElement();
		            	}
		            }
				}
				else
				{
				    $this->createXmlNode($xml, $value );
				}
	            $xml->endElement();
	        }
	        else if( is_array($value) )
	        {
	            $this->createXmlNode($xml, $value);
	        }
	        else
	        {
                if (strtolower(GLZ_CHARSET) != 'utf-8') {
                    $value = utf8_encode($value);
                }
		        $xml->writeElement($key, $value);
	        }

	    }
	}

	function executeCommand( $command )
	{
		$actionClass = &org_glizy_ObjectFactory::createObject( $command, $this );
		if ( is_object( $actionClass ) )
		{
			if ( method_exists( $actionClass, "execute" ) )
			{
				$params = func_get_args();
				array_shift($params);
				return call_user_func_array( array( $actionClass, "execute" ), $params );
			}
		}
	}
}