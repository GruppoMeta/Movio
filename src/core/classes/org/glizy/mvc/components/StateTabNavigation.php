<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_mvc_components_StateTabNavigation extends org_glizy_components_Component
{
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('addWrapDiv',        false,  false,          COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('cssClass',          false,  '',             COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassCurrent',   false,  'current',      COMPONENT_TYPE_STRING);
        $this->defineAttribute('forceLink',         false,  true,           COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('addQueryString',    false,  false,          COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('routeUrl',          false,  'moduleAction', COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function render_html()
    {
        if ($this->getAttribute('addWrapDiv'))
        {
            $output  = '<div id="'.$this->getId().'"><ul '.(!is_null($this->getAttribute('cssClass')) ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>';
        }
        else
        {
            $output = '<ul id="'.$this->getId().'"'.(!is_null($this->getAttribute('cssClass')) ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>';
        }

        $controller = $this->_parent;

        $queryString = $this->getAttribute('addQueryString') ? '?'.__Request::get('__back__url__') : '';

        foreach ( $controller->childComponents  as $c )
        {
            if ( is_a( $c, 'org_glizy_mvc_components_State' ) )
            {
                $label = $c->getAttribute('label');
                $draw = $c->getAttribute('draw');
                $aclResult = $this->evalueteAcl($c->getAttribute('acl'));
                $cssClass = trim($c->getAttribute('cssClassTab').
                                ($c->isCurrentState() ? ' '.$this->getAttribute( 'cssClassCurrent' ) : ''));
                if ($cssClass) {
                    $cssClass = ' class="'.$cssClass.'"';
                }
                $id = $c->getId();
                if ( $draw && !empty($label) && $aclResult)
                {
                    if (!empty($cssClass) && !$this->getAttribute('forceLink'))
                    {
                        $output .= '<li'.$cssClass.'>'.$label.'</li>';
                    }
                    else
                    {
                        $url = $c->getAttribute('url');
                        if (is_null($url))
                        {
                            $url = __Link::makeUrl( $this->getAttribute('routeUrl'), array( 'title' => $label, 'action' => $c->getStateAction() ) );
                        }
                        else
                        {
                            $url = __Link::makeUrl( $url  );
                        }
                        $output .= '<li'.$cssClass.'><a id="' . $id . '" href="'.$url.$queryString.'"'.$cssClass.'>'.$label.'</a></li>';
                    }
                }
            }
        }


        $output  .= '</ul>';
        if ($this->getAttribute('addWrapDiv'))
        {
            $output  .= '</div>';
        }
        $this->addOutputCode($output);
    }
}