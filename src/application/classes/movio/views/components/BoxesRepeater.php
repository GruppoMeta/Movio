<?php
class movio_views_components_BoxesRepeater extends org_glizy_components_Repeater
{
    function getContent()
    {
        $content = parent::getContent();
        $tempContent = array();
        $tempContentContainer = array();
        $span = 0;
        $i = 0;
        $numItem = count($content);
        foreach($content as $item) {
            $i++;
            if ($span+$item->width > 4) {
                $tempContentContainer[] = $tempContent;
                $tempContent = array();
            }
            $item->style = $item->height ? 'height:'.$item->height.'px;' : '';
            $tempContent[] = $item;
            $span += $item->width;
            $item->width *= 3;

            if ($span >= 4 || $i == $numItem) {
                $tempContentContainer[] = $tempContent;
                $span = 0;
                $tempContent = array();
            }
        }

        return $tempContentContainer;
    }

    function loadContent($id, $bindTo='')
    {
        if ($id=='boxes-boxes-image') {
            switch ($this->_content->width[$this->contentCount]) {
                case '1':
                    $width = __Config::get('movio.home.width-size1');
                    break;
                case '3':
                    $width = __Config::get('movio.home.width-size3');
                    break;
                case '4':
                    $width = __Config::get('movio.home.width-size4');
                    break;
                default:
                    $width = __Config::get('movio.home.width-size2');
                    break;
            }

            $c = $this->getComponentById('boxes-boxes-image');
            $c->setAttributes(array('width' => $width, 'height' => '*'));
        }
        $id = substr($id, $this->repeaterIdLen + 1);
        return $this->_content->{$id}[$this->contentCount];
    }

}
