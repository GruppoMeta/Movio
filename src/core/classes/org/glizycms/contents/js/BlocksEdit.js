Glizy.module('glizycms.BlockEdit', function(){
    var self = this;

    this.container = null;
    this.ajaxUrl = null;
    this.menuId = null;
    this.blocksPos = null;
    // this.emptySrc = null;
    // this.editSrc = null;
    // this.iframeOffset = null;

    this.run = function() {
        this.templateDefine();

        this.container = $('.js-glizycmsBlocksEdit');
        if (this.container.length) {
            this.container.addClass('GlizycmsBlockEdit loading clearfix');
            this.ajaxUrl = this.container.data('ajaxurl');
            this.menuId = this.container.data('menuid');
            this.loadChild();

            $(this.container).on('click', 'a.js-edit', function(e){
                e.preventDefault();
                Glizy.events.broadcast("glizycms.pageEdit", {"menuId": $(this).data("id")});
            });

            $(this.container).on('click', 'a.js-delete', function(e){
                e.preventDefault();
                var id = $(this).data("id");
                Glizy.confirm("{i18n:glizycms.confirm.pageDelete}", [], function(success){
                    if (success) {
                        $.ajax({
                                type: 'GET',
                                url: self.ajaxUrl.replace('pageEdit', 'treeview')+"Delete",
                                data : {
                                    "menuId" : id
                                },
                                success : function (r) {
                                    self.loadChild();
                                }
                            });
                    }
                });
            });

            $(this.container).on('click', 'a.js-duplicate', function(e){
                e.preventDefault();
                var id = $(this).data("id");
                $.ajax({
                    type: 'POST',
                    url: self.ajaxUrl.replace('pageEdit', 'treeview')+"DuplicatePage",
                    data : {
                        "menuId" : id
                    },
                    success : function (r) {
                        self.loadChild();
                    }
                });
            });

            $(this.container).on('click', 'a.js-show', function(e){
                e.preventDefault();
                var id = $(this).data("id");
                $.ajax({
                        type: 'GET',
                        url: self.ajaxUrl.replace('pageEdit', 'treeview')+"Show",
                        data : {
                            "menuId" : id
                        },
                        success : function (r) {
                            self.loadChild();
                        }
                    });
            });

            $(this.container).on('click', 'a.js-hide', function(e){
                e.preventDefault();
                var id = $(this).data("id");
                $.ajax({
                        type: 'GET',
                        url: self.ajaxUrl.replace('pageEdit', 'treeview')+"Hide",
                        data : {
                            "menuId" : id
                        },
                        success : function (r) {
                            self.loadChild();
                        }
                    });
            });

            $(this.container).on('click', 'div.blockEmpty', function(e){
                e.preventDefault();
                Glizy.events.broadcast("glizycms.pageAdd", {"href": 'glizycms_contentsedit/addblock?menuId='+self.menuId});
            });
        }

        // used in title breadcrumbs in ContentEdit.xml:edit
        $(document).on('click', 'a.js-glizycms-menu-edit', function(e){
            e.preventDefault();
            Glizy.events.broadcast("glizycms.pageEdit", {"menuId": $(this).data("id")});
        });
    };

    this.loadChild = function() {
        $.ajax({
                type: 'GET',
                url: this.ajaxUrl+"GetChildBlocks",
                data : {
                    "id" : this.menuId
                },
                success : function (r) {
                    self.render(r);
                }
            });
    };

    this.render = function(blocks) {
        var html = Glizy.template.render('glizycms.BlockEdit.items', {blocks: blocks});
        this.container.html($(html));
        this.container.find('.js-sortable').sortable({
            'stop': function(event, ui) {
                self.onChange(event, ui);
            }
        });
        this.refreshBlockPos();
    };

    this.refreshBlockPos = function() {
        this.blocksPos = [];
        this.container.find('.js-edit').each(function(index, el){
            var $el = $(el);
            self.blocksPos.push($el.data('id'));
        });
    }

    this.onChange = function(event, ui) {
        var id = ui.item.find('.js-edit').data('id');
        var oldPos = this.blocksPos.indexOf(id);
        this.refreshBlockPos();
        var newPos = this.blocksPos.indexOf(id);
        if (oldPos!=newPos) {
            $.ajax({
                type: 'POST',
                url: this.ajaxUrl+"MoveBlock",
                data : {
                    "menuId" : id,
                    "parentId" : this.menuId,
                    "position" : newPos
                }
            });
        }
    }

    this.templateDefine = function() {
        // Glizy.template.define('container', '<div class="GlizycmsBlockEdit loading js-glizycmsBlockEdit"></div>');
        Glizy.template.define('glizycms.BlockEdit.items', '<h2>Contenuti</h2>'+
            '<div class="js-sortable">'+
            '<% _.each(blocks, function(item) { %>'+
            '<div class="blockItem">'+
            '<h3><%= item.title %></h3>'+
            '<p><%= item.description %></p>'+
            '<div class="actions">'+
            '<% if (!item.visible) { %><a alt="{i18n:Show}" title="{i18n:Show}" class="js-show" href="#" data-id="<%= item.id %>"><span class="btn-icon icon-eye-close"></span></a><% } %>'+
            '<% if (item.visible) { %><a alt="{i18n:Hide}" title="{i18n:Hide}" class="js-hide" href="#" data-id="<%= item.id %>"><span class="btn-icon icon-eye-open"></span></a><% } %>'+
            '<a alt="{i18n:Edit}" title="{i18n:Edit}" class="js-edit" href="#" data-id="<%= item.id %>"><span class="btn-icon icon-pencil"></span></a>'+
            '<a alt="{i18n:Delete}" title="{i18n:Delete}" class="js-delete" href="#" data-id="<%= item.id %>"><span class="btn-icon icon-trash"></span></a>'+
            '<a alt="{i18n:Duplicate}" title="{i18n:Duplicate}" class="js-duplicate" href="#" data-id="<%= item.id %>"><span class="btn-icon icon-copy"></span></a>'+
            '</div>'+
            '</div>'+
            '<% }); %>'+
            '</div>'+
            '<div class="blockItem blockEmpty">'+
            '<i class="icon-plus"></i>'+
            '<div class="actions">Aggiungi</div>'+
            '</div>');
    };
});
