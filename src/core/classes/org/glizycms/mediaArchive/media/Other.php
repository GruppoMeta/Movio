<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_media_Other extends org_glizycms_mediaArchive_media_Media
{
	function getIconFileName()
	{
		return org_glizy_Assets::get('ICON_MEDIA_OTHER');
	}
}