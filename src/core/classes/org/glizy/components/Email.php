<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Email extends org_glizy_components_Text
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('makeLink', false, false, COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html()
	{
		$output = $this->getContent();
		$this->addOutputCode($output);
	}

	function getContent()
	{
		$tagContent = trim($this->getText());
		if ($this->getAttribute('makeLink') && !empty($tagContent))
		{
			return org_glizy_helpers_Link::makeEmailLink($tagContent);
		}
		else
		{
			return $tagContent;
		}
	}
}