<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_mvc_scaffold_controllers_Show
 */
class org_glizy_mvc_scaffold_controllers_Show extends org_glizy_mvc_scaffold_controllers_AbstractCommand
{
	protected $ar;

	function execute()
	{
		if ( !$this->submit )
		{
			if ( is_numeric( $this->id ) )
			{
				if ( $this->id > 0 )
				{
					$this->ar = org_glizy_ObjectFactory::createModel( $this->modelName );
					if ($this->ar->load( $this->id )) {
						__Request::setFromArray( $this->ar->getValuesAsArray() );
					}
				}
			}
			else
			{
				$this->changePage( 'link', array( 'pageId' => $this->pageId ) );
			}
		}
	}
}