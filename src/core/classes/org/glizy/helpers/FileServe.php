<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_FileServe
{
	static public function serve($fileName, $originalFileName=null, $expires=null)
	{
		$mime = !__Config::get('glizy.helpers.FileServe.forceDownload') ? org_glizy_helpers_FileServe::mimeType($fileName) : 'application/force-download';
		$fileSize = filesize($fileName);
		$gmdate_mod = gmdate('D, d M Y H:i:s', filemtime($fileName) );
		if(! strstr($gmdate_mod, 'GMT')) {
			$gmdate_mod .= ' GMT';
		}

		$disposition = in_array($mime, array('application/pdf', 'image/gif', 'image/png', 'image/jpeg')) ? 'inline' : 'attachment';

		if ($expires) {
			$exp_gmt = gmdate("D, d M Y H:i:s", time() + $expires) . " GMT";
    		header('Cache-Control: max-age='.$expires.', must-revalidate');
        	header('Expires: '.$exp_gmt);
		}

        header('Accept-Ranges: bytes');
	    header('Content-Type: ' . $mime);
	    header('Content-Length: ' . $fileSize);
		header('Last-Modified: ' . $gmdate_mod);
		header('Content-Transfer-Encoding: binary');
    	if ($originalFileName) {
		    header('Content-Disposition: '.$disposition.'; filename=' . $originalFileName);
	    } else {
		    header('Content-Disposition: '.$disposition);
	    }

	    @ob_end_clean();
	    @ob_end_flush();
	    readfile($fileName);
	    exit;
	}

	static public function mimeType($fileName)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $fileName);
	}
}


// http://stackoverflow.com/questions/2000715/answering-http-if-modified-since-and-http-if-none-match-in-php
// http://stackoverflow.com/questions/14661637/allowing-caching-of-image-php-until-source-has-been-changed
// http://www.coneural.org/florian/papers/04_byteserving.php

