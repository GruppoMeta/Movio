<?php
class org_glizycms_userManager_fe_controllers_user_LostPassword extends org_glizy_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function executeLater($email)
    {
        if ($this->submit && $this->controller->validate()) {
            $ar = org_glizy_ObjectFactory::createModel('org.glizy.models.User');
            if (!$ar->find(array('user_email' => $email))) {
                // utente non trovato
                $this->view->validateAddError(__T('MW_LOSTPASSWORD_ERROR'));
                return false;
            }

            // utente trovato
            // genera una nuova password e la invia per email
            glz_import('org.glizy.helpers.Mail');
            // invia la notifica all'utente
            $subject    = org_glizy_locale_Locale::get('MW_LOSTPASSWORD_EMAIL_SUBJECT');
            $body       = org_glizy_locale_Locale::get('MW_LOSTPASSWORD_EMAIL_BODY');
            $body       = str_replace('##USER##', $email, $body);
            $body       = str_replace('##HOST##', org_glizy_helpers_Link::makeSimpleLink(GLZ_HOST, GLZ_HOST), $body);
            $body       = str_replace('##PASSWORD##', $ar->user_password, $body);
            org_glizy_helpers_Mail::sendEmail(  array('email' => org_glizy_Request::get('email', ''), 'name' => $ar->user_firstName.' '.$ar->user_lastName),
                                                    array('email' => org_glizy_Config::get('SMTP_EMAIL'), 'name' => org_glizy_Config::get('SMTP_SENDER')),
                                                    $subject,
                                                    $body);
            $this->changeAction('lostPasswordConfirm');
        }
    }
}