<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_rest_core_CommandRest
 */
class org_glizy_rest_core_CommandRest extends GlizyObject
{
	/** @var org_glizy_rest_core_Application $application */
	protected $application = NULL;
	protected $user = NULL;
	public $directOutput = false;

    /**
     * @param org_glizy_rest_core_Application $application
     */
	function __construct( $application=NULL )
	{
		$this->application = $application;
		$this->user = &$this->application->getCurrentUser();
	}

    /**
     * @param null $oldAction
     *
     * @return bool
     */
    function execute($oldAction = null) {
		return true;
	}
}