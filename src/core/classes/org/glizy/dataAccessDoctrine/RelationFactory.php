<?php

class org_glizy_dataAccessDoctrine_RelationFactory extends GlizyObject
{
    function createRelation(&$parent, $options)
    {
		$relation = NULL;
		switch (strtolower($options['type']))
		{
			case 'hasone':
			case 'has_one':
			case 'fk':
				//$relation = new org_glizy_dataAccess_RelationHasOne($parent, $options);
				break;
			case 'hasmany':
			case 'has_many':
				//$relation = new org_glizy_dataAccess_RelationHasMany($parent, $options);
				break;
			case 'many':
				//$relation = new org_glizy_dataAccess_RelationMany2Many($parent, $options);
				break;
			case 'jointable':
				$relation = new org_glizy_dataAccessDoctrine_RelationJoinTable($parent, $options);
				break;
			case 'contenttable':
				//$relation = new org_glizy_dataAccess_RelationContentTable($parent, $options);
				break;
			default:
				// TODO
				// visualizzare errore
				break;
		}
		
		return $relation;
	}
}