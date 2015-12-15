<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_render_RenderCell extends GlizyObject
{
	protected $application;
	protected $user = NULL;

	function __construct($application)
	{
		$this->application = $application;
		$this->user = $this->application->getCurrentUser();
	}

	function renderCell( $key, $value, $item, $columnName )
	{
		return '';
	}

	function getHeader( $text )
	{
		return $text;
	}

	function getCssClass( $key, $value, $item )
	{
		return '';
	}

}