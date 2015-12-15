<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_Config extends org_glizy_compilers_Compiler
{
	var $_config;
	function compile($options)
	{
		$this->initOutput();

		// esegue il parsing del file di configurazione
		$this->_config = array();
		$this->_modes = '$configArray[\'__modes__\'] = array()'.GLZ_COMPILER_NEWLINE;
		$this->_compileXml($this->_fileName);

		foreach ($this->_config as $name=>$value)
		{
			$this->output .= '$configArray[\''.$name.'\'] = '.$value.GLZ_COMPILER_NEWLINE;
		}
		$this->output .= $this->_modes;

		return $this->save();
	}

	function _compileXml($fileName)
	{
		$dirPath = dirname($fileName).'/';
		$xml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$xml->load( $fileName );
		foreach( $xml->documentElement->childNodes as $nc )
		{
			$this->_compileXmlNode( $nc, $dirPath );
		}
	}

	function _compileXmlNode(&$node, $dirPath)
	{
		switch ( strtolower( $node->nodeName ) )
		{
			case 'glz:import':
			case 'import':
				$appName = isset($_SERVER['GLIZY_APPNAME']) ? $_SERVER['GLIZY_APPNAME'] : '';
				$envName = getenv('GLIZY_SERVER_NAME');
                $serverName = is_null( $_SERVER["SERVER_NAME"] ) ? (($appName ? :$envName) ? :'console') : $_SERVER["SERVER_NAME"];
				$src = str_replace('##HOST##', $serverName, $node->getAttribute('src'));

				if ($src=='##APPLICATION_TO_ADMIN##') {
					$src = '../../'.org_glizy_Paths::get('APPLICATION_TO_ADMIN').'config/';

                    $configName = '';
    				if (isset($_SERVER['GLIZY_APPNAME'])) {
						$serverName = $_SERVER['GLIZY_APPNAME'];
						$configName = 'config_'.$serverName.'.xml';
						if ( !file_exists( realpath($dirPath.$src.$configName) ) ) {
                            $configName = '';
						}
					}

					if (!$configName) {
						$configName = 'config_'.$serverName.'.xml';
					}

					if ( !file_exists( realpath($dirPath.$src.$configName) ) )
					{
						$configName = 'config.xml';
					}
					$src .= $configName;
				}

				$this->_compileXml(realpath($dirPath.$src));
				break;

			case 'glz:param':
			case 'param':
				$name 	= $node->getAttribute('name');
				$value 	= $node->hasAttribute('value') ? $node->getAttribute('value') : $node->firstChild->nodeValue;
				$value = str_replace('##ROOT##', org_glizy_Paths::get('ROOT'), $value);

				if ($value=="false") $value = false;
				else if($value=="true") $value = true;

				$this->_config[$name] = $value;

				if (gettype($value)=='string')
				{
					$this->_config[$name] = '\''.addcslashes($value, '\'').'\'';
				}
				else
				{
					$this->_config[$name] = $value ? 'true' : 'false';
				}
				break;
			case 'glz:configmode':
			case 'configmode':
				$modeName 	= $node->getAttribute('name');
				$tempConfig = $this->_config;
				$this->_config = array();
				for ($i=0; $i<count($node->childNodes); $i++)
				{
					$this->_compileXmlNode($node->childNodes[$i], $dirPath);
				}

				$this->_modes .= '$configArray[\'__modes__\'][\''.$modeName.'\'] = array()'.GLZ_COMPILER_NEWLINE;
				foreach ($this->_config as $name=>$value)
				{
					$this->_modes .= '$configArray[\'__modes__\'][\''.$modeName.'\'][\''.$name.'\'] = '.$value.GLZ_COMPILER_NEWLINE;
				}
				$this->_config = $tempConfig;
				if ( $node->getAttribute('default') == "true" )
				{
					$this->_modes .= 'org_glizy_Config::setMode(\''.$modeName.'\')'.GLZ_COMPILER_NEWLINE;
				}

				break;
		}


	}

	/**
	*	Verifica se il file � compilato, in casi affermativo restutuisce il path
	*/
	function verify($fileName)
	{
		$cacheFileName = $this->_cacheObj->verify($fileName, get_class($this));
		if ($cacheFileName===false)
		{
			$this->_fileName = $fileName;
			$cacheFileName = $this->compile($fileName);
		}

		return $cacheFileName;
	}
}