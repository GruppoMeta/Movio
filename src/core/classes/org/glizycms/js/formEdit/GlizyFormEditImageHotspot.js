jQuery.GlizyRegisterType('imageHotspot', {
    // rendere gli hotspot modificabili: sia come dimensione che come posizione
    // togliere la checkbox disegna, si disegna sempre, in modalità edit non ci deve essere qtip ed il link
    // mettere quadrangolare / circle come costanti della classe
    // mettere i vari tipi come costanti della classe così da non dovver ripetere sempre la stessa stringa
    // anche cliccando sull'hotspot devi selezionarlo per modificarlo
    // impostare la larghezza  hotspots-container dopo che si è letta l'immagine altrimenti su finestra piccola l'immagine va accapo rispetto alla colonna degli hotspot
    //
    __construct: function () {
        var self = this;
        var el = $(this);
        el.removeAttr('value');
        el.hide();

        self.el = el;
        self.glizyOpt = el.data('glizyOpt');
        self.data = {};
        self.btnLoadImage = null;
        self.image = null;
        self.id = null;
        self.selected = -1;
        self.dragresize = null;
        self.dragres = false;
        self.init = function() {
            var hotspotsContainer = $('<div/>')
                .addClass('hotspots-container');
            var btnContainer = $('<div/>')
                .addClass('btn-container');
            var btnLoadImage = $('<button/>')
                .attr('class', 'js-loadImage')
                .text(GlizyLocale.HotSpot.loadImage)
                .click(function(evt) {
                    evt.preventDefault();
                    self.onLoadImageClick();
                });
            btnContainer.append(btnLoadImage);
            var btnAddTrack = $('<button/>')
                .css('margin-left', 5)
                .text(GlizyLocale.HotSpot.addPath)
                .click(function(evt) {
                    evt.preventDefault();
                    var id = self.calcId();
                    self.createDefault(id);
                    self.saveHotspot(id);
                    self.addTrack(id);
                    self.selectHotspot(id);

                    $("#hotspotsList").animate({ scrollTop: $('#hotspotsList')[0].scrollHeight}, 500);
                });
            btnContainer.append(btnAddTrack);
            hotspotsContainer.append(btnContainer);

            var hotspotsList = $('<div/>')
                .attr('id', 'hotspotsList')
                .addClass('hotspots-list');
            hotspotsContainer.append(hotspotsList);
            var hotspotsImage = $('<div/>')
                .attr('id', 'hotspots-image')
                .addClass('hotspots-image');
            var image = $('<img/>')
                .attr('id', 'image')
                .addClass('js-image')
                .attr('src', '')
                .css('display', 'none')
                .load({container: hotspotsContainer, list: hotspotsList}, function(e) {
                    var h = $(this).height() + 8;
                    contH = h + 45;
                    e.data.container.css('height', contH);
                    e.data.list.css('height', h);

                    var w = $(this).width() + 330;
                    e.data.container.css('width', w);
                    self.dragRes();
                });
            self.image = image;
            hotspotsImage.append(image);
            hotspotsImage.append($('<div/>').attr('id', 'selectionMarquee'));
            hotspotsContainer.append(hotspotsImage);

            el.parent().before(hotspotsContainer);

            /*var html = $('<div class="imageHotSpot">'+
                            '<button class="js-loadImage">Carica immagine</button>'+
                            '<button class="js-loadImage">Aggiungi tracciato</button>'+
                            '<img class="js-image" src="" style="display: none">'+
                            '</div>');*/

            /*el.parent().before(html);
            self.btnLoadImage = html.find('.js-loadImage').first();
            self.image = html.find('.js-image').first();

            self.btnLoadImage.click(function(evt){
                evt.preventDefault();
                self.onLoadImageClick();
            })*/

            // reimposta i valori
            var value = el.data('origValue');
            if (value) {
                // console.log(value)
                self.data = JSON.parse(value);
                self.setImage({id: self.data.image});
                self.loadHotspots();
            };
        };

        self.onLoadImageClick = function() {
            var url = self.glizyOpt.mediaPicker;
            url += '&mediaType=IMAGE';
            Glizy.openIFrameDialog(  GlizyLocale.MediaPicker.imageTitle,
                                    url,
                                    900,
                                    50,
                                    50,
                                    el.data('formEdit').openDialogCallback );
            Glizy.lastMediaPicker = self;
        };

        self.setImage = function(image) {
            if (image) {
                self.image.attr('src',
                        Glizy.tinyMCE_options.urls.imageResizer
                            .replace('#id#', image.id)
                            .replace('#w#', '')
                            .replace('#h#', '')
                    );
                self.image.show();

                self.data.image = image.id;
                self.storeValue();
            }
        };

        self.loadHotspots = function() {
            if(!self.data.hotspots)
                self.data.hotspots = [];
            for (var i=0; i<self.data.hotspots.length; i++) {
                self.addTrack(self.data.hotspots[i].id);
            };
        };

        self.createDefault = function(id) {
            self.hotspot = {
                id: id,
                top: 0,
                left: 0,
                height: 0,
                width: 0,
                form: 'rect',
                type: 'linkEx',
                description: '',
                src: '',
                srcInt: ''
            };
        };

        self.selectHotspot = function(id) {
            for (var i=0; i<self.data.hotspots.length; i++) {
                var ind = self.data.hotspots[i].id;
                $('#hotspot_'+ind).removeClass('selected');
                $('#hotspot'+ind).removeClass('selected');
                if(document.getElementById('hotspot'+ind));
                    self.dragresize.deselect(document.getElementById('hotspot'+ind));
            };
            $('#hotspot_'+id).addClass('selected');
            $('#hotspot'+id).addClass('selected');
            self.hotspot = _.findWhere(self.data.hotspots, {id: id});
            self.selected = id;
            self.printHotspot(id);
            if(document.getElementById('hotspot'+id))
                self.dragresize.select(document.getElementById('hotspot'+id));
        };

        self.addTrack = function(id) {
            self.hotspot = _.findWhere(self.data.hotspots, {id: id});
            var hotspotContainer = $('<div/>')
                .attr('id', 'hotspot_'+id)
                .addClass('hotspot')
                .click(function() {
                    self.selectHotspot(id);
                });
            var closeDiv = $('<div/>')
            .addClass('delete');
            var close = $('<a/>')
                .attr('href', '#')
                .text('X')
                .click(function(evt) {
                    evt.preventDefault();
                    self.removeHotspot(id);
                });
            closeDiv.append(close);
            hotspotContainer.append(closeDiv);

            var formDiv = $('<div/>')
                .addClass('list-container')
                .css('clear', 'both');
            var label = $('<label/>')
                .attr('for', 'form_'+id)
                .addClass('label')
                .addClass('list-label')
                .text(GlizyLocale.HotSpot.hotspotForm);
            formDiv.append(label);
            var form = $('<select/>')
                .attr('id', 'form_'+id)
                .addClass('list-select')
                .click(function() {
                    self.selectHotspot(id);
                })
                .change(function() {
                    self.changeForm($(this), id);
                });
            var opt = $('<option value="rect"/>')
                .text(GlizyLocale.HotSpot.formQuad);
            form.append(opt);
            opt = $('<option value="circle"/>')
                .text(GlizyLocale.HotSpot.formCircle);
            form.append(opt);
            formDiv.append(form);
            hotspotContainer.append(formDiv);
            form.val(self.hotspot.form);

            var typeDiv = $('<div/>')
                .addClass('list-container')
                .css('clear', 'both');
            var label = $('<label/>')
                .attr('for', 'type_'+id)
                .addClass('label')
                .addClass('list-label')
                .text(GlizyLocale.HotSpot.hotspotType);
            typeDiv.append(label);
            var type = $('<select/>')
                .attr('id', 'type_'+id)
                .addClass('list-select')
                .click(function() {
                    self.selectHotspot(id);
                })
                .change(function() {
                    self.changeType($(this), id);
                });
            opt = $('<option value="linkEx"/>')
                .text(GlizyLocale.HotSpot.linkEx);
            type.append(opt);
            opt = $('<option value="link"/>')
                .text(GlizyLocale.HotSpot.linkInt);
            type.append(opt);
            opt = $('<option value="tooltip"/>')
                .text(GlizyLocale.HotSpot.tooltip);
            type.append(opt);
            typeDiv.append(type);
            hotspotContainer.append(typeDiv);
            type.val(self.hotspot.type);

            var description = $('<textarea/>')
                .attr('id', 'description_'+id)
                .attr('placeholder', GlizyLocale.HotSpot.description)
                .addClass('list-description')
                .keyup(function() {
                    self.updateDescription($(this), id);
                });
            hotspotContainer.append(description);
            description.text(self.hotspot.description);
            $('#hotspotsList').append(hotspotContainer);

            var urlDiv = $('<div/>')
                .attr('id', 'urlDiv_'+id)
                .addClass('list-container')
                .css('clear', 'both');
            var label = $('<label/>')
                .attr('for', 'url_'+id)
                .addClass('label')
                .addClass('list-label')
                .text(GlizyLocale.HotSpot.urlEx);
            urlDiv.append(label);
            var url = $('<input/>')
                .attr('id', 'url_'+id)
                .attr('type', 'text')
                .addClass('list-url')
                .blur(function() {
                    self.updateUrl($(this), id);
                });
            urlDiv.append(url);
            hotspotContainer.append(urlDiv);
            url.val(self.hotspot.src);

            var urlIntDiv = $('<div/>')
                .attr('id', 'urlIntDiv_'+id)
                .addClass('list-container')
                .css('clear', 'both');
            var label = $('<label/>')
                .attr('for', 'urlInt_'+id)
                .addClass('label')
                .addClass('list-label')
                .text(GlizyLocale.HotSpot.urlInt);
            urlIntDiv.append(label);
            var urlInt = $('<input/>')
                .attr('id', 'urlInt_'+id)
                .attr('type', 'text')
                .addClass('list-url')
                .blur(function() {
                    self.updateUrlInt($(this), id);
                });
            urlIntDiv.append(urlInt);
            hotspotContainer.append(urlIntDiv);
            urlInt.val(self.hotspot.srcInt);


            urlInt.select2({
                placeholder: '',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: Glizy.ajaxUrl + "&controllerName=org.glizycms.contents.controllers.autocomplete.ajax.PagePicker",
                    dataType: 'json',
                    quietMillis: 100,
                    data: function(term) {
                        return {
                            term: term
                        };
                    },
                    results: function(data, page ) {
                        return { results: data }
                    }
                },
                formatResult: function(data) {
                    return data.text+'<br><small>'+data.path+'</small>';
                },
                formatSelection: function(data) {
                    this.element.val(data.id);
                    this.element.trigger("blur");
                    return data.text+' <small>'+data.path+'</small>';
                }
            });
            self.changeType(type, id);

            if (self.hotspot.srcInt) {
                    $.ajax({
                        url: Glizy.ajaxUrl + "&controllerName=org.glizycms.contents.controllers.autocomplete.ajax.PagePicker",
                        dataType: 'json',
                        data: {id: self.hotspot.srcInt},
                        success: function(data) {
                            var oldHotspot = self.hotspot;
                            self.hotspot = _.findWhere(self.data.hotspots, {id: id});
                            urlInt.select2('data', data[0]);
                            self.hotspot = oldHotspot;
                        }
                    });
            }

            if(self.hotspot.height != 0 && self.hotspot.width != 0) {
                self.drawHotspot(id);
            };
        };

        self.storeValue = function() {
            self.el.val(JSON.stringify(self.data));
        };

        self.calcId = function() {
            if(!self.data.hotspots)
                self.data.hotspots = [];
            var id = -1;
            for (var i=0; i<=self.data.hotspots.length; i++) {
                var ind = self.data.hotspots.indexOf(_.findWhere(self.data.hotspots, {id: i}));
                if(ind == -1) {
                    id = i;
                    i = self.data.hotspots.length;
                };
            };
            return id;
        };

        self.drawHotspot = function(id) {
            $('#hotspot'+id).remove();
            $selectedBox = $('<div/>')
                .attr('id', 'hotspot'+id)
                .addClass('selected-box')
                .addClass('drsElement')
                .addClass('drsMoveHandle')
                .mousedown(function() {
                    self.selectHotspot(id);
                });
            if(self.hotspot.form == 'circle') {
                $selectedBox.addClass('hotspot-circle');
            }
            else {
                $selectedBox.removeClass('hotspot-circle');
            };
            $selectedBox.hide();
            $('.hotspots-image').append($selectedBox);
            $selectedBox.css('top', self.hotspot.top)
                .css('left', self.hotspot.left)
                .css('height', self.hotspot.height)
                .css('width', self.hotspot.width)
                .css('margin', 4);
            $selectedBox.show();
            self.createHotspot(id);
        };

        self.printHotspot = function(id) {
            var $selectionMarquee = $('#selectionMarquee');
            $('#image').off('mousedown')
                .off('mouseup');
            $selectionMarquee.off('mouseup');

            var startX,
                startY,
                selectedBox;

            if($('#form_'+id).val() == 'circle') {
                $selectionMarquee.addClass('hotspot-circle');
            }
            else {
                $selectionMarquee.removeClass('hotspot-circle');
            };

            positionBox = function ($box, coordinates, save) {
                if(save) {
                    self.hotspot.top = coordinates.top;
                    self.hotspot.left = coordinates.left;
                    self.hotspot.height = coordinates.bottom - coordinates.top;
                    self.hotspot.width = coordinates.right - coordinates.left;
                };
                $box.css('top', coordinates.top)
                    .css('left', coordinates.left)
                    .css('height', coordinates.bottom - coordinates.top)
                    .css('width', coordinates.right - coordinates.left)
                    .css('margin', 4);
            },

            compareNumbers = function (a, b) {
                return a - b;
            },

            getBoxCoordinates = function (startX, startY, endX, endY) {
                var x = [startX, endX].sort(compareNumbers),
                    y = [startY, endY].sort(compareNumbers);

                return {
                    top: y[0],
                    left: x[0],
                    right: x[1],
                    bottom: y[1]
                };
            },

            trackMouse = function (event) {
                event.preventDefault();
                var endX = (event.clientX - $('#image').offset().left + window.pageXOffset);
                var endY = (event.clientY - $('#image').offset().top + window.pageYOffset);
                var position = getBoxCoordinates(startX, startY, endX, endY);
                positionBox($selectionMarquee, position);
            },

            mouseDown = function (event) {
                if(!self.dragres) {
                    event.preventDefault();
                    startX = (event.clientX - $('#image').offset().left + window.pageXOffset);
                    startY = (event.clientY - $('#image').offset().top + window.pageYOffset);
                    positionBox($selectionMarquee, getBoxCoordinates(startX, startY, startX, startY));
                    $selectionMarquee.show();
                    $(this).on('mousemove', trackMouse);
                };
            },

            mouseUp = function (event) {
                if(!self.dragres) {
                    var position,
                    $selectedBox;
                    $selectionMarquee.hide();
                    var endX = (event.clientX - $('#image').offset().left + window.pageXOffset);
                    var endY = (event.clientY - $('#image').offset().top + window.pageYOffset);

                    position = getBoxCoordinates(startX, startY, endX, endY);

                    if (position.left !== position.right && position.top !== position.bottom) {
                        $('#hotspot'+id).remove();
                        $selectedBox = $('<div/>')
                            .attr('id', 'hotspot'+id)
                            .addClass('selected-box')
                            .addClass('selected')
                            .addClass('drsElement')
                            .addClass('drsMoveHandle')
                            .mousedown(function() {
                                self.selectHotspot(id);
                            });

                        if($('#form_'+id).val() == 'circle') {
                            $selectedBox.addClass('hotspot-circle');
                        }
                        else {
                            $selectedBox.removeClass('hotspot-circle');
                        };

                        $selectedBox.hide();
                        $('.hotspots-image').append($selectedBox);

                        positionBox($selectedBox, position, true);
                        //self.dragresize.deselect(document.getElementById('hotspot'+id));
                        //self.dragresize.removeHandleSet(document.getElementById('hotspot'+id));
                        self.dragresize.select(document.getElementById('hotspot'+id));
                        $selectedBox.show();

                        selectedBox = position;

                        $('#image').off('mousemove', trackMouse);

                        //self.createHotspot(id);
                        self.saveHotspot(id);
                    };
                }
            };

            $('#image').on('mousedown', mouseDown);
            $selectionMarquee.on('mousemove', trackMouse);
            $('#image').on('mouseup', mouseUp);
            $selectionMarquee.on('mouseup', mouseUp);

            $('body').on('mouseup', function() {
                $selectionMarquee.hide();
            });
        };

        self.changeForm = function($form, id) {
            if($form.val() == 'circle') {
                $('#hotspot'+id).addClass('hotspot-circle');
                $('#selectionMarquee').addClass('hotspot-circle');
            }
            else {
                $('#hotspot'+id).removeClass('hotspot-circle');
                $('#selectionMarquee').removeClass('hotspot-circle');
            };
            self.hotspot.form = $form.val();
            self.saveHotspot(id);
        };

        self.changeType = function($type, id) {
            switch($type.val()) {
                case 'linkEx':
                    $('#urlIntDiv_'+id).hide();
                    $('#urlDiv_'+id).show();
                    self.hotspot.srcInt = '';
                    $('#urlInt_'+id).val('');
                    break;
                case 'link':
                    $('#urlDiv_'+id).hide();
                    $('#urlIntDiv_'+id).show();
                    self.hotspot.src = '';
                    $('#url_'+id).val('');
                    break;
                case 'tooltip':
                    $('#urlDiv_'+id).hide();
                    $('#urlIntDiv_'+id).hide();
                    self.hotspot.src = '';
                    $('#url_'+id).val('');
                    self.hotspot.srcInt = '';
                    $('#urlInt_'+id).val('');
                    break;
            }
            self.hotspot.type = $type.val();
            self.createHotspot(id);
            self.saveHotspot(id);
        };

        self.updateDescription = function($description, id) {
            self.hotspot.description = $description.val();
            // $('#hotspot'+id).qtip({
            //     content: self.hotspot.description,
            //     position: {
            //         my: 'bottom left',
            //         at: 'bottom left',
            //         target: 'mouse'
            //     }
            // });
            self.saveHotspot(id);
        };

        self.updateUrl = function($url, id) {
            self.hotspot.src = $url.val();
            var link = $('#hotspot'+id).children().get(0);
            if(link) {
                $(link).attr('href', $url.val());
            };
            self.saveHotspot(id);
        };

        self.updateUrlInt = function($urlInt, id) {
            self.hotspot.srcInt = $urlInt.val();
            var link = $('#hotspot'+id).children().get(0);
            if(link) {
                $(link).attr('href', $urlInt.val());
            };
            self.saveHotspot(id);
        };

        self.removeHotspot = function(id) {
            $('#hotspot'+id).remove();
            $('#hotspot_'+id).remove();
            var pos = self.data.hotspots.indexOf(_.findWhere(self.data.hotspots, {id: id}));
            if(pos != -1) {
                self.data.hotspots.splice(pos, 1);
            };
            if(self.selected != -1 && self.selected == id) {
                $('#image').off('mousedown')
                    .off('mouseup');
                self.selected = -1;
            };
            self.storeValue();
        };

        self.createHotspot = function(id) {
            $('#hotspot'+id).empty();
            // $('#hotspot'+id).qtip({
            //     content: self.hotspot.description,
            //     position: {
            //         my: 'bottom left',
            //         at: 'bottom left',
            //         target: 'mouse'
            //     }
            // });
            // switch($('#type_'+id).val()) {
            //     case 'linkEx':
            //         var link = $('<a/>')
            //             .attr('target', '_blank')
            //             .css('height', self.hotspot.height)
            //             .css('width', self.hotspot.width)
            //             .css('display', 'block');
            //             if(self.hotspot.src != '')
            //                 link.attr('href', self.hotspot.src);
            //         if($('#form_'+id).val() == 'circle') {
            //             link.addClass('hotspot-circle');
            //         }
            //         else {
            //             link.removeClass('hotspot-circle');
            //         };
            //         $('#hotspot'+id).append(link);
            //         break;
            //     case 'link':
            //         // DA FARE
            //         break;
            // };
        };

        self.saveHotspot = function(id) {
            var pos = self.data.hotspots.indexOf(_.findWhere(self.data.hotspots, {id: id}));
            if(pos != -1) {
                self.data.hotspots.splice(pos, 1, self.hotspot);
            }
            else {
                self.data.hotspots.push(self.hotspot)
            };

            self.storeValue();
        };

        self.dragRes = function() {
            self.dragresize = new DragResize('dragresize',
                {minWidth: 1, minHeight: 1, minLeft: 0, minTop: 0, maxLeft: $('#image').width(), maxTop: $('#image').height() });
            self.dragresize.isElement = function(elm) {
                if (elm.className && elm.className.indexOf('drsElement') > -1) return true;
            };
            self.dragresize.isHandle = function(elm) {
                if (elm.className && elm.className.indexOf('drsMoveHandle') > -1) return true;
            };
            self.dragresize.ondragfocus = function() {

            };
            self.dragresize.ondragstart = function(isResize) {
                self.dragres = true;
            };
            self.dragresize.ondragmove = function(isResize) {

            };
            self.dragresize.ondragend = function(isResize) {
                self.hotspot.top = parseInt($('#hotspot'+self.hotspot.id).css('top'));
                self.hotspot.left = parseInt($('#hotspot'+self.hotspot.id).css('left'));
                self.hotspot.height = $('#hotspot'+self.hotspot.id).height();
                self.hotspot.width = $('#hotspot'+self.hotspot.id).width();
                self.dragres = false;
                self.saveHotspot(self.hotspot.id);
            };
            self.dragresize.ondragblur = function() {

            };
            self.dragresize.apply(document.getElementById('hotspots-image'));
        };

        self.init();
    },

    getValue: function () {
        return $(this).val();
    },

    setValue: function (value) {
        $(this).val(value);
    },

    destroy: function () {
    },

    openDialogCallback: function() {
        var $frame = jQuery(this).children();
        $frame.load(function () {
            jQuery( "img.js-glizyMediaPicker", $frame.contents().get(0)).click( function(){
                var $img = jQuery( this );
                Glizy.lastMediaPicker.setImage($img.data("jsonmedia"));
                Glizy.closeIFrameDialog();
            });

            jQuery( ".js-glizycmsMediaPicker-noMedia", $frame.contents().get(0)).click( function(){
                Glizy.lastMediaPicker.setImage();
                Glizy.closeIFrameDialog();
            });
        });
    }
});


