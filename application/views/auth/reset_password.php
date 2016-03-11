<?php if(validation_errors()):?>
    <div class="alert alert-warning" role="alert">
        <?php echo validation_errors(); ?>
    </div>
<?php endif ?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/application/views/auth/message.php';?>
<?php $url = base_url().'reset_password/'.$uuid.'/'.$token; ?>
<form action="<?php echo $url; ?>" method="POST" class="form-reset_password">
    <h2 class="form-signup-heading">Reset Password</h2>

    <?php csrf_token(); ?>

    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" name="inputPassword" id="inputPassword" class="form-control"  value="" placeholder="Password" required autofocus>

    <label for="inputPassword2" class="sr-only">Password Confirmation</label>
    <input type="password" name="inputPassword2" id="inputPassword2" class="form-control"  value="" placeholder="Password confirmation" required>

    <button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>
</form>
