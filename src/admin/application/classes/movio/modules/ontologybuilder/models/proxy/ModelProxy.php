<?php
class movio_modules_ontologybuilder_models_proxy_ModelProxy extends GlizyObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams = null)
    {
        $model = org_glizy_Modules::getModule($proxyParams->moduleId);

        $it = org_glizy_ObjectFactory::createModelIterator($model->classPath.'.models.Model')
            ->where('title', '%'.$term.'%', 'ILIKE')
            ->orderBy('title');
        
        $result = array();

        foreach ($it as $ar) {
            $result[] = array(
                'id' => $ar->getId(),
                'text' => $ar->title,
            );
        }
        
        return $result;
    }
}