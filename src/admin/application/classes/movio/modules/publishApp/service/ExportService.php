<?php
class movio_modules_publishApp_service_ExportService extends GlizyObject
{
    protected $medias;
    protected $graphs;

    function onRegister()
    {

    }

    private function processText($text)
    {
        if (preg_match_all('/<a href="media\:(.*)\:.*".*/Ui', $text, $links)) {
            for ($i = 0; $i < count($links[0]); $i++) {
                $id = $links[1][$i];
                $href = $this->addMediaById($id);
                $newLink = '<a href="'.$href.'"';
                $text = str_replace($links[0][$i], $newLink, $text);
            }
        }

        if (preg_match_all('/src="getImage\.php\?id=(\d+)[^"]*/', $text, $links)) {
            for ($i = 0; $i < count($links[0]); $i++) {
                $id = $links[1][$i];
                $src = $this->addMediaById($id);
                $newSrc = 'src="'.$src.'"';
                $text = str_replace($links[0][$i], $newSrc, $text);
            }
        }

        // replace dei link inseriti a mano e non tramite il cms
        if (preg_match_all('/<a href="..\/(\d+)[^"]*"/', $text, $links)) {
            for ($i = 0; $i < count($links[0]); $i++) {
                $id = $links[1][$i];
                $newLink = '<a href="internal:'.$id.'"';
                $text = str_replace($links[0][$i], $newLink, $text);
            }
        }

        return $text;
    }

    private function resolvePhotoGallery($gallery)
    {
        $photogallery = array();
        foreach ($gallery as $category) {
            $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.models.Media');
            $it->where('media_category', '%'.$category.'%', 'LIKE');

            foreach($it as $ar) {
                $media = array(
                    'id' => $ar->media_id,
                    'title' => $ar->media_title,
                    'fileName' => $ar->media_fileName
                );
                $photogallery[$ar->media_id] = $media;
                $this->addMedia(json_encode($media));
            }
        }

        return array_values($photogallery);
    }

    private function processPhotoGallery($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);
        $contentJson['textAfter'] = $this->processText($contentJson['textAfter']);

