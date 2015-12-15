<?php
class org_glizycms_views_components_FormEdit extends org_glizy_components_Form
{
    protected $data = '{}';
    protected $pageTitleModifiers = array();

    function init()
    {
        // define the custom attributes
        $this->defineAttribute( 'customValidation', false, NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute( 'newCode', false, false, COMPONENT_TYPE_BOOLEAN);

        // call the superclass for validate the attributes
        parent::init();

        $this->setAttribute( 'addValidationJs', false );
        $this->setAttribute('cssClass', ' formEdit', true);
    }

    public function setData($data)
    {
        $this->data = is_array($data) || is_object($data) ? json_encode($data) : $data;
        $this->_content = is_object($data) ? get_object_vars($data) : $data;
    }

    public function addPageTitleModifier(org_glizycms_views_components_FormEditPageTitleModifierVO $modifier)
    {
        $this->pageTitleModifiers[] = $modifier;
    }

    public function process()
    {
        parent::process();
        $this->changePageTitle();
    }

    // public function render_html_onStart()
    // {
    //     $this->setAttribute( 'addValidationJs', false );
    //     $this->setAttribute('cssClass', ' formEdit', true);
    //     parent::render_html_onStart();
    // }

    public function render_html_onEnd($value='')
    {
        parent::render_html_onEnd();


        $corePath = __Paths::get('CORE');
        $jQueryPath = $corePath.'classes/org/glizycms/js/jquery/';

        $languageCode = $this->_application->getLanguage();
        $language = $languageCode.'-'.strtoupper($languageCode);

        if ($this->getAttribute('newCode')) {
            $formEditPath = $corePath.'classes/org/glizycms/js/formEdit2/';

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/underscore/underscore-min.js' ));
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEdit.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditStandard.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCheckbox.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditRepeat.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDate.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDateTime.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.it.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-datetimepicker-master/css/datetimepicker.css' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditColorPicker.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-colorpicker/js/bootstrap-colorpicker.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-colorpicker/css/bootstrap-colorpicker.min.css' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGUID.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectFrom.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditTINYMCE.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditMediaPicker.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditFile.js' ) );
            //$this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'fineuploader.jquery/jquery.fineuploader.js' ) );
            //$this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'fineuploader.jquery/fineuploader.css' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.validVal-packed.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPermission.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPhotoGalleryCategory.js' ) );

            // $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGoogleMaps.js' ) );
            // $this->addOutputCode(org_glizy_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?sensor=false' ));

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCmsPagePicker.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectPageType.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditUrl.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditModalPage.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/glizy-locale/'.$language.'.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ) );

            $id = $this->getId();

            $mediaPicker = $this->getMediaPickerUrl();
            $AJAXAtion = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

            $customValidation = $this->getAttribute('customValidation');
            if ( $customValidation ) {
                $customValidation = 'customValidation: "'.$customValidation.'",';
            }

            $tinyMceUrls = json_encode($this->getTinyMceUrls());

            $jsCode = <<< EOD
jQuery(function(){
    if ( Glizy.tinyMCE_options )
    {
        Glizy.tinyMCE_options.urls = $tinyMceUrls;
    }

    var myFormEdit = Glizy.oop.create("glizy.FormEdit", '$id', {
        AJAXAction: "$AJAXAtion",
        mediaPicker: $mediaPicker,
        formData: $this->data,
        $customValidation
        lang: GlizyLocale.FormEdit
    });
});
EOD;
        } else {
            $formEditPath = $corePath.'classes/org/glizycms/js/formEdit/';

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/underscore/underscore-min.js' ));
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEdit.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditTINYMCE.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditFile.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditMediaPicker.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGoogleMaps.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGUID.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditColorPicker.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditValuesPreset.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDate.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDatetime.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.it.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-datetimepicker-master/css/datetimepicker.css' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-colorpicker/js/bootstrap-colorpicker.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-colorpicker/css/bootstrap-colorpicker.min.css' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/glizy-locale/'.$language.'.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'dropzone/dropzone.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'dropzone/css/basic2.css' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.validVal-packed.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCmsPagePicker.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectFrom.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ) );

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPermission.js' ) );

            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCheckbox.js' ) );
            $this->addOutputCode(org_glizy_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?sensor=false' ));
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectPageType.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPhotoGalleryCategory.js' ) );
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditImageHotspot.js' ) );

            // $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditTreeSelect.js' ) );
            // $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'fancytree/jquery.fancytree-all.min.js' ) );
            // $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'fancytree/skin-win7/ui.fancytree.min.css' ) );

            $id = $this->getId();

            $mediaPicker = $this->getMediaPickerUrl();
            $AJAXAtion = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

            $customValidation = $this->getAttribute('customValidation');
            if ( $customValidation ) {
                $customValidation = 'customValidation: "'.$customValidation.'",';
            }

            $tinyMceUrls = json_encode($this->getTinyMceUrls());

            $jsCode = <<< EOD
jQuery(function(){
    if ( Glizy.tinyMCE_options )
    {
        Glizy.tinyMCE_options.urls = $tinyMceUrls;
    }

    var ajaxUrl = "$AJAXAtion";
    jQuery( "#$id" ).GlizyFormEdit({
        AJAXAction: ajaxUrl ? ajaxUrl : Glizy.ajaxUrl,
        mediaPicker: $mediaPicker,
        formData: $this->data,
        $customValidation
        lang: GlizyLocale.FormEdit
    });
});
EOD;
        }

        $this->addOutputCode(org_glizy_helpers_JS::JScode( $jsCode ));
    }

