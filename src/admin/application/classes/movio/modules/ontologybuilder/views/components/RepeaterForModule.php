<?php
// quickfix 
class movio_modules_ontologybuilder_views_components_RepeaterForModule extends movio_modules_ontologybuilder_views_components_Repeater
{
    function process()
    {
        $this->_content['content'] = $this->_parent->loadContent($this->getId(), true);

        if (is_array($this->_content)) {
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
        
        $this->_content['label'] = $this->getAttribute('label');
    }
}