<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type,
    Doctrine\DBAL\Cache\QueryCacheProfile;

class org_glizy_dataAccessDoctrine_RecordIteratorDocument extends org_glizy_dataAccessDoctrine_AbstractRecordIterator
{
    const DOCUMENT_TABLE_ALIAS = 'doc';
    const DOCUMENT_ID = 'document_id';
    const DOCUMENT_TYPE = 'document_type';
    const DOCUMENT_DETAIL_TABLE_ALIAS = 'doc_detail';
    const DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS = 'doc_detail_published';
    const DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS = 'doc_detail_draft';
    const DOCUMENT_DETAIL_FK_LANGUAGE = 'document_detail_FK_language_id';
    const DOCUMENT_DETAIL_STATUS = 'document_detail_status';
    const DOCUMENT_DETAIL_IS_VISIBLE = 'document_detail_isVisible';
    const DOCUMENT_DETAIL_FK_USER = 'document_detail_FK_user_id';
    const DOCUMENT_DETAIL_BASE_PREFIX = 'document_detail_';

    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_OLD = 'OLD';

    protected $conditionNumber;
    protected $indexNumber;
    protected $typeSet;
    protected $statusSet;
    protected $visibilitySet;
    protected $languageSet;
    protected $unionArray;
    protected $unionArrayParams;
    protected $unionOrderBy;
    protected $unionLimit;
    protected $conditionsMap;
    protected $options;

    function __construct($ar)
    {
        parent::__construct($ar);
        $this->fetchMode = PDO::FETCH_NAMED;
        $this->options = array();
    }

    public function setOptions($options)
    {
        $this->options = $options;
        $this->resetQuery();

        return $this;
    }

    protected function initQueryBuilder()
    {
        if ($this->options['type'] == 'PUBLISHED_DRAFT') {
            $options = array_merge(
                $this->options,
                array(
                    'type' => 'PUBLISHED_DRAFT',
                    'tableAlias' => self::DOCUMENT_TABLE_ALIAS,
                    'tableDetailPublishedAlias' => self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS,
                    'tableDetailDraftAlias' => self::DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS,
                )
            );
            $this->qb = $this->ar->createQueryBuilder($options);

            $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $defaultLanguageId = $languageProxy->getDefaultLanguageId();
            if ($defaultLanguageId != $this->ar->getLanguageId()) {
                if ($this->options['multiLanguage'] === false) {
                    $this->qb->select(
                        self::DOCUMENT_TABLE_ALIAS.'.*',
                        self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS.'.*',
                        self::DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS.'.*'
                    );
                } else {
                    $this->qb->select(
                        self::DOCUMENT_TABLE_ALIAS.'.*',
                        self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS.'.*',
                        self::DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS.'.*',
                        self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS.'_defaultLanguage.*',
                        self::DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS.'_defaultLanguage.*'
                    );
                }
            } else {
                $this->qb->select(self::DOCUMENT_TABLE_ALIAS.'.*', self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS.'.*', self::DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS.'.*');
            }

            $this->statusSet = true;
            $this->languageSet = true;
        } else {
            $options = array(
                'tableAlias' => self::DOCUMENT_TABLE_ALIAS,
                'tableDetailAlias' => self::DOCUMENT_DETAIL_TABLE_ALIAS,
            );

            if (isset($this->options['type'])) {
                $options['type'] = $this->options['type'];
            }


            $this->qb = $this->ar->createQueryBuilder($options);
            $this->qb->select(self::DOCUMENT_TABLE_ALIAS.'.*', self::DOCUMENT_DETAIL_TABLE_ALIAS.'.*');

            $this->statusSet = false;
            $this->languageSet = false;
        }

        $this->hasSelect = true;
        $this->hasLimit = false;
        $this->typeSet = false;
        $this->visibilitySet = false;

        $this->conditionsMap = array();
    }

    protected function getDetailTableAlias()
    {
        return ($this->options['type'] == 'PUBLISHED_DRAFT') ? self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS : self::DOCUMENT_DETAIL_TABLE_ALIAS;
    }

    protected function getSystemTableAlias($fieldName)
    {
        return (strpos($fieldName, self::DOCUMENT_DETAIL_BASE_PREFIX)===false) ? self::DOCUMENT_TABLE_ALIAS : $this->getDetailTableAlias();
    }

    protected function resetQuery()
    {
        parent::resetQuery();

        $this->initQueryBuilder();

        $this->conditionNumber = 0;
        $this->indexNumber = 0;

        $this->unionArray = array();
        $this->unionArrayParams = array();
        $this->unionOrderBy = array();

        $this->unionLimit = array();
        $this->conditionsMap = array();
    }

