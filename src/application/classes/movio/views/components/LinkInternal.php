<?php
class movio_views_components_LinkInternal extends org_glizy_components_LinkTo
{
    function process()
    {
        $speakingUrlManager = $this->_application->retrieveProxy('org.glizycms.speakingUrl.Manager');

        $id = $this->_parent->loadContent($this->getId());

        $this->_content = $speakingUrlManager->makeLink($id);
    }

    function _render()
    {
        if ( !empty(  $this->_content ) )
        {
            return $this->_content;
        }

        return '';
    }
}
