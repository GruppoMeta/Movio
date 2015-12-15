<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_filters_ForceNoTagText extends org_glizy_filters_OutputFilter
{
	function apply(&$value, &$component)
	{
		$xml = new XML();
		$xml->parseXML($value);
		$rootNode = $xml->firstChild;
		$result = '';
		$this->__toString($rootNode, $result);
		$value = preg_replace('/^<root>/i', '', $result);
	}

	function __toString($node, &$result)
	{
		switch ($node->nodeType)
		{
			case XML_COMMENT_NODE:
				$result .= '<!-- '.$node->nodeValue.' -->';
				break;
			case XML_TEXT_NODE:
				$result .= '<p>'.$node->nodeValue.'</p>';
				break;
			case XML_CDATASection:
				break;
			default:
				$result .= '<'.$node->nodeName;

				if ($node->hasAttributes())
				{
					foreach ($node->attributes as $key => $val)
					{
						$result .= ' '.$key.'="'.$val.'"';
					}
				}

				if ($node->hasChildNodes())
				{
					$result .= '>';
					foreach ($node->childNodes as $child)
					{
						toString($child, $result);
					}
					$result .= '</'.$node->nodeName.'>';
				} else {
					$result .= '/>';
				}
		}
	}
}