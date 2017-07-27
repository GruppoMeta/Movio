<?php
class movio_modules_ontologybuilder_controllers_contentsEditor_ajax_GetUrl extends org_glizy_mvc_core_CommandAjax
{
    function execute($url)
    {
        $this->checkPermissionForBackend();

        $this->directOutput = true;
        $langCode = $this->application->getEditingLanguage();
        $url = str_replace('##LANGUAGE##', $langCode, $url);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $urlParts = parse_url($url);
        $baseUrl = $urlParts['scheme'].'://'.$urlParts['host'];
        if (isset($urlParts['port'])) {
            $baseUrl .= ':'.$urlParts['port'];
        }
        $baseUrl .= '/';
        $contents = $this->fetchUrl($url);
        $contents = preg_replace('/(?=href="\/[^\/])(href="\/)/si', 'href="'.$baseUrl, $contents);
        $contents = preg_replace('/(?=src="\/[^\/])(src="\/)/si', 'src="'.$baseUrl, $contents);
        return array('html' =>$contents);
    }

    private function fetchUrl($url)
    {
        return file_get_contents($url);
    }
}