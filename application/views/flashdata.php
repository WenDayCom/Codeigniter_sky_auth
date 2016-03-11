<?php if($this->session->flashdata('alert')):?>
    <div class="alert alert-warning" role="alert">
        <?php echo $this->session->flashdata('alert'); ?>
    </div>
<?php endif ?>