<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Page extends org_glizy_components_ComponentContainer
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->acceptOutput = true;
		$this->overrideEditableRegion = false;

		// define the custom attributes
		$this->defineAttribute('defaultEditableRegion',	false, 'content', COMPONENT_TYPE_STRING);
		$this->defineAttribute('templateType', 			false, 'php', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('templateFileName',		true, NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:editComponents',	false, array(), 	COMPONENT_TYPE_ENUM);
		$this->defineAttribute('addCoreJS',	false, false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('allowModulesSnippets',	false, false, 	COMPONENT_TYPE_BOOLEAN);

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

		$this->processChilds();
	}


	/**
	 * Render
	 *
	 * @return	string
	 * @access	public
	 */
	function render()
	{
		$t = '';
		$this->applyOutputFilters('pre', $t);
		$this->renderChilds();
		return $this->_render();
	}

	function _render()
	{
		if ( $this->getAttribute( 'addCoreJS' ) === true )
		{
			$this->_application->addJSLibCore();
		}

		$template = NULL;

		// riordina l'array con i dati dell'editableRegions da passare alla classe template
		$templateOutput = array();
		$atEnd = false;
		for ($j=0; $j<=1; $j++)
		{
			for ($i=0; $i<count($this->_output); $i++)
			{
				if ($this->_output[$i]['atEnd']===($j==0 ? false : true))
				{
					if (array_key_exists($this->_output[$i]['editableRegion'], $templateOutput))
					{
						$templateOutput[$this->_output[$i]['editableRegion']] .= $this->_output[$i]['code'];
					}
					else
					{
						$templateOutput[$this->_output[$i]['editableRegion']] = $this->_output[$i]['code'];
					}
				}
				if ($this->_output[$i]['atEnd']===true) $atEnd = true;
			}
			if (!$atEnd) break;
		}

		if ( org_glizy_ObjectValues::get( 'org.glizy.application', 'pdfMode' ) )
		{
			$template = & org_glizy_ObjectFactory::createObject('org.glizy.template.layoutManager.PDF', $this->getAttribute('templateFileName'));
		}
		else
		{
			switch ($this->getAttribute('templateType'))
			{
				case ('dwt'):
					$template = & org_glizy_ObjectFactory::createObject('org.glizy.template.layoutManager.DWT', $this->getAttribute('templateFileName'));
					break;
				case ('phptal'):
					$template = & org_glizy_ObjectFactory::createObject('org.glizy.template.layoutManager.PHPTAL', $this->getAttribute('templateFileName'));
					break;
				case ('php'):
					$template = & org_glizy_ObjectFactory::createObject('org.glizy.template.layoutManager.PHP', $this->getAttribute('templateFileName'));
					break;
			}
		}

		$output = $template->apply($templateOutput);
		$this->applyOutputFilters('post', $output);
		return $output ;
	}

	function loadContent($id)
	{
		return isset($this->_content[$id]) ? $this->_content[$id]['content_value'] : '';
	}

	public function setOutputCode($keyName, $value) {
		$this->addOutputCode($value, $keyName);
	}

	protected function checkAcl()
	{
		$acl = $this->getAttribute( 'acl' );
		if ($acl) {
			list( $service, $action ) = explode( ',', $acl );
			return $this->_user->acl($service, $action, false);
		}
		return true;
	}
}
