<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_compilers_Routing
 */
class org_glizy_compilers_Routing extends org_glizy_compilers_Compiler
{
	private $language;
	private $prog;

    /**
     *
     */
	function compile($options)
	{
		$this->addEventListener(GLZ_EVT_LISTENER_COMPILE_ROUTING, $this);
		$this->initOutput();

		if ( __Config::get( 'MULTILANGUAGE_ENABLED' ) )
		{
			$this->language = '{language}/';
		}

        $evt = array('type' => GLZ_EVT_START_COMPILE_ROUTING);
        $this->dispatchEvent($evt);

		if ( strpos( $this->_fileName, 'routing.xml') !== false )
		{
			$modules = org_glizy_Modules::getModules();
			foreach( $modules as $m )
			{
				$path = glz_findClassPath( $m->classPath );
				if ( !is_null( $path ) && file_exists( $path . '/config/routing.xml' ) )
				{
					$this->compileFile( $path . '/config/routing.xml' );
				}
			}
		}

		$this->compileFile( $this->_fileName );
		return $this->save();
	}

    /**
     * @param $fileName
     */
	function compileFile( $fileName )
	{
        /** @var org_glizy_parser_XML $xml */
		$xml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$xml->loadAndParseNS( $fileName );
		$this->compileXml($xml);
	}

    /**
     * @param $xmlString
     */
	function compileString($xmlString)
	{
		$xml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$xml->loadXmlAndParseNS($xmlString);
		$this->compileXml($xml);
	}

    /**
     * @param org_glizy_parser_XML $xml
     */
	private function compileXml($xml, $prefix='', $middleware='')
	{
		if ($xml->hasChildNodes()) {
			$this->prog = 0;

			foreach ($xml->childNodes as $node) {
				if ( $node->nodeName == "glz:Routing" ) {
					$this->compileXml($node, $prefix, $middleware);
				} else if ( $node->nodeName == "glz:Route" ) {
					$this->compileRouteNode($node, $prefix, $middleware);
				} else if ( $node->nodeName == "glz:RouteGroup" ) {
					if (!$node->hasAttribute('value') || $node->getAttribute('value')=='') {
						throw new Exception('glz:RouteGroup need value attribute');
					}
					$this->compileXml($node, $prefix.$node->getAttribute('value'), $middleware);
				} else if ( $node->nodeName == "glz:Middleware" ) {
					if (!$node->hasAttribute('class') || $node->getAttribute('class')=='') {
						throw new Exception('glz:Middleware need class attribute');
					}
					$this->compileXml($node, $prefix, $node->getAttribute('class'));
				}
			}
		}
	}

    /**
     * @param DOMElement $xml
     * @param String $prefix
     * @param String $middleware
     */
	private function compileRouteNode($param, $prefix='', $middleware='')
	{
		$this->prog++;
		$name 	= strtolower( $param->getAttribute('name') );
		if ( empty( $name ) )
		{
			$name = (string)$this->prog;
			$name = $param->hasAttribute('method') ? strtolower( $param->getAttribute('method') ).'_'.$name : $name;
		}

		// controlla se il nodo ha dei figli
		if ( $param->hasChildNodes() )
		{
			$this->output .= '$configArray["'.$name.'"] = array()'.GLZ_COMPILER_NEWLINE;

			foreach( $param->childNodes as $node )
			{
				if ( $node->nodeName == "glz:RouteCondition" )
				{
					$this->compileNode($node, $name, true, $prefix, $middleware);
				}
			}
		}
		else
		{
			$this->compileNode($param, $name, false, $prefix, $middleware);
		}
	}

