<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_core_Application extends org_glizy_mvc_core_Application
{
	private $error = '';
	private $metadataFormat = array();
	private $sets = array();

	function _init()
	{
		parent::_init();
		$this->contentType = 'text/xml';
		__Paths::set( 'APPLICATION_PAGE_TYPE', __Paths::get( 'CORE_CLASSES' ).'org/glizy/oaipmh/pageTypes/' );
		$this->addMetadataFormat( 'oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd', 'http://www.openarchives.org/OAI/2.0/oai_dc/', 'dc', 'http://purl.org/dc/elements/1.1/' );
	}

	function _readPageId()
	{
		__Request::set( 'pageId', 'Index' );
		parent::_readPageId();
	}

	function addMetadataFormat( $prefix, $schema, $namespace, $recordPrefix = '', $recordNamespace = '' )
	{
		$this->metadataFormat[ $prefix ] = array( 'prefix' => $prefix, 'schema' => $schema, 'namespace' => $namespace, 'recordPrefix' => $recordPrefix, 'recordNamespace' => $recordNamespace );
	}

	function getMetadataFormat()
	{
		return $this->metadataFormat;
	}

	function addSet( $classPath )
	{
		// $this->sets[] = array( 'metadata' => $metadata, 'spec' => $spec, 'name' => $name, 'description' => $description, 'creator' => $creator );
		$this->sets[] = $classPath;
	}

	function getSets()
	{
		return $this->sets;
	}

	function getError()
	{
		return $this->error;
	}

	function setError($code, $argument='', $value='')
	{
		switch ($code) {
			case 'badArgument' :
				$text = "The argument '$argument' (value='$value') included in the request is not valid.";
				break;

			case 'badGranularity' :
				$text = "The value '$value' of the argument '$argument' is not valid.";
				// $code = 'badArgument';
				break;

			case 'badResumptionToken' :
				$text = "The resumptionToken '$value' does not exist or has already expired.";
				break;

			case 'badRequestMethod' :
				$text = "The request method '$argument' is unknown.";
				// $code = 'badVerb';
				break;

			case 'badVerb' :
				$text = "The verb '$argument' provided in the request is illegal.";
				break;

			case 'cannotDisseminateFormat' :
				$text = "The metadata format '$value' given by $argument is not supported by this repository.";
				break;

			case 'exclusiveArgument' :
				$text = 'The usage of resumptionToken as an argument allows no other arguments.';
				// $code = 'badArgument';
				break;

			case 'idDoesNotExist' :
				$text = "The value '$value' of the identifier is illegal for this repository.";
				//if (!is_valid_url($value)) {
					$code = 'badArgument';
				//}
				break;

			case 'missingArgument' :
				$text = "The required argument '$argument' is missing in the request.";
				// $code = 'badArgument';
				break;

			case 'noRecordsMatch' :
				$text = 'The combination of the given values results in an empty list.';
				break;

			case 'noMetadataFormats' :
				$text = 'There are no metadata formats available for the specified item.';
				break;

			case 'noVerb' :
				$text = 'The request does not provide any verb.';
				// $code = 'badVerb';
				break;

			case 'noSetHierarchy' :
				$text = 'This repository does not support sets.';
				break;

			case 'sameArgument' :
				$text = 'Do not use them same argument more than once.';
				// $code = 'badArgument';
				break;

			case 'sameVerb' :
				$text = 'Do not use verb more than once.';
				// $code = 'badVerb';
				break;

			default:
				$text = "Unknown error: code: '$code', argument: '$argument', value: '$value'";
				$code = 'badArgument';
		}

		$this->error = ' <error code="'.org_glizy_oaipmh_OaiPmh::encode( $code ).'">'.org_glizy_oaipmh_OaiPmh::encode( $text )."</error>";
	}

}