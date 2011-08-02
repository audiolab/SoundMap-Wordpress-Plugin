<?php


/**
 * @package Soundmap
 */
/*
Plugin Name: Soundmap
Plugin URI: http://www.soinumapa.net
Description: New version of the Soinumapa Plugin for creating sound maps
Version: 0.4
Author: Xavier Balderas
Author URI: http://www.xavierbalderas.com  
License: GPLv2 or later
*/

require_once (WP_PLUGIN_DIR . "/soundmap/api/getid3.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.base.player.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.audioplayer.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.haikuplayer.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.wp-audio-gallery-player.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.jplayer.php");


function soundmap_init() {
    
    global $soundmap;
    
    $soundmap = array();
    $soundmap['on_page'] = FALSE;
    
    soundmap_register_scripts();
    
    $labels = array(
        'name' => __('Marks', 'soundmap'),
        'singular_name' => __('Mark', 'soundmap'),
        'add_new' => _x('Add New', 'marker', 'soundmap'),
        'add_new_item' => __('Add New Marker'),
        'edit_item' => __('Edit Marker'),
        'new_item' => __('New Marker'),
        'all_items' => __('All Markers'),
        'view_item' => __('View Marker'),
        'search_items' => __('Search Markers'),
        'not_found' =>  __('No markers found'),
        'not_found_in_trash' => __('No markers found in Trash'), 
        'parent_item_colon' => '',
        'menu_name' => 'Markers'
  );
  $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true, 
        'show_in_menu' => true, 
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true, 
        'hierarchical' => false,
        'menu_position' => 5,
        'register_meta_box_cb' => 'soundmap_metaboxes_register_callback',
        'supports' => array('title','editor','thumbnail')
  ); 
  register_post_type('marker',$args);
  register_taxonomy_for_object_type('category', 'marker');
  register_taxonomy_for_object_type('post_tag', 'marker');
  
  soundmap_check_map_page();
  
  soundmap_create_player_instance();
  
  if(!is_admin()){
    soundmap_register_wp_scripts();    
  }
}


function soundmap_create_player_instance(){
    
    global $soundmap_Player;
    
    $plugins = get_option( 'active_plugins', array() );
    
    if (!is_array($plugins))
        return;
    
    if(in_array('audio-player/audio-player.php', $plugins) && class_exists('sm_AudioPlayer')){
        //Audio player active
        $soundmap_Player = new sm_AudioPlayer();
        return;
    }
    
    if(in_array('haiku-minimalist-audio-player/haiku-player.php', $plugins) && class_exists('sm_HaikuPlayer')){
        //Audio player active
        $soundmap_Player = new sm_HaikuPlayer();
        return;
    }
    
    if(in_array('wp-audio-gallery-playlist/wp-audio-gallery-playlist.php', $plugins) && class_exists('sm_AudioGallery_Player')){
        //Audio player active
        $soundmap_Player = new sm_AudioGallery_Player();
        return;
    }
    
    if(in_array('jplayer/jplayer.php', $plugins) && class_exists('sm_jPlayer')){
        //Audio player active
        $soundmap_Player = new sm_jPlayer();
        return;
    }    
        
}

function soundmap_check_map_page(){
    global $soundmap;
    $uri = get_uri();
    if (end($uri) == 'map/'){        
            $soundmap['on_page'] = TRUE;
    }
}

function soundmap_register_scripts(){
    //Register google maps.
    wp_register_script('google-maps','http://maps.google.com/maps/api/js?libraries=geometry&sensor=true');
    wp_register_script('jquery-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js');
    wp_register_script('jquery-google-maps', WP_PLUGIN_URL . '/soundmap/js/jquery.ui.map.js', array('jquery', 'jquery-ui', 'google-maps'));
}



function soundmap_metaboxes_register_callback(){
    add_meta_box('soundmap-map', __("Place the Marker", 'soundmap'), 'soundmap_map_meta_box', 'marker', 'normal', 'high');
    add_meta_box('soundmap-sound-file', __("Add a sound file", 'soundmap'), 'soundmap_upload_file_meta_box', 'marker', 'normal', 'high');
    add_meta_box('soundmap-sound-info', __("Add info for the marker", 'soundmap'), 'soundmap_info_meta_box', 'marker', 'side', 'high');
    add_meta_box('soundmap-sound-attachments', __("Sound files attached.", 'soundmap'), 'soundmap_attachments_meta_box', 'marker', 'side', 'high');
}

