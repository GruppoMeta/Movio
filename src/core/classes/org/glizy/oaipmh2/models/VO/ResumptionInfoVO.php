<?php
class org_glizy_oaipmh2_models_VO_ResumptionInfoVO
{
    public $prefix;
    public $numRows;
    public $limitStart;
    public $limitEnd;
    public $from;
    public $until;
    public $set;
    public $metadataSets;

    /**
     * @param array $params
     * @return org_glizy_oaipmh2_models_VO_ResumptionInfoVO
     */
    public static function create($params)
    {
        $resumptionInfoVO = new self;
        foreach ($params as $name => $value) {
            $resumptionInfoVO->$name = $value;
        }

        return $resumptionInfoVO;
    }
}
