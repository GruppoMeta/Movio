<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_JSTabGroup extends org_glizy_components_ComponentContainer
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('addWrapDiv', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssClass', false, 'nav nav-tabs', COMPONENT_TYPE_STRING);
		$this->defineAttribute('paneCssClass', false, 'tab-content', COMPONENT_TYPE_STRING);
		$this->defineAttribute('showNav', false, false, COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}


	function render_html_onStart()
	{
		$tabId = $this->getId();
		$dropdownMenu = '';
		$output .= '<ul id="'.$tabId.'" class="'.$this->getAttribute('cssClass').'">';

		for ($i=0; $i<count($this->childComponents); $i++)
		{
			$visible = $this->childComponents[$i]->getAttribute('visible');
			if (!$visible) continue;

			$label = $this->childComponents[$i]->getAttribute('label');
			$routeUrl = $this->childComponents[$i]->getAttribute('routeUrl');
			$cssClassTab = $this->childComponents[$i]->getAttribute('cssClassTab');
			if ($this->childComponents[$i]->getAttribute('disabled')) {
				$cssClassTab .= ' disabled';
			}
			$id = $this->childComponents[$i]->getId();
			$dropdown = $this->childComponents[$i]->getAttribute('dropdown');
			if (!$dropdown) {
				$cssClassTab = $cssClassTab ? ' class="'.trim($cssClassTab).'"' : $cssClassTab;
				if (!$routeUrl) {
					$output .= '<li'.$cssClassTab.'><a href="#'.$id.'" data-target="#'.$id.'" data-toggle="tab">'.$label.'</a></li>';
				} else {
					$output .= '<li'.$cssClassTab.'><a href="'.__Link::makeUrl($routeUrl).'">'.$label.'</a></li>';
				}
			} else if ($dropdown && !$dropdownMenu) {
				$output .= '<li class="dropdown"><a href="noTranslate:#" data-toggle="dropdown" class="tab-dropdown dropdown-toggle"><span class="js-label">'.$label.'</span><span class="caret"></span></a><ul class="dropdown-menu">##dropdown##</ul></li>';
				$dropdownMenu .= '<li><a href="#'.$id.'" data-target="#'.$id.'" data-toggle="tab">'.$label.'</a></li>';
			} else if ($dropdown && $dropdownMenu) {
				$dropdownMenu .= '<li><a href="#'.$id.'" data-target="#'.$id.'" data-toggle="tab">'.$label.'</a></li>';
			}
		}
		if ($dropdownMenu) {
			$output = str_replace('##dropdown##', $dropdownMenu, $output);

			if ($this->getAttribute('showNav')) {
				$output .= '<li class="tab-navigation"><a href="#" data-toggle="tab-prev" class="btn"><span class="fa fa-angle-double-left"></span></a></li>';
				$output .= '<li class="tab-navigation"><a href="#" data-toggle="tab-next" class="btn"><span class="fa fa-angle-double-right"></span></a></li>';
			}
		}

		$output  .= '</ul>'.
					'<div id="'.$tabId.'_content" class="'.$this->getAttribute('paneCssClass').'">';

		$output .= <<<EOD
<script>
jQuery(function(){
	var navInPanel = function(dir) {
		var activePanel = $('#{$tabId}_content div[class="tab-pane active"]');
		if (activePanel.length) {
			var id;
			if (dir>0) {
				id = activePanel.next().attr('id');
			} else {
				id = activePanel.prev().attr('id');
			}
			if (id) {
				$('#{$tabId} a[data-target="#'+id+'"]').tab('show');
			}
		}
	}

	var updateNav = function() {
		var activePanel = $('#{$tabId}_content div[class="tab-pane active"]');
		$('#{$tabId} a[data-toggle="tab-prev"]').toggleClass('disabled', !activePanel.prev().hasClass('tab-pane'));
		$('#{$tabId} a[data-toggle="tab-next"]').toggleClass('disabled', !activePanel.next().hasClass('tab-pane'));
	}

	$('#{$tabId} a[data-toggle="tab"]').click(function (e) {
    	e.preventDefault();
		if ($(this).parent().hasClass('disabled') || $(this).parent().hasClass('fake-active')) {
			return false;
		}
    	$(this).tab('show');
    }).on('shown.bs.tab', function (e) {
		var target = $(e.target);
		var dropdown = target.closest('ul.dropdown-menu').prev();
		if (dropdown.length) {
			dropdown.find('span.js-label').html(target.html());
		}
		updateNav();
	});


	$('#{$tabId} a[data-toggle="tab-prev"]').click(function (e) {
		e.preventDefault();
		$(this).blur();
		navInPanel(-1);
	});
	$('#{$tabId} a[data-toggle="tab-next"]').click(function (e) {
		e.preventDefault();
		$(this).blur();
		navInPanel(1);
	});

	var aFirst = $('#{$tabId} a').first();
	if (aFirst.data('toggle')==='dropdown') {
		aFirst.parent().find('ul a').first().tab('show');
	} else {
		aFirst.tab('show');
	}
});
</script>
EOD;
		$this->addOutputCode($output);
	}

	function render_html_onEnd()
	{
		$this->addOutputCode('</div>');
	}
}
