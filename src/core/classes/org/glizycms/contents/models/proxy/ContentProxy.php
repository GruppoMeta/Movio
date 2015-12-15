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
    public function readContentFromMenu($menuId, $languageId, $setMenuTitle=true)
    {
        $menuDocument = $this->readRawContentFromMenu($menuId, $languageId);
        $contentVO = $menuDocument ? $menuDocument->getContentVO() : $this->getContentVO();

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
    public function readRawContentFromMenu($menuId, $languageId)
    {
        $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
        $it->setOptions(array('type' => 'PUBLISHED_DRAFT'));
        $menuDocument = $it->where('id', $menuId)->first();
        if (!$menuDocument) {
            $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content')
                ->setOptions(array('type' => 'PUBLISHED_DRAFT'))
                ->whereLanguageIs($languageProxy->getDefaultLanguageId())
                ->where('id', $menuId);
            $menuDocument = $it->first(true);
        }

        return $menuDocument;
    }

    /**
     * Save the content for a menu
     * @param  org_glizycms_contents_models_ContentVO $data       Content to save
     * @param  int  $languageId Language id
     * @param  boolean  $publish    Publish or save
     */
    public function saveContent(org_glizycms_contents_models_ContentVO $data, $languageId, $publish=true, $setMenuTitle=true)
    {
        $speakingUrlProxy = __Config::get('glizycms.speakingUrl') ? org_glizy_ObjectFactory::createObject('org.glizycms.speakingUrl.models.proxy.SpeakingUrlProxy') : null;

// TODO gestire meglio gli errori tramite eccezioni
        $menuId = (int)$data->getId();
        if ($menuId) {
            $invalidateSitemapCache = false;

            $menuDocument = $this->readRawContentFromMenu($menuId, $languageId);
            $originalUrl = $menuDocument->url;
            $menuDocument->setDataFromContentVO($data);

            if ($speakingUrlProxy && $originalUrl != $menuDocument->url) {
                //valida l'url
                if (!$speakingUrlProxy->validate($menuDocument->url, $languageId, $menuId, 'org.glizycms.core.models.Content')) {
                    return 'Url non valido perché già utilizzato';
                }
            }

            // salva i dati
            if ($publish) {
                $menuDocument->publish(null, $data->getComment());
            } else {
                $menuDocument->save(null, false, 'PUBLISHED', $data->getComment());
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
            $menuProxy->touch($menuId, $languageId);
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

            $evt = array('type' => org_glizycms_contents_events_Menu::SAVE_CONTENT);
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
    }
}