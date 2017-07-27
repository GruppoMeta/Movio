<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_components_HtmlFormElement extends org_glizy_components_ComponentContainer
{
	var $_hiddenItem = false;
	var $_rightLabel = false;

	function init()
	{
		$this->defineAttribute('applyFormItemTemplate',	false, 	true,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('validationType',	false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('validationMessage',	false, 	'',	COMPONENT_TYPE_STRING);

		parent::init();

		// se c'è un binding lo imposta per poterlo rileggere
		$name = $this->getAttribute( 'name' );
		$name = empty( $name ) ? $this->getOriginalId() : $name;
		$bindToField =  $this->getAttribute('bindTo');
		if ( !empty($bindToField) && !__Request::exists( $bindToField ) )
		{
			__Request::set( $bindToField, __Request::get( $name ) );
		}


		if ( !$this->_application->isAdmin() ) //&& $this->getAttribute( "required" ) )
		{
			$this->addValidatorClass();
		}
	}



	function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		$this->_parent->addOutputCode($output, empty($editableRegion) ? $this->getEditableRegion() : $editableRegion, $atEnd);
	}

	protected function applyItemTemplate($label, $element) {
		if ($this->_outputMode != 'jsediting' && is_null( $this->getAttribute( 'skin' ) ) && $this->getAttribute( 'applyFormItemTemplate' ) )
		{
			return org_glizy_helpers_Html::applyItemTemplate($label, $element, $this->_hiddenItem, $this->_rightLabel );
		} else {
			return $label.$element;
		}
	}

	function validate()
	{
		if ( !$this->getAttribute( 'enabled' ) || !$this->getAttribute( 'visible' ) ) return;

        /** @var org_glizy_validators_Validator $validator */
		$validator = org_glizy_ObjectFactory::createObject( 'org.glizy.validators.Validator' );

		if ( !$validator->validateRequired( $this->_content, $this->getAttribute( 'required' ) ) )
		{
			$message = $this->getAttribute('requiredMessage');
			$message = empty( $message ) ? $validator->getError() : $message;
			$message = empty( $message ) ? '%s' : $message;
			$message = vsprintf( $message, array( $this->getAttribute( 'label' ) ) );
			$this->validateAddError( $message );
			unset( $validator );
			return;
		}

		if ( !is_null($this->getAttribute( 'validationType' ) ) )
		{
			if (!$validator->validate($this->_content, $this->getAttribute( 'validationType' ) ) )
			{
				$message = $this->getAttribute('validationMessage');
				if ( empty( $message ) )
				{
					$message = $this->getAttribute('requiredMessage');
				}
				$message = empty( $message ) ? $validator->getError() : $message;
				$message = vsprintf( $message, array( $this->getAttribute( 'label' ) ) );
				$this->validateAddError( $message );
			}

			// valida la lunghezza massima del campo
			if ( $this->issetAttribute( 'maxLength' ) || $this->issetAttribute( 'minLength' ) )
			{
				if (!$validator->validateLength( $this->_content, $this->getAttribute( 'minLength' ), $this->getAttribute( 'maxLength' ) ) )
				{
					$message = $this->getAttribute('validationMessage');
					if ( empty( $message ) )
					{
						$message = $this->getAttribute('requiredMessage');
					}
					$message = empty( $message ) ? $validator->getError() : $message;
					$message = vsprintf( $message, array( $this->getAttribute( 'label' ) ) );
					$this->validateAddError( $message );
				}
			}
		}
		unset( $validator );
	}

	function addValidatorClass()
	{
		$cssClass = $this->getAttribute( 'cssClass' );
		$type = $this->getAttribute( 'validationType' );
		if ( !is_null( $type ) )
		{
			$required = $this->getAttribute( 'required' );

			if ($required == 'true')
			{
				$requiredString = "required,";
			}
			else
			{
				$requiredString = "";
			}

			switch ($type)
			{
				case 'phone':
					$cssClass .= " validate[" . $requiredString. "custom[phone]]";
					break;

				case 'numeric':
				case 'zip_code':
					$cssClass .= " validate[" . $requiredString. "custom[integer]]";
					break;

				case 'decimal':
					$cssClass .= " validate[" . $requiredString. "custom[number]]";
					break;

				case 'only_char':
					$cssClass .= " validate[" . $requiredString. "'nodigit']";
					break;

				case 'date':
					$cssClass .= " validate[" . $requiredString. "custom[date]]";
					break;

				case 'dateTime':
					$cssClass .= " validate[" . $requiredString. "custom[dateTime]]";
					break;

				case 'email':
					$cssClass .= " validate[" . $requiredString. "custom[email]]";
					break;

				case 'password':
					$cssClass .= " validate[" . $requiredString. "minSize[6]]";
					break;

				case 'file':
				case 'checkbox':
					$cssClass .= " validate[" . $requiredString. "]";
					break;

				case 'alphanum':
					$cssClass .= " validate[" . $requiredString. "custom[onlyLetterNumber]]";
					break;

				case 'not_empty':
					$minLength = $this->getAttribute( 'minLength' );
					if ( is_null( $minLength ) ) $minLength = 3;
					$maxLength = $this->getAttribute( 'maxLength' );
					if ( is_null( $maxLength ) ) $maxLength = 1000;
					$cssClass .= " validate[" . $requiredString. "minSize[".$minLength."],maxSize[".$maxLength."]]";
					break;
			}

			$this->setAttribute( 'cssClass', $cssClass );

		}
		else
		{
			$required = $this->getAttribute( 'required' );
			if ( $required )
			{
				$cssClass .= " validate[required]";
				$this->setAttribute( 'cssClass', $cssClass );
			}

		}
	}

	protected function addWrapDiv( &$output ) {
		if ( $this->getAttribute('wrapDiv') ) {
			$cssClass = $this->getAttribute('cssClassWrapDiv');
			$output = '<div'.( $cssClass ? ' class="'.$cssClass.'"' : '').'>'.
							$output.
							'</div>';


		}
	}

}