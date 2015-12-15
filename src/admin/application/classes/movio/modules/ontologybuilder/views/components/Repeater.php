<?php
class movio_modules_ontologybuilder_views_components_Repeater extends org_glizy_components_ComponentContainer
{
    private $childContentsLoaded = false;

    function process()
    {
        $this->_content = $this->_parent->loadContent($this->getId(), true);

        if (is_array($this->_content)) {
            for ($i = 0; $i < count($this->_content['content']); $i++) {
                if ($this->_content['content'][$i]['entityTypeId']==0) {
                    unset($this->_content['content'][$i]);
                }
            }
            if (empty($this->_content['content'])) {
                $this->setAttribute('visible', 'false');
            }
        }

        if (is_object($this->_content)) {
            $child = $this->childComponents[0];
            $childId = $child->getId();
            $this->_content = array('content' => property_exists($this->_content, $childId) ? $this->_content->$childId : array());
            $this->_content['id'] = $this->getId();
        }
    }

    function getContent()
    {
        if (!$this->childContentsLoaded) {
            $child = $this->childComponents[0];
            foreach ($this->_content['content'] as $i => $value) {
                $child->setId($i);
                $child->process();
                $this->_content['content'][$i] = $child->getContent();
            }
            $this->childContentsLoaded = true;
        }

        $this->_content['id'] = $this->getId();
        return $this->_content;
    }

    function loadContent($id)
    {
        return $this->_content['content'][$id];
    }
}