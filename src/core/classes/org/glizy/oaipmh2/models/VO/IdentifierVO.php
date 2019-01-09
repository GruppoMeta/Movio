<?php
class org_glizy_oaipmh2_models_VO_IdentifierVO
{
    public $setSpec;
    public $id;
    public $identifier;

    /**
     * @param string $identifier
     * @param string $datestamp
     * @param string $setSpec
     * @param boolean $deleted
     * @return org_glizy_oaipmh2_models_VO_IdentifierVO
     */
    public static function create($setSpec, $id, $identifier)
    {
        $self = new self;
        $self->setSpec = $setSpec;
        $self->id = $id;
        $self->identifier = $identifier;

        return $self;
    }
}
