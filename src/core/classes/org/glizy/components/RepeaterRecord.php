<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_RepeaterRecord extends org_glizy_components_ComponentContainer
{
	var $recordId = null;
	var $prefix = null;
	var $targetId = null;


	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('id', 		false, NULL, 	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('target', 	false, NULL, 	COMPONENT_TYPE_OBJECT);

		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$this->recordId = org_glizy_Request::get('id', null);

		if (!is_null($this->recordId))
		{
			$target = &$this->getAttribute('target');

			if (is_object($target))
			{
				$targetId = $target->getId();
				$prefix = $targetId.($this->recordId > 1 ? '@'.($this->recordId-1)  : '');

				for ($i=0; $i<count($target->childComponents);$i++)
				{
					$target->childComponents[$i]->setAttribute('id', str_replace($targetId, $prefix, $target->childComponents[$i]->getId()));
					$this->addChild($target->childComponents[$i]);

					$target->childComponents[$i]->_parent = &$this;
				}
				$target->childComponents = array();
				$this->processChilds();
			}
			else
			{
				// TODO
				// visualizzare errore
			}
		}

	}

	function loadContent($id)
	{
		if (!is_null($this->prefix))
		{
			$id = str_replace($this->targetId, $this->prefix, $id);

		}

		return $this->_parent->loadContent($id);
	}

	function getContent()
	{
		$item 	= array();
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$r = $this->childComponents[$i]->getContent();
			if ($this->childComponents[$i]->_tagname=='glz:Groupbox' || get_parent_class($this->childComponents[$i])=='org_glizy_components_emptycomponent')
			{
				// TODO
				// da risolvere in modo differente
				// ci sono TAG che sono solo container
				// quindi deveno passare il contenuto senza essere inseriti nel result
				// penso che il modo migliore
				// sia fare un componente dal quale derivano e poi verificare la subclass
				// oppure aggiungere una nuova proprietà a componentContainer
				// in fin dei conti se un componentContainer non accetta l'outputnon dovrebbe avere un contenuto proprio
				// ma questa è una cosa da verificare bene
				$item = array_merge($item, $r);
			}
			else
			{
				$item[$this->childComponents[$i]->getOriginalId()] = $r;
			}
		}


		return $item;
	}
}