<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_components_GetRecord extends org_glizy_components_Component
{
	protected $arPico;
	protected $setClass;

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		if ( __Request::exists( 'identifier' ) )
		{
			$identifier = __Request::get( 'identifier' );
			if (!org_glizy_oaipmh_OaiPmh::is_valid_uri($identifier))
			{
				$this->_application->setError('badArgument', 'identifier', $identifier );
				return;
			}
		}
		else
		{
			$this->_application->setError( 'missingArgument', 'identifier' );
			return;
		}

		if ( __Request::exists( 'metadataPrefix' ) )
		{
			$metadataPrefix = __Request::get( 'metadataPrefix' );
			$metadata = $this->_application->getMetadataFormat();
			if ( isset( $metadata[$metadataPrefix] ) )
			{
                //TODO
				//$inc_record = $metadata[$metadataPrefix]['myhandler'];
			}
			else
			{
				$this->_application->setError( 'cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix );
				return;
			}
		}
		else
		{
			$this->_application->setError( 'missingArgument', 'metadataPrefix' );
			return;
		}

		$id = str_replace( __Config::get( 'oaipmh.oaiPrefix' ), '', $identifier );
		if ($id == '')
		{
			$this->_application->setError( 'idDoesNotExist', '', $identifier);
			return;
		}

		$this->loadeRecord($id);
		if ( $this->arPico && $this->setClass )
			{
			if ( $this->setClass->loadRecord( $this->arPico->picoqueue_recordId ) )
				{
					$output = '<GetRecord>';
					$output .= '<record>';

					// header
				$datestamp = org_glizy_oaipmh_OaiPmh::formatDatestamp( $this->arPico->picoqueue_date );
				$status_deleted = $this->arPico->picoqueue_action == 'delete' ? true : false;

					$output .= '<header'.($status_deleted ? ' status="deleted"' : '' ).'>';
					$output .= '<identifier>'.org_glizy_oaipmh_OaiPmh::encode( $identifier ).'</identifier>';
					$output .= '<datestamp>'.org_glizy_oaipmh_OaiPmh::encode( $datestamp ).'</datestamp>';
					if (!$status_deleted)
					{
					$output .= '<setSpec>'.org_glizy_oaipmh_OaiPmh::encode( $this->arPico->picoqueue_recordModule ).'</setSpec>';
					}
					$output .= '</header>';
					$output .= '<metadata>';
				$output .= $this->setClass->getRecord($identifier);
					$output .= '</metadata>';
					$output .= '</record>';
					$output .= '</GetRecord>';
					$this->addOutputCode( $output );
				}
				else
				{
					$this->_application->setError( 'idDoesNotExist', '', $identifier);
					return;
				}
			}
			else
			{
				$this->_application->setError( 'idDoesNotExist', '', $identifier);
				return;
			}
	}

	protected function loadeRecord($id) {
		$this->arPico = org_glizy_ObjectFactory::createModel( 'org.glizy.oaipmh.models.PicoQueue' );
		if ( $this->arPico->find( array( 'picoqueue_identifier' => $id ) ) ) {
			// record trovato
			$this->setClass = org_glizy_ObjectFactory::createObject( $this->arPico->picoqueue_recordModule, $this->_application );
		} else {
			$this->arPico = null;
		}
	}
}