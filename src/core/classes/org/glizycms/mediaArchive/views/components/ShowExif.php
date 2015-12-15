<?php
class org_glizycms_mediaArchive_views_components_ShowExif extends org_glizy_components_Component
{
    protected $imageId;

    function init()
    {
        // define the custom attributes
        $this->defineAttribute('imageId', true, 0, COMPONENT_TYPE_INTEGER);

        // call the superclass for validate the attributes
        parent::init();
    }

    function process()
    {
        parent::process();

        $this->imageId = $this->getAttribute('imageId');
    }

    function eval_rational($e)
    {
        list($n, $d) = explode('/', $e);

        if ($d == 0) {
            return '';
        }

        return $n / $d;
    }

    function render()
    {
        if (!__Config::get('glizycms.mediaArchive.exifEnabled')) {
            return;
        }

        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
        $ar->load($this->imageId);

        if ($ar->media_type == 'IMAGE') {
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.mediaArchive.models.Exif');
            $result = $ar->find(array('exif_FK_media_id' => $this->imageId));

            $values = array(
                __T('Dimension') => array('values' => array($ar->exif_imageWidth, $ar->exif_imageHeight), 'format' => '%dx%d'),
                __T('Resolution') => array('values' => array($ar->exif_resolution), 'format' => '%s dpi'),
                __T('Device manufacturer') => array('values' => array($ar->exif_make)),
                __T('Device model') => array('values' => array($ar->exif_model)),
                __T('Exposure time') => array('values' => array($ar->exif_exposureTime), 'format' => '%s s'),
                __T('Aperture') => array('values' => array($this->eval_rational($ar->exif_fNumber)), 'format' => '%.1f f'),
                __T('Exposure program') => array('values' => array($ar->exif_exposureProgram)),
                __T('ISO') => array('values' => array($ar->exif_ISOSpeedRatings)),
                __T('Original date') => array('values' => array($ar->exif_dateTimeOriginal)),
                __T('Digitized date') => array('values' => array($ar->exif_dateTimeDigitized)),
                __T('GPS coordinates') => array('values' => array($ar->exif_GPSCoords)),
                __T('GPS time') => array('values' => array($ar->exif_GPSTimeStamp))
            );

            $li = $this->formatValues($values);

            if ($result) {
                $output = <<<EOD
    <ul>
        $li
    </ul>
EOD;
            }
            else {
                $output = '<fieldset class="exif">'.__T('No exif data').'</fieldset>';
            }

            $this->addOutputCode($output);
        }
    }

    protected function formatValues($valArray, $tag = 'li')
    {
        $output = '';

        foreach ($valArray as $k => $v) {
            $values = $v['values'];
            if (empty($values[0])) {
                continue;
            }

            if ($v['format']) {
                $vv = vsprintf($v['format'], $values);
            }
            else {
                $vv = implode(' ', $values);
            }

            $output .= '<'.$tag.'><strong>'.$k.'</strong>: '.$vv.'</'.$tag.'>'.PHP_EOL;
        }

        return $output;
    }
}