    protected function processUnionFields($fieldName, $value)
    {
        if (strpos($fieldName, ',') !== false && !$this->ar->fieldExists($fieldName)) {
            if (!empty( $value )) {
                $fields = explode(',', $fieldName);

                $v = array();
                foreach ($fields as $field) {
                    $v[] = array('field' => $field, 'value' => '%'.$value,'%', 'condition' => 'LIKE' );
                }
                $value = $v;
            }
        } else if (!$this->ar->fieldExists($fieldName)) {
            return '';
        }

        return $value;
    }

    public function setOrFilters($filters)
    {
        $i = 0;

        /**
         La forma del filters può essere
         $filters['nome campo'] = 'valore';
         $filters['nome campo'] = array('value' => '', 'condition' => '');
         $filters['nome campo'] = array(array('field' => '', value' => '', 'condition' => ''), array('field' => '', 'value' => '', 'condition' => ''))
         */
        foreach ($filters as $fieldName => $value) {
            if (is_array($value)) {
               if (isset($value['value']) && !is_array($value['value'])) {
                    $this->where(isset($value['field']) ? $value['field'] : $fieldName, $value['value'], $value['condition']);
                } else {
                    foreach ($value as $v) {
                        $this->where($v['field'], $v['value'], $v['condition']);
                    }
                }
            } else {
                $this->where($fieldName, $value);
            }

            if ($i != count($filters)-1) {
                $this->newQueryInOr();
                $i++;
            }
        }

        return $this;
    }


    public function selectDistinct($fieldName, $fields=array())
    {
        $indexData = $this->addIndex($fieldName, $this->options['type']);
        $indexValue = $indexData['indexFieldPrefixAlias'].'_value';
        $this->qb->resetQueryPart('select');
        if ($this->ar->getDriverName() == 'mysql') {
            $this->qb->select('DISTINCT('.$indexValue.'), '.$indexValue.' AS '.$fieldName);
        } else {
            $this->qb->select('DISTINCT ON('.$indexValue.') '.$indexValue.' AS '.$fieldName);
        }
        $this->hasSelect = true;
        return $this;
    }

    protected function addIndex($fieldName, $type=null, $joinType=null, $force=false)
    {
        if (isset($this->conditionsMap[$fieldName]) && !$force) {
            return $this->conditionsMap[$fieldName];
        }

        $indexAlias = 'index'.$this->indexNumber;
        $indexType = $this->ar->getIndexFieldType($fieldName);

        $documentDetailIdName = $this->ar->getDocumentDetailTableIdName();

        $documentIndexTablePrefix = $this->ar->getDocumentIndexTablePrefix();
        $indexTablePrefix = $documentIndexTablePrefix.$indexType;

        $documentIndexFieldPrefix = $this->ar->getDocumentIndexFieldPrefix();
        $indexFieldPrefixAlias = $indexAlias.'.'.$documentIndexFieldPrefix.$indexType;

        switch ($type) {
            case 'PUBLISHED_DRAFT':
                $detailTableAlias = self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS;
                $joinType = 'LEFT_JOIN';
                break;

            case 'PUBLISHED':
                $detailTableAlias =  self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS;
                break;

            case 'DRAFT':
                $detailTableAlias = self::DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS;
                break;

            default:
                $detailTableAlias = self::DOCUMENT_DETAIL_TABLE_ALIAS;
        }

        $expr = $this->qb->expr()->andX(
            $this->expr->eq($detailTableAlias.'.'.$documentDetailIdName, $indexFieldPrefixAlias."_FK_document_detail_id"),
            $this->expr->eq("{$indexFieldPrefixAlias}_name", ":name{$this->indexNumber}")
        );
        if ($joinType == 'LEFT_JOIN') {
            $this->qb->leftJoin(self::DOCUMENT_TABLE_ALIAS, $indexTablePrefix.'_tbl', $indexAlias, $expr);
        } else {
            $this->qb->join(self::DOCUMENT_TABLE_ALIAS, $indexTablePrefix.'_tbl', $indexAlias, $expr);
        }
        $this->qb->setParameter(":name{$this->indexNumber}", $fieldName);

        $indexData = array(
            'indexNumber' => $this->indexNumber,
            'indexAlias' => $indexAlias,
            'indexFieldPrefixAlias' => $indexFieldPrefixAlias,
            'indexType' => $indexType
        );

        $this->conditionsMap[$fieldName] = $indexData;

        $this->indexNumber++;

        return $indexData;
    }

