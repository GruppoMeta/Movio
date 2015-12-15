<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_filters_Uppercase extends org_glizy_filters_OutputFilter
{
	function apply(&$value, &$component)
	{
		if (is_string($value))
		{
			$value = strtoupper($value);
		}
	}
}