function soundmap_info_meta_box(){
    
    global $post;
    
    $soundmap_author = get_post_meta($post->ID, 'soundmap_marker_author', TRUE);
    $soundmap_date = get_post_meta($post->ID, 'soundmap_marker_date', TRUE);
    
   echo '<label for="soundmap-marker-author">' . __('Author', 'soundmap') . ': </label>';
   echo '<input type="text" name="soundmap_marker_author" id="soundmap-marker-author" value="' . $soundmap_author . '">';
   echo '<input type="hidden" name="soundmap_marker_date" id="soundmap-marker-date" value="'. $soundmap_date.'">';
    echo '<p id="soundmap-marker-datepicker"></p>';
}

function soundmap_attachments_meta_box(){
    global $post;
    
    $files = get_post_meta($post->ID, 'soundmap_attachments_id', FALSE);

    
    echo '<div id="soundmap-attachments">';
    echo '<table cellspacing="0" cellpadding="0" id="sound-att-table">';
    echo '<tr><th class="soundmap-att-left">' . __('Filename', 'soundmap') . '</th><th>' . __('Length', 'soundmap') .'</th></tr>';
    $rows = '';
    $fields = '';
    
    foreach ($files as $key => $value){
        
        $att = get_post($value);
        $f_name = $att->post_name;
        $f_info = soundmap_get_id3info(get_attached_file($value));
        $rows .= '<tr><td class="soundmap-att-left">' . $f_name . '</td><td>' . $f_info['play_time'] . '</td></tr>';
        $fields .='<input type="hidden" name="soundmap_attachments_id[]" value="' . $value . '">';        

    }
    
    echo $rows;
    echo '</table>';
    echo $fields;
    echo '</div>';
}

function soundmap_map_meta_box(){
        
    global $post;
    
    $soundmap_lat = get_post_meta($post->ID, 'soundmap_marker_lat', TRUE);
    $soundmap_lng = get_post_meta($post->ID, 'soundmap_marker_lng', TRUE);
    
   echo '<input type="hidden" name="soundmap_map_noncename" id="soundmap_map_noncename" value="' .
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
   echo '<div id="map_canvas"></div>';
   echo '<label for="soundmap-marker-lat">' . __('Latitud', 'soundmap') . '</label>';
   echo '<input type="text" name="soundmap_marker_lat" id="soundmap-marker-lat" value = "' . $soundmap_lat . '">';
   echo '<label for="soundmap-marker-lng">' . __('Longitud', 'soundmap') . '</label>';
   echo '<input type="text" name="soundmap_marker_lng" id="soundmap-marker-lng" value = "'. $soundmap_lng . '">';
   
}

function soundmap_upload_file_meta_box(){

    echo '<form id="uploader-form" method="post" action="dump.php"><div id="uploader"></div></form> ';
}

function soundmap_rewrite_flush() {
  soundmap_init();
  flush_rewrite_rules();
}

function soundmap_register_admin_scripts() {
    wp_register_script('soundmap-admin', WP_PLUGIN_URL . '/soundmap/js/soundmap-admin.js', array('jquery-google-maps', 'jquery-plupload', 'jquery-datepicker'));
    wp_register_style("soundmap-admin", WP_PLUGIN_URL . '/soundmap/css/soundmap-admin.css', array('plupload-style', 'jquery-datepicker'));
    
    //Register PLUPLOAD
    wp_register_style('plupload-style',WP_PLUGIN_URL . '/soundmap/css/jquery.plupload.queue.css');
    wp_register_script('plupload', WP_PLUGIN_URL . '/soundmap/js/plupload/plupload.full.js');
    wp_register_script('jquery-plupload', WP_PLUGIN_URL . '/soundmap/js/plupload/jquery.plupload.queue.js', array('plupload'));
    //Register DATEPICKER
    wp_register_script('jquery-datepicker', WP_PLUGIN_URL . '/soundmap/js/jquery.datepicker.js', array('jquery'));
    wp_register_style('jquery-datepicker', WP_PLUGIN_URL . '/soundmap/css/jquery.datepicker.css');    
}

function soundmap_register_wp_scripts() {
    wp_register_script('soundmap',  WP_PLUGIN_URL . '/soundmap/js/soundmap-wp.js', array('jquery-google-maps'));
}

