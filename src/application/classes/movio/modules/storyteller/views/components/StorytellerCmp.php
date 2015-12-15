<?php
class movio_modules_storyteller_views_components_StorytellerCmp extends org_glizy_components_Component
{
    /**
     * Init
     *
     * @return  void
     * @access  public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('label',     false,  NULL,   COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function process()
    {
        $content = org_glizy_helpers_Convert::formEditObjectToStdObject($this->_parent->loadContent($this->getId(), true));
        $this->_content = new movio_modules_storyteller_views_skins_StorytellerSkinIterator($content,
            $this->_application->getPageId(),
            __Routing::scriptUrl());
    }

    public static function compileAddPrefix($compiler, &$node, $componentId, $idPrefix)
    {
        return $idPrefix.'\''.$componentId.'-\'.';
    }

    public static function translateForMode_edit($node) {
        $min = $node->hasAttribute('adm:min') ? $node->getAttribute('adm:min') : '0';
        $max = $node->hasAttribute('adm:max') ? $node->getAttribute('adm:max') : '100';
        $collapsable = $node->hasAttribute('adm:collapsable') && $node->getAttribute('adm:collapsable') == 'true' ? 'true' : 'false';

        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['data'] = 'type=repeat;repeatMin='.$min.';repeatMax='.$max.';collapsable='.$collapsable;
        if ($node->hasAttribute('adm:data')) {
            $attributes['data'] .= ';'.$node->getAttribute('adm:data');
        }

        return org_glizy_helpers_Html::renderTag('glz:Fieldset', $attributes);
    }
}
