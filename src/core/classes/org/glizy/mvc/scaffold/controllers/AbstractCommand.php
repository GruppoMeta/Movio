<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_mvc_scaffold_controllers_AbstractCommand
 */
class org_glizy_mvc_scaffold_controllers_AbstractCommand extends org_glizy_mvc_core_Command
{
	protected $modelName = '';
	protected $pageId = '';
	protected $submit = false;
	protected $refreshPage = false;
	protected $show = false;
	protected $id;

	function __construct( $controller=NULL, $application=NULL )
	{
		parent::__construct( $controller, $application );

		$this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit' || strtolower( __Request::get( 'submit', '' ) ) == 'submitclose';
		$this->show = strtolower( __Request::get( 'action', '' ) ) == 'show';
		$this->refreshPage = strtolower( __Request::get( 'action', '' ) ) == 'close' || strtolower( __Request::get( 'submit', '' ) ) == 'submitclose';
		$this->id = intval( __Request::get( 'id', '' ) );
		$this->pageId = $this->application->getPageId();
	}
}