<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_core_AbstractMapping extends GlizyObject
{
	protected $application;
	protected $ar;

	function __construct( $application=NULL )
	{
		$this->application = $application;
	}

	public function getSetInfo()
	{
		$info = array();
		$info[ 'setSpec' ] = '';
		$info[ 'setName' ] = '';
		$info[ 'setDescription' ] = '';
		$info[ 'setCreator' ] = '';
		$info[ 'model' ] = '';
		return $info;
	}

	function getModelName()
	{
		$info = $this->getSetInfo();
		return $info['model'];
	}

	function loadRecord( $id )
	{
		$this->ar 				= org_glizy_ObjectFactory::createModel( $this->getModelName() );
		$pk 					= $this->ar->getPrimarykey();
		$versionFieldName 		= $this->ar->getVersionFieldName();
		$languageFieldName 		= $this->ar->getLanguageFieldName();
		$this->ar->setFieldValue( $pk, $id );
		if ( !is_null( $versionFieldName ) ) $this->ar->setFieldValue( $versionFieldName, 'PUBLISHED' );
		if ( !is_null( $languageFieldName ) ) $this->ar->setFieldValue( $languageFieldName, $this->application->getLanguageId() );
		return $this->ar->find();
	}


	function getRecord($identifier)
	{
		$output = '<record>';
		$output .= $this->getMetadata($identifier);
		$output .= '</record>';
		return $output;
	}
}