    // il primo parametro è il campo da cui si ricava l'indice da selezionare
    public function selectIndex($fieldName, $key, $value)
    {
        $indexData = $this->addIndex($fieldName, $this->options['type']);
        $indexAlias = $indexData['indexAlias'];
        $this->select($indexAlias.'.'.$key, $indexAlias.'.'.$value);
        return $this;
    }

    public function where($fieldName, $value = null, $condition = '=')
    {
        if ($fieldName == self::DOCUMENT_TYPE) {
            return $this->whereTypeIs($value, $condition);
        } else if ($fieldName == self::DOCUMENT_ID) {
            return $this->whereDocumentIdIs($value);
        } else if ($fieldName == self::DOCUMENT_DETAIL_FK_LANGUAGE) {
            return $this->whereLanguageIs($value);
        } else if ($fieldName == self::DOCUMENT_DETAIL_STATUS) {
            return $this->whereStatusIs($value);
        } else if ($this->ar->getField($fieldName)->isSystemField) {
            return $this->whereSystemField($fieldName, $value, $condition);
        } else {
            return parent::where($fieldName, $value, $condition);
        }
    }

    protected function whereCondition($fieldName, $value, $condition = '=', $composite = null)
    {
        if ($this->options['type'] == 'PUBLISHED_DRAFT') {
            $indexDataPublished = $this->addIndex($fieldName, 'PUBLISHED', 'LEFT_JOIN', true);
            $indexDataDraft = $this->addIndex($fieldName, 'DRAFT', 'LEFT_JOIN', true);
            $expr = $this->qb->expr()->orX(
                $this->getExpr($fieldName, $value, $condition, $indexDataPublished),
                $this->getExpr($fieldName, $value, $condition, $indexDataDraft)
            );
        } else {
            $indexData = $this->addIndex($fieldName);
            $expr = $this->getExpr($fieldName, $value, $condition, $indexData);
        }

        $this->qb->andWhere($expr);

        return $this;
    }

    protected function getExpr($fieldName, $value, $condition, $indexData)
    {
        $indexFieldPrefixAlias = $indexData['indexFieldPrefixAlias'];
        $indexNumber = $indexData['indexNumber'];

        $valueColumn = "{$indexFieldPrefixAlias}_value";
        $valueParam =  ":value{$indexNumber}";

        $cast = $indexData['indexType'] != 'text' && $indexData['indexType'] != 'fulltext';

        $fieldType = $this->ar->getField($fieldName)->type;

        // NOTA: nel caso di un campo di tipo array
        // viene passato 'array' come tipo di ricerca nel where e crea dei problemi perché non trova nulla
        if ($fieldType) {
            $fieldType = 'text';
        }

        $this->qb->setParameter($valueParam, $value, $fieldType);

        if ($indexData['indexType']=='fulltext') {
            // NOTA: valido solo per mysql
            return $this->qb->expr()->sql('MATCH ('.$valueColumn.') AGAINST ('.$valueParam.' IN BOOLEAN MODE)');
        }

        return $this->qb->expr()->comparison($valueColumn, $condition, $valueParam, $cast);
    }

    public function newQueryInOr()
    {
        $this->finalizeQuery();
        $this->unionArray[] = $this->qb;
        $this->unionArrayParams = array_merge($this->unionArrayParams, $this->qb->getParameters());

        $this->initQueryBuilder();

        return $this;
    }

    public function whereTypeIs($type, $condition = '=')
    {
        $valueParam = ':type'.$this->conditionNumber++;
        $fieldName = self::DOCUMENT_TABLE_ALIAS.'.'.self::DOCUMENT_TYPE;
        $this->qb->andWhere($this->expr->comparison($fieldName, $condition, $valueParam))
                 ->setParameter($valueParam, $type);
        $this->typeSet = true;

        return $this;
    }

    public function allTypes()
    {
        $this->typeSet = true;
        return $this;
    }

    public function whereStatusIs($value)
    {
        $valueParam = ':status'.$this->conditionNumber++;
        $this->qb->andWhere($this->expr->eq(self::DOCUMENT_DETAIL_TABLE_ALIAS.'.'.self::DOCUMENT_DETAIL_STATUS, $valueParam))
                 ->setParameter($valueParam, $value);
        $this->statusSet = true;
        return $this;
    }

    public function allStatuses()
    {
        $this->statusSet = true;
        return $this;
    }

    public function whereLanguageIs($value)
    {
        $alias = $this->getDetailTableAlias();
        $this->qb->andWhere($this->expr->eq($alias.'.'.self::DOCUMENT_DETAIL_FK_LANGUAGE, ':language'))
                 ->setParameter(':language', $value);
        $this->languageSet = true;
        return $this;
    }