function soundmap_admin_enqueue_scripts() {
    wp_enqueue_script('soundmap-admin');
    wp_enqueue_style('soundmap-admin');
    
    $params = array();
    $params['plugin_url'] = WP_PLUGIN_URL . '/soundmap/';
    
    wp_localize_script('soundmap-admin','WP_Params',$params);
}

function soundmap_wp_enqueue_scripts() {
    wp_enqueue_script('soundmap');

    wp_localize_script( 'soundmap', 'WP_Params', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

function soundmap_save_options(){
    
}

function soundmap_load_options(){
    
}

function soundmap_save_post($post_id) {
    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;
    
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (isset($_POST['soundmap_map_noncename'])){
        if ( !wp_verify_nonce( $_POST['soundmap_map_noncename'], plugin_basename( __FILE__ ) ) )
            return;        
    }else{
        return;
    }
    
    // Check permissions
    if ( 'marker' == $_POST['post_type'] ) 
    {
      if ( !current_user_can( 'edit_post', $post_id ) )
          return;
    }
    
    $soundmark_lat = $_POST['soundmap_marker_lat'];
    $soundmark_lng = $_POST['soundmap_marker_lng'];
    $soundmark_author = $_POST['soundmap_marker_author'];
    $soundmark_date = $_POST['soundmap_marker_date'];
    
    add_post_meta($post_id, 'soundmap_marker_lat', $soundmark_lat, TRUE);
    add_post_meta($post_id, 'soundmap_marker_lng', $soundmark_lng, TRUE);
    add_post_meta($post_id, 'soundmap_marker_author', $soundmark_author, TRUE);
    add_post_meta($post_id, 'soundmap_marker_date', $soundmark_date, TRUE);
    

    //before searching on all the $_POST array, let's take a look if there is any upload first!
    if(isset($_POST['soundmap_attachments_id'])){
        $files = $_POST['soundmap_attachments_id'];
        delete_post_meta($post_id, 'soundmap_attachments_id');
        foreach ($files as $key => $value){
            add_post_meta($post_id, 'soundmap_attachments_id', $value);
            soundmap_attach_file($value, $post_id); 
        };
    };
}

function soundmap_JSON_load_markers () {
    $query = new WP_Query(array('post_type' => 'marker'));
    $markers = array();
    
    if ( !$query->have_posts() )
	die();
    $posts = $query->posts;
    foreach($posts as $post){
        $post_id = $post->ID;
        $m_lat = get_post_meta($post_id,'soundmap_marker_lat', TRUE);
        $m_lng = get_post_meta($post_id,'soundmap_marker_lng', TRUE);
        $title = get_the_title ($post_id);
        $mark = array(
            'lat' => $m_lat,
            'lng' => $m_lng,
            'id' => $post_id,
            'title' => $title
            );
        $markers[] = $mark;
    }
    echo json_encode($markers);
    die();
    
}

function soundmap_ajax_file_uploaded_callback() {

    if (!isset($_REQUEST['file_name']))
        die();
    
    $rtn = array();
    $rtn['error'] = "";

    $file_data = $_REQUEST['file_name'];
    
    if ($file_data['status'] != 5)
        die();
        
    $fileName = sanitize_file_name($file_data['name']);

    //Check the directory.
    $months_folders=get_option( 'uploads_use_yearmonth_folders' );
    $wud = wp_upload_dir();
    if ($wud['error']){
        $rtn['error'] = $wud['error'];
        echo json_encode ($rtn);
        die();
    }
    $targetDir = $wud['path'];
    $targetURL = $wud['url'];
    //check if the file exists.
    if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
	$ext = strrpos($fileName, '.');
	$fileName_a = substr($fileName, 0, $ext);
	$fileName_b = substr($fileName, $ext);

	$count = 1;
	while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
		$count++;

	$fileName = $fileName_a . '_' . $count . $fileName_b;
    }
    $tempDir = ini_get("upload_tmp_dir");
    //move it.
    $fileDir = $targetDir . DIRECTORY_SEPARATOR . $fileName;
    $fileURL = $targetURL . DIRECTORY_SEPARATOR . $fileName;
    if(!rename($tempDir . DIRECTORY_SEPARATOR . $file_data['target_name'], $fileDir)){
        $rtn['error'] = __('Error moving the file.','soundmap');
        echo json_encode($rtn);
        die();
    }
    
    $fileInfo = soundmap_get_id3info($fileDir);
    if(!$sound_attach_id = soundmap_add_media_attachment($fileDir, $fileURL))
        die();
        
    
    $rtn['attachment'] = $sound_attach_id;
    $rtn['length'] = $fileInfo['play_time'];
    $rtn['fileName'] = $fileName;
    
    echo json_encode($rtn);    
    die();
}

function soundmap_add_media_attachment($file, $fileURL){
    
    $wp_filetype = wp_check_filetype(basename($file), null );
    $attachment = array(
       'post_mime_type' => $wp_filetype['type'],
       'post_title' => preg_replace('/\.[^.]+$/', '', basename($file)),
       'post_content' => '',
       'post_status' => 'inherit',
       'guid' => $fileURL
    );
    $attach_id = wp_insert_attachment( $attachment, $file);
    // you must first include the image.php file
    // for the function wp_generate_attachment_metadata() to work
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    return $attach_id;
}

function soundmap_get_id3info($file){
    $getID3 = new getID3;
    $fileInfo = $getID3->analyze($file);
    $result = array();
    $result['play_time'] = $fileInfo['playtime_string'];    
    return $result;
}

function soundmap_attach_file($att, $post_id){
    global $wpdb;
    return $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID IN ( $att )", $post_id ) ); 
}

function soundmap_template_redirect(){
    global $soundmap;

    if ($soundmap['on_page']){        
        if ($theme=soundmap_get_template_include('map'))            
            include ($theme);            
        exit();
    }    
}

function soundmap_get_template_include($templ){
    if (!$templ)
        return FALSE;
    $theme_file = TEMPLATEPATH . DIRECTORY_SEPARATOR . $templ . '.php';
    $plugin_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'soundmap' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'theme_' . $templ . '.php';
    
    if (file_exists($theme_file))
        return $theme_file;
    
    if(file_exists($plugin_file))
        return $plugin_file;
    
    return FALSE;

}

function soundmap_pre_get_posts( $query ) {
    global $soundmap;    
    if ($soundmap['on_page'])
        $query->parse_query( array('post_type'=>'marker') );
        
    return $query;
}

function soundmap_load_infowindow(){
    $marker_id = $_REQUEST['marker'];
    $marker = get_post( $marker_id);
    global $post;
    $post = $marker;
    setup_postdata($marker);
    if(!$marker)
        die();
        
    
    $info['m_author'] = get_post_meta($marker_id, 'soundmap_marker_author', TRUE);
    $info['m_date'] = get_post_meta($marker_id, 'soundmap_marker_date', TRUE);
    $files = get_post_meta($marker_id, 'soundmap_attachments_id', FALSE);
    foreach ($files as $key => $value){
        $file = array();
        $file['id'] = $value;
        $file['fileURI'] = wp_get_attachment_url($value);
        $file['filePath'] = get_attached_file($value);
        $file['info'] = soundmap_get_id3info($file['filePath']);
        $info['m_files'][] = $file;   
    }
    if ($theme=soundmap_get_template_include('window')) 
        include ($theme);            
    die();
}

add_action('template_redirect', 'soundmap_template_redirect');
add_action('init', 'soundmap_init');
add_action('admin_enqueue_scripts', 'soundmap_admin_enqueue_scripts');
add_action('wp_enqueue_scripts', 'soundmap_wp_enqueue_scripts');
add_action('admin_init', 'soundmap_register_admin_scripts');
add_action('save_post', 'soundmap_save_post');

add_action('wp_ajax_soundmap_file_uploaded', 'soundmap_ajax_file_uploaded_callback');
add_action('wp_ajax_nopriv_soundmap_JSON_load_markers','soundmap_JSON_load_markers');
add_action('wp_ajax_nopriv_soundmap_load_infowindow','soundmap_load_infowindow');

add_filter( 'pre_get_posts', 'soundmap_pre_get_posts' );

register_activation_hook(__FILE__, 'soundmap_rewrite_flush');


function get_uri() {
        $request_uri = $_SERVER['REQUEST_URI'];
        // for consistency, check to see if trailing slash exists in URI request
        if (substr($request_uri, -1)!="/") {
                $request_uri = $request_uri."/";
        }
        preg_match_all('#[^/]+/#', $request_uri, $matches);
        // could've used explode() above, but this is more consistent across varied WP installs
        $uri = $matches[0];
        return $uri;
}

function add_player_interface($files, $id){
    
    if(!is_array($files))
        return;

    global $soundmap_Player;
    
    $insert_content = $soundmap_Player->print_audio_content($files, $id);
    echo $insert_content;
    
}

