<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_Routing
 */
class org_glizy_Routing
{
	static $requestUrl = '';
	static $queryString = '';
	static $baseUrl = '';
	static $baseUrlParam = '';

	function __construct()
	{
	}

	public static function init()
	{
		$configArray = &self::_getValuesArray();

		$scriptName = '/' . str_replace( '/', '\/', str_replace( '\\', '/', $_SERVER["SCRIPT_NAME"] ) ) . '/';
    	$dirName =  '/^' . str_replace( '/', '\/', str_replace( '\\', '/', dirname( $_SERVER["SCRIPT_NAME"] ) )) . '\//';
		self::$requestUrl = $_SERVER["REQUEST_URI"];
    	self::$requestUrl = preg_replace( array( $scriptName, $dirName ), array( '', '' ), self::$requestUrl, 1);
		if ( org_glizy_Config::get( 'PRESERVE_SCRIPT_NAME' ) )
		{
			self::$requestUrl = preg_replace( $dirName, '', self::$requestUrl, 1);
		}
		else
		{
			preg_replace( array( $scriptName, $dirName ), array( '', '' ), self::$requestUrl, 1);
		}
		self::$requestUrl = ltrim( self::$requestUrl, '/' );
		$pos = org_glizy_Config::get('SEF_URL') === 'full' ? strpos( self::$requestUrl, '?' ) : strpos( self::$requestUrl, '&' );
		if ( $pos !== false )
		{
			self::$queryString = substr( self::$requestUrl, $pos + 1);
			self::$requestUrl = substr( self::$requestUrl, 0, $pos );
		}
		self::$requestUrl = trim( self::$requestUrl, '?' );

		if ( org_glizy_Config::get('SEF_URL') === 'full' )
		{
			self::$baseUrl = GLZ_HOST.'/';
			self::$baseUrlParam = '?';
		}
		else
		{
			self::$baseUrl = org_glizy_Paths::get('PAGE_INDEX').'?';
			self::$baseUrlParam = '&';
		}
	}

	public static function dump()
	{
		var_dump(self::_getValuesArray());
	}


	/**
     * @param bool $removeGetParams
     *
     * @return string
     */
    public static function scriptUrl( $removeGetParams=false )
	{
		if ( $removeGetParams )
		{
			return self::$baseUrl.self::$requestUrl;
		}
		else
		{
			return self::$baseUrl.self::$requestUrl.( empty( self::$queryString ) ? '' : self::$baseUrlParam.self::$queryString );
		}
	}

	/**
     * @param      $params
     * @param bool $absolute
     *
     * @return string
     */
    public static function scriptUrlWithParams( $params, $absolute = false )
	{
		$host = $absolute && org_glizy_Config::get('SEF_URL') == true ? GLZ_HOST.'/'  : '';
		$params = ltrim($params, '&');
		$params = preg_replace( '/^&amp;/', '', $params );
		return rtrim($host.self::$baseUrl.self::$requestUrl.self::$baseUrlParam.$params, '?&');
	}


	/**
     * @param string $route
     * @param array  $queryVars
     * @param array  $addParam
     *
     * @return mixed|string
     */
    public static function makeURL($route='', $queryVars=array(), $addParam=array())
	{
		if (is_object($queryVars)) {
			$queryVars = method_exists($queryVars, 'getValuesAsArray') ? $queryVars->getValuesAsArray(): (array)$queryVars;
		}
		if ( org_glizy_Config::get('SEF_URL') == false)
		{
			return self::_makeURL_NOSEF( $route, $queryVars, $addParam );
		}
		else
		{
			return self::_makeURL_SEF( $route, $queryVars, $addParam );
		}
	}

	/**
	 * Verify if the routing exixts
	 * @param  string $route Routing name
	 * @return boolean        return true if the routing exixts
	 */
	public static function exists($route)
	{
		$configArray = &self::_getValuesArray();
		return isset($configArray[strtolower($route)]);
	}

