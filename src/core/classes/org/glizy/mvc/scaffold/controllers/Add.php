<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_mvc_scaffold_controllers_Add
 */
class org_glizy_mvc_scaffold_controllers_Add extends org_glizy_mvc_scaffold_controllers_Show
{
	function executeLater()
	{
		if ( $this->submit )
		{
			if ($this->view->validate())
			{
				$isNewRecord = $this->id == 0;
				$ar = org_glizy_ObjectFactory::createModel( $this->modelName );
				$this->id = $ar->save(__Request::getAllAsArray(), $isNewRecord);

				$this->redirect( $isNewRecord );
			}
		}
	}

	protected function redirect( $isNewRecord )
	{
		$this->logAndMessage( __T( 'Informazioni salvate con successo' ) );
		if ( !$this->refreshPage )
		{
			if ( !$isNewRecord )
			{
				$this->goHere();
			}
			else
			{
				$this->changePage( 'linkChangeAction', array( 'pageId' => $this->pageId, 'action' => 'add' ), array( 'id' => $this->id ) );
			}
		}
		else
		{
			$this->changePage( 'link', array( 'pageId' => $this->pageId ) );
		}
	}
}