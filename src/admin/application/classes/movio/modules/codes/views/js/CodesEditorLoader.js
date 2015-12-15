var CodesEditorLoader = new Object();
CodesEditorLoader = {
    start: function()
    {
        //turn to inline mode
        $.fn.editable.defaults.mode = 'inline';
        
        $('.js-addButton').live('click', function( event ) {
            event.preventDefault();
            CodesEditorLoader.addRow();
        });

        $('.js-removeButton').live('click', function( event ) {
            CodesEditorLoader.deleteRow($(this).closest('tr'));
        });

        $('.js-saveButton').live('click', function(event) {
           var saveButton = this;
           $(this).closest('tr').find('a').editable('submit', {
                url: Glizy.ajaxUrl + 'New',
                ajaxOptions: {
                    dataType: 'json' //assuming json response
                },
                success: function(data, config) {
                    if (data) {
                        var id = data.result;
                        //set pk
                        $(this).closest('tr').find('a[data-type]').editable('option', 'pk', id);
                        //remove unsaved class
                        $(this).removeClass('editable-unsaved');
                        $(saveButton).hide();
                        $(this).off('save.newuser');
                        var $link = $(this).last();
                        $link.attr('href','codes/MakeQRCode/'+id);
                        $link.text('Download');
                   }
               }
           });
        });
    },

    load: function()
    {
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "Load",
            dataType: "json",
            success: function (data) {
                var data = data.result;
                CodesEditorLoader.show(data);
            }
        });
    },

    getTemplate: function()
    {
        Handlebars.registerHelper('notNull', function(item, block) {
            return (item !== null) ? block.fn(this) : block.inverse(this);
        });

        Handlebars.registerPartial('item', $('#item').html());

        var source = $('#codes-editor-template').html();

        return Handlebars.compile(source);
    },

    initEditableFields: function(selector) {
        $(selector).editable({
            url: Glizy.ajaxUrl + 'Set',
            emptytext: GlizyLocale.CodesEditorLoader.emptyText
        });
    },
    
    initSelect2: function(selector) {
        $(selector).editable({
            url: Glizy.ajaxUrl + 'Set',
            emptytext: GlizyLocale.CodesEditorLoader.emptyText,
            select2: {
                width: 'element',
                placeholder: '',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: Glizy.ajaxUrl + "&controllerName=org.glizycms.contents.controllers.autocomplete.ajax.PagePicker",
                    dataType: 'json',
                    quietMillis: 100,
                    data: function(term) {
                        return {
                            term: term
                        };
                    },
                    results: function(data, page ) {
                        return { results: data }
                    }
                },
                formatResult: function(data) {
                    return data.text+'<br><small>'+data.path+'</small>';
                },
                formatSelection: function(data) {
                    return data.text;
                }
            }
        });
    },

    show: function(data)
    {
        data.locale = GlizyLocale.CodesEditorLoader;
        
        // setta il locale per ogni item
        $.each(data['items'], function(index, item) {
            item.locale = GlizyLocale.CodesEditorLoader;
        });
        
        var template = CodesEditorLoader.getTemplate();
        var html = template(data);
        $("#admincontent").append(html);

        CodesEditorLoader.initEditableFields('#editTable > tbody a[data-type=text],#editTable > tbody a[data-type=textarea]');
        CodesEditorLoader.initSelect2('.js-resourceLink');
    },

    addRow: function()
    {
        var source = $('#item').html();
        var template = Handlebars.compile(source);
        var item = {
            isNew: true,
            id: null,
            description: null,
            code: null,
            resourceLink: null,
            downloadQrCode: null,
            locale: GlizyLocale.CodesEditorLoader
        };

        var html = template(item);
        $('#editTable tbody').append(html);

        CodesEditorLoader.initEditableFields('#editTable > tbody a[data-type=text],#editTable > tbody a[data-type=textarea]');
        CodesEditorLoader.initSelect2('.js-resourceLink');
    },

    deleteRow: function(row)
    {
        if (confirm(GlizyLocale.CodesEditorLoader.delConfirm)) {
            var id = $(row).find('a:first').data("pk");

            if (id === null) {
               row.remove();
               return;
            }

            $.ajax({
                type: "POST",
                url: Glizy.ajaxUrl + "Del",
                dataType: "json",
                data: {id: id},
                success: function (data) {
                    row.remove();
                }
            });
        }
    },
}
