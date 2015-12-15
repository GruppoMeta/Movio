Glizy.module('ontologybuilder.SearchContent', function(){
    _.templateSettings.variable = "rc";
    var self = this;

    self.html = '<div id="searchContentDiv" class="resizeDiv">' +
                    '<div id="handle" class="resize"></div>' +
                    '<div class="js-closeDiv pull-right" type="button">x</div>' +
                    '<div id="searchForm">' +
                        '<input id="searchText" type="search" style="margin-left:5px;" autocomplete="on">' +
                        '<i id="searchSubmit" class="icon-search"></i>' +
                        '<input type="radio" id="europeanaSearch"  name="searchType" value="europeana" class="js-searchMode contentSearch" checked > {i18n:search on europeana}' +
                        '<input type="radio" id="wikipediaSearch" name="searchType" value="wikipedia" class="js-searchMode contentSearch"> {i18n:search on wikipedia}' +
                        '<div id="searchResultContextmenu" style="display: none;">' +
                            '<div class="pull-left" id="input-menu">' +
                                '<div class="btn-group">' +
                                    '<a data-target="#input-menumenu" class="btn dropdown-toggle action-link" data-toggle="dropdown">' +
                                         '{i18n:Copy selection in}:' +
                                     '</a>' +
                                    '<div id="input-menumenu" class="open">' +
                                        '<ul class="dropdown-menu right">' +
                                            '<% _.each( rc.menu, function(field ){ %>' +
                                                '<li><a title=<%- field.label %> class="js-copy" href="#<%- field.id %>"><%- field.label %></a></li>' +
                                            '<% });%>' +
                                        '</ul>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div id ="loadingFrame" class="hidden" style="vertical-align:middle;"></div>' +
                    '<iframe id="searchResultFrame" name="searchResultFrame" src="about:blank" target="searchResultFrame">' +
                    '</iframe>' +
                '</div>';

    self.europeanaLink = "www.europeana.eu/portal/search.html?query=<%- rc.searchTerm%>";
    self.wikipediaLink = "it.wikipedia.org/wiki/<%- rc.searchTerm%>";
    self.selectedText ='';
    self.searchActive = true;
    self.url = '';
    self.numField = 0;
    self.instance = false;
    self.maxHeigth = 0;

    this.run = function() {
        var target = $("#outer");
        var menu = self.getInputFields();
        var templateData = { 'menu' : menu };
        var menu = self.getInputFields();
        var templateData = { 'menu' : menu };
        self.render(target, templateData, self.html, true);
        self.resizeAdjust();
        target.css('height', '');
        $('#searchContentDiv').hide();
        self.setResize()
        $("#breadcrumb-actions a").on('click', function(e){
            e.preventDefault();
            self.searchActive = self.searchActive === false;
            if (!self.searchActive) {
                self.setResizableDiv(target);
                $('#searchResultFrame').css('height', $('#searchResultFrame').height()-40);
            }
            else {
                self.closeSearchDiv(target);
            }
        });

        $(window).resize(function() {
            self.resizeAdjust(target)
            self.setMaxHeight();
            if(!self.searchActive)
            {
                $(".formButtons").css('bottom', $('#searchContentDiv').height());
            }
            self.removeSelectedText();
        });
        $(".js-closeDiv").on('click', function(){
            self.closeSearchDiv(target);
            self.searchActive = true;
        });
        $(window).on('keyup', function(e) {
            var charCode = e.charCode || e.keyCode || e.which;
            if (charCode == 27){
                self.closeSearchDiv(target);
                self.searchActive = true;
            }
        });

        $("#searchResultFrame").on("load", function () {
            self.setFrameEvents("searchResultFrame");
        });

    };

    this.setMaxHeight = function()
    {
        self.maxHeigth = (2/3)*$(window).height();
    }

    this.setResize = function() {
        var resize = false;
        var split_height = $("#searchContentDiv").height();
        self.maxHeigth = (2/3)*$(window).height();
        $(document).on('mouseup', function(event)
        {
            resize = false;
            split_height = $("#searchContentDiv").height();
        });

        $(".resize").mousedown(function(event)
        {
                resize = event.pageY;
                self.removeSelectedText();
        });
        $(document).mousemove(function(event)
        {
            if (resize)
            {
                if (split_height + resize - event.pageY < 200)
                {
                    $("#searchContentDiv").height(200);
                }
                else if (split_height + resize - event.pageY > self.maxHeigth)
                {
                    $("#searchContentDiv").height(self.maxHeigth);
                }
                else
                {
                    $("#searchContentDiv").height(split_height + resize - event.pageY);
                }
                $(".formButtons").css('bottom', $('#searchContentDiv').height());
                self.setHeight();
            }
        });

    }
    this.closeSearchDiv = function(target){
        self.resetResizableDiv(target);
        self.resizeAdjust(target)
        self.setMaxHeight();
        target.attr('style', function(i, style)
        {
            return style.replace(/height[^;]+;?/g, '');
        });
    }

    this.resizeAdjust = function(){
        var mid = $(window).scrollTop() + 2*Math.floor($(window).height() /5);
        var searchDiv = $('#searchContentDiv');
        searchDiv.css({ 'height': mid - $('#handle').height(),
                        'left': $('#sidebar').width() } );
        self.setHeight();
        self.setWidth();
    }

    this.resetResizableDiv = function(target) {
        $(document).css('overflow', 'auto');
        target.removeClass('splitDiv');
        $('#searchContentDiv').hide();
        $(".formButtons").css({'position': 'fixed', 'bottom': 0, 'z-index' : 9 });
    };

    this.setResizableDiv = function(target) {
        $("#sidebar").css('position', 'fixed');
        $(target).addClass('splitDiv');
        self.setHeight();
        $('#searchContentDiv').show();
        self.setMaxHeight();
        $(".formButtons").css({'position': 'fixed', 'bottom': $('#searchContentDiv').height(), 'z-index': 1});
        $('#searchText').focus();
        $('#searchSubmit, .js-searchMode').on('click', function(){
            self.callSearch();
        });
        $('#searchText').on('keypress', function(e){
            if(e.which == 13) {
                e.preventDefault();
                self.callSearch();
            }
        })
        $(".js-copy").click( function (e) {
            e.preventDefault();
            self.copySelectedText($(this));
        });
    };

    this.setWidth = function(){
        var searchDiv = $('#searchContentDiv');
        $('#outer').css('overflow', 'hidden');
        searchDiv.css('width', $(document).width() - $('#sidebar').width());
        $('#handle').css('width', '100%');
        $('#searchResultFrame').css('width', "100%");
        $('#outer').css('overflow', 'auto');
    }

    this.setHeight = function() {
        $("#outer").css('height', $(window).height() - $('#searchContentDiv').height());
        var iFrameHeight = $('#searchContentDiv').height() - $('#handle').height() - $('#searchForm').height() -15;
        $('#searchResultFrame').css('height', iFrameHeight);
        $('#loadingFrame').css('height', $('#searchResultFrame').height() -40);
    };

    this.render = function(targetTag, templateData, html, append) {
        var template = _.template( html );
        var rendered = template( templateData )
        if(append){
            $(targetTag).after( rendered );
        }
        else{
            $(targetTag).replaceWith( rendered );
        }
    };

this.copySelectedText = function(el) {
        selector = $(el.attr('href'));
        selector.focus();
        if(selector.prop("tagName") == "TEXTAREA" && selector.attr('data-type') == "tinymce") {
            tinyId = selector.attr('id');
            tinyMCE.get(tinyId).execCommand('mceInsertContent', true, self.selectedText + '<br />');
        } else {
            if(selector.val()) {
                selector.val( selector.val() + ' ' + self.selectedText);
            } else {
                selector.val(self.selectedText);
            }
        }
        self.removeSelectedText();
    }

    this.callSearch = function() {
        self.searchEngine = $("input[type='radio'][name='searchType']:checked").val();
        self.searchTerm = $('#searchText').val()
        if(!self.searchTerm) {
            $('#searchText').focus();
        } else {
            self.loadSearchResult();
        }
    }


    this.loadSearchResult = function()
    {
        var link ='';
        switch (self.searchEngine) {
            case 'wikipedia':
                link = self.wikipediaLink;
            break;
            default:
                link = self.europeanaLink;
        }
        var template = _.template( link );
        var templateData = { 'searchTerm' : self.searchTerm };
        link = template( templateData );
        var frameId = 'searchResultFrame';
        self.loadContent(link, frameId);
    }

    this.loadContent = function(link, frameId) {
       self.url = link;
       self.removeSelectedText();
       $('#searchResultFrame').addClass('hidden');
       $('#loadingFrame').removeClass('hidden');
       $('#loadingFrame').css('height', $('#searchResultFrame').height() - 40);
       var url = '../admin/proxy/'+ escape(link);
       $('#' + frameId).attr('src', url);
    };

    this.setFrameEvents = function(frameId) {
       var urlArray = self.url.split('.');
       $('#loadingFrame').addClass('hidden');
       $('#searchResultFrame').removeClass('hidden');
       $frameDocument = $('#' + frameId).contents()
       if (urlArray[1] === 'europeana') {
            $('#query-full', $frameDocument).hide();
            $('[id*="cb-"]', $frameDocument).on('click', function(e){
                if($(this).prop('checked')) {
                    link = $(this).parent().find('a').attr('href');
                    self.goToLink(link)
                }
            })
       } else {
            $('#p-search', $frameDocument).hide();
       }
       $('a', $frameDocument).on('click', function(e) {
            e.preventDefault();
            var link = $(this).attr('href');
            self.goToLink(link)
        });
        $frameDocument.on('mouseup', function (e) {
            var maxXval = $(document).width() - $("#input-menu").width()-$('#sidebar').width()
            var x = Math.min(e.pageX + 5, maxXval);
            var yPos = e.pageY - $('#searchResultFrame').contents().scrollTop() + $('#handle').height() + $('#searchForm').height();
            var yBottom = self.numField*$('.dropdown-menu').height();
            var maxYval = $('#searchResultFrame').height() - yBottom;
            var y = Math.min(yPos, maxYval);
            self.selectedText = self.getIframeSelectionText('searchResultFrame');
            if (self.selectedText) {
                self.openContextMenu($('#searchResultContextmenu'), x, y);
            } else {
                $('#searchResultContextmenu').hide();
            }
        });
        $frameDocument.on('beforeunload', function(e){
            e.preventDefault();
         });
        $('#searchResultFrame').contents().on('scroll', function(e) {
            self.removeSelectedText();
        });
        self.instance = true;
    };

    this.goToLink = function (link) {
        if(link) {
             if (link.indexOf('#') == 0) {
                /*TODO scroll to anchor... non funziona nell'iframe*/
                return;
             }
             var index = Math.max(link.indexOf('europeana.eu'), link.indexOf('wikipedia.org'));
             if ( index < 0  ) {
                return;
             } else {
                 link = link.replace(/http:\/\//, '');
            }
            self.loadContent(link, 'searchResultFrame');
        }
    }

    this.getIframeSelectionText = function(iframeId) {
        var iframe = document.getElementById(iframeId);
        var win, doc = iframe.contentDocument;
        if (doc) {
            win = doc.defaultView;
        } else {
            win = iframe.contentWindow;
            doc = win.document;
        }

        if (win.getSelection) {
            return win.getSelection().toString();
        } else if (doc.selection && doc.selection.createRange) {
            return doc.selection.createRange().text;
        }
    };

    this.openContextMenu = function(menu, x, y) {
        $("#input-menumenu").addClass("open");
        menu.css( { 'top': y, 'left' : x, 'z-index': 2});
        menu.show();
    };

    this.getInputFields = function() {
        self.numField = 0;
        var labelList = [];
        var divCG = $('#myForm .control-group');
        var exclude = ['selectfrom', 'mediapicker', 'entityselect', 'europeanaRelatedContents'];
        divCG.each( function(i, div) {
            var label = $(this).find('label').html();
            var field = $($(this).find('input').get(0) || $(this).find('textarea').get(0));
            if ( (field.attr('type') == 'text' || !field.attr('type'))
                 && !field.attr('disabled')
                 && $.inArray(field.attr('data-type'), exclude) ==-1
                ) {
                    var item = { 'id' : field.attr('id') ,
                                 'label': label
                                };
                    labelList.push(item);
                    self.numField++;
            }
         });
         return labelList;
    };

    this.removeSelectedText = function() {
       $('#searchResultContextmenu').hide();
       var iframe = document.getElementById('searchResultFrame');
       if(!iframe) {
           return;
       }
       var win, doc = iframe.contentDocument;
       if (doc) {
            win = doc.defaultView;
       } else {
            win = iframe.contentWindow;
            doc = win.document;
       }

       if (win.getSelection()) {
            if (win.getSelection().empty) {
                win.getSelection().empty();
            } else if (win.getSelection().removeAllRanges) {
                win.getSelection().removeAllRanges();
            }
        } else if (doc.selection) {
            doc.selection.empty();
        }
    };

    this.wait = function(milliseconds) {
        var start = new Date().getTime();
        for (var i = 0; i < 1e7; i++) {
            if ((new Date().getTime() - start) > milliseconds){
                break;
            }
        }
    };

});
