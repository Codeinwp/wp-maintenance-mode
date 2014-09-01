<?php foreach ($notices as $notice) { ?>
    <div id="message" class="<?php echo $notice['class']; ?> fade">
        <p><?php echo $notice['msg']; ?></p>
    </div>
<?php } ?>