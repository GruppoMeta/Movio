<?php

class org_glizycms_core_helpers_Fulltext extends GlizyObject
{
    function make($data, $model, $setValuesInModel = false)
    {
        $fulltext = '';
        
        if ($model->fieldExists('title')) {
            $fulltext = $model->title.org_glizycms_Glizycms::FULLTEXT_DELIMITER;
        }
        
        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$model->fieldExists($k)) continue;
            
            if ($setValuesInModel) {
                $model->$k = $v;
            }
            
            if (is_string($v) || is_numeric($v)) {
                $stripped = trim(strip_tags($v));
                if (strlen($stripped) > org_glizycms_Glizycms::FULLTEXT_MIN_CHAR || is_numeric($v)) {
                    $fulltext .= $stripped.org_glizycms_Glizycms::FULLTEXT_DELIMITER;
                }
            }
        }
        
        return $fulltext;
    }
}