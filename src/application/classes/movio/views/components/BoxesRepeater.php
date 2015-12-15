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
}
