<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_layoutManager_PHP extends org_glizy_template_layoutManager_LayoutManager
{

	function apply(&$regionContent)
	{
		$this->checkRequiredValues( $regionContent );
		foreach ($regionContent as $k => $v)
		{
			if (!isset($$k)) $$k = $v;
			else $$k .= $v;
		}

		$compiler 			= org_glizy_ObjectFactory::createObject('org.glizy.compilers.LayoutManagerPHP');
		$compiledFileName 	= $compiler->verify( $this->fileName );

		if ( $compiledFileName  === false )
		{
			$templateSource = @implode('', file($this->fileName));
			$templateSource = $this->fixUrl( $templateSource );
			$compiledFileName = $compiler->compile( $templateSource );
		}

		ob_start();
		include($compiledFileName);
		$templateSource = ob_get_contents();
		ob_end_clean();

		if (isset($regionContent['__body__']))
		{
			$templateSource = $this->modifyBodyTag($regionContent['__body__'], $templateSource);
		}

		$templateSource = $this->fixLanguages( $templateSource );
		return $templateSource;
	}
}