$.GlizyRegisterType('treeselect', {

    __construct: function () {
        var self = this;
        this.element = $(this);
        this.controllerName = this.element.data("controllername");
        this.lazyLoadUrl = Glizy.ajaxUrl + "&controllerName="+this.controllerName + "&method=lazyLoad";
        this.getPathUrl = Glizy.ajaxUrl + "&controllerName="+this.controllerName + "&method=getPath";

        this.render = function() {
            var self = this;
            if (this.element.data('isInit')!==true) {
                var name = this.element.attr('name');
                this.element.data('isInit', true);
                if (this.element.prop("tagName") == "INPUT") {
                    this.element.attr('type', 'hidden');
                }

                var html = '<div id="'+name+'-tree"></div>';
                this.element.after(html);

                $('#'+name+'-tree').fancytree({
                    source: {
                        url: this.lazyLoadUrl
                        //cache: false
                    },
                    lazyLoad: function(event, data) {
                        var node = data.node;
                        // Issue an ajax request to load child nodes
                        data.result = {
                            url: self.lazyLoadUrl,
                            data: {
                                "key": node.key
                            }
                        }
                    },
                    activate: function(event, data){
                        self.element.val(data.node.key)
                    },
                    init: function(event, data){

                        var key = self.element.val();
                        if (key && key !== "") {
                            // carico il nodo selezionato e tutta la catena per raggiungerlo (array di id)
                            $.ajax({
                                url: self.getPathUrl,
                                data: {
                                    "key": key
                                },
                                success: function(result) {
                                    if (result.status) {
                                        data.tree.loadKeyPath("/"+result.data.join("/"), function(node, status){
                                            if (status === "loaded") {
                                                // traversing node
                                            } else if (status === "ok") {
                                                // node found
                                                node.setExpanded(true);
                                                node.setActive(true);
                                            }
                                        });
                                    }
                                }
                            })
                        }
                    }
                });
            }
        };

        this.render();

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
        this.element.focus();
    }
});
