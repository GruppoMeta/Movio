<?php
class org_glizy_oaipmh2_core_Exception extends Exception
{
    public $oaiCode;

    public function __construct ($message, $oaiCode)
    {
        parent::__construct($message);
        $this->oaiCode = $oaiCode;
    }

	/**
	 * @return string
	 */
    public function __toString()
    {
        return ' <error code="'.org_glizy_oaipmh_OaiPmh::encode( $this->oaiCode ).'">'.org_glizy_oaipmh_OaiPmh::encode( $this->message )."</error>";
    }

	/**
	 * @return Exception
	 */
    public static function noVerb()
    {
        return new self('The request does not provide any verb.', 'noVerb');
	}

	/**
	 * @return Exception
	 */
    public static function noSetHierarchy()
    {
        return new self('This repository does not support sets.', 'noSetHierarchy');
	}

	/**
	 * @param string $argument
	 * @return Exception
	 */
    public static function missingArgument($argument)
    {
        return new self(sprintf('The required argument "%s" is missing in the request.', $argument), 'missingArgument');
	}

	/**
	 * @param string $argument
	 * @param string $value
	 * @return Exception
	 */
    public static function badGranularity($argument, $value)
    {
        return new self(sprintf('The value "%s" of the argument "%s" is not valid.', $value, $argument), 'missingArgument');
	}


	/**
	 * @param string $format
	 * @return Exception
	 */
    public static function cannotDisseminateFormat($format)
    {
        return new self(sprintf('The metadata format "%s" is not supported by this repository.', $format), 'cannotDisseminateFormat');
	}

	/**
	 * @return Exception
	 */
    public static function noRecordsMatch()
    {
        return new self('The combination of the given values results in an empty list.', 'noRecordsMatch');
	}

	/**
	 * @return Exception
	 */
	public static function exclusiveArgument()
    {
        return new self('The usage of resumptionToken as an argument allows no other arguments.', 'exclusiveArgument');
	}

	/**
	 * @return Exception
	 */
	public static function badResumptionToken()
    {
        return new self('The resumptionToken does not exist or has already expired.', 'badResumptionToken');
	}

	/**
	 * @param string $argument
	 * @param string $value
	 * @return Exception
	 */
    public static function badArgument($argument, $value)
    {
        return new self(sprintf('The argument "%s" (value="%s") included in the request is not valid.', $value, $argument), 'badArgument');
	}

	/**
	 * @param string $value
	 * @return Exception
	 */
    public static function idDoesNotExist($value)
    {
        return new self(sprintf('The value "%s" of the identifier is illegal for this repository.', $value), 'idDoesNotExist');
	}

	/**
	 * @return Exception
	 */
	public static function noAdapter()
    {
        return new self('No adapter class defined.', 'badArgument');
	}

	/**
	 * @param string $message
	 * @return Exception
	 */
	public static function genericError($message)
    {
        return new self(sprintf('Error: %s', $message), 'badArgument');
	}
}
