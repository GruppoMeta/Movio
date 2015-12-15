<?php
class org_glizycms_contents_controllers_pageEdit_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    protected $menuId;

    public function execute($data)
    {
        $this->directOutput = true;
// TODO: controllo acl
        $contentProxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');
        $contentVO = $contentProxy->getContentVO();
        $contentVO->setFromJson($data);
        $this->menuId = $contentVO->getId();
        $r = $contentProxy->saveContent($contentVO,
                            org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'),
                            __Config::get('glizycms.content.history')
                            );

        if ($r===true) {
            return true;
        } else {
            return array('errors' => array($r));
        }
    }
}