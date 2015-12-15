<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Code By https://github.com/ptisp/PHP-API-Client
 */

/**
 * Class org_glizy_rest_core_RestRequestMultipart
 *
 * Estensione di RestRequest specifica per le richieste di tipo multipart/form-data
 *
 * Prende come parametro $postfields una lista di chiave->valore, dove valore può anche essere un'array
 *
 * Esempio:
 * $postfields = array(
 *   'myobject[attachments][][resource]' => array('@/path/to/file1.txt','@/path/to/file2.txt')
 *   'myobject[anotherproperty] => 'a_value'
 * );
 *
 */
class org_glizy_rest_core_RestRequestMultipart extends org_glizy_rest_core_RestRequest
{

	public function __construct ($url = null, $verb = 'POST', $postfields = null, $fileNameMap = null)
	{
        $convertedRequest = $this->convertPostFieldsToString($postfields, $fileNameMap);

        parent::__construct(
            $url,
            $verb,
            $convertedRequest["content"],
            'multipart/form-data; boundary=' . $convertedRequest['boundary']
        );
	}

	public function buildPostBody ($data = null)
	{
        return;
	}

    /**
     * Converte la lista di chavi->valori in una stringa rapprensentante il corpo richiesta in multipart/form-data
     *
     * Codice originario:
     * - source: https://gist.github.com/simensen/288242
     * - usage:  https://gist.github.com/simensen/288240
     */
    protected function convertPostFieldsToString ($postfields, $fileNameMap=null) {
        $algos = hash_algos();
        $hashAlgo = null;
        foreach ( array('sha1', 'md5') as $preferred ) {
            if ( in_array($preferred, $algos) ) {
                $hashAlgo = $preferred;
                break;
            }
        }
        if ( $hashAlgo === null ) { list($hashAlgo) = $algos; }
        $boundary =
            '----------------------------' .
            substr(hash($hashAlgo, 'cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);

        $body = array();
        $crlf = "\r\n";
        $fields = array();
        foreach ( $postfields as $key => $value ) {
            if ( is_array($value) ) {
                foreach ( $value as $v ) {
                    $fields[] = array($key, $v);
                }
            } else {
                $fields[] = array($key, $value);
            }
        }
        foreach ( $fields as $field ) {
            list($key, $value) = $field;
            if ( strpos($value, '@') === 0 ) {
                preg_match('/^@(.*?)$/', $value, $matches);
                list($dummy, $filename) = $matches;
                $body[] = '--' . $boundary;
                if ( isset($fileNameMap[$filename]) && $fileNameMap[$filename] !== null )
                {
                    $computedName = $fileNameMap[$filename];
                } else
                {
                    $computedName = basename($filename);
                }
                $body[] = 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $computedName . '"';
                $body[] = 'Content-Type: application/octet-stream';
                $body[] = '';
                $body[] = file_get_contents($filename);
            } else {
                $body[] = '--' . $boundary;
                $body[] = 'Content-Disposition: form-data; name="' . $key . '"';
                $body[] = '';
                $body[] = $value;
            }
        }
        $body[] = '--' . $boundary . '--';
        $body[] = '';
        $content = join($crlf, $body);

        return array(
            "content" => $content,
            "boundary" => $boundary
        );

    }

}