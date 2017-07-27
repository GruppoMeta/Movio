<?php
class org_glizycms_contents_models_proxy_ModuleContentProxy extends GlizyObject
{
    // restituisce true se è valido
    // altrimenti un array con gli errori di validazione
    public function validate($data, $model)
    {
        $document = org_glizy_objectFactory::createModel($model);

        try {
            $document->validate($data);
        } catch (org_glizy_validators_ValidationException $e) {
            return $e->getErrors();
        }

        return true;
    }

    public function loadContent($recordId, $model, $status='PUBLISHED')
    {
        $document = org_glizy_objectFactory::createModel($model);

        if ($recordId) {
            if (!$document->load($recordId, $status)) {
                $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
                //dd($recordId, $status, $languageProxy->getDefaultLanguageId());
                $document->load($recordId, $status, $languageProxy->getDefaultLanguageId());
            }
        }
        $values = (array)$document->getValuesForced();

        if (__Config::get('ACL_MODULES')) {
            // caricamento permessi editing e visualizzazione record
            $ar = org_glizy_objectFactory::createModel('org.glizycms.contents.models.DocumentACL');
            $ar->load($recordId);

            $values['__aclEdit'] = $this->getPermissionName($ar->__aclEdit);
            $values['__aclView'] = $this->getPermissionName($ar->__aclView);
        }

        return $values;
    }

    private function getPermissionName($permissions)
    {
		$names = array();
		$permissions = explode(',', $permissions);
		$ar = org_glizy_ObjectFactory::createModel('org.glizycms.roleManager.models.Role');
		foreach ($permissions as $v) {
			if ($ar->load($v)) {
				$names[] = array (
                    'id' => $ar->role_id,
                    'text' => $ar->role_name
                );
			}
		}

		return $names;
	}

    public function saveContent($data, $publish=true, $draft=false, $saveCurrentPublished=false)
    {
        $recordId = $data->__id;
        $model = $data->__model;

        $document = org_glizy_objectFactory::createModel($model);
        $result = $document->load($recordId, 'LAST_MODIFIED');

        if (!$result) {
            $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $defaultLanguageId = $languageProxy->getDefaultLanguageId();
            $document->load($recordId, 'LAST_MODIFIED', $defaultLanguageId);
            $document->setDetailFromLanguageId($languageProxy->getLanguageId());
        }

        if (property_exists($data, 'title')) {
            $document->title = $data->title;
        }

        if (property_exists($data, 'url')) {
            $document->url = $data->url;
        }

        $document->fulltext = org_glizycms_core_helpers_Fulltext::make($data, $document, true);

        if (property_exists($data, 'document_detail_isVisible')) {
            $document->setVisible($data->document_detail_isVisible);
        }

        try {
            if ($saveCurrentPublished) {
                $id = $document->saveCurrentPublished();
            } else if ($publish && !$draft) {
                $id = $document->publish();
            } else if ($publish && $draft) {
                $id = $document->saveHistory();
            } else if (!$publish && !$draft) {
                $id = $document->save(null, false, 'PUBLISHED');
            } else if (!$publish && $draft) {
                $id = $document->save(null, false, 'DRAFT');
            }

            if (__Config::get('ACL_MODULES')) {
                // gestione acl record
                $ar = org_glizy_objectFactory::createModel('org.glizycms.contents.models.DocumentACL');
                $ar->load($id);
                $ar->__aclEdit = $data->__aclEdit;
                $ar->__aclView = $data->__aclView;
                $ar->save();
            }
        }
        catch (org_glizy_validators_ValidationException $e) {
            return $e->getErrors();
        }

        return array('__id' => $id, 'document' => $document);
    }

    public function delete($recordId, $model='')
    {
        if (__Config::get('ACL_MODULES')) {
            // cancella i permessi di editing e visualizzazione record
            // TODO gestire le relazioni in ActiveRecordDocument cosicché questo codice venga automaticamente gestito
            // dal delete sul document
            $ar = org_glizy_objectFactory::createModel('org.glizycms.contents.models.DocumentACL');
            $ar->load($recordId);
            $ar->__aclEdit = array();
            $ar->__aclView = array();
            $ar->save();
        }

        // cancella il document;
        $document = org_glizy_objectFactory::createModel(!$model ? 'org.glizycms.core.models.Content' : $model);
        $document->delete($recordId);
    }

    public function toggleVisibility($recordId, $model='')
    {
        $document = org_glizy_objectFactory::createModel(!$model ? 'org.glizycms.core.models.Content' : $model);
        $document->load($recordId);
        $document->setVisible($document->isVisible() ? 0 : 1);
        $document->saveCurrentPublished();
    }
}