<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_PdfButton extends org_glizy_components_Component
{
	var $isEnabled;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', false, 	__T( 'GLZ_PRINT_PDF' ),	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html()
	{
		if( $this->_application->getCurrentMenu()->printPdf && 	!org_glizy_ObjectValues::get( 'org.glizy.application', 'pdfMode' ) )
		{
			$url = GLZ_HOST."/index.php?".__Request::get( '__url__' )."&printPdf=1";
			$output = __Link::makeSimpleLink( $this->getAttribute( 'label' ), $url, '', 'printPdf' );
			$this->addOutputCode( $output );
		}
	}

}