    public function allLanguages()
    {
        $this->languageSet = true;
        return $this;
    }

    public function whereDocumentIdIs($value)
    {
        $paramName = ':documenId_'.count($this->unionArray);
        $this->qb->andWhere($this->expr->eq(self::DOCUMENT_ID, $paramName))
                 ->setParameter($paramName, $value);
        return $this;
    }

    protected function whereSystemField($fieldName, $value, $condition = '=')
    {
        $alias = $this->getSystemTableAlias($fieldName);
        $valueParam = ':system'.$this->conditionNumber++;
        $this->qb->andWhere($this->expr->comparison($alias.'.'.$fieldName, $condition, $valueParam))
                 ->setParameter($valueParam, $value);
        return $this;
    }


    public function showAll()
    {
        $this->visibilitySet = true;
        return $this;
    }

    public function showVisible()
    {
        $alias = $this->getDetailTableAlias();
        $valueParam = ':isVisible'.$this->conditionNumber++;
        $this->qb->andWhere($this->expr->eq($alias.'.'.self::DOCUMENT_DETAIL_IS_VISIBLE, $valueParam))
                 ->setParameter($valueParam, 1);
        $this->visibilitySet = true;
        return $this;
    }

    public function limit($offset, $limit = -1, $performCount=true)
    {
        if (!empty($this->unionArray)) {
            if (is_array($offset)) {
                if (!isset($offset['start'])) {
                    list($offset, $limit) = array_values($offset);
                } else {
                    $limit = $offset['pageLength'];
                    $offset = $offset['start'];
                }
            }

            $this->unionLimit = array($limit, $offset);
            return $this;
        }

        return parent::limit($offset, $limit, $performCount);
    }

    public function orderBy($fieldName, $order = 'ASC')
    {
        if (!empty($this->unionArray)) {
            $this->unionOrderBy = array($fieldName, $order);
            return $this;
        }

        if ($this->ar->getField($fieldName)->isSystemField) {
            return $this->orderBySystemField($fieldName, $order);
        } else {
            $indexData = $this->addIndex($fieldName, $this->options['type']);
            $indexFieldPrefixAlias = $indexData['indexFieldPrefixAlias'];
            $this->qb->addOrderBy("{$indexFieldPrefixAlias}_value", $order);
            return $this;
        }
    }

    protected function orderBySystemField($fieldName, $order = 'ASC')
    {
        $alias = $this->getSystemTableAlias($fieldName);
        $this->qb->addOrderBy($alias.'.'.$fieldName, $order);
        return $this;
    }

    private function finalizeQuery()
    {
        if (!$this->typeSet) {
            $this->whereTypeIs($this->ar->getType());
        }

        if (!$this->statusSet && !$this->options['type'] == 'PUBLISHED_DRAFT') {
            $this->whereStatusIs(self::STATUS_PUBLISHED);
        }

        if (!$this->languageSet) {
            $this->whereLanguageIs($this->ar->getLanguageId());
        }

        if (__Config::get('glizy.dataAccess.documentVisibility') && !$this->visibilitySet) {
            $this->showVisible();
        }
    }

    // TODO aggiustare
    public function addAcl()
    {
        $qb = $this->qb;
        $and = $qb->expr()->andX();
        $and->add($qb->expr()->eq('acl.join_FK_source_id', self::DOCUMENT_TABLE_ALIAS.'.'.self::DOCUMENT_ID));
        $and->add($qb->expr()->eq('acl.join_objectName', ':join_objectName'));

        $or = $qb->expr()->orX();
        $or->add($qb->expr()->isNull('acl.join_FK_dest_id'));

        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $user = $application->getCurrentUser();
        $roles = $user->getRoles();

        $i = 0;

        foreach ($roles as $role) {
            $roleParam = ':role'.$i++;
            $or->add($qb->expr()->eq('acl.join_FK_dest_id', $roleParam));
            $qb->setParameter($roleParam, $role);
        }

        $qb->addSelect('acl.*')
           ->leftJoin(self::DOCUMENT_TABLE_ALIAS, $this->ar->getTablePrefix().'joins_tbl', 'acl', $and)
           ->andWhere($or)
           ->setParameter(':join_objectName', 'documents_tbl#rel_aclView');

        return $this;
    }

