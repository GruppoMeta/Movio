tinyMCEPopup.requireLangPack();

var FormulaEditorDialog = {
	
    isMathmlSupported : false,
    mathmlFormula : null,
	mathjaxMode : null,
	undoMngr : null,
		
	init : function() {
		var selectedTxt = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		var arg = tinyMCEPopup.getWindowArg('some_custom_arg');

		// Replace mathjax delimitators
		selectedTxt = selectedTxt.replace(/^ *\\\[|\\\] *$|^ *`|` *$/g, '');
		
		// Only FF supports MathML
		//FormulaEditorDialog.isMathmlSupported = MathJax.Hub.Browser.isFirefox;
		
		// mathjax mode get
		FormulaEditorDialog.mathjaxMode = tinyMCEPopup.editor.selection.getNode().getAttribute("data-fx-type");
		if (FormulaEditorDialog.mathjaxMode == null) {
			FormulaEditorDialog.mathjaxMode = "tex";
		}
		
		// sets mathjax mode in select
		$('#mathjax-mode-select').val(FormulaEditorDialog.mathjaxMode);
		
		// decodes xml
		if (FormulaEditorDialog.mathjaxMode == "mml") {
			if (tinyMCEPopup.editor.selection.getNode().getAttribute("data-encoded") == "true") {
				selectedTxt = tinyMCEPopup.dom.decode(selectedTxt);
			}
		}
		
		// sets in textarea the selected formula in tinymce
		$("#texformula").textrange("replace", selectedTxt);
		//$("#texformula").val(selectedTxt);
		FormulaEditorDialog.preview();
		
		
		// undo manager startup
		FormulaEditorDialog.undoMngr = new BufferedUndoManager();
		FormulaEditorDialog.undoMngr.reset(selectedTxt);
		
		
		// updates formula for each key click
		$("#texformula").keyup(function() {
			FormulaEditorDialog.undoMngr.update($("#texformula").val());
			FormulaEditorDialog.preview();
		});

		
		// math palette buttons management
		$("ul.formula li a").click(function() {
			var command = "";
			switch (FormulaEditorDialog.mathjaxMode) {
			case "tex":
				command = mathmap[$(this).data("tex-command")][0];
				break;
			case "am":
				command = mathmap[$(this).data("tex-command")][1];
				break;
			case "mml":
				command = mathmap[$(this).data("tex-command")][2];
				break;
			}
			
			// block commands management: replaces ### with textarea selection
			if (command.indexOf("###") >= 0) {
				var sel = $("#texformula").textrange();
				if (sel.start != sel.end) {
					command = command.replace("###", sel.text);
				} else {
					command = command.replace("###", "");
				}
			}

			// inserts formula symbol into textarea
			$("#texformula").textrange("replace", command);
        	var sel = $("#texformula").textrange();
        	$("#texformula").textrange('set', sel.end);

        	
        	// new undo and update formula preview
        	FormulaEditorDialog.undoMngr.update($("#texformula").val());
			FormulaEditorDialog.preview();
        });

		
		// tab menu activation/disactivation
		$("#tab-menu ul.tab li a").click(function() {
			$("#tab-menu ul li a.active").removeClass('active');
			$(this).addClass('active');
        });
		
		
		// select tex|ascii|mathml change event
		$('#mathjax-mode-select').change(function() {
			FormulaEditorDialog.mathjaxMode = $(this).attr('value');
			FormulaEditorDialog.preview();
		});
	},
	
	//
	// Inserts formula in TinyMCE doc
	//
	insert : function() {
		var newformula = "";
		var encoded = false;
		var formulaSpanStyle = "";
		var mathmlSpan = "";
		
		// Adds delimiters
		newformula = FormulaEditorDialog.addMathJaxDelimiters($("#texformula").val());
		
		// If MathML output is selected, and not supported, encode xml
		if (FormulaEditorDialog.mathjaxMode == "mml") {
			if (!FormulaEditorDialog.isMathmlSupported) {
				newformula = tinyMCEPopup.dom.encode(newformula);
				encoded = true;
			}
		}

		// If MathML is supported, shows formula converted in mathml, and hides editable formula
		if (FormulaEditorDialog.isMathmlSupported) {
			formulaSpanStyle = ' style="display:none"';
			FormulaEditorDialog.getMathML();
			FormulaEditorDialog.mathmlFormula = FormulaEditorDialog.mathmlFormula.replace(' display="block"','');
			mathmlSpan = '<span>' + FormulaEditorDialog.mathmlFormula + '</span>';
		} else {
			formulaSpanStyle = '';
			mathmlSpan = '';
		}
		
		/* mathml formula insert management
		var toInsert = '<span class="mceNonEditable"' +
			'><span class="formula"' + formulaSpanStyle +
			' data-fx-type="' + FormulaEditorDialog.mathjaxMode + '"' +
			' data-encoded="' + encoded + '"' +			
			'>' +
			newformula + '</span>' + 
			mathmlSpan +
			'</span>';
		*/
		var toInsert = '<span class="mceNonEditable formula"' +
			' data-fx-type="' + FormulaEditorDialog.mathjaxMode + '"' +
			'>' +
			newformula + '</span>';

		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, toInsert);
		tinyMCEPopup.close();
	},
	
	//
	// Updates formula preview
	//
	preview : function () {
		var formula = FormulaEditorDialog.addMathJaxDelimiters($("#texformula").val());
		$("#previewcell").html(formula);
		MathJax.Hub.Queue(["Typeset",MathJax.Hub,"previewcell"]);
	},

	//
	// To MathML formula conversion
	// https://groups.google.com/forum/?fromgroups#!topic/mathjax-users/unL8IjcrTto
	toMathML : function(jax,callback) {
        var mml;
        try {
            mml = jax.root.toMathML("");
        } catch(err) {
            if (!err.restart) {throw err;} // an actual error
            return MathJax.Callback.After([FormulaEditorDialog.toMathML,jax,callback],err.restart);
        }
        MathJax.Callback(callback)(mml);
    },
    
    //
    // Get MathML definition of formula in previewcell,
    // and store it in FormulaEditorDialog.mathmlFormula var
    //
    getMathML : function() {
    	MathJax.Hub.Queue(function() {
    		var jax = MathJax.Hub.getAllJax("previewcell");
    		for (var i = 0; i < jax.length; i++) {
    			FormulaEditorDialog.toMathML(jax[i],function(mml) {
    				FormulaEditorDialog.mathmlFormula = mml;
    	        });
			}
		});
    },
    
	//
	// Returns formula with MathJax delimites, depending on set mode (tex|ascii|mathml)
	//
    addMathJaxDelimiters : function(formula) {
    	var rformula = "";
		switch (FormulaEditorDialog.mathjaxMode) {
			case "tex":
		        //tex mode:
				rformula = "\\[" + formula + "\\]";
			break;
			case "am":
		        //ascii mode:
				rformula = "`" + formula + "`" + "\r";
			break;
			case "mml":
				//mathml mode:
				rformula = FormulaEditorDialog.mathDecoration(formula);
			break;
		}
    	return rformula;
    },
    
	//
	// Math tags mathml decoration
	//
	mathDecoration : function(txtbox) {
		var rformu = "";
		
		if (txtbox.indexOf("<math") >= 0) {
			// starting math tag already exists
			rformu = txtbox;
			if (txtbox.indexOf("</math>") < 0) {
				// end math tag does not exist
				rformu = rformu + "</math>";
			}

		} else {
			rformu = '<math xmlns="http://www.w3.org/1998/Math/MathML">' +
				txtbox + '</math>';
		}
		return rformu;
	},
	
	selectTab : function(tabId) {
		$('.side-sx').css('display', 'none');
		$('#'+tabId).css('display', 'block');
		$("#texformula").focus();
	},

	undo : function () {
		if (FormulaEditorDialog.undoMngr.canUndo()) {
			FormulaEditorDialog.undoMngr.undo();
			$("#texformula").val(FormulaEditorDialog.undoMngr.state);
			FormulaEditorDialog.preview();
			$("#texformula").focus();
		}
	},

	redo : function () { 
		if (FormulaEditorDialog.undoMngr.canRedo()) {
			FormulaEditorDialog.undoMngr.redo();
			$("#texformula").val(FormulaEditorDialog.undoMngr.state);
			FormulaEditorDialog.preview();
			$("#texformula").focus();
		}
	},

	copyBuffer : "",
	
	copy : function () {
		var sel = $("#texformula").textrange();
		if (sel.start != sel.end) {
			FormulaEditorDialog.copyBuffer = sel.text;
		}		
	},

	cut : function () {
		var sel = $("#texformula").textrange();
		if (sel.start != sel.end) {
			FormulaEditorDialog.copyBuffer = sel.text;
		}
		$("#texformula").textrange("replace", "");
    	FormulaEditorDialog.undoMngr.update($("#texformula").val());
		FormulaEditorDialog.preview();
		$("#texformula").focus();
	},

	paste : function () {
		if (FormulaEditorDialog.copyBuffer != "") {
			$("#texformula").textrange("replace", FormulaEditorDialog.copyBuffer);
	    	var sel = $("#texformula").textrange();
	    	$("#texformula").textrange('set', sel.end);
	    	FormulaEditorDialog.undoMngr.update($("#texformula").val());
			FormulaEditorDialog.preview();
			$("#texformula").focus();
		}
	},

	clear : function () {
		$("#texformula").val("");
		FormulaEditorDialog.undoMngr.update("");
		FormulaEditorDialog.preview();
		$("#texformula").focus();
	}

};

tinyMCEPopup.onInit.add(FormulaEditorDialog.init, FormulaEditorDialog);
