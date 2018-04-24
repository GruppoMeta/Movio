<?php
class movio_modules_ontologybuilder_views_components_AddEntityButton extends org_glizy_components_Component
{
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('label',    	true, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('routeUrl',    false, 	'',		COMPONENT_TYPE_STRING);

        parent::init();
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        // $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('APPLICATION').'templates/js/bootstrap.min.js' ) );

        $output = '<div class="btn-group">'.
                  '<a class="btn dropdown-toggle action-link" data-toggle="dropdown" href="#">'.
                  '<i class="icon-plus"></i> '.
                  $this->getAttribute('label').
                  '</a>'.
                  '<ul class="dropdown-menu left forced-left-position">';

        $routeUrl = $this->getAttribute('routeUrl');
        $localeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $language = $this->_application->getEditingLanguage();
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Entity', 'all');
        foreach ($it as $ar) {
            $params = array( 'entityTypeId' => $ar->getId());
            $url = org_glizy_helpers_Link::makeUrl( $routeUrl, $params);
            $entityName = $localeService->getTranslation($language, $ar->entity_name);
            $output .= '<li><a href="'.$url.'">'.$entityName.'</a></li>';
        }

        $output .= '</ul>'.
                   '</div>';
        $this->addOutputCode($output);
    }
}