<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Media extends org_glizy_components_Component
{
    var $media;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('cssClass',        false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('directUrl',       false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rel',            false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('label',            false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('linkTitle',        false,     __T('GLZ_DOWNLOAD_FILE_LINK'),    COMPONENT_TYPE_STRING);

        $this->defineAttribute('adm:required',            false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('adm:mediaType',        false,     'ALL',    COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }


    /**
     * Process
     *
     * @return    boolean    false if the process is aborted
     * @access    public
     */
    function process()
    {
        $this->resetContent();

        $mediaId = $this->_parent->loadContent($this->getId());


        if (is_string($mediaId)) {
            $mediaId = json_decode($mediaId);
        }

        if (is_object($mediaId)) {
            $mediaId = org_glizycms_Glizycms::getMediaArchiveBridge()->getIdFromJson($mediaId);
        }

        if (is_numeric($mediaId) && $mediaId > 0) {
            $this->attachMedia($mediaId);
        }

        $this->processChilds();
    }


    /**
     * Render
     *
     * @return    void
     * @access    public
     */
    function render_html()
    {
        $this->_render_html();
        $this->addOutputCode($this->_content['__html__']);
    }

    function _render_html()
    {
        if ($this->_content['mediaId']>0)
        {
            $linkTitle = $this->getAttribute('linkTitle');
            $linkTitle = str_replace('#title#', $this->_content['title'], $linkTitle);
            $linkTitle = str_replace('#size#', org_glizy_helpers_String::formatFileSize($this->media->size), $linkTitle);
            $originalFileName = $this->media->originalFileName;
            $part = explode( '.', $originalFileName );
            $type = '';
            if ( count( $part ) > 1 )
            {
                $type = strtoupper( $part[ count( $part ) - 1 ] );
            }
            $linkTitle = str_replace('#format#', $type, $linkTitle);
            $this->_content['linkTitle'] = $linkTitle;
            $this->_content['__url__'] = org_glizy_helpers_Media::getFileUrlById($this->media->id, $this->getAttribute('directUrl'));
            $this->_content['__html__'] = org_glizy_helpers_Link::makeSimpleLink($this->_content['title'],
                                                                                $this->_content['__url__'],
                                                                                $linkTitle,
                                                                                $this->getAttribute('cssClass'),
                                                                                $this->getAttribute('rel'));

        }
    }


    function getContent($parent=NULL)
    {
        $this->_render_html();
        return $this->_content;
    }

    function resetContent()
    {
        $this->_content = array();
        $this->_content['mediaId']        = 0;
        $this->_content['src']             = '';
        $this->_content['title']         = '';
        $this->_content['size']         = '';
        $this->_content['mediaType']     = '';
        $this->_content['__url__']         = '';
        $this->_content['__html__']     = '';
        $this->_content['linkTitle']     = '';
    }

    function attachMedia($mediaId)
    {
        $this->media = &org_glizycms_mediaArchive_MediaManager::getMediaById($mediaId);
        if (is_object($this->media))
        {
            $this->_content['mediaId']    = $this->media->id;
            $this->_content['src']         = $this->media->getFileName();
            $this->_content['title']     = $this->media->title;
            $this->_content['size']     = $this->media->size;
            $this->_content['mediaType']= $this->media->type;
        }
    }

    public static function translateForMode_edit($node) {
        $mediaType = $node->hasAttribute('adm:mediaType') ? $node->getAttribute('adm:mediaType') : 'ALL';
        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['data'] = 'type=mediapicker;mediatype='.$mediaType.';preview=false';

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
