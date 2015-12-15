<?php
class movio_views_components_VideoCmp extends org_glizy_components_Groupbox
{
    function getContent() {
        $content = parent::getContent();
        $newContent = array();
        $newContent['textAlternative'] = $content[$this->getId().'-textAlternative'];
        $newContent['video'] = $content[$this->getId().'-video'];
        $newContent['width'] = $content[$this->getId().'-width'];
        $newContent['height'] = $content[$this->getId().'-height'];
        //$newContent['autostart'] = $content[$this->getId().'-autostart']['value'] == '1';

        return $newContent;
    }

    function render($mode) {
        if (!$this->_application->isAdmin()) {
            $this->setAttribute('skin', 'Video.html');
        }
        parent::render($mode);
    }
}