    public function exec()
    {
        if (!$this->querySqlToExec) {
            $this->finalizeQuery();
        }

        if (empty($this->unionArray)) {
            if (__Config::get('ACL_ROLES')) {
                $application = org_glizy_ObjectValues::get('org.glizy', 'application');
                $user = $application->getCurrentUser();
                if ($application->getPageId() && !$user->acl($application->getPageId(), 'all')) {
                    $this->addAcl();
                }
            }

            try {
                parent::exec();
            } catch (Exception $e) {
                require_once(org_glizy_Paths::get('CORE_LIBS').'sql-formatter/lib/SqlFormatter.php');
                $trace = $e->getTrace();
                var_dump($trace[0]['args'][0]->errorInfo);
                echo SqlFormatter::format($sql).'</br></br>';
                var_dump($this->unionArrayParams);
            }
        } else {
            $this->unionArray[] = $this->qb;
            $this->unionArrayParams = array_merge($this->unionArrayParams, $this->qb->getParameters());

            if (!empty($this->unionOrderBy)) {
                list($fieldName, $order) = $this->unionOrderBy;

                if (!$this->ar->getField($fieldName)->isSystemField) {
                    $indexType = $this->ar->getIndexFieldType($fieldName);
                    $documentDetailIdName = $this->ar->getDocumentDetailTableIdName();
                    $documentTableIndexPrefix = $this->ar->getDocumentIndexTablePrefix();
                    $indexAlias = 'orderIndex';
                    $indexTablePrefix = $documentTableIndexPrefix.$indexType;

                    $documentFieldIndexPrefix = $this->ar->getDocumentIndexFieldPrefix();
                    $indexFieldPrefix = $documentFieldIndexPrefix.$indexType;
                    $indexFieldPrefixAlias = $indexAlias.'.'.$indexFieldPrefix;

                    $detailAlias = ($this->options['type'] == 'PUBLISHED_DRAFT') ? self::DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS : self::DOCUMENT_DETAIL_TABLE_ALIAS;

                    foreach ($this->unionArray as $qb) {
                        $qb->addSelect('orderIndex.*');
                        $qb->join(self::DOCUMENT_TABLE_ALIAS, $indexTablePrefix.'_tbl', 'orderIndex',
                                  $this->expr->eq($detailAlias.'.'.$documentDetailIdName, $indexFieldPrefixAlias.'_FK_document_detail_id'));
                        $qb->andWhere($this->expr->eq("{$indexFieldPrefixAlias}_name", ':orderIndex'));
                    }

                    $this->unionArrayParams[":orderIndex"] = $fieldName;

                    $orderSql = "ORDER BY {$indexFieldPrefix}_value $order";
                }
                else {
                    $orderSql = "ORDER BY $fieldName $order";
                }
            }

            $sqlArray = array();

            foreach ($this->unionArray as $qb) {
                if (!$this->siteSet && $this->ar->getSiteField()) {
                    $qb->andWhere($this->expr->eq($this->ar->getSiteField(), ':site'));
                    $this->unionArrayParams[":site"] = $this->ar->getSiteid();
                }

                $sqlArray[] = $qb->getSql();
            }

            $sql = '(' . implode(') UNION (', $sqlArray) . ')';

            if (!empty($this->unionOrderBy)) {
                $sql .= $orderSql;
            }
            if ($this->unionLimit && count($this->unionLimit)) {
                $sql .= ' LIMIT '.$this->unionLimit[0].' OFFSET '.$this->unionLimit[1];
            }

            try {
                if (__Config::get('QUERY_CACHING') && ($cacheDriver = org_glizy_dataAccessDoctrine_DataAccess::getCache())) {
                    $lifeTime = __Config::get('QUERY_CACHING_LIFETIME');
                    $key = md5($sql);
                    $this->statement = $this->ar->getConnection()->executeQuery($sql, $this->unionArrayParams, array(), new QueryCacheProfile($lifeTime, $key, $cacheDriver));
                } else {
                    $this->statement = $this->ar->getConnection()->executeQuery($sql, $this->unionArrayParams);
                }
            } catch (Exception $e) {
                require_once(org_glizy_Paths::get('CORE_LIBS').'sql-formatter/lib/SqlFormatter.php');
                $trace = $e->getTrace();
                var_dump($trace[0]['args'][0]->errorInfo);
                echo SqlFormatter::format($sql).'</br></br>';
                var_dump($this->unionArrayParams);
            }

            $this->resetQuery();
        }
    }

    /**
     * @param string|array $sql
     * @param array $options
     * @return void
     */
    public function execSql($sql, $options=array())
    {
        if (is_string($sql)) {
            $sql = array('sql' => $sql);
        }

        $params = isset($options['params']) ? $options['params'] : ( is_array($options) ? $options : array());
        $params = isset($sql['params']) ? array_merge($sql['params'], $params) : $params;
        $this->executeSqlWithRowsCount($sql['sql'], $params);
    }
}
