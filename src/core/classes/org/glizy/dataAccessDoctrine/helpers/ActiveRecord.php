<?php
class org_glizy_dataAccessDoctrine_helpers_ActiveRecord extends GlizyObject
{
	function recordSet2List(&$iterator, $routeUrl='', $cssClass=array(), $maxRecord=NULL, $queryVars=array(), $getRelationValues=false)
	{
		$output = array();
		$tempCssClass = $cssClass;
		foreach ($iterator as $i => $ar) {
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
			if (!is_null($maxRecord) && $i==$maxRecord) break;
		}
		return $output;
	}

	function recordSet2ItemsList(&$iterator)
	{
		$output = array();
		foreach ($iterator as $ar) {
			$values = $ar->getValuesAsArray();
		}
		return $output;
	}
}