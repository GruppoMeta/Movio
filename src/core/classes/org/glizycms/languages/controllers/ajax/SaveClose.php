<?php
class org_glizycms_languages_controllers_ajax_SaveClose extends org_glizycms_languages_controllers_ajax_Save
{
    function execute($data)
    {
        $result = parent::execute($data);

        if ($result['errors']) {
            return $result;
        }

        return array('url' => $this->changeAction(''));
    }
}