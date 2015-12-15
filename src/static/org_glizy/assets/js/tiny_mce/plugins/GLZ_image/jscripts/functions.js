/**
 * TinyMCE Image plugin, based on TinyMCE-advimage plugin by Moxiecode Systems AB.
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
 * @version      $Rev: 328 $
 * @modifiedby   $LastChangedBy: ugoletti $
 * @lastmodified $Date: 2011-01-24 17:46:02 +0100 (lun, 24 gen 2011) $
 */
 

function convertURL(url, node, on_save) {
	return url;
}
function trimSize(size) {
	return size.replace(/([0-9\.]+)px|(%|in|cm|mm|em|ex|pt|pc)/, '$1$2');
}

function init() {
	var f = document.forms[0], formObj = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, elm = ed.selection.getNode();
    document.getElementById("picker").src = parent.Glizy.tinyMCE_options.image_picker;

	tinyMCEPopup.resizeToInnerSize();
	if (elm.nodeName == 'IMG') {
		var src = dom.getAttrib(elm, 'src');
		src = convertURL(src, elm, true);
		// Setup form data
		var style = dom.getAttrib(elm, 'style');
        var search = src.split("?")[1];
        var els = search.split("&");
        formObj.imgid.value = els[0].split("=")[1];
        var tn = formObj.src.value = src;
		formObj.alt.value    = dom.getAttrib(elm, 'alt');
		formObj.title.value  = dom.getAttrib(elm, 'title');
		var border = dom.getStyle(elm, 'border' );
		if (border!='')
		{
			formObj.border.value = trimSize(border.split(' ')[0]);
		}
		else
		{
			formObj.border.value = '';
		}
		
		var margin = getStyle(elm, 'margin');
		if (margin!='')
		{
			margin = margin.split(' ');
			formObj.vspace.value = trimSize(margin[0]);
			formObj.hspace.value = trimSize(margin[margin.length > 1 ? 1 : 0]);
		}
		else
		{
			formObj.vspace.value = '';
			formObj.hspace.value = '';
		}
		
		formObj.cssclass.value = dom.getAttrib(elm, 'class');
		var w = formObj.orw.value = formObj.width.value  = trimSize(dom.getStyle(elm, 'width'));
		var h = formObj.orh.value = formObj.height.value = trimSize(dom.getStyle(elm, 'height'));
		formObj.style.value  = style; ///dom.serializeStyle(style);
        with (f.thumbnail) {
            if (w>h) {
                width = 100;
                height = h/w*100;
            }
            else {
                height = 100;
                width = w/h*100;
            }
            src = tn.indexOf('http://')>-1 ? tn : parent.Glizy.tinyMCE_options.glizy_admin_path+tn;
        }

		selectByValue(f, 'align', dom.getStyle(elm, 'float'));

		updateStyle();
		changeAppearance();
	}	

	//     // Check action
	// if (elm != null && elm.nodeName == "IMG")
	// 	action = "update";
	// 
	// formObj.insert.value = tinyMCE.getLang('lang_' + action, 'Insert', true); 
	// 
	// if (action == "update") {
	// 	
	// }
}

function update(imgid,url,t,tn,w,h) {
	var formObj = document.forms[0];
    formObj.imgid.value = imgid;
    with (formObj) {
        title.value = t;
        alt.value = "";
        width.value = orw.value = w;
        height.value = orh.value = h;
        with (thumbnail) {
            if (w/h > 1) {
                width = 100;
                height = h/w*100;
            }
            else {
                height = 100;
                width = w/h*100;
            }
            src = tn;
        }
    }
    updateStyle();
}

function setAttrib(elm, attrib, value) {
	var ed = tinyMCEPopup.editor, dom = ed.dom;
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value != "") {
		dom.setAttrib(elm, attrib, value);
	} else
		elm.removeAttribute(attrib);
}

function makeAttrib(attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value == "")
		return "";
	return ' ' + attrib + '="' + value + '"';
}

