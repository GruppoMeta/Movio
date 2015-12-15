Glizy.oop.declare("glizy.FormEdit.googlemaps", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    map: null,
    marker: null,
    geocoder: null,
    elementMap: null,
    
    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        this.render();
    },
    
    getValue: function () {
        return this.$element.val();
    },
    
    setValue: function (value) {
        this.$element.val(value);
    },
    
    getName: function () {
        return this.$element.attr('name');
    },
    
    focus: function()
    {
        this.$element.focus();
    },
    
    render: function() {
        if (this.$element.data('isInit')!==true) {
            var name = this.$element.attr('name'),
            html = '<input id="'+name+'-search" class="btn" type="button" value="Cerca"/>';
            this.$element.after(jQuery(html));
            this.$element.addClass("span10");
            this.$element.data('isInit', true);
        }

        if (jQuery("#GlizyFormEditgooglemaps").length == 0) {
            window.GlizyFormEditgooglemaps = {};
            html = '<div id="GlizyFormEditgooglemaps" class="mapPicker" style="width: 600px; height: 400px; background: #fff; border: 1px solid #ccc; padding: 5px; position: absolute; z-index: 3000; display: none;"></div>';
            jQuery('body').append(html);

            window.GlizyFormEditgooglemaps.geocoder = new google.maps.Geocoder();

            var pos = this.getDefaultCurrentPosition();
            var latlng = new google.maps.LatLng(pos[ 0 ], pos[ 1 ]);
            var myOptions = {
                            zoom: pos[ 2 ],
                            center: latlng,
                            mapTypeId: google.maps.MapTypeId.HYBRID,
                            mapTypeControl: false,
                            disableDoubleClickZoom: true,
                            streetViewControl: false
                        };

            window.GlizyFormEditgooglemaps.map = new google.maps.Map( jQuery("#GlizyFormEditgooglemaps").get(0), myOptions);
            window.GlizyFormEditgooglemaps.marker = new google.maps.Marker({
                                position: latlng,
                                map: window.GlizyFormEditgooglemaps.map,
                                title: "Trascinami",
                                draggable: true
                            });
        }

        var self = this;
        this.map = window.GlizyFormEditgooglemaps.map;
        this.marker = window.GlizyFormEditgooglemaps.marker;
        this.geocoder = window.GlizyFormEditgooglemaps.geocoder;
        this.elementMap = jQuery("#GlizyFormEditgooglemaps");

        this.elementMap.click(function( e ) {
            if ( e.stopPropagation ) {
                e.stopPropagation();
            }
            e.cancelBubble = true;
        } );

        this.$element.click(function( e ) {
            if ( e.stopPropagation ) {
                e.stopPropagation();
            }
            e.cancelBubble = true;
        } );

        jQuery(document).click( function( e ) {
            self.closeMap();
        } );


        this.$element.next().click( function( e ) {
            if ( e.stopPropagation ) {
                e.stopPropagation();
            }
            e.cancelBubble = true;
            self.search();
        } );
    },
    
    trim: function (str)
    {
       var str = str.replace(/^\s\s*/, ''),
                ws = /\s/,
                i = str.length;
        while (ws.test(str.charAt(--i)));
        return str.slice(0, i + 1);
    },

    roundDecimal: function( num, decimals )
    {
        var mag = Math.pow(10, decimals);
        return Math.round(num * mag)/mag;
    },

    getDefaultCurrentPosition: function()
    {
        var posStr = this.$element.val();
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
    },

    getCurrentPosition: function()
    {
        var pos = this.getDefaultCurrentPosition();
        var latlng = new google.maps.LatLng(pos[ 0 ], pos[ 1 ]);
        this.setPosition(latlng);
    },

    setPosition: function(latLng, viewport)
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
        this.$element.val(lat + "," + lng+","+zoom);
        // this.setValue( lat + "," + lng+","+zoom );
    },

    setPositionValues: function()
    {
        if( this.elementMap.css("display") != "none")
        {
            var pos = this.getDefaultCurrentPosition();
            pos[ 2 ] = this.map.getZoom();
            this.setValue( pos.join( "," ) );
        }
    },

    isLngLat: function (val)
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
    },

    openMap: function()
    {
        var self = this;
        google.maps.event.clearListeners(this.map, 'dblclick');
        google.maps.event.clearListeners(this.map, 'idle');
        google.maps.event.clearListeners(this.marker, 'dragend');

        google.maps.event.addListener( this.map, 'dblclick', function(event) {
            self.setPosition( event.latLng );
        });

        google.maps.event.addListener( this.marker, 'dragend', function(event) {
            self.setPosition( self.marker.position );
        });

        google.maps.event.addListener( this.map, 'idle', function(event) {
            self.setPositionValues();
        });

        this.elementMap.css("left", this.$element.offset().left);
        this.elementMap.css("top", this.$element.offset().top);
        this.elementMap.css("width", this.$element.width());
        this.elementMap.css("display", "block");
        google.maps.event.trigger( this.map, 'resize');
        this.map.setCenter( this.marker.position );
    },

    closeMap: function()
    {
        google.maps.event.clearListeners(this.map, 'dblclick');
        google.maps.event.clearListeners(this.map, 'idle');
        google.maps.event.clearListeners(this.marker, 'dragend');
        this.elementMap.css("display", "none");
    },

    search: function()
    {
        this.findAddress();
    },

    findAddress: function()
    {
        console.log("findAddress")
        var self = this;
        var address = this.$element.val();
        if (address == ""){
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
    },
    
    destroy: function() {
    },
    
    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },
    
    addClass: function(className) {
        this.$element.addClass(className);
    },
    
    removeClass: function(className) {
        this.$element.removeClass(className);
    }
});
