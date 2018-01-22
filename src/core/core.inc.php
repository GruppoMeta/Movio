<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

// ini_set( "display_errors", "on" );
// ini_set( "display_startup_errors", "on" );

define('GLZ_CORE_VERSION', '1.6.0');


// directory contente tutti i file del core
define('GLZ_LOADED', true);
define('GLZ_CORE_DIR', realpath(dirname(__FILE__)).'/');

// directory contente tutti i file del core
define('GLZ_CLASSES_DIR', realpath(dirname(__FILE__)).'/classes/');

// directory contentente le librerie
define('GLZ_LIBS_DIR', realpath(dirname(__FILE__)).'/libs/');

define('GLZ_COMPILER_NEWLINE', ";\n");
define('GLZ_COMPILER_NEWLINE2', "\n");

define('GLZ_SCRIPNAME', $_SERVER['PHP_SELF']);
define('GLZ_ERR_EMPTY_APP_PATH', 'The application path can\'t be empty');

// eventi
define('GLZ_EVT_BEFORE_CREATE_PAGE', 'beforeCreatePage');
define('GLZ_EVT_START_PROCESS', 'onProcessStart');
define('GLZ_EVT_END_PROCESS', 'onProcessEnd');
define('GLZ_EVT_START_RENDER', 'onRenderStart');
define('GLZ_EVT_END_RENDER', 'onRenderEnd');
define('GLZ_EVT_CALL_CONTROLLER', 'onCallController');
define('GLZ_EVT_START_COMPILE_ROUTING', 'startCompileRouting');
define('GLZ_EVT_LISTENER_COMPILE_ROUTING', 'listenerCompileRouting');
define('GLZ_EVT_USERLOGIN', 'login');
define('GLZ_EVT_USERLOGOUT', 'onLogout');
define('GLZ_EVT_AR_UPDATE', 'update');
define('GLZ_EVT_AR_UPDATE_PRE', 'preUpdate');
define('GLZ_EVT_AR_INSERT', 'insert');
define('GLZ_EVT_AR_INSERT_PRE', 'preInsert');
define('GLZ_EVT_AR_DELETE', 'delete');
define('GLZ_EVT_SITEMAP_UPDATE', 'siteMapUpdate');
define('GLZ_EVT_BREADCRUMBS_ADD', 'onBreadcrumbsAdd');
define('GLZ_EVT_BREADCRUMBS_UPDATE', 'onBreadcrumbsUpdate');
define('GLZ_EVT_PAGETITLE_UPDATE', 'onPageTitleUpdate');
define('GLZ_EVT_CACHE_CLEAN', 'cacheClean');
define('GLZ_EVT_DUMP_EXCEPTION', 'onDumpException');
define('GLZ_EVT_DUMP_404', 'onDump404');

if (!defined('GLZ_LOG_EVENT')) 		define('GLZ_LOG_EVENT', 'logByEvent');
if (!defined('GLZ_LOG_DEBUG')) 		define('GLZ_LOG_DEBUG', 1);
if (!defined('GLZ_LOG_SYSTEM')) 	define('GLZ_LOG_SYSTEM', 2);
if (!defined('GLZ_LOG_INFO')) 		define('GLZ_LOG_INFO', 4);
if (!defined('GLZ_LOG_WARNING')) 	define('GLZ_LOG_WARNING', 8);
if (!defined('GLZ_LOG_ERROR')) 		define('GLZ_LOG_ERROR', 16);
if (!defined('GLZ_LOG_FATAL')) 		define('GLZ_LOG_FATAL', 32);
if (!defined('GLZ_LOG_ALL')) 		define('GLZ_LOG_ALL', 255);

if (!defined('E_STRICT')) define('E_STRICT', 2048);
if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);


date_default_timezone_set( 'Europe/Rome' );

set_include_path('./' . PATH_SEPARATOR . get_include_path());

$errorlevel=error_reporting();
error_reporting( $errorlevel & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT );

if (!defined('GLZ_TESTS')) {
	GlizyErrorHandler::register();

}

if ( phpversion() < 5)
{
	glz_import('org.glizy.Exception');
	org_glizy_Exception::show( 500, "GLIZY work only with php 5", "", "");
}

// import all the libraries
glz_require_once_dir(GLZ_LIBS_DIR);

