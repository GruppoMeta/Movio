<?php
class movio_modules_europeana_SendRequest
{
    public function execute($query, $start, $rows)
    {
        $europeanaResponseVO = org_glizy_objectFactory::createObject('movio.modules.europeana.EuropeanaResponseVO');
        if (__Config::get('DEBUG')) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            ini_set('display_errors', 1);
        }

        $q = array();
        $keySearch = explode(',', __Config::get('movio.europeana.searchFields'));
        foreach ($keySearch as $key) {
            if ($query[$key]) {
                $val = str_replace(' ', '+', trim($query[$key]));
                if ($key == "TYPE") {
                    $val = strtoupper($val);
                } else if($key == "LANGUAGE") {
                    $val = strtolower($val);
                }
                $q[]= $key.':"'.$val.'"';
            }
        }

        $params = array(
            'wskey' => __Config::get('movio.europeana.apiKey'),
            'rows' => $rows ?: $query['maxResult'],
            'start' =>$start ?: 1,
            'query' => implode($q, ' ')
        );

        $request = org_glizy_objectFactory::createObject('org.glizy.rest.core.RestRequest', __Config::get('movio.europeana.apiSearchUrl'), 'GET', $params);
        $request->execute();
        $response = $request->getResponseBody();
        $response = json_decode($response);
        if ($response && $response->success && $response->itemsCount > 0) {
            foreach ($response->items as $item) {
                $imgSrc = $item->edmPreview[0] ? $item->edmPreview[0] : __Config::get('movio.noImage.src');
                $europeanaResponseVO->records[] = array(
                    'id' => $item->id,
                    'image' => $imgSrc,
                    'title' => $item->title,
                    'url' => $item->guid
                );
            }

            $europeanaResponseVO->totalResults = $response->totalResults;
        } else {
            $europeanaResponseVO->error = $response->error;
        }

        return $europeanaResponseVO;
    }
}