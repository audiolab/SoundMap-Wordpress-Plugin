<?php

?>


<div class="<?php post_class('', $marker_id) ?>">
    <div class="post-title">
        <?php echo get_the_title($marker_id); ?>
    </div>
    <div class="post-content">
        <?php echo apply_filters('the_content',get_the_content()) ?>
        <hr>
        <p><?php echo __('Author', 'soundmap') . ': ' . $info['m_author']; ?></p>
        <p><?php echo __('Date', 'soundmap') . ': ' . $info['m_date']; ?></p>
        <hr>
        <?php
            add_player_interface($info['m_files'], $marker_id);
        ?>
        <hr class="clear">
    </div>
    
</div>
