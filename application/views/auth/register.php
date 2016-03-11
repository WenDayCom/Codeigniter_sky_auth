<?php
    $this->lang->load('sky_auth');
    $data = array(
      'name' => $this->lang->line('name'),
      'email' => $this->lang->line('email'),
      'password' => $this->lang->line('password'),
      'password2' => $this->lang->line('password2'),
      'register' => $this->lang->line('register'),
    );
?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/application/views/auth/message.php';?>

<?php if(validation_errors()):?>
    <div class="alert alert-warning" role="alert">
        <?php echo validation_errors(); ?>
    </div>
<?php endif ?>



<form action="<?php echo site_url("auth/register"); ?>" method="POST" class="form-signup">
    <h2 class="form-signup-heading"><?php echo $data['register']; ?></h2>

    <?php csrf_token(); ?>


    <label for="inputName" class="sr-only"><?php echo $data['name']; ?></label>
    <input type="text" name="inputName" id="inputName" value="<?php echo set_value('inputName'); ?>" class="form-control" placeholder="<?php echo $data['name']; ?>" required autofocus>

    <label for="inputEmail" class="sr-only"><?php echo $data['email']; ?></label>
    <input type="email" name="inputEmail" id="inputEmail" value="<?php echo set_value('inputEmail'); ?>" class="form-control" placeholder="<?php echo $data['email']; ?>" required autofocus>
    <label for="inputPassword" class="sr-only"><?php echo $data['password']; ?></label>
    <input type="password" name="inputPassword" id="inputPassword" class="form-control"  value="" placeholder="<?php echo $data['password']; ?>" required>

    <label for="inputPassword2" class="sr-only"><?php echo $data['password2']; ?></label>
    <input type="password" name="inputPassword2" id="inputPassword2" class="form-control"  value="" placeholder="<?php echo $data['password2']; ?>" required>

    <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $data['register']; ?></button>
</form>
