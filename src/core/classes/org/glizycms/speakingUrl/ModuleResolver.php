<?php
class org_glizycms_speakingUrl_ModuleResolver extends org_glizycms_speakingUrl_AbstractUrlResolver implements org_glizycms_speakingUrl_IUrlResolver
{
    protected $moduleVO;
    protected $moduleName;
    protected $model;
    protected $routingUrl;
    protected $titleField;

    public function __construct($moduleVO, $routingUrl, $modelName=null, $titleField='title')
    {
        parent::__construct();
        $this->moduleVO = $moduleVO;
        $this->type = $moduleVO->id;
        $this->protocol = $moduleVO->id.':';
        $this->moduleName = $moduleVO->name;
        $this->model = $modelName ? : $moduleVO->id.'.models.Model';
        $this->routingUrl = $routingUrl;
        $this->titleField = $titleField;
    }

    public function compileRouting($ar)
    {
        return '';
    }


    public function searchDocumentsByTerm($term, $id, $protocol='', $filterType='')
    {
        $result = array();
        if ($protocol && $protocol!=$this->protocol) return $result;

        $languageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');

        if ($term) {
            $it = org_glizy_objectFactory::createModelIterator($this->model)
                    ->load('all')
                    ->orderBy($this->titleField);

            if ($term) {
                $it->where($this->titleField, '%'.$term.'%', 'ILIKE');
            }

            foreach($it as $ar) {
                $result[] = array(
                    'id' => $this->protocol.$ar->document_id,
                    'text' => $ar->{$this->titleField},
                    'path' => $this->moduleName
                );
            }

        } else if ($id && strpos($id, $this->protocol) === 0) {
            $ar = org_glizy_objectFactory::createModel($this->model);
            $ar->load($this->getIdFromLink($id));
            $result[] = array(
                    'id' => $this->protocol.$ar->document_id,
                    'text' => $ar->title,
                    'path' => $this->moduleName
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

    // TODO implementare, non va bene per i moduli
    public function makeUrlFromRequest()
    {
        $id = __Request::get('id', null);
        $resolvedVO = $this->createResolvedVO($id);
        return $resolvedVO->url;
    }

    protected function createResolvedVO($id, $queryString='')
    {
        $ar = org_glizy_objectFactory::createModel($this->model);
        if ($id && $ar->load($id)) {
            $url = (__Link::makeUrl($this->routingUrl, array('document_id' => $id, 'title' => $ar->{$this->titleField}))).$queryString;
            $link = __Link::makeSimpleLink($ar->{$this->titleField}, $url);

            $resolvedVO = org_glizycms_speakingUrl_ResolvedVO::create(
                                    $ar,
                                    $url,
                                    $link,
                                    $ar->{$this->titleField}
                                );
            return $resolvedVO;
        }

        // TODO implementare, non va bene per i moduli
        // // the menu isn't found or isn't visible in this language
        // // redirect to home
        // return $this->makeUrlFromId(__Config::get('START_PAGE'), $fullLink);
        return org_glizycms_speakingUrl_ResolvedVO::create404();
    }

    public function modelName()
    {
        return $this->model;
    }

    public function makeUrlFromModel($model)
    {
        return __Link::makeUrl($this->routingUrl, array('document_id' => $model->getId(), 'title' => $model->{$this->titleField}));
    }
}