	/**
     * @param string $route
     * @param array  $queryVars
     * @param array  $addParam
     *
     * @return string
     */
    private static function _makeURL_NOSEF($route='', $queryVars=array(), $addParam=array())
	{
		$queryVars = array_merge( $queryVars, $addParam );
		if (!empty($route))
		{
			$configArray = &self::_getValuesArray();
			/** @var $application org_glizy_application_Application */
            $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
			/** @var org_glizy_application_SiteMap $siteMap */
            $siteMap = &$application->getSiteMap();

			// TODO
			// controllare se il route richiesto esiste.
			//
			// TODO
			// ci sono molte classi che usano lo stesso concetto di memorizzare
			// i dati in un array statico
			// conviene fare una classe base e estendere questa
			//
			$url = $configArray[strtolower($route)]['value'];
			$pageId = 0;
			preg_match_all("|\{(.*)\}|U", $url, $match, PREG_PATTERN_ORDER);
			for($i=0; $i<count($match[0]); $i++)
			{
				$value = '';
				$key = '';
				$command = explode('=', $match[1][$i]);
				$key = $command[0];
				switch ($command[0])
				{
					case 'language':
						$value = isset($queryVars['language']) ? $queryVars['language'] : $application->getLanguage();
						break;
					case '*':
					case 'currentMenu':
						/** @var $page org_glizy_application_SiteMapNode */
                        $page = &$application->getCurrentMenu();
						$value =  $page->id;
						$key = 'pageId';
						break;
					case 'pageId':
						// ricerca la pagina da linkare
						if (count($command)>1)
						{
							if (is_numeric($command[1]))
							{
								$page = $siteMap->getNodeById($command[1]);
							}
							else
							{
								$page = $siteMap->getMenuByPageType($command[1]);
							}
							$value =  $page->id;
						}
						else
						{
							$pageId = $queryVars[$command[0]];
							if ( empty( $pageId ) )
							{
								/** @var $page org_glizy_application_SiteMapNode */
                                $page = &$application->getCurrentMenu();
								$pageId =  $page->id;
								unset( $page );
							}
							$value = $pageId;
						}

						break;
					case 'pageTitle':
						continue;
						break;
					case 'value':
					case 'integer':
						$value =  isset($queryVars[$command[1]]) ? $queryVars[$command[1]] : __Request::get( $command[1], '' );
						$key = $command[1];
						break;
					case 'static':
						$value = $command[2];
						$key = $command[1];
						break;
					default:
						$value =  $command[1];
						break;
				}
				if (is_string($value) && $value == "" )
				{
					continue;
				}

				if ( !isset( $queryVars[ $key ] ) ) $queryVars[ $key ] = $value;
			}
		}

		$url = '';
		foreach($queryVars as $k=>$v)
		{
			$url .= (!empty($url) ? '&' : '').$k.'='.$v;
		}
		$url = org_glizy_Paths::get('PAGE_INDEX').'?'.$url;
		return $url;
	}

    /**
     * @param string $route
     * @param array  $queryVars
     * @param array  $addParam
     *
     * @return mixed|string
     */
    private static function _makeURL_SEF($route='', $queryVars=array(), $addParam=array())
	{
		if ( !isset( $addParam[ '__modal__' ] ) && __Request::exists( '__modal__' ) )
		{
			$addParam[ '__modal__' ] = __Request::get( '__modal__' );
		}

		$url = '';
		if (!empty($route))
		{
			$configArray = &self::_getValuesArray();
			/** @var org_glizy_application_Application $application */
            $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
			/** @var org_glizy_application_SiteMap $siteMap */
            $siteMap = &$application->getSiteMap();
			$isApplicationDB = $siteMap && $siteMap->getType() == 'db';

			// TODO
			// controllare se il route richiesto esiste.
			//
			// TODO
			// ci sono molte classi che usano lo stesso concetto di memorizzare
			// i dati in un array statico
			// conviene fare una classe base e estendere questa
			//
			if (!isset($configArray[strtolower($route)])) {
				return $route;
			}
			$routing = $configArray[strtolower($route)];
			if ( isset( $routing[0] ) )
			{
				foreach( $routing as $v )
				{
					if ( $queryVars[ $v[ 'keyName' ] ] == $v[ 'keyValue' ] || empty( $v[ 'keyValue' ] ))
					{
						$url = $v['value'];
						break;
					}
				}
			}
			else
			{
				$url = $routing['value'];
			}

			if ( strpos( $url, 'http://' ) !== false ) return $url;

			$pageId = 0;
			$language = isset($queryVars['language']) ? $queryVars['language'] : $application->getLanguage();
			preg_match_all("|\{(.*)\}|U", $url, $match, PREG_PATTERN_ORDER);
			for($i=0; $i<count($match[0]); $i++)
			{
                $sanitize = true;
                $value = '';
				$value2 = '';
				$command = explode('=', $match[1][$i]);
				switch ($command[0])
				{
					case 'language':
						$value = $language;
						break;
					case '*':
					case 'currentMenu':
						$page = &$application->getCurrentMenu();
						$value =  $page->id;
						$value2 = $page->title;
						unset( $page );
						break;
					case 'currentMenuId':
						$page = &$application->getCurrentMenu();
						$value =  $page->id;
						unset( $page );
						break;
					case 'pageId':
						// ricerca la pagina da linkare
						if (count($command)>1)
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

							if ($page->url) {
								$value =  preg_replace('/^'.$language.'\//', '', $page->url);
								$value2 = '';
								$sanitize = false;
							} else {
								$value =  $page->id;
								$value2 = $isApplicationDB ? $page->title : '';
							}
						}
						else
						{
							$pageId = @$queryVars[$command[0]];
							if ( empty( $pageId ) )
							{
								$page = &$application->getCurrentMenu();
								$pageId =  $page->id;
								unset( $page );
							}
							$value = $pageId;
							$value2 = '';

						}

						break;
					case 'pageTitle':
						// ricerca la pagina da linkare
						if (!isset($queryVars['title']))
						{
							// TODO
							// non deve instanziare un nuovo menù altrimenti rilegge tutto dal db ogni volta
							$page = $siteMap->getNodeById($pageId);
							$value = $page->title;
						}
						else
						{
							$value = $queryVars['title'];
						}
						break;
					case 'value':
					case 'valuePlain':
					case 'valueRaw':
					case 'integer':
						$value =  isset($queryVars[$command[1]]) ? $queryVars[$command[1]] : __Request::get( $command[1], '' );
						$sanitize = $command[0]!=='valuePlain' && $command[0]!=='valueRaw';
						if (!$sanitize && $command[0]!=='valueRaw') {
							$value = urlencode($value);
						}
						break;
					case 'i18n':
						$value =  strtolower(__T($command[1]));
			                        $sanitize = false;
						break;
					case 'static':
						$value =  '';
						break;
					case 'config':
                        $sanitize = false;
						$value =  __Config::get($command[1]);
						break;
					default:
						$value =  $command[1];
						break;
				}
				if (is_string($value) && empty($value))
				{
					$url = str_replace($match[0][$i].'/', '', $url);
					$url = str_replace($match[0][$i], '', $url);
					continue;
				}

                if ($sanitize) {
                    $value = glz_sanitizeUrlTitle($value).($value2!='' ? '/' : '');
                    $value2 = glz_sanitizeUrlTitle($value2);
                }

				$url = str_replace($match[0][$i], $value.$value2, $url);
			}

			// aggiunge in coda i valori della query string che non sono usati
			if (is_array($addParam) && count($addParam))
			{
				$qs = '';
				foreach($addParam as $k=>$v) {
					$qs .= '&'.$k.'='.urlencode($v);
				}
				$url .= self::$baseUrlParam.trim($qs, '&');
			} else if (is_string($addParam)) {
				$url .= $addParam;
			}
		}

