<?php
trait org_glizy_oaipmh2_core_XmlOutputTrait
{
    /**
	 * @param string $string
	 * @param boolean $xmlescaped
	 * @return string
	 */
    private function encodeXmlText($string, $xmlescaped=false)
	{
		$string = stripslashes($string);
		// just remove invalid characters
		$pattern ="/[\x-\x8\xb-\xc\xe-\x1f]/";
	    $string = preg_replace($pattern,'',$string);

		// escape only if string is not escaped
		if (!$xmlescaped) {
            $string = htmlspecialchars($string, ENT_QUOTES);
		}

		return $string;
	}

	/**
	 * @param org_glizy_oaipmh2_models_VO_MetadataVO $metadata
	 * @return string
	 */
	private function openMetadataHeader(org_glizy_oaipmh2_models_VO_MetadataVO $metadata)
	{
		$output = !$metadata->recordPrefix ? '<'.$metadata->prefix : '<'.$metadata->prefix.':'.$metadata->recordPrefix;
		$output .= ' xmlns:'.$metadata->prefix.'="'.$metadata->namespace.'"';
		if ( $metadata->recordPrefix && $metadata->recordNamespace )
		{
			$output .= ' xmlns:'.$metadata->recordPrefix.'="'.$metadata->recordNamespace.'"';
		}
		$output .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
					' xsi:schemaLocation="'.$metadata->namespace.' '.$metadata->schema.'">';

		return $output;
	}

	/**
	 * @param org_glizy_oaipmh2_models_VO_MetadataVO $metadata
	 * @return string
	 */
	private function closeMetadataHeader(org_glizy_oaipmh2_models_VO_MetadataVO $metadata)
	{
		return !$metadata->recordPrefix ? '</'.$metadata->prefix.'>' : '</'.$metadata->prefix.':'.$metadata->recordPrefix.'>';
	}

	/**
	 * @param string $datestamp
	 * @return string
	 */
	private function formatDatestamp($datestamp)
	{
		$datestamp = $this->date2UTCdatestamp(glz_localeDate2default($datestamp));
		if (!$this->checkDateFormat($datestamp)) {
			return __Config::get( 'oaipmh.granularity' ) == 'YYYY-MM-DD' ? '2002-01-01' : '2002-01-01T00:00:00Z';
		}

		return $datestamp;
	}

	/**
	 * @param string $date
	 * @return string
	 */
	private function date2UTCdatestamp($date)
	{
		$granularity = __Config::get( 'oaipmh.granularity' );

		switch ($granularity) {

			case 'YYYY-MM-DDThh:mm:ssZ':
				// we assume common date ("YYYY-MM-DD") or
				// datetime format ("YYYY-MM-DD hh:mm:ss")
				// in the database
				if (strstr($date, ' ')) {
					// date is datetime format
					if (strstr($date, '+')) {
						// format ("YYYY-MM-DD hh:mm:ss+01")
						list($ld, $lt) = explode(" ", $date);
						list($y, $m, $d) = explode("-", $ld);
						list($time, $tz) = explode("+", $lt);
						list($h, $min, $s) = explode(":", $time);
						if ($tz > 0) {
							$timestamp = mktime($h, $min, $s, $m, $d, $y);
							$timestamp -= (int) $tz * 86400;
							return strftime("%Y-%m-%dT%H:%M:%SZ", $timestamp);
						}
					}
					return str_replace(' ', 'T', $date).'Z';
				} else {
					// date is date format
					// granularity 'YYYY-MM-DD' should be used...
					return $date.'T00:00:00Z';
				}
				break;

			case 'YYYY-MM-DD':
				if (strstr($date, ' ')) {
					// date is datetime format
					list($date) = explode(" ", $date);
					return $date;
				} else {
					return $date;
				}
				break;

			default: die("Unknown granularity!");
		}
	}

	/**
	 * @param string $date
	 * @return string
	 */
	private function checkDateFormat($date)
	{
	    if ( __Config::get( 'oaipmh.granularity' ) == 'YYYY-MM-DDThh:mm:ssZ')
	    {
			$checkstr = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z$/';
		} else {
			$checkstr = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}$)/';
		}
		if (preg_match($checkstr, $date, $regs))
		{
			if (checkdate($regs[2], $regs[3], $regs[1])) {
				return true;
			}
			else
			{
				return false;
			}
	    }
	    else
	    {
		    return false;
	    }
	}
}