    /**
     * @param DOMElement $param
     * @param      $name
     * @param bool $child
     */
	private function compileNode( &$param, $name, $child, $prefix, $middleware )
	{
		$value 	= $param->hasAttribute('value') ? $param->getAttribute('value') : $param->firstChild->nodeValue;
		$value = !$param->hasAttribute('method') && !$param->hasAttribute('skipLanguage') ? $this->language.$prefix.$value : $prefix.$value;
		$parseUrl = $param->hasAttribute('parseUrl') ? $param->getAttribute('parseUrl') : 'true';
		$keyName = $param->hasAttribute('keyName') ? $param->getAttribute('keyName') : '';
		$keyValue = $param->hasAttribute('keyValue') ? $param->getAttribute('keyValue') : '';
		$method = $param->hasAttribute('method') ? strtolower( $param->getAttribute('method') ) : '';
		$child = $child ? '[]' : '';

		$urlPattern = '';
		$urlValues = array();
		$staticValues = array();
		if ($middleware) {
			$staticValues['__middleware__'] = $middleware;
		}
		if ( $parseUrl == 'true' )
		{
			$attributeToSkip = array( 'name', 'value', 'parseUrl', 'keyValue', 'keyValue' );
			foreach ( $param->attributes as $index=>$attr )
			{
				// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
				$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
				$attrName = $prefix.$attr->name;
				if ( !in_array( $attrName, $attributeToSkip ) )
				{
					$staticValues[ $attrName ] = $attr->value;
				}
			}

            /** @var org_glizy_application_Application $application */
			$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
			$siteMap = &$application->getSiteMap();
			$isApplicationDB = $siteMap && $siteMap->getType() == 'db';

			$urlPattern = str_replace('/', '\/', $value );
			preg_match_all("|\{(.*)\}|U", $urlPattern, $match, PREG_PATTERN_ORDER);
			for($i=0; $i<count($match[0]); $i++)
			{
				$command = explode('=', $match[1][$i]);
				$urlValuesKey = $command[count($command)-1];
				switch ($command[0])
				{
					case 'language':
						$urlPartValue = '(.{2})';
						break;
					case '*':
					case 'currentMenu':
						$urlPartValue = '(.*)';
						$urlValuesKey = 'pageId';
						break;
					case 'pageId':
						if (count($command)>1 && is_object( $siteMap ) )
						{
							if (is_numeric($command[1]))
							{
								$page = $siteMap->getNodeById($command[1]);
							}
							else
							{
								$page = $siteMap->getMenuByPageType($command[1]);
								if ( is_null( $page ) )
								{
									$module = org_glizy_Modules::getModule( $command[1] );
									if ( !is_null( $module ) && $module->pageType )
									{
										$page = $siteMap->getMenuByPageType( $module->pageType );
									}
								}
							}

							if ( $isApplicationDB )
							{
								$urlPartValue =  strtolower('('.$page->id.'\/[^\/]*?)');
							}
							else
							{
								$urlPartValue =  strtolower('('.str_replace('/', '\/', $page->id).')');
							}
						}
						else
						{
							$urlPartValue = '([^\/]*)';
						}
						$urlValuesKey = $command[0];
						break;
					case 'pageTitle':
						$urlPartValue = '([^\/]*)';
						break;
					case 'value':
						$urlPartValue = '([^\/]*)';
						break;
					case 'integer':
						$urlPartValue = '(\d*)';
						break;
					case 'static':
						$urlPartValue = '';
						$urlValuesKey = $command[1];
						break;
					case 'state':
						$urlPartValue = '('.$command[1].')';
						$urlValuesKey = $command[0];
						break;
					case 'config':
						$urlPartValue = '';
						$urlValuesKey = '';
						break;
					default:
						$urlPartValue = '('.(count($command) > 1 ? $command[1] : $command[0]).')';
						$urlValuesKey = $command[0];
						break;
				}


				if (empty($urlPartValue))
				{
					$urlPattern = str_replace(array( $match[0][$i].'\/', $match[0][$i] ) , '()', $urlPattern);
					$urlValues[$urlValuesKey] = $command[2];
					continue;
				}
				$urlValues[$urlValuesKey] = '';
				$urlPattern = str_replace($match[0][$i], $urlPartValue, $urlPattern);
				if (strpos($urlPattern, '#')!==false) {
					list($urlPattern) = explode('#', $urlPattern);
				}
			}

			$urlPattern = rtrim( $urlPattern, '\/' );
			$urlPattern = '|^'.$urlPattern.'(\/?)$|i';
		}

		$this->output .= '$configArray["'.$name.'"]'.$child.' = array("value" => "'.addslashes($value).'", "urlPattern" => "'.addcslashes($urlPattern, '"\\' ).'", "urlValues" => "'.addcslashes( serialize( $urlValues ), '"\\' ).'", "staticValues" => "'.addcslashes( serialize( $staticValues ), '"\\' ).'", "parseUrl" => '.$parseUrl.', "keyName" => "'.$keyName.'", "keyValue" => "'.$keyValue.'", "method" => "'.$method.'" )'.GLZ_COMPILER_NEWLINE;
	}

    /**
     * @param $evt
     */
	public function listenerCompileRouting($evt)
	{
		$xmlString = $evt->data;
		$this->compileString($xmlString);
	}
}