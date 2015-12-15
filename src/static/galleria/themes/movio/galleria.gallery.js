(function($) {

Galleria.addTheme({
    name: 'gallery',
    author: 'GruppoMeta',
    css: 'css/style.css',
    defaults: {
        transition: 'fade',
		imageCrop: false,
        thumbnails: 'lazy',

        // set this to false if you want to show the caption all the time:
        _toggleInfo: false

    },
    init: function(options) {

        Galleria.requires(1.28, 'This version of Classic theme requires Galleria 1.2.8 or later');

        // add some elements
        this.addElement('info-link','info-close');

		this.append({
            'info' : ['info-link','info-close']
        });

		// add some elements
        this.addElement('container-delimeter');

		this.append({
            'container' : ['container-delimeter']
        });

        // cache some stuff
        var info = this.$('info-link,info-close,info-text'),
            touch = Galleria.TOUCH,
            click = touch ? 'touchstart' : 'click';

        // show loader & counter with opacity
        this.$('loader,counter').show().css('opacity', 1);
		this.appendChild('container','counter');

        // some stuff for non-touch browsers
        if (! touch ) {
            this.addIdleState( this.get('image-nav-left'), { left:-50 });
            this.addIdleState( this.get('image-nav-right'), { right:-50 });
            this.addIdleState( this.get('counter'), { opacity:1 });
        }

        // toggle info
        if ( options._toggleInfo === true ) {
            info.bind( click, function() {
                info.toggle();
            });
        } else {
            info.show();
            this.$('info-link, info-close').hide();
        }

        // bind some stuff
        this.bind('thumbnail', function(e) {

            if (! touch ) {
                // fade thumbnails
                $(e.thumbTarget).css('opacity', 0.6).parent().hover(function() {
                    $(this).not('.active').children().stop().fadeTo(100, 1);
                }, function() {
                    $(this).not('.active').children().stop().fadeTo(400, 0.6);
                });

                if ( e.index === this.getIndex() ) {
                    $(e.thumbTarget).css('opacity',1);
                }
            } else {
                $(e.thumbTarget).css('opacity', this.getIndex() ? 1 : 0.6);
            }
        });

        this.bind('loadstart', function(e) {
            if (!e.cached) {
                this.$('loader').show().fadeTo(200, 0.4);
            }

            this.$('info').toggle( this.hasInfo() );

            $(e.thumbTarget).css('opacity',1).parent().siblings().children().css('opacity', 0.6);
        });

        this.bind('loadfinish', function(e) {
            this.$('loader').fadeOut(200);
            this.lazyLoadChunks( 10 );
        });
    }
});

Galleria.ready(function() {
    var gallery = this;
    this.addElement('fscr');
    this.appendChild('container','fscr');
    var fscr = this.$('fscr')
        .click(function() {
            gallery.toggleFullscreen();
        });
    this.addIdleState(this.get('fscr'), { opacity:0.7 });

    $(".galleria-fscr").click(function () {
          $(".galleria-fscr").toggleClass('active-fscr');
    });

});
}(jQuery));
