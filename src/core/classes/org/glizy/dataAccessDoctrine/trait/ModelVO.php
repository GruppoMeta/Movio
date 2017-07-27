<?php

trait org_glizy_dataAccessDoctrine_trait_ModelVO
{
    public $__id;
    public $__title = '';
    public $__url = '';
    public $__comment = '';
    public $__indexFields = array();

    public function setId($value)
    {
        $this->__id = intval($value);
    }

    public function getId()
    {
        return $this->__id;
    }

    public function setTitle($value)
    {
        $this->__title = $value;
    }

    public function setUrl($value)
    {
        $this->__url = $value;
    }

    public function getUrl($value)
    {
        return $this->__url;
    }

    public function setType($value)
    {
        $this->questionType = $value;
    }

    public function getType()
    {
        return $this->questionType;
    }

    public function setIndexFields(array $indexFields)
    {
        $this->__indexFields = $indexFields;
    }

    public function getComment()
    {
        return $this->__comment;
    }

    public function setFromJson($data)
    {
        $data = json_decode($data);
        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, 'pageEdit_command') === 0) continue;
            $this->$k = $v;
        }
        $this->__indexFields = $data->__indexFields ? json_decode($data->__indexFields): array();
    }
}