/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

Glizy.pageContent.$ = function( sel) {
	return jQuery( sel )[ 0 ];
}

Glizy.pageContent.$all = function( sel) {
	return jQuery( sel );
}

if ( !Glizy.pageContent_widgets )
{
	Glizy.pageContent_widgets = {};
}

Glizy.pageContent_widgets._registredWidgets = {};
Glizy.pageContent_widgets.callMethod = function( id, method, arg )
{
	var widget = Glizy.pageContent_widgets._registredWidgets[ id ];
	if ( widget )
	{
		widget[ method ].call( widget, arg );
	}
}

Glizy.pageContent_widgets.Text = function()
{
	this.properties	= null;
	this.element 	= null;
	this.elementId 	= null;
	this.self 		= this;
	this.init 		= function(oEl)
	{
		this.properties = oEl;
		this.elementId 	= oEl.name;
		oEl._obj = this;
		var html = '<input type="text" id="'+oEl.name+'" value="'+oEl.value.replace(/\"/gi,"&quot;")+'" alt="" size="'+oEl.size+'"'+(oEl.disabled?' disabled="disabled"':'')+(oEl.readOnly?' readOnly="true"':'')+(oEl.maxLength?' maxlength="'+oEl.maxLength+'"':'')+'/>';
		return html;
	};

	this.getValue	= function()
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);
		return this.element.value;
	}
	
	this.setValue	= function(val)
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);	
		this.element.value = val;	
	}
}

Glizy.pageContent_widgets.Date = function()
{
	this.properties	= null;
	this.element 	= null;
	this.elementId 	= null;
	this.self 		= this;
	this.init 		= function(oEl)
	{
		Glizy.pageContent_widgets._registredWidgets[ oEl.name ] = this;
		this.properties = oEl;
		this.elementId 	= oEl.name;
		oEl._obj = this;
		var html = '<input type="text" id="'+oEl.name+'" value="'+oEl.value+'" autocomplete="off" size="'+( oEl.type == "date" ? '10' : '20' )+'" />';
		return html;
	};
	
	this.refine	= function()
	{
		$( "#"+this.properties.name ).datepicker().datepicker( "option", "dateFormat", "dd/mm/yy" );
		$( "#"+this.properties.name ).val( this.properties.value );
	}

	this.getValue	= function()
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);
		return this.element.value;
	}
	
	this.setValue	= function(val)
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);	
		this.element.value = val;	
	}
}

Glizy.pageContent_widgets.ImageSelect = function()
{
	this.properties	= null;
	this.element 	= null;
	this.elementId 	= null;
	this.self 		= this;
	this.init 		= function(oEl)
	{
		this.properties = oEl;
		this.elementId 	= oEl.name;
		oEl._obj = this;
		
		var html = '';
		var value = '';
		html += '<div id="cpanel_'+oEl.name+'" class="cpanel">';
		for (var i=0;i<oEl.options.length;i++)
		{
			html += '<div class="icon"><a href="#" class="'+(oEl.options[i][2] ? 'selected' : '')+'" onclick="return Glizy.pageContent_widgets.ImageSelect_onclick(this, \''+oEl.name+'\', \''+oEl.options[i][1]+'\');">';
			html += '<img src="'+oEl.options[i][3]+'" alt="'+oEl.options[i][0]+'" align="middle" border="0">';
			html += '<span>'+oEl.options[i][0]+'</span></a></div>';

			if (oEl.options[i][2])
			{
				var value = oEl.options[i][1];
			}
		}
		
		html += '<input type="hidden" id="'+oEl.name+'" value="'+value+'" alt="" /><div class="clear"></div></div>';
		return html;
	};

	this.getValue	= function()
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);
		return this.element.value;
	};
	
	this.setValue	= function(val)
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);	
		this.element.value = val;	
	};

}

Glizy.pageContent_widgets.ImageSelect_onclick = function(el, elName, val)
{
	var olEl = Glizy.pageContent.$all('#cpanel_'+elName+' a.selected');
	if (olEl.length) olEl[0].className = '';
	el.className = 'selected';
	var el = document.getElementById(elName);	
	el.value = val;	
	return false;
}

Glizy.pageContent_widgets.EditableSelect = function()
{
	this.properties	= null;
	this.element 	= null;
	this.elementId 	= null;
	this.self 		= this;
	this.init 		= function(oEl)
	{
		this.properties = oEl;
		this.elementId 	= oEl.name;
		oEl._obj = this;
		
		var html = '';

		html += '<input type="text" id="'+oEl.name+'" value="'+oEl.value.replace(/\"/gi,"&quot;")+'" size="'+oEl.size+'"'+(oEl.disabled?' disabled="disabled"':'')+'/>';
		html += '<img src="../static/org_glizy/assets/images/icon_add.gif" onclick="Glizy.pageContent_widgets.EditableSelect_onclick('+(oEl.append ? true:false)+', \''+oEl.name+'\',\''+oEl.delimiter+'\')" class="iconAction" />';
		html += '<select id="sel__'+oEl.name+'" size="'+(oEl.options.length>10 ? 10 : oEl.options.length+1)+'" style="position: absolute; display: none;">';
		html += '<option value=""></option>';

		for (var i=0;i<oEl.options.length;i++)
		{
			
			html += '<option value="'+oEl.options[i][1]+'">'+oEl.options[i][0]+'</option>';
		}
		
		html += '</select>';
		return html;
	};

	this.getValue	= function()
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);
		return this.element.value;
	};
	
	this.setValue	= function(val)
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);	
		this.element.value = val;	
	};

}

Glizy.pageContent_widgets.EditableSelect_onclick = function(append, elName, delimiter)
{
	var inputEl = Glizy.pageContent.$("#"+elName);
	if (inputEl) 
	{
		var selectEl = Glizy.pageContent.$('#sel__'+elName);
		if (selectEl) 
		{
			Glizy.pageContent.aSelect = document.getElementsByTagName("select");
	        for (var i=0; i< Glizy.pageContent.aSelect.length; i++)
			{
				Glizy.pageContent.aSelect[i].style.visibility = "hidden";
	        }
			
			var sb = Glizy.pageContent.$all(".mceToolbar");
			if ( sb.length )
			{
				for( var i=0; i < sb.length; i++ )
				{
					sb[i].style.visibility = "hidden";
				}
			}
			var offset = jQuery(inputEl).offset();
			selectEl.style.left = ( offset.left - 2 )+"px";
			selectEl.style.top = ( offset.top -2)+"px";
			selectEl.style.width = jQuery(inputEl).width() +"px";
			selectEl.style.display = "inline";
			selectEl.style.visibility = "visible";
			selectEl.style.zIndex = 100;
			selectEl.style.height = "auto";
			selectEl._parentObj = inputEl;
			selectEl._append = append;
			selectEl.selectedIndex = 0;
			selectEl.focus();
			selectEl.onchange = function()
			{
		        for (var i=0; i< Glizy.pageContent.aSelect.length; i++)
				{
					Glizy.pageContent.aSelect[i].style.visibility = "visible";
		        }
				var sb = Glizy.pageContent.$all(".mceToolbar");
				if ( sb.length )
				{
					for( var i=0; i < sb.length; i++ )
					{
						sb[i].style.visibility = "visible";
					}
				}
				this.style.display = "none";
				if (this.selectedIndex==0) return false;
				if (this._append)
				{
					this._parentObj.value += (this._parentObj.value=='' ? '' : delimiter)+this.options[this.selectedIndex].text;
				}
				else
				{
					this._parentObj.value = this.options[this.selectedIndex].text;
				}
				this._parentObj.focus();
				return false;
			};
			selectEl.onblur = function()
			{
				for (var i=0; i< Glizy.pageContent.aSelect.length; i++)
				{
					Glizy.pageContent.aSelect[i].style.visibility = "visible";
		        }
				var sb = Glizy.pageContent.$all(".mceToolbar");
				if ( sb.length )
				{
					for( var i=0; i < sb.length; i++ )
					{
						sb[i].style.visibility = "visible";
					}
				}
				this.style.display = "none";
				this._parentObj.focus();
			};
		}
	}
}



Glizy.pageContent_widgets.EditableSelectThesaurus = function()
{
	this.elForPicker	= null;
	this.properties	= null;
	this.element 	= null;
	this.elementId 	= null;
	this.self 		= this;
	this.mode 		= "0";
	this.init 		= function(oEl)
	{
		Glizy.pageContent_widgets._registredWidgets[ oEl.name ] = this;
		this.properties = oEl;
		this.elementId 	= oEl.name;
		oEl._obj = this;
		
		var html = '<input type="hidden" id="'+oEl.name+'" value="'+oEl.value.replace(/\"/gi,"&quot;")+'" />';
		html += '<div id="'+oEl.name+'_thesaurus" class="thesaurus"></div>';
		html += '<img id="'+oEl.name+'_btn2" src="../static/org_glizy/assets/images/icon_addThesaurus.gif" onclick="Glizy.pageContent_widgets.callMethod( \''+oEl.name+'\', \'openThesaurus\')" class="iconAction" />';

		return html;
	};
	
	this.refine = function()
	{
		this.updateState();
	};

	this.updateState = function()
	{
		var value = document.getElementById(this.properties.name).value
		var html = "";
		// disegna i valori
		// __pico__http://culturaitalia.it/pico/thesaurus/4.1#web_designer"Web designer",http://culturaitalia.it/pico/thesaurus/4.1#designer_di_interni_e_allestimenti"Designer di interni e allestimenti"
		if ( value != "" )
		{
			value = value.substring( 8 )
			var aValues = value.split( "," );
			for( var i=0; i < aValues.length; i++ )
			{
		        var part = aValues[ i ].split( "\"" );
		        html += "<div class=\"thesaurusItem\">"+part[ 1 ]+"<a href=\"#\" onclick=\"Glizy.pageContent_widgets.callMethod( '"+this.properties.name+"', 'removeThesaurus', '"+part[ 0 ]+"');return false;\"></a></div>";
			}
		}
		
		document.getElementById(this.properties.name+"_thesaurus").innerHTML = html;
	};
	
	this.removeThesaurus = function( id )
	{
		var value = document.getElementById(this.properties.name).value;
		value = value.substring( 8 )
		var aValues = value.split( "," );
		var newValues = [];
		for( var i=0; i < aValues.length; i++ )
		{
			var part = aValues[ i ].split( "\"" );
			if ( part[ 0 ] != id )
			{
				newValues.push( aValues[ i ] );
			}
		}
		
		if ( newValues.length )
		{
			document.getElementById(this.properties.name).value = "__pico__"+newValues.join(",")
		}
		else
		{
			document.getElementById(this.properties.name).value = "";
		}
		this.updateState();
	};
	
	this.openThesaurus = function()
	{
		Glizy.pageContent_widgets.EditableSelectThesaurus.elForPicker = this.elementId;
		Glizy.pageContent.openModalPicker( Glizy.pageContent.thesaurusSrc, "Seleziona voci da aggiungere al thesaurus PICO" );
	};
	
	this.addThesaurus = function( newVal )
	{
		var value = document.getElementById(this.properties.name).value;
		if ( value.indexOf( newVal ) != -1 ) return;
		if ( value == "" ) value = "__pico__";
		else value += ",";
		value += newVal;
		document.getElementById(this.properties.name).value = value;
		this.updateState();
		Glizy.pageContent.closeModalPicker();
	},
	
	this.getValue	= function()
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);
		return this.element.value;
	};
	
	this.setValue	= function(val)
	{
		if (!this.element) this.element = document.getElementById(this.properties.name);	
		this.element.value = val;	
	};
}


