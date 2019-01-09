<?php
interface org_glizy_oaipmh2_core_RecordIteratorInterface extends Iterator
{
    /**
     * @return org_glizy_oaipmh2_models_VO_RecordVO
     */
    public function current();
}
