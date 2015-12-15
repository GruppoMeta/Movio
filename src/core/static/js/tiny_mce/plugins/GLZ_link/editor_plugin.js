/* SVN FILE: $Id: editor_plugin.js 162 2007-03-30 22:57:05Z ugoletti $ */
/**
 * TinyMCE Image plugin, based on TinyMCE-advlink plugin by Moxiecode Systems AB.
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
 * @subpackage   javascript
 * @author		 Daniele Ugoletti <daniele@ugoletti.com>, Giorgio Braga <giorgio@justattributes.com>
 * @category	 javascript
 * @since        Glizy v 0.01
 * @version      $Rev: 162 $
 * @modifiedby   $LastChangedBy: ugoletti $
 * @lastmodified $Date: 2007-03-31 00:57:05 +0200 (sab, 31 mar 2007) $
 */


(function() {
	tinymce.PluginManager.requireLangPack('GLZ_link');
	
	tinymce.create('tinymce.plugins.GlizyLinkPlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceGlzLink', function() {
				var se = ed.selection;

				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;

				ed.windowManager.open({
					file : url + '/link.htm',
					width : 420 + parseInt(ed.getLang('GLZ_link.delta_width', 0)),
					height : 530 + parseInt(ed.getLang('GLZ_link.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('link', {
				title : 'advlink.link_desc',
				cmd : 'mceGlzLink'
			});

			ed.addShortcut('ctrl+k', 'advlink.link_desc', 'mceGLZ_link');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('link', co && n.nodeName != 'A');
				cm.setActive('link', n.nodeName == 'A' && !n.name);
			});
		},

		getInfo : function() {
			return {
				longname : 'Glizy link (based on TinyMCE-advlink plugin by Moxiecode Systems AB)',
				author : 'Daniele Ugoletti',
				authorurl : 'http://www.glizy.org',
				infourl : 'http://www.glizy.org',
				version : '1.2.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('GLZ_link', tinymce.plugins.GlizyLinkPlugin);
})();