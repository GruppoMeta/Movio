<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_layoutManager_DWT extends org_glizy_template_layoutManager_LayoutManager
{
	var $pathPrefix = 'Templates/';

	function apply(&$regionContent)
	{
		$this->checkRequiredValues( $regionContent );
		$templateSource = @implode('', file($this->fileName));
		$templateSource = $this->fixUrl( $templateSource );

		foreach ($regionContent as $region => $content)
		{
			if ($content == '')
			{
				$templateSource =   preg_replace("/(<!--[\s]+TemplateBeginIf[\s]+cond\=\"".$region."[^\>]+-->)[\s]*(\s.*)+?[\s]*(<!--[\s]+TemplateEndIf[\s]+-->)/i", "", $templateSource);
			}

			preg_match("/(<!--\s*TemplateBeginEditable\s*name=\"".$region."\"[^>]*?>)(.*?)(<!--[^>]*?>)/si", $templateSource, $matches, PREG_OFFSET_CAPTURE);
			if (count($matches))
			{
				$templateSource = preg_replace("/(<!--\s*TemplateBeginEditable\s*name=\"".$region."\"[^>]*?>)(.*?)(<!--[^>]*?>)/si", "$1 ".$content." $3",$templateSource);
			}
			else
			{
				$templateSource = preg_replace("/(<!--\s*InstanceBeginEditable\s*name=\"".$region."\"[^>]*?>)(.*?)(<!--[^>]*?>)/si", "$1 ".$content." $3",$templateSource);
			}
		}

		$templateSource = preg_replace("/(^|[^(\/\/\s)])<!\[CDATA\[/mi", "$1",$templateSource);
		$templateSource = preg_replace("/(^|[^(\/\/\s)])\]\]>/mi", "$1",$templateSource);

		if (isset($regionContent['__body__']))
		{
			$templateSource = $this->modifyBodyTag($regionContent['__body__'], $templateSource);
		}
		$templateSource = $this->fixLanguages( $templateSource );
		return $templateSource;
	}
}