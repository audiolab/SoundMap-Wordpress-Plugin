<?php


/**
 * @package Soundmap
 */
/*
Plugin Name: Soundmap
Plugin URI: http://www.soinumapa.net
Description: New version of the Soinumapa Plugin for creating sound maps
Version: 2.5.3
Author: Xavier Balderas
Author URI: http://www.xavierbalderas.com  
License: GPLv2 or later
*/


function soundmap_init() {
    
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
}

function soundmap_register_scripts(){
    //Register google maps.
    wp_register_script('google-maps','http://maps.google.com/maps/api/js?libraries=geometry&sensor=true');
    wp_register_script('jquery-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js');
    wp_register_script('jquery-google-maps', WP_PLUGIN_URL . '/soundmap/js/jquery.ui.map.js', array('jquery', 'jquery-ui', 'google-maps'));
}



function soundmap_metaboxes_register_callback(){
    add_meta_box('sounmap-map', __("Place the Marker", 'soundmap'), 'soundmap_map_meta_box', 'marker', 'normal', 'high');
    add_meta_box('sounmap-sound-file', __("Add a sound file", 'soundmap'), 'soundmap_upload_file_meta_box', 'marker', 'side', 'high');
}
function soundmap_map_meta_box(){
   echo '<input type="hidden" name="soundmap_map_noncename" id="soundmap_map_noncename" value="' .
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
   echo '<div id="map_canvas"></div>';
   echo '<label for="soundmap-marker-lat">' . __('Latitud', 'soundmap') . '</label>';
   echo '<input type="text" name="soundmap_marker_lat" id="soundmap-marker-lat" value="0">';
   echo '<label for="soundmap-marker-lng">' . __('Longitud', 'soundmap') . '</label>';
   echo '<input type="text" name="soundmap_marker_lng" id="soundmap-marker-lng" value="0">';
    
}

function soundmap_upload_file_meta_box(){

    echo '<form id="uploader-form" method="post" action="dump.php"><div id="uploader"></div></form> ';
}

function soundmap_rewrite_flush() {
  soundmap_init();
  flush_rewrite_rules();
}

function soundmap_register_admin_scripts() {
    wp_register_script('soundmap-admin', WP_PLUGIN_URL . '/soundmap/js/soundmap-admin.js', array('jquery-google-maps', 'jquery-plupload'));
    wp_register_style("soundmap-admin", WP_PLUGIN_URL . '/soundmap/css/soundmap-admin.css', array('plupload-style'));
    
    //Register PLUPLOAD
    wp_register_style('plupload-style',WP_PLUGIN_URL . '/soundmap/css/jquery.plupload.queue.css');
    wp_register_script('plupload', WP_PLUGIN_URL . '/soundmap/js/plupload/plupload.full.js');
    wp_register_script('jquery-plupload', WP_PLUGIN_URL . '/soundmap/js/plupload/jquery.plupload.queue.js', array('plupload'));    
    
}

function soundmap_admin_enqueue_scripts() {
    wp_enqueue_script('soundmap-admin');
    wp_enqueue_style('soundmap-admin');
    
    $params = array();
    $params['plugin_url'] = WP_PLUGIN_URL . '/soundmap/';
    
    wp_localize_script('soundmap-admin','WP_Params',$params);
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
    
    add_post_meta($post_id, 'soundmap_marker_lat', $soundmark_lat, TRUE);
    add_post_meta($post_id, 'soundmap_marker_lng', $soundmark_lng, TRUE);
    

    //before searching on all the $_POST array, let's take a look if there is any upload first!
    if(!array_key_exists("uploader_0_name",$_POST))
        return;
    
    $files = preg_grep('/mp3$/', $_POST);
    
    foreach($files as $key => $value){
        if(preg_match('/^uploader_[0-9]*_name$/',$key)){
            $exp = explode('_', $key);
            $number = $exp[1];
            $name = $value;
            $tmpname = $_POST['uploader_' . $number . '_tmpname'];
            soundmap_add_sound_file($name, $tmpname, $post_id);
        }
    }    
}

function soundmap_add_sound_file ($name, $tmpname, $post_id){
//    var_dump($name);
}

add_action('init', 'soundmap_init');
add_action('admin_enqueue_scripts', 'soundmap_admin_enqueue_scripts');
add_action('admin_init', 'soundmap_register_admin_scripts');
add_action('save_post', 'soundmap_save_post');

register_activation_hook(__FILE__, 'soundmap_rewrite_flush');


