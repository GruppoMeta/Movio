<?php
if (!defined('GLZ_LOADED'))
{
  require_once('core/core.inc.php');
  $application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.Application', 'application');
  org_glizy_Paths::addClassSearchPath('admin/application/classes/');

  if (file_exists(org_glizy_Paths::get('APPLICATION_STARTUP')))
  {
    // if the startup folder is defined all files are included
    glz_require_once_dir(org_glizy_Paths::get('APPLICATION_STARTUP'));
  }
}


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;
if (is_null($id)) exit;

glz_import('org.glizycms.mediaArchive.MediaManager');
$media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);
if (!$media || !$media->exists()) {
  header('HTTP/1.0 404 Not Found');
  echo "<h1>404 Not Found ".$media->originalFileName."</h1>";
  exit();
}

$browser=id_browser();
$extension = strtolower(substr(strrchr($media->originalFileName, '.'),1));
switch( $extension )
{
  case "pdf":
    $ctype="application/pdf";
    $disposition = "inline";
  break;
  case "vcf": $ctype="application/vcard"; break;
  case "exe":
	$ctype= ($browser=='IE' || $browser=='OPERA') ? "application/octetstream" : "application/octet-stream";
	break;
  case "zip": $ctype="application/zip"; break;
  case "doc": $ctype="application/msword"; break;
  case "xls": $ctype="application/vnd.ms-excel"; break;
  case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
  case "gif": $ctype="image/gif"; break;
  case "png": $ctype="image/png"; break;
  case "jpeg":
  case "jpg": $ctype="image/jpg"; break;
  default: $ctype="application/force-download";
}


header("Pragma: public");
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: ".$ctype);
header("Content-Transfer-Encoding: binary");
header("Content-Disposition: ". ($disposition ?: 'attachment')."; filename=\"".$media->originalFileName."\"");
@readfile($media->getFileName()) or die();

$media->addDownloadCount();

function id_browser()
{
	$browser=$GLOBALS['__SERVER']['HTTP_USER_AGENT'];

   if(ereg('Opera(/| )([0-9].[0-9]{1,2})', $browser)) {
       return 'OPERA';
   } else if(ereg('MSIE ([0-9].[0-9]{1,2})', $browser)) {
       return 'IE';
   } else if(ereg('OmniWeb/([0-9].[0-9]{1,2})', $browser)) {
       return 'OMNIWEB';
   } else if(ereg('(Konqueror/)(.*)', $browser)) {
       return 'KONQUEROR';
   } else if(ereg('Mozilla/([0-9].[0-9]{1,2})', $browser)) {
       return 'MOZILLA';
   } else {
       return 'OTHER';
   }
}