<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_views_components_Page extends org_glizy_components_Page
{
	private $templateData;
	private $customTemplate;
	private $selfId;

	protected $menu;
	protected $siteProp;


	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('allowBlocks',	false, false, 	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		if (!$this->_application->canViewPage() || !$this->checkAcl()) {
			org_glizy_helpers_Navigation::accessDenied($this->_user->isLogged());
		}

		$this->selfId = $this->getId();
		$this->_content = array();

		$this->loadMenuAndSiteProps();
		$this->checkRedirectUrl($this->menu->url);
		$this->loadContentFromDB();
		$this->loadTemplate();

		$this->processChilds();
	}


	function render()
	{
		$this->renderPageProperties($this->menu, $this->siteProp['title']);
		$this->renderSiteProperties($this->siteProp);


		if (is_object($this->customTemplate)) {
			$this->customTemplate->render($this->_application, $this, $this->templateData);
		}
		return parent::render();
	}


	function loadContent($id)
	{
		if (property_exists($this->_content, $id)) {
			return $this->_content->{$id};
		} else if (strpos($id, 'template:')===0) {
			$id = substr($id, strlen('template:'));
		} else if (strpos($id, $this->selfId)===0) {
			$id = substr($id, strlen($this->selfId)+1);
		} else {
			return '';
		}

		return property_exists($this->templateData, $id) ? $this->templateData->{$id} : '';
	}


	protected function loadContentFromDB()
	{
		// if ($this->_user->backEndAccess && org_glizy_Request::get( 'draft', '' ) == '1')
		// {
		// 	$versionStatus = 'DRAFT';
		// }
// TODO gestire lo stato PUBLISHED E DRAFT
		$contentProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');
		$this->_content = $contentProxy->readContentFromMenu($this->_application->getPageId(), org_glizy_ObjectValues::get('org.glizy', 'languageId'));
	}

	protected function loadTemplate()
	{
		if (__Config::get('glizycms.contents.templateEnabled')) {
	 		$templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
	        $templateName = $templateProxy->getSelectedTemplate();
	        $templatePath = $templateProxy->getTemplateRealpath();
	        $this->templateData = $templateProxy->getDataForMenu($this->_application->getPageId());

			// if is defined a custom XML file read and attach to component DOM
			if (file_exists($templatePath.'/Template.xml'))
			{
				org_glizy_ObjectFactory::attachPageToComponent(
	                $this,
	                $this->_application,
	                'Template',
	                $templateProxy->getTemplateRealpath(),
	                array(),
	                $this->selfId.'-',
	                false);
			}

			// check if there is a templateFileName override
			if (property_exists($this->templateData, 'templateFileName') && $this->templateData->templateFileName != 'default') {
				$this->setAttribute('templateFileName', $this->templateData->templateFileName);
			}

			$this->customTemplate = $templateProxy->getTemplateCustomClass();

			if (is_object($this->customTemplate) && method_exists($this->customTemplate, 'process')) {
				$this->customTemplate->process($this->_application, $this, $this->templateData);
			}
		}
	}

	protected function loadSiteProperties()
	{
		// $menu = $this->_application->getCurrentMenu();
		$siteProp = $this->_application->getSiteProperty();

	}

	protected function renderPageProperties($menu, $siteName)
	{
		$title = org_glizy_ObjectValues::get('org.glizy.og', 'title', $menu->title );
		$description = org_glizy_ObjectValues::get('org.glizy.og', 'description', $menu->description );
        $keywords = org_glizy_ObjectValues::get('org.glizy.og', 'keywords', $menu->keywords );

        $pageTitle = $title.' - '.$siteName;
		$this->addOutputCode(glz_encodeOutput($pageTitle), 'docTitle');
		$this->addOutputCode(glz_encodeOutput($pageTitle), 'doctitle'); // NOTE: per compatibilità
        $this->addOutputCode($title, 'metadata_title');
        $this->addOutputCode($description, 'metadata_description');
        $this->addOutputCode($keywords, 'metadata_keywords');

		$reg = __T( strlen( $menu->creationDate ) <= 10 || preg_match('/00:00:00|12:00:00 AM/', $menu->creationDate) ? 'GLZ_DATE_FORMAT' : 'GLZ_DATETIME_FORMAT' );
		$updateText= org_glizy_locale_Locale::get('MW_FOOTER',
													glz_defaultDate2locale($reg, $menu->creationDate),
													glz_defaultDate2locale($reg, $menu->modificationDate));
		$this->addOutputCode($updateText, 'docUpdate');
	}


	protected function renderSiteProperties($siteProp)
	{
        $this->addOutputCode($siteProp['copyright'], 'copyright');
		$this->addOutputCode(org_glizy_helpers_Link::parseInternalLinks($siteProp['address']), 'address');

		$slideShowSpeed = ((int)$siteProp['slideShow'] ? :5)*1000;
		$this->addOutputCode( org_glizy_helpers_JS::JScode( 'if (typeof(Glizy)!=\'object\') Glizy = {}; Glizy.slideShowSpeed = '.$slideShowSpeed.';' ), 'head' );
	}

	/**
	 * @return null
	 */
	protected function loadMenuAndSiteProps()
	{
		$this->menu = $this->_application->getCurrentMenu();
		$this->siteProp = $this->_application->getSiteProperty();
	}

	/**
	 * @param  string $url
	 */
	protected function checkRedirectUrl($url)
	{
		if (strpos($url, 'http')===0) {
            org_glizy_helpers_Navigation::gotoUrl($url);
        }
	}
}
