<?php

/** Sets up the WordPress Environment. */

require( '../../../../wp-load.php' );
//nocache_headers();
session_start();
/** End Set up de thordpress Environment. **/
//load_plugin_textdomain('soundmap');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

<?php
    wp_register_script('soundmap_upload',  WP_PLUGIN_URL . '/soundmap/modules/public_upload/js/upload.js', array(), NULL, true);
    wp_register_script('soundmap_upload_map',  WP_PLUGIN_URL . '/soundmap/modules/public_upload/js/soundmap-upload-map.js', array('jquery-google-maps') , NULL, true);
    
    global $soundmap;
    
    $params = array();
    $params['plugin_url'] = WP_PLUGIN_URL . '/soundmap/';    
    $params += $soundmap['origin'];
    $params ['mapType'] = $soundmap['mapType'];
    
    
    $params_up = array();
    $params_up['url_plugin'] = WP_PLUGIN_URL . '/soundmap';
    $params_up['php_sesion'] = session_id();
    $params_up['max_upload_size'] = ini_get('upload_max_filesize');
    
    $params_up['upload_button_tag'] = __("Select audio file","soundmap");
    $params_up['error_not_place_tag'] = __("Please, place the recording",'soundmap');
    $params_up['error_not_sound_tag'] = __("Please, select one file to upload.",'soundmap');
    $params_up['error_max_upload_size_tag']=__("The selected file is too big.","soundmap");
    $params_up['locked_tag'] = __("Locked: form can't be submited.",'soundmap');
    $params_up['unlocked_tag'] = __('Unlocked: form can be submited.','soundmap');
    $params_up['sfile_name_tag'] = __('Name: ',"soundmap");
    $params_up['sfile_length_tag'] = __('Length: ','soundmap');
    
    $params_up['ajaxurl'] = admin_url( 'admin-ajax.php' );    

    wp_enqueue_script('soundmap_upload');    
    wp_localize_script('soundmap_upload','WP_Params_up',$params_up);
    
    wp_enqueue_script('soundmap_upload_map');    
    wp_localize_script('soundmap_upload_map','WP_Params',$params);
    

    
?>

<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/text.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/960.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/newpost.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/jquery-ui-1.8.13.custom.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/QapTcha.jquery.css" type="text/css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/jquery.autocomplete.css" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/swfupload/swfupload.js"></script>

<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/fileprogress.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/handlers.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/additional-methods.min.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/QapTcha.jquery.js"></script>

<!-- Anything Slider -->
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/anythingslider.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/print.css" type="text/css" media="print" />
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/jquery.anythingslider.min.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/js/jquery.anythingslider.fx.min.js"></script>
<!-- Add the stylesheet(s) you are going to use here. All stylesheets are included below, remove the ones you don't use. -->

<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL  ?>/soundmap/modules/public_upload/css/theme-minimalist-round.css" type="text/css" media="screen" />

<!-- Older IE stylesheet, to reposition navigation arrows, added AFTER the theme stylesheet above -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="css/anythingslider-ie.css" type="text/css" media="screen" />
<![endif]-->



<?php
	$language = "";
	if (isset($_GET['lang'])){
		$language = $_GET['lang'];		
	}
	
?>
	
<?php
function insertFirstPanel($language){
		include_once("public_upload/slide_first.php");
}

function insertSecondPanel($language){
		include_once("public_upload/slide_second.php");
}
function insertThirdPanel($language){
		include_once("public_upload/slide_third.php");
}
function insertFourthPanel($language){
		include_once("public_upload/slide_fourth.php");	
}

?>

</head>
<body>
	<div id="page">
		
		<div class="steps-bar">
			<ul id="crumbs">
				<li class="selected"><span><?php _e("Sound file","soundmap") ?></span></li>
				<li><span><?php _e("Map","soundmap")?></span></li>
				<li><span><?php _e("Information","soundmap")?></span></li>
				<li><?php _e("Submit","soundmap")?></li>
			</ul>
		</div>
		<div id="slider">			
			<?php
				
				insertFirstPanel($language);
				insertSecondPanel($language);
				insertThirdPanel($language);
				insertFourthPanel($language);
			?>
		</div>
		<div id="file-progress-bar">
			<div id="progress-bar">
				
			</div>
			<div id="recordingInfo">
			
				<h5><?php _e("Sound file data: ","soundmap")?></h5>
			</div>
		</div>	
	</div>	
	<div id="savingContent">
		<div id="savingInfo">
			<h3><?php _e("Saving the recording","soundmap")?></h3>
			<img src="<?php echo WP_PLUGIN_URL?>/soundmap/modules/public_upload/images/loading.gif"></img>
		</div>
	</div>
<?php wp_footer();?>
</body>
</html>
