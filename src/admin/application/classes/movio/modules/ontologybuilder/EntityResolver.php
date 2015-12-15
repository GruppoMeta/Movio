<?php
class movio_modules_ontologybuilder_EntityResolver extends org_glizycms_speakingUrl_AbstractUrlResolver implements org_glizycms_speakingUrl_IUrlResolver
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'movio.modules.ontologybuilder.content';
        $this->protocol = 'movioContent:';
    }

    public function compileRouting($ar)
    {
        $option = unserialize($ar->speakingurl_option);
        $entityTypeId = $option['entityTypeId'];

        // ricava il pageid tramite entitySelect
        $menu = $this->getPage($entityTypeId);

        return $menu ? '<glz:Route skipLanguage="true" value="'.$ar->language_code.'/'.$ar->speakingurl_value.'" action="show" pageId="'.$menu->id.'" entityTypeId="'.$entityTypeId.'" document_id="'.$ar->speakingurl_FK.'" cms:urlResolver="movio.modules.ontologybuilder.content"  />' : '';
    }

    // NOTA: questo moetodo dovrebbe essere spostato  in un'altra classe
    // perché questa classe ha il compito di gestire gli url paranti, lo switch della lingua e la risoluzione dei link codificati
    public function getMenuVisibleEntity($entityTypeId)
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityMenu');
        $menuDocument = $it->load('getVisibleEntityByTypeId', array('entityTypeId' => $entityTypeId))->first();

        if (!is_null($menuDocument)) {
            return $menuDocument;
        } else {
            return null;
        }
    }

    protected function getIdFromLink($id)
    {
        return str_replace($this->protocol, '', $id);
    }

    public function searchDocumentsByTerm($term, $id, $protocol='', $filterType='')
    {
        $result = array();
        if ($protocol && $protocol!=$this->protocol) return $result;

        $application = __ObjectValues::get('org.glizy', 'application');
        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');

        if ($term) {
            $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityDocument', 'All');

            if ($term != '') {
                $it->where('title', '%'.$term.'%', 'ILIKE');
            }

            $it->orderBy('title');

            foreach($it as $ar) {
                $entityTypeId = $entityTypeService->getEntityTypeId($ar->document_type);

                $result[] = array(
                    'id' => $this->protocol.$ar->document_id,
                    'text' => $ar->title,
                    'path' =>  __T('Content').'/' . $entityTypeService->getEntityTypeName($entityTypeId)
                );
            }
        } elseif ($id) {
            if (strpos($id, $this->protocol) !== 0) {
                return $result;
            }

            $ar = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityDocument');
            $ar->load($this->getIdFromLink($id));
            $entityTypeId = $entityTypeService->getEntityTypeId($ar->document_type);

            $result[] = array(
                'id' => $this->protocol.$ar->document_id,
                'text' => $ar->title,
                'path' =>  __T('Content').'/' . $entityTypeService->getEntityTypeName($entityTypeId)
            );
        }

        return $result;
    }

    public function makeUrl($id)
    {
        if (strpos($id, $this->protocol) === 0) {
            $id = $this->getIdFromLink($id);
            return $this->makeUrlFromId($id);
        } else {
            return false;
        }
    }


    public function makeLink($id)
    {
        if (strpos($id, $this->protocol) === 0) {
            $id = $this->getIdFromLink($id);
            return $this->makeUrlFromId($id, true);
        } else {
            return false;
        }
    }

    public function makeUrlFromRequest()
    {
        $id = __Request::get('document_id');
        return $this->makeUrlFromId($id);
    }

    private function makeUrlFromId($id, $fullLink=false)
    {
        $ar = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityDocument');
        if ($ar->load($this->getIdFromLink($id))) {
            $entityTypeId = $this->getEntityTypeId($ar->document_type);
            $menu = $this->getPage($entityTypeId);

            if ($ar->document_detail_isVisible && $ar->document_detail_translated && $menu) {
                // if the document is visible and is traslated and the entity page exists
                if ($ar->keyInDataExists('url') && $ar->url) {
                    $language = $this->application->getLanguage();
                    $url = GLZ_HOST.'/'.$language.'/'.$ar->url;
                } else {
                    $url = __Link::makeUrl('showEntityDetail', array(   'pageId' => $menu->id,
                                                                        'entityTypeId' => $entityTypeId,
                                                                        'document_id' => $id,
                                                                        'title' => $ar->title));
                }

                return $fullLink ? __Link::makeSimpleLink($ar->title, $url) : $url;
            }
        }

        // document not found, isn't visible or isn't traslated
        // go to entity page or home
        $speakingUrlManager = $this->application->retrieveProxy('org.glizycms.speakingUrl.Manager');
        return $speakingUrlManager->makeUrl('internal:'.( $menu ? $menu->id : __Config::get('START_PAGE')));
    }

    protected function getPage($entityTypeId)
    {
        $siteMap = $this->application->getSiteMap();
        $ar = org_glizy_objectFactory::createModel('org.glizycms.core.models.Content');
        $ar->content = new StdClass();
        $ar->content->entitySelect = $entityTypeId;
        $ar->addFieldsToIndex(array('entitySelect' => 'text'));
        $menuDocument = $ar->createRecordIterator()->where('entitySelect', $entityTypeId);
        foreach ($menuDocument as $ar) {
            $menu = $siteMap->getNodeById($ar->id);
            // check if the page is visible
            if ($menu && $menu->isVisible && $menu->pageType=='Entity') {
                return $menu;
            }
        }

        return null;
    }

    protected function getEntityTypeId($type)
    {
        $entityTypeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        return $entityTypeService->getEntityTypeId($type);
    }
}
