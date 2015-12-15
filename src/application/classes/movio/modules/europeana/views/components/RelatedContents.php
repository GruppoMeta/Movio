<?php

class movio_modules_europeana_views_components_RelatedContents extends org_glizy_components_ComponentContainer
{

    public function process()
    {
        $this->_content = $this->_parent->loadContent($this->getId());
        $query =  (array)json_decode($this->_content);

        $this->_content = array(
                'id' => $this->getId(),
                'label' => $this->getAttribute('label'),
                'ajax' => false,
                'ajaxUrl' => GLZ_HOST.'/'.$this->getAjaxUrl().'show&document_id='.__Request::get('document_id'),

            );

        $this->setAttribute('visible', $query['visible']);
    }


    public function process_ajax()
    {
        $this->_content = $this->_parent->loadContent($this->getId());
        $query =  (array)json_decode($this->_content);

        $this->_content = array(
            'id' => $this->getId(),
            'records' => array(),
            'error' => null,
            'label' => $this->getAttribute('label'),
            'ajax' => true
        );

        if ($query['imgCheckBox'] === "checkBoxEnable" && count($query['savedImage'])) {
            $this->loadSelectedImage($query);
        } else {
            $request = org_glizy_ObjectFactory::createObject('movio.modules.europeana.SendRequest');
            $response = $request->execute($query, null, null);
            if (!$response->error) {
                $this->_content['records'] = $response->records;
            } else {
                $this->_content['error'] = $response->error;
            }
        }

        $template = org_glizy_ObjectFactory::createObject('org.glizy.template.skin.PHPTAL', 'EuropeanaRelatedContents.html');
        $template->set('Component', $this->_content);
        $html = $template->execute();
        return array('status' => true, 'content' => $html);
    }

    private function loadSelectedImage($query)
    {
        foreach ($query['savedImage'] as $item) {
                $imgSrc = $item->edmPreview[0] ? $item->edmPreview[0] : __Config::get('movio.noImage.src');
                $this->_content['records'][] = array(
                    'id' => $item->id,
                    'image' => $item->src,
                    'title' => $item->title,
                    'url' => $item->href
                );
            }
    }

}