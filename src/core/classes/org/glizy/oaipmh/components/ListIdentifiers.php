<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_oaipmh_components_ListIdentifiers extends org_glizy_components_Component
{
	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		$from = '';
		$until = '';
		$set = '';
		$limitStart = 0;

		if ( __Request::exists( 'resumptionToken' ) )
		{
			if ( !__Request::exists( 'from' ) && !__Request::exists( 'until' ) && !__Request::exists( 'set' ) && !__Request::exists( 'metadataPrefix' ) )
			{
				// TODO controllare la data di scadenza del token
				$tokenId = __Request::get( 'resumptionToken' );
				$fileName =  __Paths::get( 'CACHE' ).$tokenId;
				if ( file_exists( $fileName ) )
				{
					$info = unserialize( file_get_contents( $fileName ) );
					$limitStart = $info[ 'limitEnd' ];
					$filters = $info[ 'filters' ];
					$metadataPrefix = $info[ 'metadataPrefix' ];
				}
				else
				{
					$this->_application->setError( 'badResumptionToken', '', $tokenId );
					return;
				}
			}
			else
			{
				$this->_application->setError( 'exclusiveArgument' );
				return;
			}
		}
		else
		{
			// controlla i parametri ricevuti
			if ( __Request::exists( 'from' ) )
			{
				$from = __Request::get( 'from' );
				if ( !org_glizy_oaipmh_OaiPmh::checkDateFormat($from) )
				{
					$this->_application->setError( 'badGranularity', 'from', $from);
					return;
				}
			}

			if ( __Request::exists( 'until' ) )
			{
				$until = __Request::get( 'until' );
				if (!org_glizy_oaipmh_OaiPmh::checkDateFormat($until))
				{
					$this->_application->setError( 'badGranularity', 'until', $until);
					return;
				}
			}

			if ( __Request::exists( 'set' ) )
			{
				$set = __Request::get( 'set' );
			}

			if ( __Request::exists( 'metadataPrefix' ) )
			{
				$metadataPrefix = __Request::get( 'metadataPrefix' );

			}
			else
			{
				$this->_application->setError( 'missingArgument', 'metadataPrefix' );
				return;
			}

			// TODO: scrivere i filtri per ActiveRecord Doctrine
			$filters = array();
			if ( $from )
			{
				$filters[] = 'picoqueue_date >= '.org_glizy_dataAccess_DataAccess::qstr( $from );
			}
			if ( $until )
			{
				$filters[] = 'picoqueue_date <= '.org_glizy_dataAccess_DataAccess::qstr( $until );
			}
			if ( $set )
			{
				$filters[] = 'picoqueue_recordModule = '.org_glizy_dataAccess_DataAccess::qstr( $set );
			}
		}


		$metadata = $this->_application->getMetadataFormat();
		if ( isset( $metadata[$metadataPrefix] ) )
		{
            // TODO
//			$inc_record = $metadata[$metadataPrefix]['myhandler'];
		}
		else
		{
			$this->_application->setError( 'cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix );
			return;
		}

		$limitLength = __Config::get( 'oaipmh.maxIds' );
		$it = org_glizy_ObjectFactory::createModelIterator( 'org.glizy.oaipmh.models.PicoQueue', 'all', array(
																										'filters' => $filters,
																										'limit' => array( $limitStart, $limitLength ),
																										'numRows' => true
																										 ) );
		$num_rows = $it->count();
		if ( $num_rows > 0 )
		{
			$oaiPrefix = __Config::get( 'oaipmh.oaiPrefix' );
			$countrec = 0;

			$output = '<ListIdentifiers>';
			$output .= org_glizy_oaipmh_OaiPmh::createResumptionToken( 'ListIdentifiers',
										array(
											'numRows' => $num_rows,
											'limitStart' => $limitStart,
											'limitEnd' => $limitStart + $limitLength,
											'filters' => $filters,
											'metadataPrefix' => $metadataPrefix,
										) );

			foreach ($it as $arC) {
				$countrec++;
				$identifier = $oaiPrefix.$arC->picoqueue_identifier;
				$datestamp = org_glizy_oaipmh_OaiPmh::formatDatestamp( $arC->picoqueue_date );
				$status_deleted = $arC->picoqueue_action == 'delete' ? true : false;

				$output .= '  <header';
				if ($status_deleted)
				{
					$output .= ' status="deleted"';
				}
				$output .='>';

				// use xmlrecord since we use stuff from database
				$output .= '<identifier>'.org_glizy_oaipmh_OaiPmh::encode( $identifier ).'</identifier>';
				$output .= '<datestamp>'.org_glizy_oaipmh_OaiPmh::encode( $datestamp ).'</datestamp>';
				if (!$status_deleted)
				{
					$output .= '<setSpec>'.org_glizy_oaipmh_OaiPmh::encode( $arC->picoqueue_recordModule ).'</setSpec>';
				}
				$output .= '  </header>'."\n";
			}

			$output .= '</ListIdentifiers>';
			$this->addOutputCode( $output );
		}
		else
		{
			$this->_application->setError( 'noRecordsMatch' );
		}
	}
}