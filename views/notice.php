<?php
foreach ($notices as $key => $notice) {
    if (in_array($key, $dismissed_notices) && $this->plugin_screen_hook_suffix != $screen->id) {
        continue;
    }
    ?>
    <div id="message" class="<?php echo $notice['class']; ?>" data-key="<?php echo $key; ?>">
        <p><?php echo $notice['msg']; ?></p>
    </div>
<?php } ?>