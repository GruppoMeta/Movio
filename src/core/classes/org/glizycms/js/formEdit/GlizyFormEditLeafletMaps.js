jQuery.GlizyRegisterType('googlemaps', {

    __construct: function () {
        var self = this;
        self.element = $(this);

        self.render = function() {
            var self = this;
            if (this.element.data('isInit')!==true) {
                var name = this.element.attr('name'),
                html = jQuery('<input id="'+name+'-search" class="btn" type="button" value="'+GlizyLocale.GoogleMap.search+'"/>');
                this.element.after(html);
                this.element.addClass("span10");
                this.element.data('isInit', true);
            }

            if (jQuery("#GlizyFormEditgooglemaps").length == 0) {
                window.GlizyFormEditgooglemaps = {};
                html = '<div id="GlizyFormEditgooglemaps" class="mapPicker" style="width: 600px; height: 400px; background: #fff; border: 1px solid #ccc; padding: 5px; position: absolute; z-index: 3000; display: none; overflow: hidden"><div id="leafletmap" style="max-width: 100%; min-width: 471px; height: 400px;"></div></div>';
                jQuery('body').append(html);
            }

            this.elementMap = jQuery("#GlizyFormEditgooglemaps");

            this.elementMap.click(function( e ) {
                if ( e.stopPropagation ) {
                    e.stopPropagation();
                }
                e.cancelBubble = true;
            } );

            this.element.click(function( e ) {
                if ( e.stopPropagation ) {
                    e.stopPropagation();
                }
                e.cancelBubble = true;
            } );

            jQuery(document).click( function( e ) {
                self.closeMap();
            } );


            this.element.next().click( function( e ) {
                if ( e.stopPropagation ) {
                    e.stopPropagation();
                }
                e.cancelBubble = true;
                self.search();
            } );

            this.element.keyup( function( e ) {
                 var charCode = e.charCode || e.keyCode || e.which;
                if (charCode == 13){
                    if ( e.stopPropagation ) {
                        e.stopPropagation();
                    }
                    e.cancelBubble = true;
                    self.search();
                }
            } );

        },

        self.trim = function (str)
        {
            var str = str.replace(/^\s\s*/, ''),
                    ws = /\s/,
                    i = str.length;
            while (ws.test(str.charAt(--i)));
            return str.slice(0, i + 1);
        },

        self.roundDecimal = function( num, decimals )
        {
            var mag = Math.pow(10, decimals);
            return Math.round(num * mag)/mag;
        },

        self.getDefaultCurrentPosition = function()
        {
            var posStr = this.element.val();
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

        self.getCurrentPosition = function()
        {
            var pos = this.getDefaultCurrentPosition();
            this.setPosition(pos[0], pos[1]);
        },

        self.setPosition = function(lat, lng)
        {
            var lat = this.roundDecimal( lat, 6 );
            var lng = this.roundDecimal( lng, 6 );

            this.marker.setLatLng(L.latLng(lat, lng));

            var zoom = this.map.getZoom();
            this.map.setView(this.marker.getLatLng(), zoom);
            this.element.val(lat + "," + lng+","+zoom);
        },

        self.setPositionValues = function()
        {
            if( this.elementMap.css("display") != "none")
            {
                var pos = this.getDefaultCurrentPosition();

                pos[ 2 ] = this.map.getZoom();
                this.element.val( pos.join( "," ) );
            }
        },

        self.isLngLat = function (val)
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

        self.openMap = function()
        {
            this.elementMap.css("left", this.element.offset().left);
            this.elementMap.css("top", this.element.offset().top);
            this.elementMap.css("width", this.element.width());
            this.elementMap.css("display", "block");

            if (window.GlizyFormEditgooglemaps.map == undefined) {
                var pos = this.getDefaultCurrentPosition();

                window.GlizyFormEditgooglemaps.map = L.map('leafletmap', {
                    doubleClickZoom: false
                }).setView([pos[0], pos[1]], pos[2]);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(window.GlizyFormEditgooglemaps.map);

                window.GlizyFormEditgooglemaps.marker = L.marker([pos[0], pos[1]], {
                    draggable: true
                }).addTo(window.GlizyFormEditgooglemaps.map);

                this.map = window.GlizyFormEditgooglemaps.map;
                this.marker = window.GlizyFormEditgooglemaps.marker;

                var self = this;
                this.map.on('dblclick', function(e) {
                    self.setPosition(e.latlng.lat, e.latlng.lng);
                });

                this.marker.on('dragend', function(e) {
                    self.setPosition(window.GlizyFormEditgooglemaps.marker._latlng.lat, window.GlizyFormEditgooglemaps.marker._latlng.lng);
                });

                function myButton(){
                    var btn = $('<div class="close-button" style="z-index: 1000 !important; position: absolute; top: 0px; right: 0px">'+GlizyLocale.GoogleMap.close+'</div>');
                    btn.bind('click', function(){
                        self.setPositionValues();
                        self.closeMap();
                    });
                    return btn[0];
                }
                jQuery('#GlizyFormEditgooglemaps').append(myButton());
            }
            
            this.map.panTo(this.marker.getLatLng());
        },

        self.closeMap = function()
        {
            this.elementMap.css("display", "none");
            if (window.GlizyFormEditgooglemaps.map != undefined) {
                window.GlizyFormEditgooglemaps.map.remove();
                window.GlizyFormEditgooglemaps.map = undefined;
            }
        },

        self.search = function()
        {
            this.findAddress();
        },

        self.findAddress = function()
        {
            var self = this;
            var address = this.element.val();
            if(address == ""){
                alert(GlizyLocale.GoogleMap.error_1);
            }else{
                if(this.isLngLat(address)){
                    this.openMap();
                    self.getCurrentPosition();
                }else{
                    $.ajax({
                        url: 'https://nominatim.openstreetmap.org/search?q=' + address + '&format=json&limit=1',
                        success: function(result) {
                            if (result.length) {
                                self.openMap();
                                self.setPosition(
                                    result[0].lat,
                                    result[0].lon
                                );
                            } else {
                                alert(GlizyLocale.GoogleMap.error_1);
                            }
                        }
                    });
                }
                this.focus();
            }
        }

        self.render();

    },

    getValue: function () {
        return $(this).val();
    },

    setValue: function (value) {
        $(this).val(value);
    },

    destroy: function () {
    },



    focus: function()
    {
         this.focus();
    }
});
