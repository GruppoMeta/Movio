<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_helpers_Modules extends GlizyObject
{
    public function getFields($pageId, $getRepChild = false)
    {
        $editForm = $this->getEditForm($pageId);
        $fields = $this->getChildFields($editForm, $getRepChild);
        return $fields;
    }

    public function getModelPath($pageId)
    {
        $editForm = $this->getEditForm($pageId);

        for ($i = 0; $i < count($editForm->childComponents); $i++) {
            $c = $editForm->childComponents[$i];
            $id = $c->getAttribute('id');

            if ($id == '__model') {
                return $c->getAttribute('value');
            }
        }

        return null;
    }

    protected function getEditForm($pageId, $formId='editForm', $formAction='edit')
    {
        $oldAction = __Request::get('action');
        __Request::set('action', $formAction);

        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $originalRootComponent = $application->getRootComponent();

        $siteMap = $application->getSiteMap();
        $siteMapNode = $siteMap->getNodeById($pageId);
        $pageType = $siteMapNode->getAttribute('pageType');

        $path = org_glizy_Paths::get('APPLICATION_PAGETYPE');
        $templatePath = org_glizycms_Glizycms::getSiteTemplatePath();
        $options = array(
            'skipImport' => true,
            'pathTemplate' => $templatePath,
            'mode' => 'edit'
        );
        $pageTypeObj = &org_glizy_ObjectFactory::createPage($application, $pageType, $path, $options);
        $rootComponent = $application->getRootComponent();
        $rootComponent->init();
        $application->_rootComponent = &$originalRootComponent;
        __Request::set('action', $oldAction);

        return $rootComponent->getComponentById($formId);
    }

    protected function getChildFields($component, $getRepChild = false)
    {
        $fields = array();
        $childComponents = $component->childComponents;
        for ($i = 0; $i < count($childComponents); $i++) {
            $c = $childComponents[$i];
            $id = $c->getAttribute('id');
            $data = $c->getAttribute('data');

            if (( is_subclass_of($c, 'org_glizy_components_HtmlFormElement') ||
                    ( is_a($c, 'org_glizy_components_Fieldset') && $data)
                    ) && substr($id, 0, 2) != '__' ) {
                $temp = new StdClass;
                $temp->type = $this->getFieldTypeFromComponent($c);
                 if($getRepChild && ($temp->type == org_glizycms_core_models_enum_FieldTypeEnum::REPEATER_IMAGE
                                  || $temp->type == org_glizycms_core_models_enum_FieldTypeEnum::REPEATER_MEDIA
                                  || $temp->type == org_glizycms_core_models_enum_FieldTypeEnum::REPEATER) ){
                    $fields = array_merge($fields, $this->getChildFields($c, $getRepChild));
                } else {
                    $temp->id = $id;
                    $temp->label = $c->getAttribute('label');
                    $fields[$id] = $temp;
                }
            } else if (is_a($c, 'org_glizy_components_Fieldset') && !$data) {
                $fields = array_merge($fields, $this->getChildFields($c, $getRepChild));
            }
        }

        return $fields;
    }

    protected function getFieldTypeFromComponent($component)
    {
        $data = $component->getAttribute('data');

        if (strpos($data, 'type=mediapicker') !== false && strpos($data, 'mediatype=IMAGE') !== false) {
            return org_glizycms_core_models_enum_FieldTypeEnum::IMAGE;
        } else if (strpos($data, 'type=mediapicker') !== false && strpos($data, 'mediatype=IMAGE') === false) {
            return org_glizycms_core_models_enum_FieldTypeEnum::MEDIA;
        } else if (strpos($data, 'type=checkbox') !== false) {
            return org_glizycms_core_models_enum_FieldTypeEnum::CHECKBOX;
        } else if (strpos($data, 'type=url') !== false) {
            return org_glizycms_core_models_enum_FieldTypeEnum::URL;
        } else if (strpos($data, 'type=number') !== false) {
            return org_glizycms_core_models_enum_FieldTypeEnum::NUMBER;
        } else if (strpos($data, 'type=repeat') !== false) {
            if (count($component->childComponents)==1) {
                $temp = $this->getFieldTypeFromComponent($component->childComponents[0]);
                if ($temp==org_glizycms_core_models_enum_FieldTypeEnum::IMAGE) {
                    return org_glizycms_core_models_enum_FieldTypeEnum::REPEATER_IMAGE;
                } else if ($temp==org_glizycms_core_models_enum_FieldTypeEnum::MEDIA) {
                    return org_glizycms_core_models_enum_FieldTypeEnum::REPEATER_MEDIA;
                }
            }
            return org_glizycms_core_models_enum_FieldTypeEnum::REPEATER;
        } else {
            return org_glizycms_core_models_enum_FieldTypeEnum::TEXT;
        }
    }
}