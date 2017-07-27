<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_Mail extends GlizyObject
{
	/**
     * @param       $to
     * @param       $from
     * @param       $subject
     * @param       $body
     * @param array $attach
     * @param array $cc
     * @param array $bcc
     * @param null  $embedDir
     *
     * @return array
     */
    static function sendEmail($to, $from, $subject, $body, $attach=array(), $cc=array(), $bcc=array(), $embedDir=NULL, $templateHeader='', $templateFooter='')
	{
		try
        {
			require_once(GLZ_LIBS_DIR."phpmailer/class.phpmailer.php");
            $host = org_glizy_Config::get('SMTP_HOST');

            /** @var PHPMailer $mail */
			$mail = new PHPMailer();
            $mail->CharSet = __Config::get('CHARSET');
			if ($host!='')
			{
				$mail->IsSMTP();
				$mail->Host = $host;

                $port = org_glizy_Config::get('SMTP_PORT');
                $username = org_glizy_Config::get('SMTP_USER');
                $smtpSecure = org_glizy_Config::get('SMTP_SECURE');

				if ($username!='')
				{
                    $mail->SMTPAuth = true;
					$mail->Username = $username;
					$mail->Password = org_glizy_Config::get('SMTP_PSW');
				}

				if ($port) {
					$mail->Port = $port;
				}
                if ($smtpSecure) {
                    $mail->SMTPSecure = $smtpSecure;
                }
			}

			$mail->From 	= trim($from['email']);
			$mail->FromName = trim($from['name']);
			$mail->AddAddress(trim($to['email']), trim($to['name']));
			$mail->Subject 	= $subject;

            if ($cc)
			{
				if ( !is_array( $cc ) ) $cc = array( $cc );
				foreach( $cc as $v )
				{
                    if ($v) $mail->AddCC($v);
				}
			}

            if ($bcc)
			{
				if ( !is_array( $bcc ) ) $bcc = array( $bcc );
				foreach( $bcc as $v )
				{
                    if ($v) $mail->AddBCC($v);
				}
			}

			$bodyTxt = $body;
			$bodyTxt = str_replace('<br>', "\r\n", $bodyTxt);
			$bodyTxt = str_replace('<br />', "\r\n", $bodyTxt);
			$bodyTxt = str_replace('</p>', "\r\n\r\n", $bodyTxt);
			$bodyTxt = strip_tags($bodyTxt);
			$bodyTxt = html_entity_decode($bodyTxt);

			if (!is_null($attach)){
				foreach ($attach as $a)
				{
					$mail->AddAttachment($a['fileName'], $a['originalFileName']);
				}
			}

			if (!is_null($embedDir))
			{
                $processedImage = array();
                $embImage = 0;
				// controlla se c'è da fare l'embed delle immagini
				preg_match_all('/<img[^>]*src=("|\')([^("|\')]*)("|\')/i', $body, $inlineImages);
				if (count($inlineImages) && count($inlineImages[2]))
				{
					for ($i=0;$i<count($inlineImages[2]);$i++)
					{
						if (in_array($inlineImages[2][$i], $processedImage)) continue;
						$processedImage[] = $inlineImages[2][$i];

						$embImage++;
						$imageType = explode('.', $inlineImages[2][$i]);
						$code = str_pad($embImage, 3, '0', STR_PAD_LEFT);
						$mail->AddEmbeddedImage($embedDir.$inlineImages[2][$i], $code, $inlineImages[2][$i], "base64", "image/".$imageType[count($imageType)-1]);
						$body = str_replace($inlineImages[2][$i], 'cid:'.$code, $body);
					}
				}

				preg_match_all('/<td[^>]*background=("|\')([^("|\')]*)("|\')/i', $body, $inlineImages);
				if (count($inlineImages) && count($inlineImages[2]))
				{
					for ($i=0;$i<count($inlineImages[2]);$i++)
					{
						if (in_array($inlineImages[2][$i], $processedImage)) continue;
						$processedImage[] = $inlineImages[2][$i];

						$embImage++;
						$imageType = explode('.', $inlineImages[2][$i]);
						$code = str_pad($embImage, 3, '0', STR_PAD_LEFT);
						$mail->AddEmbeddedImage($embedDir.$inlineImages[2][$i], $code, $inlineImages[2][$i], "base64", "image/".$imageType[count($imageType)-1]);
						$body = str_replace($inlineImages[2][$i], 'cid:'.$code, $body);
					}
				}
			}

			$mail->Body    = $templateHeader.$body.$templateFooter;
			$mail->AltBody = $bodyTxt;

            $r = array('status' => $mail->Send(),
                       'error' => $mail->ErrorInfo);

        }
        catch (Exception $e)
        {
            $r = array('status' => false,
                       'error' => $e->getMessage());
		}

        if (isset($mail)) {
            $smtp_host = $mail->Host;
            $smtp_port = $mail->Port;
        } else {
            $smtp_host = '';
            $smtp_port = '';
        }

        $eventInfo = array('type' => GLZ_LOG_EVENT, 'data' => array(
                                    'level' => $r['status'] ? GLZ_LOG_DEBUG : GLZ_LOG_ERROR,
                                    'group' => 'glizy.helpers.mail',
            						'message' => array('result' => $r,
                                            'to' => $to,
                                            'from' => $from,
                                            'subject' => $subject,
                                            'body' => $body,
                                            'attach' => $attach,
                                            'cc' => $cc,
                              'bcc' => $bcc,
                              'smtp_host' => $smtp_host,
                              'smtp_port' => $smtp_port
            )));

            $evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', null, $eventInfo );
            org_glizy_events_EventDispatcher::dispatchEvent( $evt );

        return $r;
	}

    /**
     * @param string $fileName
     * @param array $info
     * @param string $htmlTemplateHeader
     * @param string $htmlTemplateFooter
     * @param string $templatePath
     * @return array
     */
    static function sendEmailFromTemplate( $fileName, $info, $htmlTemplateHeader = '', $htmlTemplateFooter = '', $templatePath = '')
    {
        /** @var org_glizy_application_Application $application */
        $application  = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $templatePath = $templatePath ? $templatePath : __Paths::get( 'APPLICATION_STATIC' ) . '/templatesEmail/'. $application->getLanguage() .'/';
        $emailText    = file_get_contents( $templatePath.$fileName.'.txt' );
        $emailText    = explode( "\n", $emailText );
        $emailTitle   = array_shift( $emailText );
        $emailBody    = implode( "\n<br />", $emailText );
        foreach( $info as $k => $v )
        {
            $emailBody  = str_replace('##'.$k.'##', $v, $emailBody);
            $emailTitle = str_replace('##'.$k.'##', $v, $emailTitle);
        }

        if ($htmlTemplateHeader && $htmlTemplateFooter) {
            $emailBody = file_get_contents($templatePath.$htmlTemplateHeader).
                $emailBody.
                file_get_contents($templatePath . $htmlTemplateFooter);
        }

        $sender = isset($info['SENDER']) ? $info['SENDER'] : array(
                                'email' => org_glizy_Config::get('SMTP_EMAIL'),
                                'name' => org_glizy_Config::get('SMTP_SENDER'));

        return org_glizy_helpers_Mail::sendEmail(
            array('email' => $info['EMAIL'], 'name' => $info['FIRST_NAME'].' '.$info['LAST_NAME'] ),
            $sender,
            $emailTitle,
            $emailBody,
            $info['ATTACHS'],
            $info['CC'],
            $info['BCC']
        );
    }

    /**
     * @return array
     */
    static function getEmailInfoStructure()
	{
		$info = array();
		$info['EMAIL'] = '';
		$info['FIRST_NAME'] = '';
		$info['LAST_NAME'] = '';
		$info['USER'] = '';
		$info['PASSWORD'] = '';
		$info['URL_SITE'] = org_glizy_helpers_Link::makeSimpleLink(GLZ_HOST, GLZ_HOST);
		$info['HOST'] = GLZ_HOST;
		$info['ATTACHS'] = array();
		$info['BCC'] = array();
		$info['CC'] = array();
		return $info;
	}
}