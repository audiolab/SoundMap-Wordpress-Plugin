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
require_once (WP_PLUGIN_DIR . "/soundmap/api/soundmap_tags.php");
require_once (WP_PLUGIN_DIR . "/soundmap/api/soundmap.soinudroid.php");
require_once (WP_PLUGIN_DIR . "/soundmap/api/soundmap.uploader.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.base.player.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.audioplayer.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.haikuplayer.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.wp-audio-gallery-player.php");
require_once (WP_PLUGIN_DIR . "/soundmap/modules/module.jplayer.php");



add_action('init', 'soundmap_init');
add_action('init', 'soundmap_add_feed');

add_action('admin_enqueue_scripts', 'soundmap_admin_enqueue_scripts');
add_action('wp_enqueue_scripts', 'soundmap_wp_enqueue_scripts');

add_action('wp_print_footer_scripts', 'soundmap_wp_print_footer_scripts',1);

add_action('admin_init', 'soundmap_register_admin_scripts');
add_action('save_post', 'soundmap_save_post');
add_action('admin_menu',   "soundmap_admin_menu"); 

add_action('wp_ajax_soundmap_file_uploaded', 'soundmap_ajax_file_uploaded_callback');
add_action('wp_ajax_nopriv_soundmap_file_uploaded', 'soundmap_ajax_file_uploaded_callback');
add_action('wp_ajax_soundmap_JSON_load_markers','soundmap_JSON_load_markers');
add_action('wp_ajax_nopriv_soundmap_JSON_load_markers','soundmap_JSON_load_markers');
add_action('wp_ajax_nopriv_soundmap_load_infowindow','soundmap_load_infowindow');
add_action('wp_ajax_nopriv_soundmap_save_public_upload', 'soundmap_save_public_upload');
add_action('wp_ajax_nopriv_soundmap_verify_captcha', 'soundmap_verify_captcha');

add_filter( 'pre_get_posts', 'soundmap_pre_get_posts' );

register_activation_hook(__FILE__, 'soundmap_rewrite_flush');



