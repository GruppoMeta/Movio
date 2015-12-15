<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Date extends org_glizy_components_Input
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('showTime',	false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('defaultNow',	false, 	false,	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->_content = $this->_parent->loadContent($this->getId());
		if ($this->_content=='0000-00-00') $this->_content = '';
		if ($this->_content == '' && $this->getAttribute( 'defaultNow' ) )
		{
			$this->_content = date( 'd/m/Y' );
		}
	}

	function render_html()
	{
		parent::render_html();

		if ( !$this->getAttribute( 'readOnly') )
		{
			$id = $this->getId();
			$format = $this->getAttribute('showTime') ? 'GlizyLocale.datetime.format' : 'GlizyLocale.date.format';
			$minView = $this->getAttribute('showTime') ? '' : 'minView: \'month\',';

			$jsCode = <<<EOD
$(function () {
	$("#$id").datetimepicker({
            language: 'it',
            format: $format,
            $minView
            autoclose: true,
            todayHighlight: true
        });
});
EOD;

			$this->_parent->addOutputCode( org_glizy_helpers_JS::JScode( $jsCode ) );
		}
	}
}
