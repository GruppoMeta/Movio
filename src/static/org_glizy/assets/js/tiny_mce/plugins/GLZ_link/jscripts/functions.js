/* SVN FILE: $Id: functions.js 298 2008-10-21 23:21:58Z ugoletti $ */

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
 * @author		 Daniele Ugoletti <daniele@ugoletti.com>
 * @category	 javascript
 * @since        Glizy v 0.01
 * @version      $Rev: 298 $
 * @modifiedby   $LastChangedBy: ugoletti $
 * @lastmodified $Date: 2008-10-22 01:21:58 +0200 (mer, 22 ott 2008) $
 */


/* Functions for the advlink plugin popup */

var currentSize = {w: 420, h: 530 };

function renderInternalLinks() {
    var intLinks = parent.Glizy.tinyMCE_options.internal_links;
    var aSel = ['<select id="internalLink" style="width: 100%" disabled="disabled">'];
    aSel.push('<option value="">---</option>');
    for (var i=0;i<intLinks.length;i++) {
        aSel.push('<option value="internal:'+intLinks[i][1]+'">'+intLinks[i][0]+'</option>');
    }
    aSel.push('</select>');

  	var intLinks = parent.Glizy.tinyMCE_options.glossary_links;
    aSel.push( '<select id="glossaryLinks" style="width: 100%; display: none;">' );
    aSel.push('<option value="">---</option>');
    for (var i=0;i<intLinks.length;i++) {
        aSel.push('<option value="glossary:'+intLinks[i]+'">'+intLinks[i]+'</option>');
    }
    aSel.push('</select>');
    document.write(aSel.join(""));
}



function setLinkType(type) {
	var wSize;
	if (type=="1")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "disabled";
		document.getElementById("preview").disabled = "disabled";
		document.getElementById("internalLink").disabled = "";
		document.getElementById("glossaryLinks").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
		wSize = {w: 420, h: 530 }
	}
	else if (type=="2")
	{
		document.getElementById("protocol").disabled = "";
		document.getElementById("linkUrl").disabled = "";
		document.getElementById("preview").disabled = "";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("glossaryLinks").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
		wSize = {w: 420, h: 530 }
	}
	else if (type=="3")
	{
		if (document.getElementById("picker").src=='') document.getElementById("picker").src = parent.Glizy.tinyMCE_options.media_picker;
		document.getElementById("pickerPanel").style.display = "block";
		document.getElementById("linkUrl").disabled = "disabled";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("glossaryLinks").style.display = "none";
		document.getElementById("protocol").disabled = "disabled";
		wSize = {w: 900, h: 530 }
	}
	else if (type=="4")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "";
		document.getElementById("preview").disabled = "disabled";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("glossaryLinks").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
		wSize = {w: 420, h: 530 }
	}
	else if (type=="5")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "disabled";
		document.getElementById("preview").disabled = "disabled";
		document.getElementById("internalLink").style.display = "none";
		document.getElementById("glossaryLinks").style.display = "inline";
		document.getElementById("pickerPanel").style.display = "none";
		wSize = {w: 420, h: 530 }
		resizeMe(wSize.w, wSize.h);
	}
	else if (type=="6")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "";
		document.getElementById("preview").disabled = "disabled";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("glossaryLinks").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
		wSize = {w: 420, h: 530 }
	}

	resizeMe( wSize );
}

function resizeMe( newSize ) {
    var winID = tinyMCEPopup.id;
    var wm = tinyMCEPopup.editor.windowManager;
	
    wm.resizeBy(newSize.w - currentSize.w, newSize.h - currentSize.h, winID);
	var left = parseInt( tinymce.DOM.getStyle( winID, 'left' ).replace( 'px', '' ) );
	tinymce.DOM.setStyle( winID, 'left', ( left - (newSize.w - currentSize.w) / 2 ) + 'px' ); 
	currentSize = newSize;
}

function previewLink() {
    var url = document.getElementById("protocol").value+document.getElementById("linkUrl").value;
    if (url) try {window.open(url,"urlTest","")} catch(err) {};
}

