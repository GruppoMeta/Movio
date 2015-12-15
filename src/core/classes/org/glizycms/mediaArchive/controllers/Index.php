<?php
class org_glizycms_mediaArchive_controllers_Index extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $c = $this->view->getComponentById('dp');

        if (stripos($this->application->getPageId(), 'picker') !== false) {
            // picker
            $sessionEx = org_glizy_ObjectFactory::createObject('org.glizy.SessionEx', $c->getId());
            $mediaType = org_glizy_Request::get('mediaType', '');
            if (empty($mediaType)) {
                $mediaType = $sessionEx->get('mediaType', 'ALL', false, false);
            }

            if (!empty($mediaType) && strtoupper($mediaType)!='ALL') {
                $sessionEx->set('mediaType', $mediaType, GLZ_SESSION_EX_PERSISTENT);
                $c->setAttribute('query', 'all'.ucfirst(strtolower(str_replace(',', '_', $mediaType))));
                // $this->setAttribute('filters', 'media_type IN (\''.str_replace(',', '\',\'',$mediaType).'\')');
            }

            if (stripos($this->application->getPageId(), 'tiny') !== false) {
                $this->setComponentsVisibility('buttonsBar', false);
            }
        } else {
            $query = str_replace('all', '', __Request::get('tabs_state', 'allMedia'));
            // $query = str_replace('mediaarchive_all', '', strtolower($this->application->getPageId()));
            if (empty($query) || $query=='mediaarchive') $query = 'media';
            $query = 'all'.ucfirst($query);
            $c->setAttribute('query', $query);

        }

        // TODO  disabilitare il  pulsane aggiungi in base all'acl
    }
}


