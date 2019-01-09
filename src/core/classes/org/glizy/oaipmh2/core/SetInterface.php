<?php
interface org_glizy_oaipmh2_core_SetInterface
{
    /**
     * @return array
     */
    public function getSetInfo();

    /**
     * @param org_glizy_oaipmh2_models_VO_RecordVO $recordVO
     * @return string
     */
    public function getRecord(org_glizy_oaipmh2_models_VO_RecordVO $recordVO);
}
