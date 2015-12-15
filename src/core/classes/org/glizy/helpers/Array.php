<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_helpers_Array */
class org_glizy_helpers_Array extends GlizyObject
{
	// the multisort code if based on http://wiki.grusp.it/tips:array_key_multisort?s=multisort by AlberT (http://www.superalbert.it/)

	/**
	 * ordina un array multidimensionale in base ad un campo
	 *
	 * @param $arr, l'array da ordinare
	 * @param $l la "label" che identifica il campo di ordinamento
	 * @param $f la funzione di ordinamento che si vuole applicare, di default si usa strnatcasecmp()
	 * @return  TRUE in caso di successo, FALSE in caso di fallimento.
	 */
	static function arrayMultisortByLabel(&$arr, $l, $invert=false, $f='strnatcasecmp')
	{
		if ( $invert )
		{
			return usort($arr, create_function('$a, $b', "return $f(\$b['$l'], \$a['$l']);"));
		}
		else
		{
	        return usort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
	}
	}


	/**
	 * ordina un array multidimensionale in base all'indice
	 *
	 * @param $arr, l'array da ordinare
	 * @param $l la "label" che identifica il campo di ordinamento
	 * @param $f la funzione di ordinamento che si vuole applicare, di default si usa strnatcasecmp()
	 * @return  TRUE in caso di successo, FALSE in caso di fallimento.
	 */
	function arrayMultisortByIndex(&$arr, $l , $f='strnatcasecmp') {
	        return usort($arr, create_function('$a, $b', "return $f(\$a[$l], \$b[$l]);"));
	}
}