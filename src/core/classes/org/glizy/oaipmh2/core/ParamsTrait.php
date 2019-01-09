<?php
trait org_glizy_oaipmh2_core_ParamsTrait
{
    protected $application;

    /**
     * @param string $name
     * @param boolean $required
     * @param string $type
     * @return mixed
     */
    private function getParam($name, $required, $type)
    {
        $exists = __Request::exists($name);

        if ($required && !$exists) {
            throw org_glizy_oaipmh2_core_Exception::missingArgument($name);
        } else if (!$required && !$exists) {
            return null;
        }

        $value = __Request::get($name);
        return $this->validate($name, $value, $type);
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function validate($name, $value, $type)
    {
        $validateMetod = 'validate'.ucwords($type);
        return call_user_func_array([$this, $validateMetod], [$name, $value]);
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    private function validateDate($name, $value)
    {
        if (!org_glizy_oaipmh_OaiPmh::checkDateFormat($value) ) {
            throw org_glizy_oaipmh2_core_Exception::badGranularity($name, $value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    private function validateSet($name, $value)
    {
        return $value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    private function validateMetadataprefix($name, $value)
    {
        return $this->application->getSet($value);
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    private function validateGeneric($name, $value)
    {
        return $value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    private function validateResumptiontoken($name, $value)
    {
        if ($value && __Request::exists('from') && __Request::exists('until') && __Request::exists('set') && __Request::exists('metadataPrefix')) {
            throw org_glizy_oaipmh2_core_Exception::exclusiveArgument();
        }

        return $value;
    }



}
