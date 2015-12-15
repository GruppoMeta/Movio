<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_components_ListMetadataFormats extends org_glizy_components_Component
{

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		$metadataFormat = $this->_application->getMetadataFormat();
		$output = '<ListMetadataFormats>';
		foreach( $metadataFormat as $v )
		{
			$output .= '<metadataFormat>';
			$output .= '<metadataPrefix>'.org_glizy_oaipmh_OaiPmh::encode($v[ 'prefix' ] ).'</metadataPrefix>';
			$output .= '<schema>'.org_glizy_oaipmh_OaiPmh::encode($v[ 'schema' ] ).'</schema>';
			$output .= '<metadataNamespace>'.org_glizy_oaipmh_OaiPmh::encode($v[ 'namespace' ] ).'</metadataNamespace>';
			$output .= '</metadataFormat>';
		}
		$output .= '</ListMetadataFormats>';
		$this->addOutputCode( $output );
	}

}