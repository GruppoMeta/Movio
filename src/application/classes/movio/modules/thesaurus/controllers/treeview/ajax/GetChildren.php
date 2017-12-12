<?php
class movio_modules_thesaurus_controllers_treeview_ajax_GetChildren extends org_glizy_mvc_core_CommandAjax
{
    private $thesaurusProxy;
    private $dictionaryId;

    public function execute($termId)
    {
        $this->checkPermissionForBackend();
        
        $this->directOutput = true;
        $this->thesaurusProxy =org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $this->dictionaryId =  __Request::get('dictionaryId');
        $result = array();
        if ($termId==0) {
            // root
            $dict = $this->thesaurusProxy->getDictionaryById($this->dictionaryId);
            $result[] = $this->addNode($dict, true);
            return $result;
        }

        $terms = $this->thesaurusProxy->getFirstLevelChildrens($this->dictionaryId, $termId);
        foreach ($terms as $termObj) {
            $result[] = $this->addNode($termObj);
        }

        return $result;
    }

    protected function addNode($termObj, $isRoot=false) {
        $node = array(
            'data' => array(
                'title' => $isRoot ? $termObj->title : $termObj->term,
                'icon' => $isRoot ? 'folder' : 'page',
            ),
            'attr' =>array(
                'id' => $termObj->getId(),
                'rel' => $isRoot ? 'root' : 'default',
                'class' => '',
                'title' => $title
            )
        );

        if (!$isRoot) {
            $numChild = count((array)$this->thesaurusProxy->getFirstLevelChildrens($this->dictionaryId, $termObj->getId()));
            $node['metadata']['edit'] = $this->user->acl($this->application->getPageId(),'edit');
            $node['metadata']['delete'] = $this->user->acl($this->application->getPageId(),'delete');
            $node['metadata']['move'] = $this->user->acl($this->application->getPageId(),'move');
            $node['state'] = $numChild ? 'closed' : '';
        } else {
            $node['metadata']['delete'] = false;
            $node['metadata']['show'] = false;
            $node['metadata']['edit'] = 0;
            $node['metadata']['delete'] = 0;
            $node['metadata']['move'] = 0;
            $node['state'] = 'closed';
        }

        return $node;
    }
}