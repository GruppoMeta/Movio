<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_components_Page extends org_glizy_mvc_components_Page
{
    protected $actionClass;

	function process()
	{
		$this->action = __Request::get( $this->actionName, $this->getAttribute( 'defaultAction' ) );
		if ( empty( $this->action ) )
		{
			$this->_application->setError( 'noVerb' );
			return;
		}

		$found = false;
		foreach ( $this->childComponents  as $c )
		{
			if ( is_a( $c, 'org_glizy_mvc_components_State' ) )
			{
				$c->deferredChildCreation();
				if ( $c->isCurrentState() )
				{
					$found = true;
					break;
				}
			}
		}

		if ( $found )
		{
			$this->actionClass = &org_glizy_ObjectFactory::createObject( $this->controllerBasePath.$this->action, $this );
			if ( !is_object( $this->actionClass ) )
			{
				$this->action = ucfirst( $this->action );
				$this->actionClass = &org_glizy_ObjectFactory::createObject( $this->controllerBasePath.$this->action, $this );
			}

			if ( is_object( $this->actionClass ) )
			{
				if ( method_exists( $this->actionClass, "execute" ) )
				{
					call_user_func( array( $this->actionClass, "execute" ), '' );
				}
			}

			$this->action = strtolower( $this->action );

			parent::process();

			if ( is_object( $this->actionClass ) )
			{
				if ( method_exists( $this->actionClass, 'executeLater' ) )
				{
					call_user_func( array( $this->actionClass, 'executeLater' ), '' );
				}
			}
		}
		else
		{
			$this->_application->setError( 'badVerb', $this->action );
		}


	}

	function process_ajax()
	{
		return false;
	}

	/**
	 * Render
	 *
	 * @return	string
	 * @access	public
	 */
	function render()
	{
		$this->renderChilds();

		$responseDate = gmstrftime('%Y-%m-%dT%T').'Z';
		$error = $this->_application->getError();
		$requestAttribs = '';
		$content = '';

		if ( empty( $error ) )
		{

			for ($i=0; $i<count($this->_output); $i++)
			{
				if ( $this->_output[$i]['editableRegion'] != 'content' ) continue;
				$content .= $this->_output[$i]['code'];
			}

			$params	= &org_glizy_Request::_getValuesArray();
			unset($params['__url__']);
			unset($params['__back__url__']);
			foreach($params	as $k=>$v)
			{
				if ( $v[ GLZ_REQUEST_TYPE ] == GLZ_REQUEST_GET )
				{
					$requestAttribs .= ' '.$k.'="'.htmlentities( $v[GLZ_REQUEST_VALUE] ).'"';
				}
			}
		}
		else
		{
			$content = $error;
		}
		$requestUrl = org_glizy_Routing::$baseUrl;
		$charset = GLZ_CHARSET;
		$output = <<<EOD
<?xml version="1.0" encoding="$charset"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
		<responseDate>$responseDate</responseDate>
		<request $requestAttribs>$requestUrl</request>
		$content
</OAI-PMH>
EOD;


		return $output;
	}
}