<?php

class org_glizy_dataAccessDoctrine_RelationJoinTable extends org_glizy_dataAccessDoctrine_AbstractRelation
{
    protected $iterator = NULL;
    protected $bindTo = '';
    protected $objectName = '';
    protected $objectField = 'join_objectName';
    protected $ordered = true;
    protected $newRecord;

    function __construct($parent, $options)
    {
        parent::__construct($parent, $options);
		assert(isset($options['field']));
		$this->key = $options['field'];
		assert(!is_null($options['destinationField']));
		$this->destinationKey = $options['destinationField'];
		$this->bindTo = $options['bindTo'];
        $this->objectName = $options['objectName'];
	}

    function build($params=array())
    {
        $this->record = org_glizy_ObjectFactory::createModel($this->className);
        $this->iterator = null;

        $parentId = $this->parent->getId();
        
        if (!is_null($parentId)) {
            $this->newRecord = empty( $parentId );
            
            $this->iterator = org_glizy_ObjectFactory::createModelIterator($this->className)
                            ->where($this->key, $parentId)
                            ->where($this->objectField, $this->objectName)
                            ->orderBy($this->record->getPrimaryKeyName());
            
            if (is_null($this->parent->{$this->bindTo})) {
                $this->record = null;
                $values = array();
                foreach($this->iterator as $ar) {
                    $values[] = $ar->{$this->destinationKey};
                    $this->record = $this->iterator->current();
                }
                
                $this->parent->{$this->bindTo} = implode(',', $values);
            } else {
                $this->record = $this->iterator->current();
            }

            if ($this->iterator->count()) {
                $this->iterator->first();
            }
        }
    }

    function postSave()
    {
        $values = $this->parent->{$this->bindTo};
        
        if (is_null($values)) {
            return;
        }
        
        $values = is_string($values) ? !empty($values) ? explode(',', $values) : array()
                                       : $values;
        if ( !is_array( $values ) ) {
            $values = array( $values );
        }
        
        if (is_null($this->record->{$this->destinationKey})
            || $this->record->{$this->destinationKey} == $this->record->getField($this->destinationKey)->defaultValue
            || is_null($this->iterator)) {
            // nuovo record
            $parentId = $this->parent->getId();
            foreach ($values as $v) {
                $this->record = org_glizy_ObjectFactory::createModel($this->className);
                $this->record->{$this->key} = $parentId;
                $this->record->{$this->destinationKey} = $v;
                $this->record->{$this->objectField} = $this->objectName;
                $this->record->save();
            }
        } else {
            $recordIds = array();
            foreach ($this->iterator as $ar) {
                if ( $this->ordered ) {
                    if ( !$this->newRecord ) {
                        $ar->delete();
                    }
                } else {
                    if (!in_array($ar->{$this->destinationKey}, $values)) {
                        $ar->delete();
                    } else {
                        $recordIds[] = $ar->{$this->destinationKey};
                    }
                }
            }

            if (count($values)) {
                foreach ($values as $v) {
                    if (!in_array($v, $recordIds)) {
                        $this->record = org_glizy_ObjectFactory::createModel($this->className);
                        $this->record->{$this->key} = $this->parent->getId();
                        $this->record->{$this->destinationKey} = $v;
                        $this->record->{$this->objectField} = $this->objectName;
                        $newId = $this->record->save();
                    }
                }
            }
        }
    }
    
    public function delete()
    {
        $this->iterator = org_glizy_ObjectFactory::createModelIterator($this->className)
                        ->where($this->key, $this->parent->getId())
                        ->where($this->objectField, $this->objectName);
        
        foreach ($this->iterator as $ar) {
            $ar->delete();
        }
    }
}