/*

DragResize v1.0
(c) 2005-2006 Angus Turnbull, TwinHelix Designs http://www.twinhelix.com

Licensed under the CC-GNU LGPL, version 2.1 or later:
http://creativecommons.org/licenses/LGPL/2.1/
This is distributed WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

*/


// Common API code.

if (typeof addEvent != 'function')
{
 var addEvent = function(o, t, f, l)
 {
  var d = 'addEventListener', n = 'on' + t, rO = o, rT = t, rF = f, rL = l;
  if (o[d] && !l) return o[d](t, f, false);
  if (!o._evts) o._evts = {};
  if (!o._evts[t])
  {
   o._evts[t] = o[n] ? { b: o[n] } : {};
   o[n] = new Function('e',
    'var r = true, o = this, a = o._evts["' + t + '"], i; for (i in a) {' +
     'o._f = a[i]; r = o._f(e||window.event) != false && r; o._f = null;' +
     '} return r');
   if (t != 'unload') addEvent(window, 'unload', function() {
    removeEvent(rO, rT, rF, rL);
   });
  }
  if (!f._i) f._i = addEvent._i++;
  o._evts[t][f._i] = f;
 };
 addEvent._i = 1;
 var removeEvent = function(o, t, f, l)
 {
  var d = 'removeEventListener';
  if (o[d] && !l) return o[d](t, f, false);
  if (o._evts && o._evts[t] && f._i) delete o._evts[t][f._i];
 };
}