// code based on http://code.google.com/p/locationpicker/
Glizy.pageContent_widgets.Map = function()
{
	this.properties	= null;
	this.element 	= null;
	this.elementId 	= null;
	this.elementMap = null;
	this.self 		= this;
	this.map		= null;
	this.marker 	= null;
	this.geocoder 	= null;
	this.init 		= function(oEl)
	{
		Glizy.pageContent_widgets._registredWidgets[ oEl.name ] = this;
		this.properties = oEl;
		this.elementId 	= oEl.name;
		oEl._obj = this;
		
		
		var html = '<input type="text" id="'+oEl.name+'" value="'+oEl.value.replace(/\"/gi,"&quot;")+'" alt="" size="30" />';
		html += '<input id="'+oEl.name+'-search"class="mapPickerSearch" type="button" value="Cerca"/>';
		html += '<div id="'+oEl.name+'-picker" class="mapPicker" style="left: 230px; width: 400px; height: 400px; background: #fff; border: 1px solid #ccc; padding: 5px; position: absolute; z-index: 3000; display: none;"></div>';
		return html;
	};
	
	this.refine = function()
	{
		var self = this;
		this.geocoder = new google.maps.Geocoder();
		this.element = document.getElementById( this.properties.name );
		
		this.elementMap = document.getElementById(this.properties.name+"-picker");

		var pos = this.getDefaultCurrentPosition();
		var latlng = new google.maps.LatLng(pos[ 0 ], pos[ 1 ]);
		var myOptions = {
	                    zoom: pos[ 2 ],
	                    center: latlng,
	                    mapTypeId: google.maps.MapTypeId.HYBRID,
	                    mapTypeControl: false,
	                    disableDoubleClickZoom: true,
	                    streetViewControl: false
	                }
		this.map = new google.maps.Map( this.elementMap, myOptions);
		this.marker = new google.maps.Marker({
		                    position: latlng, 
		                    map: this.map, 
		                    title: "Trascinami",
		                    draggable: true
		                });
						
						
		var self = this;
		google.maps.event.addListener( this.map, 'dblclick', function(event) {
		    self.setPosition( event.latLng );
		});

		google.maps.event.addListener( this.marker, 'dragend', function(event) {
		    self.setPosition( self.marker.position );
		});
		
		google.maps.event.addListener( this.map, 'idle', function(event) {
			self.setPositionValues();
		});
		
		Glizy.pageContent.addEvent( this.elementMap, "click", function( e ) {
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}
			e.cancelBubble = true;
		} );
		
		Glizy.pageContent.addEvent( this.element, "click", function( e ) {
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}
			e.cancelBubble = true;
		} );
		
		Glizy.pageContent.addEvent( document, "click", function( e ) {
			self.elementMap.style.display = "none";
		} );
		
		
		var elementBtn = document.getElementById(this.properties.name+"-search");
		Glizy.pageContent.addEvent( elementBtn, "click", function( e ) {
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}
			e.cancelBubble = true;
			self.search();
		} );
	};

	this.trim = function (str)
	{
		var	str = str.replace(/^\s\s*/, ''),
				ws = /\s/,
				i = str.length;
		while (ws.test(str.charAt(--i)));
		return str.slice(0, i + 1);
	}
	
	this.roundDecimal = function( num, decimals )
	{
        var mag = Math.pow(10, decimals);
        return Math.round(num * mag)/mag;
	};
	
	this.getDefaultCurrentPosition = function()
	{
		var posStr = this.getValue();
		if(posStr != "")
		{
			var posArr = posStr.split(",");
			if(posArr.length == 2 || posArr.length == 3 )
			{
				var lat = this.trim( posArr[0] );
				var lng = this.trim( posArr[1] );
				var zoom = posArr.length == 3 ? parseInt( this.trim( posArr[2] ) ) : 15;
				return [lat, lng, zoom ];
			}
		}
		return [ 51.500152, -0.126236, 15 ];
	}
	
	this.getCurrentPosition = function()
	{
		var pos = this.getDefaultCurrentPosition();
		var latlng = new google.maps.LatLng(pos[ 0 ], pos[ 1 ]);
		this.setPosition(latlng);
	}
	
	this.setPosition = function(latLng, viewport)
	{
		var lat = this.roundDecimal( latLng.lat(), 6 );
		var lng = this.roundDecimal( latLng.lng(), 6 );
		this.marker.setPosition( latLng );
		var zoom;
		if ( viewport )
		{
			this.map.fitBounds( viewport );
			this.map.setZoom( this.map.getZoom() + 2);
			zoom = this.map.getZoom() + 2;
		}
		else
		{
			this.map.panTo(latLng);
			zoom = this.map.getZoom();
		}
		this.map.setZoom( zoom );
		this.setValue( lat + "," + lng+","+zoom );
	}
	
	this.setPositionValues = function()
	{
		if( this.elementMap.style.display != "none")
		{
			var pos = this.getDefaultCurrentPosition();
			pos[ 2 ] = this.map.getZoom();
			this.setValue( pos.join( "," ) );
		}
	}
	
	this.isLngLat = function (val)
	{
        var lngLatArr = val.split(",");
        if(lngLatArr.length == 2 || lngLatArr.length == 3 ){
            if(isNaN(lngLatArr[0]) || isNaN(lngLatArr[1])){
                return false;
            }else{
                return true;
            }
        }
        return false;
    }
	
	this.openMap = function()
	{
		this.elementMap.style.display = "block";
		google.maps.event.trigger( this.map, 'resize');
		this.getCurrentPosition();
		this.map.setCenter( this.marker.position );
	};

	this.search = function()
	{
		this.findAddress();
    }

	this.findAddress = function()
	{
		var self = this;
        var address = this.getValue();
        if(address == ""){
            alert("Inserire un indirizzo o le coordinate longitudine/latitudine.");
        }else{
            if(this.isLngLat(address)){
                this.openMap();
            }else{
                this.geocoder.geocode( {'address': address, 'region': 'it'}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        self.setPosition(
                            results[0].geometry.location,
                            results[0].geometry.viewport
                        );
                        self.openMap();
                    } else {
                        alert("Geocode was not successful for the following reason: " + status);
                    }
                });
            }
            this.focus();
        }
    }

	this.focus = function()
	{
		this.element.focus();
	}

	this.getValue	= function()
	{
		return this.element.value;
	}
	
	this.setValue	= function(val)
	{
		this.element.value = val;	
	}
}

with(navigator){
    Glizy.pageContent.b=0;
    if(appName=="Microsoft Internet Explorer"){if(userAgent.indexOf("MSIE 6.0"))Glizy.pageContent.b=1}
    else{if(product=="Gecko"&&appVersion.substring(0,1)=="5")Glizy.pageContent.b=2};
}
if(self.Node) {
    Node.prototype.swapNode = function (node) {
        var nextSibling = this.nextSibling;
        var parentNode = this.parentNode;
        node.parentNode.replaceChild(this, node);
        parentNode.insertBefore(node, nextSibling); 
    }
}

if ( Glizy.tinyMCE_options )
{
	Glizy.tinyMCE_options.internal_links = Glizy.pageContent.internalLinks || [];
	Glizy.tinyMCE_options.image_picker = Glizy.pageContent.editorImgPicker || "";
	Glizy.tinyMCE_options.image_resizer = Glizy.pageContent.editorImgResizer || "";
	Glizy.tinyMCE_options.media_picker = Glizy.pageContent.editorMediaPicker || "";
	Glizy.tinyMCE_options.glizy_admin_path = Glizy.pageContent.editorAdminPath || "";
}

Glizy.pageContent.aHTML = [];
Glizy.pageContent.hO = [];
Glizy.pageContent.nF = 0;
Glizy.pageContent.nE = 0;
Glizy.pageContent.origMediaSrc = Glizy.pageContent.mediaSrc;

