<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_RecordDetail extends org_glizy_components_ComponentContainer
{
    protected $recordId;
    protected $ar;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('dataProvider',    true,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('idName',        false,     'id',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('routeUrl',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('ogTitle',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('modifyBreadcrumbs',         false,    true,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('processCell',     false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('processCellParams',    false,    NULL,        COMPONENT_TYPE_STRING);

        parent::init();
    }


    function process()
    {
        $this->recordId = org_glizy_Request::get($this->getAttribute('idName'), NULL);
        if (is_null($this->recordId)) return;

        $dataProvider = &$this->getAttribute('dataProvider');
        if ($dataProvider) {
            $this->ar = $dataProvider->load($this->recordId);
            $processCell = org_glizy_ObjectFactory::createObject($this->getAttribute('processCell'), $this->_application);
            if ($processCell) {
                $ar = &$this->ar;
                call_user_func_array(array($processCell, 'renderCell'), array($ar, $this->getAttribute('processCellParams')));
            }
            $this->_content = org_glizy_ObjectFactory::createObject('org.glizy.components.RecordDetailVO', $this->ar);

            $ogTitle = $this->getAttribute('ogTitle');
            if ($ogTitle) {
                org_glizy_ObjectValues::set('org.glizy.og', 'title', $this->ar->{$ogTitle});
                if ($this->getAttribute('modifyBreadcrumbs')) {
                    $evt = array('type' => GLZ_EVT_BREADCRUMBS_UPDATE, 'data' => $this->ar->{$ogTitle});
                    $this->dispatchEvent($evt);

                    $evt = array('type' => GLZ_EVT_PAGETITLE_UPDATE, 'data' => $this->ar->{$ogTitle});
                    $this->dispatchEvent($evt);
                }
            }
// TODO controllare che i dati siano stati caricati correttamento
        } else {
// TODO generare errore, dataprovider non valid
        }

        $this->_content->__url__ = !is_null( $this->getAttribute( 'routeUrl' ) ) ? org_glizy_helpers_Link::makeURL( $this->getAttribute( 'routeUrl' ), $this->_content) : '';
        parent::process();
    }


    function getContent()
    {
        if (count($this->childComponents))
        {
            for ($i=0; $i<count($this->childComponents);$i++)
            {
                $id = preg_replace('/^'.$this->getId().'\-/', '', $this->childComponents[$i]->getId());
                $r = $this->childComponents[$i]->getContent();
                $this->_content->{$id} = $r;
            }
        }

        return $this->_content;
    }


    function loadContent($id)
    {
        $id = preg_replace('/^'.$this->getId().'\-/', '', $id);
        return $this->_content->{$id};
    }

    public function getRecordId()
    {
        return $this->recordId;
    }

    public function getRecord()
    {
        return $this->ar;
    }
}

class org_glizy_components_RecordDetailVO
{
    private $content;
    function __construct( $content )
    {
        $this->content = $content;
    }

    public function __get($name)
    {
        $value = $this->content->{$name};
        if (is_string($value) && strrpos($value, '<')!==false) {
            // TODO migliorare
            $value = org_glizy_helpers_Link::parseInternalLinks($value);
        }
        return $value;
    }
}


if (!class_exists("org_glizy_components_RecordDetail_render"))
{
    class org_glizy_components_RecordDetail_render extends org_glizy_components_render_Render
    {
        function getDefaultSkin()
        {
        $skin = <<<EOD
<span>ERROR: custom skin required<br /></span>
EOD;
        return $skin;
        }
    }
}