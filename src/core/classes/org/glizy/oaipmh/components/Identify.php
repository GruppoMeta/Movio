<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_components_Identify extends org_glizy_components_Component
{
	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		$output = '<Identify>';
		$output .= '<repositoryName>'.org_glizy_oaipmh_OaiPmh::encode( __Config::get( 'oaipmh.title' ) ).'</repositoryName>';
		$output .= '<baseURL>'.org_glizy_oaipmh_OaiPmh::encode( GLZ_HOST ).'</baseURL>';
		$output .= '<protocolVersion>'.org_glizy_oaipmh_OaiPmh::encode( __Config::get( 'oaipmh.protocolVersion' ) ).'</protocolVersion>';
		$output .= '<adminEmail>'.org_glizy_oaipmh_OaiPmh::encode( __Config::get( 'oaipmh.adminEmail' ) ).'</adminEmail>';
		$output .= '<earliestDatestamp>'.org_glizy_oaipmh_OaiPmh::encode( __Config::get( 'oaipmh.earliestDatestamp' ) ).'</earliestDatestamp>';
		$output .= '<deletedRecord>no</deletedRecord>';
		$output .= '<granularity>'.org_glizy_oaipmh_OaiPmh::encode( __Config::get( 'oaipmh.granularity' ) ).'</granularity>';
		$output .= '</Identify>';
		$this->addOutputCode( $output );
	}

}