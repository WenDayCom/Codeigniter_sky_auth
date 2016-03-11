<?php if(validation_errors()):?>
    <div class="alert alert-warning" role="alert">
        <?php echo validation_errors(); ?>
    </div>
<?php endif ?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/application/views/auth/message.php';?>

<form action="<?php echo site_url("auth/forgot_password");?>" method="post" class="form-forgot-password">
    <h2 class="form-forgot-password-heading">Reset Password</h2>
    <?php csrf_token(); ?>
    <p>Enter your email below and we'll send you a link to reset your password.</p>
    <label for="inputEmail" class="sr-only">Email address</label>
    <input type="email" name="inputEmail" id="inputEmail"  class="form-control"
           value="<?php set_value('inputEmail'); ?>" placeholder="Email address" required autofocus>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Send</button>
</form>