<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_parser_XML extends DOMDocument
{
	public $namespaces = array();
	private $defaultNamespaces = array ( 'xmlns:glz' => '"http://www.glizy.org/dtd/glz/1.0"', 'xmlns:model' => '"http://www.glizy.org/dtd/model/1.0"', 'xmlns:adm' => '"http://www.glizy.org/dtd/adm/1.0"' );

	public function load( $file )
	{
		$this->checkIfFileExits( $file );
		$this->preserveWhiteSpace = false;
		$r = parent::load( $file, LIBXML_NOERROR );

		return $r;
	}


	public function loadAndParseNS( $file )
	{
		$this->checkIfFileExits( $file );
		$xmlString = file_get_contents( $file );
		return $this->loadXmlAndParseNS( $xmlString );
	}


	public function loadXmlAndParseNS( $xmlString )
	{
		// esegue il parsing del primo nodo per ricavare i namespace definiti
		preg_match_all( '/<[^\?]*\s*[^>]*>/iU', $xmlString, $match );
		if ( count( $match[ 0 ] ) )
		{
			foreach( $match[ 0 ] as $m )
			{
				if ( strpos( $m, '<?' ) === false )
				{
					// controlla se sono presenti i namespace di default
					$rootNodeString =  $m;
					foreach( $this->defaultNamespaces as $ns => $uri )
					{
						$rootNodeString = $this->addDefaultNS( $rootNodeString, $ns, $uri );
					}
					$xmlString = str_replace( $m, $rootNodeString, $xmlString );

					preg_match_all( '/xmlns:(.*)[\s\\n\\r]*=[\s\\n\\r]*["\'](.*)["\']/iU', $rootNodeString, $matchns );
					$numNS = count( $matchns[ 0 ] );
					if ( $numNS )
					{
						for( $i = 0; $i < $numNS; $i++ )
						{
							$this->namespaces[ $matchns[ 1 ][ $i ] ] = $matchns[ 2 ][ $i ];
						}
					}

					break;
				}
			}
		}

		$this->preserveWhiteSpace = false;
		$r = $this->loadXML( $xmlString , LIBXML_NOERROR );
		return $r;
	}

	private function addDefaultNS( $text, $ns, $uri )
	{
		if ( stripos( $text, $ns ) === false )
		{
			$text = preg_replace( '/(\s|>)/', ' '.$ns.'='.$uri.'$1', $text, 1 );
		}
		return $text;
	}


	private function checkIfFileExits( $file )
	{
		if ( !file_exists( $file ) )
		{
			throw new Exception( 'File non esiste '.$file );
		}
	}
}