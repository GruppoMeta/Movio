var GlizycmsSiteTree = dejavu.Class.declare({
    treeId: null,
    tree: null,
    ajaxUrl: null,
    treeOffset: null,

    initialize: function(treeId, addPageId) {
        var self = this;
        this.treeId = treeId;
        this.tree = $(this.treeId);
        this.ajaxUrl = this.tree.data('ajaxurl');
        
        // initialize the tree
        this.tree.jstree({
            // List of active plugins
            "plugins" : [ "themes","json_data","ui","crrm","cookies","dnd","types", "checkbox" ],
            "json_data" : {
                "ajax" : {
                    "url" : this.ajaxUrl+"GetSiteTree",
                    "data" : function (n) {
                        self.onResize();
                        return {
                            "id" : n.attr ? n.attr("id").replace("node_","") : 0
                        };
                    }
                }
            },
            "cookies" : {
                "save_selected" : false
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
        Glizy.events.on("glizycms.pageAdded", this.onPageAdded);
        Glizy.events.on("glizycms.refreshTree", this.onRefreshTree);
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
        this.tree.jstree('check_all');
    }.$bound(),

    /**
     * Tree node selected, dispatch the event with the menuId param
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeSelect: function (event, data) {
        if (data.rslt.e) {
            Glizy.events.broadcast("glizycms.pageEdit", {"menuId": data.rslt.obj.attr("id")});
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
        data.rslt.obj.each(function () {
            $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+"Delete",
                data : {
                    "menuId" : this.id.replace("node_","")
                },
                success : function (r) {
                    if(!r.status) {
                        data.inst.refresh();
                    }
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
                url: self.ajaxUrl+"Move",
                data : {
                    "menuId" : $(this).attr("id").replace("node_",""),
                    "parentId" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""),
                    "position" : data.rslt.cp + i
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
        Glizy.events.broadcast("glizycms.pageAdd", {"href": e.currentTarget.href});
    }.$bound(),

    onPageAdded: function(e){
        Glizy.events.broadcast("glizycms.pageEdit", {"menuId": e.message.menuId});
        var node = this.tree.find("#"+e.message.parentId);
        var tree = jQuery.jstree._reference(this.treeId);
        tree.refresh(node);
    }.$bound(),

    onRefreshTree: function(e){
        var tree = jQuery.jstree._reference(this.treeId);
        var currentNode = tree._get_node(null, false);
        var parentNode = tree._get_parent(currentNode);
        tree.refresh(currentNode);
    }.$bound(),

    onTreeAjaxCall: function(e){
        var self = this;
        $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+e.message.action,
                data : {
                    "menuId" : e.message.menuId
                },
                success : function (r) {
                    Glizy.events.broadcast("glizycms.refreshTree");
                }
            });
    }.$bound(),
});