<?php $url = base_url().'reset_password/'.$user_id.'/'.$reset_password_token; ?>
<h1>Reset Your Password</h1>
<p>from: <?php echo $site_name ;?></p>
<p>Dear <?php echo $username;?>, you have just been request to reset your password, please click the link below.</p>

<a href="<?php echo $url ?>">Reset Password</a>
<p>If the link does not work, copy the link below and open it in your browser.</p>
<p><?php echo $url;?></p>

