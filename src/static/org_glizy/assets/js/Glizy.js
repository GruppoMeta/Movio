/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
 
var tinyMCE = undefined;
var Glizy = new Object();
Glizy = {
	Onload: new Array(),
	Onunload: new Array(),
	Onbeforeunload: new Array(),
	windowsList: new Array(),
	slideShowSpeed: 20,

	start : function()
	{
		var oldonload = window.onload;
		var oldunonload = window.onunload;
		var onbeforeunload = window.onbeforeunload;

		window.onload = function ()
		{
			if (typeof oldonload == 'function')
			{
				oldonload();
			}
			for (var i=0;i<Glizy.Onload.length;i++)
			{
				eval(Glizy.Onload[i]);
			}
		}
		
		window.onunload = function ()
		{
			if (typeof oldunonload == 'function')
			{
				oldunonload();
			}
			for (var i=0;i<Glizy.Onunload.length;i++)
			{
				eval(Glizy.Onunload[i]);
			}
		}
		
		window.onbeforeunload = function ()
		{
			if (typeof oldunonload == 'function')
			{
				onbeforeunload();
			}
			for (var i=0;i<Glizy.Onbeforeunload.length;i++)
			{
				eval(Glizy.Onbeforeunload[i]);
			}
		}
	},
	
	addEvent: function (theType, theEvent)
	{
		if (theType=="onload")
			Glizy.Onload.push(theEvent);
		else if (theType=="onunload")
			Glizy.Onunload.push(theEvent);
		else if (theType=="onbeforeunload")
			Glizy.Onbeforeunload.push(theEvent);	
	},

	hideMessage: function(box)
	{
		if (!box) box = 'message-box'
		var el = typeof( box ) == "string" ? Glizy.$(box) : box;
		if (el.innerHTML!='')
		{
			el.innerHTML = '';
		}
	},
	
	showWarningMessage: function(text, messages, box)
	{
		if (!box) box = 'message-box'
		var html = '';
		if (messages.length)
		{
			html = '<div class="message error">';
			html += '<p><strong>'+GlizyLocale.WARNING+'</strong> '+text+'</p>';
			html += '<ul><li>'+messages.join('</li><li>')+'</li></ul></div>';
		}
		var el = typeof( box ) == "string" ? Glizy.$(box) : box;
		if (el) 
		{
			el.innerHTML = html;
			if (document.documentElement && document.documentElement.scrollTop)
				document.documentElement.scrollTop = 0;
			else if (document.body)
				document.body.scrollTop = 0;		
		}
	},
	
	showSuccessMessage: function(messages)
	{
		box = 'message-box'
		html = '<div class="message success">';
		html += '<p><strong>'+GlizyLocale.SUCCESS+'</strong> '+messages+'</p>';
		html += '</div>';
		var el = Glizy.$(box);
		if (el) 
		{
			el.innerHTML = html;
			if (document.documentElement && document.documentElement.scrollTop)
				document.documentElement.scrollTop = 0;
			else if (document.body)
				document.body.scrollTop = 0;		
		}
	},
	
	validateForm: function(elValue, elName, elType, message)
	{
		elValue = unescape(elValue);
		var resultState = true;
		switch (elType) {
			case 'date':
				resultState = elValue!='';
				break;
			case 'text':
			case 'longtext':
			case 'editableSelect':
				//var regExp = new RegExp("([a-zA-Z\ ]{3,})");
				//resultState = regExp.test(elValue);
				resultState = elValue!='';
				break;
			case 'select':
			case 'multipleselect':
				resultState = elValue!='';
				break;
			case 'colorPicker':
				var regExp = new RegExp("^(#){1}([a-fA-F0-9]){6}$");
				resultState = regExp.test(elValue);
				break;
		}
		//alert("validateForm "+elName+" "+(!resultState ? (!message ? message : elName) : '')) 
		return !resultState ? (!message ? message : elName) : '';
	},
	
	tableruler: function()
	{
		if (document.getElementById && document.createTextNode)
		{
			var tables=document.getElementsByTagName("table");
			for (var i=0;i<tables.length;i++)
			{
				if(tables[i].className=="list")
				{
					var trs=tables[i].getElementsByTagName("tr");
					for(var j=0;j<trs.length;j++)
					{
						if(trs[j].parentNode.nodeName=="TBODY" && trs[j].parentNode.nodeName!="TFOOT" && trs[j].className!="labels")
						{
							trs[j].oldClass = trs[j].className;
							trs[j].resetClass =function(){this.className=this.oldClass;return false}
							trs[j].onmouseover=function(){
								var nodes = this.parentNode.childNodes;
								for ( var i=0; i < nodes.length; i++ )
								{
									if ( nodes[i] != this && nodes[i].resetClass ) nodes[i].resetClass();
								}
								this.className="ruled";return false
							}
							trs[j].onmouseout=function(){this.resetClass()}
						}
					}
				}
			}
		}
	},

	tablerulerReset: function( element )
	{
		if (document.getElementById && document.createTextNode)
		{
			var nodes = element.parentNode.childNodes;
			for ( var i=0; i < nodes.length; i++ )
			{
				nodes[i].className = i % 2? "odd" : "even";
			}
			Glizy.tableruler();
		}
	},
	
	selectAllCheckbox: function(el, name)
	{
		var actionFrm = el.form;
		var checks = actionFrm.elements[name];
		if ( !el.checked )
		{
			el.checked = false;
		}
		//el.checked = !el.checked;
		var checkValue = el.checked;
		if (checks.length!=undefined)
		{
			for (counter = 0; counter < checks.length; counter++)
				checks[counter].checked = checkValue;
			}
		else
			checks.checked = checkValue;
	},

	
	
	openWindow: function(url,wnd_name,wnd_props)
	{
		var w = window.open(url,wnd_name,wnd_props);
		Glizy.windowsList.push(w);
		return w;
	},
	
	closeWindows: function()
	{
		for(var i=0; i<Glizy.windowsList.length; i++)
		{
			Glizy.windowsList[i].close();
		}
		Glizy.windowsList = new Array();
	},
	
	previewImage: function(id)
	{
		Glizy.closeWindows();
		Glizy.openWindow("imagePreview.php?id="+id,"preview","width=100,height=100,menubar=false,alwaysRaised=true,copyhistory=false,resize=false");
	},
	
	externalLinks: function()
	{
		// from SitePoint article
		// http://www.sitepoint.com/article/standards-compliant-world
		if (!document.getElementsByTagName) return;
		var anchors = document.getElementsByTagName("a");
		for (var i=0; i<anchors.length; i++) {
			var anchor = anchors[i];
			if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") anchor.target = "_blank";
		}
	},
	zoomViewer: null,
	openZoom: function( id )
	{
		var zoomContainer = document.getElementById('zoomContainer');
		if ( Glizy.zoomViewer == null )
		{
			SeadragonConfig.imgPath = "static/";
			Glizy.zoomViewer = new Seadragon.Viewer("zoomContainer");
			Glizy.zoomViewer.setFullPage( true );
			Glizy.zoomViewer.onFullPage = function()
			{
				Glizy.zoomViewer.close();
				zoomContainer.style.display = "none";
			}
		}
		var url = "zoom.php?id="+id;
		Seadragon.Utils.makeAjaxRequest(url, function(xhr) {
			zoomContainer.style.display = "block";
			Glizy.zoomViewer.setFullPage( true );
			Glizy.zoomViewer.openDzi("cache/zoom_"+id+".xml");
		});

	},
	
	$: function() {
	  var elements = new Array();
	
	  for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
		  element = document.getElementById(element);
	
		if (arguments.length == 1) 
		  return element;
	
		elements.push(element);
	  }
	
	  return elements;
	}
	
};

Glizy.tinyMCE_plugins = '';
Glizy.tinyMCE_pluginsNames = '';

var GlizyLocale = new Object();
Glizy.start();
Glizy.addEvent('onload', 'Glizy.tableruler()');
Glizy.addEvent('onload', 'Glizy.externalLinks()');