// import the main Object class
glz_import('org.glizy.GlizyObject');

// import the needed classes for start-up a application
glz_import('org.glizy.locale.Locale');
glz_import('org.glizy.*');
glz_import('org.glizy.helpers.Link');

GlizyClassLoader::register();

function glz_defineBaseHost()
{
	if ( !defined( 'GLZ_HOST' ) )
	{
		$host = __Config::get( 'GLZ_HOST', '' );
		if ( !$host )
		{
			$protocol = @$_SERVER["HTTPS"] === true || @$_SERVER["HTTPS"] === 'on' ? 'https://' : 'http://';
			$host = $protocol.$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
			$host = substr( $host, 0, strrpos( $host, '/' ) );
		} else {
			__Config::set( 'GLZ_HOST', $host );
		}

		define('GLZ_HOST', $host);
	} else {
        $host = GLZ_HOST;
    }

	if ( !defined( 'GLZ_HOST_ROOT' ) )
	{
		define('GLZ_HOST_ROOT', str_replace( '/admin', '', $host ) );
	}
}

/**
 * @param        $dir
 * @param string $default
 */
function glz_require_once_dir($dir, $default='')
{
	$dir = rtrim($dir, "*");
	if ($default!='')
	{
		if (!is_array($default))
		{
			$default = array($default);
		}

		foreach ($default as $value)
		{
			$retArray[] = "$dir/$value";
			require_once("$dir/$value");
		}
	}

	if ($dir_handle = @opendir($dir))
	{
		while ($file_name = readdir($dir_handle))
		{
			if ($file_name!="." &&
				$file_name!=".." &&
				$file_name!=$default &&
				!is_dir("$dir/$file_name") &&
				substr($file_name, -3)=='php' &&
				strpos($file_name, '._') === false )
			{
				require_once("$dir/$file_name");
			}
		}
		closedir($dir_handle);
	}
	else
	{
		echo "Could not open directory $dir";
	}
}


/**
 * @param        $classPath
 * @param array  $classToReadFirst
 * @param string $path
 *
 * @return bool
 */
function glz_import($classPath, $classToReadFirst=array(), $path='')
{
	static $loadedClass = array();
	$classPath 			= str_replace('.', '/', $classPath);
	$classPath 			= rtrim($classPath, '*');
	$origClassPath 		= $classPath;

	if (in_array($classPath, $loadedClass)) return true;

	if (empty($path))
	{
		if (class_exists('org_glizy_Paths') && !is_null(org_glizy_Paths::get('APPLICATION_CLASSES')))
		{
			$path = NULL;
			$searchPath = org_glizy_Paths::getClassSearchPath();
			foreach($searchPath as $p)
			{
				$path = $p.$classPath;
				if (file_exists($p.$classPath) || file_exists($p.$classPath.'.php'))
				{
					$path = $p;
					break;
				}
			}

			if (is_null($path))
			{
				// TODO
				// visualizzare errore
				echo "errore";
			}
		}
		else
		{
			$path = realpath(dirname(__FILE__)).'/classes/';
		}
	}

	if (substr($classPath, -1, 1)=='/' || $classPath=='')
	{
		// import all file in the folder
		$classPath = rtrim($classPath, '/');
		glz_require_once_dir($path.$classPath, $classToReadFirst);
		glz_loadLocale( $classPath );
	}
	else
	{
		// import a single file
		if (file_exists($path.$classPath.'.php'))
		{
			require_once($path.$classPath.'.php');
		}
		else
		{
			return false;
		}
	}

	$loadedClass[] = $origClassPath;
	return true;
}

/**
 * @param $classPath
 */
function glz_loadLocale( $classPath )
{
	static $loadedLocale = array();

	if (class_exists('org_glizy_ObjectValues'))
	{
		if (in_array($classPath, $loadedLocale)) return true;

		$loadedLocale[] = $classPath;
		$classPath = str_replace('.', '/', $classPath);
		$classPath = rtrim($classPath, '*');
        /** @var org_glizy_application_Application $application */
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		if (is_object($application) )
		{
			$language = $application->getLanguage();
			$searchPath = org_glizy_Paths::getClassSearchPath();
			foreach($searchPath as $p)
			{
				if (glz_loadLocaleReal($p.$classPath, $language)) {
					break;
				}
			}
		}
	}
}

