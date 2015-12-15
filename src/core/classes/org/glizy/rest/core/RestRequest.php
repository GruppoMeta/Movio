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
 * Class org_glizy_rest_core_RestRequest
 */

class org_glizy_rest_core_RestRequest extends GlizyObject
{
	protected $url;
	protected $verb;
	protected $requestBody;
	protected $requestLength;
	protected $username;
	protected $password;
	protected $acceptType;
	protected $responseBody;
	protected $responseInfo;
    protected $responseError;
	protected $authType;
	protected $disableSslCheck = false;
    protected $contentType;
    protected $cookies;
    protected $proxy;
    protected $proxyPort;
    protected $timeout;

    /**
     * @param null   $url
     * @param string $verb
     * @param null   $requestBody
     * @param null   $contentType
     */
	public function __construct ($url = null, $verb = 'GET', $requestBody = null, $contentType = null)
	{
		$this->url				= $url;
		$this->verb				= $verb;
		$this->requestBody		= $requestBody;
		$this->requestLength	= 0;
		$this->username			= null;
		$this->password			= null;
		$this->acceptType		= 'application/json';
		$this->responseBody		= null;
		$this->responseInfo		= null;
		$this->authType 		= CURLAUTH_BASIC;
        $this->contentType      = $contentType;
        $this->cookies          = null;
        $this->proxy            = null;
        $this->proxyPort        = null;

		if ($this->requestBody !== null && !is_string($this->requestBody))
		{
			$this->buildPostBody();
		}
	}

	public function flush ()
	{
		$this->requestBody		= null;
		$this->requestLength	= 0;
		$this->verb				= 'GET';
		$this->responseBody		= null;
		$this->responseInfo		= null;
	}

    /**
     * @return bool
     * @throws Exception
     */
	public function execute ()
	{
		$ch = curl_init();
		$this->setAuth($ch);

		try
		{
			switch (strtoupper($this->verb))
			{
				case 'GET':
                    $result = $this->executeGet($ch);
					break;
				case 'POST':
                    $result = $this->executePost($ch);
					break;
				case 'PUT':
                    $result = $this->executePut($ch);
					break;
				case 'DELETE':
                    $result = $this->executeDelete($ch);
					break;
				default:
					throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
			}

            return $result;
		}
		catch (InvalidArgumentException $e)
		{
			curl_close($ch);
			throw $e;
		}
		catch (Exception $e)
		{
			curl_close($ch);
			throw $e;
		}

	}

    /**
     * @param null $data
     */
	public function buildPostBody ($data = null)
	{
        if ($this->contentType == 'multipart/form-data') return;
		$data = ($data !== null) ? $data : $this->requestBody;

        if (!is_array($data))
		{
			throw new InvalidArgumentException('Invalid data input for postBody.  Array expected');
		}

		$data = http_build_query($data, '', '&');
		$this->requestBody = $data;
	}

    /**
     * @param $ch
     *
     * @return bool
     */
	protected function executeGet ($ch)
	{
		if (!is_string($this->requestBody) && !is_null($this->requestBody))
		{
			$this->buildPostBody();
		}

		$this->url .= ( strpos( '?', $this->url ) === false ? '?' : '&' ).$this->requestBody;
        return $this->doExecute($ch);
	}

    /**
     * @param $ch
     *
     * @return bool
     */
	protected function executePost ($ch)
	{
		if (!is_string($this->requestBody) && !is_null($this->requestBody))
		{
			$this->buildPostBody();
		}

        curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);

