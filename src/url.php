<?php
require_once("core/core.inc.php");

org_glizy_ObjectValues::set('org.glizy', 'editingLanguageId', 1);

$application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.Application', 'application');
org_glizy_Paths::addClassSearchPath('admin/application/classes/');
$application->runSoft();

$req = __Request::get('req');
$timestamp = __Request::get('timestamp');
$languageCode = __Request::get('languageCode');
$action = __Request::get('action');

if ($languageCode) {
    $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
    $result = $ar->find(array('language_code' => $languageCode));
    if ($result) {
        org_glizy_ObjectValues::set('org.glizy', 'editingLanguageId', $ar->language_id);
    } else {
        header("Content-Type: application/json");
        echo json_encode('NO_LANGUAGE');
        die;
    }
}

if ($req == 'checkUrl') {
    $result = array('status' => true);
} else if ($req == 'news') {
    $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.news.models.News');
    
    if ($timestamp) {
        $it->where('document_detail_modificationDate', date('Y-m-d H:i:s', $timestamp), '>');
    }
    
    $result = array();
    
    foreach ($it as $ar) {
        $item = $ar->getValues(false, false, false, false);
        
        $item->externalId = $ar->getid();
        
        $item->images = new StdClass(); 
        
        foreach ((array)$ar->images->image as $i => $image) {
            $item->images->image[$i] = imageAbsoluteUrl($image);
        }
        
        $item->attaches = new StdClass(); 
        
        foreach ((array)$ar->attaches->media as $i => $media) {
            $item->attaches->media[$i] = imageAbsoluteUrl($media);
        }
        
        unset($item->fulltext);
        
    	$result[] = $item;
    }
} else if ($req == 'mostra') {
    $result = getDataFromPageType('Exhibition', $timestamp);
} else if ($req == 'mostraDigitale') {
    $result = getDataFromPageType('DigitalExhibition', $timestamp);
} else if ($req == 'getLanguageCodes') {
   $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
   $result = array();
   foreach ($it as $ar) {
       $result[] = $ar->language_code;
   }
}

header("Content-Type: application/json");
echo json_encode($result);

function imageAbsoluteUrl($item, $field) {
    $json = $item->$field;
    if (preg_match('/getImage[^"]+/', $json, $m)) {
        $media = json_decode($json);
        $item->{$field.'Title'} = $media->title;
        $item->{$field.'Url'} = GLZ_HOST.'/'.org_glizy_helpers_Media::getUrlById($media->id, true);
    } else {
        $item->{$field.'Title'} = '';
        $item->{$field.'Url'} = '';
    }
    unset($item->$field);
}

function mediaAbsoluteUrl($item, $field) {
    $json = $item->$field;
    if (preg_match('/{"id":(\d+)/', $json, $m)) {
        $media = json_decode($json);
        $item->{$field.'Title'} = $media->title;
        $m = org_glizycms_mediaArchive_MediaManager::getMediaById($media->id);
        
        if ($m->type == 'VIDEO') {
            $item->{$field.'Url'}  = GLZ_HOST.'/'.org_glizy_helpers_Media::getFileUrlById($media->id, true);
        } else {
            $item->{$field.'Url'}  = GLZ_HOST.'/'.org_glizy_helpers_Media::getFileUrlById($media->id);
        }
    } else {
        $item->{$field.'Title'} = '';
        $item->{$field.'Url'} = '';
    }
    unset($item->$field);
}

function getDataFromPageType($pageType, $timestamp) {
    $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');
    $it->where('menu_pageType', $pageType);
    
    if ($timestamp) {
        $it->where('menu_modificationDate', date('d/m/Y H:i:s', $timestamp), '>');
    }
    
    $result = array();
    
    foreach($it as $ar) {
        $menuId = $ar->getId();
        $contentProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');
        $item = $contentProxy->readContentFromMenu($menuId, org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'));
        $item->externalId = $item->__id;
        $item->title = $item->__title;
        
        foreach ($item as $k => $v) {
            if (strpos($k, '__') === 0 )  {
                unset($item->$k);
            }
        }
        
        imageAbsoluteUrl($item, 'banner');
        imageAbsoluteUrl($item, 'appImage');
        mediaAbsoluteUrl($item, 'video');
        
        foreach ((array)$item->images->image as $i => $image) {
            $obj = new StdClass;
            $obj->image = $image;
            imageAbsoluteUrl($obj, 'image');
            $item->images->title[$i] = $obj->imageTitle;
            $item->images->url[$i] = $obj->imageUrl;
        }
        
        unset($item->images->image);
        
        foreach ((array)$item->attaches->media as $i => $media) {
            $obj = new StdClass;
            $obj->media = $media;
            mediaAbsoluteUrl($obj, 'media');
            $item->attaches->title[$i] = $obj->mediaTitle;
            $item->attaches->url[$i] = $obj->mediaUrl;
        }
        
        unset($item->attaches->media);
        
        $result[] = $item;
    }
    
    return $result;
}