/**
 * @param  string $path
 * @param  string $language
 * @return void
 */
function glz_loadLocaleReal( $path, $language )
{
	$pathLang = $path.'/locale/'.$language.'.php';
	$pathEn = $path.'/locale/en.php';
	if ( file_exists($pathLang) ) {
		require( $pathLang );
		return true;
	} else if ( file_exists($pathEn) ) {
		require( $pathEn );
		return true;
	}

	return false;
}


/**
 * @param string $classPath
 * @param boolean $dotPaths
 * @param boolean $onlyFile
 *
 * @return null|string
 */
function glz_findClassPath($classPath, $dotPaths=true, $onlyFile=false)
{
	if (!class_exists('org_glizy_Paths')) return NULL;
	$extensionsToCheck = array('', '.xml', '.php');
	$classPath = $dotPaths ? str_replace(array('.', '*'), '/', $classPath) : $classPath;

	$path = NULL;
	$searchPath = org_glizy_Paths::getClassSearchPath();
	foreach($searchPath as $p) {
		foreach ($extensionsToCheck as $value) {
			$fileToCheck = $p.$classPath.$value;
			if (file_exists($fileToCheck) && (!$onlyFile || ($onlyFile && !is_dir($fileToCheck)))) {
				return $fileToCheck;
			}
		}
	}
	return $path;
}

/**
 * @param $path
 *
 * @return null
 */
function glz_importLib($path)
{
	if (!class_exists('org_glizy_Paths')) return NULL;
	require_once(org_glizy_Paths::get('CORE_LIBS').$path);
}

/**
 * @param $path
 *
 * @return null
 */
function glz_importApplicationLib($path)
{
	if (!class_exists('org_glizy_Paths')) return NULL;
	require_once(org_glizy_Paths::get('APPLICATION_LIBS').$path);
}

/**
 * @param $output
 *
 * @return mixed
 */
function glz_encodeOutput($output)
{
	if (!$output) return $output;
	if (is_array($output))
	{
		return glz_encodeOutputArray($output);
	}
	else return glz_htmlentities($output);
}

/**
 * @param $output
 *
 * @return mixed
 */
function glz_encodeOutputArray($output)
{
	$keys = array_keys($output);
	$count = count($output);
	for ($i = 0; $i < $count; $i++)
	{
		if (is_array($output[$keys[$i]]))
		{
			$output[$keys[$i]] = glz_encodeOutputArray($output[$keys[$i]]);
		}
		else
		{
			$output[$keys[$i]] = glz_htmlentities($output[$keys[$i]]);
		}
	}
	return $output;
}

/**
 * @param $text
 *
 * @return mixed
 */
function glz_htmlentities( $text )
{
	if (!$text) return $text;
	$tempText = @htmlentities( $text, ENT_COMPAT | ENT_SUBSTITUTE , GLZ_CHARSET );
	if (!$tempText) {
		$tempText = @htmlentities( $text, ENT_COMPAT);
	}

	if ($tempText) {
		$text = $tempText;
	}

	return str_replace('&amp;#', '&#', $text);
}

/**
 * @param $psw
 *
 * @return string
 */
function glz_password($psw)
{
	switch (org_glizy_config::get('PSW_METHOD'))
	{
		case 'MD5':
			return md5($psw);
        case 'SHA1':
            return sha1($psw);
        case 'SHA1OFMD5':
            return sha1(md5($psw));
		default:
			return $psw;
	}
}

/**
 * @param $name
 *
 * @return mixed
 */
function glz_basename($name)
{
	return preg_replace('/.php/', '', basename($name));
}


/**
 * @param $value
 *
 * @return bool
 */
function glz_empty($value)
{
	if ( strpos( $value, '<img') !== false ) return false;
	$value = is_string($value) ? strip_tags($value) : $value;
	return empty($value);
}
/**
 * @param $value
 *
 * @return mixed
 */
function glz_localeDate2ISO( $value )
{
	if (!is_string($value)) return $value;
	$type = strlen( $value ) <= 10 ? 'date' : 'datetime';
	$reg = __T( $type == 'date' ? 'GLZ_DATE_TOISO_REGEXP' : 'GLZ_DATETIME_TOTIME_REGEXP' );
	if ( is_array( $reg ) && preg_match( $reg[0], $value ) )
	{
		$value = preg_replace( $reg[0], $reg[1], $value );
	}
	return $value;
}

