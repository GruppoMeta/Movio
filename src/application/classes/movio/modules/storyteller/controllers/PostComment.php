<?php
class movio_modules_storyteller_controllers_PostComment extends org_glizy_mvc_core_Command
{
    public function execute($hash, $authorName, $authorEmail, $text, $captcha)
    {
        $valid = true;
        $error = '';
        if (!($hash && $authorName && $authorEmail && $text && $captcha)) {
            $valid = false;
            $error = 'Wrong input';
        }

        if ($valid && !filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
            $valid = false;
            $error = 'Wrong email';
        }

        $sessionEx = new org_glizy_SessionEx($this->application->getPageId());
        $verCaptcha = $sessionEx->get('captcha'.$hash);
        if ($valid && $verCaptcha!=$captcha) {
            $valid = false;
            $error = 'Wrong verify code';
        }

        if ($valid) {
            $ar = org_glizy_ObjectFactory::createModel('movio.modules.storyteller.models.Comment');
            $ar->hash = $hash;
            $ar->menuId = $this->application->getPageId();
            $ar->authorName = $authorName;
            $ar->authorEmail = $authorEmail;
            $ar->approved = 1;
            $ar->date = new org_glizy_types_Date();
            $ar->text = nl2br($text);
            $ar->save();

            $sessionEx->remove($hash.'_author');
            $sessionEx->remove($hash.'_email');
            $sessionEx->remove($hash.'_text');
            $sessionEx->remove($hash.'_error');
            $destHash = '#comments_'.$hash;
        } else {
            $sessionEx->set($hash.'_author', $authorName);
            $sessionEx->set($hash.'_email', $authorEmail);
            $sessionEx->set($hash.'_text', $text);
            $sessionEx->set($hash.'_error', $error);
            $destHash = '#form_'.$hash;
        }

        $this->goHere(null, $destHash);
    }
}
