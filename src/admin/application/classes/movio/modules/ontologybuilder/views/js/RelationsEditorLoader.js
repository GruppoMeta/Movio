var RelationsEditorLoader = new Object();
RelationsEditorLoader = {
    languages: [],

    start : function()
    {
        $('.js-addButton').live('click', function( event ) {
            event.preventDefault();
            RelationsEditorLoader.addRow();
        });

        $('.js-removeButton').live('click', function( event ) {
            RelationsEditorLoader.deleteRow($(this).closest('tr'));
        });

        $('.js-saveButton').live('click', function(event) {
           var saveButton = this;
           $(this).closest('tr').find('a').editable('submit', {
                url: Glizy.ajaxUrl + 'relation.NewRelation',
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
                RelationsEditorLoader.languages = data.result;
            }
        });
    },

    loadRelations : function(id)
    {
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "relation.LoadRelations",
            dataType: "json",
            data: {entityId: id},
            success: function (data) {
                var relations = data.result;
                if (relations.length === 0) {
                    relations.relations = [];
                }
                RelationsEditorLoader.showRelations(relations);
            }
        });
    },

    getTemplate : function()
    {
        Handlebars.registerHelper('showTranslations', function(relation) {
            var html = "";

            $.each(RelationsEditorLoader.languages, function(index, language) {
                var term;

                if (relation.translation !== null) {
                    term = relation.translation[language.language_code];
                    term = term !== undefined ? term : null;
                }
                else {
                    term = null;
                }

                html += '<td>'
                html += '<a href="#" data-type="text" data-pk="'+relation.id+'" data-name="translation.'+language.language_code+'" data-value="'+term+'"></a>';
                html += '</td>'
            });

            return new Handlebars.SafeString(html);
        });

        Handlebars.registerHelper('notNull', function(item, block) {
            return (item !== null) ? block.fn(this) : block.inverse(this);
        });

        Handlebars.registerPartial('relation', $('#relation').html());

        var source = $('#relations-editor-template').html();

        return Handlebars.compile(source);
    },

    initEditableFields: function(selector) {
        $(selector).editable({
            url: Glizy.ajaxUrl + 'relation.SetRelation',
            emptytext: GlizyLocale.RelationsEditorLoader.emptyText
        });
    },

    showRelations : function(relations)
    {
        relations.languages = RelationsEditorLoader.languages;
        relations.locale = GlizyLocale.RelationsEditorLoader;

        $.each(relations["relations"], function(index, relation) {
            relation.languages = RelationsEditorLoader.languages;
            relation.locale = GlizyLocale.RelationsEditorLoader;
        });

        var template = RelationsEditorLoader.getTemplate();
        var html = template(relations);
        $("#admincontent").append(html);

        RelationsEditorLoader.initEditableFields('#editTable > tbody a');
    },

    addRow : function()
    {
        var source = $('#relation').html();
        var template = Handlebars.compile(source);
        var relation = {
            isNew: true,
            id: null,
            translation: null,
            cardinality: 0,
            languages: RelationsEditorLoader.languages,
            locale: GlizyLocale.RelationsEditorLoader
        };

        var html = template(relation);
        $('#editTable tbody').append(html);

        RelationsEditorLoader.initEditableFields('#editTable > tbody tr:last a');

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
        if (confirm(GlizyLocale.RelationsEditorLoader.relationDelConfirm)) {
            var id = $(row).find('a:first').data("pk");

            if (id === null) {
               row.remove();
               return;
            }

            $.ajax({
                type: "POST",
                url: Glizy.ajaxUrl + "relation.DelRelation",
                dataType: "json",
                data: {id: id},
                success: function (data) {
                    row.remove();
                }
            });
        }
    },
}