Glizy.pageContent.buildHTML = function(name,oEl,loopItm) {
    oEl.name = "elID"+Glizy.pageContent.nF;
    this.aHTML.push('<div id="s'+oEl.name+'" class="formItem clearfix"'+( oEl.type == "hidden" ? ' style="display: none"' : '' )+'>');
    if (oEl.id) {
        Glizy.pageContent.aId[oEl.id] = oEl.name;
    }
    Glizy.pageContent.nF++;
    if ("repeater record static hidden".indexOf(oEl.type)<0 && oEl.label != false ) {
		// MODIFICA DANIELE
        this.aHTML.push('<label id="label_'+oEl.id+'" for="'+oEl.name+'" class="'+(oEl.required && oEl.required!=false ? 'required':'')+'">'+(oEl.label || name+"????")+'</label>');
    }
    switch (oEl.type) {
    // case "hidden":
    //     this.aHTML.push('</div>');
    //     return;
    case "text":
        this.aHTML.push('<input type="text" id="'+oEl.name+'" value="'+oEl.value.replace(/\"/gi,"&quot;")+'" alt="" size="'+oEl.size+'"'+(oEl.disabled?' disabled="disabled"':'')+(oEl.readOnly?' readOnly="true"':'')+(oEl.maxLength?' maxlength="'+oEl.maxLength+'"':'')+'/>');
		//this.aHTML.push(new Glizy.pageContent_widgets.Text().init(oEl));
        break;
    case "imageSelect":
		this.aHTML.push(new Glizy.pageContent_widgets.ImageSelect().init(oEl));
        break;
    case "editableSelect":
		this.aHTML.push(new Glizy.pageContent_widgets.EditableSelect().init(oEl));
        break;
	case "editableSelectThesaurus":
		this.aHTML.push(new Glizy.pageContent_widgets.EditableSelectThesaurus().init(oEl));
        break;
	case "map":
		this.aHTML.push(new Glizy.pageContent_widgets.Map().init(oEl));
        break;
    case "longtext":
        this.aHTML.push('<textarea id="'+oEl.name+'"');
        this.aHTML.push(' rows="'+oEl.rows+'" cols="'+oEl.cols+'"');
        
		if (!oEl.htmlEditor) 
		{
			oEl.value = oEl.value.replace(/<br>/g, "\n");
			oEl.value = oEl.value.replace(/<br \/>/g, "\n");
		}
		else
		{
			oEl.value = unescape( oEl.value );		
		}
		this.aHTML.push(oEl.maxLength ? ' onkeypress="event.keyCode=(this.value.length>='+oEl.maxLength+'?0:event.keyCode)"':'');
        this.aHTML.push(' wrap="VIRTUAL">'+oEl.value+'</textarea>');
        if (oEl.htmlEditor) {
            this.aHtmlEditables.push(oEl.name);
        }
        break;
    case "image":
        oEl.value = oEl.img_id;
        this.aHTML.push(oEl.template.replace("##src##",oEl.src).replace("##title##",oEl.title).replace("></div"," onmousedown=\"Glizy.pageContent.changeImg(event,this,"+loopItm+")\" id=\""+oEl.name+"\"></div"));
        break;
    case "media":
        this.aHTML.push('<input type="text" id="'+oEl.name+'" value="'+oEl.value+'" alt="" size="'+(oEl.size || 50)+'" readonly="readonly" style="cursor:pointer" onmousedown="Glizy.pageContent.changeMedia(event,this,'+loopItm+', \''+oEl.mediaType+'\')"/>');
        oEl.value = oEl.media_id;
        break;
    case "date":
    case "datetime":
		this.aHTML.push(new Glizy.pageContent_widgets.Date().init(oEl));

        // this.aHTML.push('<input type="text" id="'+oEl.name+'" value="'+oEl.value+'" autocomplete="off" size="'+( oEl.type == "date" ? '10' : '20' )+'" />');
        // this.aHTML.push('<img src="'+Glizy.pageContent.iconPath+'calendar.gif" style="cursor: pointer; border: 1px solid red;" title="Date selector" onmouseover="this.style.background=\'red\';" onmouseout="this.style.background=\'\'" id="'+oEl.name+'bt"/>');
        // this.aCalendarAttach.push([ oEl.name, oEl.type ]);
        break;
    case "checkbox":
        this.aHTML.push('<input id="'+oEl.name+'" type="checkbox"'+(oEl.value=="1"?' checked':'')+'/>');
        break;
    case "radio":
        this.aHTML.push('<input id="'+oEl.name+'" name="'+oEl.formName+'" type="radio"'+(oEl.value=="1"?' checked':'')+'/>');
        break;
    case "helpedselect": 
        this.aHTML.push('<table class="listSimple" style="width:500px;table-layout:fixed"><tr>');

        this.aHTML.push('<td width="33%" style="border:1px solid #CCC;border-bottom:0;padding:5px"><select id="'+oEl.name+'P0" style="margin-bottom:0;width:100%" onchange="Glizy.pageContent.helpedChange(\''+oEl.name+'P0\',\''+oEl.name+'S0\')"><option selected value="">'+pageTexts.searchOn+'</option>');
            for (var h=0;h<oEl.headers.length;h++) {
                this.aHTML.push('<option value="'+oEl.headers[h][1]+'">'+oEl.headers[h][0]+'</option>');
            }
            this.aHTML.push('</select></td>');
        this.aHTML.push('<td width="66%"><input id="'+oEl.name+'S0" style="margin-top:0;width:97%" disabled="disabled"/></td>');
        this.aHTML.push("</tr><tr>")

        this.aHTML.push('</tr><tr><td colSpan="3" style="background-color:#44A1FF;text-align:center"><button class="popupButton" onclick="Glizy.pageContent.getResult(\''+name+'\','+oEl.iconWidth+')">'+pageTexts.goSearch+'</button></tr>');
        this.aHTML.push('<tr><td colspan="3"><div id="'+oEl.name+'R" style="overflow: auto; width: 100%; height: 150px;"></div></td></tr>');
        this.aHTML.push('<tr><td colspan="3" style="background-color:#44A1FF;color:#FFF;text-align:center">'+pageTexts.selItems+'</tr>');
        this.aHTML.push('<tr><td colspan="3"><div style="width: 100%;">');
        this.aHTML.push('<table class="list" style="table-layout:fixed;width:100%" id="'+oEl.name+'V">');
        this.aHTML.push('<tr class="_ph"'+(oEl.options.length?' style="display:none"':'')+'><td width="'+oEl.iconWidth+'"></td><td></td></tr>');
        for (var s=0;s<oEl.options.length;s++) {
            this.aHTML.push('<tr class="'+(s%2?"odd":"even")+'" value="'+oEl.options[s][1]+'">');
            this.aHTML.push('<td width="'+oEl.iconWidth+'">');
            if (oEl.options[s][2]) {
                this.aHTML.push('<img style="width:'+oEl.iconWidth+'px;" src="'+oEl.options[s][2]+'"/>');
            }
            this.aHTML.push('</td><td width="100%">');
            this.aHTML.push('<img style="float:right" onclick="Glizy.pageContent.delOption(event,\''+oEl.name+'\')" alt="'+pageTexts.del+'" src="'+Glizy.pageContent.iconPath+'icon_delete.gif" width="16" height="16" border="0"/>');
            if (oEl.options[s][3]) {
                this.aHTML.push('<img style="float:right;cursor:pointer" onclick="Glizy.pageContent.pagePreview(\''+oEl.options[s][3]+'\')" alt="'+pageTexts.preview+'" src="'+Glizy.pageContent.iconPath+'icon_preview.gif" width="16" height="16" border="0"/>');
            }
            this.aHTML.push('<span>'+oEl.options[s][0]+'</span>');
            this.aHTML.push('</td></tr>');
        }
        this.aHTML.push('</table>');
        this.aHTML.push('</div></td></tr></table>');
        break;
    case "select":
    case "multipleselect":
        this.aHTML.push('<select id="'+oEl.name+'"'+(oEl.target?' onchange="Glizy.pageContent.applyOpt(\''+oEl.target+'\',\''+name+'\',event)"':'')+(oEl.type=="multipleselect"?" multiple":"")+(oEl.size?" size='"+oEl.size+"'":"")+'>');
        oEl.value = "";
        if (oEl.options.length) {
            for (var i=0;i<oEl.options.length;i++) {
                this.aHTML.push('<option value="'+oEl.options[i][1]+'"'+(oEl.options[i][2]?' selected="selected"':'')+(oEl.options[i][4]?' disabled="disabled"':'')+'>'+oEl.options[i][0]+'</option>');
                if (oEl.options[i][2]) {
                    if (oEl.type=="select") {
                        oEl.value = oEl.options[i][1];
                    }
                    else {
                        oEl.value += (","+i);
                    }
                }
            }
        }
        else {
            for (var i in oEl.options) {
                this.aHTML.push('<option value="'+oEl.options[i][1]+'"'+(oEl.options[i][2]?' selected="selected"':'')+(oEl.options[i][4]?' disabled="disabled"':'')+'>'+oEl.options[i][0]+'</option>');
                if (oEl.options[i][2]) {
                    if (oEl.type=="select") {
                        oEl.value = oEl.options[i][1];
                    }
                    else {
                        oEl.value += ","+i;
                    }
                }
            }
        }
        this.aHTML.push('</select>');
        if (oEl.insertInto) {
            this.aHTML.push('<button onclick="Glizy.pageContent.insertTag(\''+oEl.name+'\',\''+oEl.insertInto+'\')">'+pageTexts.ins+'</button>');
        }
        if (oEl.target) {
            this.aApplyOpts.push(name);
        }
        break;
    case "colorPicker":
        this.aHTML.push("<table cellpadding='2' class='colorpicker'><tr><td style=\"width:14px;border:1px solid #000000;background-color:"+oEl.value+";padding:2px;cursor:pointer\" onmousedown=\"Glizy.pageContent.changeColor(event)\">&nbsp;</td><td width=\"16\"></td><td style=\"padding:0px;\"><input style=\"border:1px solid #CCCCCC;font-family:monospace;width:60px;margin:0px\" id=\""+oEl.name+"\" value=\""+oEl.value+"\" onchange=\"Glizy.pageContent.chkColor(event)\"/></td><td width=\"16\"></td><td style=\"padding:0px;cursor:pointer\"><img src=\""+Glizy.pageContent.iconPath+"picker.gif\"/ onmousedown=\"Glizy.pageContent.changeColor(event)\"></td></tr></table>"); 
        break;
    case "repeater":
        this.aHTML.push("<fieldset class=\"repeater\">");
		if (oEl.title!='') this.aHTML.push("<legend>"+oEl.title+"</legend>");
        this.aHTML.push("<table width=\"100%\" class=\"list\">");
        if (!oEl.repeaterCmd) {
            var aBld = [];
            aBld.push("<img onclick=\"Glizy.pageContent.manageItem(event,'"+name+"',0)\" title=\""+pageTexts.addBefore+"\" src=\""+Glizy.pageContent.iconPath+"icon_add.gif\" width=\"16\" height=\"16\" border=\"0\"/>");
            aBld.push("<img onclick=\"Glizy.pageContent.manageItem(event,'"+name+"',1)\" title=\""+pageTexts.moveUp+"\" src=\""+Glizy.pageContent.iconPath+"icon_up.gif\" width=\"16\" height=\"16\" border=\"0\"/>");
            aBld.push("<img onclick=\"Glizy.pageContent.manageItem(event,'"+name+"',2)\" title=\""+pageTexts.moveDn+"\" src=\""+Glizy.pageContent.iconPath+"icon_down.gif\" width=\"16\" height=\"16\" border=\"0\"/>");
            aBld.push("<img onclick=\"Glizy.pageContent.manageItem(event,'"+name+"',3)\" title=\""+pageTexts.edit+"\" src=\""+Glizy.pageContent.iconPath+"icon_edit.gif\" width=\"16\" height=\"16\" border=\"0\"/>");
            aBld.push("<img onclick=\"if (confirm('"+pageTexts.confirmDel+"'))Glizy.pageContent.manageItem(event,'"+name+"',4)\" title=\""+pageTexts.del+"\" src=\""+Glizy.pageContent.iconPath+"icon_delete.gif\" width=\"16\" height=\"16\" border=\"0\"/>");
            oEl.repeaterCmd = aBld.join("");
        }
        for (var i=0;i<oEl.items.length;i++) {
            this.aHTML.push("<tr class=\""+(i%2?"odd":"even")+"\">");
            this.aHTML.push("<td title=\""+oEl.title+"\">");
            var n = 0;
            for (var o in oEl.items[i]) {
                n++;
                if (n==1) {
                    var el = o;
                }
            }
            var elName = name //+(i?"@"+i:"")+"-"+el
            if (n==1) {
               //if (oEl.items[i][el].type=="image") {
                    Glizy.pageContent.buildHTML(elName,oEl.items[i][el],true);
              //  }
             //  else {
              //      n = 2;
              //      oEl.emptyItem.type = "record";
              //      oEl.emptyItem.dummy = {type:"hidden",value:""};
              //      oEl.items[i].dummy = {type:"hidden",value:""};
              //  }
            }
            if (n>1) {
                with (oEl.items[i][el]) {
                    switch (type) {
                    case "date":
                    case "datetime":
                    case "media":
                    case "colorPicker":
                    case "text":
                        oEl.items[i].label = value;
                        break;
                    case "longtext":
                        oEl.items[i].label = value.substring(0,50)+(value.length>50?"...":"");
                        break;
                    case "image":
                        oEl.items[i].label = title;
                        break;
                    case "chekbox":
                        oEl.items[i].label = value?"true":"false";
                        break;
                    case "select":
                        for (var o=0;o<options.length;o++) {
                            if (options[o][2]) break;
                        }
                        if (o>=options.length) o = 0;
                        oEl.items[i].label = options[o][0];
                        break;
                    case "multipleselect":
                        var t = [];
                        for (var o=0;o<options.length;o++) {
                            if (options[o][2]) t.push(options[o][0]);
                        }
                        t = t.join(",");
                        oEl.items[i].label = t.substring(0,50)+t.length>50?"...":"";
                        break;
                    }
                }
                oEl.items[i].type = "record";
                oEl.items[i].repeaterName = elName+".items["+i+"]";
                Glizy.pageContent.buildHTML(elName,oEl.items[i],true);
            }
            this.aHTML.push("<td style=\"text-align: right\">");
            this.aHTML.push(oEl.repeaterCmd);
            this.aHTML.push("</td>");
			this.aHTML.push("</tr>");
        }
        this.aHTML.push("</table>");
		this.aHTML.push("<div class=\"formButtons\">");
		this.aHTML.push("<input type=\"button\" value=\""+pageTexts.addFirst+"\" class=\"button\" onClick=\"Glizy.pageContent.manageItem(event,'"+name+"',-1,null,0)\"/></td>");
		this.aHTML.push("<input type=\"button\" value=\""+pageTexts.addLast+"\" class=\"button\" onClick=\"Glizy.pageContent.manageItem(event,'"+name+"',-1,null,-1)\"/></td>");
		this.aHTML.push("</div>");
		// this.aHTML.push("<HR class=\"formRuler\" width=\"100%\">");
        this.aHTML.push("</fieldset>");
        break;
    case "record": 
        this.aHTML.push('<label for="'+name+'" style=\"cursor:pointer\" onmousedown = \"Glizy.pageContent.manageItem(event,\''+name+'\',3)\">'+oEl.label+'</label>');        
        this.aHTML.push('<table><tr><td style="border: 0; heigth: auto; padding: 0;" id="'+oEl.name+'"></td></tr></table>');
        break;
	case "static":
		// MODIFICA DANIELE
		this.aHTML.push(oEl.value);
		return;
	default:
		var comp = eval( "Glizy.pageContent_widgets."+oEl.type );
		if ( comp )
		{
			this.aHTML.push( new comp().init(oEl) );
		}
    }
	//this.aHTML.push('<br class="return" />');
    this.aHTML.push('</div>');
//    oEl.value = oEl.type=="image"?oEl.src:oEl.value;
    Glizy.pageContent.aSource.push(oEl);
}
Glizy.pageContent.aHtmlEditables = [];
Glizy.pageContent.aCalendarAttach = [];
Glizy.pageContent.aApplyOpts = [];
Glizy.pageContent.aSource = [];
Glizy.pageContent.clone = function(obj) {
    var newObj = obj.length>=0?[]:{};
    for (var i in obj) {
        if (typeof obj[i] != "object") {
            newObj[i] = obj[i];
        }
        else {
            newObj[i] = Glizy.pageContent.clone(obj[i]);
        }
    }
    return newObj;
}
Glizy.pageContent.manageItem = function(e,obj,op,isNew,newPos) {
    var addNew = function(pCrepeater,row,e,index) {
        var modalSim = Glizy.pageContent.modalSim || Glizy.pageContent.createElements("modalSim");
        modalSim.newRepeaterElement = [pCrepeater,row]
        var newObj = Glizy.pageContent.clone(pCrepeater.emptyItem);
        newObj.repeaterName = pCrepeater.objName+".items["+index+"]";
        newObj.name = "elID"+Glizy.pageContent.nF;
        var els = 0;
        var el;
        for (var x in newObj) {
            if (typeof newObj[x] == "object") {
                els++;
                el = el || newObj[x];
                newObj[x].name = "elID"+Glizy.pageContent.nF;
            }
        }
        if (els>1) {
            for (var x in newObj) {
                if (typeof newObj[x] == "object") {
                }
                newObj.label = newObj.label || newObj[x].value+" ";
                break;
            }
        }
        Glizy.pageContent.nF++;
        var cell = row.insertCell(-1);
        if (els==1 && el.type=='image')
		{
			cell.innerHTML = '<span><label></label>'+el.template.replace("##src##",el.src).replace("##title##",el.title).replace("></div"," onmousedown=\"Glizy.pageContent.changeImg(event,this,true)\" id=\""+el.name+"\"></div")+'</span>';
            with (Glizy.pageContent) {
                aSource.push(el);
                refine();
                changeImg(null,cell.getElementsByTagName("img")[0],true);
            }
        }
		else if (els==1 && el.type=='media')
		{
		    cell.innerHTML = '<span><label>'+el.label+'</label><input type="text" id="'+el.name+'" value="'+el.value+'" alt="" size="'+(el.size || 50)+'" readonly="readonly" style="cursor:pointer" onmousedown="Glizy.pageContent.changeMedia(event,this,false, \''+el.mediaType+'\')"/>'+'</span>';
			with (Glizy.pageContent) {
				aSource.push(el);
				refine();
				changeMedia(null,cell.getElementsByTagName("input")[0],false,el.mediaType);
			}
        }
        else
		{
            cell.innerHTML = '<span><label for="'+newObj.name+'" style=\"cursor:pointer\" onmousedown = \"Glizy.pageContent.manageItem(event,\''+pCrepeater.objName+'\',3)\">'+newObj.label+'</label><table><tr><td id="'+newObj.name+'"></td></tr></table>'+'</span>';
            Glizy.pageContent.manageItem(e,newObj,3,true);           
        }
        cell = row.insertCell(-1);
        cell.style.textAlign = "right";
        cell.innerHTML = pCrepeater.repeaterCmd;
		Glizy.tablerulerReset( row );
        return newObj;
    }
    var pCrepeater,repeaterItm;
    if (typeof obj == "object") {
        repeaterItm = obj;
        pCrepeater = obj.repeater;
    }
    else {
        pCrepeater = eval("Glizy.pageContent."+obj);
        pCrepeater.objName = obj;
    }
    obj = e?e.target||e.srcElement:document.getElementById(repeaterItm.name)
    if (op<0) {
        obj = obj.parentNode.previousSibling;
        obj = obj.insertRow(newPos);
        var newContent = addNew(pCrepeater,obj,null,newPos?pCrepeater.items.length:0);
        if (newPos) {
            pCrepeater.items.push(newContent);
        }
        else {
            pCrepeater.items.unshift(newContent);
        }
        Glizy.pageContent.refine();
    }
    else {
        var row = obj;
        while (row.tagName!="TR") {
            row = row.parentNode;
        }
        var i = 0;
        while (row.previousSibling) {
            i++;
            row = row.previousSibling;
        }
        repeaterItm = repeaterItm || pCrepeater.items[i];
        var els = 0;
        var elType = '';
        var elMediaType = '';
        for (var k in repeaterItm) {
            if (typeof repeaterItm[k] == "object") {
                var pCelement = repeaterItm[k];
				elType = repeaterItm[k].type;
				if ( repeaterItm[k].mediaType ) elMediaType = repeaterItm[k].mediaType;
                els++;
            }
        }
        switch (op) {
        case 0:
            var aL = [];
            for (var j=0;j<i;j++) {
                aL.push(pCrepeater.items[j]);
            }
            var o = obj.parentNode.parentNode;
            var iIndex = 0;
            while (o.previousSibling) {
                iIndex ++;
                o = o.previousSibling;
            }
            var row = o.parentNode.parentNode.insertRow(iIndex);
            aL.push(addNew(pCrepeater,row,els>1?e || event:null,aL.length));
            for (var j=i;j<pCrepeater.items.length;j++) {
                aL.push(pCrepeater.items[j]);
            }
            pCrepeater.items = aL;
            break;
        case 1:
            if (i>0) {
                var aL = [];
                for (var j=0;j<i-1;j++) {
                    aL.push(pCrepeater.items[j]);
                }
                aL.push(pCrepeater.items[i]);
                aL.push(pCrepeater.items[i-1]);
                for (var j=i+1;j<pCrepeater.items.length;j++) {
                    aL.push(pCrepeater.items[j]);
                }
                pCrepeater.items = aL;
                row = obj.parentNode.parentNode;
                var pRow = row.previousSibling;
                row.swapNode(pRow)
            }
            break;
        case 2:
            if (i<pCrepeater.items.length-1) {
                var aL = [];
                for (var j=0;j<i;j++) {
                    aL.push(pCrepeater.items[j]);
                }
                aL.push(pCrepeater.items[i+1]);
                aL.push(pCrepeater.items[i]);
                for (var j=i+2;j<pCrepeater.items.length;j++) {
                    aL.push(pCrepeater.items[j]);
                }
                pCrepeater.items = aL;
                row = obj.parentNode.parentNode;
                var nRow = row.nextSibling;
                nRow.swapNode(row)
            }
            break;
        case 3:
            if (els==1 && elType=='image') {
				Glizy.pageContent.changeImg(null,obj.parentNode.previousSibling.getElementsByTagName("img")[0],true);
            }
			else if (els==1 && elType=='media')
			{
				Glizy.pageContent.changeMedia(null,obj.parentNode.previousSibling.getElementsByTagName("input")[0],true,elMediaType)
			}
            else
			{
                Glizy.pageContent.popup(repeaterItm,isNew?repeaterItm:null);
            }
            break;
        case 4:
            var aL = [];
            for (var j=0;j<i;j++) {
                aL.push(pCrepeater.items[j]);
            }
            for (var j=i+1;j<pCrepeater.items.length;j++) {
                aL.push(pCrepeater.items[j]);
            }
            pCrepeater.items = aL;
            row.parentNode.parentNode.deleteRow(i);
            break;
        }
		
		if ( row ) Glizy.tablerulerReset( row );

    }
    if (op!=3) {
        for (var i=0;i<pCrepeater.items.length;i++) {
            for (o in pCrepeater.items[i]) {
                var obj = pCrepeater.items[i];
                if (obj.type!="record") obj = obj[o];
                var obj = document.getElementById(obj.name);
                while (obj.tagName!="TR") {
                    obj = obj.parentNode;
                }
                obj.className = i%2?"odd":"even"
                break;
            }

        }
    }
}
Glizy.pageContent.changeImg = function(e,src,loopItm) {
    var z = Glizy.pageContent.modal(true);
    var imgPicker = Glizy.pageContent.imgPicker || Glizy.pageContent.createElements("imgPicker");
    var obj = src || e.target || e.srcElement;
    with (imgPicker.style) {
        top = (document.documentElement.scrollTop+20)+"px";
        left = ((document.documentElement.clientWidth-imgPicker.style.pixelWidth)/2)+"px";
        zIndex = z*10+5+100;
        display = "block";
    }
    var value = "0";
    if (src.source) {
        value = src.source.value;
    }
    src.value = src.cur_id = value;
    imgPicker.ico = src;
    var label;
    if (loopItm) {
        label = src.parentNode.parentNode.getElementsByTagName("label")[0]
    }
    imgPicker.label = label
}
Glizy.pageContent.changeMedia = function(e,src,loopItm,mediaType) {
	Glizy.pageContent.mediaSrc = Glizy.pageContent.origMediaSrc+"&mediaType="+mediaType;
    var z = Glizy.pageContent.modal(true)
    var mediaPicker = Glizy.pageContent.mediaPicker || Glizy.pageContent.createElements("mediaPicker"); 
	if ( mediaPicker.content.src != Glizy.pageContent.mediaSrc )
	{
		mediaPicker.content.src = Glizy.pageContent.mediaSrc;
	}
    var obj = src || e.target || e.srcElement;
    with (mediaPicker.style) {
        top = (document.documentElement.scrollTop+20)+"px";
        left = ((document.documentElement.clientWidth-mediaPicker.style.pixelWidth)/2)+"px";
        zIndex = z*10+5+100;
        display = "block";
    }
    var label;
    var value = "0";
    if (src.source) {
        value = src.source.media_id;
    }
    src.media_id = src.cur_id = value;
    mediaPicker.fld = src;
    if (loopItm) {
        label = src.parentNode.parentNode.getElementsByTagName("label")[0];
    }
    mediaPicker.label = label
}
Glizy.pageContent.changeColor = function(e,src) {
    var obj = src || e.target || e.srcElement;
    while (obj.tagName!="TR") {
        obj = obj.parentNode;
    }
    var z = Glizy.pageContent.modal(true)
    var colorPicker = Glizy.pageContent.colorPicker || Glizy.pageContent.createElements("colorPicker");
    with (colorPicker.style) {
        top = (document.documentElement.scrollTop+e.clientY)+"px";
        zIndex = z*10+5+100;
        left = (document.documentElement.scrollLeft+e.clientX)+"px";
        display = "block";
    }
    colorPicker.text = obj.getElementsByTagName("input")[0];
    var color = colorPicker.text.value;
    colorPicker.setValue(color);
    colorPicker.sample = obj.cells[0];
}
Glizy.pageContent.chkColor = function(e) {
    var obj = e.target || e.srcElement;
	var sample = obj.parentNode.parentNode.firstChild;
    var color = obj.value.toLowerCase();
    if (color.substring(0,1)!="#") {
        color = "#"+color;
    }
    if (color.length!=7) {
        obj.value = sample.style.backgroundColor;
    }
    else {
        var pattern = "0123456789abcdef";
        for (var i=1;i<7;i++) {
            if (pattern.indexOf(color.substring(i,i+1))<0) {
                obj.value = sample.style.backgroundColor;
                return;
            }
        }
        obj.value = color;
        sample.style.backgroundColor = color;
    }
}
Glizy.pageContent.createElements = function(el,reference,newObj) {
    var container = function(l,w,h,title,id) {
        var obj = document.createElement("div");
        obj.className = "popupContainer";
       	if( id) obj.id = "popupContainer_"+id;
        with (obj.style) {
            left = l+"px";
            width = w+"px";
            height = h+"px";
        }
        var header = obj.header = document.createElement("div");
        obj.appendChild(header);
        header.className = "popupHeader";
        header.onmousedown = function(e) {
            e = e || event;
            Glizy.pageContent.modalInfo = {x:e.screenX,y:e.screenY,o:obj};
            document.onmousemove = function(e) {
                e = e || event;
                with (Glizy.pageContent.modalInfo) {
                    o.style.top = (o.offsetTop+e.screenY-y)+"px";
                    o.style.left = (o.offsetLeft+e.screenX-x)+"px";
                    x = e.screenX;
                    y = e.screenY;
                }
                return false;
            }
            document.onmouseup = function(e) {
                document.onmousemove = null;
                if (Glizy.pageContent.modalInfo.o.header.contentFrame) {
                    Glizy.pageContent.modalInfo.o.header.contentFrame.contentWindow.document.onmousemove = null;
                }
                if (Glizy.pageContent.modalInfo.o.header.contentFrame) {
                    Glizy.pageContent.modalInfo.o.header.contentTopper.style.visibility = "hidden";
                }
            }
            if (this.contentFrame) {
                this.contentTopper.style.visibility = "visible";
            }
            /*
            if (Glizy.pageContent.b==2 && this.contentFrame) {
                this.contentFrame.contentWindow.document.onmousemove = function(e) {
                    with (Glizy.pageContent.modalInfo) {
                        o.style.top = (o.offsetTop+e.screenY-y)+"px";
                        o.style.left = (o.offsetLeft+e.screenX-x)+"px";
                        x = e.screenX;
                        y = e.screenY;
                    }
                    return false;
                }
                this.contentFrame.contentWindow.document.onmouseup = function() {
                    document.onmousemove = null;
                    this.contentFrame.contentWindow.document.onmousemove = null;
                }
                
            }
            */
        }
        var bt = document.createElement("img");
        bt.className = "popupClose";
        bt.src = Glizy.pageContent.iconPath+"close_button.gif";
        bt.obj = obj;
        bt.onmousedown = function() {
            this.obj.style.display = "none";
            Glizy.pageContent.modal(false,true);
        }
        header.appendChild(bt);
        var txt = document.createElement("span");
        txt.innerHTML = title;
        header.appendChild(txt);
        var footer = obj.footer = document.createElement("div");
        footer.className = "popupFooter";
        obj.appendChild(footer);
        return obj;
    }
    switch (el) {
    case "modalSim":
        var obj = Glizy.pageContent.modalSim = document.createElement("div");
        obj.className = "modalSimulator";
        var cont = document.getElementById("container");
        obj.stack = [];
        obj.resize = function(e) {
            with (Glizy.pageContent.modalSim.style) {
                width = document.documentElement.scrollWidth+"px";
                height = document.documentElement.scrollHeight+"px";
            }
        }
        if (window.addEventListener){
            addEventListener("resize",Glizy.pageContent.modalSim.resize,false);
        } else if (window.attachEvent){
            attachEvent("onresize",Glizy.pageContent.modalSim.resize);
        }
        break;
    case "imgPicker":
    case "mediaPicker":
        var obj = container(150,900,550,el=="imgPicker"?pageTexts.newImg:pageTexts.newMedia);
        if (el=="imgPicker") {
            Glizy.pageContent.imgPicker = obj;
        }
        else {
            Glizy.pageContent.mediaPicker = obj;
        }
        obj.content = obj.header.contentFrame = document.createElement("iframe");
        obj.content.frameBorder = "0";
        with (obj.content.style) {
            width = "100%";
            height = (parseFloat(obj.style.height)-20)+"px";
        }
        obj.appendChild(obj.content);
        obj.header.contentTopper = document.createElement("div");
        with (obj.header.contentTopper.style) {
            position = "absolute";
            top = "16px";
            left = "0px";
            width = "100%";
            height = obj.content.style.height;
            filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=0)";
            MozOpacity = opacity = 0;
            backgroundColor = "#FFFFFF";
            visibility = "hidden";
        }
        obj.appendChild(obj.header.contentTopper);
        if (el=="imgPicker") {
            obj.update = function(img_id,img_url,img_title,img_thumb) { 
                this.ico.src = img_thumb;
                this.ico.title = img_title;
                if (this.label) {
                    this.label.innerHTML = img_title;
                }
                this.ico.value = this.ico.source.value = img_id;
                this.style.display = "none";
                Glizy.pageContent.modal(false);
            }
        }
        else {
            obj.update = function(media_id,media_url,media_title) { 
                this.fld.value = media_title;
                this.fld.media_id = this.fld.source.media_id = media_id
                this.style.display = "none";
                Glizy.pageContent.modal(false);
            }
        }
        obj.content.src = el=="imgPicker"?Glizy.pageContent.imgSrc:Glizy.pageContent.mediaSrc;
        break;

    case "colorPicker":
        var cPslider = function(t,l,fc) {
            var obj = document.createElement("div");
            obj.fc = fc;
            with (obj.style) {
                position = "absolute";
                top = t+"px";
                left = l+"px";
                width = (259-2*Glizy.pageContent.b)+"px";
                height = "6px";
                border = "1px inset threedhighlight";
            }
            obj.onmousedown = function(e) {
                var obj = e?e.target:event.srcElement;
                obj.offsetParent.active = true;
            }
            for (var i=0;i<256;i++) {
                var x = document.createElement("DIV");
                with (x.style) {
                    position = "absolute";
                    left = i+"px";
                    width = "1px";
                    height = "6px";
                    overflow = "hidden";
                }
                x.sl = obj;
                x.value = i;
                x.onmousedown = function(e) {
                    var obj = e?e.target:event.srcElement;
                    Glizy.pageContent.CPon = true;
                    obj.sl.value = obj.value;
                    obj.sl.offsetParent.refresh();
                    obj.sl.onmouseout = function() {
                        Glizy.pageContent.CPon = false;
                    }
                }
                x.onmouseover = function(e) {
                    var obj = e?e.target:event.srcElement;
                    if (Glizy.pageContent.CPon) {
                        obj.sl.value = obj.value;
                        obj.sl.parentElement.refresh();
                    }
                }
                obj.appendChild(x)
            }
            obj.paint = function(a,k) {
                var str0 = "#";
                for (var i=0;i<k;i++) {
                    str0 += (a[i]<16?"0":"")+a[i].toString(16);
                }
                var str1 = "";
                for (var i=k+1;i<3;i++) {
                    str1 += (a[i]<16?"0":"")+a[i].toString(16);
                }
                for (var i=0;i<256;i++) {
                    this.childNodes[i].style.backgroundColor = str0+(i<16?"0":"")+i.toString(16)+str1;
                }
                this.value = a[k];
            }
            obj.value = 0;
            obj.cursor = document.createElement("div");
            obj.cursor.obj = obj;
            with (obj.cursor.style) {
                position = "absolute";
                backgroundImage = "url("+Glizy.pageContent.iconPath+"slider_cursor.gif)";
                top = (t+7)+"px";
                left = l+"px";
                height = "6px";
                width = "11px";
                overflow = "hidden";
            }
            obj.cursor.min = l-5;
            obj.cursor.max = l+250;
            obj.cursor.onmousedown = function(e) {
                obj = e?e.target:event.srcElement;
                e = e || event;
                Glizy.pageContent.modalInfo = {x:e.screenX,o:obj};
                document.onmousemove = function(e) {
                    e = e || event;
                    with (Glizy.pageContent.modalInfo) {
                        o.style.left = Math.min(Math.max(o.offsetLeft+e.screenX-x,o.min),o.max)+"px";
                        x = e.screenX;
                        o.result.style.backgroundColor = o.obj.childNodes[o.offsetLeft-o.min].style.backgroundColor;
                    }
                    return false;
                }
                document.onmouseup = function() {
                    document.onmousemove = null;
                    with (Glizy.pageContent.modalInfo) {
                        var code = o.obj.childNodes[o.offsetLeft-o.min].style.backgroundColor;
                        if (Glizy.pageContent.b==2) {
                            code = code.replace("rgb(","[");
                            code = code.replace(")","]");
                            var rgb = eval(code);
                            code = "#"+(rgb[0]<16?"0":"")+rgb[0].toString(16)+(rgb[1]<16?"0":"")+rgb[1].toString(16)+(rgb[2]<16?"0":"")+rgb[2].toString(16);
                        }
                        o.picker.setValue(code);
                    }
                }
            }
            return obj;
        }
        var cRainbow = function() {
            var obj = document.createElement("img");
            obj.src = Glizy.pageContent.iconPath+"colorPicker.gif";
            with (obj.style) {
                position = "absolute";
                border = "1px inset threedhighlight";
            }
            var c = [0,1,2,3,4,5,6,7,8,9,"a","b","c","d","e","f"];
            var basic = [[15,15,15,0,0,0,15],[0,15,0,15,15,15,0],[0,0,15,15,15,0,0]];
            var palette = [];
            var p = [];
            for (var i=0;i<31;i++) {
                palette[i] = [];
            }
            for (var i=0;i<6;i++) {
                var cur = [];
                for (var j=0;j<3;j++) {
                    cur.push(basic[j][i])
                }
                var next = [];
                for (var j=0;j<3;j++) {
                    next.push(basic[j][i+1])
                }
                var d = [];
                for (var j=0;j<3;j++) {
                    d[j] = cur[j]>next[j]?-1:cur[j]<next[j]?1:0;
                }
                while (cur[0]!=next[0] || cur[1]!=next[1] || cur[2]!=next[2]) {
                    p.push([cur[0],cur[1],cur[2]]);
                    palette[14].push("#"+c[cur[0]]+c[cur[0]]+c[cur[1]]+c[cur[1]]+c[cur[2]]+c[cur[2]]);
                    for (var j=0;j<3;j++) {
                        if (cur[j]!=next[j]) {
                            cur[j]+=d[j] //*2;
                        }
                    }
                }
            }
            for (var i=0;i<p.length;i++) {
                cur = [15,15,15];
                for (var j=0;j<3;j++) {
                    d[j] = (p[i][j]-cur[j])/14;
                }
                for (var r=0;r<14;r++) {
                    palette[r][i] = "#"+c[Math.round(cur[0])]+c[Math.round(cur[0])]+c[Math.round(cur[1])]+c[Math.round(cur[1])]+c[Math.round(cur[2])]+c[Math.round(cur[2])];
                    for (var j=0;j<3;j++) {
                        cur[j]+=d[j];
                    }
                }
                cur = p[i];
                for (var j=0;j<3;j++) {
                    d[j] = -p[i][j]/15;
                }
                for (var r=15;r<31;r++) {
                    palette[r][i] = "#"+c[Math.round(cur[0])]+c[Math.round(cur[0])]+c[Math.round(cur[1])]+c[Math.round(cur[1])]+c[Math.round(cur[2])]+c[Math.round(cur[2])];
                    for (var j=0;j<3;j++) {
                        cur[j]+=d[j];
                    }
                }
            }
            var r = palette.length-2;
            var c = palette[0].length;
            with (obj.style) {
                width = (c+1)*4-2*Glizy.pageContent.b;
                height = r+2-2*Glizy.pageContent.b;
            }
            obj.palette = palette;
            obj.onmousedown = function(e) {
                if (e) {
                    var x = e.layerX;
                    var y = e.layerY;
                }
                else {
                    var x = event.offsetX;
                    var y = event.offsetY;
                }
                var obj = e?e.target:event.srcElement;
                var color = obj.palette[y][Math.floor(x/4)];
                obj.offsetParent.setValue(color);
            }
            return obj;
        }
        Glizy.pageContent.CPon = false;
        var obj = Glizy.pageContent.colorPicker = container(0,318-2*Glizy.pageContent.b,140-2*Glizy.pageContent.b,pageTexts.color);
        obj.footer.style.height = "24px";
        var bts = document.createElement("div");
        obj.footer.appendChild(bts);
        with (bts.style) {
            cssFloat = "right";
            paddingTop = "3px";
        }
        obj.result = document.createElement("div");
        obj.appendChild(obj.result);
        with (obj.result.style) {
            position = "absolute";
            top = "28px";
            left = "6px";
            width = "36px";
            height = "40px";
            border = "1px inset threedhighlight";
        }
        obj.close = document.createElement("button");
        obj.close.className = "popupButton";
        bts.appendChild(obj.close);
        obj.close.innerHTML = pageTexts.cancel;
        obj.close.style.marginRight = "10px";
        obj.close.obj = obj;
        obj.close.onmousedown = function() {
            this.obj.style.display = "none";
            Glizy.pageContent.modal(false,true);
        }
        obj.ok = document.createElement("button");
        obj.ok.className = "popupButton";
        bts.appendChild(obj.ok);
        obj.ok.innerHTML = pageTexts.ok;
        obj.ok.style.marginRight = "2px";
        obj.ok.sample = obj.result;
        obj.ok.onmousedown = function() {
            var code = this.sample.style.backgroundColor;
            if (Glizy.pageContent.b==2) {
                code = code.replace("rgb(","[");
                code = code.replace(")","]");
                var rgb = eval(code);
                code = "#"+(rgb[0]<16?"0":"")+rgb[0].toString(16)+(rgb[1]<16?"0":"")+rgb[1].toString(16)+(rgb[2]<16?"0":"")+rgb[2].toString(16);
            }
            this.sample.parentNode.text.value = code;
            this.sample.parentNode.sample.style.backgroundColor = this.sample.style.backgroundColor;
            Glizy.pageContent.modal(false);
            Glizy.pageContent.colorPicker.style.display = "none";
        }
        obj.r = cPslider(28,51,"r");
        obj.g = cPslider(44,51,"g");
        obj.b = cPslider(60,51,"b");
        obj.appendChild(obj.r);
        obj.appendChild(obj.g);
        obj.appendChild(obj.b);
        obj.appendChild(obj.r.cursor);
        obj.appendChild(obj.g.cursor);
        obj.appendChild(obj.b.cursor);
        obj.r.cursor.picker = obj;
        obj.g.cursor.picker = obj;
        obj.b.cursor.picker = obj;
        obj.r.cursor.result = obj.result;
        obj.g.cursor.result = obj.result;
        obj.b.cursor.result = obj.result;
        obj.rb = cRainbow();
        with (obj.rb.style) {
            top = "78px";
            left = "6px";
        }
        obj.appendChild(obj.rb);
        obj.hex = [];
        var str = "0123456789ABCDEF";
        for (var i=0;i<16;i++) {
            obj.hex[str.substring(i,i+1)] = i;
        }
        obj.setValue = function(rgb) {
            rgb = rgb || "#ffffff";
            this.result.style.backgroundColor = rgb;
            var r = this.htd(rgb.substring(1,3).toUpperCase());
            var g = this.htd(rgb.substring(3,5).toUpperCase());
            var b = this.htd(rgb.substring(5,7).toUpperCase());
            this.r.paint([r,g,b],0);
            this.g.paint([r,g,b],1);
            this.b.paint([r,g,b],2);
            this.r.cursor.style.left = (r+46)+"px";
            this.g.cursor.style.left = (g+46)+"px";
            this.b.cursor.style.left = (b+46)+"px";
        }
        obj.htd = function(str) {
            return this.hex[str.substring(0,1)]*16+this.hex[str.substring(1)]
        }
        obj.refresh = function() {
            var r = this.r.value;
            var g = this.g.value;
            var b = this.b.value;
            //this.r.paint([r,g,b],0);
            //this.g.paint([r,g,b],1);
            //this.b.paint([r,g,b],2);
            var str = "#"+(r<16?"0":"")+r.toString(16)+(g<16?"0":"")+g.toString(16)+(b<16?"0":"")+b.toString(16) 
            this.setValue(str); //this.result.style.backgroundColor = str;
        }
        break;
    case "popup":
        var element = document.getElementById(reference.name) || reference;
        while (element.tagName != "FIELDSET") {
            element = element.parentNode;
        }
        var popupHeight = document.documentElement.clientHeight-100;
        var obj = container(0,element.offsetWidth,popupHeight,"", reference.name);
        var l = 0;
        while (element && element!=document.body) {
            l += element.offsetLeft;
            element = element.offsetParent;
        }
        var objBg = obj.body = document.createElement("div");
        obj.appendChild(objBg);
        with (objBg.style) {
            height = (popupHeight-60)+"px";
            padding = "8px";
            overflow = "auto";
        }
        obj.style.left = l+"px";
        obj.footer.style.height = "24px";
        var bts = document.createElement("div");
        obj.footer.appendChild(bts);
        with (bts.style) {
            bottom = "0px";
            paddingTop = "3px";
            paddingLeft = "10px";
        }
        var bt = document.createElement("button");
        bt.className = "popupButton";
        bts.appendChild(bt);
        bt.innerHTML = pageTexts.save;
        bt.reference = reference;
        bt.onmousedown = function(e) {
       	 	// MODIFICA DANIELE
      		Glizy.hideMessage( Glizy.pageContent.$( '#popupContainer_'+reference.name+' div.message-box2' ) );
            var obj = e?e.target:event.srcElement;
            var aE = [];
            var label; 
            for (var o in obj.reference) {
                if (typeof obj.reference[o]=="object") {
                    var objO = obj.reference[o];
                    var objP = document.getElementById(objO.name);
                    if (objP) {
                        var lbl;
                        switch (objO.type) {
                        case "date":
                        case "datetime":
                        case "media":
                        case "colorPicker":
                        case "text":
							lbl = objP.value;
                            objO.value = objP.value;
                            break;
                        case "image":
                            lbl = objP.title;
                            objO.value = objP.value;
                            break;
                        case "longtext":
                            lbl = objP.value.substring(0,50)+(objP.value.length>50?"...":"");
                            objO.value = objP.value;
                            break;
                        case "chekbox":
                            lbl = objP.value?"true":"false";
                            objO.value = objP.value;
                            break;
                        case "select":
                            if (objP.selectedIndex>=0) {
                                lbl = objP.options[objP.selectedIndex].text;
                            }
                            objO.value = objP.value;
                            break;
                        case "multipleselect":
                            var t = [];
                            objO.value = "";
                            for (var o=0;o<objP.options.length;o++) {
                                if (objP.options[o].selected) {
                                    t.push(objP.options[o].text);
                                    objO.value += (","+o);
                                }
                            }
                            t = t.join(",");
                            lbl = t.substring(0,50)+t.length>50?"...":"";
                            break;
                        }
                        label = label || lbl;
					
						var skip = false;
						var obj2 = document.getElementById("s"+objO.name);
						if (obj2 && obj2.style.display=='none') skip = true;
                        if (objO.required && !skip) {
                            if (objO.required==1) {
                                if (!objP.value) {
                                    aE.push(objO.requiredMessage)
                                }
                            }
                            else {
                                var value = escape(objP.value);
								// MODIFICA DANIELE
                                var message = eval(objO.required+"(\""+value+"\",\""+objO.label+"\",\""+objO.type+"\",\""+objO.requiredMessage+"\")");
                                if (message) {
                                    aE.push(message);
                                }
                            }
                        }
                    }
                }
            }
            if (aE.length) {
//                alert(aE.join("\n"));
				// MODIFICA DANIELE
				Glizy.showWarningMessage(GlizyLocale.REQUIREDFIELDS, aE, Glizy.pageContent.$( '#popupContainer_'+reference.name+' div.message-box2' ) );
            }
            else {
                document.getElementById(obj.reference.name).parentNode.parentNode.parentNode.parentNode.parentNode.firstChild.firstChild.innerHTML = label;
                Glizy.pageContent.modal(false);
                (e?e.target:event.srcElement).parentNode.parentNode.parentNode.style.display = "none";
            }
        }
        var bt = document.createElement("button");
        bt.className = "popupButton";
        bts.appendChild(bt);
        bt.style.marginLeft = "10px";
        bt.innerHTML = pageTexts.cancel;
        bt.reference = reference;
        bt.onmousedown = function(e) {
            var obj = e?e.target:event.srcElement;
            var first = true;
            var valid = "";
            for (var oEl in obj.reference) {
                if (obj.reference[oEl]) {
                    if (first) {
                        valid = obj.reference[oEl].value;
                    }
                    var target = document.getElementById(obj.reference[oEl].name);
                    if (target) {
                        switch (obj.reference[oEl].type) {
                        case "image":
                            //target.src = obj.reference[oEl].value;
							//alert( obj.reference[oEl].img_id+" "+ obj.reference[oEl].value+" "+ obj.reference[oEl].cur_id)
                            target.src = obj.reference[oEl].src;
                            target.value = obj.reference[oEl].img_id;
                            break;
                        case "colorPicker":
                            target.value = obj.reference[oEl].value;
                            target.parentNode.previousSibling.previousSibling.style.backgroundColor = obj.reference[oEl].value;
                            break;
                        case "multipleselect":
                            for (var o=0;o<options.length;o++) {
                                target.options[o].selected = false;
                            }
                            var selected = obj.reference[oEl].value.split(",");
                            for (var o=0;o<selected.length;o++) {
                                target.options[selected[o]].selected = true;
                            }
                        default:
                            if (target.isRichText) {
								tinyMCE.activeEditor.setContent( jQuery("#"+target.id).val() );
                            }
                            target.value = obj.reference[oEl].value;
                            if (obj.reference[oEl].type=="media") {
                                target.media_id = target.cur_id;
                            }
                            break;
                        }
                    }
                    first = false;
                }
            }
            Glizy.pageContent.modal(false,true);
            (e?e.target:event.srcElement).parentNode.parentNode.parentNode.style.display = "none";
        }      
        Glizy.pageContent.aHTML = ['<div class="message-box2"></div>'];
        for (var oEl in reference) {
            if (typeof reference[oEl]=="object") {
                Glizy.pageContent.buildHTML((reference.repeaterName?reference.repeaterName+".":"")+oEl,reference[oEl])
            }
        }
        document.body.appendChild(obj);
        with (Glizy.pageContent) {
            objBg.innerHTML = aHTML.join("");
            /*
            while (true) {
                var editable = aHtmlEditables.pop();
                if (editable) {
                    var element_contenuto = tinyMCE._getElementById(editable);
                    if (element_contenuto) tinyMCE.addMCEControl(element_contenuto, editable);
                }
                else break;
            }
            */
        }
    }
    if (el!="popup") document.body.appendChild(obj);
    return obj;
}
Glizy.pageContent.modal = function(active,isNew) {
    modalSim = Glizy.pageContent.modalSim || Glizy.pageContent.createElements("modalSim");
    if (active) {
        with (modalSim.style) {
            width = document.body.scrollWidth+"px";
            height = Math.max(document.documentElement.clientHeight,document.body.scrollHeight)+"px";
            zIndex = modalSim.stack.length*10+100;
            display = "block";
        }
        var allS = [];
        for (var i=0;i<modalSim.stack.length;i++) {
            allS = allS.concat(modalSim.stack[i][0]);
        }
        var aS = Glizy.pageContent.aSelect = document.getElementsByTagName("select");
        var newS = [];
        for (var i=0;i<aS.length;i++) {
            var add = true;
            for (var j=0;j<allS.length;j++) {
                if (aS[i]==allS[j]) {
                    add = false;
                    break;
                }
            }
            if (add) {
                aS[i].style.visibility = "hidden";
                newS.push(aS[i]);
            }
        }
        modalSim.stack.push([newS,modalSim.newRepeaterElement]);
        modalSim.newRepeaterElement = null;
    }
    else {
        show = modalSim.stack.pop();
        if (show) {
            if (modalSim.stack.length) {
                modalSim.style.zIndex = modalSim.stack.length*10+100;
            }
            else {
                modalSim.style.display = "none";
            }
            modalSim.newRepeaterElement = show[1];
            show = show[0];
            for (var i=0;i<show.length;i++) {
                show[i].style.visibility = "inherit";
            }
            if (isNew) {
                if (modalSim.newRepeaterElement) {
                    var pCrepeater = modalSim.newRepeaterElement[0];
                    var row = modalSim.newRepeaterElement[1];
                    var tbl = row.parentNode.parentNode;
                    for (var i=0;i<tbl.rows.length;i++) {
                        if (tbl.rows[i]==row) {
                            break;
                        }
                    }
                    var aL = [];
                    for (var j=0;j<i;j++) {
                        aL.push(pCrepeater.items[j]);
                    }
                    for (var j=i+1;j<pCrepeater.items.length;j++) {
                        aL.push(pCrepeater.items[j]);
                    }
                    pCrepeater.items = aL;
                    tbl.deleteRow(i);
                    // newRepeaterElement = [pCrepeater,row]
                }
            }
        }
        modalSim.newRepeaterElement = null;
    }
    return modalSim.stack.length;
}
Glizy.pageContent.popup = function(element,newObj) {
    var z = Glizy.pageContent.modal(true)
    element.popup = element.popup || Glizy.pageContent.createElements("popup",element,newObj);
    setTimeout("Glizy.pageContent.refine()",50);
    with (element.popup.style) {
        top = (document.documentElement.scrollTop+50)+"px";
        zIndex = z*10+5+100;
        display = "block";
    }
    var sel = element.popup.getElementsByTagName("select");
    for (var i=0;i<sel.length;i++) {
        sel[i].style.visibility = "inherit";
    }
}
Glizy.pageContent.save = function(ask,test,param,resetSaved) {
    var key = Glizy.pageContent.lastKey;
    Glizy.pageContent.lastKey = -1;
    if (key==13) {
        return;
    }
    if (self.tinyMCE) {
        if (test) {
            try {
                //tinyMCE.triggerSave();
				for (var i = 0; i < tinyMCE.editors.length; i++ )
				{
				    tinyMCE.editors[ i ].save();
				}
				
            }
            catch(e) {
                setTimeout("Glizy.pageContent.save(false,true)",50);
                return;
            }
        }
        else {
            //tinyMCE.triggerSave();
			for (var i = 0; i < tinyMCE.editors.length; i++ )
			{
			    tinyMCE.editors[ i ].save();
			}
        }
    }
	// MODIFICA DANIELE
    var aS = [];
    var aA = [];
    var aE = [];
    for (var oEl in Glizy.pageContent) {
        if (typeof Glizy.pageContent[oEl] == "object" && typeof Glizy.pageContent[oEl].length != "number") {
            Glizy.pageContent.buildSave(oEl, Glizy.pageContent[oEl],aS,aA,aE);
        }
    }

	if (param)
	{
		aS.push(param);
	}
    var ok = aS.length; // MODIFICA DANIELE
    if (Glizy.pageContent.saved) {
        if (!test) {
	        ok = ok && Glizy.pageContent.saved != aS.join("&");
        }
    }
    else {
        ok = false;
    }
    if (ask && ok) {
        var ok = confirm(pageTexts.confirmSave);
    }
    if (ok) {
        Glizy.hideMessage();
        if (aS.length>0) {
            if (aE.length) {
                //alert(aE.join("\n"));
				// MODIFICA DANIELE
				Glizy.showWarningMessage(GlizyLocale.REQUIREDFIELDS, aE);
            }
            else {
                for (var i=0;i<aA.length;i++) {
                    aA[i][0].value = aA[i][1];
                }
				Glizy.pageContent.modal(true);
				var action = aS.join("&");
				action = action.split("+").join("%2B");
				jQuery.ajax( { url: Glizy.pageContent.action,
							type: 'POST',
							data: aS.join("&"),
							dataTypeString: 'json',
							success: Glizy.pageContent.formResponse
							});
           }
        }
        else {
//            ok = false;
            //alert(pageTexts.noSave)
        }
    }
//    if (!ok) {
	    var aS = [];
	    var aA = [];
	    var aE = [];
	    for (var oEl in Glizy.pageContent) {
	        if (typeof Glizy.pageContent[oEl] == "object" && typeof Glizy.pageContent[oEl].length != "number") {
	            Glizy.pageContent.buildSave(oEl,Glizy.pageContent[oEl],aS,aA,aE);
	        }
	    }
		Glizy.pageContent.saved = aS.join("&") || "empty";
//    }
	if ( resetSaved ) 
	{
		Glizy.pageContent.saved = "empty";
	}
}
Glizy.pageContent.buildSave = function(oEl,objO,aS,aA,aE,isLoop) {
	if (objO._obj)
	{
		var value = objO._obj.getValue() || "";
		if (objO.required)
		{
			var message = eval(objO.required+"(\""+escape(value)+"\",\""+(objO.label)+"\",\""+objO.type+"\",\""+escape(objO.requiredMessage)+"\")");
			if (message)
			{
				aE.push(message);
				return;
			}
		}
				
		aS.push(oEl+"="+escape(value)); 
        aA.push([objO,value]);
		return;
	}
	
    if (objO.name) {
	     var objP = document.getElementById(objO.name+(objO.type=="helpedselect"?"V":""));
		var skip = false;
		var obj2 = document.getElementById("s"+objO.name);
		if (obj2 && obj2.style.display=='none') skip = true;
		if (objO.required && !skip) {
            if (objO.required==1) {
                if (!objP.value) {
                    aE.push(objO.requiredMessage)
                }
            }
            else {
                var value = escape(objP.value) || "";
				// MODIFICA DANIELE
				var message = eval(objO.required+"(\""+value+"\",\""+objO.label+"\",\""+objO.type+"\",\""+objO.requiredMessage+"\")");
                if (message) {
                    aE.push(message);
                }
            }
        }
        switch (objO.type) {
        case "hidden":
            aS.push(oEl+"="+escape(objO.value));
            break;
        case "checkbox":
		case "radio":
            objP.value = objP.checked?1:0;
        case "text":
        case "longtext":
        case "colorPicker":
        case "select":
        case "date":
        case "datetime":
			var value = objP.value;
			if ( value == "" && objO['textIfEmpty'] && objO['textIfEmpty'] != "" )
			{
				value = objO['textIfEmpty'];
			}
	        aS.push(oEl+"="+escape(value)); 
	        aA.push([objO,value]);
            break;
        case "helpedselect":  
            var aMS = [];
            for (var k=1;k<objP.rows.length;k++) {
                aMS.push(objP.rows[k].getAttributeNode("value").value);
            }
            aS.push(oEl+"="+aMS.join(","));
            break;
        case "multipleselect":
            var aMS = [];
            for (var k=0;k<objP.options.length;k++) {
                if (objP.options[k].selected) {
                    aMS.push(objP.options[k].value);
                }
            }
            aS.push(oEl+"="+aMS.join(","));
            break;
        case "image":
            if (objP.value) {
                aS.push(oEl+"="+objP.value);
            }            
            break;
        case "media":
            if (objP.media_id) {
                aS.push(oEl+"="+objP.media_id);
            }
            break;
        case "repeater":
            aS.push(oEl+"="+objO.items.length);
            var aL = [];
            var toDo = [];
            for (var i=0;i<objO.items.length;i++) {
                var ok = false;
                for (var o in objO.items[i]) {
                    if (typeof objO.items[i][o] == "object") {
                        var el = document.getElementById(objO.items[i][o].name);
                        if (el) {
                            el.value = el.value || objO.items[i][o].value;
                            if (el.value) ok = true;
                            else {
                                var row = el.parentNode.parentNode.parentNode;
                                var rowIndex = 0;
                                while (row.previousSibling) {
                                    rowIndex++;
                                    row = row.previousSibling;
                                }
                                toDo.push([row.parentNode.parentNode,rowIndex]);
                            }
                        }
                        else {
                            ok = true;
                        }
                    }
                }
                if (ok) aL.push(objO.items[i]);
            }
            objO.items = aL;
			var IDs = new Array();
			for (var i=0;i<objO.items.length;i++)
			{
				if (objO.simpleMode)
				{
					oEl = objO.bindTo;
					if (objO.items[i][oEl].type=='image')
					{
						IDs.push(objO.items[i][oEl].value);
					}
					else
					{
						IDs.push(objO.items[i][oEl].media_id);
					}
				}
				else
				{
					for (var o in objO.items[i]) {
						if (typeof objO.items[i][o] == "object") {
							var name = oEl+(i?"@"+i:"")+"-"+o;
							var el = document.getElementById(objO.items[i][o].name);
							if (el) {
								el.value = el.value || objO.items[i][o].value;
							}
							
							Glizy.pageContent.buildSave(name,objO.items[i][o],aS,aA,aE,true);
						}
					}
				}
			}
			if (objO.simpleMode)
			{
				aS.push(objO.id+"="+IDs.join(','));
			}
            break;
        case "record":
            break;
        }
    }
    else {
        switch (objO.type) {
        case "hidden":
        case "checkbox":
		case "radio":
        case "text":
        case "longtext":
        case "colorPicker":
        case "date":
        case "datetime":
            aS.push(oEl+"="+escape(objO.value));
            break;
        case "select":
        case "multipleselect":
            if (objO.options.length) {
                var aMS = [];
                for (var k=0;k<objO.options.length;k++) {
                    if (objO.options[k][2]) {
                        aMS.push(objO.options[k][1]);
                    }
                }
                if (objO.type=="select") {
                    if (aMS.length) {
                        aS.push(oEl+"="+aMS[0]);
                    }
                    else {
                        aS.push(oEl+"="+objO.options[0][1]);
                    }
                }
                else {
                    aS.push(oEl+"="+aMS.join(","));
                }
            }
            break;
        case "image":
            aS.push(oEl+"="+objO.img_id);
            break;
        case "media":
            aS.push(oEl+"="+objO.media_id);
            break;
        }
    }
}
Glizy.pageContent.formResponse = function(response, response2) {
	Glizy.pageContent.modal(false);
	// console.log(response);
	// response = jQuery.parseJSON( response );
	// console.log(response);
	switch (response['status'])
	{
		case 'success':
			switch (response['action'])
			{
				case 'redirect':
					location.href = response['actionValue'];
					return;
					break;
				case 'setValues':
					for (a in response['actionValue'])
					{
						Glizy.pageContent[a]['value'] = response['actionValue'][a];
					}
                    var aS = [];
                    var aA = [];
                    var aE = [];
                    for (var oEl in Glizy.pageContent) {
                        if (typeof Glizy.pageContent[oEl] == "object" && typeof Glizy.pageContent[oEl].length != "number") {
                            Glizy.pageContent.buildSave(oEl,Glizy.pageContent[oEl],aS,aA,aE);
                        }
                    }
                    Glizy.pageContent.saved = aS.join("&") || "empty";
					break;
				case 'call':
					break;
			}
			//Glizy.pageContent.modal(false);
			Glizy.showSuccessMessage(GlizyLocale.SUCCESSMSG)
			break;
		case 'error':
			alert(response['message']);
			break;
	}
}
Glizy.pageContent.applyOpt = function(target,obj,e) {
    target = target.split(",");
    var index = (typeof e == "number"?e:(e.target||e.srcElement).selectedIndex);
    var obj = "Glizy.pageContent."+obj;
    var obj0 = eval(obj);
    obj = obj.substring(0,obj.lastIndexOf("."));
    var opts = obj0.options[index][3];
    if (!opts.length) return;
    var action = obj0.action || "value";
    for (var i=0;i<target.length;i++) {
        var obj1 = eval(obj+"."+target[i]);
        switch (action) {
        case "value":
            obj2 = document.getElementById(obj1.name);
            obj2.value = opts[i];
            if (obj1.type=="colorPicker") {
                obj2.parentNode.parentNode.cells[0].style.backgroundColor = opts[i];
                obj1.value = opts[i];
            }
            break;
        case "display": 
            obj2 = document.getElementById("s"+obj1.name);
            if (obj2) {obj2.style.display = opts[i]};
            break;
        }
    }
}
Glizy.pageContent.aId = [];
Glizy.pageContent.insertTag = function(what,where) {
    var tag = document.getElementById(what);
    var target = Glizy.pageContent.aId[where]+"_ifr";
	if ( tinyMCE.editors[ Glizy.pageContent.aId[where] ] )
	{
		tinyMCE.activeEditor.execCommand('mceFocus', false, target);
		tinyMCE.activeEditor.execCommand('mceInsertContent', true, tag.value);
	}
	else 
	{
		var targetField = document.getElementById(Glizy.pageContent.aId[where]);
		if (targetField) targetField.value = tag.value;
	}
}
Glizy.pageContent.refine = function() {
    for (var i in this) {
        if (this[i].name && this[i].type=="text") {
            var el = document.getElementById(this[i].name);
            el.value = el.value.replace(/\|\|/gi,'"');
        }
    }

    with (Glizy.pageContent) { 
        while (true) {
            var obj = aSource.pop();
            if (obj) {
                var obj1 = document.getElementById(obj.name);
                if (obj1) obj1.source = obj;
            }
            else break;
        }
        while (true) {
            var editable = aHtmlEditables.pop();
            if (editable && self.tinyMCE) {
                document.getElementById(editable).isRichText = true;
                var element_contenuto = document.getElementById(editable);
                if (element_contenuto) {
                    var nA = document.getElementsByTagName("A").length;
					var options = Glizy.tinyMCE_options;
					options.mode = "exact";
					options.elements = editable;
					tinyMCE.init( options );
                    for (var k in Glizy.pageContent.aId) {
                        if (Glizy.pageContent.aId[k]==editable) {
                            Glizy.pageContent.aId[k] = Glizy.pageContent.nE;
                            Glizy.pageContent.nE++
                        }
                    }
                    /*var aElements = document.getElementsByTagName("A");
                    for (var i=aElements.length-1;i>=nA;i--) {
                        if (aElements[i].href && aElements[i].href.indexOf("javascript:")==0) {
                            var href = aElements[i].href.substring(11);
                            var newHTML = "<span onmouseup=\""+href+"\">"+aElements[i].innerHTML+"</span>";
                            newHTML = newHTML.replace(/%20/gi,"");
                            aElements[i].outerHTML = newHTML;
                        }
                    }*/
                }
            }
            else break;
        }
        while (true) {
            var id = aCalendarAttach.pop();
            if (id) {
                Calendar.setup({
                    inputField     :    id[ 0 ],      // id of the input field
                    ifFormat       :    id[ 1 ] == "date" ? GlizyLocale.DATE_FORMAT : GlizyLocale.DATETIME_FORMAT,       // format of the input field
                    showsTime      :    id[ 1 ] != "date",            // will display a time selector
                    button         :    id[ 0 ]+"bt",   // trigger for the calendar (button ID)
                    singleClick    :    true,           // double-click mode
                    step           :    1,               // show all years in drop-down boxes (instead of every other year as default)
                    firstDay		:	1,
                    weekNumbers		:	false,
                    date			:	"",
                    showOthers		:	true
                });
            }
            else break;
        }
        while (true) {
            var obj = aApplyOpts.pop();
            if (obj) {
                var index = 0;
                var obj0 = eval("Glizy.pageContent."+obj);
                if (obj0) {
                    for (var i=0;i<obj0.options.length;i++) {
                        if (obj0.options[i][2]) {
                            index = i;
                            break
                        }
                    }
                    applyOpt(obj0.target,obj,index);
                }
            }
            else break;
        }

		for( var a in Glizy.pageContent_widgets._registredWidgets )
		{
			Glizy.pageContent_widgets.callMethod( a, 'refine' );
		}
    }
}
Glizy.pageContent.getThumb = function() {
}


Glizy.pageContent.helpedChange = function( sourceId, targetId ) {
	var source = Glizy.pageContent.$("#"+sourceId);
	var target = Glizy.pageContent.$("#"+targetId);
	if ( source.value )
	{
		target.disabled = "";
		
	}
	else
	{
		target.disabled = "disabled";
		target.value = "";
	}
}

Glizy.pageContent.getResult = function(id,iconWidth) {
    var obj = Glizy.pageContent[id];
    var url = obj.src;
    var ok = false;
    var source = Glizy.pageContent.$("#"+obj.name+"P0");
	var target = Glizy.pageContent.$("#"+obj.name+"S0");
	if ( source.value && target.value )
	{
		target.disabled = "";
		url += "&"+source.value+"="+target.value;
        ok = true;
    }

    if (ok ) {
        var target = Glizy.pageContent.$("#"+obj.name+"R");
        target.iconWidth = iconWidth;
        target.refresh = Glizy.pageContent.setResult;
		jQuery.ajax( { url: url,
			type: 'POST',
			dataTypeString: 'text',
			success: function( responseText )
			{
				target.refresh( responseText );
			}
			});
    }
}
Glizy.pageContent.setResult = function(text) {
    eval("var response = "+text+";");
    var aHTML = [];
    var rows = response.rows;
    if (rows.length) {
        aHTML.push('<table id="elID'+Glizy.pageContent.nF+'" style="table-layout:fixed;" cellSpacing="0">');
        for (var i=0;i<rows.length;i++) {
            aHTML.push('<tr class="'+(i%2?"odd":"even")+'" value="'+rows[i].v+'">');
            aHTML.push('<td width="'+this.iconWidth+'">');
            if (rows[i].ico) {
                aHTML.push('<img style="width:'+this.iconWidth+'px;" src="'+rows[i].ico+'"/>');
            }
            aHTML.push('</td><td><div style="width:100%">');
            aHTML.push('<img style="float:right;cursor:pointer" onclick="Glizy.pageContent.insOption(event,\''+this.id+'\')" alt="'+pageTexts.include+'" src="'+Glizy.pageContent.iconPath+'icon_add.gif" width="16" height="16" border="0"/>');
            if (rows[i].url) {
                aHTML.push('<img style="float:right;cursor:pointer" onclick="Glizy.pageContent.pagePreview(\''+rows[i].url+'\')" alt="'+pageTexts.preview+'" src="'+Glizy.pageContent.iconPath+'icon_preview.gif" width="16" height="16" border="0"/>');
            }
            aHTML.push('<span>'+rows[i].t+'</span>');
            aHTML.push('</div></td></tr>');
        }
        aHTML.push('</table>');
        setTimeout('Glizy.pageContent.$("#elID'+Glizy.pageContent.nF+'").style.width=Glizy.pageContent.$("#'+this.id+'").clientWidth+"px";Glizy.pageContent.$("#'+this.id+'").style.visibility=""',50);
        Glizy.pageContent.nF++;
    }
    else {
        aHTML.push(pageTexts.noResult);
    }
    this.style.visibility = "hidden";
    this.innerHTML = aHTML.join("");
}
Glizy.pageContent.insOption = function(e,id) {
    var obj = (e.target||event.srcElement);
    obj.src = Glizy.pageContent.iconPath+"icon_delete.gif";
    obj.alt = pageTexts.del;
    obj.onclick = Glizy.pageContent.delOption;
    while (obj.tagName!="TR") {
        obj = obj.parentNode;
    }
    var o = Glizy.pageContent.$("#"+id.replace("R","V"));
    for (var i=0;i<o.childNodes.length;i++) {
        if (o.childNodes[i].tagName=="TBODY") {
            var tb = o.childNodes[i];
            break
        }
    }
    tb.appendChild(obj); 
    tb.rows[0].style.display = "none";
    o.value = Glizy.pageContent.altRows(o);
    Glizy.pageContent.altRows(Glizy.pageContent.$("#"+id).firstChild);
    // _.resize()
}
Glizy.pageContent.delOption = function(e) {
    var obj = (e?(e.target||event.srcElement):event.srcElement);
    while (obj.tagName!="TR") {
        obj = obj.parentNode;
    }
    var o = obj;
    while (o.tagName!="TBODY") {
        o = o.parentNode;
    }
    if (o.rows.length==2) {
        o.rows[0].style.display = "";
    }
    o.removeChild(obj);
    o.parentNode.value = Glizy.pageContent.altRows(o.parentNode);
    // _.resize();
}
Glizy.pageContent.altRows = function(obj) {
    var aReturn = [];
    var k = 0;
    for (var i=0;i<obj.rows.length;i++) {
        if (obj.rows[i].className=="_ph") {
            k = 1;
        }
        else {
            obj.rows[i].className = (i+k)%2?"odd":"even";
            aReturn.push(obj.rows[i].value);
        }
    }
    return aReturn.join(",");
}
Glizy.pageContent.suggestFlt = function(obj) {
    var ids = obj.id.split("S");
    ids[1] = parseFloat(ids[1]);
    var aFlt,sB;
    for (var i=0;i<3;i++) {
        aFlt = [];
        for (var j=0;j<3;j++) {
            if (j!=i) {
                sB = Glizy.pageContent.$("#"+ids[0]+"S"+j);
                if (sB.value && sB.key) {
                    aFlt.push(sB.key+"="+sB.value+"");
                }
            }
        }
        sB = Glizy.pageContent.$("#"+ids[0]+"S"+i);
        sB._extra = sB._extra || sB.extra;
        sB.extra = aFlt.join("&");
        sB.extra += (sB.extra?"&":"")+sB._extra+"="+obj.key;
    }
}

Glizy.pageContent.pagePreview = function(id, draft, sessionprefix) 
{
	Glizy.closeWindows();
	Glizy.openWindow("../index.php?pageId="+id+"&draft="+draft+"&sespre="+sessionprefix,"preview","width=800,height=600,innerWidth=800,innerHeight=600,toolbar=yes,location=yes,menubar=yes,scrollbars=yes,resizable=yes");
}

Glizy.pageContent.addEvent = function(el, e,fn) {
    if(window.addEventListener){
        return el.addEventListener(e,fn,false);
    }
    else if(window.attachEvent){
        return el.attachEvent("on"+e,fn);
    }
}
Glizy.pageContent.removeEvent = function(el, e,fn) {
     if(window.addEventListener){
         return el.removeEventListener(e,fn,false);
     }
     else if(window.attachEvent){
         return el.detachEvent("on"+e,fn);
     }
}
Glizy.pageContent.changeVisibility = function( elements, state )
{
	jQuery( elements ).each( function( index, value ){
		if ( Glizy.pageContent.aId[ value ] )
		{
			jQuery( "#s"+Glizy.pageContent.aId[ value ] ).css( "display", state ? "inline" : "none" );
		}
	});
}
Glizy.pageContent.cancel = function()
{
	location.href = Glizy.pageContent.editRecord_resetUrl.value;
}

Glizy.pageContent.openModalPicker = function( url, title )
{
	if ( jQuery("#modalDiv").data( "isDialog" ) != "true" )
	{
		jQuery("#modalDiv").dialog({
				modal: true,
				autoOpen: false,
				draggable: true,
				resizeable: true
			    });
		jQuery("#modalDiv").data( "isDialog", "true" );
	}

	var w = Math.min( jQuery( window ).width() - 50, 900 );
	var h = jQuery( window ).height() - 50;
	$("#modalDiv").dialog( "option", { height: h, width: w, title: title } );
	$("#modalDiv").dialog( "open" );
	if ( $("#modalIFrame").attr( "src") != url )
	{
		$("#modalIFrame").attr( "src", url );
	}
}

Glizy.pageContent.closeModalPicker = function( url, title )
{
	$("#modalDiv").dialog( "close" );
}

for (var oEl in Glizy.pageContent) {
    if (Glizy.pageContent[oEl].type=="helpedselect") {
    }
    if (typeof Glizy.pageContent[oEl] == "object" && typeof Glizy.pageContent[oEl].length != "number") {
        Glizy.pageContent.buildHTML(oEl, Glizy.pageContent[oEl])
    }
}

Glizy.pageContent.aHTML.push( '<div id="modalDiv" style="display: none; margin: 0; padding: 0; overflow: hidden;"><iframe src="" id="modalIFrame" width="100%" height="100%" marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto" title="Seleziona Media"></iframe></div>' );


document.write(Glizy.pageContent.aHTML.join(""));
Glizy.pageContent.refine();

if ( Glizy.pageContent.enableFirstSave )
{
	setTimeout("Glizy.pageContent.save(false, true, null, true)",50);
}
else
{
	setTimeout("Glizy.pageContent.save(false, true )",50);
}

onbeforeunload = function() {
    Glizy.pageContent.save(true);
}
document.onkeydown = function() {
    if (self.event) {
        Glizy.pageContent.lastKey = event.keyCode;
    }
}