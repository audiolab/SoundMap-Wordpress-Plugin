<?php

?>


<div <?php post_class('marker-window', $marker_id) ?>>
    <div class="post-title"><h3><a href="<?php echo get_permalink($marker_id);?>"><?php echo get_the_title($marker_id); ?></a></h3></div>
    <div class="post-content">
        <?php echo apply_filters('the_content',get_the_content()) ?>
        <hr>
        <p class="marker-info"><?php echo __('Author', 'soundmap') . ': ' . $info['m_author']; ?></br>
        <?php echo __('Date', 'soundmap') . ': ' . $info['m_date']; ?></p>
        <hr>
        <?php
            add_player_interface($info['m_files'], $marker_id);
        ?>        
        <hr class="clear">
        <div class="marker-info">
        <?php the_tags(__('Tags','soundmap') . ': ', ' | ', '</br>'); ?>
        <?php echo __('Categories','soundmap') . ': '; the_category(' | '); ?>
        </div>
    </div>
 
    
</div>
