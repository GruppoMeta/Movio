<?php
class movio_modules_ontologybuilder_models_EntityDocument extends org_glizy_dataAccessDoctrine_ActiveRecordDocument {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'title',
                            Doctrine\DBAL\Types\Type::STRING,
                            255,
                            false,
                            null,
                            '')
        );

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'subtitle',
                            Doctrine\DBAL\Types\Type::STRING,
                            255,
                            false,
                            null,
                            '',
                            false,
                            false,
                            '',
                            org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED)
        );



        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'url',
                            Doctrine\DBAL\Types\Type::STRING,
                            255,
                            false,
                            null,
                            '',
                            false,
                            false,
                            '',
                            org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED)
        );

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'profile',
                            Doctrine\DBAL\Types\Type::STRING,
                            255,
                            false,
                            null,
                            '',
                            false)
        );

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'externalId',
                            Doctrine\DBAL\Types\Type::INTEGER,
                            10,
                            false,
                            null,
                            '',
                            false)
        );
    }

    function query_All($iterator) {
        $iterator->whereTypeIs('entity%', 'LIKE');
    }

    function query_allFromTypeAllStatusAllLanguages($iterator, $entityTypeId) {
        $iterator->whereTypeIs('entity'.$entityTypeId)
                 ->allStatuses()
                 ->allLanguages();
    }

    function query_allFromTypeRequest($iterator) {
        $iterator->whereTypeIs('entity'.__Request::get('entityTypeId'))
        				 ->orderBy('title');
    }

    function query_getReferenceRelations($iterator, $entityTypeId, $attribute, $value) {
        $iterator->where($attribute, $value)
                 ->whereTypeIs('entity'.$entityTypeId);
    }
}
