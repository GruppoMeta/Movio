<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_views_renderer_CellMediaArchiveThumb extends GlizyObject
{
	function renderCell( $key, $value, $row )
	{
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		$media = org_glizycms_mediaArchive_MediaManager::getMediaByValues( $row );
		$sizes = method_exists( $media, 'getOriginalSizes') ? $media->getOriginalSizes() : array( 'width' => 0, 'height' => 0 );
		// $scale = strpos( $application->getPageId(), 'picker' ) !== false ? 2 : 1;
		// vavr_dump(__Config::get('THUMB_WIDTH'));
		$scale = 1;

		$thumbnail = $media->getThumbnail( __Config::get('THUMB_WIDTH') / $scale, __Config::get('THUMB_HEIGHT') / $scale, __Config::get('ADM_THUMBNAIL_CROP'), __Config::get('ADM_THUMBNAIL_CROPPOS'));
		$title = !empty( $media->title ) ? $media->title : $media->fileName;
		return '<img  src="'.$thumbnail['fileName'].'" width="'.$thumbnail['width'].'" height="'.$thumbnail['height'].'" alt="'.$title.'" title="'.$title.'"  data-id="'.$media->id.'" data-filename="'.$media->fileName.'" data-width="'.$sizes['width'].'" data-height="'.$sizes['height'].'" style="cursor: pointer;" />';
	}
}