function cancelEvent(e, c)
{
 e.returnValue = false;
 if (e.preventDefault) e.preventDefault();
 if (c)
 {
  e.cancelBubble = true;
  if (e.stopPropagation) e.stopPropagation();
 }
};







// *** DRAG/RESIZE CODE ***

function DragResize(myName, config)
{
 var props = {
  myName: myName,                  // Name of the object.
  enabled: true,                   // Global toggle of drag/resize.
  handles: ['tl', 'tm', 'tr',
   'ml', 'mr', 'bl', 'bm', 'br'], // Array of drag handles: top/mid/bot/right.
  isElement: null,                 // Function ref to test for an element.
  isHandle: null,                  // Function ref to test for move handle.
  element: null,                   // The currently selected element.
  handle: null,                  // Active handle reference of the element.
  minWidth: 10, minHeight: 10,     // Minimum pixel size of elements.
  minLeft: 0, maxLeft: 9999,       // Bounding box area, in pixels.
  minTop: 0, maxTop: 9999,
  zIndex: 1,                       // The highest Z-Index yet allocated.
  mouseX: 0, mouseY: 0,            // Current mouse position, recorded live.
  lastMouseX: 0, lastMouseY: 0,    // Last processed mouse positions.
  mOffX: 0, mOffY: 0,              // A known offset between position & mouse.
  elmX: 0, elmY: 0,                // Element position.
  elmW: 0, elmH: 0,                // Element size.
  allowBlur: true,                 // Whether to allow automatic blur onclick.
  ondragfocus: null,               // Event handler functions.
  ondragstart: null,
  ondragmove: null,
  ondragend: null,
  ondragblur: null
 };

 for (var p in props)
  this[p] = (typeof config[p] == 'undefined') ? props[p] : config[p];
};


