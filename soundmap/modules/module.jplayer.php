<?php


class sm_jPlayer extends SoundMap_Player{

    var $capabilities;
    var $options;
    
    function sm_jPlayer(){
        $this->_load_capabilites();
    }
    
    function _load_capabilites(){
        $capabilities = array();
        $capabilities['playLists'] = TRUE;
    }

    function print_audio_content($files, $id){
        
        
        $playlist = FALSE;    
        $total_files = count($files);
    
        if (!$total_files)
            return;
    
        if ($total_files>1)
            $playlist = TRUE;
            
        $out = "";
        $out = jplayer_insert($files, $id, TRUE);
        //$out_script = '<script type="text/javascript">'. $AudioPlayer->footerCode . '</script>';
        return $out; // . $out_script;
    }

}