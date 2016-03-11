<?php
if(isset($_message)):
    if(is_array($_message)):?>
        <?php foreach($_message as $msg): ?>
            <div class="alert alert-warning" role="alert">
                <?php echo $msg; ?>
            </div>
        <?php endforeach?>
    <?php else:?>
        <div class="alert alert-warning" role="alert">
            <?php echo $_message; ?>
        </div>
    <?php endif?>
<?php endif ?>