DragResize.prototype.apply = function(node)
{
 // Adds object event handlers to the specified DOM node.

 var obj = this;
 addEvent(node, 'mousedown', function(e) { obj.mouseDown(e) } );
 addEvent(node, 'mousemove', function(e) { obj.mouseMove(e) } );
 addEvent(node, 'mouseup', function(e) { obj.mouseUp(e) } );
};


DragResize.prototype.select = function(newElement) { with (this)
{
 // Selects an element for dragging.

 if (!document.getElementById || !enabled) return;

 // Activate and record our new dragging element.
 if (newElement && (newElement != element) && enabled)
 {
  element = newElement;
  // Elevate it and give it resize handles.
  element.style.zIndex = ++zIndex;
  if (this.resizeHandleSet) this.resizeHandleSet(element, true);
  // Record element attributes for mouseMove().
  elmX = parseInt(element.style.left);
  elmY = parseInt(element.style.top);
  elmW = element.offsetWidth;
  elmH = element.offsetHeight;
  if (ondragfocus) this.ondragfocus();
 }
}};


DragResize.prototype.deselect = function(delHandles) { with (this)
{
 // Immediately stops dragging an element. If 'delHandles' is true, this
 // remove the handles from the element and clears the element flag,
 // completely resetting the .

 if (!document.getElementById || !enabled) return;

 if (delHandles)
 {
  if (ondragblur) this.ondragblur();
  if (this.resizeHandleSet && element) this.resizeHandleSet(element, false);
  element = null;
 }

 handle = null;
 mOffX = 0;
 mOffY = 0;
}};


