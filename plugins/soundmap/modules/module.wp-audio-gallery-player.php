<?php


class sm_AudioGallery_Player extends SoundMap_Player{

    var $capabilities;
    var $options;
    
    function sm_AudioGallery_Player(){
        $this->_load_capabilites();
    }
    
    function _load_capabilites(){
        $capabilities = array();
        $capabilities['playLists'] = TRUE;
    }

    function print_audio_content($files, $id){
                
        $total_files = count($files);
    
        if (!$total_files)
            return;
    
        if ($total_files>1)
        
            
        $file_links = array();
        $out = '';
        $out = wp_audio_gallery_playlist('[WP AUDIO PLAYLIST]');
       // $out_script = '<script type="text/javascript"> load_haiku_players(); </script>';
        
        return $out;// . $out_script;
    }

}