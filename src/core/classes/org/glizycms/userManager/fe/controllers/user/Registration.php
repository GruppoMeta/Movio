<?php
class org_glizycms_userManager_fe_controllers_user_Registration extends org_glizy_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function executeLater()
    {
       if ($this->submit && $this->controller->validate()) {
            $email = org_glizy_Request::get('user_email', '');
            $ar = org_glizy_ObjectFactory::createModel('org.glizy.models.User');
            if ($ar->find(array('user_loginId' => $email))) {
// TODO tradurre
                $this->view->validateAddError('L\'email è già presente nel database, usare un\'altra email o richiedere la password');
                return;
            }

            $fields = $ar->getFields();
            foreach($fields as $k=>$v) {
                if (__Request::exists($k)) {
                    $ar->$k = __Request::get($k);
                }
            }

            $ar->user_FK_usergroup_id = __Config::get('USER_DEFAULT_USERGROUP');
            $ar->user_isActive = __Config::get('USER_DEFAULT_ACTIVE_STATE');
            $ar->user_password = glz_password(__Request::get('user_password'));
            $ar->user_loginId = $email;
            $ar->user_email = $email;
            $ar->user_dateCreation = new org_glizy_types_DateTime();
            $ar->save();
            $this->changeAction('registrationConfirm');
        }
    }
}