<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?php echo base_url(); ?>">Foobar</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Users<span class="sr-only">(current)</span></a></li>
                <?php if(isset($change_password)): ?>
                <li><a href="<?php echo base_url().'change_password/'. $change_password;?>">Change Password</a></li>
                <?php endif; ?>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">查看状态</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="#">Separated link</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                </li>

            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php if(isset($user_name)): ?>
                <li><a href="#"><?php echo $user_name; ?></a></li>
                <li><a href="<?php echo base_url('auth/logout'); ?>">Log Out</a></li>
                <?php else: ?>
                <li><a href="<?php echo base_url('auth/login'); ?>">Log In</a></li>
                <li><a href="<?php echo base_url('auth/register'); ?>">Sign Up</a></li>
                <?php endif ?>
            </ul>

        </div>

    </div>
</nav>
<div class="container content">