function insertAction() {
	var ed = tinyMCEPopup.editor, dom = ed.dom;
	var formObj = document.forms[0];
	tinyMCEPopup.restoreSelection();

	// Fixes crash in Safari
	if (tinymce.isWebKit)
		ed.getWin().focus();
		
	if (formObj.imgid.value) {
        var src = parent.Glizy.tinyMCE_options.image_resizer+"?id="+formObj.imgid.value+"&w="+formObj.width.value+"&h="+formObj.height.value;
        formObj.alt.value = formObj.alt.value || formObj.title.value;
        var src = convertURL(src, tinyMCE.imgElement);
        
		var args
		var elm = ed.selection.getNode();
		if (elm && elm.nodeName == 'IMG') {
            setAttrib(elm, 'src', src);
            setAttrib(elm, 'alt');
            setAttrib(elm, 'title');
            setAttrib(elm, 'style');
            setAttrib(elm, 'class', formObj.cssclass.value);
        } else {
            var html = "<img";

            html += makeAttrib('src', src);
            html += makeAttrib('alt');
            html += makeAttrib('title');
            html += makeAttrib('style');
            html += makeAttrib('class', formObj.cssclass.value);
            html += " />";

            ed.execCommand("mceInsertContent", false, html, {skip_undo : 1});
			ed.undoManager.add();
        }
    }

	tinyMCEPopup.editor.execCommand('mceRepaint');
	tinyMCEPopup.editor.focus();
	tinyMCEPopup.close();
}

function cancelAction() {
	tinyMCEPopup.close();
}

function changeAppearance() {
	var formObj = document.forms[0];
	var img = document.getElementById('alignSampleImg');

	if (img) {
		img.align = formObj.align.value;
		img.border = formObj.border.value;
		img.hspace = formObj.hspace.value;
		img.vspace = formObj.vspace.value;
		updateStyle();
	}
}

function changeCssClass(elm) {
	if (elm.selectedIndex !=0)
	{
		var formObj = document.forms[0];
		formObj.cssclass.value = elm.value;
		elm.selectedIndex = 0;
	}
}

function updateStyle() {
	var ed = tinyMCEPopup.editor, dom = ed.dom;
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

	if (ed.settings.inline_styles) {
		st['width'] = formObj.width.value == '' ? '' : formObj.width.value + "px";
		st['height'] = formObj.height.value == '' ? '' : formObj.height.value + "px";
		st['border'] = formObj.border.value == '' ? '' : formObj.border.value + "px solid";
		if (formObj.vspace.value != '' || formObj.hspace.value != '')
		{
			st['margin'] = formObj.vspace.value == '' ? '0' : formObj.vspace.value + "px"
			st['margin'] += formObj.hspace.value == '' ? ' 0' : ' '+formObj.hspace.value + "px";
			st['margin'] += formObj.vspace.value == '' ? ' 0' : ' '+formObj.vspace.value + "px"
			st['margin'] += formObj.hspace.value == '' ? ' 0' : ' '+formObj.hspace.value + "px";
		}
		else
		{
			delete st['margin'];
		}
		
	} else {
		st['width'] = st['height'] = st['border'] = null;

		if (st['margin-top'] == st['margin-bottom'])
			st['margin-top'] = st['margin-bottom'] = null;

		if (st['margin-left'] == st['margin-right'])
			st['margin-left'] = st['margin-right'] = null;
	}

	st['float'] = formObj.align.value == '' ? '' : formObj.align.value;
	
	formObj.style.value = dom.serializeStyle(st);
}

function styleUpdated() {
	var ed = tinyMCEPopup.editor, dom = ed.dom;
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

	if (st['width'])
		formObj.width.value = st['width'].replace('px', '');

	if (st['height'])
		formObj.height.value = st['height'].replace('px', '');

	if (st['margin-top'] && st['margin-top'] == st['margin-bottom'])
		formObj.vspace.value = st['margin-top'].replace('px', '');

	if (st['margin-left'] && st['margin-left'] == st['margin-right'])
		formObj.hspace.value = st['margin-left'].replace('px', '');

	if (st['border-width'])
		formObj.border.value = st['border-width'].replace('px', '');
}

function changeHeight() {
	var formObj = document.forms[0];

	var temp = (formObj.width.value / formObj.orw.value) * formObj.orh.value;
	formObj.height.value = temp.toFixed(0);
	updateStyle();
}

function changeWidth() {
	var formObj = document.forms[0];

	var temp = (formObj.height.value / formObj.orh.value) * formObj.orw.value;
	formObj.width.value = temp.toFixed(0);
	updateStyle();
}

function getSelectValue(form_obj, field_name) {
	var elm = form_obj.elements[field_name];

	if (elm == null || elm.options == null)
		return "";

	return elm.options[elm.selectedIndex].value;
}

tinyMCEPopup.onInit.add(init, null);
