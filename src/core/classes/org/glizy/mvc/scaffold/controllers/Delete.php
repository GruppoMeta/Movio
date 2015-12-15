<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_mvc_scaffold_controllers_Delete
 */
class org_glizy_mvc_scaffold_controllers_Delete extends org_glizy_mvc_scaffold_controllers_AbstractCommand
{
	function execute()
	{
		if ( $this->id > 0 )
		{
			$this->logAndMessage( __T( 'Record cancellato' ) );
			$ar = org_glizy_ObjectFactory::createModel( $this->modelName );
			$ar->delete( $this->id );
			$this->changePage( 'link', array( 'pageId' => $this->pageId ) );
		}
	}
}