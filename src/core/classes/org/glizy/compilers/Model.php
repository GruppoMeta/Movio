<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_Model extends org_glizy_compilers_Compiler
{
    function compile( $options )
    {
        if (!file_exists( $this->_fileName )) {
            throw org_glizy_compilers_CompilerException::fileNotFound($this->_fileName);
        }

        $this->initOutput();

        $xml = org_glizy_ObjectFactory::createObject('org.glizy.parser.XML');
        $xml->loadAndParseNS($this->_fileName);
        $xmlRootNode         = $xml->documentElement;
        $className           = glz_basename($this->_cacheObj->getFileName());
        $tableName           = $xmlRootNode->getAttribute('model:tableName');
        if (empty($tableName)) {
            throw org_glizy_compilers_ModelException::missingTableName($this->_fileName);
        }
        $joinFields         = $xmlRootNode->hasAttribute('model:joinFields') ? explode(',', $xmlRootNode->getAttribute('model:joinFields')) : null;
        $modelType          = $xmlRootNode->hasAttribute('model:type') ? $xmlRootNode->getAttribute('model:type') : 'activeRecord';
        $dbConnection       = $xmlRootNode->hasAttribute('model:connection') ? $dbConnection = $xmlRootNode->getAttribute('model:connection') : '0';
        $usePrefix          = $xmlRootNode->hasAttribute('model:usePrefix') &&
                              $xmlRootNode->getAttribute('model:usePrefix') == 'true' ? 'true' : 'false';
        $languageField      = $xmlRootNode->hasAttribute('model:languageField') ? '$this->setLanguageField(\''.$xmlRootNode->getAttribute('model:languageField').'\');' : '';
        $siteField          = $xmlRootNode->hasAttribute('model:siteField') ? '$this->setSiteField(\''.$xmlRootNode->getAttribute('model:siteField').'\');' : '';
        $originalClassName  = isset($options['originalClassName']) ? '$this->_className = \''.$options['originalClassName'].'\';' : '';
        $baseClass          = $xmlRootNode->hasAttribute('model:baseClass') ? glz_classNSToClassName($xmlRootNode->getAttribute('model:baseClass')) : null;

        if ($modelType == '2tables') {
            list($tableName, $detailTableName) = explode(',', $tableName);
        }

        $baseclassName = $this->resolveBaseClass($modelType);

        if ($baseClass) {
            $baseclassName['activeRecord'] = $baseClass;
        }

        $fields = $this->compileFields($xmlRootNode);
        $queries = $this->compileQueries($xmlRootNode);
        $script = $this->compileScript($xmlRootNode);


        if ( $baseclassName['type'] == 'activeRecord' ) {
            $compiledOutput    = <<<EOD
class $className extends {$baseclassName['activeRecord']}
{
    function __construct(\$connectionNumber=$dbConnection) {
        parent::__construct(\$connectionNumber);
        $originalClassName
        \$this->setTableName('$tableName',
                $usePrefix ? org_glizy_dataAccessDoctrine_DataAccess::getTablePrefix(\$connectionNumber) : '' );

        static \$fields;
        if (!\$fields) {
            \$sm = new org_glizy_dataAccessDoctrine_SchemaManager(\$this->connection);
            \$fields = \$sm->getFields(\$this->getTableName());
        }

        foreach (\$fields as \$field) {
            \$this->addField(\$field);
        }

        {$fields}

        if (__Config::get('MULTISITE_ENABLED')) {
            {$siteField}
        }
    }

    public function createRecordIterator() {
        return new {$className}_iterator(\$this);
    }

    {$script['model']}
    $queries
}
class {$className}_iterator extends {$baseclassName['recordIterator']}
{
    {$script['iterator']}
}
EOD;
        } elseif ( $baseclassName['type'] == '2tables' ) {
            $compiledOutput    = <<<EOD
class $className extends {$baseclassName['activeRecord']}
{
    function __construct(\$connectionNumber=$dbConnection) {
        parent::__construct(\$connectionNumber);
        \$this->setTableName('$tableName',
                $usePrefix ? org_glizy_dataAccessDoctrine_DataAccess::getTablePrefix(\$connectionNumber) : '' );

        \$this->setDetailTableName('$detailTableName',
                $usePrefix ? org_glizy_dataAccessDoctrine_DataAccess::getTablePrefix(\$connectionNumber) : '' );

        \$this->setJoinFields('$joinFields[0]', '$joinFields[1]');

        static \$fields;
        static \$fieldsDetail;
        if (!\$fields) {
            \$sm = new org_glizy_dataAccessDoctrine_SchemaManager(\$this->connection);
            \$fields = \$sm->getFields(\$this->getTableName());
            \$fieldsDetail = \$sm->getFields(\$this->getDetailTableName());
        }

        foreach (\$fields as \$field) {
            \$this->addField(\$field);
        }

        foreach (\$fieldsDetail as \$field) {
            \$this->addField(\$field, true);
        }

        {$fields}

        {$languageField}

        if (__Config::get('MULTISITE_ENABLED')) {
            {$siteField}
        }
    }

    public function createRecordIterator() {
        return new {$className}_iterator(\$this);
    }

    {$script['model']}
    $queries
}
class {$className}_iterator extends {$baseclassName['recordIterator']}
{
    {$script['iterator']}
}
EOD;
        } else {
        $compiledOutput    = <<<EOD
class $className extends {$baseclassName['activeRecord']}
{
    function __construct(\$connectionNumber=$dbConnection) {
        parent::__construct(\$connectionNumber);
        $originalClassName
        \$this->setTableName('$tableName',
                $usePrefix ? org_glizy_dataAccessDoctrine_DataAccess::getTablePrefix(\$connectionNumber) : '' );
        \$this->setType('$tableName');

        $fields
    }

    public function createRecordIterator() {
        return new {$className}_iterator(\$this);
    }

    {$script['model']}
    $queries
}
class {$className}_iterator extends {$baseclassName['recordIterator']}
{
    {$script['iterator']}
}
EOD;
        }

        $this->output .= $compiledOutput;
        return $this->save();
    }

    private function compileQueries($xmlRootNode)
    {
        $output = '';
        $queriesNodes = $xmlRootNode->getElementsByTagNameNS('http://www.glizy.org/dtd/1.0/model/', 'Query');
        foreach ($queriesNodes as $node) {
            $phpType = $node->hasAttribute('type') && $node->getAttribute('type') == 'function' ? true : false;
            $name = $node->getAttribute('name');
            if (empty($name)) {
                throw org_glizy_compilers_ModelException::queryWithoutName($this->_fileName);
            }
            if ( $phpType) {
                $funcArguments = array('$iterator');
                $arguments = explode(',', (String)$node->getAttribute('arguments'));
                foreach($arguments as $v) {
                    if ($v) $funcArguments[] = '$'.$v;
                }
                $tempOutput = 'public function query_'.$name.'('.implode($funcArguments, ',').') {'.GLZ_COMPILER_NEWLINE2;
                $tempOutput .= $node->textContent.GLZ_COMPILER_NEWLINE2;
            } else {
                $tempOutput = 'public function querysql_'.$name.'() {'.GLZ_COMPILER_NEWLINE2;
                $tempOutput .= 'return '.$this->replacePlaceholderInSql($node->textContent).GLZ_COMPILER_NEWLINE;
            }

            $tempOutput .= '}'.GLZ_COMPILER_NEWLINE2;
            $output .= $tempOutput;
        }

        return $output;
    }


    private function compileScript($xmlRootNode) {
        $output = array('model' => '', 'iterator' => '');
        $queriesNodes = $xmlRootNode->getElementsByTagNameNS('http://www.glizy.org/dtd/1.0/model/', 'Script');
        foreach ($queriesNodes as $node) {
            $parent = $node->getAttribute('parent');
            if ($parent != 'model' && $parent != 'iterator') {
                throw org_glizy_compilers_ModelException::scriptParentError($this->_fileName);
            }
            $output[$parent] .= $node->textContent;
        }

        return $output;
    }

    private function compileFields($xmlRootNode)
    {
        $output = '';
        $compositeValidators = '';
        $defineNodes = $xmlRootNode->getElementsByTagNameNS('http://www.glizy.org/dtd/1.0/model/', 'Define');
        foreach ($defineNodes as $dn ) {
            $fieldNodes = $dn->getElementsByTagNameNS('http://www.glizy.org/dtd/1.0/model/', 'Field');
            foreach ($fieldNodes as $node) {
                // TODO: controllare se ci sono i valori
                $name = $node->hasAttribute('name') ? $node->getAttribute('name') : '';
                $type = $node->hasAttribute('type') ? $node->getAttribute('type') : '';
                $key = $node->hasAttribute('key') && $node->getAttribute('key') == 'true' ? 'true' : 'false';
                $validator = $node->hasAttribute('validator') ? $node->getAttribute('validator') : 'null';
                $value = $node->hasAttribute('defaultValue') ? $node->getAttribute('defaultValue') : '';
                $readFormat = $node->hasAttribute('readFormat') && $node->getAttribute('readFormat') == 'false' ? 'false' : 'true';
                $virtual = $node->hasAttribute('virtual') && $node->getAttribute('virtual') == 'true' ? 'true' : 'false';
                $description = $node->hasAttribute('description') ? $node->getAttribute('description') : '';
                $index = $node->hasAttribute('index') ? $node->getAttribute('index') : 0;
                $onlyIndex = $node->hasAttribute('onlyIndex') ? $node->getAttribute('onlyIndex') : 0;
                $option = $node->hasAttribute('option') && $node->getAttribute('option') ? $node->getAttribute('option') : null;

                // TODO impostare la lunghezza del campo in base al tipo
                $type = $this->resolveFieldType($type);
                $validator = $this->resolveValidator($validator, $compositeValidators);
                $index = $this->resolveIndex($index);
                $onlyIndex = $this->resolveOnlyIndex($onlyIndex);

                if ($onlyIndex) {
                    $index .= ' | '.$onlyIndex;
                }

                $output .= <<<EOD
\$this->addField(new org_glizy_dataAccessDoctrine_DbField(
                '$name',
                $type,
                255,
                $key,
                $validator,
                '$value',
                $readFormat,
                $virtual,
                '$description',
                $index,
                '$option'
                ));

EOD;
            }
        }
        return $compositeValidators.GLZ_COMPILER_NEWLINE2.$output.GLZ_COMPILER_NEWLINE2;
    }


    private function replacePlaceholderInSql($sql)
    {
        $sql = str_replace(array("\n", "\r", "'"), array(" ", " ", "\'"), $sql);
        $sql = str_replace('##TABLE_NAME##', "'.\$this->getTableName().'", $sql);
        $sql = str_replace('##TABLE_PREFIX##',"'.\$this->tablePrefix.'", $sql);
        $sql = str_replace('##SELECT_ALL##', "'.\$this->query_all().'", $sql);
        $sql = str_replace('##SITE_ID##',"'.__Config::get('glizy.multisite.id').'", $sql);
        $sql = str_replace('##USER_ID##', "'.org_glizy_ObjectValues::get('org.glizy', 'user')->id.'", $sql);
        $sql = str_replace('##USER_GROUP_ID##', "'.org_glizy_ObjectValues::get('org.glizy', 'user')->group.'", $sql);
        $sql = str_replace('##LANGUAGE_ID##', "'.org_glizy_ObjectValues::get('org.glizy', 'languageId').'", $sql);
        $sql = str_replace('##EDITING_LANGUAGE_ID##', "'.org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId').'", $sql);

        $params = '';
        if (strstr($sql, "??") != FALSE ) {
            preg_match_all( "/\?\?([^\?]*)\?\?/U", $sql, $resmatch );
            foreach( $resmatch[1] as $varname)
            {
                $sql = str_replace('??'.$varname.'??',  ':'.$varname, $sql);
                $params .= '\':'.$varname.'\' => __Request::get(\''.$varname.'\'),';
            }
        }
        if (preg_match("/\{config\:.*\}/i", $sql)) {
            $varname = preg_replace("/(.*)\{config\:(.*)\}(.*)?/i", "$2", $sql);
            $sql = preg_replace("/(.*)\{config\:(.*)\}(.*)?/i", "$1 ".__Config::get($varname)." $3", $sql);
        }
        return 'array(\'sql\' =>\''.$sql.'\', \'params\' => array('.$params.'), \'filters\' => array())';
    }


    private function resolveBaseClass($type)
    {
        $type = strtolower($type);

        switch ($type) {
            case 'document':
                return array('activeRecord' => 'org_glizy_dataAccessDoctrine_ActiveRecordDocument',
                            'recordIterator' => 'org_glizy_dataAccessDoctrine_RecordIteratorDocument',
                            'type' => 'document' );

            case 'simpledocument':
                return array('activeRecord' => 'org_glizy_dataAccessDoctrine_ActiveRecordSimpleDocument',
                            'recordIterator' => 'org_glizy_dataAccessDoctrine_RecordIteratorSimpleDocument',
                            'type' => 'document' );

            case 'simpledocumentindexed':
                return array('activeRecord' => 'org_glizycms_models_ActiveRecordSimpleDocumentArraysIndexed',
                            'recordIterator' => 'org_glizy_dataAccessDoctrine_RecordIteratorSimpleDocument',
                            'type' => 'document' );

            case '2tables':
                return array('activeRecord' => 'org_glizy_dataAccessDoctrine_ActiveRecord2tables',
                            'recordIterator' => 'org_glizy_dataAccessDoctrine_RecordIterator2tables',
                            'type' => '2tables' );

            default:
                return array('activeRecord' => 'org_glizy_dataAccessDoctrine_ActiveRecord',
                            'recordIterator' => 'org_glizy_dataAccessDoctrine_RecordIterator',
                            'type' => 'activeRecord' );
                break;
        }
    }


    private function resolveFieldType($type)
    {
        $type = strtolower($type);

        switch ($type) {
            case 'array':
                return '\Doctrine\DBAL\Types\Type::TARRAY';
                break;

            case 'array_id':
                return 'org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID';
                break;

            case 'bigint':
                return '\Doctrine\DBAL\Types\Type::BIGINT';
                break;

            case 'boolean':
                return '\Doctrine\DBAL\Types\Type::BOOLEAN';
                break;

            case 'date':
                return '\Doctrine\DBAL\Types\Type::DATE';
                break;

            case 'datetime':
                return '\Doctrine\DBAL\Types\Type::DATETIME';
                break;

            case 'datetimez':
                return '\Doctrine\DBAL\Types\Type::DATETIMETZ';
                break;

            case 'time':
                return '\Doctrine\DBAL\Types\Type::TIME';
                break;

            case 'decimal':
                return '\Doctrine\DBAL\Types\Type::DECIMAL';
                break;

            case 'int':
            case 'integer':
                return '\Doctrine\DBAL\Types\Type::INTEGER';
                break;

            case 'object':
                return '\Doctrine\DBAL\Types\Type::OBJECT';
                break;

            case 'smallint':
                return '\Doctrine\DBAL\Types\Type::SMALLINT';
                break;

            case 'blob':
                return '\Doctrine\DBAL\Types\Type::BLOB';
                break;

            case 'text':
                return '\Doctrine\DBAL\Types\Type::TEXT';
                break;

            case 'float':
                return '\Doctrine\DBAL\Types\Type::FLOAT';
                break;

            case 'guid':
                return '\Doctrine\DBAL\Types\Type::GUID';
                break;

            case 'string':
            default:
                return 'Doctrine\DBAL\Types\Type::STRING';
                break;
        }
    }

    private function resolveValidator($validator, &$compositeValidators)
    {
        $validator = explode(',', strtolower($validator));
        $validatorClass = array();
        foreach ($validator as $v) {
            switch ($v) {
                case 'notnull':
                    $validatorClass[] = 'new org_glizy_validators_NotNull()';
                    break;

                case 'date':
                    $validatorClass[] = 'new org_glizy_validators_Date()';
                    break;

                case 'datetime':
                    $validatorClass[] = 'new org_glizy_validators_DateTime()';
                    break;

                case 'numeric':
                    $validatorClass[] = 'new org_glizy_validators_Numeric()';
                    break;

                case 'text':
                    $validatorClass[] = 'new org_glizy_validators_Text()';
                    break;

                case 'notempty':
                    $validatorClass[] = 'new org_glizy_validators_NotEmpty()';
                    break;
            }
        }

        if (!count($validatorClass)) {
            return 'null';
        } else if (count($validatorClass)==1) {
            return $validatorClass[0];
        } else {
            $fuid = uniqid();
            $compositeValidators .= '$fn_'.$fuid.' = function(){'.GLZ_COMPILER_NEWLINE2;
            $compositeValidators .= '$v = new org_glizy_validators_CompositeValidator()'.GLZ_COMPILER_NEWLINE;
            foreach($validatorClass as $v) {
                $compositeValidators .= '$v->add('.$v.')'.GLZ_COMPILER_NEWLINE;
            }
            $compositeValidators .= 'return $v'.GLZ_COMPILER_NEWLINE;
            $compositeValidators .= '}'.GLZ_COMPILER_NEWLINE;
            return '$fn_'.$fuid.'()';
        }

    }

    private function resolveIndex($index)
    {
        $index = strtolower($index);
        switch ($index) {
            case 'fulltext':
                return 'org_glizy_dataAccessDoctrine_DbField::FULLTEXT';

            case 'true':
                return 'org_glizy_dataAccessDoctrine_DbField::INDEXED';

            default:
                return 'org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED';
        }
    }

    private function resolveOnlyIndex($onlyIndex)
    {
        if (strtolower($onlyIndex) == 'true') {
            return 'org_glizy_dataAccessDoctrine_DbField::ONLY_INDEX';
        }
        else {
            return null;
        }
    }

}