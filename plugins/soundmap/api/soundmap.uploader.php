<?php



function soundmap_verify_captcha(){
    
    session_start();

    $aResponse['error'] = false;
    $_SESSION['iQaptcha'] = false;	
            
    if(isset($_POST['data']))
    {
            if(htmlentities($_POST['data'], ENT_QUOTES, 'UTF-8') == 'qaptcha')
            {
                    $_SESSION['iQaptcha'] = true;
                    if($_SESSION['iQaptcha']==true){
                            echo json_encode($aResponse);
                    }
                    else
                    {
                            $aResponse['error'] = true;
                            echo json_encode($aResponse);
                    }
            }
            else
            {
                    $aResponse['error'] = true;
                    echo json_encode($aResponse);
            }
    }
    else
    {
            $aResponse['error'] = true;
            echo json_encode($aResponse);
    }
    die();
}

function soundmap_save_public_upload(){
    $info = $_REQUEST['info'];
    $uploader = $_REQUEST['uploader'];
    
    $content = _soundmap_mountPostContent($info);
    $title = _soundmap_mountPostTitle($info);
    
    $soundmark_lat = $info['posLat'];
    $soundmark_lng = $info['posLong'];
    $soundmark_author = $info['author'];
    $soundmark_date = $info['date'];
    $soundmark_mail = $uploader['email'];
    
    if ($title == "" && $soundmark_author != ""){
        $title = $soundmark_author;
    }elseif ($title =="" && $soundmark_author ==""){
        $title = $info['fileName'];
    }
    
    if (!is_array($info['categoria'])){
        $val = $info['categoria'];
        $info['categoria'] = array($val);
    }               
    
    $post_d = array(
	'post_category' => $info['categoria'],
	'post_content' => $content,
	'post_status' => 'publish', 
	'post_title' => $title,
	'post_type' => 'marker',
        'comment_status' => 'closed'
				  //'tags_input' => [ '<tag>, <tag>, <...>' ] //For tags.
    );
    $post_id=wp_insert_post($post_d);
    
    

    
    update_post_meta($post_id, 'soundmap_marker_lat', $soundmark_lat);
    update_post_meta($post_id, 'soundmap_marker_lng', $soundmark_lng);
    update_post_meta($post_id, 'soundmap_marker_author', $soundmark_author);
    update_post_meta($post_id, 'soundmap_marker_date', $soundmark_date);
    update_post_meta($post_id, 'EMAIL', $soundmark_mail);
    
    
        $files = $info['attachID'];
        delete_post_meta($post_id, 'soundmap_attachments_id');
        update_post_meta($post_id, 'soundmap_attachments_id', $files);
        soundmap_attach_file($files, $post_id); 
        
    echo "ok";
    die();
    
}
?>