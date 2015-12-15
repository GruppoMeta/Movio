<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_views_renderer_CellMediaArchiveInfo extends GlizyObject
{
	function renderCell( $key, $value, $row )
	{
		$output = '';
		$output .= '<p>'.__T('GLZ_MEDIA_TITLE').': <strong>'.( !empty( $row[ 'media_title' ] ) ? $row[ 'media_title' ] : '-').'</strong><br />';
		$output .= __T('GLZ_MEDIA_CATEGORY').': <strong>'.( !empty( $row[ 'media_category' ] ) ? $row[ 'media_category' ] : '-').'</strong><br />';
		$output .= __T('GLZ_MEDIA_FILENAME').': <strong>'.$row[ 'media_originalFileName' ].'</strong> <small>('.$row[ 'media_fileName' ].')</small><br />';
		$output .= __T('GLZ_MEDIA_SIZE').': <strong>'.number_format( $row[ 'media_size' ] /1024).' Kb</strong></p>';
		return $output;
	}
}