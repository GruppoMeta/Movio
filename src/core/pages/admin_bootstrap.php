<?php
/* SVN FILE: $Id: admin_bootstrap.php 78 2006-12-19 14:59:02Z ugoletti $ */

/**
 *
 *
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2006 Daniele Ugoletti <daniele@ugoletti.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 *
 * @copyright    Copyright (c) 2005, 2006 Daniele Ugoletti
 * @link         http://www.glizy.org Glizy Project
 * @license      http://www.gnu.org/copyleft/lesser.html GNU LESSER GENERAL PUBLIC LICENSE
 * @package      glizy
 * @subpackage   pages
 * @author		 Daniele Ugoletti <daniele@ugoletti.com>
 * @category	 script
 * @since        Glizy v 0.4.0
 * @version      $Rev: 78 $
 * @modifiedby   $LastChangedBy: ugoletti $
 * @lastmodified $Date: 2006-12-19 15:59:02 +0100 (mar, 19 dic 2006) $
 */

require_once('../core/core.inc.php');

$application = &org_glizy_ObjectFactory::createObject('org.glizy.admin.AdminApplication', 'INSERT HERE THE ADMIN APPLICATION FOLDER', 'INSERT HERE THE CORE FOLDER', 'INSERT HERE THE APPLICATION FOLDER');
$application->run();