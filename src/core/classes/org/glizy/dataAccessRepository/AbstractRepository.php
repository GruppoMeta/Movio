<?php
abstract class org_glizy_dataAccessRepository_AbstractRepository
{
    use org_glizy_dataAccessRepository_EntityBuilderTrait;

    protected $modelName;
    protected $entityName;
    protected $ar;
    protected $it;


    public function __construct()
    {
        $this->ar = __ObjectFactory::createModel($this->modelName);
        $this->it = $this->ar->createRecordIterator();
    }

    /**
     * @param int $id
     *
     * @return null|StdClass
     */
    public function findById($id)
    {
        $this->ar->emptyRecord();
        $r = $this->ar->load($id);
        return $r ? $this->arToEntity() : null;
    }

    /**
     * @param  org_glizy_dataAccessRepository_EntityInterface $entity
     * @return int
     */
    public function save(org_glizy_dataAccessRepository_EntityInterface $entity)
    {
        if (!$entity->isValid()) {
            throw new DomainException('Entity is not valid: ' . get_class($entity));
        }

        $this->ar->emptyRecord();
        if (!$entity->isNew()) {
            $this->ar->load($entity->getId());
        }

        foreach($entity as $k => $v) {
            if (is_null($v) && $this->ar->$k) {
                continue;
            }
            $this->ar->$k = $v;
        }

        return $this->ar->save(null, $entity->isNew());
    }

    protected function arToEntity()
    {
        return self::createEntity($this->entityName, $this->ar->getValuesAsArray());
    }
}