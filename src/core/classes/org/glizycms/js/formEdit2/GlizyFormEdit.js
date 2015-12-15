Glizy.oop.declare("glizy.FormEdit", {
    formId: null,
    $form: null,
    glizyOpt: null,
    invalidFields: 0,
    customValidationInvalid: false,
    lang: null,
    fields: [],

    $statics: {
        fieldTypes: [],
        registerType: function (name, object) {
            this.fieldTypes[name] = object;
        }
    },

    initialize: function(formId, glizyOpt) {
        this.formId = formId;
        this.glizyOpt = glizyOpt;
        this.$form = $('#'+this.formId);
        this.lang = glizyOpt.lang;

        var self = this;

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

        jQuery('.js-glizycms-save').click(function (e) {
            $(this).attr("disabled", "disabled");
            e.preventDefault();
            self.save(e.currentTarget, $(this));
        });

        this.enableValidation();

        // aggangia anche l'evento submit per permettere la validazione dei campi
        this.$form.submit(function(event){
            if (self.$form.triggerHandler('submitForm') === false && this.invalidFields || this.customValidationInvalid) {
                self.customValidationInvalid = false;
                return false;
            } else {
                return true;
            }
        })
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

    enableValidation: function () {
        var self = this;
        var firstInvalidObj = null;

        self.$form.validVal({
            validate: {
                fields: {
                    hidden: true
                }
            },
            fields: {
                onValidate: function ($form, language) {
                    var obj = $(this).data('instance');
                    
                    if (obj && !obj.isValid()) {
                        obj.addClass('GFEValidationError');
                        obj.getElement().closest('.control-group').addClass('GFEValidationError');
                        self.invalidFields++;
                        return false;
                    }
                },
                onInvalid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    if (obj && !obj.isValid()) {
                        obj.addClass('GFEValidationError');
                        obj.getElement().closest('.control-group').addClass('GFEValidationError');
                    }
                },
                onValid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    if (obj && obj.isValid())  {
                        obj.removeClass('GFEValidationError');
                        obj.getElement().closest('.control-group').removeClass('GFEValidationError');
                    }
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
                    /*
                    var $invalidEl = self.filterEditingFields.call(field_arr);

                    if ($invalidEl.length) {
                        if ($invalidEl.attr('data-type')) {
                            var obj = $invalidEl.data('instance');
                            obj.focus();
                        } else {
                            $invalidEl.focus();
                        }

                        $invalidEl.addClass('GFEValidationError');
                        alert(self.lang.errorValidationMsg);
                    }
                    */
                },
                onValid: function() {
                    if (self.customValidationInvalid && firstInvalidObj) {
                        Glizy.events.broadcast("glizy.message.showError", {"title": self.lang.errorValidationMsg, "message": ""});
                        firstInvalidObj.focus();
                    }
                }
            }
        });
    },

    save: function (el, $saveButton) {
        var formData = {};
        var result = this.$form.triggerHandler('submitForm');

        if (result === false || this.invalidFields || this.customValidationInvalid) {
            this.customValidationInvalid = false;
            $saveButton.removeAttr("disabled");
            this.invalidFields = 0;
            Glizy.events.broadcast("glizy.message.showError", {"title": this.lang.errorValidationMsg, "message": ""});
            return;
        }

        var self = this;

        this.fields.forEach(function(field) {
            if (!field.isDisabled()) {
                var val = field.getValue();
                formData[field.getName()] = val;
            }
        });


        var triggerAction = $(el).data("trigger");

        // return;

        jQuery.ajax(this.glizyOpt.AJAXAction, {
            data: jQuery.param({action: $(el).data("action"), data: JSON.stringify(formData)}),
            type: "POST",
            success: function (data) {
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
                } else if (data.errors) {
                    // TODO localizzare
                    var errorMsg = '<p>Impossibile salvare questo documento, a causa dei seguenti errori:</p><ul>';
                    $.each(data.errors, function(id, value) {
                        errorMsg += '<li><p class="alert alert-error">'+value+'</p></li>';
                    });
                    Glizy.events.broadcast("glizy.message.showError", {"title": self.lang.errorValidationMsg, "message": errorMsg});

                } else {
                    if (triggerAction) {
                        triggerAction('click', formData);
                        //$(triggerAction).trigger('click');
                    } else {
                        Glizy.events.broadcast("glizy.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                    }
                }

                $saveButton.removeAttr("disabled");
            }
        });
    }
});


