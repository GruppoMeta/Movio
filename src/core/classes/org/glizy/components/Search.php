<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Search extends org_glizy_components_Form
{
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('label',            false,     org_glizy_locale_Locale::get('GLZ_SEARCH_LABEL'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('buttonLabel',    false,     org_glizy_locale_Locale::get('GLZ_SEARCH_BUTTON'),    COMPONENT_TYPE_STRING);
        $this->defineAttribute('comment',        false,     org_glizy_locale_Locale::get('GLZ_SEARCH_COMMENT'),    COMPONENT_TYPE_STRING);
        $this->defineAttribute('skipFormTag',    false,     false,    COMPONENT_TYPE_BOOLEAN);
        parent::init();
        $this->setAttribute('method', 'get');
    }

    function process()
    {
        if ( !$this->_application->isAdmin() )
        {
            $this->_content = array();
            $this->_content['label']        = $this->getAttribute('label');
            $this->_content['buttonLabel']  = $this->getAttribute('buttonLabel');
            $this->_content['comment']      = $this->getAttribute('comment');
            $this->_content['comment1']     = org_glizy_locale_Locale::get('GLZ_SEARCH_RESULT');
            $this->_content['value']        = org_glizy_Request::get('search', '');
            $this->_content['result']       = null;

            // preg_match( '/"([^"]*)"/i', $this->_content['value'], $match );
            // if ( count( $match ) )
            // {
            //     $searchArray2 = array( $match[ 1 ] );
            // }
            // else
            // {
            //     if ($this->getAttribute('explodeWords')) {
            //         $searchArray = explode(' ', $this->_content['value']);
            //         $searchArray2 = array();

            //         foreach ($searchArray as $word)
            //         {
            //             if (strlen($word)>=3) $searchArray2[] = $word;
            //         }
            //     } else {
            //         $searchArray2 = $this->_content['value'];
            //     }
            // }

            if (strlen($this->_content['value'])>=3)
            {
                $pluginObj = &org_glizy_ObjectFactory::createObject('org.glizy.plugins.Search');
                $this->_content['result'] = $pluginObj->run(array('search' => $this->_content['value'], 'languageId' => $this->_application->getLanguageId()));
            }

            $this->_content['total'] =  org_glizy_locale_Locale::get('GLZ_SEARCH_RESULT_TOTAL').' '.count($this->_content['result']);
        }
    }


    function render()
    {
        if ( !$this->_application->isAdmin() )
        {
            if ($this->_content['result']) {
                org_glizy_helpers_Array::arrayMultisortByLabel($this->_content['result'], '__weight__');
            }

            if (!$this->getAttribute('skipFormTag'))
            {
                parent::render_html_onStart();
            }
            parent::render();
            if (!$this->getAttribute('skipFormTag'))
            {
                parent::render_html_onEnd();
            }
        }
    }
}

class org_glizy_components_Search_render extends org_glizy_components_render_Render
{
    function getDefaultSkin()
    {
        $skin = <<<EOD
<span tal:omit-tag="">
    <div class="formItem">
        <label for="search" tal:content="Search/label" />
        <input type="text" name="search" id="search" value="" size="20" tabindex="22" class="long" tal:attributes="value Search/value"/>
        <input type="submit" class="submitButton" value="cerca" tal:attributes="value Search/buttonLabel"/>
    </div>
    <p tal:content="structure Search/comment" />
    <span tal:omit-tag="" tal:condition="php: !is_null(Search['result'])" >
        <div id="searchResult" tal:condition="php: count(Search['result'])">
            <h2 tal:content="structure Search/comment1"/>
            <p tal:content="structure Search/total"/>
            <ul>
                <li tal:repeat="item Search/result"><strong tal:content="structure item/__url__"></strong>
                <p class="small" tal:content="structure item/description" />
                </li>
            </ul>
        </div>
    </span>
    <span tal:condition="php: !count(Search['result'])" tal:content="php:__T('MW_NO_RECORD_FOUND')" />
</span>
EOD;
        return $skin;
    }
}