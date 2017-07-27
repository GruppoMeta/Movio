<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_Config
 */
class org_glizy_Config
{
    /**
     * @param string $serverName
     */
	public static function init( $serverName='' )
	{
    	$configArray = &org_glizy_Config::_getConfigArray( $serverName );
		$debugErrorLevel = self::get('DEBUG_ERROR_LEVEL');
        if ($debugErrorLevel != '') {
        	$debugErrorLevel = explode(' ', $debugErrorLevel);
        	$level = 0;
        	$lastOp = '';
        	foreach($debugErrorLevel as $v) {
        		if ($v=='&' || $v=='|') {
        			$lastOp = $v;
        		} else if ($v{0}=='~') {
	        		$level = $level &~constant(substr($v, 1));
        		} else if ($lastOp=='|') {
        			$level = $level | constant($v);
        		} else if ($lastOp=='&') {
        			$level = $level & constant($v);
        		} else {
        			$level = $level + constant($v);
        		}
        	}

            error_reporting($level);
        }
	}

    /**
     * @param $code
     * @return mixed|null
     */
	public static function get($code)
	{
		$configArray = &org_glizy_Config::_getConfigArray();
		$value = isset($configArray[$code]) ? $configArray[$code] : NULL;
		if( strpos($value, "{{") !== false )
        {
            preg_match_all( "/\{\{env:([^\{]*)\}\}/U", $value, $resmatch );
			if (count($resmatch)) {
				foreach( $resmatch[1] as $varname)
				{
					list($envName, $envDefaultValue) = explode(',', $varname);
					$envValue = getenv($envName);
					if (($enValue===false || is_null($enValue)) && $envDefaultValue) {
						$envValue = $envDefaultValue;
					}
					$value = str_replace('{{env:'.$varname.'}}', $envValue, $value);
				}
			}

      		preg_match_all( "/\{\{path:([^\{]*)\}\}/U", $value, $resmatch );
			if (count($resmatch)) {
				foreach( $resmatch[1] as $varname)
				{
					$value = str_replace('{{path:'.$varname.'}}', org_glizy_Paths::get( $varname ), $value);
				}
            }

            preg_match_all( "/\{\{([^\{]*)\}\}/U", $value, $resmatch );
			if (count($resmatch)) {
				foreach( $resmatch[1] as $varname)
				{
					$value = str_replace('{{'.$varname.'}}', org_glizy_Config::get( $varname ), $value);
				}
            }
        }

		return $value;
	}

    /**
     * @param $code
     * @param $value
     */
    public static function set($code, $value)
	{
		if ( $value == "true" )
		{
			$value = true;
		}
		else if ( $value == "false" )
		{
			$value = false;
		}

		$configArray = &org_glizy_Config::_getConfigArray();
		$configArray[$code] = $value;

		// if ($code=='CHARSET')
		// {
		// 	org_glizy_Request::removeAll();
		// }
	}

    /**
     * @param  string $code
     * @return boolean
     */
    public function exists($code)
    {
        $configArray = &org_glizy_Config::_getConfigArray();
        return isset($configArray[$code]);
    }

    public static function dump()
	{
		$configArray = &org_glizy_Config::_getConfigArray();
		var_dump($configArray);
	}

    /**
     * @param string $serverName
     * @return array
     */
	public static function &_getConfigArray( $serverName='' )
	{
		// Array associativo (PATH_CODE=>PATH)
		static $_configArray = null;
		if (is_null($_configArray))
		{
			$_configArray = array();
			org_glizy_Config::_parse( $serverName );
		}
		return $_configArray;
	}

	static function getAllAsArray()
	{
		$configArray = &org_glizy_Config::_getConfigArray();
		return $configArray;
	}

