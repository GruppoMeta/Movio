Glizy.oop.declare("glizy.FormEdit", {
    formId: null,
    $form: null,
    glizyOpt: null,
    invalidFields: 0,
    customValidationInvalid: false,
    lang: null,
    fields: [],
    formDataJSON: "{}",

    $statics: {
        fieldTypes: [],
        registerType: function (name, object) {
            this.fieldTypes[name] = object;
        }
    },

    getCurrentFormData: function() {
        var formData = {};

        this.fields.forEach(function(field) {
            if (!field.isDisabled()) {
                formData[field.getName()] = field.getValue();
            }
        });

        return formData;
    },

    hasUnmodifiedData: function() {
        return _.isEmpty(this.glizyOpt.formData) || this.formDataJSON == JSON.stringify(this.getCurrentFormData());
    },

    updateFormData: function () {
        this.formDataJSON = JSON.stringify(this.getCurrentFormData());
    },

    initialize: function(formId, glizyOpt) {
        var self = this;

        this.formId = formId;
        this.glizyOpt = glizyOpt;
        this.$form = $('#'+this.formId);
        this.$form.data('instance', this);
        this.lang = glizyOpt.lang;
        this.readOnly = glizyOpt.readOnly;

        $('#'+this.formId+' input[name]:not( [type="button"], [type="submit"], [type="reset"] ), '+
          '#'+this.formId+' textarea[name], '+
          '#'+this.formId+' select[name]').each(function () {
            self.createField(this);
        });

        // non unire alla selezione precedente, altrimenti gli input nel fieldset custom vengono
        // assegnati anche al formedit
        $('#'+this.formId+' fieldset[data-type]').each(function () {
            self.createField(this);
        });

        self.verifySelectWithTarget(this.$form);

        $('.js-glizycms-save').click(function (e) {
            self.setFormButtonStates(false);
            e.preventDefault();
            self.save(e.currentTarget, true, $(this));
        });

        $('.js-glizycms-cancel').click(function (e) {
            self.setFormButtonStates(false);
            window.onbeforeunload = null;
        });

        $('.js-glizycms-save-novalidation').click(function (e) {
            $.each(self.fields, function (index, obj) {
                obj.removeClass('GFEValidationError');
                obj.getElement().closest('.control-group').removeClass('GFEValidationError');
            });
            self.setFormButtonStates(false);
            e.preventDefault();
            self.save(e.currentTarget, false, $(this));
        });

        this.initValidator();

        // aggangia anche l'evento submit per permettere la validazione dei campi
        this.$form.submit(function(event){
            if (self.$form.triggerHandler('submitForm') === false && this.invalidFields || this.customValidationInvalid) {
                self.customValidationInvalid = false;
                return false;
            } else {
                return true;
            }
        });

        window.setTimeout( //2s perché ci mette "un po'" ad aggiornare tutti i campi
            function(){
                self.updateFormData();
                window.onbeforeunload = function exitWarning(e) {
                    if (!self.hasUnmodifiedData()) {
                        var msg = GlizyLocale.FormEdit.discardConfirmation;
                        e = e || window.event;
                        // For IE and Firefox prior to version 4
                        if (e) {
                            e.returnValue = msg;
                        }
                        // For Safari
                        return msg;
                    }
                };
            },
            2000
        );

        Glizy.events.broadcast("glizycms.formEdit.onReady");
    },

    verifySelectWithTarget: function($container) {
        var self = this;
        $container.find('select').each(function () {
            if (self.isSubComponent($(this))) {
                return;
            }
            var target = $(this).data('target');
            if ( target ) {
                $(this).change(function(e){
                    var sel = this.selectedIndex,
                        states = $(this).data("val_"+sel),
                        stateMap = {};
                    var t = target.split(",");
                    states = states.split(",");

                    $(t).each(function(index, val) {
                        stateMap[val] = states[index]==="1";

                        if (stateMap[val]) {
                            $container.find("#"+val).show().find("[name]").data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else {
                            $container.find("#"+val).hide().find("[name]").data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });

                    $container.find("[name]").each(function(){
                        var $el = $(this);
                        var state = stateMap[$el.attr("name")];
                        if (state===true) {
                            $el.data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else if (state===false) {
                            $el.data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });
                });
                $(this).trigger("change");
            }
        });
    },

    setFormButtonStates: function(state) {
        if (state) {
            $('.js-glizycms-save').removeAttr('disabled');
            $('.js-glizycms-save-novalidation').removeAttr('disabled');
            $('.js-glizycms-cancel').removeAttr('disabled');
            $('.js-glizycms-preview').removeAttr('disabled');
        } else {
            $('.js-glizycms-save').attr('disabled', 'disabled');
            $('.js-glizycms-save-novalidation').attr('disabled', 'disabled');
            $('.js-glizycms-cancel').attr('disabled', 'disabled');
            $('.js-glizycms-preview').attr('disabled', 'disabled');
        }
    },

    // restituisce true se l'elemento è contenuto in un altro componente
    isSubComponent: function(element) {
        // se l'elemento è contenuto in altri tipi contenitori
        if ($(element).parents('[data-type]').length !== 0) {
            return true;
        } else {
            return false;
        }
    },

    createField: function(element) {
        if (this.isSubComponent(element)) {
            return;
        }

        var type = $(element).data('type') || 'standard';
        var obj = Glizy.oop.create("glizy.FormEdit."+type, $(element), this.glizyOpt, this.$form);
        if (obj) {
        var value = this.glizyOpt.formData[obj.getName()];
            if (value !== undefined) {
                obj.setValue(value);
            }

            this.fields.push(obj);
        }
    },

    initValidator: function() {
        var self = this;
        var firstInvalidObj = null;

        function testInvalidation(obj) {
            if (obj && !obj.isValid() && obj.getElement().is(":visible")) {
                obj.addClass('GFEValidationError');
                obj.getElement().closest('.control-group').addClass('GFEValidationError');
                self.invalidFields++;
            }
        }

        function testValidation(obj) {
            if (obj && obj.isValid()) {
                obj.removeClass('GFEValidationError');
                obj.getElement().closest('.control-group').removeClass('GFEValidationError');
            }
        }

        self.$form.validVal({
            validate: {
                fields: {
                    hidden: true
                }
            },
            fields: {
                onInvalid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    testInvalidation(obj);
                },
                onValid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    testValidation(obj);
                    testInvalidation(obj);
                }
            },
            form: {
                onValidate: function () {
                    var error, fieldVals = {};

                    firstInvalidObj = null;

                    $('#'+self.formId+' fieldset[data-type]').each(function () {

                        // se l'elemento è contenuto in altro componente
                        if (self.isSubComponent($(this))) {
                            return;
                        }

                        var obj = $(this).data('instance');
                        if (!obj.isValid()) {
                            obj.addClass('GFEValidationError');
                            obj.getElement().closest('.control-group').addClass('GFEValidationError');
                            if (!self.customValidationInvalid) {
                                firstInvalidObj = obj;
                            }
                            self.customValidationInvalid = true;
                        } else {
                            obj.removeClass('GFEValidationError');
                            obj.getElement().closest('.control-group').removeClass('GFEValidationError');
                        }
                    });

                    if (self.glizyOpt.customValidation && typeof(window[self.glizyOpt.customValidation]) == 'function') {
                        jQuery(this).find('input:not( [type="button"], [type="submit"], [type="reset"] ), textarea, select').each(function () {
                            if (this.name) fieldVals[this.name] = jQuery(this).val();
                        });
                        if (error = window[self.glizyOpt.customValidation](fieldVals)) {
                            alert(error);
                            Glizy.events.broadcast("glizy.message.showError", {"title": error, "message": ""});
                            self.customValidationInvalid = true;
                        }
                    }
                },
                onInvalid: function( field_arr, language ) {
                    var $invalidEl = field_arr.first();
                    if (!$invalidEl.is(":visible")) {
                        return true;
                    }

                    var obj = $invalidEl.data('instance');
                    obj.focus();

                    self.invalidFields = $invalidEl.length;

                    $invalidEl.addClass('GFEValidationError');
                    $invalidEl.closest('.control-group').addClass('GFEValidationError');

                    if (!self.customValidationInvalid) {
                        var inTab = $invalidEl.closest('div.tab-pane');
                        if (inTab.length) {
                            $('a[data-target="#'+inTab.attr('id')+'"]').tab('show');
                        }
                    }
                },

                onValid: function() {
                    if (self.customValidationInvalid && firstInvalidObj) {
                        firstInvalidObj.focus();
                    }
                }
            }
        });
    },

    save: function (el, enableValidation, $saveButton) {
        var formData = this.getCurrentFormData();
        var self = this;

        if (enableValidation) {
            self.$form.triggerHandler('submitForm');

            if (self.invalidFields || self.customValidationInvalid) {
                self.customValidationInvalid = false;
                self.setFormButtonStates(true);
                self.invalidFields = 0;
                Glizy.events.broadcast("glizy.message.showError", {"title": self.lang.errorValidationMsg, "message": ""});
                return;
            }
        }

        var triggerAction = $(el).data("trigger");

        // return;

        jQuery.ajax(this.glizyOpt.AJAXAction, {
            data: jQuery.param({action: $(el).data("action"), data: JSON.stringify(formData)}),
            type: "POST",
            success: function (data) {
                if (data.errors) {

                    // TODO localizzare
                    var errorMsg = '<p>' + GlizyLocale.FormEdit.unableToSave + '</p><ul>';
                    $.each(data.errors, function(id, value) {
                        errorMsg += '<li><p class="alert alert-error">'+value+'</p></li>';
                    });
                    Glizy.events.broadcast("glizy.message.showError", {"title": self.lang.errorValidationMsg, "message": errorMsg});

                } else {

                    if (data.evt) {

                        window.parent.Glizy.events.broadcast(data.evt, data.message);
                    } else if (data.url) {

                        if (data.target == 'window') {
                            parent.window.location.href = data.url;
                        } else {
                            document.location.href = data.url;
                        }

                    } else if (data.set) {

                        $.each(data.set, function(id, value){
                            $('#'+id).val(value);
                        });
                        Glizy.events.broadcast("glizy.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                        if (triggerAction) {
                            triggerAction('click', formData);
                        }

                    } else if (data.callback) {

                        window[data.callback](data);

                    } else {

                        if (triggerAction) {
                            triggerAction('click', formData);
                        } else {
                            Glizy.events.broadcast("glizy.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                        }

                    }

                    self.updateFormData();
                }

                self.setFormButtonStates(true);
            }
        });
    }
});


