<?php
class org_glizycms_contents_models_proxy_ContentProxy extends GlizyObject
{
    /**
     * Return a object with the system property for read or save the content values
     * @return org_glizycms_contents_models_ContentVO
     */
    public function getContentVO()
    {
        $vo = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.ContentVO');
        return $vo;
    }


    /**
     * Read the content for a menu
     * @param  int  $menuId     Menu id
     * @param  int  $languageId Language id
     * @return org_glizycms_contents_models_ContentVO   Content
     */
    public function readContentFromMenu($menuId, $languageId, $setMenuTitle=true, $status='PUBLISHED')
    {
        $menuDocument = $this->readRawContentFromMenu($menuId, $languageId, $status);
        $contentVO = $menuDocument ? $menuDocument->getContentVO() : $this->getContentVO();

        if ($menuDocument && $contentVO->__status!=$status) {
            $contentVO = $this->getContentVO();
        }

        // il contenuto può non esserci
        // viene caricato il titolo e l'id dal menù
        $contentVO->setId($menuId);

        $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        $menu = $menuProxy->getMenuFromId($menuId, $languageId);
        if ($setMenuTitle){
            $contentVO->setTitle($menu->menudetail_title);
        }
        if (__Config::get('glizycms.speakingUrl')) {
            $contentVO->setUrl($menu->speakingurl_value);
        }

        return $contentVO;
    }

    public function availableContentFromMenu($menuId, $languageId)
    {
        $menuDocument = $this->readRawContentFromMenu($menuId, $languageId, org_glizy_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED_DRAFT);

        $hasPublishedVersion = $menuDocument->hasPublishedVersion();
        $hasDraftVersion = $menuDocument->hasDraftVersion();

        if (is_null($hasPublishedVersion) && is_null($hasDraftVersion)) {
            $hasPublishedVersion = true;
        }

        return array(
                        'PUBLISHED' => $hasPublishedVersion,
                        'DRAFT' => $hasDraftVersion
                    );
    }


    /**
     * Read the content for a menu
     *
     * NOTA: per velocizzare non viene controllato se il menù esiste
     * quindi se il menù non esiste oppure se non ci sono contenuti
     * ritorna un oggetto nullo
     *
     * @param  int  $menuId     Menu id
     * @param  int  $languageId Language id
     * @return org_glizycms_contents_models_Content   Content
     */
    public function readRawContentFromMenu($menuId, $languageId, $status)
    {
        $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
        if ($status==org_glizy_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED_DRAFT) {
            $it->setOptions(array('type' => $status, 'language' => $languageId));
        } else {
            $it->whereStatusIs($status);
            $it->whereLanguageIs($languageId);
        }

        $menuDocument = $it->where('id', $menuId)->first();

        if (!$menuDocument) {
            $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
            $it->setOptions(array('type' => $status=='PUBLISHED' ? 'DRAFT' : 'PUBLISHED'));
            $it->whereLanguageIs($languageId);
            $menuDocument = $it->where('id', $menuId)->first();
        }
        if (!$menuDocument) {
            $menuDocument = org_glizy_objectFactory::createModel('org.glizycms.core.models.Content');
        }
        return $menuDocument;
    }


