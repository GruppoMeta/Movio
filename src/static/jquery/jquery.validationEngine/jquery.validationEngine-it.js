(function($){
    $.fn.validationEngineLanguage = function(){};
    $.validationEngineLanguage = {
        newLang: function(){
            $.validationEngineLanguage.allRules = {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "Campo richiesto",
                    "alertTextCheckboxMultiple": "Per favore selezionare un'opzione",
                    "alertTextCheckboxe": "E' richiesta la selezione della casella"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "* Minimo ",
                    "alertText2": " caratteri permessi"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "* Massimo ",
                    "alertText2": " caratteri permessi"
                },
                "length": {
                    "regex": "none",
                    "alertText": "Fra ",
                    "alertText2": " e ",
                    "alertText3": " caratteri permessi"
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "Numero di caselle da selezionare in eccesso"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "Per favore selezionare ",
                    "alertText2": " opzioni"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "I campi non corrispondono"
                },
                "phone": {
                    // credit: jquery.h5validate.js / orefalo
                    "regex": /^([\+][0-9]{1,3}[ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9 \.\-\/]{3,20})((x|ext|extension)[ ]?[0-9]{1,4})?$/,
                    "alertText": "Numero di telefono non corretto"
                },
                "email": {
                    // Shamelessly lifted from Scott Gonzalez via the Bassistance Validation plugin http://projects.scottsplayground.com/email_address_validation/
                    "regex": /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,6})$/,
                    "alertText": "Email non valida"
                },
                "integer": {
                    "regex": /^[\-\+]?\d+$/,
                    "alertText": "Numero intero non corretto"
                },
                "number": {
                    // Number, including positive, negative, and floating decimal. Credit: bassistance
                    "regex": /^[\-\+]?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)$/,
                    "alertText": "Numero decimale non corretto"
                },
                "date": {
                    // Date in ISO format. Credit: bassistance
                    "regex": /^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/,
                    "alertText": "Data non  corretta, re-inserire secondo formato AAAA-MM-GG"
                },

                "ipv4": {
                	"regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "IP non corretto"
                },
                "url": {
                    "regex": /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/,
                    "alertText": "URL non corretta"
                },
                "onlyNumber": {
                    "regex": /^[0-9\ ]+$/,
                    "alertText": "Solo numeri"
                },
				"onlyLetter": {
                    "regex": /^[a-zA-Z\ \']+$/,
                    "alertText": "Solo lettere"
                },
				"onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z\ \']+$/,
                    "alertText": "* Non sono permessi caratteri speciali"
                },
				"name": {
                    "regex": /^[^0-9<>\=,.\!\?]+$/,
                    "alertText": "Campo richiesto"
                },
                "validate2fields": {
                    "nname": "validate2fields",
                    "alertText": "Occorre inserire nome e cognome"
                },
                "noSpecialCharacters": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "Caratteri speciali non permessi"
                },
                "ajaxUserCall": {
                    "file": "ajaxValidateFieldName",
                    "extraData": "name=eric",
                    "alertTextLoad": "Caricamento, attendere per favore",
                    "alertText": "Questo user � gi� stato utilizzato"
                },
                "ajaxNameCall": {
                    "file": "ajaxValidateFieldName",
                    "alertText": "Questo nome � gi� stato utilizzato",
                    "alertTextOk": "Questo nome � disponibile",
                    "alertTextLoad": "Caricamento, attendere per favore"
                }

            };

        }
    };
    $.validationEngineLanguage.newLang();
})(jQuery);