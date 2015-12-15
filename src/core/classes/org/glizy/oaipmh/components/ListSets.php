<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_components_ListSets extends org_glizy_components_Component
{
	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		$sets = $this->_application->getSets();

		if ( !count( $sets ) )
		{
			$this->_application->setError( 'noSetHierarchy' );
		}
		else
		{
			$metadataFormat = $this->_application->getMetadataFormat();
			$output = '<ListSets>';
			foreach( $sets as $v )
			{
				$setClass = org_glizy_ObjectFactory::createObject( $v );
				if ( $setClass )
				{
					$info = $setClass->getSetInfo();
					$output .= '<set>';
					$output .= '<setSpec>'.org_glizy_oaipmh_OaiPmh::encode($info[ 'setSpec' ] ).'</setSpec>';
					$output .= '<setName>'.org_glizy_oaipmh_OaiPmh::encode($info[ 'setName' ] ).'</setName>';
					if ( !empty( $info[ 'setDescription' ] ) )
					{
						$output .= '<setDescription>';
						$output .= org_glizy_oaipmh_OaiPmh::openMetadataHeader( $metadataFormat[ 'oai_dc' ] );
						$output .= '<dc:description>'.org_glizy_oaipmh_OaiPmh::encode($info[ 'setDescription' ] ).'</dc:description>';
						$output .= '<dc:creator>'.org_glizy_oaipmh_OaiPmh::encode($info[ 'setCreator' ] ).'</dc:creator>';
						$output .= org_glizy_oaipmh_OaiPmh::closeMetadataHeader( $metadataFormat[ 'oai_dc' ] );
						$output .= '</setDescription>';
					}
					$output .= '</set>';
				}
			}
			$output .= '</ListSets>';
			$this->addOutputCode( $output );
		}
	}
}