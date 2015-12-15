Glizy.oop.declare("glizy.FormEdit.tinymce", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    elementId: null,
    editor: null,
    
    $statics: {
        id: 0
    },
    
    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        
        if (!element.attr('id')) {
            this.$self.id++;
            this.elementId = element.attr('name') + this.$self.id;
            element.attr('id', this.elementId);
        } else {
            this.elementId = element.attr('id');
        }
        
		var options = Glizy.tinyMCE_options;
        
		options.mode = "exact";
		options.elements = this.elementId;
		options.document_base_url = Glizy.tinyMCE_options.urls.root;
		tinyMCE.init( options );
    },
    
    save: function () { 
        return tinyMCE.get(this.elementId).save();
    },
    
    getValue: function () {
        return tinyMCE.get(this.elementId).getContent();
    },
    
    setValue: function (value) {
        this.$element.val(value);
    },
    
    getName: function () {
        return this.$element.attr('name');
    },
    
    focus: function()
    {   
        $('html, body').animate({ scrollTop: this.$element.parent().offset().top - this.$element.parent().prop('scrollHeight') }, 'slow');
    },
    
    destroy: function() {
    },
    
    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },
    
    addClass: function(className) {
        var container = tinyMCE.get(this.elementId).getContainer();
        $(container).find('.mceLayout').addClass(className);
    },
    
    removeClass: function(className) {
        var container = tinyMCE.get(this.elementId).getContainer();
        $(container).find('.mceLayout').removeClass(className);
    }
});