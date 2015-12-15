<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_Acl extends org_glizy_compilers_Compiler
{
	function compile($options)
	{
		$this->initOutput();

		// esegue il parsing del file di configurazione
		$xml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$xml->loadAndParseNS( $this->_fileName );
		$services	= $xml->getElementsByTagName('AclService');

		$this->output .= '$acl = array();';
		foreach ($services as $service)
		{
			$name 		= strtolower($service->getAttribute('name'));
			$default	= $service->hasAttribute('default') ? $service->getAttribute('default') : 'true';
			$this->output .= '$acl[\''.$name.'\'] = array(\'default\' => '.$default.', \'rules\' => array(';

			$rules = $service->getElementsByTagName('AclAction');
			foreach ($rules as $rule)
			{
				$name 		= strtolower($rule->getAttribute('name'));
				$allowGroups= $rule->getAttribute('allowGroups');
				$allowGroups= !empty($allowGroups) ? 'array(\''.str_replace(',', '\', \'', $allowGroups).'\')' : 'array()';
				$allowUsers	= $rule->getAttribute('allowUsers');
				$allowUsers	= !empty($allowUsers)	? 'array(\''.str_replace(',', '\', \'', $allowUsers).'\')' : 'array()';
				$this->output .= '\''.$name.'\' => array(\'allowGroups\' => '.$allowGroups.', \'allowUsers\' => '.$allowUsers.'), ';
			}

			$this->output .= '))'.GLZ_COMPILER_NEWLINE;
		}
		return $this->save();
	}
}