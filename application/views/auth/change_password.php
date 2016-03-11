<?php if(validation_errors()):?>
    <div class="alert alert-warning" role="alert">
        <?php echo validation_errors(); ?>
    </div>
<?php endif ?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/application/views/auth/message.php';?>
<?php $url = base_url().'change_password/'.$uuid; ?>
<form action="<?php echo $url; ?>" method="POST" class="form-change_password">
    <h2 class="form-change-password-heading">Change Password</h2>

    <?php csrf_token(); ?>
    <label for="currentPassword" class="sr-only">Current Password</label>
    <input type="password" name="currentPassword" id="currentPassword" class="form-control"  value="" placeholder="Current Password" required autofocus>

    <label for="inputPassword" class="sr-only">New Password</label>
    <input type="password" name="inputPassword" id="inputPassword" class="form-control"  value="" placeholder="New Password" required autofocus>

    <label for="inputPassword2" class="sr-only">Password Confirmation</label>
    <input type="password" name="inputPassword2" id="inputPassword2" class="form-control"  value="" placeholder="Password confirmation" required>

    <button class="btn btn-lg btn-primary btn-block" type="submit">Change Password</button>
</form>
