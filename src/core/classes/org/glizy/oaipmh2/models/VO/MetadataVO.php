<?php
class org_glizy_oaipmh2_models_VO_MetadataVO
{
    public $prefix;
    public $schema;
    public $namespace;
    public $recordPrefix;
    public $recordNamespace;

    /**
     * @param array $params
     * @return org_glizy_oaipmh2_models_VO_MetadataVO
     */
    public static function create($prefix, $schema, $namespace, $recordPrefix = '', $recordNamespace = '')
    {
        $self = new self;
        $self->prefix = $prefix;
        $self->schema = $schema;
        $self->namespace = $namespace;
        $self->recordPrefix = $recordPrefix;
        $self->recordNamespace = $recordNamespace;

        return $self;
    }
}



