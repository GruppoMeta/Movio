<?php
interface org_glizy_oaipmh2_core_AdapterInterface
{
    /**
     * @param string $sets
     * @param string $from
     * @param string $until
     * @param integer $limitStart
     * @param integer $limitLength
     * @return org_glizy_oaipmh2_models_VO_ListVO
     */
    public function findAll($sets, $from, $until, $limitStart, $limitLength);


     /**
     * @param string $model
     * @param string $id
     * @return string
     */
    public function createIdentifier($model, $id);

    /**
     * @param string $identifier
     * @return org_glizy_oaipmh2_models_VO_IdentifierVO
     */
    public function parseIdentifier($identifier);
}