function soundmap_init() {
    
    global $soundmap;
    
    $soundmap = array();
    $soundmap['on_page'] = FALSE;
    
    soundmap_register_scripts();
    
    $labels = array(
        'name' => __('Marks', 'soundmap'),
        'singular_name' => __('Mark', 'soundmap'),
        'add_new' => _x('Add New', 'marker', 'soundmap'),
        'add_new_item' => __('Add New Marker', 'soundmap'),
        'edit_item' => __('Edit Marker', 'soundmap'),
        'new_item' => __('New Marker', 'soundmap'),
        'all_items' => __('All Markers', 'soundmap'),
        'view_item' => __('View Marker', 'soundmap'),
        'search_items' => __('Search Markers', 'soundmap'),
        'not_found' =>  __('No markers found', 'soundmap'),
        'not_found_in_trash' => __('No markers found in Trash', 'soundmap'), 
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
        'rewrite' => array( 'slug' => 'marker', 'with_front' => false ),
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
  flush_rewrite_rules();
  
  _soundmap_load_options();
  
  soundmap_create_player_instance();
  
  if(!is_admin()){
    soundmap_register_wp_scripts();    
  }
}

function soundmap_get_markers(){
    
    $query = new WP_Query(array(
            'post_type' => 'marker',
            'post_status' => 'publish',
            )
        );
    
    if ( !$query->have_posts() )
	die();
    

    foreach($query->posts as &$post){
        $post_id = $post->ID;
        $custom = get_post_custom($post_id);
        $post->marker['lat'] = $custom['soundmap_marker_lat'];
        $post->marker['lng'] = $custom['soundmap_marker_lng'];
        $post->marker['author'] = $custom['soundmap_marker_author'];
        $post->marker['date'] = $custom['soundmap_marker_date'];
        $post->marker['attachments'] = $custom['soundmap_attachments_id'];        
    }
    
    return $query->posts;
    
}

function soundmap_create_player_instance(){
    
    global $soundmap_Player, $soundmap;
        
    $plugins = get_option( 'active_plugins', array() );
    
    if (!is_array($plugins))
        return;
    
    if(in_array('audio-player/audio-player.php', $plugins) && class_exists('sm_AudioPlayer') && $soundmap['player_plugin'] =="audio-player/audio-player.php"){
        //Audio player active
        $soundmap_Player = new sm_AudioPlayer();
        return;
    }
    
    if(in_array('haiku-minimalist-audio-player/haiku-player.php', $plugins) && class_exists('sm_HaikuPlayer') && $soundmap['player_plugin'] =='haiku-minimalist-audio-player/haiku-player.php'){
        //Audio player active
        $soundmap_Player = new sm_HaikuPlayer();
        return;
    }
    
    if(in_array('wp-audio-gallery-playlist/wp-audio-gallery-playlist.php', $plugins) && class_exists('sm_AudioGallery_Player') && $soundmap['player_plugin'] =='wp-audio-gallery-playlist/wp-audio-gallery-playlist.php'){
        //Audio player active
        $soundmap_Player = new sm_AudioGallery_Player();
        return;
    }
    
    if(in_array('jplayer/jplayer.php', $plugins) && class_exists('sm_jPlayer') && $soundmap['player_plugin'] =='jplayer/jplayer.php'){
        //Audio player active
        $soundmap_Player = new sm_jPlayer();
        return;
    }    
        
}







function soundmap_rewrite_flush() {
  soundmap_init();
  flush_rewrite_rules();
}


function soundmap_JSON_load_markers () {
    
    if (!isset($_POST['markers']))
        return;
    
    if ($_POST['markers']=='all'){
        $query = new WP_Query(array('post_type' => 'marker', 'post_status' => 'publish'));            
    }else{
        if (!is_array($_POST["markers"])){
            $markers_list = json_decode($_POST['markers']);
        }else{$markers_list = $_POST['markers'];}
        
        $query = new WP_Query(array('post_type' => 'marker', 'post_status' => 'publish', 'post__in' => $markers_list));        
    }
    
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
 if ( (is_category() || is_tag()) && false == $query->query_vars['suppress_filters'] )
        $query->set( 'post_type', array( 'marker') );
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
    $info['m_files'] = array();
    foreach ($files as $key => $value){
        $file = array();
        $att = get_post($value);
        $file['id'] = $value;
        $file['fileURI'] = wp_get_attachment_url($value);
        $file['filePath'] = get_attached_file($value);
        $file['info'] = soundmap_get_id3info($file['filePath']);
        $file['name'] = $att->post_name;
        $info['m_files'][] = $file;   
    }
    if ($theme=soundmap_get_template_include('window')) 
        include ($theme);            
    die();
}


/*
 *
 *
 *  METABOXES FUNCTIONS
 *
 *
 */
function soundmap_metaboxes_register_callback(){
    add_meta_box('soundmap-map', __("Place the Marker", 'soundmap'), 'soundmap_map_meta_box', 'marker', 'normal', 'high');
    add_meta_box('soundmap-sound-file', __("Add a sound file", 'soundmap'), 'soundmap_upload_file_meta_box', 'marker', 'normal', 'high');
    add_meta_box('soundmap-sound-info', __("Add info for the marker", 'soundmap'), 'soundmap_info_meta_box', 'marker', 'side', 'high');
    add_meta_box('soundmap-sound-attachments', __("Sound files attached.", 'soundmap'), 'soundmap_attachments_meta_box', 'marker', 'side', 'high');
    add_meta_box('soundmap-email', __("Uploader Mail", 'soundmap'), 'soundmap_email_meta_box', 'marker', 'side', 'low');
}

function soundmap_email_meta_box(){
     global $post;
    $mail = get_post_meta($post->ID, 'EMAIL', TRUE);
    echo "<p>" . $mail . "</p>";    

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

    $html = <<<EOHTML
    <div id="uploaderContainer"> 
	<div id="uploaderButton"></div>
        <div id="uploaderQueue"></div>
        <div class="clear"></div>
    </div> 
EOHTML;
    echo $html;   

}




/*
 *
 *
 *  MARKERS & FILES SAVE/MANIPULATION FUNCTIONS
 *
 *
 */
function soundmap_ajax_file_uploaded_callback() {
		
    if (!isset($_FILES['Filedata']))
        die();
    
    $rtn = array();
    $rtn['error'] = "";

    $file_data = $_FILES['Filedata'];
    
    if ($file_data['error'] != 0)
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
    $tempDir = $file_data['tmp_name'];
    //move it.
    $fileDir = $targetDir . DIRECTORY_SEPARATOR . $fileName;
    $fileURL = $targetURL . DIRECTORY_SEPARATOR . $fileName;
    if(!rename($tempDir, $fileDir)){
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
    
    update_post_meta($post_id, 'soundmap_marker_lat', $soundmark_lat);
    update_post_meta($post_id, 'soundmap_marker_lng', $soundmark_lng);
    update_post_meta($post_id, 'soundmap_marker_author', $soundmark_author);
    update_post_meta($post_id, 'soundmap_marker_date', $soundmark_date);
    

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

/*
 *
 *
 *  JAVASCRIPT MANIPULATION FUNCTIONS
 *
 *
 */


function soundmap_register_scripts(){
    //Register google maps.
    wp_register_script('google-maps','http://maps.google.com/maps/api/js?libraries=geometry&sensor=true');
    wp_register_script('jquery-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js');
    wp_register_script('jquery-google-maps', WP_PLUGIN_URL . '/soundmap/js/jquery.ui.map.min.js', array('jquery', 'jquery-ui', 'google-maps'));
}

function soundmap_register_admin_scripts() {
    wp_register_script('soundmap-admin', WP_PLUGIN_URL . '/soundmap/js/soundmap-admin.js', array('jquery-google-maps', 'swfupload', 'swfupload-queue',  'jquery-datepicker'));
    wp_register_style("soundmap-admin", WP_PLUGIN_URL . '/soundmap/css/soundmap-admin.css', array('plupload-style', 'jquery-datepicker'));
    
    //Register PLUPLOAD
    wp_register_style('plupload-style',WP_PLUGIN_URL . '/soundmap/css/jquery.plupload.queue.css');
    wp_register_script('plupload', WP_PLUGIN_URL . '/soundmap/js/plupload/plupload.full.js');
    wp_register_script('jquery-plupload', WP_PLUGIN_URL . '/soundmap/js/plupload/jquery.plupload.queue.js', array('plupload'));
    
    //Register YUI Uploader
    wp_register_script('yui-uploader', 'http://yui.yahooapis.com/3.4.1/build/yui/yui-min.js');
    
    //Register DATEPICKER
    wp_register_script('jquery-datepicker', WP_PLUGIN_URL . '/soundmap/js/jquery.datepicker.js', array('jquery'));
    wp_register_style('jquery-datepicker', WP_PLUGIN_URL . '/soundmap/css/jquery.datepicker.css');    
}

function soundmap_register_wp_scripts() {
    wp_register_script('soundmap',  WP_PLUGIN_URL . '/soundmap/js/soundmap-wp.js', array('jquery-google-maps') , "1.0", true);
}

function soundmap_admin_enqueue_scripts() {
    wp_enqueue_script('soundmap-admin');
    wp_enqueue_style('soundmap-admin');
    
    global $soundmap;
    
    $params = array();
    $params['plugin_url'] = WP_PLUGIN_URL . '/soundmap/';    
    $params += $soundmap['origin'];
    $params ['mapType'] = $soundmap['mapType'];
    $params['swfupload_flash'] = includes_url('js/swfupload/swfupload.swf');
    $params['swfupload_picture'] = includes_url( 'images/upload.png?ver=20100531' );
    $params['selectfile'] = __('Select file');
    
    wp_localize_script('soundmap-admin','WP_Params',$params);
}

function soundmap_wp_enqueue_scripts() {
    
    wp_enqueue_script('soundmap');
 //   add_thickbox();
 
}



function soundmap_wp_print_footer_scripts(){
    global $soundmap;
    
    $params = array();
    $params['ajaxurl'] = admin_url( 'admin-ajax.php' );    
    $params += $soundmap['origin'];
    $params ['mapType'] = $soundmap['mapType'];
    if (isset($soundmap['markers'])){
	$params ['markers'] = json_encode($soundmap['markers']);	
    }

    $params ['pluginURL'] = WP_PLUGIN_URL . "/soundmap/";

    wp_localize_script( 'soundmap', 'WP_Params', $params);
}


/*
 *
 *
 *  CONFIG PAGE FUNCTIONS
 *
 *
 */

function soundmap_admin_menu(){
    add_options_page(__('Sound Map Configuration','soundmap'), __('Sound Map','soundmap'), 'manage_options', 'soundmap-options-menu','soundmap_menu_page_callback');
}


function soundmap_menu_page_callback(){
    
    if (!current_user_can('manage_options'))  {
            wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
        // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (isset($_POST['soundmap_op_noncename'])){
        if ( !wp_verify_nonce( $_POST['soundmap_op_noncename'], plugin_basename( __FILE__ ) ) )
            return;
        _soundmap_save_options();
    }
    
    global $soundmap;
    
    ?>
    	<div class="wrap">
            <h2><?php  _e("Sound Map Configuration", 'soundmap') ?></h2>
            <div id="map_canvas_options"></div>
            <form method="post" action = "" id="form-soundmap-options">
                <h3><?php _e('Map Configuration','soundmap') ?></h3>
                <table class="form-table">                
                    <tr valign="top">
                        <th scope="row"><?php _e('Origin configuration','soundmap') ?></th>
                        <td>
                            <fieldset>
                                <span class="description"><?php _e("Choose the original configuration with the map.", 'soundmap') ?></span>
                                <br>
                                <label for="soundmap_op_origin_lat"><?php _e('Latitude','soundmap') ?>: </label><br>
                                <input class="regular-text" name="soundmap_op_origin_lat" id="soundmap_op_origin_lat" type="text" value="<?php echo $soundmap['origin']['lat'] ?>">                                    
                                <br>
                                <label for="soundmap_op_origin_lng"><?php _e('Longitude','soundmap') ?>: </label><br>
                                <input class="regular-text" name="soundmap_op_origin_lng" id="soundmap_op_origin_lng" type="text" value="<?php echo $soundmap['origin']['lng'] ?>">                                                                
                                <br>
                                <label for="soundmap_op_origin_zoom"><?php _e('Zoom','soundmap') ?>: </label><br>
                                <input class="small-text" name="soundmap_op_origin_zoom" id="soundmap_op_origin_zoom" type="text" value="<?php echo $soundmap['origin']['zoom'] ?>">
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="soundmap_op_origin_type"><?php _e('Map type','soundmap') ?></label></th>
                        <td>
                            <fieldset>
                                <select class="postform" name="soundmap_op_origin_type" id="soundmap_op_origin_type" >
                                    <option class="level-0" <?php if ($soundmap['mapType']=="TERRAIN") echo 'selected ="selected"' ?> value="TERRAIN"><?php _e("Terrain") ?></option>
                                    <option class="level-0" <?php if ($soundmap['mapType']=="SATELLITE") echo 'selected ="selected"' ?>value="SATELLITE"><?php _e("Satellite") ?></option>
                                    <option class="level-0" <?php if ($soundmap['mapType']=="ROADMAP") echo 'selected ="selected"' ?>value="ROADMAP"><?php _e("Map") ?></option>
                                    <option class="level-0" <?php if ($soundmap['mapType']=="HYBRID") echo 'selected ="selected"' ?>value="HYBRID"><?php _e("Hybrid") ?></option> 
                                </select>                                
                            </fieldset>
                        </td>
                    </tr>
                </table>                
                <h3><?php _e('Sound Player Modules','soundmap') ?></h3>                
                    <?php _soundmap_list_installer_audio_players(); ?>
                <p class="submit">
                    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
                </p>
                
                <input type="hidden" name="soundmap_op_noncename" id="soundmap_op_noncename" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ) ?>" />
            </form>
	</div>
<?php
}


/*
 *
 *
 *  FEED FUNCTIONS
 *
 *
 */


function soundmap_customfeed() {
    load_template( WP_PLUGIN_DIR . '/soundmap/theme/rss-markers.php'); // You'll create a your-custom-feed.php file in your theme's directory
}


function soundmap_custom_feed_rewrite($wp_rewrite) {
    $feed_rules = array(
    'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index(1),
    '(.+).xml' => 'index.php?feed='. $wp_rewrite->preg_index(1)
    );
    $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
}


function soundmap_rewrite_rules( $wp_rewrite ) {
  $new_rules = array(
    'feed/(.+)' => 'index.php?feed='.$wp_rewrite->preg_index(1)
  );
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function soundmap_add_feed(  ) {
  global $wp_rewrite;
  add_feed('podcast', 'soundmap_customfeed');
  add_action('generate_rewrite_rules', 'soundmap_rewrite_rules');
  $wp_rewrite->flush_rules();
}


/*
 *
 *
 *  INTERNAL FUNCTIONS
 *
 *
 */


function _soundmap_mountPostContent($info){
		
	$t = "";
	
	if (function_exists("qtrans_init")){
		//we are using multilanguage;
		global $q_config;
			foreach ($q_config['enabled_languages'] as $key=>$value){
				$sel_Lang = $value;
				$lang_Name = $q_config['language_name'][$sel_Lang];
				$texts[$sel_Lang] = $info["descripcion_" . $sel_Lang];
			}
			$t = qtrans_join($texts);
							
	}
	return $t;	
}

function _soundmap_mountPostTitle($info){
		
	$t = "";
	
	if (function_exists("qtrans_init")){
		//we are using multilanguage;
		global $q_config;
			foreach ($q_config['enabled_languages'] as $key=>$value){
				$sel_Lang = $value;
				$lang_Name = $q_config['language_name'][$sel_Lang];
				$texts[$sel_Lang] = $info["title_" . $sel_Lang];
			}
			$t = qtrans_join($texts);
							
	}
	return $t;	
}

function _soundmap_list_installer_audio_players(){
    
    include_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php');
    $plugins = get_plugins();
    global $soundmap;
    $valid_plugins = array('Audio player', 'Haiku - minimalist audio player', 'JPlayer', 'wp audio gallery playlist');
    $active_plugins = get_option( 'active_plugins', array() );
    $rows = '';
    foreach($active_plugins as $key => $name){
        if (array_key_exists ($name,$plugins)){
            $plugin = $plugins[$name];        
            if (!(array_search($plugin["Name"], $valid_plugins) === FALSE)){
                if($name == $soundmap['player_plugin']){$ch = 'checked = "checked"';}else{$ch = '';}
                $rows .= '<tr class="inactive"><th scope="row" class="check-column">';
                $rows .= '<input type="radio" name="soundmap_op_plugin" value="' .$name . '" '. $ch .'></th>';
                $rows .= '<td class="plugin-title"><strong>' . $plugin['Name'] . '</strong></td>';
                $rows .= '<td class="column-description desc"><div class="plugin-description"><p>'. $plugin['Description'] .'</p></div>';
                $rows .= '<div class="inactive second plugin-version-author-uri">' . __('Version') . ' ' . $plugin['Version'] . ' | ' . __('By') . ' <a href="' . $plugin['AuthorURI'] . '">' . $plugin['Author'] . '</a> | <a href="' . $plugin['PluginURI'] . '">' . __('Visit plugin site') . '</a></div>';
                $rows .= '</td></tr>';
            }
        }
    }
    ?>
    <table class="wp-list-table widefat plugins" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" class="manage-column check-column"></th>
                <th scope="col" class="manage-column column-name"><?php _e("Plugin") ?></th>
                <th scope="col" class="manage-column column-description"><?php _e("Description") ?></th>
            </tr>            
        </thead>
        <tbody id="the-list">
            <?php echo $rows; ?>
        </tbody>
        
    </table>
    
    <?php
}

function _soundmap_postsArrayToList($posts){
    if (!is_array($posts))
        return;
    $list = array();
    foreach ($posts as $post){
        array_push($list,$post->ID);
    }
    return $list;
}



function _soundmap_load_options(){
    $_soundmap = array();
    global $soundmap;
    //Load defaults;
    $defaults = array();
    $defaults['on_page'] = FALSE;
    $defaults['origin']['lat'] = 0;
    $defaults['origin']['lng'] = 0;
    $defaults['origin']['zoom'] = 10;
    $defaults['mapType'] = 'ROADMAP';
    
    $defaults['player_plugin'] = "";
    
    $_soundmap = maybe_unserialize(get_option('soundmap'));
    
    $_soundmap = wp_parse_args($_soundmap, $defaults);
    
    $soundmap = $_soundmap;
    load_plugin_textdomain('soundmap', "wp-content/plugins/soundmap/languages", dirname( plugin_basename( __FILE__ ) ) . "/languages");
}

function _soundmap_save_options(){
    
    $_soundmap = array();
    global $soundmap;
    //Load defaults;
    
    $_soundmap ['origin']['lat'] = $_POST['soundmap_op_origin_lat'];
    $_soundmap ['origin']['lng'] = $_POST['soundmap_op_origin_lng'];
    $_soundmap ['origin']['zoom'] = $_POST['soundmap_op_origin_zoom'];
    $_soundmap ['mapType'] = $_POST['soundmap_op_origin_type'];
    
    if(isset($_POST['soundmap_op_plugin']))
        $_soundmap ['player_plugin'] = $_POST['soundmap_op_plugin'];
    
    update_option('soundmap',maybe_serialize($_soundmap));   
    $soundmap = wp_parse_args($_soundmap, $soundmap);
}