function init() {
	tinyMCEPopup.resizeToInnerSize();
	var formObj = document.forms[0];
	var inst = tinyMCEPopup.editor;
	var elm = inst.selection.getNode(); //inst.getFocusElement();
	var action = "insert"; 

	elm = inst.dom.getParent(elm, "A");
	if (elm != null && elm.nodeName == "A")
		action = "update";
	
	formObj.insert.value = tinyMCEPopup.getLang(action, 'Insert', true); 

    if (action == "update") {
		var href = inst.dom.getAttrib(elm, 'href');
		href = convertURL(href, elm, true);

		// Setup form data
		var linkType = 0;
		var protocol = 'http://';
		if (href.indexOf("internal:")==0) 
		{
			linkType = 1;
			setFormValue('linkUrl', '');
		}
		else if (href.indexOf("media:")==0) 
		{
			linkType = 3;
			var mediaInfo = href.split(':');
			setFormValue('linkUrl', mediaInfo[2]);
			setFormValue('mediaId', mediaInfo[1]);
			setFormValue('mediaTitle', mediaInfo[2]);
		}
		else if (href.indexOf("#")==0) 
		{
			linkType = 4;
			setFormValue('linkUrl', href);
		}
		else if (href.indexOf("glossary:")==0) 
		{
			linkType = 5;
			setFormValue('linkUrl', '');
			setFormValue('glossaryLinks', href );
		}
		else
		{
			var regExp = new RegExp("(http://|https://|ftp://|mailto:)(.*)", "gi");
			linkType = href.match( regExp ) ? 2  : 6 ;
			if ( linkType == 2 )
			{
				protocol = href.replace(regExp, "$1");
			setFormValue('linkUrl', href.replace(protocol,""));
		}
			else
			{
				setFormValue('linkUrl', href );
			}
		}
		
		setFormValue('linkType', String(linkType));
		setLinkType(String(linkType));
        setFormValue('protocol', protocol);
		setFormValue('internalLink', linkType==1 ? href:"");
		setFormValue('title', inst.dom.getAttrib(elm, 'title'));
		setFormValue('id', inst.dom.getAttrib(elm, 'id'));
		setFormValue('style', inst.dom.getAttrib(elm, 'style'));
		setFormValue('cssclass', inst.dom.getAttrib(elm, 'class'));
		setFormValue('dir', inst.dom.getAttrib(elm, 'dir'));
		setFormValue('hreflang', inst.dom.getAttrib(elm, 'hreflang'));
		setFormValue('lang', inst.dom.getAttrib(elm, 'lang'));
		setFormValue('charset', inst.dom.getAttrib(elm, 'charset'));
		document.forms[0].elements['relExternal'].checked = tinymce.DOM.getAttrib(elm, 'rel') == "external";
		setFormValue('tabindex', inst.dom.getAttrib(elm, 'tabindex', typeof(elm.tabindex) != "undefined" ? elm.tabindex : ""));
		setFormValue('accesskey', inst.dom.getAttrib(elm, 'accesskey', typeof(elm.accesskey) != "undefined" ? elm.accesskey : ""));
	}


	window.focus();
}

function setFormValue(name, value) {
	document.forms[0].elements[name].value = value;
}



function convertURL(url, node, on_save) {
	return url;
	//return eval("tinyMCEPopup.windowOpener." + tinyMCE.settings['urlconverter_callback'] + "(url, node, on_save);");
}

function setAttrib(elm, attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib.toLowerCase()];
	var dom = tinyMCEPopup.editor.dom;

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	// Clean up the style
	if (attrib == 'style')
		value = dom.serializeStyle(dom.parseStyle(value), 'a');

	dom.setAttrib(elm, attrib, value);
}

function insertAction() {
	var inst = tinyMCEPopup.editor;
	var elm, elementArray, i;

	elm = inst.selection.getNode();
	elm = inst.dom.getParent(elm, "A");

	tinyMCEPopup.execCommand("mceBeginUndoLevel");

	// Create new anchor elements
	if (elm == null) {
		inst.getDoc().execCommand("unlink", false, null);
		tinyMCEPopup.execCommand("CreateLink", false, "#mce_temp_url#", {skip_undo : 1});

		elementArray = tinymce.grep(inst.dom.select("a"), function(n) {return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';});
		for (i=0; i<elementArray.length; i++)
			setAllAttribs(elm = elementArray[i]);
	} else
		setAllAttribs(elm);

	// Don't move caret if selection was image
	if (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG') {
		inst.focus();
		inst.selection.select(elm);
		inst.selection.collapse(0);
		tinyMCEPopup.storeSelection();
	}

	tinyMCEPopup.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}



function setAllAttribs(elm) {
	var formObj = document.forms[0];
	var linkUrlObj = document.getElementById("linkUrl");
    var href;
    switch (formObj.linkType.value) {
    case "1":
        href = formObj.internalLink.value;
        break;
    case "2":
        href = formObj.protocol.value+linkUrlObj.value;
        break;
    case "3":
        href = 'media:'+formObj.mediaId.value+":"+formObj.mediaTitle.value;
        break;
	 case "4":
        href = (linkUrlObj.value.indexOf('#')!=0 ? '#' : '')+linkUrlObj.value;
        break;
    case "5":
        href = formObj.glossaryLinks.value;
        break;
	 case "6":
		href = linkUrlObj.value;
    }
	if (href) {
        href = convertURL(href, elm);
        setAttrib(elm, 'href', href);
        setAttrib(elm, 'title');
        setAttrib(elm, 'id');
        setAttrib(elm, 'style');
        setAttrib(elm, 'class', formObj.cssclass.value);
        setAttrib(elm, 'rel', formObj.elements['relExternal'].checked ? "external" : "" );
        setAttrib(elm, 'charset');
        setAttrib(elm, 'hreflang');
        setAttrib(elm, 'dir');
        setAttrib(elm, 'lang');
        setAttrib(elm, 'tabindex');
        setAttrib(elm, 'accesskey');

        // Refresh in old MSIE
        if (tinyMCE.isMSIE5)
            elm.outerHTML = elm.outerHTML;
    }
    else {
        setAttrib(elm, 'href', null);
    }
}


function getSelectValue(form_obj, field_name) {
	var elm = form_obj.elements[field_name];

	if (elm == null || elm.options == null)
		return "";

	return elm.options[elm.selectedIndex].value;
}


function update(i,t) {
	var linkUrlObj = document.getElementById("linkUrl");
	var formObj = document.forms[0];
    with (formObj) {
        mediaId.value = i;
        mediaTitle.value = t;
        title.value = t;
    }
	linkUrlObj.value = t;
}


tinyMCEPopup.onInit.add(init);