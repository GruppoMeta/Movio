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
        $this->defineAttribute( 'initJS', false, true, COMPONENT_TYPE_BOOLEAN);

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


    public function resetPageTitleModifier()
    {
        $this->pageTitleModifiers = array();
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
        $imageResizer = org_glizycms_Glizycms::getMediaArchiveBridge()->imageResizeTemplateUrl(
                                        __Config::get('THUMB_WIDTH'),
                                        __Config::get('THUMB_HEIGHT'),
                                        __Config::get('ADM_THUMBNAIL_CROP'),
                                        __Config::get('ADM_THUMBNAIL_CROPPOS'));
        $googleApiKey = __Config::get('glizy.maps.google.apiKey');

        if ($this->getAttribute('newCode')) {
            $formEditPath = $corePath.'classes/org/glizycms/js/formEdit2/';

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/underscore/underscore-min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEdit.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditStandard.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCheckbox.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditRepeat.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditRecordPicker.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDate.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDateTime.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.it.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-datetimepicker-master/css/datetimepicker.css' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditColorPicker.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-colorpicker/js/bootstrap-colorpicker.min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-colorpicker/css/bootstrap-colorpicker.min.css' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGUID.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectFrom.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditTINYMCE.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditMediaPicker.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditFile.js' ), 'head');
            //$this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'fineuploader.jquery/jquery.fineuploader.js' ), 'head');
            //$this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'fineuploader.jquery/fineuploader.css' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.validVal-packed.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPermission.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPhotoGalleryCategory.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCmsPagePicker.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectPageType.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditUrl.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditModalPage.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/glizy-locale/'.$language.'.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ), 'head');

            if ($googleApiKey) {
                $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGoogleMaps.js' ), 'head');
                $this->addOutputCode(org_glizy_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?key='.$googleApiKey), 'head');
            }


            $id = $this->getId();

            $mediaPicker = $this->getMediaPickerUrl();
            $AJAXAtion = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

            $customValidation = $this->getAttribute('customValidation');
            if ( $customValidation ) {
                $customValidation = 'customValidation: "'.$customValidation.'",';
            }

            $tinyMceUrls = json_encode($this->getTinyMceUrls());

            $readOnly = $this->getAttribute('readOnly');

            $jsCode = <<< EOD
jQuery(function(){
    if ( Glizy.tinyMCE_options )
    {
        Glizy.tinyMCE_options.urls = $tinyMceUrls;
    }

    var myFormEdit = Glizy.oop.create("glizy.FormEdit", '$id', {
        AJAXAction: "$AJAXAtion",
        mediaPicker: $mediaPicker,
        imageResizer: "$imageResizer",
        formData: $this->data,
        $customValidation
        lang: GlizyLocale.FormEdit,
        readOnly: "$readOnly"
    });
});
EOD;
        } else {
            $formEditPath = $corePath.'classes/org/glizycms/js/formEdit/';

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/underscore/underscore-min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEdit.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditTINYMCE.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditFile.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditMediaPicker.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGUID.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditColorPicker.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditValuesPreset.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditRecordPicker.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDate.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditDatetime.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.it.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-datetimepicker-master/css/datetimepicker.css' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-colorpicker/js/bootstrap-colorpicker.min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-colorpicker/css/bootstrap-colorpicker.min.css' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $corePath.'classes/org/glizycms/js/glizy-locale/'.$language.'.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'dropzone/dropzone.min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'dropzone/css/basic2.css' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.validVal-packed.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCmsPagePicker.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectFrom.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPermission.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ), 'head');
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditCheckbox.js' ), 'head');

            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditSelectPageType.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditPhotoGalleryCategory.js' ), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditImageHotspot.js' ), 'head');

            if ($googleApiKey) {
                $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditGoogleMaps.js' ), 'head');
                $this->addOutputCode(org_glizy_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?key='.$googleApiKey), 'head');
            }

            $id = $this->getId();

            $mediaPicker = $this->getMediaPickerUrl();
            $AJAXAtion = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

            $initJS = $this->getAttribute('initJS') ? 'true' : 'false';
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
    if (initJs = $initJS) {
        jQuery( "#$id" ).GlizyFormEdit({
            AJAXAction: ajaxUrl ? ajaxUrl : Glizy.ajaxUrl,
            mediaPicker: $mediaPicker,
            imageResizer: "$imageResizer",
            formData: $this->data,
            $customValidation
            lang: GlizyLocale.FormEdit
        });
    } else {
        jQuery( "#$id" ).hide();
    }
});
EOD;
        }

        if (empty($googleApiKey)) {
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $formEditPath.'GlizyFormEditLeafletMaps.js' ), 'head');
            $this->addOutputCode('<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>', 'head');
            $this->addOutputCode('<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>', 'head');
        }

        $this->addOutputCode(org_glizy_helpers_JS::JScode( $jsCode ), 'head');
    }

    protected function getMediaPickerUrl()
    {
        return '"'.org_glizycms_Glizycms::getMediaArchiveBridge()->mediaPickerUrl().'"';
    }

    protected function getTinyMceUrls()
    {
        return array(
                        'ajaxUrl' => GLZ_HOST.'/'.$this->getAjaxUrl(),
                        'mediaPicker' => org_glizycms_Glizycms::getMediaArchiveBridge()->mediaPickerUrl(),
                        'mediaPickerTiny' => org_glizycms_Glizycms::getMediaArchiveBridge()->mediaPickerUrl(true),
                        'imagePickerTiny' => org_glizycms_Glizycms::getMediaArchiveBridge()->mediaPickerUrl(true, 'IMAGE'),
                        'imageResizer' => org_glizycms_Glizycms::getMediaArchiveBridge()->imageResizeTemplateUrl(),
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

		return org_glizy_Request::get($bindToField, isset($this->_content[$bindToField]) ? $this->_content[$bindToField] : null);
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


