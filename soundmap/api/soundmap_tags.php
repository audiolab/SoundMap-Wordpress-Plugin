<?php

function get_the_latitude($id){
    return get_post_meta($id,'soundmap_marker_lat', TRUE);
}

function get_the_longitude($id){
    return get_post_meta($id,'soundmap_marker_lng', TRUE);
}

function the_map($css_id = 'map_canvas', $all_markers = FALSE, $options = array()){
    
    $_soundmap = array();
    global $soundmap;
    
    $soundmap = wp_parse_args($options, $soundmap);    // cojo las opciones para poder montar el mapa, por si algo cambia...
    
    ?>
    
    <div class="<?php echo $css_id ?>">
    
    </div>
    
    
    <?php
    if ($all_markers){
        //cargo todos los marcadores
        $soundmap['markers'] = 'all';
    }else{
        global $posts;  //Array with the posts to show
        $list = _soundmap_postsArrayToList($posts);
        
        if (!is_array($list))
            return;
        
        $soundmap['markers'] = $list;
    }
    
}

function the_marker_info($pre = '<ul><li class="post-info">',$sep = '</li><li>',$after = '</li></ul>'){
    global $post;
    $marker_id = $post->ID;
    
    if ($post->post_type != "marker")
        return;
    
    $author = get_post_meta($marker_id, 'soundmap_marker_author', TRUE);
    $date = get_post_meta($marker_id, 'soundmap_marker_date', TRUE);
    echo $pre;
    echo $author;
    echo $sep;
    echo $date;
    echo $after;
    
}

function get_marker_author($id){
    
    return get_post_meta($id, 'soundmap_marker_author', TRUE);

}


function get_marker_date($id){    
    
    return get_post_meta($id, 'soundmap_marker_date', TRUE);

}

function the_player($marker_id){
    
    $data = array();
    $files = get_post_meta($marker_id, 'soundmap_attachments_id', FALSE);
    foreach ($files as $key => $value){
        $file = array();
        $att = get_post($value);
        $file['id'] = $value;
        $file['fileURI'] = wp_get_attachment_url($value);
        $file['filePath'] = get_attached_file($value);
        $file['info'] = soundmap_get_id3info($file['filePath']);
        $file['name'] = $att->post_name;
        $data['m_files'][] = $file;   
    }
    add_player_interface($data['m_files'], $marker_id);
}


function insert_upload_form($text){
	
	if (function_exists("qtrans_init")){
		//We are using multilanguage
		global $q_config;
		$lang=$q_config['language'];
		$dir = WP_PLUGIN_URL . "/soundmap/modules/module.soundmap.upload.php?lang=$lang&TB_iframe=true&width=960&height=550";
	}else{
		$dir = WP_PLUGIN_URL . "/soundmap/modules/module.soundmap.upload.php?TB_iframe=true&width=960&height=550";
	}
	$t="";
	$title=__("Add new recording","soundmap");
	$t .="<a class=\"thickbox\" title=\"$title\" href=\"$dir\">$text</a>";
	echo $t;
}

function soundmap_rss_enclosure(){
    
    $id = get_the_ID();
    
    $files = get_post_meta($id, 'soundmap_attachments_id', FALSE);
    
    foreach ( (array) $files as $key => $value) {        
        
        $fileURI= wp_get_attachment_url($value);        
        $info= soundmap_get_id3info($file['filePath']);
        echo apply_filters('rss_enclosure', '<enclosure url="' . trim(htmlspecialchars($fileURI)) . '" length="' . trim($info['playtime_string']) . '" type="audio/mpeg" />' . "\n");
            
    }
}


function add_player_interface($files, $id){
    
    if(!is_array($files) || (count($files)==0))
        return;

    global $soundmap_Player;
    
    $insert_content = $soundmap_Player->print_audio_content($files, $id);
    echo $insert_content;
    
}