        if ($contentJson['gallery-images']) {
            $contentJson['photogallery'] = $this->removeLabelFromContainer($contentJson['gallery-images'], 'image');
            unset($contentJson['gallery-images']);
        }
        return $contentJson;
    }

    private function processPhotoGalleryCategory($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);
        $contentJson['textAfter'] = $this->processText($contentJson['textAfter']);

        if ($contentJson['gallery-images']) {
            $contentJson['photogallery']  = $this->resolvePhotoGallery($contentJson['gallery-images']);
            unset($contentJson['gallery-images']);
        }
        return $contentJson;
    }


    private function processStoryTeller($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);

        if ($contentJson['storyteller-story']) {
            $storytellerStory = $contentJson['storyteller-story'];

            foreach($storytellerStory as $story) {
                if ($story->type == 'photogallery') {
                    $story->gallery = $this->resolvePhotoGallery($story->gallery);
                }

                $story->text = $this->processText($story->text);
                $story->textAfter = $this->processText($story->textAfter);
            }
        }
        return $contentJson;
    }

    private function processImageHotspot($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);

        if ($contentJson['image']) {
            $contentJson['image'] = json_decode($contentJson['image']);
            $imageId = $contentJson['image']->image;
            $image = org_glizycms_mediaArchive_MediaManager::getMediaById($imageId);
            $image = $this->addMedia(json_encode($image));
            $contentJson['image']->image = $image;
        }
        return $contentJson;
    }

    private function processTimeline($menuId, $contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);

        $request = org_glizy_objectFactory::createObject('org.glizy.rest.core.RestRequest', GLZ_HOST_ROOT.'/ajax.php', 'GET', array('pageId' => $menuId, 'ajaxTarget' => 'timeline'));
        $request->execute();
        $response = $request->getResponseBody();
        $response = json_decode($response);
        $contentJson['timeline'] = $response->timeline;
        if ($contentJson['timeline']->date) {
            foreach ($contentJson['timeline']->date as $i => $item) {
                $media = $contentJson['timeline-timelineDef'][$i]->media->fileName;
            	$thumbnail = $contentJson['timeline-timelineDef'][$i]->media->fileName;
            	$item->asset->media = $media ? 'media/'.$media : '';
            	$item->asset->thumbnail = $item->asset->media;
            	$item->asset->caption = $this->processText($contentJson['timeline-timelineDef'][$i]->mediaCaption);
            	$item->text = $this->processText($contentJson['timeline-timelineDef'][$i]->text);
            }
        }
        unset($contentJson['timeline-timelineDef']);
        return $contentJson;
    }

    private function processPage($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);
        $contentJson['images'] = $this->removeLabelFromContainer($contentJson['images'], 'image');
        $contentJson['attachments'] = $this->removeLabelFromContainer($contentJson['attachments'], 'media');
        return $contentJson;
    }

    private function removeLabelFromContainer($container, $key)
    {
        if ($container) {
            $elements = array();

            foreach ($container as $element) {
                $elements[] = $element->$key;
            }

            return $elements;
        } else {
            return $container;
        }
    }

    private function processExhibition($contentJson)
    {
        $contentJson['abstract'] = $this->processText($contentJson['abstract']);
        $contentJson['ticketOffice'] = $this->processText($contentJson['ticketOffice']);
        $contentJson['catalog'] = $this->processText($contentJson['catalog']);
        $contentJson['sponsor1'] = $this->processText($contentJson['sponsor1']);
        $contentJson['sponsor2'] = $this->processText($contentJson['sponsor2']);
        $contentJson['sponsor3'] = $this->processText($contentJson['sponsor3']);
        $contentJson['honoraryCommittee'] = $this->processText($contentJson['honoraryCommittee']);
        $contentJson['scientificCommittee'] = $this->processText($contentJson['scientificCommittee']);
        $contentJson['dedication'] = $this->processText($contentJson['dedication']);
        $contentJson['aknowledgements'] = $this->processText($contentJson['aknowledgements']);
        $contentJson['promotion'] = $this->processText($contentJson['promotion']);
        $contentJson['pressOffice'] = $this->processText($contentJson['pressOffice']);
        $contentJson['projectConstruction'] = $this->processText($contentJson['projectConstruction']);
        $contentJson['reviews'] = $this->processText($contentJson['reviews']);
        $contentJson['contacts'] = $this->processText($contentJson['contacts']);
        $contentJson['services'] = $this->processText($contentJson['services']);

        $contentJson['images'] = $this->removeLabelFromContainer($contentJson['images'], 'image');
        $contentJson['attaches'] = $this->removeLabelFromContainer($contentJson['attaches'], 'media');
        return $contentJson;
    }

    private function processDigitalExhibition($contentJson)
    {
        $contentJson['description'] = $this->processText($contentJson['description']);
        $contentJson['credits'] = $this->processText($contentJson['credits']);
        $contentJson['sponsor1'] = $this->processText($contentJson['sponsor1']);
        $contentJson['sponsor2'] = $this->processText($contentJson['sponsor2']);
        $contentJson['sponsor3'] = $this->processText($contentJson['sponsor3']);
        $contentJson['honoraryCommittee'] = $this->processText($contentJson['honoraryCommittee']);
        $contentJson['scientificCommittee'] = $this->processText($contentJson['scientificCommittee']);
        $contentJson['dedication'] = $this->processText($contentJson['dedication']);
        $contentJson['aknowledgements'] = $this->processText($contentJson['aknowledgements']);
        $contentJson['contacts'] = $this->processText($contentJson['contacts']);
        $contentJson['services'] = $this->processText($contentJson['services']);

        $contentJson['images'] = $this->removeLabelFromContainer($contentJson['images'], 'image');
        $contentJson['attaches'] = $this->removeLabelFromContainer($contentJson['attaches'], 'media');
        return $contentJson;
    }

    private function processVideo($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);
        $contentJson['video-textAlternative'] = $this->processText($contentJson['video-textAlternative']);
        return $contentJson;
    }

    private function processCover($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);
        $contentJson['textAfter'] = $this->processText($contentJson['textAfter']);
        return $contentJson;
    }

    private function processGoogleMap($contentJson)
    {
        $contentJson['text'] = $this->processText($contentJson['text']);
        return $contentJson;
    }

    private function processGraph($contentJson, $menuId, $languageCode)
    {
        $parent = null;
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $c = __ObjectFactory::createComponent('movio.modules.publishApp.views.components.Graph', $application, $parent, 'cmp:Graph', 'idDummy');
        $c->setAttribute('entityTypeId', $contentJson['entitySelect']);
        $c->setAttribute('generateLinks', true);
        $c->setAttribute('addGraphJsLibs.js', false);
        $c->process();
        $c->render();

        $this->addGraph($languageCode, $menuId, $c->getGraphData());

        $contentJson['graph'] = 'graph/'.$languageCode.'/'.$menuId.'.svg';

        return $contentJson;
    }

    public function export($languageId = 1, $languageCode = null, $menuIdArray = array(), $title = null, $subtitle =  null, $creditPageId = null, $isExhibitionActive = null)
    {
        __Paths::set('APPLICATION_TEMPLATE_DEFAULT', __Paths::get('STATIC_DIR').'movio/templates/Default/');

        $menuIdArray = array_flip($menuIdArray);
        $menuIdArray = array_fill_keys(array_keys($menuIdArray), 1);

        $this->medias = array();

        $contentProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');

        // scorre tutti i menù
        $menus = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');
        $menus->load('getAllMenu', array('params' => array( 'languageId' => $languageId)));
        foreach ($menus as $ar) {
        	$menuId = $ar->menu_id;

            // salta tutte le pagine che non sono in menuIdArray
            if (!$menuIdArray[$menuId]) {
                continue;
            }

            $contentVO = $contentProxy->readContentFromMenu($menuId, $languageId);

            $contentJson = array();
            foreach($contentVO as $k=>$v) {
                if ($k == '__title') {
                    $contentJson['title'] = $v;
                    continue;
                }
                if (@strpos($k, '__')===0) continue;

                if (is_object($v)) {
                    $contentJson[$k] = $this->convertObjectToArray($v);
                } else {
                    if (@strpos($v, '{"id"')===0) {
                        $v = $this->addMedia($v);
                    }
                    $contentJson[$k] = $v;
                }
            }

            $arMobile = org_glizy_objectFactory::createModel('movio.models.Mobilecontents');

        	// informaizoni da salvare per il menu:
        	// menuId, parent, titolo, pageType, type, contenuto
        	// salvare solo i menu visibili
        	// creare una tabella apposta e salvarci i dati dentro
        	//
        	if ($ar->menudetail_isVisible) {
            	$arMobile->content_menuId = $menuId;
        	    $arMobile->content_pageType = $ar->menu_pageType;
        	    $arMobile->content_parent = $ar->menu_parentId;
        	    $arMobile->content_type = $ar->menu_type;
        	    $arMobile->content_title = $ar->menudetail_title;

        	    if ($arMobile->content_pageType == 'Storyteller') {
        	        $contentJson = $this->processStoryTeller($contentJson);
        	    } elseif ($arMobile->content_pageType == 'Photogallery') {
        	        $contentJson = $this->processPhotoGallery($contentJson);
        	    } elseif ($arMobile->content_pageType == 'Photogallery_category') {
        	        $arMobile->content_pageType = 'Photogallery';
                    $contentJson = $this->processPhotoGalleryCategory($contentJson);
        	    } elseif ($arMobile->content_pageType == 'ImageHotspot') {
        	        $contentJson = $this->processImageHotspot($contentJson);
        	    } elseif ($arMobile->content_pageType == 'Timeline') {
                    $contentJson = $this->processTimeline($menuId, $contentJson);
        	    } elseif ($arMobile->content_pageType == 'Page') {
        	        $contentJson = $this->processPage($contentJson);
        	    } elseif ($arMobile->content_pageType == 'Exhibition') {
        	        $contentJson = $this->processExhibition($contentJson);
        	        $contentJson['isActive'] = $isExhibitionActive ? 1 : 0;
        	    }  elseif ($arMobile->content_pageType == 'DigitalExhibition') {
        	        $contentJson = $this->processDigitalExhibition($contentJson);
        	    }  elseif ($arMobile->content_pageType == 'Home') {
        	        $contentJson['title'] = $title;
        	        $contentJson['subtitle'] = $subtitle;
        	    }  elseif ($arMobile->content_pageType == 'Video') {
        	        $contentJson = $this->processVideo($contentJson);
        	    }  elseif ($arMobile->content_pageType == 'Cover') {
        	        $contentJson = $this->processCover($contentJson);
        	    }  elseif ($arMobile->content_pageType == 'GoogleMap') {
        	        $contentJson = $this->processGoogleMap($contentJson);
        	    }  elseif ($arMobile->content_pageType == 'Graph') {
        	        $contentJson = $this->processGraph($contentJson, $menuId, $languageCode);
        	    }

        	    if ($menuId == $creditPageId) {
    	        	$arMobile->content_pageType = 'Credits';
    	        	$arMobile->content_parent = 0;
        	    }

        	    $arMobile->content_content = json_encode($contentJson);
        	    $contentId = $arMobile->save();

        	    $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
        	    $fulltextAr = $it->where("id", $menuId)
                                 ->whereLanguageIs($languageId)
                                 ->selectIndex('fulltext', 'document_index_fulltext_name', 'document_index_fulltext_value')->first();

                if ($fulltextAr->document_index_fulltext_value) {
            	    $ar = org_glizy_objectFactory::createModel('movio.modules.publishApp.models.Mobilefulltext');
                    $ar->mobilefulltext_FK_content_id = $contentId;
            	    $ar->mobilefulltext_text = str_replace(' ##', '', $fulltextAr->document_index_fulltext_value);
            	    $ar->mobilefulltext_title = $contentJson['title'];
            	    $ar->mobilefulltext_subtitle = $contentJson['subtitle'];
            	    $ar->save();
                }

        	    // quando menu_pageType è Entity c'è da scorrere tutti i contenuti dell'entità
        	    // e caricare i dati
        	    // salvare
        	    // documenId, titolo, contenuto
        	    if ($arMobile->content_pageType == 'Entity') {
                    $parent = $arMobile->content_id;
                    $application = org_glizy_ObjectValues::get('org.glizy', 'application');

                    $it = __ObjectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityDocument');
                    $it->whereTypeIs('entity'.$contentJson['entitySelect']);

                    foreach ($it as $arEntitySelect) {
                        $documentId = $arEntitySelect->document_id;
                        $c = __ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.EntityToJSON', $application, $parent, 'glz:EntityToJSON', $documentId);
                        $c->setAttribute('visible', true);
                        $c->process();
                        $c->render();

                        $medias = $c->getMedias();
                        $this->addMediaArray($medias);

                        $graphCode = $c->getGraph();
                        $this->addGraph($languageCode, $documentId, $graphCode, 'document');

                        $jsonEntity = $c->getJson();
                        $jsonEntity['graph'] = 'graph/document/'.$languageCode.'/'.$documentId.'.svg';
                        $jsonEntity['content'] = $this->processText($jsonEntity['content']);

                        $arContentMobile = org_glizy_objectFactory::createModel('movio.models.Mobilecontents');
                        $arContentMobile->content_documentId = $documentId;
                        $arContentMobile->content_pageType = 'EntityChild';
                        $arContentMobile->content_parent = $parent;
                        $arContentMobile->content_title = $arEntitySelect->title;
                        $arContentMobile->content_content = json_encode($jsonEntity);
                        $contentId = $arContentMobile->save();

                    	$fulltextAr = org_glizy_objectFactory::createModel('movio.modules.publishApp.models.DocumentIndexFulltext');
                        $result = $fulltextAr->find(array('document_index_fulltext_FK_document_detail_id' => $arEntitySelect->document_detail_id));

                        if ($result) {
                            $ar = org_glizy_objectFactory::createModel('movio.modules.publishApp.models.Mobilefulltext');
                            $ar->mobilefulltext_FK_content_id = $contentId;
                            $ar->mobilefulltext_text = str_replace(' ##', '', $fulltextAr->document_index_fulltext_value);
                            $ar->mobilefulltext_title = $arEntitySelect->title;
            	            $ar->mobilefulltext_subtitle = $arEntitySelect->subtitle;
            	            $ar->save();
                        }
                    }
        	    }
        	}
        }
    }

    private function convertObjectToArray($data)
    {
        $result = array();
        if (is_object($data)) {
            $objectKeys = array_keys(get_object_vars($data));
            if ($objectKeys) {
                $numItems = 0;
                foreach($objectKeys as $k) {
                    $numItems = max(count($data->{$k}), $numItems);
                }
                for($i=0; $i < $numItems; $i++) {
                    $tempObj = new StdClass;
                    foreach($objectKeys as $k) {
                        $v = $data->{$k}[$i];
                        if (@strpos($v, '{"id"')===0) {
                            $v = $this->addMedia($v);
                        }

                        $tempObj->{$k} = $v;
                    }

                    $result[] = $tempObj;
                }
            }
        }

        return $result;
    }

    private function addMedia($media)
    {
        $result = new StdClass();
        $media = json_decode($media);
        $m = org_glizycms_mediaArchive_MediaManager::getMediaById($media->id);
        if ($m == null) {
            return null;
        }
        $result->type = $m->type;
        $result->title = $media->title;
        if ($result->type == 'VIDEO') {
            $result->url = GLZ_HOST.'/'.org_glizy_helpers_Media::getFileUrlById($media->id, true);
        } else {
            $result->fileName = $media->fileName;
            $this->medias[$media->id] = $media->fileName;
        }
        return $result;
    }

    private function addMediaById($mediaId)
    {
        $media = org_glizycms_mediaArchive_MediaManager::getMediaById($mediaId);

        if ($media == null) {
            return null;
        }

        if ($media->type == 'VIDEO') {
            return GLZ_HOST.'/'.org_glizy_helpers_Media::getFileUrlById($media->id);
        } else {
            $this->medias[$media->id] = $media->fileName;
            return 'media/'.$media->fileName;
        }
    }

    private function addMediaArray($medias)
    {
        foreach ($medias as $mediaId => $mediaFileName) {
            $this->medias[$mediaId] = $mediaFileName;
        }
    }

    public function getMedias()
    {
        return $this->medias;
    }

    private function addGraph($languageCode, $id, $graphCode, $type = '')
    {
        return $this->graphs[] = array('languageCode' => $languageCode, 'id' => $id, 'code' => $graphCode, 'type' => $type);
    }

    public function getGraphs()
    {
        return $this->graphs;
    }
}