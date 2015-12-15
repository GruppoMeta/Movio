<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_LayoutManagerPHP extends org_glizy_compilers_Compiler
{
	function compile($options)
	{
		$this->_cacheObj->save($options);
		return $this->_cacheObj->getFileName();
	}

	/**
	*	Verifica se il file è compilato, in casi affermativo restituisce il path
	*/
	function verify($fileName, $options=NULL)
	{
		$cacheFileName = $this->_cacheObj->verify($fileName, get_class($this));
		return $cacheFileName;
	}

}