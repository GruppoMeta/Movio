<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_Validator extends GlizyObject
{
	var $messageResult = '';



	function org_glizy_validators_Validator ()
	{
	}

	function getError()
	{
		return __Tp( $this->messageResult );
	}


	function validate( $value, $type )
	{
		$this->messageResult = '';

		if ( !empty( $value ) )
		{
			switch ($type)
			{
				case 'phone':
					if ($this->phone_validation($value) == 0)
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				case 'numeric':
					if ($this->numeric_validation($value)  == 0)
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				case 'only_char':
					if ($this->only_char_validation($value)  == 0 )
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				case 'date':
					if ($this->date_validation($value)  == 0)
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				case 'email':
					if ($this->email_validation($value)  == 0)
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				case 'decimal':
					if ($this->decimal_validation($value)  == 0)
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				case 'zip_code':
					if ($this->zip_code_validation($value)  == 0)
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;

				default:
					if ($this->alphaNumeric_validation($value) == 0 )
					{
						$this->messageResult = $this->get_error_input_message ($type);
					}
					break;
			}
		}
		return empty( $this->messageResult );
	}

	function validateRequired ($value, $required )
	{
		$this->messageResult = '';
		if ( !$required )
		{
			return true;
		}

		if ( empty( $value ) )
		{
			$this->messageResult = "Il campo '%s' &egrave; richiesto";
		}

		return empty( $this->messageResult );
	}

	function validateLength ($value, $minLength, $maxLength )
	{
		$this->messageResult = '';

		if( !is_null( $minLength  )  && (strlen ($value ) < $minLength ) )
		{
			$this->messageResult = "Il campo '%s' &egrave; inferiore alla lunghezza minima consentita";
		}

		if( !empty( $value ) && !is_null( $maxLength  ) && (strlen ($value ) > $maxLength ) )
		{
			$this->messageResult = "Il campo '%s' supera la lunghezza massima consentita";
		}
		return empty( $this->messageResult );
	}


	function phone_validation($phone)
	{
		$result = preg_match("/^[0-9\+\-\ ]+$/ ",$phone);
		return $result;
	}

	function numeric_validation($value)
	{
		$result = preg_match("/^[0-9]+$/",$value);
		return $result;
	}

	function only_char_validation($value)
	{
		$result = preg_match("/^[a-zA-Z]+$/s",$value);
		return $result;
	}

	function alphaNumeric_validation($value)
	{
		#$result =  preg_match("/^[a-zA-Z0-9\.\-\Ä\ä\Ö\ö\Ü\ü\è\é\ò\à\ù\ì\/\\ ]+$/",$value);
		$result = !empty($value);
		return $result;
	}



	function date_validation($date)
	{
		$acc_date = __T( 'GLZ_DATE_TOTIME_REGEXP');
		$result = preg_match($acc_date[0], $date);
		return $result;
	}

	function email_validation($email)
	{
		$result = TRUE;
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email))
		{
			$result = FALSE;
		}
		return $result;
	}

	function decimal_validation($value)
	{
		$result = preg_match("/^\d*\.?\d*$/",$value);
		return $result;
	}

	function zip_code_validation ($zip_code)
	{
		$result =  preg_match("/^[0-9]+$/ ",$zip_code);
		return $result;
	}

	//
	function get_error_input_message ($type)
	{
		switch ($type)
		{
			case 'phone': $message = "Il numero telefonico in input non &egrave; ammesso" ;
				break;

			case 'numeric': $message = "Il valore in input non &egrave; numerico" ;
				break;

			case 'only_char': $message = "Il valore in input non &egrave; valido";
				break;

			case 'date': $message = "la data Il valore in input non &egrave; ammessa";
				break;

			case 'email': $message = "Il valore email in input non &egrave; ammesso";
				break;

			case 'decimal': $message = "Il valore in input non &egrave; decimale corretto";

				break;

			case 'zip_code': $message = "CAP non ammesso";
				break;

			default: $message = "Il valore in input non &egrave; alfanumerico";
				break;
		}
		return $message;
	}
}