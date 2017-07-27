<?php
class org_glizy_request_inputFilters_StripTagsFilter implements org_glizy_request_interfaces_IInputFilter
{
    public function filter($values, $excludedFields=null)
    {
        if (!$excludedFields) {
            $excludedFields = [];
        }

        $newValues = [];
        foreach($values as $k=>$v) {
            if (in_array($k, $excludedFields) || !isset($newValues[$k][GLZ_REQUEST_VALUE])) {
                $newValues[$k] = $v;
            } else {
                $newValues[$k] = [];
                $newValues[$k][GLZ_REQUEST_VALUE] = filter_var($v[GLZ_REQUEST_VALUE], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                $newValues[$k][GLZ_REQUEST_TYPE] = $v[GLZ_REQUEST_TYPE];
            }
        }

        return $newValues;
    }
}

