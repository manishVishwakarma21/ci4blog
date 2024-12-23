<p>Dear <b><?= $mail_data['user']->name; ?></b></p>
<br>
<p>
	Your password on ci4blog system was changed successfully. Here are your new login credentials:
	<br><br>
	<b>Login Id: </b><?= $mail_data['user']->username; ?> or <?= $mail_data['user']->email; ?>
	<br>
	<b>Password: </b><?= $mail_data['new_password']; ?>

</p>
<br><br>
Please, keep your credentials confidentials. Your username and password are your own credentials and 
you should never share with anybody else.
<P>
	ci4Blog will not be liable for any misuse of your username or password.
</P>
<br>
--------------------------------------------------
<p>
	This email was automatically sent by ci4Blog system. Do not reply it.
</p>