/**
 * @param $value
 *
 * @return mixed
 */
function glz_localeDate2default( $value )
{
	if (!is_string($value)) return $value;
	$type = strlen( $value ) <= 10 ? 'date' : 'datetime';
	$reg = __T( $type == 'date' ? 'GLZ_DATE_TOTIME_REGEXP' : 'GLZ_DATETIME_TOTIME_REGEXP' );
	if ( is_array( $reg ) && preg_match( $reg[0], $value ) )
	{
		$value = preg_replace( $reg[0], $reg[1], $value );
	}
	return $value;
}

/**
 * @param string $format
 * @param $value
 *
 * @return bool|string
 */
function glz_defaultDate2locale( $format, $value )
{
    list( $d, $t ) = explode( ' ', $value . ' ');
	list( $y, $m, $day ) = explode( '-', $d );
    if (!$t) $t = '00:00:00';
	list( $hh, $mm, $ss ) = explode( ':', $t );
	return date( $format, mktime( intval( $hh ), intval( $mm ), intval( $ss ), intval( $m ), intval( $day ), intval( $y ) ) );
}


/*
	code borrowed By Stewart Rosenberger
	http://www.stewartspeak.com/headings/
	convert embedded, javascript unicode characters into embedded HTML
	entities. (e.g. '%u2018' => '&#8216;'). returns the converted string.
*/

/*
APL Quote	"	&quot;	&#34;	&#x22;
Double Quote (left) 	“	&ldquo;	&#8220;	&#x201C;
Double Quote (right)	”	&rdquo;	&#8221;	&#x201D;
Single Quote (left)	‘	&lsquo;	&#8216;	&#x2018;
Single Quote (right)	’	&rsquo;	&#8217;	&#x2019;
Prime	'	&prime;	&#8242;	&#x2032;
Double Prime	?	&Prime;	&#8243;	&#x2033;
Em Dash		&mdash;	&#8212;	&#x2013;
En Dash	–	&ndash;	&#8211;	&#x2013;
Minus	-	&minus;	&#8722;	&#x2212;
Multiplication Symbol	×	&times;	&#215;	&#xD7;
Division Symbol	÷	&divide;	&#247;	&#xF7;
Ellipsis	…	&hellip;	&#8230;	&#x2026;
Copyright Symbol	©	&copy;	&#169;	&#xA9;
Trademark	™	&trade;	&#8482;	&#x2122;
Registered Trademark	®	&reg;	&#174;	&#xAE;

*/

/**
 * @param string $text
 *
 * @return string mixed
 */
function javascript_to_html($text)
{
	$matches = null ;

	preg_match_all('/%u([0-9A-F]{4})/i',$text,$matches) ;
	if(!empty($matches))
	{
		$convTable = array(
							'2026' => "…",
							'201C' => "“",
							'201D' => "”",
							'2018' => "‘",
							'2019' => "’",
							'2032' => "'",
							'2013' => "—",
							'2212' => "–"
						);
		for($i=0;$i<sizeof($matches[0]);$i++)
		{
			if (isset($convTable[$matches[1][$i]]))
			{
				$text = str_replace($matches[0][$i], $convTable[$matches[1][$i]], $text);
			}
			else
			{
				$text = str_replace($matches[0][$i], '&#'.hexdec($matches[1][$i]).';',$text);
			}
		}
	}

	preg_match_all('/\&#([0-9A-F]{4});/i',$text,$matches) ;
	if(!empty($matches))
	{
		$convTable = array(
							'8230' => "…",
							'8220' => "“",
							'8221' => "”",
							'8216' => "‘",
							'8217' => "’",
							'8242' => "'",
							'8211' => "—",
							'8212' => "-",
							'8722' => "–",
							'8364' => "€"
						);
		for($i=0;$i<sizeof($matches[0]);$i++)
		{
			if (isset($convTable[$matches[1][$i]]))
			{
				$text = str_replace($matches[0][$i], $convTable[$matches[1][$i]], $text);
			}
		}
	}
	return $text;
}

/**
 * @param int $len
 *
 * @return string
 */
