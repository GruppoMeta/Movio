<?php


class movio_modules_modulesBuilder_views_components_SelectTable extends org_glizy_components_HtmlFormElement
{

	// TODO:
	// controllare che sia scrivibile
	// admin/MW/startup
	// MW/classes/userModules
	// admin/MW/
	//

	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('emptyValue',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('rows',			false, 	1,		COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapLabel',		false, 	false,	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
	}

	function render()
	{
        $dbServiceFactory = org_glizy_ObjectFactory::createObject('movio.modules.modulesBuilder.services.DbServiceFactory');
        $dbService = $dbServiceFactory->createDbService(__Config::get('DB_TYPE'));
        $dbService->connect(__Config::get('DB_HOST'), __Config::get('DB_PORT'), __Config::get('DB_USER'), __Config::get('DB_PSW'), __Config::get('DB_NAME'));
   		$tables = $dbService->getTableNames();


		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['class'] 		= $this->getAttribute('required') ? 'required' : '';
		$attributes['class'] 		.= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';
		$attributes['title'] 		= $this->getAttributeString('title');
		$attributes['onchange'] 		= $this->getAttribute('onChange');
		if ( $this->getAttribute('rows')>1)
		{
			$attributes['size'] 		= $this->getAttribute('rows');
		}

		$output = '<select '.$this->_renderAttributes($attributes).'>';
		foreach($tables as $item)
		{
			$output .= '<option value="'.glz_encodeOutput($item).'">'.glz_encodeOutput($item).'</option>';
		}
		$output .= '</select>';

		$output = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(),  $this->getAttribute('wrapLabel'), $output, array('class' => ($this->getAttribute('required') ? 'required' : '')), false);
		$this->addOutputCode($output);
	}

}
?>