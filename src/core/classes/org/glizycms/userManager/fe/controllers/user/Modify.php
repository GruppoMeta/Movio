<?php
class org_glizycms_userManager_fe_controllers_user_Modify extends org_glizy_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function execute()
    {
        if ($this->user->isLogged() && !$this->submit) {
            $ar = org_glizy_ObjectFactory::createModel('org.glizy.models.User');
            $ar->load($this->user->id);
            $values = $ar->getValuesAsArray();
            $values['user_password'] = '';
            __Request::setFromArray($values);
        }
    }

    public function executeLater()
    {
        if ($this->user->isLogged() && $this->submit && $this->controller->validate()) {
            $ar = org_glizy_ObjectFactory::createModel('org.glizy.models.User');
            $ar->load($this->user->id);

            $email = org_glizy_Request::get('user_email', '');
            if ($email != $ar->user_loginId) {
                $ar2 = org_glizy_ObjectFactory::createModel('org.glizy.models.User');
                if ($ar2->find(array('user_loginId' => $email)) && $ar2->user_id!=$ar->user_id) {
                    $this->view->validateAddError('L\'email è già presente nel database, usare un\'altra email');
                    return;
                }
            }

            // TODO migliorare così siamo esposti a problemi di sicurezza
            $fields = $ar->getFields();
            foreach($fields as $k=>$v) {
                if ($k=='user_password') continue;
                if (__Request::exists($k)) {
                    $ar->$k = __Request::get($k);
                }
            }
            $password = __Request::get('user_password');
            if ($password) {
                $ar->user_password = glz_password($password);
            }
            $ar->user_loginId = $email;
            $ar->user_email = $email;
            $ar->save();
            $this->changeAction('modifyConfirm');
        }
    }
}