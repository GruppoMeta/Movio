(function (a) {
    Galleria.addTheme({
        name: 'pageflip',
        author: 'GruppoMeta',
        css: 'css/style.css',
        defaults: {
            thumbnails: 'lazy',
            transition: "fade",
            transitionSpeed: 1500,
            imageCrop: false,
            thumbCrop: !0,
            carousel: !1,
            _locale: {
                show_thumbnails: "Mostra le miniature",
                hide_thumbnails: "Nascondi le miniature",
                play: "Play slideshow",
                pause: "Pause slideshow",
                enter_fullscreen: "Entra fullscreen",
                exit_fullscreen: "Esci fullscreen",
                popout_image: "Pop image",
                showing_image: "immagini %s di %s"
            },
            _showFullscreen: !0,
            _showPopout: !1,
            _showProgress: !0,
            _showTooltip: !0
        },
        init: function (b) {
            this.addElement("bar", "fullscreen", "play", "popout", "thumblink", "delimiter1", "delimiter2", "delimiter3", "delimiter4", "progress"), this.append({
                stage: "progress",
                container: ["bar", "tooltip"],
                bar: ["fullscreen", "play", "popout", "thumblink", "info", "delimiter1", "delimiter2", "delimiter3", "delimiter4"]
            }), this.prependChild("info", "counter");
            var c = this,
                d = this.$("thumbnails-container"),
                e = this.$("thumblink"),
                f = this.$("fullscreen"),
                g = this.$("play"),
                h = this.$("popout"),
                i = this.$("bar"),
                j = this.$("progress"),
                k = b.transition,
                l = b._locale,
                m = !1,
                n = !1,
                o = !! b.autoplay,
                p = !1,
                q = function () {
                    d.height(c.getStageHeight()).width(c.getStageWidth()).css("top", m ? 0 : c.getStageHeight() + 30)
                }, r = function (a) {
                    m && p ? c.play() : (p = o, c.pause()), Galleria.utils.animate(d, {
                        top: m ? c.getStageHeight() + 30 : 0
                    }, {
                        easing: "galleria",
                        duration: 400,
                        complete: function () {
                            c.defineTooltip("thumblink", m ? l.show_thumbnails : l.hide_thumbnails), e[m ? "removeClass" : "addClass"]("open"), m = !m
                        }
                    })
                };
            q(), b._showTooltip && c.bindTooltip({
                thumblink: l.show_thumbnails,
                fullscreen: l.enter_fullscreen,
                play: l.play,
                popout: l.popout_image,
                caption: function () {
                    var a = c.getData(),
                        b = "";
                    return a && (a.title && a.title.length && (b += "<strong>" + a.title + "</strong>"), a.description && a.description.length && (b += "<br>" + a.description)), b
                },
                counter: function () {
                    return l.showing_image.replace(/\%s/, c.getIndex() + 1).replace(/\%s/, c.getDataLength())
                }
            }), b.showInfo || this.$("info").hide(), this.bind("play", function () {
                o = !0, g.addClass("playing")
            }), this.bind("pause", function () {
                o = !1, g.removeClass("playing"), j.width(0)
            }), b._showProgress && this.bind("progress", function (a) {
                j.width(a.percent / 100 * this.getStageWidth())
            }), this.bind("loadstart", function (a) {
                a.cached || this.$("loader").show()
            }), this.bind("loadfinish", function (a) {
                j.width(0), this.$("loader").hide(), this.refreshTooltip("counter", "caption");
                this.lazyLoadChunks( 10 );
            }), this.bind("thumbnail", function (b) {
                a(b.thumbTarget).hover(function () {
                    c.setInfo(b.thumbOrder), c.setCounter(b.thumbOrder)
                }, function () {
                    c.setInfo(), c.setCounter()
                }).click(function () {
                    r()
                })
            }), this.bind("fullscreen_enter", function (a) {
                n = !0, c.setOptions("transition", !1), f.addClass("open"), i.css("bottom", 0), this.defineTooltip("fullscreen", l.exit_fullscreen), Galleria.TOUCH || this.addIdleState(i, {
                    bottom: 0
                })
            }), this.bind("fullscreen_exit", function (a) {
                n = !1, Galleria.utils.clearTimer("bar"), c.setOptions("transition", k), f.removeClass("open"), i.css("bottom", 0), this.defineTooltip("fullscreen", l.enter_fullscreen), Galleria.TOUCH || this.removeIdleState(i, {
                    bottom: -31
                })
            }), this.bind("rescale", q), Galleria.TOUCH || (this.addIdleState(this.get("image-nav-left"), {
                left: -36
            }), this.addIdleState(this.get("image-nav-right"), {
                right: -36
            })), e.click(r), b._showPopout ? h.click(function (a) {
                c.openLightbox(), a.preventDefault()
            }) : (h.remove(), b._showFullscreen && (this.$("s4").remove(), this.$("info").css("right", 40), f.css("right", 0))), g.click(function () {
                c.defineTooltip("play", o ? l.play : l.pause), o ? c.pause() : (m && e.click(), c.play())
            }), b._showFullscreen ? f.click(function () {
                n ? c.exitFullscreen() : c.enterFullscreen()
            }) : (f.remove(), b._show_popout && (this.$("s4").remove(), this.$("info").css("right", 40), h.css("right", 0))), !b._showFullscreen && !b._showPopout && (this.$("s3,s4").remove(), this.$("info").css("right", 10)), b.autoplay && this.trigger("play")
        }
    })
})(jQuery)
$("#galleria").addClass("sfogliatore");