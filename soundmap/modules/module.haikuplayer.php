<?php


class sm_HaikuPlayer extends SoundMap_Player{

    var $capabilities;
    var $options;
    
    function sm_HaikuPlayer(){
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
        foreach($files as $file){
            $atts['url'] = $file['fileURI'];
            $out .= haiku_player_shortcode($atts);
        }
        $out_script = '<script type="text/javascript"> load_haiku_players(); </script>';
        
        return $out . $out_script;
    }

}