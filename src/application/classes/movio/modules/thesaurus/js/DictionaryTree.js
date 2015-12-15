var DictionaryTree = dejavu.Class.declare({
    treeId: null,
    tree: null,
    ajaxUrl: null,
    dictionaryId: null,
    treeOffset: null,

    initialize: function(treeId, addPageId) {
        var self = this;
        this.treeId = treeId;
        this.tree = $(this.treeId);
        this.ajaxUrl = this.tree.data('ajaxurl');
        this.lastAddPageLink = '';

        // initialize the tree
        this.tree.jstree({
            // List of active plugins
            "plugins" : [ "themes","json_data","ui","crrm","cookies","dnd","types","contextmenu"],
            "json_data" : {
                "ajax" : {
                    "url" : this.ajaxUrl+"GetChildren",
                    "data" : function (n) {
                        self.onResize();
                        return {
                            "termId" : n.attr ? n.attr("id").replace("node_","") : 0
                        };
                    }
                }
            },
            "crrm" : {
                "move" : {
                    "check_move" : function (m) {
                        //console.log(m)
                        var targetId = parseInt(m.np.attr('id'));
                        return targetId && m.o.attr('id') != m.np.attr('id') && m.np.attr('id') != m.op.attr('id');
                    }
                }
            },
            "dnd" : {
                "drop_target" : false,
                "drag_target" : false
            },
            "types" : {
               "types" : {
                    "root" : {
                        "select_node" : function () {return false;}
                    }
                }
            },
            "cookies" : {
                "save_selected" : false
            },
            "contextmenu" : {
                "items" : this.customMenu
            }
        })
        .bind("select_node.jstree", this.onNodeSelect)
        .bind("remove.jstree", this.onNodeRemove)
        .bind("move_node.jstree", this.onNodeMove)
        .bind("loaded.jstree", this.onTreeLoaded);
        this.tree.css("overflow", "auto");
        this.treeOffset = this.tree.offset();

        // add page button
        $(addPageId).click(this.onAddPageClick);

        // Listen events
        Glizy.events.on("thesaurus.termAdded", this.onPageAdded);
        Glizy.events.on("glizycms.refreshTree", this.onRefreshTree);
        Glizy.events.on("glizycms.renameTitle", this.onRefreshTree);
        Glizy.events.on("glizycms.treeCallAjaxForMenu", this.onTreeAjaxCall);

        $(window).resize(this.onResize);
        this.onResize();
    },

    onResize: function (event) {
        var h = $(window).height() - this.treeOffset.top;
        this.tree.height(h);
    }.$bound(),

    onTreeLoaded: function (event, data) {
        this.tree.jstree('open_all');
        //this.tree.jstree('check_all');
    }.$bound(),

    /**
     * Tree node selected, dispatch the event with the menuId param
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeSelect: function (event, data) {
        this.lastAddPageLink = '';
        if (data.rslt.e) {
            Glizy.events.broadcast("thesaurus.termEdit", {"termId": data.rslt.obj.attr("id")});
        }
    }.$bound(),

    /**
     * Tree node delete, send a ajax request to delete the node,
     * after deleted the tree is refreshed.
     * The ajax call Delete command with:
     *    menuId: id to delete
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeRemove: function (event, data) {
        var self = this;
        //var treeContainer = $.jstree._reference(this.treeId).get_container();
        //var count =treeContainer.find("li").length;
        data.rslt.obj.each(function () {
            $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+"DeleteTerm",
                data : {
                    "termId" : this.id
                },
                success : function (r) {
                    if(!r.status) {
                        data.inst.refresh();
                    }
                    Glizy.events.broadcast("thesaurus.termAdd", {"href": ''});
                }
            });
        });
    }.$bound(),

    /**
     * Tree node move, send a ajax request to move the node,
     * after the mode the tree is refreshed.
     * The ajax call Move command with:
     *    menuId: id to move
     *    parentId: id of parent node
     *    position: a new position
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeMove: function (event, data) {
        var self = this;
        data.rslt.o.each(function (i) {
            $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+"MoveTerm",
                data : {
                    "termId" : $(this).attr("id"),
                    "parentId" : data.rslt.cr === -1 ? 0 : data.rslt.np.attr("id"),
                },
                success : function (r) {
                    if(!r.status) {
                        $.jstree.rollback(data.rlbk);
                    }
                    else {
                        $(data.rslt.oc).attr("id", "node_" + r.id);
                        if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                }
            });
        });
    }.$bound(),

    onAddPageClick: function(e){
        e.preventDefault();
        self.lastAddPageLink = e.currentTarget.href+(e.currentTarget.href.indexOf('?') > -1 ? '&' : '?')+'parentId='+this.tree.jstree('get_selected').attr("id");
        Glizy.events.broadcast("thesaurus.termAdd", {"href": self.lastAddPageLink});
        $("#js-glizycmsPageEdit").on('load', function(e){
            $frame = $("#js-glizycmsPageEdit").contents();
            if($('#parentId option', $frame).size() < 2) {
                $("#parentId", $frame).prop('disabled', 'disabled');
            }
        })
    }.$bound(),

    onPageAdded: function(e){
        Glizy.events.broadcast("thesaurus.termAdd", {"href": self.lastAddPageLink});
        var node = this.tree.find("#"+e.message.parentId);
        var tree = jQuery.jstree._reference(this.treeId);
        tree.refresh(node);
        Glizy.events.broadcast("glizy.message.showSuccess", {"title": "{i18n:Data saved correctly}", "message": ""});
    }.$bound(),


    onRefreshTree: function(e){
        var tree = jQuery.jstree._reference(this.treeId);
        var currentNode = tree._get_node(null, false);
        var parentNode = tree._get_parent(currentNode);
        tree.refresh(currentNode);
        tree.open(currentNode);
    }.$bound(),

    onTreeAjaxCall: function(e){
        var self = this;
        $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+e.message.action,
                data : {
                    "termId" : e.message.termId
                },
                success : function (r) {
                    Glizy.events.broadcast("glizycms.refreshTree");
                }
            });
    }.$bound(),

    customMenu: function(node) {
        var self = this;
        var menu = {};
        if (node.data("edit") == "1") {
            menu.modify = {
                "label": "{i18n:Edit}",
                "icon": "icon-pencil",
                action : function(obj) {
                    Glizy.events.broadcast("thesaurus.termEdit", {"termId": obj.attr("id")});
                }
            };
        }

        if (node.data("delete") == "1") {
            menu.remove = {
                "label": "{i18n:Delete}",
                "icon": "icon-remove",
                "action":  function(obj) {
                    Glizy.confirm("{i18n:thesaurus.confirm.termDelete}", [], function(success){
                        if (success) {
                            self.remove(obj);
                        }
                    });
                }
            };
        }
        return menu;
    }
});