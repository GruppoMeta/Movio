<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
 
use Doctrine\DBAL\Types\Type;

class org_glizycms_models_ActiveRecordSimpleDocumentArraysIndexed extends org_glizy_dataAccessDoctrine_ActiveRecordSimpleDocument
{
    protected function insertValuesIntoIndex($id, $values)
    {
        foreach ($values as $fieldName => $value)
        {
            $field = $this->fields[$fieldName];
            
            if (!$field->virtual && !$field->key && $field->index != $field::NOT_INDEXED && isset(self::$typeMap[$field->type])) {
                $indexFieldType = $this->getIndexFieldType($fieldName);
                $tableName = self::DOCUMENT_INDEX_TABLE_PREFIX . $indexFieldType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexFieldType;
                
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if ($v != '') {
                            $documentIndex = array(
                                                $fieldPrefix.'_FK_simple_document_id' => $id, 
                                                $fieldPrefix.'_name' => $fieldName,
                                                $fieldPrefix.'_value' => $k.":".$v,
                                            );
                                            
                            $this->connection->insert($tableName, $documentIndex);
                        }
                    } 
                }
                else {
                    $documentIndex = array(
                                        $fieldPrefix.'_FK_simple_document_id' => $id, 
                                        $fieldPrefix.'_name' => $fieldName,
                                        $fieldPrefix.'_value' => $value,
                                    );
                                    
                    $this->connection->insert($tableName, $documentIndex);
                }
            }
        }
    }
    
    protected function updateValuesIntoIndex($fields, $values)
    {
        // per ogni campo modificato si va a modificare la corrispondente tabella indice
        foreach ($fields as $fieldName => $field) {
            if (!$field->virtual && !$field->key && $field->index != $field::NOT_INDEXED && isset(self::$typeMap[$field->type])) {
                $indexFieldType = $this->getIndexFieldType($fieldName);
                $tableName = self::DOCUMENT_INDEX_TABLE_PREFIX . $indexFieldType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexFieldType;
               
                $value = $values[$fieldName];

                $indexIdentifier = array(
                                    $fieldPrefix.'_FK_simple_document_id' => $this->getId(),
                                    $fieldPrefix.'_name' => $fieldName
                                );
    
                $this->connection->delete($tableName, $indexIdentifier);
    
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if ($v != '') {
                            $documentIndex = array_merge($indexIdentifier, array($fieldPrefix.'_value' => $k.":".$v));
                            $this->connection->insert($tableName, $documentIndex);
                        }
                    } 
                }
                else {
                    $documentIndex = array_merge($indexIdentifier, array($fieldPrefix.'_value' => $value));
                    $this->connection->insert($tableName, $documentIndex);
                }
            }
        }
    }
}