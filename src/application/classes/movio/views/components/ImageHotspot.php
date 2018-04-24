<?php
class movio_views_components_ImageHotspot extends org_glizy_components_Component
{
    public function process()
    {
        $this->_content = @json_decode($this->_parent->loadContent($this->getId()));
    }



    public function render($outputMode = NULL, $skipChilds = false)
    {
        if (is_object($this->_content) && $this->_content->image && $this->_content->hotspots) {
            $media = org_glizycms_mediaArchive_MediaManager::getMediaById($this->_content->image);
            $speakingUrlManager = $this->_application->retrieveProxy('org.glizycms.speakingUrl.Manager');
            if (is_object($media)) {
                $id = $this->getId();
                $imageInfo = $media->getImageInfo();
                $width = $imageInfo['width'];
                $height = $imageInfo['height'];

                $attributes             = array();
                $attributes['src']      = $media->getFileName(true);
                $attributes['width']    = $imageInfo['width'];
                $attributes['height']   = $imageInfo['height'];
                $attributes['alt']    = $media->title;
                $attributes['title']    = $media->title;
                $image = '<img '.$this->_renderAttributes($attributes).' />';

                $hotspots = '';
                foreach($this->_content->hotspots as $h) {
                    $attributes             = array();
                    $attributes['id']       = $id.'-'.$h->id;
                    $attributes['class']    = 'movio-hotspot'.($h->form=='circle' ? '-circle' : '');
                    $attributes['style']    = 'display: block; top: '.$h->top.'px; left: '.$h->left.'px; height: '.$h->height.'px; width: '.$h->width.'px;';
                    if ($h->description) $attributes['data-tooltip'] = glz_encodeOutput($h->description);
                    $link = '';
                    if ($h->type='linkEx' && $h->src) {
                        $link = __Link::formatLink($h->src);
                    } else if ($h->type='link' && $h->srcInt) {
                        $link = __Link::formatInternalLink($speakingUrlManager->makeUrl($h->srcInt));
                    }

                    if ($link) {
                        $link = str_replace('<a ', '<a style="text-indent: -9999px; height: '.$h->height.'px; width: '.$h->width.'px; display: block;"', $link);
                    }
                    // <a target="_blank" style="height: 148px; width: 186px; display: block;" href="www.google.com" class="hotspot-circle"></a>
                    $hotspots .= '<div '.$this->_renderAttributes($attributes).'>'.
                                $link.
                              '</div>';

                }

                $css = trim('movio-hotspotContainer '.$this->getAttribute('cssClass'));

                $output = <<<EOD
<div id="$id" class="$css">
    $image
    <div class="movio-imageHotspot-scale">
    $hotspots
    </div>
</div>
<script src="static/jquery/jquery-transform/jquery.transform2d.js"></script>
<script>
jQuery( function(){
    $('div.movio-hotspotContainer').find('div[data-tooltip!=""]').qtip({
        content: {
            attr: 'data-tooltip'
        },
        position: {
                    my: 'bottom left',
                    at: 'bottom left',
                    target: 'mouse'
                },
        style: {
            classes: 'qtip-bootstrap'
        }
    });
    var img = $('div.movio-hotspotContainer img').first();
    var scale = img.width() / parseInt(img.attr('width'));
    $('.movio-imageHotspot-scale').css('transform', 'scale('+scale+','+scale+')');
});
</script>
EOD;
                $this->addOutputCode($output);

                $this->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery.qtip/jquery.qtip.min.js' ), 'head');
                $this->addOutputCode( org_glizy_helpers_CSS::linkStaticCSSfile( 'jquery/jquery.qtip/jquery.qtip.min.css' ), 'head');
            }
        }

    }

    public static function translateForMode_edit($node) {
        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['data'] = 'type=imageHotspot';

        return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
    }
}
