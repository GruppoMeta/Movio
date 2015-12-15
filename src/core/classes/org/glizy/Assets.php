<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_Assets
{
	function init()
	{
		$basePath = org_glizy_Paths::get('CORE').'static/images/';
		$pathsArray 				= &org_glizy_Assets::_getPathsArray(true);
		$pathsArray['ICON_ADD'] 			= $basePath.'icon_add.gif';
		$pathsArray['ICON_ADD_OFF'] 		= $basePath.'icon_add_off.gif';
		$pathsArray['ICON_CHECKED'] 		= $basePath.'icon_checked.gif';
		$pathsArray['ICON_CHECKED_OFF'] 	= $basePath.'icon_checked_off.gif';
		$pathsArray['ICON_DELETE'] 			= $basePath.'icon_delete.gif';
		$pathsArray['ICON_DELETE_OFF']		= $basePath.'icon_delete_off.gif';
		$pathsArray['ICON_DOWN'] 			= $basePath.'icon_down.gif';
		$pathsArray['ICON_DOWN_OFF']		= $basePath.'icon_down_off.gif';
		$pathsArray['ICON_DOWNLOAD'] 		= $basePath.'icon_download.gif';
		$pathsArray['ICON_DOWNLOAD_OFF']	= $basePath.'icon_download_off.gif';
		$pathsArray['ICON_EDIT'] 			= $basePath.'icon_edit.gif';
		$pathsArray['ICON_EDIT_OFF']		= $basePath.'icon_edit_off.gif';
		$pathsArray['ICON_EDITDRAFT'] 		= $basePath.'icon_editDraft.gif';
		$pathsArray['ICON_EDITDRAFT_OFF']	= $basePath.'icon_editDraft_off.gif';
		$pathsArray['ICON_INVISIBLE'] 		= $basePath.'icon_invisible.gif';
		$pathsArray['ICON_INVISIBLE_OFF'] 	= $basePath.'icon_invisible_off.gif';
		$pathsArray['ICON_NEW'] 			= $basePath.'icon_add.gif';
		$pathsArray['ICON_NEW_OFF'] 		= $basePath.'icon_add_off.gif';
		$pathsArray['ICON_ORDER_UP'] 		= $basePath.'icon_orderUp.gif';
		$pathsArray['ICON_ORDER_DOWN'] 		= $basePath.'icon_orderDown.gif';
		$pathsArray['ICON_ORDER_UPBLACK'] 	= $basePath.'icon_orderUpBlack.gif';
		$pathsArray['ICON_ORDER_DOWNBLACK'] = $basePath.'icon_orderDownBlack.gif';
		$pathsArray['ICON_PREVIEW'] 		= $basePath.'icon_preview.gif';
		$pathsArray['ICON_PREVIEW_OFF'] 	= $basePath.'icon_preview_off.gif';
		$pathsArray['ICON_PUBLISH'] 		= $basePath.'icon_publish.gif';
		$pathsArray['ICON_PUBLISH_OFF'] 	= $basePath.'icon_publish_off.gif';
		$pathsArray['ICON_UP'] 				= $basePath.'icon_up.gif';
		$pathsArray['ICON_UP_OFF'] 			= $basePath.'icon_up_off.gif';
		$pathsArray['ICON_UNCHECKED'] 		= $basePath.'icon_unchecked.gif';
		$pathsArray['ICON_UNCHECKED_OFF'] 	= $basePath.'icon_unchecked_off.gif';
		$pathsArray['ICON_UNPUBLISH'] 		= $basePath.'icon_unpublish.gif';
		$pathsArray['ICON_UNPUBLISH_OFF'] 	= $basePath.'icon_unpublish_off.gif';
		$pathsArray['ICON_VISIBLE'] 		= $basePath.'icon_visible.gif';
		$pathsArray['ICON_VISIBLE_OFF'] 	= $basePath.'icon_visible_off.gif';
		$pathsArray['ICON_MEDIA_IMAGE'] 	= $basePath.'mediaImage.png';
		$pathsArray['ICON_MEDIA_OFFICE'] 	= $basePath.'mediaOffice.png';
		$pathsArray['ICON_MEDIA_PDF'] 		= $basePath.'mediaPdf.png';
		$pathsArray['ICON_MEDIA_AUDIO'] 	= $basePath.'mediaAudio.png';
		$pathsArray['ICON_MEDIA_ARCHIVE'] 	= $basePath.'mediaArchive.png';
		$pathsArray['ICON_MEDIA_FLASH'] 	= $basePath.'mediaFlash.png';
		$pathsArray['ICON_MEDIA_VIDEO'] 	= $basePath.'mediaVideo.png';
		$pathsArray['ICON_MEDIA_OTHER'] 	= $basePath.'mediaOther.png';
		$pathsArray['ICON_LOCK'] 			= $basePath.'icon_lock.gif';
		$pathsArray['ICON_LOCK_OFF'] 		= $basePath.'icon_lock_off.gif';
		$pathsArray['ICON_GO'] 				= $basePath.'icon_go.gif';
		$pathsArray['ICON_GO_OFF'] 			= $basePath.'icon_go_off.gif';
		$pathsArray['ICON_DOCUMENT'] 		= $basePath.'icon_document.gif';
		$pathsArray['ICON_FOLDER'] 			= $basePath.'icon_folder.gif';
		$pathsArray['ICON_REENABLE'] 			= $basePath.'icon_folder.gif';
		$pathsArray['ICON_OPEN'] 			= $basePath.'icon_open.gif';
		$pathsArray['ICON_COD_START']		= $basePath.'icon_cod_start.png';
		$pathsArray['ICON_COD_ALERT'] 		= $basePath.'icon_cod_alert.png';
		$pathsArray['ICON_COD_STOP'] 		= $basePath.'icon_cod_stop.png';
		$pathsArray['ICON_COD_WAIT'] 		= $basePath.'icon_cod_wait.png';
		$pathsArray['ICON_USER'] 			= $basePath.'icon_user.png';
		$pathsArray['ICON_USER_RED'] 		= $basePath.'icon_user_red.png';
		$pathsArray['ICON_USER_GREEN'] 		= $basePath.'icon_user_green.png';
		$pathsArray['ICON_USER_ALERT'] 		= $basePath.'icon_user_alert.png';
		$pathsArray['ICON_USER_DENY'] 		= $basePath.'icon_user_deny.gif';
		$pathsArray['ICON_WHEEL']			= $basePath.'wheel.gif';
		$pathsArray['ICON_RELOAD']			= $basePath.'icon_reload.png';
	}


    /**
     * @param $pathCode
     * @return null
     */
	public static function get($pathCode)
	{
		$pathsArray = &org_glizy_Assets::_getPathsArray();
		return isset($pathsArray[$pathCode]) ? $pathsArray[$pathCode] : NULL;
	}

    /**
     * @param $pathCode
     * @param string $title
     * @return null|string
     */
	public static function getIcon($pathCode, $title='')
	{
		$pathsArray = &org_glizy_Assets::_getPathsArray();
		$src = isset($pathsArray[$pathCode]) ? $pathsArray[$pathCode] : NULL;
		if ( !is_null( $src ) )
		{
			return '<img src="'.$src.'" width="16" height="16" border="0" alt="'.__T($title).'" title="'.__T($title).'" />';
		}
		else
		{
			return null;
		}
	}

    /**
     * @param $pathCode
     * @param string $title
     * @return null|string
     */
	public static function getImgTag($pathCode, $title='')
	{
		$pathsArray = &org_glizy_Assets::_getPathsArray();
		return isset($pathsArray[$pathCode]) ?
			'<img src="'.$pathsArray[$pathCode].'" alt="'.__t($title).'" title="'.__t($title).'" height="16" width="16" />'
			:
			NULL;
	}

    /**
     * @param $iconName
     * @param $title
     * @param $routing
     * @param $params
     * @param string $confirm
     * @return string
     */
	public static function getImgAction( $iconName, $title, $routing, $params, $confirm='' )
	{
		$params[ 'label' ] = org_glizy_Assets::getImgTag( $iconName, $title );
		$params[ 'title' ] = $title;
		return org_glizy_helpers_Link::makeLink( $routing, $params, array(), $confirm, false );
	}

    /**
     * @param $iconName
     * @param $title
     * @param $routing
     * @param $params
     * @param string $confirm
     * @param bool $encode
     * @return string
     */
	public static function getImgActionWithLabel( $iconName, $title, $routing, $params, $confirm='', $encode=true )
	{
		$params[ 'label' ] = &org_glizy_Assets::getImgTag( $iconName, $title ).$title;
		$params[ 'title' ] = $title;
		return org_glizy_helpers_Link::makeLink( $routing, $params, array(), $confirm, $encode ).'<br />';
	}

    /**
     * @param $iconName
     * @param $title
     * @param $description
     * @param $routing
     * @param $params
     * @param string $confirm
     * @return string
     */
	public static function getImgActionWithTooltip( $iconName, $title, $description, $routing, $params, $confirm='' )
	{
		$params[ 'label' ] = org_glizy_Assets::getImgTag( $iconName, $title );
		$params[ 'title' ] = $title;
		return '<span title="'.$title.'|'.$description.'">'.org_glizy_helpers_Link::makeLink( $routing, $params, array(), $confirm ).'</span>';
	}

    /**
     * @param $iconName string
     * @param $title string
     * @param $description string
     * @return string
     */
	public static function getImgActionWithEnTooltip( $iconName, $title, $description )
	{
		static $t = 0;
		$t++;
		$output = '<a id="t'.$t.'" title="'.$title.'" rel="#td'.$t.'">'.org_glizy_Assets::getImgTag( $iconName, $title ).'</a>';
		$output .= '<div class="hidden"><div id="td'.$t.'">'.$description.'</div></div>';
		$output .= '<script type="text/javascript">';
		$output .= '$j(document).ready(function() { $j("#t'.$t.'").cluetip({ arrows: true, dropShadow: true, cluetipClass: "jtip", fx: {open: "fadeIn" }, local:true, mouseOutClose:true, hoverIntent: false, closePosition: "title",  closeText: "<img src=\"metacms/templates/js/cluetip/images/cross.png\" alt=\"chiudi\" />",sticky: true});});';
		$output .= '</script>';
		return 	$output;
	}


    /**
     * @param bool $init
     * @return mixed
     */
	public static function &_getPathsArray($init=false)
	{
		// Array associativo (PATH_CODE=>PATH)
		static $_pathsArray = array();
		if (!count($_pathsArray) && !$init) org_glizy_Assets::init();
		return $_pathsArray;
	}


	public static function makeLinkWithIcon($routeUrl, $iconName, $params, $deleteMsg=NULL )
	{
		$icon = '<i class="'.$iconName.'"></i>';
	    $params[ 'label' ] = $icon;
		if ( !is_null( $deleteMsg ) )
		{
			$deleteJs = 'if (!confirm(\''.addslashes( $deleteMsg ).'\')){return false;}';
		}
		return org_glizy_helpers_Link::makeLink( $routeUrl, $params, array(), $deleteJs, false );
	}
}

// shortcut version
/**
 * Class __Assets
 */
class __Assets extends org_glizy_Assets
{
}
