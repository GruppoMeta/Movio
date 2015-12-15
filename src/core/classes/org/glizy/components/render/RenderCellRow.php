<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_render_RenderCellRow extends GlizyObject
{
	var $application;

	function __construct(&$application)
	{
		$this->application = $application;
	}

	function renderRow( $item, $cssClass )
	{
		return '';
	}



}