(function (d) {
    Galleria.addTheme({
        name: 'slider',
        author: 'GruppoMeta',
        css: 'css/style.css',
        defaults: {
            thumbnails: 'lazy',
            transition: "pulse",
            thumbCrop: true,
            imageCrop: false,
            carousel: false,
            imagePan: true,
            clicknext: true,
            _locale: {
                enter_fullscreen: "Enter fullscreen",
                exit_fullscreen: "Exit fullscreen",
                click_to_close: "Click to close",
                show_thumbnails: "Show thumbnails",
                show_info: "Show info"
            }
        },
        init: function (e) {
            var c = this,
                h = false,
                b;
            b = 0;
            var f, j, i;
            this.addElement("desc", "dots", "thumbs", "fs", "more");
            this.append({
                container: ["desc", "dots",
                    "thumbs", "fs", "info-description", "more"
                ]
            });
            i = this.$("thumbnails-container").hide().css("visibility", "visible");
            for (b = 0; b < this.getDataLength(); b++) this.$("dots").append(d("<div>").click(function (a) {
                return function (g) {
                    g.preventDefault();
                    c.show(a)
                }
            }(b)));
            b = this.$("dots").outerWidth();
            f = this.$("desc").hide().hover(function () {
                d(this).addClass("hover")
            }, function () {
                d(this).removeClass("hover")
            }).click(function () {
                d(this).hide()
            });
            j = this.$("loader");
            this.bindTooltip({
                fs: function () {
                    return h ? e._locale.exit_fullscreen :
                        e._locale.enter_fullscreen
                },
                desc: e._locale.click_to_close,
                more: e._locale.show_info,
                thumbs: e._locale.show_thumbnails
            });
            this.bind("loadstart", function (a) {
                a.cached || this.$("loader").show().fadeTo(200, 0.4)
            });
            this.bind("loadfinish", function (a) {
                var g = c.getData().title,
                    k = c.getData().description;
                f.hide();
                j.fadeOut(200);
                this.$("dots").children("div").eq(a.index).addClass("active").siblings(".active").removeClass("active");
                if (g && k) {
                    f.empty().append("<strong>" + g + "</strong>", "<p>" + k + "</p>").css({
                        marginTop: this.$("desc").outerHeight() / -2
                    });
                    this.$("more").show()
                } else this.$("more").hide();
                i.fadeOut(e.fadeSpeed);
                c.$("thumbs").removeClass("active");
                this.lazyLoadChunks( 10 );
            });
            this.bind("thumbnail", function (a) {
                d(a.thumbTarget).hover(function () {
                    c.setInfo(a.index)
                }, function () {
                    c.setInfo()
                })
            });
            this.$("fs").click(function () {
                c.toggleFullscreen();
                h = !h
            });
            this.$("thumbs").click(function (a) {
                a.preventDefault();
                i.toggle();
                d(this).toggleClass("active");
                f.hide()
            });
            this.$("more").click(function () {
                f.toggle()
            });
            this.$("info").css({
                width: this.getStageWidth() - b - 30,
                left: b + 10
            })
        }
    })
})(jQuery);
// $("#galleria").addClass("slider");