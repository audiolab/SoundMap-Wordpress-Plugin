<?php


class sm_AudioPlayer extends SoundMap_Player{

    var $capabilities;
    var $options;
    
    function sm_AudioPlayer(){
        $this->_load_capabilites();
    }
    
    function _load_capabilites(){
        $capabilities = array();
        $capabilities['playLists'] = TRUE;
    }

    function print_audio_content($files, $id){
        
        global $AudioPlayer;
        
        $playlist = FALSE;    
        $total_files = count($files);
    
        if (!$total_files)
            return;
    
        if ($total_files>1)
            $playlist = TRUE;
            
        $file_links = array();
        foreach($files as $file){
            $file_links[] = $file['fileURI'];
        }
        $param = "";
        if ($playlist){
            $param_files = implode(',', $file_links);
        }else{
            $param_files = $file_links[0];
        }
        $param = '[audio:' . $param_files . '|autostart=yes]';
        $out = "";
        $out = $AudioPlayer->processContent($param);
        $out_script = '<script type="text/javascript">'. $AudioPlayer->footerCode . '</script>';
        return $out . $out_script;
    }

}