function glz_makePass( $len = 7)
{
	$pass = "";
	$salt = "abchefghjkmnpqrstuvwxyz0123456789";
	srand((double)microtime()*1000000);
	$i = 0;
	while ($i <= $len )
	{
		$num = rand() % 33;
		$tmp = substr($salt, $num, 1);
		$pass = $pass . $tmp;
		$i++;
	}
	return $pass;
}

/**
 * @param int  $len
 * @param null $id
 *
 * @return string
 */
function glz_makeConfirmCode( $len=7, $id=NULL )
{
       $convTable = array( 0 => "f", 1 => "x", 2 => "r", 3 => "i", 4 => "d",5 => "g", 6 => "n", 7 => "k", 8 => "h", 9 => "o" );

       // creazione password normale
       $code = glz_makePass( $len );
       // codifica di cesare per id
       $s_id = sprintf("%d",$id);
       for( $i=0; $i<strlen($id); $i++ )
       {
               // accodiamo la conversione dell'id al codice
               $code .= $convTable[ $s_id{$i} ];
       }
       return $code;
}

/**
 * @param        $str
 * @param int    $maxlen
 * @param string $elli
 * @param int    $maxoverflow
 *
 * @return string
 */
function glz_strtrim($str, $maxlen=200, $elli='...', $maxoverflow=15)
{
	$str = strip_tags( $str );
	if ( strlen($str) > $maxlen)
	{
		$output = NULL;
		$body = explode(" ", $str);
		$body_count = count($body);

		$i=0;
		do {
			$output .= $body[$i]." ";
			$thisLen = strlen($output);
			$cycle = ($thisLen < $maxlen && $i < $body_count-1 && ($thisLen+strlen($body[$i+1])) < $maxlen+$maxoverflow?true:false);
			$i++;
		} while ($cycle);
		return $output.$elli;
	}
	else return $str;
}

/**
 * @return string
 */
function glz_hostName()
{
	return GLZ_HOST.'/';
}

/**
 * @param $string
 *
 * @return string
 */
function glz_htmlWithUnicodeToUtf8( $string )
{
	return utf8_encode( glz_htmlWithUnicodeDecode( $string ) );
}

/**
 * @param $string
 *
 * @return string
 */
function glz_htmlWithUnicodeDecode( $string )
{
	return html_entity_decode( preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $string ), ENT_NOQUOTES, 'UTF-8');
}

/**
 * @param $title
 *
 * @return string
 */
function glz_sanitizeUrlTitle($title, $force=false) {
	if (!$title) return $title;
	if ( __Config::get( 'SANITIZE_URL' ) || $force )
	{
		$title = glz_slugify($title, true);
	} else {
		$title = str_replace(' ', '%20', $title);
	}

	return $title;
}

/**
 * http://stackoverflow.com/questions/10152894/php-replacing-special-characters-like-%C3%A0-a-%C3%A8-e
 * @param $title
 * @param $strict
 *
 * @return string
 */
function glz_slugify($text, $strict = false) {
	if ($text) {
	    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	    // replace non letter or digits by -
	    $text = preg_replace('~[^\\pL\d.]+~u', '-', $text);

	    // trim
	    $text = trim($text, '-');
	    setlocale(LC_CTYPE, 'en_GB.utf8');
	    // transliterate
	    if (function_exists('iconv')) {
	       $transliterateText = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	       if ($transliterateText) {
	       	// in some *nix iconv//TRANSLIT can fail
	       	$text = $transliterateText;
	       }
	    }

	    // lowercase
	    $text = strtolower($text);
	    // remove unwanted characters
	    $text = preg_replace('~[^-\w.]+~', '', $text);
	    if (empty($text)) {
	       return 'empty_$';
	    }
	    if ($strict) {
	        $text = str_replace(".", "_", $text);
	    }
	}
    return $text;
}

/**
 * @param string $text
 * @param bool $html
 *
 * @return string
 */
function glz_stringToJs($text, $html=false) {
	if ($html) {
		$text = str_replace(array("\n","\r"), '', $text);
	}
	$text = addslashes($text);
	return $text;
}

function glz_closeGlizy()
{
    GlizyClassLoader::unregister();
    GlizyErrorHandler::unregister();
}

function glz_classNSToClassName($classNameSpace)
{
	return str_replace('.', '_', $classNameSpace);
}

