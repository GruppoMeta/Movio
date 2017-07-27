<?php
class org_glizycms_userManager_controllers_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
		$this->directOutput = true;

    	$data = json_decode($data);
    	$id = (int)$data->__id;
    	$modelName = $data->__model;

		// controlla se l'email è già nel DB
		$ar = __ObjectFactory::createModel($modelName);
		if ( $ar->find( array( 'user_email' => $data->user_email ) ) ) {
			if ( $id != $ar->user_id ) {
				return array('errors' => array(__T( 'E-mail is already present' )));
			}
		}
        $ar ->emptyRecord();
        if ( $ar->find( array( 'user_loginId' => $data->user_loginId ) ) ) {
            if ( $id != $ar->user_id ) {
                return array('errors' => array(__T( 'Username is already present' )));
            }
        }

		$password = $data->user_password;
		$password = $password ? glz_password( $password ) : $ar->user_password;
		$data->user_password = $password;
		if ( $id == 0 ) {
			$data->user_dateCreation = new org_glizy_types_DateTime();
		}

        $proxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ActiveRecordProxy');
        $result = $proxy->save($data);

        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errors' => $result);
        }
    }
}