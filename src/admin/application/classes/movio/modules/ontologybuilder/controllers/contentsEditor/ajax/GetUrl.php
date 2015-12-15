<?php
class movio_modules_ontologybuilder_controllers_contentsEditor_ajax_GetUrl extends org_glizy_mvc_core_CommandAjax
{
    const BOOTSTRAPMIN = 'http://www.europeana.eu/portal/themes/default/js/eu/europeana/bootstrap/min/bootstrap.min.js';

    function execute($url)
    {
        $this->directOutput = true;
        $langCode = $this->application->getEditingLanguage();
        $valid_url_regex = '/.*/';
        if ( !$url || !preg_match( $valid_url_regex, $url ) || !$this->user->isLogged()) {
            return false;
        } else {
            $urlArray = parse_url ($url);
            $baseUrl = strstr($url, '/', true);
            list($base, $domain) = explode('.', $baseUrl, 2);
            if($domain === 'wikipedia.org') {
                $url = str_replace("it", $langCode, $url);
            } else if(strpos($url,':')) {
                $url = str_replace(":", "%3A", $url);
                $url = str_replace(" ", "+", $url);
            }
            $contents = $this->fetchUrl($url);
            $contents = preg_replace('/(?=href="\/[^\/])(href="\/)/si', 'href="http://'.$baseUrl.'/' , $contents);
            $contents = preg_replace('/(?=src="\/[^\/])(src="\/)/si', 'src="http://'.$baseUrl.'/' , $contents);
            if($domain === 'europeana.eu') {
                $contents = str_replace('\'/portal', '\'http://'.$baseUrl.'/portal', $contents);
                $contents = str_replace('"/portal', '"http://'.$baseUrl.'/portal', $contents);
                $this->scriptAdjust($contents, $baseUrl);
            }
            return array('html' =>$contents);
        }
    }

    private function scriptAdjust(&$contents, $baseUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::BOOTSTRAPMIN);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        curl_close( $ch );
        $result = str_replace('\'/portal', '\'http://'.$baseUrl.'/portal', $result);
        $result = str_replace('"/portal', '"http://'.$baseUrl.'/portal', $result);
        $destination = "../cache/bootstrap.min.js";
        $file = fopen($destination, "w+");
        fputs($file, $result);
        fclose($file);
        $contents = str_replace('<script src="http://www.europeana.eu/portal/themes/default/js/eu/europeana/bootstrap/min/bootstrap.min.js"></script>', '<script src="../cache/bootstrap.min.js"></script>', $contents);
    }


    private function fetchUrl($url)
    {
        return file_get_contents('http://'.$url);
    }
}