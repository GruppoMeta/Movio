/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

var Glizy = new (function(){
    this.slideShowSpeed = 20;
    this.modulesMap = {};

    var oopManager = function(){
        this.classMap = {};
        this.declare = function(name, def) {
            this.classMap[name] = dejavu.Class.declare(def);
        };

        this.get = function(name) {
            return this.classMap[name];
        };

        this.create = function(name) {
            var args = Array.prototype.slice.call(arguments, 0);
            var name = args.shift();
            return !this.classMap[name] ? null : new this.classMap[name](
                args[0] !== undefined ? args[0] : undefined,
                args[1] !== undefined ? args[1] : undefined,
                args[2] !== undefined ? args[2] : undefined,
                args[3] !== undefined ? args[3] : undefined,
                args[4] !== undefined ? args[4] : undefined
             );
        };

    };
    this.oop = new oopManager();


	this.module = function(name, def) {
		if (def) {
			this.modulesMap[name] = def;
		} else {
			return new this.modulesMap[name];
		}
	};

    this.confirm = function (message, buttons, callback) {
        callback(confirm(message));
    };

    this.responder = function( owner, method ) {
        return function( a, b, c, d ){ method.call( owner, a, b, c, d  )};
    };

    var uiDialog = null;

    this.openDialog  = function(el, options) {
        if (window.self !== window.top) {
            window.top.Glizy.openDialog(el, options);
            return;
        }

        uiDialog = jQuery(el).dialog(options);
    }

    this.closeDialog  = function() {
        if (window.self !== window.top) {
            window.top.Glizy.closeDialog();
            return;
        }

        if (uiDialog) {
            var result = {};
            uiDialog.find('select, input, textarea').each(function(index, el){
                var $el = jQuery(el);
                result[$el.attr('id')] = $el.val();
            });
            this.events.broadcast("glizy.closeDialog", result);
            uiDialog.dialog("destroy");
        }
    }


    this.openIFrameDialog  = function(title, url, minWidth, widthOffset, heightOffset, openCallback) {
        if (window.self !== window.top) {
            window.top.Glizy.openIFrameDialog(title, url, minWidth, widthOffset, heightOffset, openCallback);
            return;
        }
        if (!jQuery('#modalDiv').length) {
            jQuery('body').append('<div id="modalDiv" style="display: none; padding: 0; overflow: hidden;"><iframe src="" id="modalIFrame" width="100%" height="100%" marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"></iframe></div>');
        }

        if ( jQuery("#modalDiv").data( "isDialog" ) != "true" ) {
            jQuery("#modalDiv").dialog({
                    modal: true,
                    autoOpen: false,
                    draggable: true,
                    resizable: false,
                    open: openCallback
                });
            jQuery("#modalDiv").data( 'isDialog', 'true' );
        }
        var w = Math.min( jQuery( window ).width() - widthOffset, minWidth ),
            h = jQuery( window ).height() - heightOffset;
        jQuery("#modalDiv").dialog( "option", { height: h, width: w, title: title } );
        jQuery("#modalDiv").dialog( "open" );
        if ( jQuery("#modalIFrame").attr( "src") != url )
        {
            jQuery("#modalIFrame").attr( "src", url );
        }
    };

    this.closeIFrameDialog= function(removeFromDom) {
        if (window.self !== window.top) {
            window.top.Glizy.closeIFrameDialog(removeFromDom);
            return;
        }
        jQuery("#modalDiv").dialog('close');

        if (removeFromDom) {
            jQuery("#modalDiv").remove();
        }
    };

    this.externalLinks = function() {
        jQuery("a[rel='external']")
            .attr("target", "_blank")
            .attr("rel", "external noopener noreferrer");
    };


})();

