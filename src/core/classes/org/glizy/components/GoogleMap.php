<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_GoogleMap extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',				false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:required',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$this->_content = $this->_parent->loadContent($this->getId());
	}


	function render_html()
	{
		$this->_render_html();
		$this->addOutputCode($this->_content );
	}


	function getContent()
	{
		$this->_render_html();
		return $this->_content;
	}


	function _render_html()
	{
		if ( !empty( $this->_content ) )
		{
			if ( !org_glizy_ObjectValues::get( 'org.glizy.application', 'pdfMode' ) )
			{
				$this->_addJsCode();

				$id = $this->getOriginalId().'_initialize';
				$values = $this->_content;
				$this->_content = <<<EOD
<script type="text/javascript">
// <![CDATA[
function $id() {
	var pos = ("$values").split(",");
	if ( pos.length < 3 )
	{
		pos = [51.500152, -0.126236, 15];
	}
	var myLatlng = new google.maps.LatLng(pos[0], pos[1]);
	var myOptions = {
	  zoom: parseInt(pos[2]),
	  center: myLatlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	var marker = new google.maps.Marker({
	                    position: myLatlng,
	                    map: map
	                });
}
$(function() {
	$id();
});
// ]]>
</script>
<div id="map_canvas" style="min-height:300px"></div>
EOD;
			}
			else
			{
				list( $la, $lo, $z ) = explode( ',', $this->_content );
				$this->_content = '<img src="http://maps.googleapis.com/maps/api/staticmap?center='.$la.','.$lo.'&zoom='.$z.'&size=400x400&markers=color:red%7C'.$la.','.$lo.'" />';
			}

		}
	}

	function _addJsCode()
	{
		if (!org_glizy_ObjectValues::get('org.glizy.googleMap', 'add', false))
		{
			$siteProp = $this->_application->getSiteProperty();
			$apiKey = @$siteProp['googleMapsApiKey'] ?: __Config::get('glizy.maps.google.apiKey');

			if (!$apiKey) {
				//TODO: far presente all'utente che manca l'APIKey, in qualche modo
				$this->logAndMessage("È necessario che ci sia impostata una APIKey per la libreria GoogleMaps, o nel config del sistema (.xml) o nelle proprietà del sito in questione!", '', GLZ_LOG_WARNING);
			}
			$rootComponent = $this->getRootComponent();
			$rootComponent->addOutputCode(org_glizy_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?key='.$apiKey ), 'head');
		}
	}

	public static function translateForMode_edit($node) {

        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['data'] = 'type=googlemaps';
        $attributes['xmlns:glz'] = "http://www.glizy.org/dtd/1.0/";

        return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
    }
}