<?php
class movio_views_components_PhotogalleryCmp extends org_glizy_components_Groupbox
{
    private $galleryType;

    function process() {
        $this->galleryType = $this->loadContent($this->getId().'-galleryType');
        if (!$this->galleryType) {
            $this->galleryType = 'gallery';
        }
        $queryOr = $this->loadContent($this->getId().'-queryOr') === 1;
        $c = $this->getComponentById($this->getId().'-images');
        if ($c) {
            $c->setAttribute('queryOr', $queryOr);
        }

        parent::process();
    }

    function getContent() {
        $imageCrop = $this->loadContent($this->getId().'-imageCrop');
        $imagePan = $this->loadContent($this->getId().'-imagePan');
        $imageResize = $this->loadContent($this->getId().'-imageResize');
        $imagePosition = $imageCrop == 1 && $imagePan == 1 ? 'top right' : '50%';
        $content = parent::getContent();
        $images = array();

        foreach ($content[$this->getId().'-images'] as $obj) {
            if ($obj->image['mediaId']) {
                $images[] = $obj;
            }
        }

        return array(   'images' => $images,
                        'imageCrop' => $imageCrop == 1 ? 'true' : 'false',
                        'imagePan' => $imagePan == 1 ? 'true' : 'false',
                        'imageResize' => $imageResize == 1 ? 'true' : 'false',
                        'imagePosition' => $imagePosition,
                        'type' => $this->galleryType,
                        'key' => 'gallery_' . $this->getId()
                    );
    }

    function render($outputMode = NULL, $skipChilds = false) {
        if (!$this->_application->isAdmin()) {
            $this->setAttribute('skin', 'Photogallery.html');
        }
        parent::render($outputMode, $skipChilds);
    }
}
