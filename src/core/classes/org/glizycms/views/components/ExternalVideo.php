<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_ExternalVideo extends org_glizy_components_Component
{
    public function init()
    {
        // define the custom attributes
        $this->defineAttribute('addLightbox',          false,  false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('cssClass',          false,  NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute('url',               false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('width',             false,  640, COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('height',            false,  480, COMPONENT_TYPE_INTEGER);

        // call the superclass for validate the attributes
        parent::init();
    }

    public function process()
    {
        $this->_content = null;

        $url = $this->getAttribute('url');
        if (!$url) {
            $url = $this->_parent->loadContent($this->getId());
        }
        if ($url) {
            preg_match_all('/http(s)?:\/\/(?:www.)?(vimeo|youtube|youtu)(\.\w{2,3})\/(?:watch\?v=)?(.*?)(?:\z|&)/', $url, $match);
            if (count($match[0])) {
                $this->_content = new StdClass;
                $this->_content->externalLink = $url;
                $this->_content->width = $this->getAttribute('width');
                $this->_content->height = $this->getAttribute('height');
                if ($match[2][0] == 'vimeo') {
                    $this->_content->type = 'vimeo';
                    $this->_content->url = 'http://player.vimeo.com/video/'.$match[4][0].'?title=0&amp;byline=0&amp;portrait=0';
                } else {
                   $this->_content->type = 'youtube';
                   $this->_content->url = 'http://www.youtube.com/embed/'.$match[4][0].'?rel=0';
                }

                if ($this->getAttribute('addLightbox')) {
                    $this->_application->addLightboxJsCode();
                }
            }
        }
    }

    public static function translateForMode_edit($node) {
        $mediaType = $node->getAttribute('adm:mediaType');
        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['xmlns:glz'] = "http://www.glizy.org/dtd/1.0/";

        if (count($node->attributes))
        {
            foreach ( $node->attributes as $index=>$attr )
            {
                if ($attr->prefix=="adm")
                {
                    $attributes[$attr->name] = $attr->value;
                }
            }
        }

        return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
    }
}

/**
 * Class org_glizycms_views_components_ExternalVideo_render
 */
class org_glizycms_views_components_ExternalVideo_render extends org_glizy_components_render_Render
{
    /**
     * @return string
     */
    function getDefaultSkin()
    {
        $skin = <<<EOD
<div tal:attributes="id Component/id;class Component/__cssClass__" tal:condition="Component">
    <iframe width="640" height="360" frameborder="0" allowfullscreen="" tal:attributes="src item/url"></iframe>
</div>
EOD;
        return $skin;
    }
}