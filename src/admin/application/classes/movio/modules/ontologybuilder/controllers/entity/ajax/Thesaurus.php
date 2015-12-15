<?php
class movio_modules_ontologybuilder_controllers_entity_ajax_Thesaurus extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        return array('sendOutput' => 'thesaurus', 'sendOutputState' => 'thesaurus', 'sendOutputFormat' => 'html');
    }
}
