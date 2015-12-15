<?php
class movio_modules_europeana_views_components_ShowResults extends org_glizy_components_Component
{
    function process()
    {
        $params = __Request::get('params');
        $query = json_decode($params, true);

        $paginateClass = $this->getComponentById( 'paginate' );
        $paginateClass->setRecordsCount();
        $limits = $paginateClass->getLimits();
        $start = $limits['start']+1;
        $checkBoxEnable = $query['imgCheckBox'] === "checkBoxEnable";
        $rows = 18;
        $diff = 0;
        if ($query['maxResult'] && !$checkBoxEnable && ($start + $rows > $query['maxResult'])) {
            $rows = $query['maxResult'] - $start+1;
            $diff = 18 - $rows;
        }
        $this->_content = array(
            'id' => $this->getId(),
            'records' => array(),
            'error' => null,
            'params' => $params,
            'rows' => $rows,
            'currentOffset' => $limits['start']
        );

        $request = org_glizy_ObjectFactory::createObject('movio.modules.europeana.SendRequest');
        $response = $request->execute($query, $start, $rows);
        if (!$response->error) {
            $this->_content['records'] = $response->records;
            if ($checkBoxEnable) {
                $this->_content['imgCheckBox'] = true;
                $total = $response->totalResults;
            } else {
                $this->_content['imgCheckBox'] = false;
                $total = $response->totalResults ? $query['maxResult'] : 0;
            }

        } else {
            $this->_content['error'] = $response->error;
        }

        $this->_content['imgList'] = $query['imgList'] ? $query['imgList'] : array();
        $paginateClass->setRecordsCount($total -$diff, $rows);
    }

}