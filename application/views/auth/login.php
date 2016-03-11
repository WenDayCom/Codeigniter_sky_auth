<?php if(validation_errors()):?>
    <div class="alert alert-warning" role="alert">
        <?php echo validation_errors(); ?>
    </div>
<?php endif ?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/application/views/auth/message.php';?>
<form action="<?php echo site_url("auth/login"); ?>" method="POST" class="form-signin">
    <h2 class="form-signin-heading">Please sign in</h2>

    <?php csrf_token(); ?>
    <label for="inputEmail" class="sr-only">Email address</label>
    <input type="email" name="inputEmail" id="inputEmail" value="<?php set_value('inputEmail'); ?>" class="form-control"
           placeholder="Email address" required autofocus>

    <label for="inputPassword" class="sr-only">Password</label>

    <input type="password" name="inputPassword" id="inputPassword" class="form-control"
           value="<?php set_value('inputPassword'); ?>" placeholder="Password" required>

    <div class="checkbox">
        <label>
            <input type="checkbox" name="rememberMe" value="remember-me"> Remember me
        </label>
    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    <a href="<?php echo site_url("auth/forgot_password");?>">Forgot Password?</a>
</form>


