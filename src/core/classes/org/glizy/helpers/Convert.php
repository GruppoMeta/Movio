<?php
class org_glizy_helpers_Convert
{
    public static function formEditObjectToStdObject($data)
    {
        $result = array();
        if (is_object($data)) {
            $objectKeys = array_keys(get_object_vars($data));
            if ($objectKeys) {
                $numItems = 0;
                foreach($objectKeys as $k) {
                    $numItems = max(count($data->{$k}), $numItems);
                }
                for($i=0; $i < $numItems; $i++) {
                    $tempObj = new StdClass;
                    foreach($objectKeys as $k) {
                        $value = $data->{$k}[$i];
                        $tempObj->{$k} = $value;
                    }
                    $result[] = $tempObj;
                }
            }
        }
        return $result;
    }
}