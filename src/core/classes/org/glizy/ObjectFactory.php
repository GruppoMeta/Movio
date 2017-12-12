<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class  org_glizy_ObjectFactory */
class org_glizy_ObjectFactory
{
    /**
     * @return object
     * @throws Exception
     */
	static function createObject()
    {
        $args = func_get_args();

        // Retrieve class from object name string
        $classPath = array_shift($args);
        if (substr($classPath, -1, 1) == '*') {
           throw new \Exception(sprintf('%s: can\'t create class with *', __METHOD__, $classPath));
        }

        $classPath = org_glizy_ObjectFactory::resolveClass($classPath);
        $className = str_replace('.', '_', $classPath);

        if (!$className) {
            // for compatibility
            // NOTE: in the next version replace with Exception
            return null;
        } else if (!class_exists($className)) {
            throw org_glizy_exceptions_GlobalException::classNotExists($className);
        }

        if (empty($args)) {
            return new $className();
        }

        $reflectionClass = new \ReflectionClass($className);

        if (null === $reflectionClass->getConstructor()) {
            throw new \Exception(sprintf('%s: class %s does not have constructor', __METHOD__, $className));
        }

        // Can be removed if we are sure constructor parameters are not passed by reference
        $rewriteArgs = array();
        $reflectionMethod = new ReflectionMethod($className,  '__construct');
        $construcParameters = $reflectionMethod->getParameters();
        $numArgs = count($args);
        foreach ($construcParameters as $key => $param) {
            if ($param->isDefaultValueAvailable()) {
                $rewriteArgs[$key] = $param->getDefaultValue();
            }
            if ($key < $numArgs) {
                if ($param->isPassedByReference()) {
                    $rewriteArgs[$key] = &$args[$key];
        } else {
                    $rewriteArgs[$key] = $args[$key];
                }
            }
        }
        return $reflectionClass->newInstanceArgs($rewriteArgs);
    }


    /**
     * @param $className
     * @param $application
     * @param $parent
     * @param $tagName
     * @param $id
     * @param string $originalId
     * @param bool $skipImport
     * @param string $mode
     * @return mixed
     */
    static function &createComponent($className, &$application, &$parent, $tagName, $id, $originalId='', $skipImport=false, $mode='')
    {
        $className = org_glizy_ObjectFactory::resolveClass($className);
        $componentClassName = str_replace('.', '_', $className);

        if (!class_exists($componentClassName))
        {
            // controlla se il file className.xml esiste sia nelle classi dell'applicazione
            // si in quelle di sistema
            // se esiste:
            // deve compilarlo e caricarlo
            // se non esiste
            // deve dare un messaggio di errore

            // TODO
            // in questo modo non carica eventuali classi dal core
            //
            // TODO
            // deve essere prevista anche la compilazione dei models se sono in PHP e non in XML
            //
            $fileName = glz_findClassPath($className);
            $pathInfo = pathinfo($fileName);
            if (empty($pathInfo['basename']))
            {
                trigger_error($className.': component file not found', E_USER_ERROR);
            }
            if ($pathInfo['extension']=='xml')
            {
                /** @var org_glizy_compilers_Component $compiler */
                $compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Component');
                $compiledFileName = $compiler->verify($fileName, array('originalClassName' => $className, 'mode' => $mode));
                require_once($compiledFileName);
                $componentClassName = glz_basename($compiledFileName);
            }
            else
            {
                require_once($fileName);
            }

            $newObj = new $componentClassName($application, $parent, $tagName, $id, $originalId, $skipImport);
            return $newObj;
        }

        $newObj =  new $componentClassName($application, $parent, $tagName, $id, $originalId, $skipImport);
        return $newObj;
    }

    /**
     * @param         $classPath
     * @param integer $connectionNumber
     *
     * @return org_glizy_dataAccess_ActiveRecord
     * @throws org_glizy_compilers_CompilerException
     */
    static function &createModel($classPath, $connectionNumber=null)
    {
        $classInfo = org_glizy_ObjectFactory::resolveClassNew($classPath);
        if (isset($classInfo['path'])) {
            $compiler             = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Model');
            $compiledFileName     = $compiler->verify($classInfo['path'], array('originalClassName' => $classInfo['originalClassName']));
            require_once($compiledFileName);
            $className = glz_basename($compiledFileName);
            $newObj = $connectionNumber ? new $className($connectionNumber) : new $className();

            $classMap = &org_glizy_ObjectValues::get('org.glizy.ObjectFactory', 'ClassMap', array());
            $classMap[$classPath] = $className;
        } else if (isset($classInfo['class'])) {
            $newObj = $connectionNumber ? new $classInfo['class']($connectionNumber) : new $classInfo['class']();
        } else {
            throw org_glizy_compilers_CompilerException::fileNotFound($classPath);
        }
        return $newObj;
    }

    /**
     * @param string $classPath
     * @param string $queryName
     * @param array $options
     *
     * @return org_glizy_dataAccessDoctrine_RecordIterator
     * @throws org_glizy_compilers_CompilerException
     */
    static function &createModelIterator($classPath, $queryName=null, $options=array())
    {
        /** @var org_glizy_dataAccessDoctrine_ActiveRecord $ar */
        $ar = org_glizy_objectFactory::createModel($classPath);
        if ($ar instanceof Iterator) {
            $it = $ar;
        } else {
            $it = $ar->createRecordIterator();
        }

        if ($queryName) {
            $it->load($queryName, isset($options['params']) ? $options['params'] : null);

            if (isset($options['filters'])) {
                $it->setFilters($options['filters']);
            }

            if (isset($options['order'])) {
                $it->setOrderBy($options['order']);
            }

            if (isset($options['limit'])) {
                $it->limit($options['limit']);
            }
        }

        return $it;
    }

