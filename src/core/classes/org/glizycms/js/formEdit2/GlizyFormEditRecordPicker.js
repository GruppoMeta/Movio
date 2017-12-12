Glizy.oop.declare("glizy.FormEdit.recordpicker", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),

    data: [],

    container: null,

    previewUrl: null,

    eventPos: null,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;

        this.data = [];
        this.container = null;
        this.previewUrl = this.$element.data('preview_url');

        /*console.log(this.$element.val())
        var value = this.$element.val();
        if (value) {
            this.data = typeof(value) === 'string' ? JSON.parse(value) : value;
        }
*/
        this.templateDefine();
        this.render();
        this.addEvents();
    },

    render: function() {
        if (!this.container) {
            this.container = $(Glizy.template.render('glizyformedit.recordpicker.container', {})).insertAfter(this.$element);
            this.$element.hide();
        }

        var html = Glizy.template.render('glizyformedit.recordpicker.items', {data: this.data, previewUrl: this.previewUrl});
        this.container.html($(html));

        this.container.find('.js-sortable').sortable({
            'stop': Glizy.responder(this, this.onChange)
        });
        
        this.storeValue();
    },

    addEvents: function() {
        var self = this;

        var container = $(this.container);

        container.on('click', 'div.js-add', function(e){
            e.preventDefault();
            self.onOpenPicker();
        });

        container.on('click', 'a.js-delete', function(e){
            e.preventDefault();
            self.onDelete(e);
        });

        container.on('click', 'a.js-preview', function(e){
            e.preventDefault();
            self.onPreview(e);
        });

    },

    onOpenPicker: function() {
        Glizy.openIFrameDialog( this.$element.attr('title'),
            this.$element.data('picker_url'),
            1400,
            50,
            50,
            null,
            Glizy.responder(this, this.disposeEvent));
        this.eventPos = Glizy.events.on("recordsPicker.set", Glizy.responder(this, this.onPickerSetValue));
    },

    onDelete: function(e) {
        this.data.splice($(e.currentTarget).data('pos'), 1);
        this.render();
    },

    onPreview: function(e) {
        var url = this.previewUrl.replace('##ID##', $(e.currentTarget).data('id'));
        this.open(url, '_blank');
    },

    disposeEvent: function() {
        if (this.eventPos !== null) {
            Glizy.events.unbind("recordsPicker.set", this.eventPos);
        }
        this.eventPos = null;
    },

    onPickerSetValue: function(event) {
        this.disposeEvent();
        Glizy.closeIFrameDialog(true);
        _.each(event.message, Glizy.responder(this, this.pushData));
        this.render();
    },

    onChange: function(event, ui) {
        var self = this;

        var newData = [];
        this.container.find('.js-delete').each(function(i, el) {
            newData.push(self.data[$(el).data('pos')]);
        });

        this.data = newData;
        this.render();
    },

    pushData: function(item){
        if (_.findWhere(this.data, {id: item.id})===undefined) {
            this.data.push(item);
        }
    },

    storeValue: function() {
        this.$element.val(JSON.stringify(this.data));
    },

    setValue: function (value) {
        this.$element.val(value);
        if (value) {
            this.data = typeof(value) === 'string' ? JSON.parse(value) : value;
        }
        this.render();
    },

    templateDefine: function() {
        Glizy.template.define('glizyformedit.recordpicker.container',
            '<div class="glizy-formedit-recordpicker">'+
            '</div>');

        Glizy.template.define('glizyformedit.recordpicker.items',
            '<div class="js-sortable">'+
            '<% _.each(data, function(item, index) { %>'+
            '<div class="blockItem">'+
            '<h3><%= item.title %></h3>'+
            '<div class="actions">'+
            '<% if (previewUrl) { %>'+
            '<a title="<%= GlizyLocale.FormEdit.preview %>" class="js-preview" href="#" data-id="<%= item.id %>"><span class="btn-icon fa fa-eye icon-eye-open"></span></a>'+
            '<% } %>'+
            '<a title="<%= GlizyLocale.FormEdit.remove %>" class="js-delete" href="#" data-pos="<%= index %>"><span class="btn-icon fa fa-trash icon-trash"></span></a>'+
            '</div>'+
            '</div>'+
            '<% }); %>'+
            '</div>'+
            '<div class="blockItem blockEmpty js-add">'+
            '<i class="icon-plus"></i>'+
            '<div class="actions"><%= GlizyLocale.FormEdit.add %></div>'+
            '</div>');
    }
});
