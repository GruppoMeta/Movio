<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_SelectPageType extends org_glizy_components_HtmlFormElement
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('bindTo',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',			false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassLabel',			false, 	__Config::get('glizy.formElement.cssClassLabel'),		COMPONENT_TYPE_STRING);
		// $this->defineAttribute('defaultValue',	false, 	NULL,	COMPONENT_TYPE_STRING);
		// $this->defineAttribute('emptyValue',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('modify',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		// $this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		// $this->defineAttribute('requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		// $this->defineAttribute('size',			false, 	'',		COMPONENT_TYPE_STRING);


		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->_content = $this->getAttribute('value');
		if (is_object($this->_content))
		{
			// legge il contenuto da un dataProvider
			$contentSource = &$this->getAttribute('value');
			$this->_content = $contentSource->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
		else if (is_null($this->_content))
		{
			// richiede il contenuto al padre
			$this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
	}

	function render()
	{
// TODO: controllo acl
		$name = $this->getId();

		if (!$this->_user->acl($this->_application->getPageId(),'new'))
		{
			$output = org_glizy_helpers_Html::hidden( $name , $this->_content, array( 'class' => $this->getAttribute( 'cssClass' ) ) );
		}
		else
		{
			$pageTypes = array();
			if ($dh = @opendir(org_glizy_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE')))
			{
				// scan the pageType folder
				while ($fileName = readdir($dh))
				{
					// check if the item is a folder
					if ($fileName!="." &&
						$fileName!=".." &&
						strpos($fileName, '.xml')!==false)
					{
						if ($fileName=='Common.xml') continue;
						$pageTypes[] = preg_replace('/\.xml/i', '', $fileName);
					}
				}
				closedir($dh);
				glz_loadLocale(org_glizy_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE'));
			}
			else
			{
				// can't open pageTypes folder
				// show the error
				new org_glizy_Exception(array('[%s] %s: %s', $this->getClassName(), GLZ_ERR_NO_PAGETYPE_FOLDER, org_glizy_Paths::get('APPLICATION_TO_ADMIN').'/pageTypes/'));
			}

			$modules = org_glizy_Modules::getModules();
			foreach($modules as $moduleVO) {
				if ($moduleVO->pageType) {
					$pageTypes[] = $moduleVO->pageType;
				}
			}

            $output = '<option value=""></option>';
			$values = array(array($this->getAttribute('emptyValue'), '', 1, array(), 0));
			$modifyMode = $this->getAttribute('modify');
			foreach($pageTypes as $item)
			{
				$moduleVO = null;
				foreach ($modules as $m) {
					if ($m->pageType==$item) {
						$moduleVO = $m;
						break;
					}
				}
				if ($moduleVO) {
					if (!$moduleVO->show) {
						continue;
					}

					if ( $moduleVO->unique && (!$modifyMode || ($modifyMode && $item!=$this->_content))) {
						$ar = &org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
						$result = $ar->find(array('menu_pageType' => $item));
						unset($ar);
						if ($result)
						{
							continue;
						}
					}
				}
				$pageName = __T($item);
				$output .= '<option value="'.$item.'"'.($item==$this->_content ? ' selected':'').'>'.__T($item).'</option>';
			}

			$attributes 				= array();
			$attributes['id'] 			= $this->getId();
			$attributes['name'] 		= $this->getOriginalId();
			$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
			$attributes['class'] 		= $this->getAttribute('required') ? 'required' : '';
			$attributes['class'] 		.= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';

			$output = '<select '.$this->_renderAttributes($attributes).'>'.$output.'</select>';

			$cssClassLabel = $this->getAttribute( 'cssClassLabel' );
			$cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
			if ($this->getAttribute('wrapLabel')) {
				$label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), true, $output, array('class' => $cssClassLabel ), false);
				$output = '';
			} else {
				$label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);
			}
			$this->addOutputCode($this->applyItemTemplate($label, $output));
		}
	}
}
