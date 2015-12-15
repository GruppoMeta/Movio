<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_dataAccess_RelationContentTable */
class org_glizy_dataAccess_RelationContentTable extends org_glizy_dataAccess_Relation
{
	var $_objectName = '';
	var $_newRecord;
	var $_bindTo;

	function __construct(&$parent, $options)
	{
		parent::__construct($parent, $options);
		$this->_objectName = $options['objectName'].':'.$options['name'];
		$this->_bindTo = $options['bindTo'];
	}

	function build($params=array())
	{
		$this->_reset();
		$parentId = $this->_parent->getId();
		if (!is_null($parentId))
		{
			$this->_newRecord = empty( $parentId );
			if ( !$this->_newRecord )
			{

				if ( $this->record->find( array( 'contentplugin_FK_id' => $parentId, 'contentplugin_objectName' => $this->_objectName ) ) )
				{
					if ( __Request::exists( $this->_bindTo[ 0 ] ) ) return;
					$values = unserialize( $this->record->contentplugin_value );
					if ( is_array( $values ) )
					{
						foreach( $values as $k => $v )
						{
							if ( in_array( $k, $this->_bindTo ) )
							{
								$this->_parent->{$k} = $v;
							}
						}
					}
				}
			}
		}
	}

	function create($params)
	{
		$this->record = org_glizy_ObjectFactory::createModel( $this->_className );
		$this->record->setProcessRelations(false);

		// TODO
	}

	function bind(&$object)
	{
		$this->record = &$object;
		$this->record->setProcessRelations(false);

		// TODO
	}

	function postSave()
	{
		if ( !is_object( $this->record ) )
		{
			$this->_reset();
		}

		$values = array();
		foreach( $this->_bindTo as $v )
		{
			$values[ $v ] = $this->_parent->{$v};
		}

		$parentId = $this->_parent->getId();
		// $this->record->find( array( 'contentplugin_FK_id' => $parentId, 'contentplugin_objectName' => $this->_objectName ) );
		$this->record->contentplugin_FK_id = $parentId;
		$this->record->contentplugin_objectName = $this->_objectName;
		$this->record->contentplugin_value = serialize( $values );
		$this->record->save();
	}

	function delete()
	{
		// NOTA il delete deve funzionare anche senza la build della relazione
		$this->_reset();
		$parentId = $this->_parent->getId();
		$this->record->execSql( 'DELETE ##TABLE_NAME## WHERE contentplugin_FK_id = '.$parentId.' AND contentplugin_objectName="'.$this->_objectName.'"' );
		$this->record->delete();
	}


	function _reset()
	{
		$this->record = org_glizy_ObjectFactory::createModel( $this->_className );
		$this->_newRecord = false;
	}

}