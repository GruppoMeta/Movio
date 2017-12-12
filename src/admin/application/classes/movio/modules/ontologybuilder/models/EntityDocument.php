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

    public function query_All($iterator) {
        $iterator->whereTypeIs('entity%', 'LIKE');
    }

    public function query_allFromTypeAllStatusAllLanguages($iterator, $entityTypeId) {
        $iterator->whereTypeIs('entity'.$entityTypeId)
                 ->allStatuses()
                 ->allLanguages();
    }

    public function query_allFromTypeRequest($iterator) {
        $iterator->whereTypeIs('entity'.__Request::get('entityTypeId'))
        				 ->orderBy('title');
    }

    public function query_getReferenceRelations($iterator, $entityTypeId, $attribute, $value) {
        $iterator->where($attribute, $value)
                 ->whereTypeIs('entity'.$entityTypeId);
    }

    public function query_documentWithDictionaryOrTerm($iterator, $dictionaryId, $termId)
    {
        $iterator
            ->select('doc.*', 'doc_detail.*', 't3.document_detail_object as term', 't3.document_detail_FK_document_id as termId')
            ->join('doc', 'documents_index_int_tbl', 't2', 't2.document_index_int_FK_document_detail_id = doc_detail.document_detail_id')
            ->join('doc', 'documents_detail_tbl', 't3', 't2.document_index_int_value = t3.document_detail_FK_document_id AND t3.document_detail_FK_language_id = doc_detail.document_detail_FK_language_id')
            ->allTypes()
            ->orderBy('title', 'ASC');

        $qb = $iterator->qb();
        $qb->andWhere($qb->expr()->comparison('t2.document_index_int_name', '=', ':dictionary_id'))
            ->andWhere($qb->expr()->comparison('t3.document_detail_status', '=', ':term_published'))
            ->setParameter(':dictionary_id', 'attribute.thesaurus.'.$dictionaryId)
            ->setParameter(':term_published', 'PUBLISHED');
        if ($termId) {
            $qb->andWhere($qb->expr()->comparison('t2.document_index_int_value', '=', ':term_id'))  
                ->setParameter(':term_id', $termId);
        }
    }
}