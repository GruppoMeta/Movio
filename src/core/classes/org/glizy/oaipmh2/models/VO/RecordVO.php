<?php
class org_glizy_oaipmh2_models_VO_RecordVO
{
    public $id;
    public $datestamp;
    public $setSpec;
    public $document;
    public $deleted = false;

    /**
     * @param string $identifier
     * @param string $datestamp
     * @param string $model
     * @param boolean $deleted
     * @return org_glizy_oaipmh2_models_VO_RecordVO
     */
    public static function create($id, $datestamp, $setSpec, $document = null, $deleted = false)
    {
        $self = new self;
        $self->id = $id;
        $self->datestamp = $datestamp;
        $self->setSpec = $setSpec;
        $self->document = $document;
        $self->deleted = $deleted;

        return $self;
    }

}