		$url = !empty($url) ? $url : $route;
		return !preg_match('/^(javascript:|http:|https:)/', $url) ? self::$baseUrl.$url : $url;
	}

	private static function _compile()
	{
		$configArray = &self::_getValuesArray();

		// compila il file di routing custom
		$fileName = org_glizy_Paths::getRealPath('APPLICATION', 'config/routing_custom.xml');
		if ( file_exists( $fileName ) )
		{
			$compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Routing');
			$compiledFileName = $compiler->verify($fileName);
			include($compiledFileName);
		}

		// compila il file di routing
		$fileName = org_glizy_Paths::getRealPath('APPLICATION', 'config/routing.xml');
		if ( file_exists( $fileName ) )
		{
			$compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Routing');
			$compiledFileName = $compiler->verify($fileName);
			include($compiledFileName);
		}
	}

	public static function _parseUrl()
	{
		$newParser = __Config::get('glizy.routing.newParser');
		$scriptUrl = self::$requestUrl;
		$scriptUrl = preg_replace('/(.*)\/?\?(.*)?/i', '$1', $scriptUrl);
		$configArray = &self::_getValuesArray();
		$method = strtolower( @$_SERVER['REQUEST_METHOD'] );

		foreach( $configArray as $k => $v )
		{
			if ( !$v[ 'parseUrl' ] || ( $v[ 'method' ] != '' &&  $v[ 'method' ] != $method ) ) continue;
			$urlPattern = $v[ 'urlPattern' ];

			if (preg_match($urlPattern, $scriptUrl, $matches))
			{
				$staticValues = unserialize( $v[ 'staticValues' ] );
				$urlValues = unserialize( $v[ 'urlValues' ] );
				$keys = array_keys($urlValues);

                if ($newParser) {
                    // il codice della versione precedente
                    // da problemi quando il valore del match contiene /
                    // non ricordo il motivo percui viene fatto l'explode del valore
                    // quindi per non rompere la compatibilità ho aggiunto nel config
                    // glizy.routing.newParser per abilitare questo nuovo parsing
                    for ($i=0; $i<count($keys); $i++) {
                        $urlValues[$keys[$i]] = $matches[$i+1];
                    }

                } else {
                    for ($i=0; $i<count($keys); $i++)
                    {
                        $value = explode('/', $matches[$i+1]);
                        if ( $value[0] != "" )
                        {
                            $urlValues[$keys[$i]] = $value[0];
                        }
                        if (count($value)>1)
                        {
                            for ($j=1; $j<count($value); $j++, $j++)
                            {
                                if ($j+1<count($value)) $urlValues[$value[$j]] = $value[$j+1];
                            }
                        }
                    }
                }


				$urlValues[ '__params__' ] = array_values( $urlValues );
				$urlValues[ '__routingName__' ] = $k;
				$urlValues[ '__routingPattern__' ] = $urlPattern;

				__Request::setFromArray(  $urlValues );
				__Request::setFromArray( $staticValues, GLZ_REQUEST_ROUTING);
				break;
			}
		}
	}

	public static function &_getValuesArray()
	{
		// Array associativo (PATH_CODE=>PATH)
		static $_configArray = null;
		if (is_null($_configArray))
		{
			$_configArray = array();
			self::_compile();
		}
		return $_configArray;
	}

	public static function getAllAsArray()
	{
		return self::_getValuesArray();
	}

    public static function destroy()
    {
        $configArray = &self::_getValuesArray();
        $configArray = null;
    }
}

// shortchut version
/**
 * Class __Routing
 */
class __Routing extends org_glizy_Routing
{
}