    /**
     * Save the content for a menu
     * @param  org_glizycms_contents_models_ContentVO $data       Content to save
     * @param  int  $languageId Language id
     * @param  boolean  $saveHistory    Publish or save
     * @param  boolean  $setMenuTitle
     * @param  boolean  $updateModificationDate
     * @param  boolean  $draft
     * @param  boolean  $publishDraft
     */
    public function saveContent(org_glizycms_contents_models_ContentVO $data, $languageId, $saveHistory=true, $setMenuTitle=true, $updateModificationDate=true, $draft=false, $publishDraft=false)
    {
        $speakingUrlProxy = __Config::get('glizycms.speakingUrl') ? org_glizy_ObjectFactory::createObject('org.glizycms.speakingUrl.models.proxy.SpeakingUrlProxy') : null;

// TODO gestire meglio gli errori tramite eccezioni
        $menuId = (int)$data->getId();
        if ($menuId) {
            $invalidateSitemapCache = false;

            $menuDocument = $this->readRawContentFromMenu($menuId, $languageId, $draft ? 'DRAFT' : 'PUBLISHED');
            $originalUrl = $menuDocument->url;
            $menuDocument->setDataFromContentVO($data);

            if (
                ($speakingUrlProxy && $menuDocument->url && $originalUrl != $menuDocument->url) &&
                !$speakingUrlProxy->validate($menuDocument->url, $languageId, $menuId, 'org.glizycms.core.models.Content')
                ) {
                //valida l'url
                return __T('Url already used');
            }

            try {
                if (($saveHistory && !$draft) || $publishDraft===true) {
                    $id = $menuDocument->publish(null, $data->getComment());
                    if (!$saveHistory) {
                        // delete all OLD
                        $menuDocument->deleteStatus('OLD');
                    }
                } else if ($saveHistory && $draft) {
                    $id = $menuDocument->saveHistory(null, false, $data->getComment());
                } else if (!$saveHistory && !$draft) {
                    $id = $menuDocument->save(null, false, 'PUBLISHED', $data->getComment());
                } else if (!$saveHistory && $draft) {
                    $id = $menuDocument->save(null, false, 'DRAFT', $data->getComment());
                }
            }
            catch (org_glizy_validators_ValidationException $e) {
                return $e->getErrors();
            }

            if ($speakingUrlProxy && $originalUrl!=$menuDocument->url) {
                // aggiorna l'url parlante
                $speakingUrlProxy = org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.proxy.SpeakingUrlProxy');
                if ($menuDocument->url) {
                    $speakingUrlProxy->addUrl($menuDocument->url, $languageId, $menuId, 'org.glizycms.core.models.Content');
                } else {
                    $speakingUrlProxy->deleteUrl($languageId, $menuId, 'org.glizycms.core.models.Content');
                }
                $invalidateSitemapCache = true;
            }

            // aggiorna il titolo della pagina
            $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            if ($updateModificationDate) {
                $menuProxy->touch($menuId, $languageId);
            }
            $menu = $menuProxy->getMenuFromId($menuId, $languageId);
            if ($setMenuTitle && $menu->menudetail_title != $menuDocument->title) {
                $menuProxy->rename($menuId, $languageId, $menuDocument->title);
            }

            // TODO implementare meglio
            if (strtolower($menu->menu_pageType) == 'alias') {
                $menu->menu_url = 'alias:'.$data->link;
                $menu->save();
                $invalidateSitemapCache = true;
            }

            if ($invalidateSitemapCache) {
                $menuProxy->invalidateSitemapCache();
            }

            $evt = array('type' => org_glizycms_contents_events_Menu::SAVE_CONTENT, 'data' => array('document' => $menuDocument, 'menu' => $menu));
            $this->dispatchEvent($evt);

            return true;
        } else {
            // TODO: errore dati non validi
        }
    }


    public function deleteContent($menuId)
    {
        // cancella il contenuto del documento associato
        $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
        $menuDocument = $it->load('getContentForMenu', array('menuId' => $menuId))->first();

        if ($menuDocument) {
            $menuDocument->delete();
        }

        if (__Config::get('glizycms.speakingUrl')) {
            $speakingUrlProxy = org_glizy_ObjectFactory::createObject('org.glizycms.speakingUrl.models.proxy.SpeakingUrlProxy');
            $speakingUrlProxy->deleteUrl(org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'), $menuId, 'org.glizycms.core.models.Content');
        }
    }

    /**
     * Duplicate a menu and its contents
     *
     * @param int $menuId      the menu id to copy
     * @param int $allBranch   copy all childs if true
     * @param int $parentId    the parentId of menu (optional)
     * @return int             new menu id
     */
    public function duplicateMenuAndContent($menuId, $allBranch = false, $parentId = null)
    {
        $languageId = __ObjectValues::get('org.glizy', 'editingLanguageId');
        $menuProxy = __ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        $duplicateId = $menuProxy->duplicateMenu($menuId, $languageId, $parentId);

        $menu = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');

        foreach ($menu->getLanguagesId() as $langId) {
            $menu = $menuProxy->getMenuFromId($menuId, $langId);
            $title = __T('Copy of').' '.$menu->menudetail_title;

            $contentVO = $this->readContentFromMenu($menuId, $langId);
            $contentVO->setId($duplicateId);
            $contentVO->setTitle($title);
            $contentVO->setUrl('');
            $this->saveContent($contentVO, $langId, __Config::get('glizycms.content.history'));
        }

        // duplica i figli
        $itMenus = $menuProxy->getChildMenusFromId($menuId, $languageId, false);
        foreach($itMenus as $subMenu) {
            if ($subMenu->menu_type =='BLOCK' || $allBranch)  {
                $this->duplicateMenuAndContent($subMenu->menu_id, $allBranch, $duplicateId);
            }
        }

        return $duplicateId;
    }
}
