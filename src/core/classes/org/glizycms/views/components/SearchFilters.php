<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_SearchFilters extends org_glizy_components_SearchFilters
{
	function process()
	{
		$visible = $this->_parent->loadContent($this->getId())===1;
		if ($visible) {
			parent::process();
		} else {
			$this->setAttribute('visible', false);
		}
	}

	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['data'] = '';
		$attributes['noChild'] = 'true';

		return org_glizy_helpers_Html::renderTag('glz:Checkbox', $attributes);
	}
}