<?php
class movio_modules_ontologybuilder_models_FieldTypesDocument extends org_glizy_dataAccessDoctrine_ActiveRecordSimpleDocument {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setType('field_types_tbl');

        $notNullAndText = new org_glizy_validators_CompositeValidator();
        $notNullAndText->add(new org_glizy_validators_NotNull());
        $notNullAndText->add(new org_glizy_validators_Text());

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                                        'key',
                                        Doctrine\DBAL\Types\Type::STRING,
                                        255,
                                        false,
                                        $notNullAndText,
                                        '',
                                        true,
                                        false,
                                        '',
                                        org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED
                                        )
                        );

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                                        'map_to',
                                        Doctrine\DBAL\Types\Type::STRING,
                                        255,
                                        false,
                                        $notNullAndText,
                                        '',
                                        true,
                                        false,
                                        '',
                                        org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED
                                        )
                        );

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                                        'translation',
                                        Doctrine\DBAL\Types\Type::TARRAY,
                                        255,
                                        false,
                                        new org_glizy_validators_NotNull(),
                                        '',
                                        true,
                                        false,
                                        '',
                                        org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED
                                        )
                        );
    }

    function query_all($iterator) {
    }
}
