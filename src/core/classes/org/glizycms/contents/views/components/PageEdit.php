<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_contents_views_components_PageEdit  extends org_glizycms_views_components_FormEdit
{
    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_DRAFT = 'DRAFT';

	protected $emptySrc;
	protected $editSrc;
    protected $_pageTypeObj;
    protected $allowBlocks;
	protected $menuId;

    protected $statusToEdit;
    protected $availableStatus;
    protected $languageId;

    /**
     * @var org_glizycms_contents_models_proxy_ContentProxy
     */
    private $contentProxy;
    /**
     * @var org_glizycms_contents_models_proxy_MenuProxy
     */
    private $menuProxy;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	public function init()
	{
		$this->defineAttribute('mode', false, 'container', COMPONENT_TYPE_STRING);
		$this->defineAttribute('initialState', false, 'empty', COMPONENT_TYPE_STRING);
		$this->defineAttribute('editState', false, 'edit', COMPONENT_TYPE_STRING);
		$this->defineAttribute('editUrl', false, __Config::get('glizycms.speakingUrl'), COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:cssClass', false, __Config::get('glizy.formElement.admCssClass'), COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	public function process() {
        $this->params();

		if ($this->getAttribute('mode')=='container') {
			$this->emptySrc = __Routing::makeUrl('linkChangeAction', array( 'action' => $this->getAttribute('initialState')));
			$this->editSrc = __Routing::makeUrl('linkChangeAction', array( 'action' => $this->getAttribute('editState'))).'?menuId=';
		} else {

// TODO: lanciare un'eccezione se l'id non è valido
			$menuProxy = $this->getMenuProxy();
	        $menu = $menuProxy->getMenuFromId($this->menuId, $this->languageId);
// TODO: il menù viene letto due volte, in questo codice ed in readContentFromMenu
			$contentProxy = $this->getContentProxy();
			$content = $contentProxy->readContentFromMenu($this->menuId, $this->languageId, true, $this->statusToEdit);
			$this->setData($content);

			$this->addComponentsToEdit($menu);
			parent::process();
		}
	}

	public function render_html_onStart()
	{
        if ($this->getAttribute('mode')=='container') {

			$this->addOutputCode('<iframe id="js-glizycmsPageEdit" src="" data-emptysrc="'.$this->emptySrc.'" data-editsrc="'.$this->editSrc.'"></iframe>');

            $corePath = __Paths::get('CORE');
            $jQueryPath = $corePath.'classes/org/glizycms/js/jquery/';
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ) );

    	} else {
            $this->renderStatusSwicth();
			parent::render_html_onStart();
		}
	}

	public function render_html_onEnd($value='')
	{
		if ($this->getAttribute('mode')!='container') {
			parent::render_html_onEnd();

            if ($this->allowBlocks) {
                $this->addOutputCode('<div class="js-glizycmsBlocksEdit" data-menuid="'.$this->menuId.'" data-ajaxurl="'.$this->getAjaxUrl().'"></div>');
            }
		}
	}



    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return 'ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().'&status='.$this->statusToEdit.'&action=';
    }


    /**
     * @return array
     */
    public function availableStatus()
    {
        return $this->availableStatus;
    }

    /**
     * @return string
     */
    public function statusToEdit()
    {
        return $this->statusToEdit;
    }



	protected function addComponentsToEdit($menu)
	{
		$templatePath = org_glizycms_Glizycms::getSiteTemplatePath();
		$originalRootComponent 	= &$this->_application->getRootComponent();
		$originalChildren = $this->childComponents;
		$this->childComponents = array();

		$this->addDefaultComponents($menu);

		$this->_pageTypeObj = &org_glizy_ObjectFactory::createPage($this->_application,
				$menu->menu_pageType,
				org_glizy_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE'),
				array(	'idPrefix' => $this->getId().'-',
						'skipImport' => true,
						'pathTemplate' => $templatePath,
						'mode' => 'edit' ) );

		$rootComponent = &$this->_application->getRootComponent();
		$rootComponent->init();
		$this->_application->_rootComponent = &$originalRootComponent;
        $editComponents = $rootComponent->getAttribute('adm:editComponents');
        $this->allowBlocks = $rootComponent->getAttribute('allowBlocks');
		$this->setAttribute('newCode', $rootComponent->getAttribute('adm:formEditNewCode')==='true');
		if (count($editComponents))
		{
			foreach($editComponents as $id)
			{
				$component = &$rootComponent->getComponentById($this->getId().'-'.$id);
				if (!is_object($component)) continue;
				$component->remapAttributes($this->getId().'-');
				$this->addChild($component);
				$component->_parent = &$this;
                $component->setAttribute('visible', true);
				$component->setAttribute('enabled', true);
			}
		}
		else
		{
			for($i=0; $i<count($rootComponent->childComponents); $i++)
			{
				$rootComponent->childComponents[$i]->remapAttributes($this->getId().'-');
				$this->addChild($rootComponent->childComponents[$i]);
				$rootComponent->childComponents[$i]->_parent = &$this;
			}
		}

		$this->childComponents = array_merge($this->childComponents, $originalChildren);
	}

	private function addDefaultComponents($menu) {
		$id = '__id';
		$c = org_glizy_ObjectFactory::createComponent('org.glizy.components.Hidden', $this->_application, $this, 'glz:Hidden', $id, $id);
        $this->addChild($c);
        $c->init();

        $id = '__indexFields';
		$childs[$id] = org_glizy_ObjectFactory::createComponent('org.glizy.components.Hidden', $this->_application, $this, 'glz:Hidden', $id, $id);
        $c = &$childs[$id];
        $this->addChild($c);
        $c->init();

        $id = '__title';
        $childs[$id] = org_glizy_ObjectFactory::createComponent('org.glizy.components.Input', $this->_application, $this, 'glz:Input', $id, $id);
        $c = &$childs[$id];
        $c->setAttribute('label', __T('Title'));
        $c->setAttribute('required', true);
        $c->setAttribute('size', '90');
        $c->setAttribute('cssClass', $this->getAttribute('adm:cssClass'));
        $this->addChild($c);
        $c->init();


        $id = '__url';
        if ($this->getAttribute('editUrl')) {
    	    $childs[$id] = org_glizy_ObjectFactory::createComponent('org.glizy.components.Input', $this->_application, $this, 'glz:Input', $id, $id);
        } else {
	        $childs[$id] = org_glizy_ObjectFactory::createComponent('org.glizy.components.Hidden', $this->_application, $this, 'glz:Hidden', $id, $id);
        }
        $c = &$childs[$id];
        $c->setAttribute('label', __T('URL'));
        $c->setAttribute('required', false);
        $c->setAttribute('size', '90');
        $c->setAttribute('cssClass', $this->getAttribute('adm:cssClass'));
        $this->addChild($c);
        $c->init();
	}

    protected function params()
    {
        $this->menuId = __Request::get('menuId');
        $this->languageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');

        if ($this->menuId && __Config::get('glizycms.content.draft')) {
            $contentProxy = $this->getContentProxy();
            $this->availableStatus = $contentProxy->availableContentFromMenu($this->menuId, $this->languageId);
            $defaultState = !$this->availableStatus ?
                        self::STATUS_PUBLISHED :
                        ($this->availableStatus[self::STATUS_DRAFT] ? self::STATUS_DRAFT : self::STATUS_PUBLISHED);
            $this->statusToEdit = __Request::get('status', $defaultState);
        } else {
            $this->statusToEdit = self::STATUS_PUBLISHED;
        }
    }

    /**
     * @return org_glizycms_contents_models_proxy_ContentProxy
     */
    protected function getContentProxy()
    {
        if (!$this->contentProxy) {
            $this->contentProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');
        }
        return $this->contentProxy;
    }

    /**
     * @return org_glizycms_contents_models_proxy_MenuProxy
     */
    protected function getMenuProxy()
    {
        if (!$this->menuProxy) {
            $this->menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        }
        return $this->menuProxy;
    }

    private function renderStatusSwicth()
    {
        if (!$this->availableStatus ||
            ($this->availableStatus[self::STATUS_PUBLISHED] && !$this->availableStatus[self::STATUS_DRAFT])) return;
        $label = __T('GLZ_RECORD_STATUS_EDIT_LABEL');
        $linkPublished = !$this->availableStatus[self::STATUS_PUBLISHED] ? '' : __Link::makeSimpleLink(
                                                    __T('GLZ_RECORD_STATUS_PUBLISHED'),
                                                    __Link::addParams(array('status' => self::STATUS_PUBLISHED)),
                                                    '',
                                                    $this->statusToEdit==self::STATUS_PUBLISHED ? 'active' : ''
        );

        $linkDraft = !$this->availableStatus[self::STATUS_DRAFT] ? '' :__Link::makeSimpleLink(
                                                    __T('GLZ_RECORD_STATUS_DRAFT'),
                                                    __Link::addParams(array('status' => self::STATUS_DRAFT)),
                                                    '',
                                                    $this->statusToEdit==self::STATUS_DRAFT ? 'active' : ''
        );

        $html = <<<EOD
<ul class="glzFormEdit status-swicth {$this->statusToEdit}">
    <li>$label</li>
    <li>$linkPublished</li>
    <li>$linkDraft</li>
</ul>
EOD;
        $this->addOutputCode($html);
    }
}