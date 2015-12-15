<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_OaiPmh
{
	public static function is_valid_uri($url)
	{
	         return((bool) preg_match("'^[^:]+:(?:[a-z_0-9-]+[\.]{1})*(?:[a-z_0-9-]+\.)[a-z]{2,3}.*$'i", $url));
	}

	public static function encode($string, $xmlescaped=false)
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

	public static function openMetadataHeader( $metadata )
	{
		$output = !$metadata[ 'recordPrefix' ] ? '<'.$metadata[ 'prefix' ] : '<'.$metadata[ 'prefix' ].':'.$metadata[ 'recordPrefix' ];
		$output .= ' xmlns:'.$metadata[ 'prefix' ].'="'.$metadata['namespace'].'"';
		if ( $metadata[ 'recordPrefix' ] && $metadata[ 'recordNamespace' ] )
		{
			$output .= ' xmlns:'.$metadata[ 'recordPrefix' ].'="'.$metadata['recordNamespace'].'"';
		}
		$output .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
		$output .= ' xsi:schemaLocation="'.$metadata['namespace'].' '.$metadata['schema'].'">';

		return $output;
	}

	public static function closeMetadataHeader( $metadata )
	{
		return !$metadata[ 'recordPrefix' ] ? '</'.$metadata[ 'prefix' ].'>' : '</'.$metadata[ 'prefix' ].':'.$metadata[ 'recordPrefix' ].'>';
	}

	public static function checkDateFormat($date)
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


	public static function formatDatestamp($datestamp)
	{
		$datestamp = glz_localeDate2default($datestamp);
		$datestamp = org_glizy_oaipmh_OaiPmh::date2UTCdatestamp($datestamp);
		if (!org_glizy_oaipmh_OaiPmh::checkDateFormat($datestamp)) {
			if ( __Config::get( 'oaipmh.granularity' ) == 'YYYY-MM-DD') {
				return '2002-01-01';
			} else {
				return '2002-01-01T00:00:00Z';
			}
		} else {
			return $datestamp;
		}
	}


	public static function date2UTCdatestamp($date)
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

	public static function tokenValid()
	{
		return 24*3600;
	}

	public static function getTokenId()
	{
		return md5( microtime( true ) );
	}

	public static function createResumptionToken( $type, $info )
	{
		$isLast = $info[ 'numRows' ] <= $info[ 'limitEnd' ];
		if ( !$isLast )
		{
			$expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time()+self::tokenValid());
			$tokenId = $type.'-'.self::getTokenId();
			file_put_contents( __Paths::get( 'CACHE' ).$tokenId, serialize( $info ) );
			return '<resumptionToken expirationDate="'.$expirationdatetime.'" completeListSize="'.$info[ 'numRows' ].'" cursor="'.$info[ 'limitStart' ].'">'.$tokenId.'</resumptionToken>';
		}
		else
		{
			return '<resumptionToken completeListSize="'.$info[ 'numRows' ].'" cursor="'.$info[ 'limitStart' ].'"></resumptionToken>';
		}
	}
}