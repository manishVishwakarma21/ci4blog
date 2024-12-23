<p>Dear <?= $mail_data['user']->name; ?>

<p>
	We are received a request to reset password for ci4Blog account associated with <i><?= $mail_data['user']->email; ?></i>

	You can reset your password by clicking the button below:
	<br><br>

	<!-- <a href="<?php // base_url($mail_data['actionLink']); ?>" style="color: #d23c88; border-color: #ccc; background-color: #ccc; border-style: solid; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); -webkit-text-size-adjust:none; box-sizing: border-box; padding: 10px;" target="_blank">Reset password</a> -->

	<a href="<?= $mail_data['actionLink']; ?>" style="color: #d23c88; border-color: #ccc; background-color: #ccc; border-style: solid; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); -webkit-text-size-adjust:none; box-sizing: border-box; padding: 10px;" target="_blank">Reset password</a>	

	<br><br>

	<b>NB:</b> This link will still valid within 15 minutes.

	<br><br>
	If you did not request for password reset, please ignore this email.


</p>