    protected function getMediaPickerUrl()
    {
        return '"'.org_glizycms_Glizycms::getMediaArchiveBridge()->getMediaPickerUrl().'"';
    }

    protected function getTinyMceUrls()
    {
        return array(
                        'ajaxUrl' => GLZ_HOST.'/'.$this->getAjaxUrl(),
                        'mediaPicker' => GLZ_HOST.'/'.org_glizycms_Glizycms::getMediaArchiveBridge()->getMediaPickerUrl(),
                        'mediaPickerTiny' => GLZ_HOST.'/'.org_glizycms_Glizycms::getMediaArchiveBridge()->getMediaPickerUrl(true),
                        'imagePickerTiny' => GLZ_HOST.'/'.org_glizycms_Glizycms::getMediaArchiveBridge()->getMediaPickerUrl(true, 'IMAGE'),
                        'imageResizer' => org_glizycms_Glizycms::getMediaArchiveBridge()->getImageResizeTemplate(),
                        'root' => GLZ_HOST.'/',
            );
    }

    protected function changePageTitle()
    {
        if ( method_exists( $this->_parent, "getAction" ) )
        {
            $currentAction = $this->_parent->getAction();
            foreach($this->pageTitleModifiers as $modifier)
            {
                if ($currentAction==$modifier->action) {
                    $newTitle = $modifier->label;
                    $newSubtitle = $modifier->fieldSubtitle && $this->_content[$modifier->fieldSubtitle] ? $this->_content[$modifier->fieldSubtitle] : '';
                    if ($modifier->isNew) {
                        if ($modifier->idField &&
                                ($this->_content[$modifier->idField]!='0' && isset($this->_content[$modifier->idField]))) {
                            continue;
                        }
                    }

                    if (preg_match("/\{i18n\:.*\}/i", $newTitle))
                    {
                        $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $newTitle);
                        $newTitle = org_glizy_locale_Locale::get($code, $newSubtitle);
                    }

                    $evt = array('type' => GLZ_EVT_PAGETITLE_UPDATE, 'data' => $newTitle);
                    $this->dispatchEvent($evt);
                    break;
                }
            }
        }
    }

    function loadContent($name, $bindToField=NULL)
	{
        if (empty($bindToField)) {
			$bindToField = $name;
		}

		return org_glizy_Request::get($bindToField, $this->_content[$bindToField]);
	}


    public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );

        $oldcounter = $counter;
        foreach ($node->childNodes as $n ) {
            if ( $n->nodeName == "cms:pageTitleModifier" ) {
                $action = $n->hasAttribute('action') ? $n->getAttribute('action') : '';
                $label = $n->hasAttribute('label') ? $n->getAttribute('label') : '';
                $new = $n->hasAttribute('new') ? $n->getAttribute('new') : 'false';
                $field = $n->hasAttribute('field') ? $n->getAttribute('field') : '';
                $idField = $n->hasAttribute('idField') ? $n->getAttribute('idField') : '__id';
                if ( $action && $label )
                {
                    $compiler->_classSource .= '$n'.$counter.'->addPageTitleModifier('.
                                'new org_glizycms_views_components_FormEditPageTitleModifierVO("'.$action.'", "'.$label.'", '.$new.', "'.$idField.'", "'.$field.'"));';
                }
            } else {
                $counter++;
                $compiler->compileChildNode($n, $registredNameSpaces, $counter, $oldcounter, $idPrefix);
            }
        }

        return false;
    }
}

class org_glizycms_views_components_FormEditPageTitleModifierVO
{
    function __construct($action, $label, $isNew, $idField, $fieldSubtitle) {
        $this->action = $action;
        $this->label = $label;
        $this->isNew = $isNew;
        $this->idField = $idField;
        $this->fieldSubtitle = $fieldSubtitle;
    }
}