DragResize.prototype.mouseDown = function(e) { with (this)
{
 // Suitable elements are selected for drag/resize on mousedown.
 // We also initialise the resize boxes, and drag parameters like mouse position etc.
 if (!document.getElementById || !enabled) return true;

 var elm = e.target || e.srcElement,
  newElement = null,
  newHandle = null,
  hRE = new RegExp(myName + '-([trmbl]{2})', '');

 while (elm)
 {
  // Loop up the DOM looking for matching elements. Remember one if found.
  if (elm.className)
  {
   if (!newHandle && (hRE.test(elm.className) || isHandle(elm))) newHandle = elm;
   if (isElement(elm)) { newElement = elm; break }
  }
  elm = elm.parentNode;
 }

 // If this isn't on the last dragged element, call deselect(),
 // which will hide its handles and clear element.
 if (element && (element != newElement) && allowBlur) deselect(true);

 // If we have a new matching element, call select().
 if (newElement && (!element || (newElement == element)))
 {
  // Stop mouse selections if we're dragging a handle.
  if (newHandle) cancelEvent(e);
  select(newElement, newHandle);
  handle = newHandle;
  if (handle && ondragstart) this.ondragstart(hRE.test(handle.className));
 }
}};


DragResize.prototype.mouseMove = function(e) { with (this)
{
 // This continually offsets the dragged element by the difference between the
 // last recorded mouse position (mouseX/Y) and the current mouse position.
 if (!document.getElementById || !enabled) return true;

 // We always record the current mouse position.
 mouseX = e.pageX || e.clientX + document.documentElement.scrollLeft;
 mouseY = e.pageY || e.clientY + document.documentElement.scrollTop;
 // Record the relative mouse movement, in case we're dragging.
 // Add any previously stored & ignored offset to the calculations.
 var diffX = mouseX - lastMouseX + mOffX;
 var diffY = mouseY - lastMouseY + mOffY;
 mOffX = mOffY = 0;
 // Update last processed mouse positions.
 lastMouseX = mouseX;
 lastMouseY = mouseY;

 // That's all we do if we're not dragging anything.
 if (!handle) return true;

 // If included in the script, run the resize handle drag routine.
 // Let it create an object representing the drag offsets.
 var isResize = false;
 if (this.resizeHandleDrag && this.resizeHandleDrag(diffX, diffY))
 {
  isResize = true;
 }
 else
 {
  // If the resize drag handler isn't set or returns fase (to indicate the drag was
  // not on a resize handle), we must be dragging the whole element, so move that.
  // Bounds check left-right...
  var dX = diffX, dY = diffY;
  if (elmX + dX < minLeft) mOffX = (dX - (diffX = minLeft - elmX));
  else if (elmX + elmW + dX > maxLeft) mOffX = (dX - (diffX = maxLeft - elmX - elmW));
  // ...and up-down.
  if (elmY + dY < minTop) mOffY = (dY - (diffY = minTop - elmY));
  else if (elmY + elmH + dY > maxTop) mOffY = (dY - (diffY = maxTop - elmY - elmH));
  elmX += diffX;
  elmY += diffY;
 }

 // Assign new info back to the element, with minimum dimensions.

 with (element.style)
 {
  left =   elmX + 'px';
  width =  elmW + 'px';
  top =    elmY + 'px';
  height = elmH + 'px';
 }

 // Evil, dirty, hackish Opera select-as-you-drag fix.
 if (window.opera && document.documentElement)
 {
  var oDF = document.getElementById('op-drag-fix');
  if (!oDF)
  {
   var oDF = document.createElement('input');
   oDF.id = 'op-drag-fix';
   oDF.style.display = 'none';
   document.body.appendChild(oDF);
  }
  oDF.focus();
 }

 if (ondragmove) this.ondragmove(isResize);

 // Stop a normal drag event.
 cancelEvent(e);

}};


