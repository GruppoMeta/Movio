<?php
class org_glizycms_speakingUrl_PageResolver extends org_glizycms_speakingUrl_AbstractUrlResolver implements org_glizycms_speakingUrl_IUrlResolver
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'org.glizycms.core.models.Content';
        $this->protocol = 'internal:';
    }

    public function compileRouting($ar)
    {
        return '<glz:Route skipLanguage="true" value="'.$ar->language_code.'/'.$ar->speakingurl_value.'" pageId="'.$ar->speakingurl_FK.'" language="'.$ar->language_code.'"/>';
    }


    public function searchDocumentsByTerm($term, $id, $protocol='', $filterType='')
    {
        $result = array();
        if ($protocol && $protocol!=$this->protocol) return $result;

        $languageId = $this->editLanguageId;
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');

        if ($term) {
            $it->load('autocompletePagePicker', array('search' => '%'.$term.'%', 'languageId' => $languageId, 'menuId' => '', 'pageType' => $filterType));
        } else if ($id) {
            if (!is_numeric($id) && strpos($id, $this->protocol) !== 0) {
                return $result;
            } elseif (is_string($id)) {
                $id = $this->getIdFromLink($id);
            }

            $it->load('autocompletePagePicker', array('search' => '', 'languageId' => $languageId, 'menuId' => $id));
        }  else  {
            return $result;
        }

        foreach($it as $ar) {
            $result[] = array(
                'id' => $this->protocol.$ar->menu_id,
                'text' => $ar->menudetail_title,
                'path' => ltrim($ar->p1.'/'.$ar->p2.'/'.$ar->p3, '/').'/'.$ar->menudetail_title
            );
        }

        return $result;
    }

    public function makeUrl($id)
    {
        list($protocol, $id) = explode(':', $id);
        if ($protocol.':' == $this->protocol && is_numeric($id)) {
            return $this->makeUrlFromId($id);
        }
        return false;
    }

    public function makeLink($id)
    {
        list($protocol, $id) = explode(':', $id);
        if ($protocol.':' == $this->protocol && is_numeric($id)) {
            return $this->makeUrlFromId($id, true);
        }
        return false;
    }


    public function makeUrlFromRequest()
    {
        $id = __Request::get('pageId', __Config::get('START_PAGE'));
        return $this->makeUrlFromId($id);
    }

    private function makeUrlFromId($id, $fullLink=false)
    {
        $siteMap = $this->application->getSiteMap();
        $menu = $siteMap->getNodeById($id);
        if ($menu && $menu->isVisible) {
            $menuUrl = $menu->url;
            $menuTitle = $menu->title;
            $url = $menuUrl ? GLZ_HOST.'/'.$menuUrl : __Link::makeUrl('link', array('pageId' => $id, 'title' => $menuTitle));

            return $fullLink ? __Link::makeSimpleLink($menuTitle, $url) : $url;
        }

        // the menu isn't found or isn't visible in this language
        // redirect to home
        return $this->makeUrlFromId(__Config::get('START_PAGE'), $fullLink);
    }
}
