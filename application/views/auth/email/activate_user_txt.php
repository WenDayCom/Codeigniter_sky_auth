<?php $url = base_url().'activate_user/'.$user_id.'/'.$activate_token; ?>
<h1>Activate Your Account</h1>
<p>from: <?php echo $website_name ;?></p>
<p>Dear <?php echo $username;?>, click the following link to activate you account.</p>

<a href="<?php echo $url ?>">Activate</a>
<p>If the link does not work, copy the link below and open it in your browser.</p>
<p><?php echo $url;?></p>