DragResize.prototype.mouseUp = function(e) { with (this)
{
 // On mouseup, stop dragging, but don't reset handler visibility.
 if (!document.getElementById || !enabled) return;

 var hRE = new RegExp(myName + '-([trmbl]{2})', '');
 if (handle && ondragend) this.ondragend(hRE.test(handle.className));
 deselect(false);
}};

/* Mattia remove handle */

DragResize.prototype.removeHandleSet = function(elm) { with (this)
{

 // Remove handles.
 for (var h = 0; h < handles.length; h++)
 {
  elm['_handle_' + handles[h]] = undefined;
 }
}};

/* Resize Code -- can be deleted if you're not using it. */

DragResize.prototype.resizeHandleSet = function(elm, show) { with (this)
{
 // Either creates, shows or hides the resize handles within an element.

 // If we're showing them, and no handles have been created, create 4 new ones.
 if (!elm._handle_tr)
 {
  for (var h = 0; h < handles.length; h++)
  {
   // Create 4 news divs, assign each a generic + specific class.
   var hDiv = document.createElement('div');
   hDiv.className = myName + ' ' +  myName + '-' + handles[h];
   elm['_handle_' + handles[h]] = elm.appendChild(hDiv);
  }
 }

 // We now have handles. Find them all and show/hide.
 for (var h = 0; h < handles.length; h++)
 {
  elm['_handle_' + handles[h]].style.visibility = show ? 'inherit' : 'hidden';
 }
}};


