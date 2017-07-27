<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

function getImage($applicationPath, $corePath='')
{
	require_once($corePath.'core/core.inc.php');
	org_glizy_Paths::init($applicationPath, $corePath);
	org_glizy_Config::init();

	$id 	= isset($_REQUEST['id']) ? intval($_REQUEST['id']) : NULL;
	$w 		= isset($_REQUEST['w']) ? intval($_REQUEST['w']) : NULL;
	$h 		= isset($_REQUEST['h']) ? intval($_REQUEST['h']) : NULL;
	$force 	= isset($_REQUEST['f']) ? $_REQUEST['f']=='true' || $_REQUEST['f']=='1': false;
	$crop 	= isset($_REQUEST['c']) ? $_REQUEST['c']=='true' || $_REQUEST['c']=='1' : false;

	if (is_null($id)) exit;

	glz_import('org.glizy.media.MediaManager');
	$media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);

	if ($media->type!='IMAGE') exit;

	if (!is_null($w) && !is_null($h))
	{
		//resize the image
		$mediaInfo = $media->getResizeImage($w, $h, $crop, 0, $force);
	}
	else
	{
		// get the full image
		$mediaInfo = $media->getImageInfo();
	}

	$ext = array(IMG_GIF => '.gif', IMG_JPG => '.jpeg', IMG_PNG => '.png', IMG_WBMP => '.wbmp');

	header("location: ".GLZ_HOST.'/'.$mediaInfo['fileName'] );
}