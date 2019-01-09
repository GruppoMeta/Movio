<?php
class org_glizycms_speakingUrl_PageResolver extends org_glizycms_speakingUrl_AbstractUrlResolver implements org_glizycms_speakingUrl_IUrlResolver
{
    private $multilanguage;

    public function __construct()
    {
        parent::__construct();
        $this->type = 'org.glizycms.core.models.Content';
        $this->protocol = 'internal:';
        $this->multilanguage = __Config::get('MULTILANGUAGE_ENABLED');
    }

    public function compileRouting($ar)
    {
        $language = $this->multilanguage ? $ar->language_code.'/' : '';
        return '<glz:Route skipLanguage="true" value="'.$language.$ar->speakingurl_value.'" pageId="'.$ar->speakingurl_FK.'" language="'.$ar->language_code.'"/>';
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

            $it->load('autocompletePagePicker', array('search' => '', 'languageId' => $languageId, 'menuId' => $id, 'pageType' => ''));
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
        $resolvedVO = $this->resolve($id);
        return $resolvedVO ? $resolvedVO->url : false;
    }

    public function makeLink($id)
    {
        $resolvedVO = $this->resolve($id);
        return $resolvedVO ? $resolvedVO->link : false;
    }

    public function resolve($id)
    {
        $info = $this->extractProtocolAndId($id);
        if ($info->protocol.':' === $this->protocol && is_numeric($info->id)) {
            return $this->createResolvedVO($info->id, $info->queryString);
        }
        return false;
    }

    public function makeUrlFromRequest()
    {
        $id = __Request::get('pageId', __Config::get('START_PAGE'));
        $resolvedVO = $this->createResolvedVO($id);
        return $resolvedVO->url;
    }

    protected function createResolvedVO($id, $queryString='')
    {
        $siteMap = $this->application->getSiteMap();
        $menu = $siteMap->getNodeById($id);

        if ($menu) {
            $menuUrl = $menu->url;
            $menuTitle = $menu->title;
            $url = ($menuUrl ? GLZ_HOST.'/'.$menuUrl : __Link::makeUrl('link', array('pageId' => $id, 'title' => $menuTitle))).$queryString;

            $resolvedVO = org_glizycms_speakingUrl_ResolvedVO::create(
                        $menu,
                        $url,
                        __Link::makeSimpleLink($menuTitle, $url),
                        $menuTitle
                    );
            return $resolvedVO;
        }

        // the menu isn't found or isn't visible in this language
        // redirect to home
        return $this->createResolvedVO(__Config::get('START_PAGE'));
    }
}
