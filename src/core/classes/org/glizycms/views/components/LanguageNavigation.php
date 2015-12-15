<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_LanguageNavigation extends org_glizy_components_Component
{

	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',	false, 	'languages',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('separator',	false, 	'none',			COMPONENT_TYPE_STRING); // none, start, end

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html()
	{
		if (!__Config::get('MULTILANGUAGE_ENABLED')) {
			return false;
		}

		$iterator = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language',
				'all', array('order' => 'language_order'));

		if ($iterator->count() > 1)
		{
			$output = '<ul class="'.$this->getAttribute('cssClass').'" id="'.$this->getId().'">';
			if ($this->getAttribute('separator')=='start')
			{
				$output .= '<li class="separator">|</li>';
			}

			foreach($iterator as $ar)
			{
				$url = __Link::addParams(array('language' => $ar->language_code));
				if ($ar->language_id==$this->_application->getLanguageId())
				{
					$output .= '<li class="'.$ar->language_code.'">'.org_glizy_helpers_Link::makeSimpleLink( glz_encodeOutput( $ar->language_name ), $url, '', 'active').'</li>';
				}
				else
				{
					$output .= '<li class="'.$ar->language_code.'">'.org_glizy_helpers_Link::makeSimpleLink( glz_encodeOutput( $ar->language_name ), $url).'</li>';
				}
			}

			if ($this->getAttribute('separator')=='end')
			{
				$output .= '<li>|</li>';
			}
			$output .= '</ul>';
			$this->addOutputCode($output);
		}
	}
}