function glz_maybeJsonDecode($string, $inArray) {
	$result = $string;
	if (is_string($string)) {
   		$json = json_decode($string, $inArray);
   		if ((is_object($json) || is_array($json)) && json_last_error() === JSON_ERROR_NONE) {
   			$result = $json;
   		}
   	}
   	return $result;
}

function glz_nestedCachePath($filename, $nestLevel=3, $path='', $prefix='')
{
    $nestLevel = max(intval($nestLevel), 0);

    if ($nestLevel>0) {
        $hash = md5($filename);
        for ($i=0 ; $i<$nestLevel; $i++) {
            $path = $path.$prefix.substr($hash, 0, $i + 1) . '/';
        }
    }

    return $path;
}



function dd($var)
{
	array_map(function ($x) { var_dump($x); }, func_get_args());
	die;
}

if ( !function_exists( "stripos" ) )
{
    /**
     * @param $str
     * @param $needle
     *
     * @return int
     */
	function stripos($str,$needle)
	{
		return strpos(strtolower($str),strtolower($needle));
	}
}

/**
 * Class GlizyClassLoader
 */
class GlizyClassLoader {
   	private static $instance = null;
   	public $libMap = array();

	public static function register()
    {
        if (!self::$instance) {
            self::$instance = new GlizyClassLoader();
        }
        spl_autoload_register(array(self::$instance, 'loadClass'));
    }

    public static function unregister()
    {
        spl_autoload_unregister(array(self::$instance, 'loadClass'));
    }

    /**
     * @param string $name
     * @param string $path
     */
    public static function addLib($name, $path)
    {
    	self::$instance->libMap[$name] = $path;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public function loadClass($className)
    {
    	foreach($this->libMap as $name=>$path) {
    		if (strpos($className, $name)===0) {
    			$fileName = str_replace(array($name.'\\', '\\'), array('/', '/'), $className).'.php';
	            require_once($path.$fileName);
	            return true;
    		}
    	}

        $className = str_replace( '_', '.', $className );
        return glz_import( $className );
    }
}

/**
 * Class GlizyErrorHandler
 */
class GlizyErrorHandler {
    private static $isRegistred = false;
    private static $instance = null;

    /**
     *
     */
    function __construct()
    {
        set_error_handler(array($this, 'onErrorHandler'), E_ALL);
        set_exception_handler(array($this, 'onExceptionHandler'));
        register_shutdown_function(array($this, 'onShutdownFunction'));

		glz_import('org.glizy.Exception');
    }

    public static function register()
    {
        if (!self::$instance) {
            self::$instance = new GlizyErrorHandler();
        }
        self::$isRegistred = true;
    }

    public static function unregister()
    {
        self::$isRegistred = false;
        restore_exception_handler();
        restore_error_handler();
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     */
	public function onErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!self::$isRegistred) return;
		$errorlevel=error_reporting();
		if ($errorlevel&$errno && !($errno&E_STRICT))
		{
			$this->sendLog($errno, $errstr, $errfile, $errline);
			org_glizy_Exception::show($errno, $errstr, $errfile, $errline);
		}
	}

    /**
     * @param Exception $exception
     */
	public function onExceptionHandler($exception)
	{
		if (!self::$isRegistred) return;
		$this->sendLog($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
		org_glizy_Exception::show($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
	}


	public function onShutdownFunction() {
		if (!self::$isRegistred) return;
		$error = error_get_last();
		if ($error['type'] == 1) {
			$this->sendLog($error['type'], $error['message'], $error['file'], $error['line']);
			org_glizy_Exception::show($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	private function sendLog($errno, $errstr, $errfile, $errline)
	{
		if (class_exists('org_glizy_Config')) {
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
	        $e['file']       = $errfile;
	        $e['line']       = $errline;
	        $e['stacktrace'] = array_slice(debug_backtrace(), 2);
	        $eventInfo = array( 'type' => GLZ_EVT_DUMP_EXCEPTION,
	                            'data' => array(
	                                'level' => GLZ_LOG_FATAL,
	                                'group' => 'GLZ_E_500',
	                                'message' => $e
	                            ));
	        $evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', null, $eventInfo );
	        org_glizy_events_EventDispatcher::dispatchEvent( $evt );
	    }
	}
}
