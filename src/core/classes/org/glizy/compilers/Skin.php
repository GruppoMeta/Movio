<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_Skin extends org_glizy_compilers_Compiler
{	
	function compile( $options )
	{
		$this->_cacheObj->save($options['defaultHtml']);
		return $this->_cacheObj->getFileName();
	}
	
	/**
	*	Verifica se il file è compilato, in casi affermativo restutuisce il path
	*/
	function verify($fileName, $options=NULL)
	{	
		$cacheFileName = $fileName;
		if (!empty($options['defaultHtml']))
		{
			// memorizza la skin di defaul del componente
			// come file in cache per poi passarlo al template engine
			$cacheFileName = $this->_cacheObj->verify($fileName, get_class($this));
			if ($cacheFileName===false)
			{
				$this->_fileName = $fileName;
				$cacheFileName = $this->compile( $options );
				if ($cacheFileName===false)
				{
					// TODO
					echo "FATAL ERROR ".$fileName;
				}
			}
		}

		return $cacheFileName;
	}

}