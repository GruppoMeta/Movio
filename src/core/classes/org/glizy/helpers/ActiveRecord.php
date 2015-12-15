<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_ActiveRecord extends GlizyObject
{
	function recordSet2List(&$iterator, $routeUrl='', $cssClass=array(), $maxRecord=NULL, $queryVars=array(), $getRelationValues=false)
	{
		$output = array();
		$tempCssClass = $cssClass;
		$i = 0;
		while ($iterator->hasMore())
		{
			$ar = &$iterator->current();
			if ($getRelationValues)
			{
				$ar->setProcessRelations(true);
				$ar->buildAllRelations();
			}
			$values = $ar->getValuesAsArray($getRelationValues, true, true);
			$values = array_merge($values, $queryVars);
			if (!count($tempCssClass)) $tempCssClass = $cssClass;
			if (count($tempCssClass)) $values['__cssClass__'] = array_shift($tempCssClass);

			$values['__url__'] = org_glizy_helpers_Link::makeURL($routeUrl, $values);
			$output[] = $values;
			$iterator->next();
			$i++;
			if (!is_null($maxRecord) && $i==$maxRecord) break;
		}
		return $output;
	}

	function recordSet2ItemsList(&$iterator)
	{
		$output = array();
		while ($iterator->hasMore())
		{
			$ar = &$iterator->current();
			$values = $ar->getValuesAsArray();
//			$output[] = array('key' => $values[0], 'value' => $values[count($values)-1]);
			$iterator->next();
		}
		return $output;
	}
}