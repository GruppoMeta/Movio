<?php
class movio_modules_touristoperators_controllers_Index extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $data = $this->view->getContent();
        if (!$data->visualization) {
            $data->visualization = 'list';
        }

        $this->setComponentsAttribute('filters', 'enabled', $data->showForm == 'true'
                                                    && ($data->showFulltext
                                                    || $data->showType
                                                    || $data->showCountry
                                                    || $data->showDistrict
                                                    || $data->showPlace));
        $this->setComponentsAttribute('filterTitle', 'enabled', $data->showFulltext);
        $this->setComponentsAttribute('filterType', 'enabled', $data->showType);
        $this->setComponentsAttribute('filterCountry', 'enabled', $data->showCountry);
        $this->setComponentsAttribute('filterDistrict', 'enabled', $data->showDistrict);
        $this->setComponentsAttribute('filterPlace', 'enabled', $data->showPlace);
        $this->setComponentsAttribute($data->visualization, 'enabled', true);
    }
}
