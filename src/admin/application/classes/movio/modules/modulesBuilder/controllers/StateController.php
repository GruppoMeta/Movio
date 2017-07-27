<?php
class movio_modules_modulesBuilder_controllers_StateController extends org_glizy_components_StateSwitchClass
{
	/**
	 * Submit dello step2D, � stata scelta la tabella
	 *
	 * @param string $oldState
	 * @return void
	 */
	function executeLater_step2Csv( $oldState )
	{
		// controlla se � stato ftto submit
		if ( strtolower( __Request::get( 'action', '' ) ) == 'next' )
		{
			// sposta il file caricato nella cache
			if(isset($_FILES['mbCsvFile']) && !empty($_FILES['mbCsvFile']) && !empty($_FILES['mbCsvFile']['name']))
			{
				$file 		= $_FILES['mbCsvFile'];
				$file_path 	= $file['tmp_name'];
				$file_name 	= $file['name'];
				$file_destname 	= __Paths::getRealPath( 'CACHE' ).md5( time().$file_name ).'.csv';

				if ( move_uploaded_file( $file_path, $file_destname ) )
				{
					$moduleName = explode('.', $file_name );
	                $options = array(
	                    'filePath' => $file_destname,
	                    'enclosure' => __Request::get( 'mbCsvEnclose', ';' ),
	                    'delimiter' => __Request::get( 'mbCsvSeparate', ';' )
	                );
					$this->_parent->refreshToState( 'step3',
							array(	'mbTable' => $this->getModuleName($moduleName[0]),
									'mbTableDB' => __Request::get('table'),
									'mbName' => $moduleName[0],
									'mbModuleType' => 'csv',
									'mbCsvOptions' => $options
								));
				}
				else
				{
					// errore
					$this->_parent->validateAddError( __T( 'Errore nel caricamento del file' ) );
				}

				@unlink( $file_destname );
			}
			else
			{
				// errore
				$this->_parent->validateAddError( __T( 'Errore nel caricamento del file' ) );
			}

		}
	}

	/**
	 * Submit dello step2D, � stata scelta la tabella
	 *
	 * @param string $oldState
	 * @return void
	 */
	function executeLater_step2DB( $oldState )
	{
		// controlla se � stato ftto submit
		if ( strtolower( __Request::get( 'action', '' ) ) == 'next' )
		{
			// controlla la validit� della form
			if ( $this->_parent->validate() )
			{
				$this->_parent->refreshToState( 'step3',
							array(	'mbTable' => $this->getModuleName(__Request::get('table')),
									'mbTableDB' => __Request::get('table'),
									'mbName' => __Request::get('table'),
									'mbModuleType' => 'db'
								));
			}
		}
	}

	function execute_step3( $oldState )
	{
		if ( strtolower( __Request::get( 'action', '' ) ) != 'next' )
		{
			// in fase di modifica il nome del modulo
			// viene passato in get
			if ( __Request::exists( 'mbTable' ) && __Request::exists( 'mbName' ) && __Request::exists( 'mod' ))
			{
				// cambia il tiolo della pagina
				$c = $this->_parent->getComponentById( "pageTitle" );
				$c->setAttribute( 'value', __T( 'Modifica modulo' ) );
				$c->process();

				// imposta altri valori dal file info
				$builder = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.Builder' );
				$values = file_get_contents( $builder->getCustomModulesFolder().'/Info' );
				$values = unserialize( $values );
				__Request::set( 'fieldOrder', $values[ 'fieldOrder' ] );
				__Request::set( 'fieldRequired', $values[ 'fieldRequired' ] );
				__Request::set( 'fieldType', $values[ 'fieldType' ] );
				__Request::set( 'fieldSearch', $values[ 'fieldSearch' ] );
				__Request::set( 'fieldListSearch', $values[ 'fieldListSearch' ] );
				__Request::set( 'fieldAdmin', $values[ 'fieldAdmin' ] );
				__Request::set( 'fieldLabel', $values[ 'fieldLabel' ] );
				__Request::set( 'mbModuleType', isset($values[ 'mbModuleType' ]) ? $values[ 'mbModuleType' ] : 'document' );
				__Request::set( 'mbTableDB', $values[ 'tableDb' ] );
			}
		}
	}


	function executeLater_step3( $oldState )
	{
		// controlla se � stato fatto submit
		if ( strtolower( __Request::get( 'action', '' ) ) == 'next' )
		{
			// controlla la validit� della form
			if ( $this->_parent->validate() )
			{
				$fieldOrder = __Request::get( 'fieldOrder' );
				$fieldSearch = __Request::get( 'fieldSearch' );
				$fieldListSearch = __Request::get( 'fieldListSearch' );
				$fieldAdmin = __Request::get( 'fieldAdmin' );

				$error = false;
				if ( is_null( $fieldSearch ) )
				{
					$error = true;
					$this->_parent->validateAddError( __T( 'Specificare i campi per la ricerca' ) );
				}
				if ( !$error && is_null( $fieldListSearch ) )
				{
					$error = true;
					$this->_parent->validateAddError( __T( 'Specificare i campi per il risultato della ricerca' ) );
				}
				if ( !$error && is_null( $fieldAdmin ) )
				{
					$error = true;
					$this->_parent->validateAddError( __T( 'Specificare i campi per la lista in amministrazione' ) );
				}

				if ( !$error )
				{
					__Request::set( 'fieldName', explode( ',', $fieldOrder ) );
					$builder = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.Builder' );
					$builder->execute();

					$this->_parent->refreshToState( 'step4' );
				}
			}
		}
	}

	function executeLater_step3new( $oldState )
	{
		// controlla se � stato fatto submit
		if ( strtolower( __Request::get( 'action', '' ) ) == 'next' )
		{
				$fieldOrder = __Request::get( 'fieldOrder' );
				__Request::set( 'fieldName', explode( ',', $fieldOrder ) );
				$mbName = __Request::get( 'mbName' );
				$mbTable = $this->getModuleName($mbName);
				__Request::set( 'mbTable', $mbTable );
				__Request::set( 'mbModuleType', 'document');

				$builder = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.Builder' );
				$builder->execute();
				$this->_parent->refreshToState( 'step4',
					array(
						'mbTable' => $mbTable
					)
				);
		}
	}

	function execute_step4( $oldState )
	{
    	$model = __Request::get( 'mbTable' ).'.models.Model';
		$ar = __ObjectFactory::createModel($model);

		// reindicizza se necessario
		if (method_exists($ar, 'reIndex')) {
			$it = __ObjectFactory::createModelIterator($model);
		    foreach ($it as $ar) {
		        $ar->reIndex();
		        $data = $ar->getValuesAsArray();
		        $ar->fulltext = org_glizycms_core_helpers_Fulltext::make($data, $ar);
		        $ar->forceModified('fulltext');
		        $ar->save();
		    }
		}
	}

	private function getModuleName($mbTable)
	{
		return strtolower( str_replace( array( '_', ' ', ')', '(', '/', '\\' ), '', $mbTable ) ).''.uniqid();
	}
}