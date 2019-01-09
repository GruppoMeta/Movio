<?php

class org_glizycms_views_components_PageTypeInclude extends org_glizy_components_ComponentContainer
{

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    public function init()
    {
        $this->defineAttribute('src', false, '', COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }


    /**
     * @throws Exception
     */
    public function process()
    {
        $this->addComponentsToEdit($this->getAttribute('src'));
        parent::process();
    }


    /**
     * @param $src
     * @throws Exception
     */
    protected function addComponentsToEdit($src)
    {
        $originalRootComponent = &$this->_application->getRootComponent();
        $this->childComponents = array();

        $pageTypeObj = org_glizy_ObjectFactory::createPage($this->_application,
            $src,
            org_glizy_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE'),
            array(/*'idPrefix' => $this->getId() . '-',*/
                'skipImport' => true,
                'mode' => 'edit'));

        $rootComponent = &$this->_application->getRootComponent();
        $rootComponent->init();
        $this->_application->_rootComponent = &$originalRootComponent;
        for ($i = 0; $i < count($rootComponent->childComponents); $i++) {
            //$rootComponent->childComponents[$i]->remapAttributes($this->getId() . '-');
            $this->addChild($rootComponent->childComponents[$i]);
            $rootComponent->childComponents[$i]->_parent = &$this;
        }
    }
}
