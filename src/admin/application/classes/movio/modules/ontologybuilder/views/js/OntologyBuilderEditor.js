var OntologyBuilderEditor = new Object();
OntologyBuilderEditor = {
    deletedRows: [],
    entities: [],
    fieldTypes: [],
    relationTypes: [],
    dictionaries: [],
    modules: [],

    start : function()
    {
        $('#addButton').live('click', function( event ) {
            OntologyBuilderEditor.addRow();
        });

        $('#saveButton').live('click', function( event ) {
            OntologyBuilderEditor.saveEntity();
        });

        $('#saveCloseButton').live('click', function( event ) {
            var result = OntologyBuilderEditor.saveEntity(false);
            if (result) {
                history.back();
            }
        });

        $('#cancelButton').live('click', function( event ) {
            history.back();
        });

        $('.-removeButton').live('click', function( event ) {
            OntologyBuilderEditor.deleteRow($(this).closest('.-property'));
        });

        $('.-typeSelector').live('change', function( event ) {
            var selectedType = $("option:selected", this).attr('class');
            var property = $(this).closest('.-property');
            var selectors = [];
            selectors.push( property.find('.-targetSelector') );
            selectors.push( property.find('.-relationShowSelector') );

            if (selectedType == "-field") {
                $.each(selectors, function(index, selector) {
                    selector.hide();

                    selector.prepend('<option></option>');
                    selector.val($('option:first', selector).val());
                });

                property.find('.-relationShowSelectorPanel').hide();
                
                if ($(this).val() === 'attribute.thesaurus') {
                    property.find('.-paramSelectorPanel').show();
                    property.find('.-moduleParamSelectorPanel').hide();
                } else if ($(this).val() === 'attribute.module') {
                    property.find('.-paramSelectorPanel').hide();
                    property.find('.-moduleParamSelectorPanel').show();
                } else {
                    property.find('.-paramSelectorPanel').hide();
                    property.find('.-moduleParamSelectorPanel').hide();
                }
            }
            else {
                var verb = $(':selected', this).text();
                // rimuove il prefisso "Relation: " o "Relazione: "
                verb = verb.substring(verb.indexOf(':')+2);

                var $propertyLabel = $(this).parent().find('.-propertyLabel');
                if ($propertyLabel.select2('val') == '') {
                    $.ajax({
                        type: "POST",
                        url: Glizy.ajaxUrl + "FindEntityLabels",
                        dataType: "json",
                        data: {term: verb},
                        async: false,
                        success: function (data) {
                            var result = data.result;
                            if (result.length == 0) {
                                OntologyBuilderEditor.addTerm($propertyLabel, verb);
                                //$propertyLabel.select2('data', {id: verb, text: verb});
                            } else {
                                $propertyLabel.select2('data', result[0]);
                            }
                        }
                    });
                }

                $.each(selectors, function(index, selector) {
                    selector.show();
                    selector.find(':first-child:empty').remove();
                });

                property.find('.-relationShowSelectorPanel').show();

                property.find('.-paramSelectorPanel').hide();
                property.find('.-moduleParamSelectorPanel').hide();
            }
        });

        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "GetFieldTypes",
            dataType: "json",
            async: false,
            success: function (data) {
                OntologyBuilderEditor.fieldTypes = data.result;
            }
        });

        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "GetRelationTypes",
            dataType: "json",
            async: false,
            success: function (data) {
                OntologyBuilderEditor.relationTypes = data.result;
            }
        });

        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "GetDictionaries",
            dataType: "json",
            async: false,
            success: function (data) {
                OntologyBuilderEditor.dictionaries = data.result;
            }
        });
        
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "GetModules",
            dataType: "json",
            async: false,
            success: function (data) {
                OntologyBuilderEditor.modules = data.result;
            }
        });
    },

    showLoader : function(id)
    {
        id = typeof id !== 'undefined' ? id : "#preloader";
        $(id).css("display", "inline");
    },

    hideLoader : function(id)
    {
        id = (typeof id !== 'undefined') ? id : "#preloader";
        $(id).css("display", "none");
    },

    loadEntity : function(id)
    {
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "LoadEntity",
            dataType: "json",
            data: {entityId: id},
            success: function (data) {
                OntologyBuilderEditor.entities = data.result["entities"];
                OntologyBuilderEditor.showEntity(data.result);
            }
        });
    },

    getTemplate : function()
    {
        Handlebars.registerHelper('selected', function(a, b) {
            return a == b ? 'selected' : '';
        });

        Handlebars.registerHelper('checked', function(value) {
            return value ? 'checked' : '';
        });

        Handlebars.registerHelper('isDisabled', function(value) {
            return value === null ? 'disabled' : '';
        });

        Handlebars.registerHelper('dictionaries', function(target) {
            var html = "";

            $.each(OntologyBuilderEditor.dictionaries, function(index, item) {
                html += '<option '+(target == item.id ? 'selected' : '')+' value="'+item.id+'">'+item.name+'</option>';
            });

            return new Handlebars.SafeString(html);
        });
        
        Handlebars.registerHelper('modules', function(target) {
            var html = "";

            $.each(OntologyBuilderEditor.modules, function(index, item) {
                html += '<option '+(target == item.id ? 'selected' : '')+' value="'+item.id+'">'+item.name+'</option>';
            });

            return new Handlebars.SafeString(html);
        });

        Handlebars.registerHelper('entities', function(target) {
            var html = "";

            $.each(OntologyBuilderEditor.entities, function(index, item) {
                html += '<option '+(target == item.id ? 'selected' : '')+' value="'+item.id+'">'+item.name+'</option>';
            });

            return new Handlebars.SafeString(html);
        });

        Handlebars.registerHelper('fieldTypes', function(type) {
            var html = "";

            $.each(OntologyBuilderEditor.fieldTypes, function(index, item) {
                html += '<option class="-field" '+(type == item.id ? 'selected' : '')+' value="'+item.id+'">'+GlizyLocale.OntologyBuilderEditor.field+': '+item.name+'</option>';
            });

            return new Handlebars.SafeString(html);
        });

        Handlebars.registerHelper('relationTypes', function(type) {
            var html = "";

            $.each(OntologyBuilderEditor.relationTypes, function(index, item) {
                html += '<option class="-relation" '+(type == item.id ? 'selected' : '')+' value="'+item.id+'">'+GlizyLocale.OntologyBuilderEditor.relation+': '+item.name+'</option>';
            });

            return new Handlebars.SafeString(html);
        });

        Handlebars.registerPartial('default-entity-property', $('#default-entity-property').html());
        Handlebars.registerPartial('entity-property', $('#entity-property').html());
        Handlebars.registerPartial('reference-relations-template', $('#reference-relations-template').html());

        var source = $('#entity-template').html();

        return Handlebars.compile(source);
    },

    // aggiunge term al dizionario delle etichette nel db
    addTerm : function(item, term, key)
    {
        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "AddEntityLabel",
            dataType: "json",
            data: {
                text: term,
                key: key
            },
            success: function (data) {
                var newLabel = {
                    id: data.result,
                    text: term
                };

                if (item) {
                    $(item).select2('data', newLabel).select2('close');
                }
            }
        });
    },

    initSelect2 : function(item) {
        $(item).select2({
            minimumInputLength: 2,
            ajax: {
                url: Glizy.ajaxUrl + "FindEntityLabels",
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        term: term
                    };
                },
                results: function(data, page ) {
                    return { results: data.result }
                }
            },
            formatSelection: function(data) {
                if (data.id == '__newTerm') {
                    OntologyBuilderEditor.addTerm(null, data.text, data.key);
                    $(item).select2('data').id = data.key ? data.key : data.text;
                }
                return data.text;
            },
            initSelection : function (element, callback) {
                var id = $(element).attr('value');
                var text = $(element).attr('data-init-text');
                callback({id: id, text: text});
            },
            escapeMarkup: function (m) { return m; },
            createSearchChoice: function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {
                    return {id:'__newTerm', text:term, key: $(item).val()};
                }
            }
        });
    },

    showEntity : function(entity)
    {
        entity['defaultProperties'] = [{
            type: 'attribute.text',
            target: null,
            labelId: 1,
            labelText: 'Title',
            relationShow: null,
            required: true,
            locale: GlizyLocale.OntologyBuilderEditor
        }, {
            type: 'attribute.text',
            target: null,
            labelId: 1,
            labelText: 'Subtitle',
            relationShow: null,
            required: false,
            locale: GlizyLocale.OntologyBuilderEditor
        }, {
            type: 'attribute.text',
            target: null,
            labelId: 1,
            labelText: 'URL',
            relationShow: null,
            required: false,
            locale: GlizyLocale.OntologyBuilderEditor
        }];

        entity.locale = GlizyLocale.OntologyBuilderEditor;

        $.each(entity["properties"], function(index, property) {
            property.locale = GlizyLocale.OntologyBuilderEditor;
        });

        $.each(entity["relations"], function(index, relation) {
            relation.locale = GlizyLocale.OntologyBuilderEditor;
        });

        var template = OntologyBuilderEditor.getTemplate();
        var html = template(entity);
        $("#admincontent").append(html);

        // forza il change dei selettori in base al loro tipo
        $('.-typeSelector').change();

        OntologyBuilderEditor.initSelect2('#entityName');

        $.each(entity["properties"], function(index, property) {
            OntologyBuilderEditor.initSelect2('#propertyLabel'+property["id"]);
        });

        $("#editTable").sortable({
            items: ".-sortable"
        });
    },

    saveEntity : function(async)
    {
        $('#saveButton').attr("disabled", true);
        OntologyBuilderEditor.showLoader();

        var entity = {}

        entity["id"] = $("#editTable").data("id");
        entity["name"] = $("#entityName").select2('data').id;
        
        if (entity["name"] === '') {
            $("#entityName").select2("focus");
            OntologyBuilderEditor.hideLoader();
            $('#saveButton').attr("disabled", false);
            alert(GlizyLocale.OntologyBuilderEditor.alertEmptyEntityName);
            return false;
        }

        entity["showRelationsGraph"] = $("#showRelationsGraph").is(':checked');

        entity["properties"] = [];

        var existsEmptyLabels = false;

        $("#editTable .-property").each( function(index, item) {
            // salta le prime 3 proprietà (title, subtitle e url)
            // che sono obbligatori e non vengono salvati nel db
            if (index >= 3) {
                var property = {}
                property["id"] = $(this).data("id");
                property["type"] = $('.-typeSelector', this).val();
                property["target"] = $('.-targetSelector', this).val();
                property["label"] = $('.-propertyLabel', this).select2('data') ? $('.-propertyLabel', this).select2('data').id : '';
                property["relationShow"] = $('.-relationShowSelector', this).val();
                property["dcField"] = $('.-dcFieldSelector', this).val();
                property["required"] = $('.-fieldRequired', this).is(':checked');
                property["showLabelInFrontend"] = $('.-showLabelInFrontend', this).is(':checked');
                property["rowIndex"] = index - 2;

                if (property["type"] === 'attribute.thesaurus') {
                    property["params"] = $('.-paramSelector', this).val();
                } else if (property["type"] === 'attribute.module') {
                    property["params"] = $('.-moduleParamSelector', this).val();
                }

                entity["properties"].push(property);

                if (property["label"] === '') {
                    if (!existsEmptyLabels) {
                        $('.-propertyLabel', this).select2("focus");
                    }
                    existsEmptyLabels = true;
                }
            }
        });

        if (existsEmptyLabels) {
            alert(GlizyLocale.OntologyBuilderEditor.alertEmptyLabels);
            $('#saveButton').attr("disabled", false);
            OntologyBuilderEditor.hideLoader();
            return false;
        }

        entity["relations"] = [];

        $("#reference-relations > tbody > tr").each( function(index, item) {
            var relation = {}
            relation["id"] = $(this).data("id");
            relation["show"] = $('.-relationDisplaySelector', this).val();

            entity["relations"].push(relation);
        });

        entity["deletedRows"] = OntologyBuilderEditor.deletedRows;

        $.ajax({
            type: "POST",
            url: Glizy.ajaxUrl + "SaveEntity",
            dataType: "json",
            data: {entity: entity},
            async: async,
            success: function (data) {
                $('#saveButton').attr("disabled", false);
                OntologyBuilderEditor.hideLoader();
                OntologyBuilderEditor.deletedRows = [];
                var returnEntity = data.result;

                if ($("#editTable").data("id") === '') {
                    $("#editTable").data("id", returnEntity["id"]);
                }

                var i = 0;

                // aggiorna gli id delle nuove righe create
                $("#editTable .-property").each( function() {
                    var id = $(this).data("id");
                    if (id === '') {
                        $(this).data("id", returnEntity["newProperties"][i++]);
                    }
                });

                Glizy.events.broadcast("glizy.message.showSuccess", {"title": GlizyLocale.OntologyBuilderEditor.entitySavedMsg, "message": ""});
            },
            error: function() {
                Glizy.events.broadcast("glizy.message.showError", {"title": GlizyLocale.OntologyBuilderEditor.entitySavedError, "message": ""});
            }
        });

        return true;
    },

    deleteRow : function(row)
    {
        if (confirm(GlizyLocale.OntologyBuilderEditor.propertyDelConfirm)) {
            var id = $(row).data("id");
            OntologyBuilderEditor.deletedRows.push(id);
            row.remove();
        }
    },

    addRow : function()
    {
        var source = $('#entity-property').html();
        var template = Handlebars.compile(source);
        var property = {
            type: null,
            target: null,
            labelId: null,
            labelText: null,
            relationShow: null,
            showLabelInFrontend: true,
            locale: GlizyLocale.OntologyBuilderEditor,
            params: null
        }
        var html = template(property);
        $('#editTable').append(html);

        OntologyBuilderEditor.initSelect2('.-propertyLabel:last');
        $(".-propertyLabel:last").select2('data', null);

        $('.-typeSelector:last').change();
    }
}
