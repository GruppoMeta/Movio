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
	public function init()
	{
		$this->defineAttribute('addWrapDiv', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssClass', false, __Config::get('glizy.jstab'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassPane', false, __Config::get('glizy.jstab.pane'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassDropdownTab', false, __Config::get('glizy.jstab.dropdown.tab'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassDropdown', false, __Config::get('glizy.jstab.dropdown.menu'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassDropdownLink', false, __Config::get('glizy.jstab.dropdown.link'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassDropdownCaret', false, __Config::get('glizy.jstab.dropdown.caret'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassNavigation', false, __Config::get('glizy.jstab.navigation'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassNavigationLink', false, __Config::get('glizy.jstab.navigation.link'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('iconNext', false, __Config::get('glizy.jstab.navigation.next'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('iconPrev', false, __Config::get('glizy.jstab.navigation.prev'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('showNav', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('enableTabListener', false, __Config::get('glizy.jstab.enableTabListener'), COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('enableScrollOnChange', false, __Config::get('glizy.jstab.enableScrollOnChange'), COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}




	public function render_html_onStart()
	{
		$this->addOutputCode($this->renderTabsNavigation().$this->renderJSCode());
	}

	public function render_html_onEnd()
	{
		$this->addOutputCode('</div>');
	}

	/**
	 * @return string
	 */
	private function renderTabsNavigation()
	{
		$tabId = $this->getId();
		$tabIdContent = $tabId.'_content';
		$dropdownMenu = '';

		for ($i=0; $i<count($this->childComponents); $i++) {
			$visible = $this->childComponents[$i]->getAttribute('visible');
			if (!$visible || $this->childComponents[$i]->_tagname!='glz:JSTab') continue;

			$label = $this->childComponents[$i]->getAttribute('label');
			$routeUrl = $this->childComponents[$i]->getAttribute('routeUrl');
			$id = $this->childComponents[$i]->getId();
			$dropdown = $this->childComponents[$i]->getAttribute('dropdown');
			$cssClassTab = $this->childComponents[$i]->getAttribute('cssClassTab');
			if ($this->childComponents[$i]->getAttribute('disabled')) {
				$cssClassTab .= ' disabled';
			}

			if ($dropdown && !$dropdownMenu) {
				$output .= '<li class="'.$this->getAttribute('cssClassDropdownTab').'">'.
							'<a href="noTranslate:#" data-toggle="dropdown" class="'.$this->getAttribute('cssClassDropdownLink').'">'.
							'<span class="js-label">'.$label.'</span>'.
							'<span class="'.$this->getAttribute('cssClassDropdownCaret').'"></span>'.
							'</a>'.
							'<ul class="'.$this->getAttribute('cssClassDropdown').'">##dropdown##</ul></li>';
			}

			if (!$dropdown) {
				$cssClassTab = $cssClassTab ? ' class="'.trim($cssClassTab).'"' : $cssClassTab;
				if (!$routeUrl) {
					$output .= '<li'.$cssClassTab.'><a href="#'.$id.'" data-target="#'.$id.'" data-toggle="tab">'.$label.'</a></li>';
				} else {
					$output .= '<li'.$cssClassTab.'><a href="'.__Link::makeUrl($routeUrl).'">'.$label.'</a></li>';
				}
			} else {
				$dropdownMenu .= '<li><a href="#'.$id.'" data-target="#'.$id.'" data-toggle="tab">'.$label.'</a></li>';
			}
		}

		if ($dropdownMenu) {
			$output = str_replace('##dropdown##', $dropdownMenu, $output);
		}

		if ($dropdownMenu && $this->getAttribute('showNav')) {
			$output .= '<li class="'.$this->getAttribute('cssClassNavigation').'">'.
						'<a href="#" data-toggle="tab-prev" class="'.$this->getAttribute('cssClassNavigationLink').'"><span class="'.$this->getAttribute('iconPrev').'"></span></a>'.
						'</li>'.
						'<li class="'.$this->getAttribute('cssClassNavigation').'">'.
						'<a href="#" data-toggle="tab-next" class="'.$this->getAttribute('cssClassNavigationLink').'"><span class="'.$this->getAttribute('iconNext').'"></span></a>'.
						'</li>';
		}

		$output = '<ul id="'.$tabId.'" class="'.$this->getAttribute('cssClass').'">'.
					$output.
					'</ul>'.
					'<div id="'.$tabIdContent.'" class="'.$this->getAttribute('cssClassPane').'">';
		return $output;
	}


	/**
	 * @return string
	 */
	private function renderJSCode()
	{
		$tabId = $this->getId();
		$tabIdContent = $tabId.'_content';
		$enableTabListener = $this->getAttribute('enableTabListener') ? 'true' : 'false';
		$enableScrollOnChange = $this->getAttribute('enableScrollOnChange') ? 'true' : 'false';
		$tabCssClass = __Config::get('glizy.jstab.pane');

		$output .= <<<EOD
<script>
jQuery(function(){
	var enableTabListener = {$enableTabListener};
	var enableScrollOnChange = {$enableTabListener};
	var isNested = $('#{$tabIdContent}').parent().closest('div.{$tabCssClass}').length != 0;

	if (Glizy && Glizy.events) {
	 	Glizy.events.on("glizy.message.showError", function() {
			var obj = $('.GFEValidationError').first();
			var panelId = obj.parents('div.tab-pane').attr('id');
			$('#{$tabId} a[data-target="#'+panelId+'"]').tab('show');
		});
	}

	var navInPanel = function(dir) {
		var activePanel = $('#{$tabIdContent} div[class="tab-pane active"]');
		if (activePanel.length) {
			var id = dir > 0 ? activePanel.next().attr('id') : activePanel.prev().attr('id');
			if (id) {
				$('#{$tabId} a[data-target="#'+id+'"]').tab('show');
			}
		}
	}

	var updateNav = function() {
		var activePanel = $('#{$tabIdContent} div[class="tab-pane active"]');
		$('#{$tabId} a[data-toggle="tab-prev"]').toggleClass('disabled', !activePanel.prev().hasClass('tab-pane'));
		$('#{$tabId} a[data-toggle="tab-next"]').toggleClass('disabled', !activePanel.next().hasClass('tab-pane'));

		if (!isNested) {
			location.hash = '#'+activePanel.attr('id');
		}

		if (!isNested && enableScrollOnChange) {
			window.scrollTo(0, $('#{$tabId}').offset().top);
		}

		setTimeout(function(){
			$('#{$tabIdContent} div[class="tab-pane active"]').find('input, select, textarea, radio, checkbox').first().focus();
		}, 100);
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

	var lastKeyDown = null;
	$('#{$tabIdContent} input, #{$tabIdContent} textarea, #{$tabIdContent} select, #{$tabIdContent} radio, #{$tabIdContent} checkbox').keydown(function(e){
		if (!enableTabListener) return;
		if (e.keyCode!=9) return;
		setTimeout(function(){
			var el = $(e.currentTarget)
			var panel = el.parents('div.tab-pane');
			var panelId = panel.attr('id');
			var focusPanelId = $(':focus').parents('div.tab-pane').attr('id');
			console.log(panelId, focusPanelId, $(':focus'));

			if (panelId==focusPanelId) return;
			var elements = panel.find('input, select, textarea, radio, checkbox');
			var pos = elements.index(el);

			if (pos == 0 && e.shiftKey) {
				$('#{$tabId} a[data-toggle="tab-prev"]').trigger('click');
			}
			if (pos > elements.length / 2 && !e.shiftKey) {
				$('#{$tabId} a[data-toggle="tab-next"]').trigger('click');
			}
		}, 100);
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

	var aFirst;
	if (location.hash && !isNested) {
		$('#{$tabId} a').each(function(i, el) {
			if ($(el).data('target')==location.hash) {
				aFirst = $(el);
				return false;
			}
		});
	} else {
		aFirst = $('#{$tabId} a').first();
	}

	if (aFirst.data('toggle')==='dropdown') {
		aFirst.parent().find('ul a').first().tab('show');
	} else {
		aFirst.tab('show');
	}
});
</script>
EOD;
		return $output;
	}
}