DragResize.prototype.resizeHandleDrag = function(diffX, diffY) { with (this)
{
 // Passed the mouse movement amounts. This function checks to see whether the
 // drag is from a resize handle created above; if so, it changes the stored
 // elm* dimensions and mOffX/Y.

 var hClass = handle && handle.className &&
  handle.className.match(new RegExp(myName + '-([tmblr]{2})')) ? RegExp.$1 : '';

 // If the hClass is one of the resize handles, resize one or two dimensions.
 // Bounds checking is the hard bit -- basically for each edge, check that the
 // element doesn't go under minimum size, and doesn't go beyond its boundary.
 var dY = diffY, dX = diffX, processed = false;
 if (hClass.indexOf('t') >= 0)
 {
  rs = 1;
  if (elmH - dY < minHeight) mOffY = (dY - (diffY = elmH - minHeight));
  else if (elmY + dY < minTop) mOffY = (dY - (diffY = minTop - elmY));
  elmY += diffY;
  elmH -= diffY;
  processed = true;
 }
 if (hClass.indexOf('b') >= 0)
 {
  rs = 1;
  if (elmH + dY < minHeight) mOffY = (dY - (diffY = minHeight - elmH));
  else if (elmY + elmH + dY > maxTop) mOffY = (dY - (diffY = maxTop - elmY - elmH));
  elmH += diffY;
  processed = true;
 }
 if (hClass.indexOf('l') >= 0)
 {
  rs = 1;
  if (elmW - dX < minWidth) mOffX = (dX - (diffX = elmW - minWidth));
  else if (elmX + dX < minLeft) mOffX = (dX - (diffX = minLeft - elmX));
  elmX += diffX;
  elmW -= diffX;
  processed = true;
 }
 if (hClass.indexOf('r') >= 0)
 {
  rs = 1;
  if (elmW + dX < minWidth) mOffX = (dX - (diffX = minWidth - elmW));
  else if (elmX + elmW + dX > maxLeft) mOffX = (dX - (diffX = maxLeft - elmX - elmW));
  elmW += diffX;
  processed = true;
 }

 return processed;
}};