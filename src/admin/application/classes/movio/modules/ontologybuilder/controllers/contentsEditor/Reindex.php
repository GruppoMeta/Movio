<?php
class movio_modules_ontologybuilder_controllers_contentsEditor_Reindex extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $this->checkPermissionForBackend();
        set_time_limit(0);
        $page = (int)__Request::get('page', 0);
        $pagePart = 100;
        $entityProxy = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityDocument', 'all');
        $it->limit($page, $pagePart);

        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');
        foreach ($it as $ar) {
            $documentId = $ar->document_id;
            $document->emptyRecord();
            $document->load($documentId);
            $rawData = $document->getRawData();
            $data = array_filter((array)$rawData, function($value, $key) {
                return strpos($key, 'document_')===false;
            }, ARRAY_FILTER_USE_BOTH);
            $data['entityTypeId'] = str_replace('entity', '', $rawData->document_type);
            $data['entityId'] = $documentId;
            $data['__isVisible'] = $rawData->document_detail_isVisible;
            $entityProxy->saveContent((object)$data);
        }

        if ($it->count() > $page + $pagePart) {
            sleep(1);
            org_glizy_helpers_Navigation::gotoUrl( __Link::addParams(array('page' => $page + $pagePart)));
            exit;
        }

        $this->changeAction('index');
    }
}