    /**
     * @param org_glizy_application_Application $application
     * @param string $pageType
     * @param string $path
     * @param array $options
     *
     * @return mixed
     */
    static function &createPage(&$application, $pageType, $path=NULL, $options=NULL)
    {
        $pageType = org_glizy_ObjectFactory::resolvePageType($pageType);
        $options['pageType'] = $pageType.'.xml';
        $options['path'] = is_null($path) ? org_glizy_Paths::getRealPath('APPLICATION_PAGE_TYPE') : $path;
        $fileName = $options['path'].$options['pageType'];

        if (isset($options['pathTemplate']) && isset($options['mode'])) {
            $verifyFileName = $options[ 'pathTemplate' ].'/pageTypes/'.$options[ 'pageType' ];
            if (file_exists($verifyFileName)) {
                $options['verifyFileName'] = $verifyFileName;
            }
        }

        if ( !file_exists( $fileName ) ) {
            $fileName = glz_findClassPath( $pageType, true, true);
            if ( !$fileName ) {
                throw new Exception( 'PageType not found '.$pageType );
            }
        }

        $compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.PageType');
        $compiledFileName = $compiler->verify($fileName, $options);

        // TODO verificare se la pagina è stata compilata
        require_once($compiledFileName);

        $idPrefix = isset($options['idPrefix']) ? $options['idPrefix'] : '';
        $className = glz_basename($compiledFileName);
        $newObj = new $className($application, isset($options['skipImport']) ? $options['skipImport'] : false, $idPrefix);
        return $newObj;
    }

    /**
     * @param org_glizy_components_Component $component
     * @param org_glizy_application_Application $application
     * @param string $pageType
     * @param string $path
     * @param array $options
     * @param string $remapId
     * @param bool $atTop
     */
    function attachPageToComponent($component, $application, $pageType, $path, $options, $remapId, $atTop=true)
    {
        $originalRootComponent = $application->getRootComponent();
        $originalChildren = $component->childComponents;
        $component->childComponents = array();
        org_glizy_ObjectFactory::createPage($application, $pageType, $path, $options);
        $rootComponent = $application->getRootComponent();
        $rootComponent->init();

        for($i=0; $i<count($rootComponent->childComponents); $i++)
        {
            $rootComponent->childComponents[$i]->remapAttributes($remapId);
        }

        $rootComponent->execDoLater();
        $application->_rootComponent = &$originalRootComponent;

        for($i=0; $i<count($rootComponent->childComponents); $i++)
        {
            $component->addChild($rootComponent->childComponents[$i]);
            $rootComponent->childComponents[$i]->_parent = &$component;
        }

        $component->childComponents = $atTop ? array_merge($component->childComponents, $originalChildren) :
                                               array_merge($originalChildren, $component->childComponents);
    }

    /**
     * @param string $orig
     * @param string $dest
     */
    static function remapClass($orig='', $dest='')
    {
        $classMap = &org_glizy_ObjectValues::get('org.glizy.ObjectFactory', 'ClassMap', array());
        $classMap[$orig] = $dest;
    }

    static function resetRemapClass()
    {
        org_glizy_ObjectValues::set('org.glizy.ObjectFactory', 'ClassMap', null);
    }

    /**
     * @param $classPath
     * @return mixed
     */
    static function resolveClass($classPath)
    {
        $classMap = &org_glizy_ObjectValues::get('org.glizy.ObjectFactory', 'ClassMap', array());
        return isset($classMap[$classPath]) ? $classMap[$classPath] : $classPath;
    }

    /**
     * @param string $classPath
     *
     * @return array
     */
    static function resolveClassNew($classPath)
    {
        $classMap = &org_glizy_ObjectValues::get('org.glizy.ObjectFactory', 'ClassMap', array());
        $newClassPath = isset($classMap[$classPath]) ? $classMap[$classPath] : $classPath;
        $className = str_replace('.', '_', $newClassPath);
        if (!class_exists($className)) {
            return array('originalClassName' => $classPath, 'path' => glz_findClassPath($newClassPath));
        } else {
            return array('originalClassName' => $classPath, 'class' => $className);
        }
    }

    /**
     * @param string $orig
     * @param string $dest
     */
    function remapPageType($orig='', $dest='')
    {
        $orig = preg_replace('/\.xml$/i', '', $orig);
        $dest = preg_replace('/\.xml$/i', '', $dest);
        $pageTypeMap = &org_glizy_ObjectValues::get('org.glizy.ObjectFactory', 'PageTypeMap', array());
        $pageTypeMap[$orig] = $dest;
    }

    /**
     * @param $pageTypePath
     * @return mixed
     */
    function resolvePageType($pageTypePath)
    {
        $pageTypeMap = &org_glizy_ObjectValues::get('org.glizy.ObjectFactory', 'PageTypeMap', array());
        return isset($pageTypeMap[$pageTypePath]) ? $pageTypeMap[$pageTypePath] : $pageTypePath;
    }

    /**
     * @param $cachedFile
     * @param $fileName
     */
    function requireComponent( $cachedFile, $fileName )
    {
        if ( !file_exists( $cachedFile ) )
        {
            /** @var org_glizy_compilers_Component $compiler */
            $compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Component');
            $cachedFile = $compiler->verify($fileName);
        }
        require_once( $cachedFile );
    }
}

// shortcut version
/**
 * Class __ObjectFactory
 */
class __ObjectFactory extends org_glizy_ObjectFactory
{
}