    /**
     * @param string $serverName
     * @throws Exception
     */
    function _parse( $serverName='' )
	{
		// imposta i valori di default
		$configArray 						= &org_glizy_Config::_getConfigArray();
		$configArray['DEBUG'] 				= false;
		$configArray['DEBUG_ERROR_LEVEL'] 	= '';
		$configArray['ERROR_DUMP']			= '';
		$configArray['DATASOURCE_MODE'] 	= '';
		$configArray['SESSION_PREFIX'] 		= '';
		$configArray['SESSION_TIMEOUT'] 	= 1800;
		$configArray['DEFAULT_LANGUAGE'] 	= 'en';
		$configArray['DEFAULT_LANGUAGE_ID'] = '1';
		$configArray['glizy.languages.available'] = '{{DEFAULT_LANGUAGE}}';
		$configArray['CHARSET'] 			= 'utf-8';
		$configArray['DB_LAYER'] 			= 'pdo';
		$configArray['DB_TYPE'] 			= 'mysql';
		$configArray['DB_HOST'] 			= '';
		$configArray['DB_NAME'] 			= '';
		$configArray['DB_USER'] 			= '';
		$configArray['DB_PSW'] 				= '';
        $configArray['DB_MYSQL_BUFFERED_QUERY'] = false;
		$configArray['DB_ATTR_PERSISTENT'] = false;
		$configArray['DB_PREFIX'] 			= '';
		$configArray['DB_SOCKET'] 			= '';
		$configArray['SMTP_HOST'] 			= '';
		$configArray['SMTP_PORT'] 			= 25;
		$configArray['SMTP_USER'] 			= '';
		$configArray['SMTP_PSW'] 			= '';
		$configArray['SMTP_SECURE'] 		= '';
		$configArray['SMTP_SENDER'] 		= '';
		$configArray['SMTP_EMAIL'] 			= '';
		$configArray['START_PAGE'] 			= 'HOME';
		$configArray['CACHE_IMAGES'] 		= -1;
		$configArray['CACHE_CODE'] 			= -1;
		$configArray['CACHE_PAGE']			= -1;
        $configArray['ACL_MODE']        	= 'xml';
		$configArray['ACL_CLASS']			= 'org.glizy.application.Acl';
		$configArray['ADM_THUMBNAIL_CROP']	= false;
		$configArray['ADM_THUMBNAIL_CROPPOS']	= 1;
		$configArray['ADM_SITE_MAX_DEPTH']	= NULL;
		$configArray['HIDE_PRIVATE_PAGE']	= true;
		$configArray['APP_NAME'] 			= '';
		$configArray['APP_VERSION'] 		= '';
		$configArray['APP_AUTHOR'] 			= '';
		$configArray['CORE_VERSION'] 		= GLZ_CORE_VERSION;
		$configArray['SEF_URL'] 			= false;
		$configArray['PRESERVE_SCRIPT_NAME']= false;
		$configArray['SITEMAP'] 			= 'config/siteMap.xml';
		$configArray['REMEMBER_PAGEID']		= false;
		$configArray['PSW_METHOD']			= 'MD5';
		$configArray['USER_LOG']			= false;
		$configArray['JS_COMPRESS']			= false;
		$configArray['JPG_COMPRESSION']		= 80;
		$configArray['THUMB_WIDTH']			= 150;
		$configArray['THUMB_HEIGHT']		= 150;
		$configArray['THUMB_SMALL_WIDTH']	= 50;
		$configArray['THUMB_SMALL_HEIGHT']	= 50;
		$configArray['IMG_LIST_WIDTH']		= 100;
		$configArray['IMG_LIST_HEIGHT']		= 100;
		$configArray['IMG_WIDTH_ZOOM'] 		= 800;
		$configArray['IMG_HEIGHT_ZOOM'] 	= 600;
		$configArray['IMG_WIDTH'] 			= 200;
		$configArray['IMG_HEIGHT'] 			= 200;
		$configArray['LOG_FILE']			= '';
		$configArray['LOG_LEVEL']			= GLZ_LOG_ALL;
		$configArray['STATIC_FOLDER']		= NULL;
		$configArray['TEMPLATE_FOLDER']		= NULL;
		$configArray['FORM_ITEM_TEMPLATE']	= '<div class="formItem">##FORM_LABEL####FORM_ITEM##<br /></div>';
		$configArray['FORM_ITEM_RIGHT_LABEL_TEMPLATE']	= '<div class="formItemRigthLabel">##FORM_LABEL####FORM_ITEM##<br /></div>';
		$configArray['FORM_ITEM_HIDEN_TEMPLATE'] = '<div class="formItemHidden">##FORM_ITEM##</div>';
		$configArray['SITE_ID']				= '{{glizy.multisite.id}}';
		$configArray['ALLOW_MODE_OVERRIDE']	= false;
		$configArray['USER_DEFAULT_ACTIVE_STATE'] = 0;
		$configArray['USER_DEFAULT_USERGROUP'] = 4;
		$configArray['MULTILANGUAGE_ENABLED'] 	= false;
		$configArray['ACL_ENABLED'] 		= false;
		$configArray['CATEGORY_ENABLED'] 	= false;
		$configArray['SANITIZE_URL'] 		= true;
		$configArray['DEFAULT_SKIN_TYPE'] 	= 'PHPTAL';

		$configArray['AJAX_SKIP_DECODE'] 		= true;
		$configArray['GLIZY_ADD_CORE_JS'] 		= true;
		$configArray['GLIZY_ADD_JS_LIB'] 		= true;
		$configArray['GLIZY_ADD_JQUERY_JS'] 	= true;
		$configArray['GLIZY_ADD_JQUERYUI_JS'] 	= false;
		$configArray['GLIZY_ADD_VALIDATE_JS'] 	= true;
		$configArray['GLIZY_JQUERY'] 			= 'jquery-1.7.2.min.js';
		$configArray['GLIZY_JQUERYUI'] 			= 'jquery-ui-1.8.14.custom.min.js';
		$configArray['GLIZY_JQUERYUI_THEME'] 	= 'ui-lightness/jquery-ui-1.8.14.custom.css';
		$configArray['TINY_MCE_DEF_PLUGINS'] 	= 'inlinepopups,paste,directionality,xhtmlxtras,fullscreen,GLZ_link,GLZ_image';
		$configArray['TINY_MCE_PLUGINS'] 	= '';
		$configArray['TINY_MCE_BUTTONS1'] 	= 'bold,italic,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,blockquote';
		$configArray['TINY_MCE_BUTTONS2'] 	= 'formatselect,|,undo,redo,pastetext,pasteword,removeformat,|,link,unlink,anchor,image,hr,charmap,|,code,fullscreen';
		$configArray['TINY_MCE_BUTTONS3'] 	= '';
        $configArray['TINY_MCE_STYLES']     = '[]';
        $configArray['TINY_MCE_IMG_STYLES']     = '';
        $configArray['TINY_MCE_IMG_SIZES']  = '';
		$configArray['TINY_MCE_TEMPLATES'] 	= '';
		$configArray['TINY_MCE_INTERNAL_LINK'] 	= true;
		$configArray['TINY_MCE_ALLOW_LINK_TARGET'] 	= false;
		$configArray['COLORBOX_SLIDESHOWAUTO'] 	= true;
		$configArray['BASE_REGISTRY_PATH'] 			= 'org/glizy';
		$configArray['REGISTRY_TEMPLATE_NAME'] 		= '{{BASE_REGISTRY_PATH}}/templateName';
		$configArray['REGISTRY_TEMPLATE_VALUES'] 	= '{{BASE_REGISTRY_PATH}}/templateValues/';
		$configArray['REGISTRY_SITE_PROP'] 			= '{{BASE_REGISTRY_PATH}}/siteProp/';
		$configArray['REGISTRY_METANAVIGATION'] 	= '{{BASE_REGISTRY_PATH}}/metanavigation/';
		$configArray['glizy.media.imageMagick'] 			= false;
        $configArray['glizy.media.image.unsharpMask']       = false;
		$configArray['glizy.routing.newParser'] 		    = true;
		$configArray['glizy.form.cssClass'] 		= '';
		$configArray['glizy.formElement.cssClass'] 	= '';
		$configArray['glizy.formElement.cssClassLabel'] 	= '';
		$configArray['glizy.formElement.admCssClass'] 		= '';
        $configArray['glizy.formButton.cssClass']  = '';
		$configArray['glizy.datagrid.action.editCssClass'] 		= 'icon-pencil btn-icon';
		$configArray['glizy.datagrid.action.editDraftCssClass'] = 'icon-edit btn-icon';
		$configArray['glizy.datagrid.action.deleteCssClass'] 	= 'icon-trash btn-icon';
		$configArray['glizy.datagrid.action.hideCssClass'] 		= 'icon-eye-close btn-icon';
        $configArray['glizy.datagrid.action.showCssClass']      = 'icon-eye-open btn-icon';
        $configArray['glizy.datagrid.checkbox.on']          = 'icon-check btn-icon';
		$configArray['glizy.datagrid.checkbox.off'] 		= 'icon-check-empty btn-icon';
		$configArray['glizy.authentication'] 		= 'org.glizy.authentication.Database';
		$configArray['glizy.dataAccess.schemaManager.cacheLife'] 		= 36000;
		$configArray['glizy.dataAccess.serializationMode'] 				= 'json';
		$configArray['glizy.dataAccess.document.enableComment'] 		= false;
		$configArray['glizy.session.store'] 		= '';
		$configArray['glizy.dataAccess.validate'] = true;
        $configArray['glizy.multisite.sitename'] = '';
		$configArray['glizy.multisite.id'] = 0;

        if (!$serverName) {
            $configFileName = org_glizy_Paths::get('APPLICATION').'config/config.xml';
            $tempName = array(
                isset($_SERVER['GLIZY_APPNAME']) ? $_SERVER['GLIZY_APPNAME'] : '',
                getenv('GLIZY_SERVER_NAME'),
                is_null( $_SERVER["SERVER_NAME"] ) ? 'console' : $_SERVER["SERVER_NAME"]
            );
            foreach($tempName as $serverName) {
                if ($serverName) {
                    $tempConfigFileName = org_glizy_Paths::get('APPLICATION').'config/config_'.$serverName.'.xml';
                    if (file_exists($tempConfigFileName)) {
                        $configFileName = $tempConfigFileName;
                        break;
                    }
                }
            }
        } else {
            $configFileName = org_glizy_Paths::get('APPLICATION').'config/config_'.$serverName.'.xml';
        }

		if (!file_exists($configFileName)) {
            $configFileName = org_glizy_Paths::get('APPLICATION').'config/config.xml';
            if (!file_exists($configFileName)) {
                throw new Exception('Config file not found.');
            }
		}

        /** @var $compiler org_glizy_compilers_Config  */
		$compiler 		= org_glizy_ObjectFactory::createObject('org.glizy.compilers.Config');
		$compiledConfigFileName = $compiler->verify($configFileName);

		// TODO
		// controllare errore
		include($compiledConfigFileName);

		if (!empty($configArray['STATIC_FOLDER']))
		{
			org_glizy_Paths::set('APPLICATION_STATIC', org_glizy_Config::get( 'STATIC_FOLDER' ) );
		}
		if (!empty($configArray['TEMPLATE_FOLDER']))
		{
			org_glizy_Paths::set('APPLICATION_TEMPLATE', org_glizy_Config::get('TEMPLATE_FOLDER') );
		}

		if ( $configArray['ALLOW_MODE_OVERRIDE'] && isset( $_GET['mode'] ) )
		{
			org_glizy_Config::setMode( $_GET['mode'] );
		}

		if (isset($configArray['glizy.config.mode'])) {
			org_glizy_Config::setMode($configArray['glizy.config.mode']);
		}

		define( 'GLZ_CHARSET', __Config::get( 'CHARSET' ) );
	}

    /**
     * @param $modeName
     */
	function setMode( $modeName )
	{
		$configArray = &org_glizy_Config::_getConfigArray();
		if ( isset( $configArray[ '__modes__'][ $modeName ] ) )
		{
			foreach( $configArray[ '__modes__'][ $modeName ] as $k => $v )
			{
				$configArray[ $k ] = $v;
			}
		}
		else
		{
			// TODO: in modalit� debug visualzzare un warning
		}
	}

    static function destroy()
    {
        $configArray = &self::_getConfigArray();
        $configArray = null;
    }
}

// shortcut version
/**
 * Class __Config
 */
class __Config extends org_glizy_Config
{
}