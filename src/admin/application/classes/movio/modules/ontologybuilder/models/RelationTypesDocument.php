<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class movio_modules_ontologybuilder_models_RelationTypesDocument extends org_glizycms_models_ActiveRecordSimpleDocumentArraysIndexed
{
    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setType('relation_types_tbl');

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
                                        'translation',
                                        Doctrine\DBAL\Types\Type::TARRAY,
                                        255,
                                        false,
                                        new org_glizy_validators_NotNull(),
                                        '',
                                        true,
                                        false,
                                        '',
                                        org_glizy_dataAccessDoctrine_DbField::INDEXED
                                        )
                        );

        $this->addField(new org_glizy_dataAccessDoctrine_DbField(
                                        'cardinality',
                                        Doctrine\DBAL\Types\Type::INTEGER,
                                        1,
                                        false,
                                        new org_glizy_validators_NotNull(),
                                        0,
                                        true,
                                        false,
                                        '',
                                        org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED
                                        )
                        );
    }

    function query_all($iterator) {
    }

    function query_relationTypesFromLanguage($iterator, $language) {
        $iterator->where("translation LIKE $language:%")
                 ->orderBy('translation');
    }

    function query_relationLabelsFromTerm($iterator, $language, $term) {
        $iterator->where('translation', "$language:$term%", 'LIKE')
        	     ->orderBy('translation');
    }
}