Glizy.superzoom = new(function() {
    var self = this;
    this.zoomViewer = null;

    this.init = function() {
        jQuery('span.js-glizySuperZoom')
            .each(function(index, el){
                var $el = jQuery(el);
                var $img = $el.prev();
                $el.parent().css({position: 'relative', display: 'inline-block'});
                $el.hide();
                var setPos = function() {
                    if ($img.width()<300) {
                        $el.addClass('small');
                    }
                    $el.css({
                        position: 'absolute',
                        top: $img.height() - parseInt($img.css('border-top-width').replace('px')) - parseInt($img.css('padding-top').replace('px'))  - $el.height(),
                        left: parseInt($img.css('padding-left').replace('px')) + parseInt($img.css('border-left-width').replace('px'))
                    });
                    $el.show();
                }
                if ($img.width()) {
                    setPos();
                } else {
                    setTimeout(setPos, 500);
                }
            })
            .click(function(e){
              e.preventDefault();
              e.stopPropagation();
              self.openZoom(jQuery(this).data('mediaid'), jQuery(this).data('mediawatermark'));
            });
    };

    this.closeZoom = function() {
        self.zoomViewer.close();
    }

    this.openZoom  = function( id, watermark, fullScreen, zoomId) {
        fullScreen = fullScreen===undefined ? true : fullScreen;
        zoomId = zoomId || 'zoomContainer';
        var zoomContainer = jQuery('#'+zoomId);
        var zoomFile = zoomContainer.data('cache')+"/zoom_"+id+"_"+watermark+".xml";
        if ( this.zoomViewer == null ) {
            SeadragonConfig.imgPath = "static/";
            this.zoomViewer = new Seadragon.Viewer(zoomId);
            this.zoomViewer.setFullPage( fullScreen );
            this.zoomViewer.onFullPage = function() {
                self.zoomViewer.close();
                zoomContainer.hide();
            }
        }
        var url = "zoom.php?id="+id+"&w="+watermark;
        Seadragon.Utils.makeAjaxRequest(url, function(xhr) {
            zoomContainer.show();
            self.zoomViewer.setFullPage( fullScreen );
            self.zoomViewer.openDzi(zoomFile);
        });
    };
});

Glizy.events = new(function() {
    var eventMap = undefined;
    this.init = function() {
        if (window.postMessage) {
            if (eventMap===undefined) {
                eventMap = {};
                var triggeredFunction = function(e) {
                    if (!e.data) return;
                    if (eventMap[e.data.type]!==undefined) {
                       $(eventMap[e.data.type]).each(function(index, el){
                            if (el) {
                                el({
                                        type: e.data.type,
                                        message: e.data.message,
                                        time: new Date()
                                    });
                            }
                        });
                    }

                    $('iframe').each(function(index, el){
                        el.contentWindow.postMessage({type: e.data.type, message: e.data.message}, '*');
                    });
                }

                if (typeof window.addEventListener != 'undefined') {
                    window.addEventListener('message', triggeredFunction, false);
                } else if (typeof window.attachEvent != 'undefined') {
                    window.attachEvent('onmessage', triggeredFunction);
                }
            }
        }
    };

    this.broadcast = function(type, message) {
        if (window.postMessage) {
            window.top.postMessage({type: type, message: message}, '*');
        }
    };

    this.on = function(type, callback) {
        $(document).on(type, callback);
        if (window.postMessage) {
            var pos;
            if (eventMap[type]===undefined) {
               eventMap[type] = [];
            }
            pos = eventMap[type].length;
            eventMap[type].push(callback);

            return pos;
        }
        return null;
    };

    this.unbind = function(type, pos) {
        $(document).unbind(type);
        if (window.postMessage) {
            if (eventMap[type]===undefined) {
               eventMap[type] = [];
            }
            eventMap[type][pos] = null;
        }
    };

    this.init();
});

Glizy.template = new(function(){
    var templatesCache = {};
    var templates = {};

    this.define = function(name, tpl) {
        templates[name] = tpl;
        templatesCache[name] = undefined;
    }

    this.render = function(name, data) {
        if (!templates[name]) {
            console.error('Template not defined: '+name);
            return '';
        }
        if (!templatesCache[name]) {
            templatesCache[name] = _.template(templates[name]);
        }
        return templatesCache[name](data);
    }
});

Glizy.message = new(function() {
    var notify = {
            history: false,
            type: "success",
            animate_speed: "fast",
            sticker: false,
            delay: 3000,
            addclass: "stack-bar-top",
            styling: "fontawesome",
            cornerclass: "",
            width: "100%",
            stack: {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0}
        };

    this.showSuccess = function(title, message) {
        notify.title = title;
        notify.text = message;
        notify.type = "success";
        $.pnotify(notify);
    };
    this.showError = function(title, message) {
        notify.title = title;
        notify.text = message;
        notify.type = "error";
        $.pnotify(notify);
    };
    this.showWarning = function(title, message) {
        notify.title = title;
        notify.text = message;
        notify.type = "notice";
        $.pnotify(notify);
    };
    this.confirm = function(title, message) {
        return confirm(message);
    };

    if (window==window.top) {
        var self = this;
        Glizy.events.on("glizy.message.showSuccess", function(e){
            self.showSuccess(e.message.title, e.message.message);
        });
        Glizy.events.on("glizy.message.showError", function(e){
            self.showError(e.message.title, e.message.message);
        });
        Glizy.events.on("glizy.message.showWarning", function(e){
            self.showWarning(e.message.title, e.message.message);
        });
    }
});

try {
    Glizy.$ = jQuery;
    jQuery(function(){
        Glizy.externalLinks();
        Glizy.superzoom.init();
    })
} catch (e) {}
