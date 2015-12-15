<?php
class org_glizycms_contents_controllers_autocomplete_ajax_FindTerm extends org_glizy_mvc_core_CommandAjax
{
    function execute($fieldName, $model, $query, $term, $proxy, $proxyParams, $getId)
    {
        $fieldName  = explode('-', $fieldName);
        $fieldName = array_pop($fieldName);
        if (!$proxy) {
            $it = org_glizy_objectFactory::createModelIterator($model, $query);

            if ($term != '') {
                $it->where($fieldName, '%'.$term.'%', 'LIKE');
            }

            $it->where($fieldName, '', '<>')
               ->orderBy($fieldName);

            $foundValues = array();

            foreach($it as $ar) {
                if (is_array($ar->$fieldName)) {
                    foreach ($ar->$fieldName as $value) {
                        if ($term == '' || stripos($value, $term) !== false) {
                            if ($getId) {
                                $foundValues[$ar->getId()] = $value;
                            } else {
                                $foundValues[$value] = $value;
                            }
                        }
                    }
                }
                else {
                    if ($getId) {
                        $foundValues[$ar->getId()] = $ar->$fieldName;
                    } else {
                        $foundValues[$ar->$fieldName] = $ar->$fieldName;
                    }
                }
            }

            ksort($foundValues);

            $result = array();

            foreach ($foundValues as $k => $v) {
                $result[] = array(
                            'id' => $k,
                            'text' => $v
                        );
            }
           return $result;
        }
        else {
            $p = $this->application->retrieveProxy($proxy);
            if (!$p) {
                $p = org_glizy_ObjectFactory::createObject($proxy);
            }
            $result = $p->findTerm($fieldName, $model, $query, $term, json_decode($proxyParams));
            return $result;
        }
    }
}