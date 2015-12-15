var EntityLabelsEditorLoader = new Object();
EntityLabelsEditorLoader = {
    languages: [],
    
    start : function()
    {
        $('#addButton').live('click', function( event ) {
            EntityLabelsEditorLoader.addRow();
        });
        
        $('.-removeButton').live('click', function( event ) {
            EntityLabelsEditorLoader.deleteRow($(this).closest('tr'));
        });
        
        $('.saveButton').live('click', function(event) {
           var saveButton = this;
           $(this).closest('tr').find('a').editable('submit', { 
                url: Glizy.ajaxUrl + 'entityLabel.NewEntityLabel', 
                ajaxOptions: {
                    dataType: 'json' //assuming json response
                },           
                success: function(data, config) {
                    if (data) {
                        var id = data.result;
                        //set pk
                        $(this).closest('tr').find('a').editable('option', 'pk', id);
                        //remove unsaved class
                        $(this).removeClass('editable-unsaved');
                        $(saveButton).hide(); 
                        $(this).off('save.newuser');                     
                   }             
               }
           });
        });
        
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "GetLanguages",
            dataType: "json",
            async: false,
            success: function (data) {
                EntityLabelsEditorLoader.languages = data.result;
            }
        });
    },
    
    loadEntityLabels : function(id)
    {
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "entityLabel.LoadEntityLabels",
            dataType: "json",
            data: {entityId: id},
            success: function (data) {
                var entityLabels = data.result;
                if (entityLabels.length === 0) {
                    entityLabels.entityLabels = [];
                }
                EntityLabelsEditorLoader.showEntityLabels(entityLabels);
            }
        });
    },
    
    getTemplate : function()
    {
        Handlebars.registerHelper('showTranslations', function(entityLabel) {
            var html = "";
            
            $.each(EntityLabelsEditorLoader.languages, function(index, language) {
                var term;
                
                if (entityLabel.translation !== null) {
                    term = entityLabel.translation[language.language_code];
                    term = term !== undefined ? term : null;
                }
                else {
                    term = null;
                }
                
                html += '<td>'
                html += '<a href="#" data-type="text" data-pk="'+entityLabel.id+'" data-name="translation.'+language.language_code+'" data-value="'+term+'"></a>';
                html += '</td>'
            });
            
            return new Handlebars.SafeString(html);
        });
    
        Handlebars.registerHelper('notNull', function(item, block) {
          return (item !== null) ? block.fn(this) : block.inverse(this);
        });
        
        Handlebars.registerPartial('entity-labels', $('#entity-labels').html());
        
        var source = $('#entity-labels-editor-template').html();
        
        return Handlebars.compile(source);
    },
    
    initEditableFields: function(selector) {
        $(selector).editable({
            url: Glizy.ajaxUrl + 'entityLabel.SetEntityLabel',
            emptytext: GlizyLocale.EntityLabelsEditorLoader.emptyText
        });
    },
    
    showEntityLabels : function(entityLabels)
    {
        entityLabels.languages = EntityLabelsEditorLoader.languages;
        entityLabels.locale = GlizyLocale.EntityLabelsEditorLoader;
        
        $.each(entityLabels["entityLabels"], function(index, relation) {
            relation.languages = EntityLabelsEditorLoader.languages;
            relation.locale = GlizyLocale.EntityLabelsEditorLoader;
        });
        
        var template = EntityLabelsEditorLoader.getTemplate();
        var html = template(entityLabels);
        $("#admincontent").append(html);
        
        EntityLabelsEditorLoader.initEditableFields('#editTable > tbody a');
    },
    
    addRow : function()
    {
        var source = $('#entity-labels').html();
        var template = Handlebars.compile(source);
        var entityLabel = {
            isNew: true,
            id: null,
            translation: null,
            languages: EntityLabelsEditorLoader.languages,
            locale: GlizyLocale.EntityLabelsEditorLoader
        };
        
        var html = template(entityLabel);
        $('#editTable tbody').append(html);
        
        EntityLabelsEditorLoader.initEditableFields('#editTable > tbody tr:last a');
        
        /*
        // automatically show next editable
        $('#editTable > tbody tr:last a').on('save.newuser', function(){
            var that = this;
            setTimeout(function() {
                var error = $(that).parents('tr').find('a').editable('validate');
            
                if ($.isEmptyObject(error)) {
                    $(that).closest('tr').find('.saveButton').removeAttr("disabled"); 
                }
                
                $(that).closest('td').next().find('a').editable('show');
            }, 200);
        });
        */
    },
    
    deleteRow : function(row) 
    {
        if (confirm(GlizyLocale.EntityLabelsEditorLoader.relationDelConfirm)) {
            var id = $(row).find('a:first').data("pk");
            
            if (id === null) {
               row.remove();
               return;
            }
            
            $.ajax({
                type: "POST",
                url: Glizy.ajaxUrl + "entityLabel.DelEntityLabel",
                dataType: "json",
                data: {id: id},
                success: function (data) {
                    row.remove();
                }
            });
        }
    },
}