        return $this->doExecute($ch);
	}

    /**
     * @param $ch
     *
     * @return bool
     */
	protected function executePut ($ch)
	{
		if (!is_string($this->requestBody) && !is_null($this->requestBody))
		{
			$this->buildPostBody();
		}

		$this->requestLength = strlen($this->requestBody);

		$fh = fopen('php://memory', 'rw');
		fwrite($fh, $this->requestBody);
		rewind($fh);

		curl_setopt($ch, CURLOPT_INFILE, $fh);
		curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
		curl_setopt($ch, CURLOPT_PUT, true);

        $result = $this->doExecute($ch);

		fclose($fh);

        return $result;
	}

    /**
     * @param $ch
     *
     * @return bool
     */
	protected function executeDelete ($ch)
	{
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->doExecute($ch);
	}

    /**
     * @param $curlHandle
     *
     * @return bool
     */
	protected function doExecute (&$curlHandle)
	{
		$this->setCurlOpts($curlHandle);

        if ($result = curl_exec($curlHandle)) {
            $this->responseBody = $result;
		$this->responseInfo	= curl_getinfo($curlHandle);
            $result = true;
        } else {
            $this->responseError = curl_error($curlHandle);
            $result = false;
        }

		curl_close($curlHandle);
		return $result;
	}

    /**
     * @param $ch
     * @param string $headerLine
     *
     * @return int
     */
	function curlResponseHeaderCallback($ch, $headerLine)
	{
        if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1) {
            $this->cookies[] = $cookie[1];
        }
        return strlen($headerLine); // Needed by curl
    }

    /**
     * @param $curlHandle
     */
	protected function setCurlOpts (&$curlHandle)
	{
	    if (!empty($this->cookies)) {
	        curl_setopt($curlHandle, CURLOPT_COOKIE, implode('; ', $this->cookies));
	        $this->cookies = array();
	    }
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout ? $this->timeout : 10);
		curl_setopt($curlHandle, CURLOPT_URL, $this->url);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HEADERFUNCTION, array($this, 'curlResponseHeaderCallback'));

        $header = array('Accept: ' . $this->acceptType);

        if ($this->contentType) {
            $header[] = 'Content-Type: ' . $this->contentType;
        }

	    curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header);

        if ($this->proxy && $this->proxyPort)
        {
            curl_setopt($curlHandle, CURLOPT_PROXY, $this->proxy);
            curl_setopt($curlHandle, CURLOPT_PROXYPORT, $this->proxyPort);
        }

		if ( $this->disableSslCheck )
		{
			curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
		}
	}

    /**
     * @param $curlHandle
     */
	protected function setAuth (&$curlHandle)
	{
		if ($this->username !== null && $this->password !== null)
		{
			curl_setopt($curlHandle, CURLOPT_HTTPAUTH,  $this->authType );
			curl_setopt($curlHandle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
	}

    /**
     * @return string
     */
	public function getAcceptType ()
	{
		return $this->acceptType;
	}

    /**
     * @param $acceptType
     */
	public function setAcceptType ($acceptType)
	{
		$this->acceptType = $acceptType;
	}

    /**
     * @return null
     */
	public function getPassword ()
	{
		return $this->password;
	}

    /**
     * @param $password
     */
	public function setPassword ($password)
	{
		$this->password = $password;
	}

    /**
     * @return null
     */
	public function getResponseBody ()
	{
		return $this->responseBody;
	}

    /**
     * @return null
     */
	public function getResponseInfo ()
	{
		return $this->responseInfo;
	}

    /**
     * @return mixed
     */
    public function getResponseError ()
    {
        return $this->responseError;
    }

    /**
     * @return null
     */
	public function getUrl ()
	{
		return $this->url;
	}

    /**
     * @param $url
     */
	public function setUrl ($url)
	{
		$this->url = $url;
	}

    /**
     * @return null
     */
	public function getUsername ()
	{
		return $this->username;
	}

    /**
     * @param $username
     */
	public function setUsername ($username)
	{
		$this->username = $username;
	}

    /**
     * @return string
     */
	public function getVerb ()
	{
		return $this->verb;
	}

    /**
     * @param $verb
     */
	public function setVerb ($verb)
	{
		$this->verb = $verb;
	}

    /**
     * @return string
     */
	public function getCookies()
	{
		return $this->cookies;
	}

    /**
     * @param string $cookies
     */
	public function setCookies($cookies)
	{
		$this->cookies = $cookies;
	}

    /**
     * @return int
     */
	public function getAuthType ()
	{
		return $this->authType;
	}

    /**
     * @param $authType
     */
	public function setAuthType ($authType)
	{
		$this->authType = $authType;
	}

    /**
     * @param bool $state
     */
    public function disableSslCheck ($state = true)
	{
		$this->disableSslCheck = $state;
	}

    /**
     * @return int
     */
    public function getProxyPort()
    {
        return $this->proxyPort;
    }

    /**
     * @param $proxyPort
     */
    public function setProxyPort($proxyPort)
    {
        $this->proxyPort = $proxyPort;
    }

    /**
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @param $proxy
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

	/**
     * @param $value
     *
     * @return bool
     */
	public function setTimeout ($value)
	{
		if ($value) {
			$this->timeout = $value;
			return true;
		}
		else {
			return false;
		}
	}
}