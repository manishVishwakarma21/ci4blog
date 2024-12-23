<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


/** Send Email Function Using PHPMailer Library */

if(!function_exists('sendEmail')){  //yadi 'sendEmail' naam ka nahi hai tabhi ye function create ho

	function sendEmail($mailConfig){


		require 'PHPMailer/src/Exception.php';
		require 'PHPMailer/src/PHPMailer.php';
		require 'PHPMailer/src/SMTP.php';


		$mail = new PHPMailer(true);

		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		$mail->Host = env('EMAIL_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = env('EMAIL_USERNAME');
		$mail->Password = env('EMAIL_PASSWORD');
		$mail->SMTPSecure = env('EMAIL_ENCRYPTION');
		$mail->Port = env('EMAIL_PORT');
		$mail->setFrom($mailConfig['mail_from_email'],$mailConfig['mail_from_name']);
		//$mail->addAddress($mailConfig['mail_recipient_email'],$mailConfig['mail_recipient_name']);
		$mail->addAddress('kumarmanish8821@gmail.com',$mailConfig['mail_recipient_name']);
		$mail->isHTML(true);
		$mail->Subject = $mailConfig['mail_subject'];
		$mail->Body = $mailConfig['mail_body'];

		//echo $mail->Body;
		//exit;

		if($mail->send()){

			return true;

		}else{

			return false;

		}


	}


}











