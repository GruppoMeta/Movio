<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


if (!defined('GLZ_PAGINATE_PAGE')) 		define('GLZ_PAGINATE_PAGE', 'pageNum');

/**
 * Class org_glizy_components_PaginateResult
 */
class org_glizy_components_PaginateResult extends org_glizy_components_Component
{
	var $pageUrl;
	var $pageLength;
    /** @var org_glizy_SessionEx $_sessionEx */
	var $_sessionEx		= NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('pageLength', 			false, 	15,	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('cssClass', 				false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('showTotal',				false,	false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('showGoTo',				false,	false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('showDisabledLinks',		false,	false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('groupLength',			false,	10, COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('remember',				false,	true, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:showControl',     false,    false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('arrowNext',		false,	'&raquo;', COMPONENT_TYPE_STRING);
		$this->defineAttribute('arrowPrev',		false,	'&laquo;', COMPONENT_TYPE_STRING);
		$this->defineAttribute('arrowGroupNext',		false,	'&raquo;|', COMPONENT_TYPE_STRING);
		$this->defineAttribute('arrowGroupPrev',		false,	'|&laquo;', COMPONENT_TYPE_STRING);
		// call the superclass for validate the attributes
		parent::init();

		$this->_sessionEx	= org_glizy_ObjectFactory::createObject('org.glizy.SessionEx', $this->getId());
	}

    /**
     * @return array|null
     */
	function getLimits()
	{
		if (isset($this->_content['start']) && isset($this->_content['stop']))
		{
			return array('start' => $this->_content['start'], 'stop' => $this->_content['stop'], 'pageLength' => $this->_content['pageLength']);
		}
		else return NULL;
	}

	function resetContent()
	{
		$this->_content = array();
		$this->_content['id'] 			= $this->getId();
		$this->_content['__cssClass__']	= $this->getAttribute('cssClass');
		$this->_content['pagesLinks'] 	= array();
		$this->_content['pageLength'] 	= 0;
		$this->_content['start'] 		= 0;
		$this->_content['stop'] 		= 0;
		$this->_content['totalPages'] 	= 0;
		$this->_content['goTo'] 		= NULL;
		$this->_content['totalRecords']	= NULL;
	}

    /**
     * @param null $recordsCount
     * @param null $pageLength
     */
	function setRecordsCount($recordsCount=NULL, $pageLength=NULL)
	{
		if (is_null($pageLength)) {
		    if ($this->getAttribute('adm:showControl')) {
	            $content = (int)$this->_parent->loadContent($this->getId());
	            if ($content) {
	                $this->setAttribute('pageLength', $content);
	            }
	        }

			$pageLength = $this->getAttribute("pageLength");
		}

		$this->pageUrl = GLZ_PAGINATE_PAGE;
		if ( $this->getAttribute('remember')) {
			$currentPage = $this->_sessionEx->get($this->pageUrl, '1', true, true);
		}
		else
		{
			$currentPage = __Request::get($this->getId().'_'.$this->pageUrl, 1);
		}
		if (is_null($recordsCount))
		{
			$recordsCount = $currentPage * $pageLength;
		}
		if (($currentPage-1)*$pageLength>$recordsCount) $currentPage = 1;
		$this->_sessionEx->set($this->pageUrl, $currentPage);

		$this->resetContent();
		$this->_content['pageLength'] 	= $pageLength;
		$this->_content['currentPage'] 	= $currentPage;
		$this->_content['recordsCount'] 	= $recordsCount;

		if ($currentPage=="ALL" || $recordsCount==0)
		{
			$this->_content['start']		= 0;
			$this->_content['stop']			= $recordsCount - 1;
			$this->_content['totalPages'] 	= 0;
		}
		else
		{
			$this->_content['start']		= ($currentPage-1) * $pageLength;
			$this->_content['stop']			= $this->_content['start'] + $pageLength - 1;
			$this->_content['totalPages']	= ceil($recordsCount / $pageLength);
		}
	}

    /**
     * @param null $outputMode
     * @param bool $skipChilds
     */
	function render($outputMode=NULL, $skipChilds=false)
	{
		if ($this->_content['totalPages']>1)
		{
			$currentPage = $this->_content['currentPage'];
			$groupLength = $this->getAttribute("groupLength");
			$start = 1;
			$stop = $this->_content['totalPages'];
			$lastLink = array();
			$showDisabledLinks = $this->getAttribute('showDisabledLinks');
			if ($this->_content['totalPages']>$groupLength )
			{
				$start = max(1, floor(($currentPage)/$groupLength )*$groupLength );
				$stop = min($this->_content['totalPages'], $start + ($groupLength-1));

				if ($currentPage!=1 || $showDisabledLinks)
				{
					$label =  $this->getAttribute('arrowGroupPrev');
					if ($label) {
						$tempArray = array();
						$tempArray['__cssClass__'] 	= 'noNumber';
						$tempArray['__url__']		= $currentPage!=1 ? org_glizy_helpers_Link::addParams(array($this->getId().'_'.$this->pageUrl => 1)) : '';
						$tempArray['value']	   		= $label;
						$this->_content['pagesLinks'][]= $tempArray;
					}

					$label =  $this->getAttribute('arrowPrev');
					if ($label) {
						$tempArray = array();
						$tempArray['__cssClass__'] = 'noNumber';
						$tempArray['__url__'] = $currentPage != 1 ? org_glizy_helpers_Link::addParams(array($this->getId() . '_' . $this->pageUrl => max(1, $currentPage - 1))) : '';
						$tempArray['value'] = $label;
						$this->_content['pagesLinks'][] = $tempArray;
					}
				}

				if ($currentPage!=$this->_content['totalPages'] || $showDisabledLinks )
				{
					$label =  $this->getAttribute('arrowNext');
					if ($label) {
						$tempArray = array();
						$tempArray['__cssClass__'] = 'noNumber';
						$tempArray['__url__'] = $currentPage != $this->_content['totalPages'] ? org_glizy_helpers_Link::addParams(array($this->getId() . '_' . $this->pageUrl => min($this->_content['totalPages'], $currentPage + 1))) : '';
						$tempArray['value'] = $label;
						$lastLink[] = $tempArray;
					}
					$label =  $this->getAttribute('arrowGroupNext');
					if ($label) {
						$tempArray = array();
						$tempArray['__cssClass__'] = 'noNumber';
						$tempArray['__url__'] = $currentPage != $this->_content['totalPages'] ? org_glizy_helpers_Link::addParams(array($this->getId() . '_' . $this->pageUrl => $this->_content['totalPages'])) : '';
						$tempArray['value'] = $label;
						$lastLink[] = $tempArray;
					}
				}
			}
			for ($i = $start; $i<=$stop; $i++)
			{
				$tempArray = array();
				$tempArray['__cssClass__'] 	= $currentPage==$i ? 'current' : 'number';
				$tempArray['__cssClass__'] 	.= $i == $start ? ($tempArray['__cssClass__']!='' ? ' ' : '').'first' : '';
				$tempArray['__cssClass__'] 	.= $i == $stop ? ($tempArray['__cssClass__']!='' ? ' ' : '').'last' : '';
				$tempArray['__url__']	   	= '';
				$tempArray['value']	   		= $i;

				if ($currentPage!=$i) $tempArray['__url__']	= org_glizy_helpers_Link::addParams(array($this->getId().'_'.$this->pageUrl => $i));
				$this->_content['pagesLinks'][]= $tempArray;
			}
			if (count($lastLink)) $this->_content['pagesLinks'] = array_merge($this->_content['pagesLinks'], $lastLink);
		}
		$this->_content['goTo'] = '';
		if ($this->getAttribute('showGoTo'))
		{
			$form = '<form action="'.org_glizy_helpers_Link::addParams(array()).'" method="post">';
			$form .= '<input class="page" type="text" onfocus="this.value=\'\';" value="#" name="'.$this->getId().'_'.$this->pageUrl.'"/>';
			$form .= '<input class="go" type="image" src="'.org_glizy_Assets::get('ICON_GO').'"/>';
			$form .= '</form>';
			$this->_content['goTo'] = $form;
		}
		if ($this->getAttribute('showTotal'))
		{
			$this->_content['totalRecords'] = $this->_content['recordsCount'];
		}

		parent::render( $outputMode, $skipChilds );
	}

    /**
     * @param      $records
     * @param null $pageLength
     *
     * @return array
     */
	function splitResult(&$records, $pageLength=NULL)
	{
		if (is_null($pageLength)) $pageLength = $this->getAttribute("pageLength");
		$this->setRecordsCount(count($records), $pageLength);
		$currentPage = $this->_sessionEx->get($this->pageUrl, '1', true, true);
		if ($currentPage=="ALL" || !count($records))
		{
			return $records;
		}
		else
		{
			return array_slice($records, $this->_content['start'], $pageLength);
		}
	}

    /**
     * @param $records
     *
     * @return mixed
     */
	function splitTextResult($records)
	{
		$records = $this->htmlParsingSplitter($records,'hr');
		$this->pageUrl = GLZ_PAGINATE_PAGE; // TODO sostituire con un attributo
		$currentPage = $this->_sessionEx->get($this->pageUrl, '1', true, true); //org_glizy_Request::get($this->pageUrl, '1');

		if (($currentPage-1)>=count($records)) $currentPage = 1;
		$this->_sessionEx->set($this->pageUrl, $currentPage);

		$this->resetContent();
		$this->_content['pagesLinks'] 		= array();
		$this->_content['pageLength'] 		= 1;
		$this->_content['currentPage'] 		= $currentPage;

		$result = array();
		if ($currentPage=="ALL" || !count($records))
		{
			$this->_content['start']		= 0;
			$this->_content['stop']			= count($records) - 1;
			$this->_content['totalPages'] 	= 0;
			$result							= $records;
		}
		else
		{
			$this->_content['start']		= $currentPage-1;
			$this->_content['stop']			= $this->_content['start'];
			$this->_content['totalPages']	= count($records);
			$result							= array_slice($records, $this->_content['start'], 1);
		}

		if ($this->_content['totalPages']>1)
		{
			for ($i = 1;$i<=$this->_content['totalPages'];$i++)
			{
				$tempArray = array();
				$tempArray['__cssClass__'] 	= '';
				$tempArray['__url__']	   	= '';
				$tempArray['value']	   		= $i;

				if ($i==$this->_content['totalPages']) $tempArray['__cssClass__'] = "last";
				if ($currentPage!=$i) $tempArray['__url__']	= org_glizy_helpers_Link::addParams(array($this->getId().'_'.$this->pageUrl => $i));
				$this->_content['pagesLinks'][]= $tempArray;
			}

		}

		if ($this->getAttribute('showTotal'))
		{
			$this->_content['totalRecords'] = count($records);
		}
		return	$result[0];
	}

    /**
     * @param        $source
     * @param string $delimiter
     *
     * @return array
     */
	function htmlParsingSplitter($source, $delimiter='hr')
	{
		if (strpos($source, '<'.$delimiter)===false)
		{
			return array($source);
		}
		$stack = array();
		$pages = array();
		$output='';
		$c='';
		$i=0;
		$skip=false;
		$ordA=ord('A');
		$ordZ=ord('Z');
		$orda=ord('a');
		$ordz=ord('z');
		$ord0=ord('0');
		$ord9=ord('9');
		while (strlen($source))
		{
			while (strlen($source) && ($c=substr($source,0,1))!='<')
			{
				$output .= $c;
				$source = substr($source,1);
			}
			if (!strlen($source))
				break;
			// Apertura di un tag
			if (substr($source,1,1)!='/')
			{
				$tag='';
				$source = substr($source,1);
				do {
					$tag.=$c=substr($source,0,1);
					$source = substr($source,1);
					$ordc=ord($c);
				} while (strlen($source) &&
					(
						($ordc>=$ordA && $ordc<=$ordZ)
					||	($ordc>=$orda && $ordc<=$ordz)
					||	($ordc>=$ord0 && $ordc<=$ord9)
					)
				);
				if (!strlen($source))
					break;
				$source = substr($tag,-1).$source;
				$tag = substr($tag,0,-1);
				// Tag normale
				if (strtolower($tag)!=$delimiter)
				{
					// Skip � true se il tag � un tag autochiuso tipo <ciao/>
					$i=0;
					$skip=false;
					while (!$skip && $i<strlen($source) && substr($source,$i,1)!='>')
						$skip = substr($source,$i++,2)=='/>';
					if ($i<strlen($source) && !$skip)
						array_push($stack,$tag);
					$output.='<'.$tag;
				}
				else
				// Delimitatore
				{
					$stack2 = $stack;
					while (count($stack2))
						$output .= '</'.array_pop($stack2).'>';
					array_push($pages, $output);
					$output='';
					$stack2 = $stack;
					while (count($stack2))
						$output ='<'.array_shift($stack2).'>';
					while (strlen($source) && substr($source,0,1)!='>')
						$source = substr($source,1);
					if (strlen($source) && substr($source,0,1)=='>')
						$source = substr($source,1);
				}
			}
			// Chiusura di un tag
			else
			{
				$tag='';
				$source = substr($source,2);
				do {
					$tag.=$c=substr($source,0,1);
					$source = substr($source,1);
					$ordc=ord($c);
				} while (strlen($source) &&
					(
						($ordc>=$ordA && $ordc<=$ordZ)
					||	($ordc>=$orda && $ordc<=$ordz)
					||	($ordc>=$ord0 && $ordc<=$ord9)
					)
				);
				if (!strlen($source))
					break;
				$source = substr($tag,-1).$source;
				$tag = substr($tag,0,-1);
				if (($lasttag=array_pop($stack))!=$tag)
					array_push($stack,$lasttag);
				$output.='</'.$tag;
			}
		}
		while (count($stack))
			$output .= '</'.array_pop($stack).'>';
		if (strlen($output))
			array_push($pages,$output);
		return $pages;
	}

	public static function translateForMode_edit($node) {
        if ($node->hasAttribute('adm:showControl') && $node->getAttribute('adm:showControl') == 'true') {
            $attributes = array();
            $attributes['id'] = $node->getAttribute('id');
            $attributes['label'] = $node->getAttribute('label');
            $attributes['size'] = 10;
            return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
        }
    }
}

/**
 * Class org_glizy_components_PaginateResult_render
 */
class org_glizy_components_PaginateResult_render extends org_glizy_components_render_Render
{

    /**
     * @return string
     */
	function getDefaultSkin()
	{
		$skin = <<<EOD
<div tal:attributes="id PaginateResult/id;class PaginateResult/__cssClass__" tal:condition="php: count(PaginateResult['pagesLinks']) || !is_null(PaginateResult['totalRecords'])">
	<span tal:condition="php: !is_null(PaginateResult['totalRecords'])" >
		<span tal:omit-tag="" tal:content="php: __T('GLZ_TOTAL_RECORDS')" /> <span tal:omit-tag="" tal:content="structure PaginateResult/totalRecords" />
	</span>
	<ul tal:condition="php: count(PaginateResult['pagesLinks'])">
		<li tal:repeat="item PaginateResult/pagesLinks" tal:attributes="class item/__cssClass__">
			<a href="" tal:condition="php: item['__url__'] != ''" tal:attributes="href structure item/__url__" tal:content="structure item/value">1</a>
			<span tal:condition="php: item['__url__'] == ''" tal:content="structure item/value">1</span>
		</li>
		<span tal:condition="PaginateResult/goTo" tal:omit-tag="">
			<li tal:content="structure PaginateResult/goTo" />
		</span>
	</ul>
</div>
EOD;
		return $skin;
	}
}