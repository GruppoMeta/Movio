<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_layoutManager_PHPTAL extends org_glizy_template_layoutManager_LayoutManager
{

	function apply(&$regionContent)
	{
		$this->checkRequiredValues( $regionContent );
		$templateSource = @implode('', file($this->fileName));
		$templateSource = $this->fixUrl( $templateSource );
		$compiler 			= org_glizy_ObjectFactory::createObject('org.glizy.compilers.Skin');
		$compiledFileName 	= $compiler->verify($this->fileName, array('defaultHtml' => $templateSource));

		$pathInfo = pathinfo($compiledFileName);
		$templClass = new PHPTAL($pathInfo['basename'], $pathInfo['dirname'], org_glizy_Paths::getRealPath('CACHE_CODE'));
		foreach ($regionContent as $region => $content)
		{
			$templClass->set($region,  $content);
		}
		$res = $templClass->execute();
		if (PEAR::isError($res))
		{
		   $templateSource = $res->toString()."\n";
		}
		else
		{
		   $templateSource = $res;
		}

		if (isset($regionContent['__body__']))
		{
			$templateSource = $this->modifyBodyTag($regionContent['__body__'], $templateSource);
		}
		$templateSource = $this->fixLanguages( $templateSource );
		return $templateSource;
	}
}