/**
 * TinyMCE Image plugin, based on TinyMCE-advimage plugin by Moxiecode Systems AB.
 *
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 *
 * @copyright    Copyright (c) 2005, 2006 Daniele Ugoletti
 * @link         http://www.glizy.org Glizy Project
 * @license      http://www.gnu.org/copyleft/lesser.html GNU LESSER GENERAL PUBLIC LICENSE
 * @package      glizy
 * @subpackage   javascript
 * @author		 Daniele Ugoletti <daniele.ugoletti@glizy.com>, Giorgio Braga <giorgio@justattributes.com>
 * @category	 javascript
 * @since        Glizy v 0.01
 * @version      $Rev: 81 $
 * @modifiedby   $LastChangedBy: ugoletti $
 * @lastmodified $Date: 2007-01-14 08:59:55 +0100 (dom, 14 gen 2007) $
 */


(function() {
	tinymce.PluginManager.requireLangPack('GLZ_image');

	tinymce.create('tinymce.plugins.GlizyImagePlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceGlzImage', function() {
				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class', '').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : url + '/image.htm',
					width : $(window).width() - 80 + parseInt(ed.getLang('GLZ_image.delta_width', 0)),
					height : 530 + parseInt(ed.getLang('GLZ_image.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('image', {
				title : 'advimage.image_desc',
				cmd : 'mceGlzImage'
			});
		},

		getInfo : function() {
			return {
				longname : 'Glizy Image (based on TinyMCE-advimage plugin by Moxiecode Systems AB)',
				author : 'Daniele Ugoletti',
				authorurl : 'http://www.glizy.org',
				infourl : 'http://www.glizy.org',
				version : '1.2.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('GLZ_image', tinymce.plugins.GlizyImagePlugin);
})();
