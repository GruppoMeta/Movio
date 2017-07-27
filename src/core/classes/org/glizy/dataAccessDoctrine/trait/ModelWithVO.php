<?php

trait org_glizy_dataAccessDoctrine_trait_ModelWithVO
{
    private function createVO($VOclass)
    {
        $vo = org_glizy_ObjectFactory::createObject($VOclass);
        $vo->__id = $this->id;
        $vo->__title = $this->title;

        foreach($this->fields as $name => $value) {
            if (property_exists($vo, $name)) {
                $vo->{$name} = $this->{$name};
            }
        }

        if ($this->fieldExists('content')) {
            $content = $this->content;
            if ($content) {
                foreach ($content as $name => $value) {
                    $vo->{$name} = $value;
                }
            }
        }

        return $vo;
    }

    private function setFromVO($data)
    {
        if ($this->isValidVO($data)) {
            if (isset($this->fields['id']) && property_exists($data, '__id')) {
                $this->id = $data->__id;
            }
            if (isset($this->fields['url']) && property_exists($data, '__url')) {
                $this->url = $data->__url;
            }
            if (isset($this->fields['title']) && property_exists($data, '__title')) {
                $this->title = $data->__title;
            }

            foreach($this->fields as $name => $value) {
                if (property_exists($data, $name)) {
                    $this->{$name} = $data->{$name};
                }
            }
            $fulltext = ($this->fieldExists('title') ? $this->title : '' ).org_glizycms_Glizycms::FULLTEXT_DELIMITER;
            $newContent = new StdClass();
            foreach ($data as $k => $v) {
                // remove the system values
                if (strpos($k, '__') === 0 || isset($this->fields[$k])) continue;
                $newContent->$k = $v;
                $this->addInFulltext($v, $fulltext);
            }
            $this->fulltext = $fulltext;
            $this->addFieldsToIndex($data->__indexFields, $this->fieldExists('content'));
            if ($this->fieldExists('content')) {
                $this->content = $newContent;
            }
            return true;
        } else {
            return false;
        }
    }



    private function addInFulltext($value, &$fulltext)
    {
        if (is_array($value) || is_object($value)) {
            foreach($value as $v) {
                $this->addInFulltext($v, $fulltext);
            }
        } else {
            if (strpos($value, '{"')===0) return;
            $stripped = trim(html_entity_decode(strip_tags($value)));
            if (!is_numeric($value) && strlen($stripped) > org_glizycms_Glizycms::FULLTEXT_MIN_CHAR ) {
                $fulltext .= $stripped.org_glizycms_Glizycms::FULLTEXT_DELIMITER;
            }
        }
    }


    // init the index fields they can be:
    // index={tipo} ie. index=true, index=int, index=text
    // for objects in array
    // index={fieldName:type,fieldName:type} ie. id:int,type:text
    private function addFieldsToIndex($indexFields, $useContent=true)
    {
        if (!$indexFields) return;

        $addedFields = array();
        foreach($indexFields as $k=>$v) {
            $fieldPath = explode('.', $k);
            $targetObj = $useContent ? $this->content : $this;
            $targetKeyName = '';
            $targetKeyNamePart = '';
            $found = true;
            foreach($fieldPath as $p) {
                if (strpos($p, '@')!==false) {
                    list($p, $targetKeyNamePart) = explode('@', $p);
                }
                if (!property_exists($targetObj, $p)) {
                    $found = false;
                    break;
                }
                if (is_object($targetObj->{$p})) $targetObj = $targetObj->{$p};
                $targetKeyName = $p;
            }
            if (!$found || is_null($targetObj->{$targetKeyName})) continue;

            $indexType = org_glizy_dataAccessDoctrine_DbField::INDEXED;
            $options = null;
            switch ($v) {
                case 'int':
                    $type = \Doctrine\DBAL\Types\Type::INTEGER;
                    break;
                case 'date':
                    $type = \Doctrine\DBAL\Types\Type::DATE;
                    break;
                case 'fulltext':
                    $type = Doctrine\DBAL\Types\Type::STRING;
                    $indexType = org_glizy_dataAccessDoctrine_DbField::FULLTEXT;
                    break;
                case 'array_id':
                    $type = org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID;
                    break;
                default:
                    $type = Doctrine\DBAL\Types\Type::STRING;
            }

            // verify if the field to index is part of array
            $targetPropName = $targetKeyName;

            if ($targetKeyNamePart) {
                $options = array( org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID => array(
                    'type' => $type,
                    'field' => $targetKeyNamePart
                ));
                $type = org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID;
                $targetPropName .= $targetKeyNamePart;
            }

            $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                $targetPropName,
                $type,
                255,
                false,
                null,
                '',
                false,
                false,
                '',
                $indexType | org_glizy_dataAccessDoctrine_DbField::ONLY_INDEX,
                $options
            ));

            if (!in_array($targetPropName, $addedFields)) {
                $this->{$targetPropName} = array();
                $addedFields[] = $targetKeyName;
            }

            $this->{$targetPropName} = array_merge($this->{$targetPropName}, is_array($targetObj->{$targetKeyName}) ? $targetObj->{$targetKeyName} : array($targetObj->{$targetKeyName}));
        }
    }
}