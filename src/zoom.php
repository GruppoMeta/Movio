<?php
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : NULL;
$w = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : 0;
if (is_null($id)) exit;
if (!defined('GLZ_LOADED'))
{
	require_once('core/core.inc.php');
	org_glizy_Paths::init('application', '');
	org_glizy_Config::init();

	if (file_exists(org_glizy_Paths::get('APPLICATION_STARTUP')))
	{
		// if the startup folder is defined all files are included
		glz_require_once_dir(org_glizy_Paths::get('APPLICATION_STARTUP'));
	}
	org_glizy_ObjectValues::set('org.glizy', 'languageId', 1);
}
$zoomFile = __Paths::get('CACHE').'/zoom_'.$id.'_'.$w.'.xml';
if ( !file_exists( $zoomFile ) )
{
	glz_import('org.glizycms.mediaArchive.MediaManager');
	set_time_limit(0);
	$media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);

	if (preg_match('/\.tif$/', $media->fileName)) {
		$mediaInfo = $media->getResizeImage(2000, 2000);
		$media->fileName = $mediaInfo['fileName'];
	}

	set_include_path(implode(PATH_SEPARATOR, array( realpath('application/libs/openzoom/'), get_include_path())));
	require 'Oz/Deepzoom/ImageCreator.php';
	$converter = new Oz_Deepzoom_ImageCreator( 254, 1, "jpg", 0.80 );
	$converter->create( realpath( $media->getFileName() ), $zoomFile );
}
echo json_encode( true );
