
var sendOnFinish = false;

jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
    var map_canvas = $('#map_canvas');
    var or_latln = new google.maps.LatLng(WP_Params.lat, WP_Params.lng);
    var or_maptype;
    switch (WP_Params.mapType) {
        case 'ROADMAP':
            or_maptype = google.maps.MapTypeId.ROADMAP;
            break;
        case 'HYBRID':
            or_maptype = google.maps.MapTypeId.HYBRID;
            break;
        case 'TERRAIN':
            or_maptype = google.maps.MapTypeId.TERRAIN;
            break;
        case 'SATELLITE':
            or_maptype = google.maps.MapTypeId.SATELLITE;
            break;
    }
    var soundmark;
    if(map_canvas.length) {
        $('#map_canvas').gmap({
            'center' : or_latln,
            'zoom' : Number(WP_Params.zoom),
            'mapTypeId' : or_maptype
        }).bind('init', function(event, map) {
            if($('#soundmap-marker-lat').val() != "") {
                var latlng = new google.maps.LatLng($('#soundmap-marker-lat').val(), $('#soundmap-marker-lng').val());
                map.panTo(latlng);
                soundmark = map_canvas.gmap('addMarker', {
                    'position' : latlng,
                    'title' : '',
                    'draggable' : true
                }, function(map, marker) {
                });
            }
            $(map).click(function(event) {
                if(soundmark == undefined) {
                    soundmark = map_canvas.gmap('addMarker', {
                        'position' : event.latLng,
                        'title' : '',
                        'draggable' : true
                    }, function(map, marker) {
                        var marker_position = marker.getPosition();
                        $('#soundmap-marker-lat').val(marker_position.lat());
                        $('#soundmap-marker-lng').val(marker_position.lng());
                    });
                } else {
                    var marker = soundmark.get(0);
                    var new_position = event.latLng;

                    marker.setPosition(new_position);

                    $('#soundmap-marker-lat').val(new_position.lat());
                    $('#soundmap-marker-lng').val(new_position.lng());
                }
            })
        });
    }

    var map_canvas_options = $('#map_canvas_options');
    if(map_canvas_options.length) {
        $('#map_canvas_options').gmap({
            'center' : or_latln,
            'zoom' : Number(WP_Params.zoom),
            'mapTypeId' : or_maptype
        }).bind('init', function(event, map) {
            $(map).dragend(function(event) {
                var new_center = map.getCenter();
                $('#soundmap_op_origin_lat').val(new_center.lat());
                $('#soundmap_op_origin_lng').val(new_center.lng());
            });
            $(map).addEventListener('zoom_changed', function(event) {
                var new_zoom = map.getZoom();
                $('#soundmap_op_origin_zoom').val(new_zoom);
            });
            $(map).addEventListener('maptypeid_changed', function(event) {
                var new_type = map.getMapTypeId();
                switch (new_type) {
                    case google.maps.MapTypeId.ROADMAP:
                        $('#soundmap_op_origin_type').children('option[value|="ROADMAP"]').attr('selected', 'selected');
                        break;
                    case google.maps.MapTypeId.HYBRID:
                        $('#soundmap_op_origin_type').children('option[value|="HYBRID"]').attr('selected', 'selected');
                        break;
                    case google.maps.MapTypeId.TERRAIN:
                        $('#soundmap_op_origin_type').children('option[value|="TERRAIN"]').attr('selected', 'selected');
                        break;
                    case google.maps.MapTypeId.SATELLITE:
                        $('#soundmap_op_origin_type').children('option[value|="SATELLITE"]').attr('selected', 'selected');
                        break;
                }
            });
        });
        $('#soundmap_op_origin_lat, #soundmap_op_origin_lng, #soundmap_op_origin_zoom', '#soundmap_op_origin_type').change(function() {
            var latlng = new google.maps.LatLng($('#soundmap_op_origin_lat').val(), $('#soundmap_op_origin_lng').val());
            var z = $('#soundmap_op_origin_zoom').val();
            map = map_canvas_options.gmap('getMap');
            map.panTo(latlng);
            map.setCenter(latlng);
            map.setZoom(Number(z));
        });
        $('#soundmap_op_origin_type').change(function() {
            var t = $('#soundmap_op_origin_type').val();
            map = map_canvas_options.gmap('getMap');
            switch (t) {
                case 'ROADMAP':
                    mt = google.maps.MapTypeId.ROADMAP;
                    break;
                case 'HYBRID':
                    mt = google.maps.MapTypeId.HYBRID;
                    break;
                case 'TERRAIN':
                    mt = google.maps.MapTypeId.TERRAIN;
                    break;
                case 'SATELLITE':
                    mt = google.maps.MapTypeId.SATELLITE;
                    break;
            }
            map.setMapTypeId(mt);

        });
    }

    $('#soundmap-marker-lat, #soundmap-marker-lng').change(function() {
        var latlng = new google.maps.LatLng($('#soundmap-marker-lat').val(), $('#soundmap-marker-lng').val());
        map = map_canvas.gmap('getMap');
        map.panTo(latlng);
        if(soundmark == undefined) {
            soundmark = map_canvas.gmap('addMarker', {
                'position' : latlng,
                'title' : '',
                'draggable' : true
            }, function(map, marker) {
            });
        } else {
            var marker = soundmark.get(0);
            marker.setPosition(latlng);
        }
    });

    $('#post').submit(function(e) {

        var sT = uploader.getStats();
        // Validate number of uploaded files
        if(sT.in_progress == 1) {
                sendOnFinish = true;
                 alert('File not uploaded yet.');
                 e.preventDefault();                
        } 
    });
    

    $('#soundmap-marker-datepicker').DatePicker({
        format : 'd/m/Y',
        date : $('#soundmap-marker-date').val(),
        current : $('#soundmap-marker-date').val(),
        starts : 1,
        flat : true,
        onChange : function(formated, dates) {
            $('#soundmap-marker-date').val(formated);
        }
    });

    var uploader_settings = {
        upload_url : ajaxurl,
        use_query_string : false,
        http_success : [201, 202],
        file_post_name : "Filedata",
        prevent_swf_caching : false,
        preserve_relative_urls : false,

        flash_url : WP_Params.swfupload_flash,
        button_placeholder_id : 'uploaderButton',
        post_params : {
            'action' : 'soundmap_file_uploaded'
        },

        // File Upload Settings
        file_size_limit : "20 MB", // 2MB
        file_types : "*.mp3",
        file_types_description : "MP3 Files",
        file_upload_limit : "0",
        file_queue_limit : 1,

        button_text : '<span class="button">' + WP_Params.selectfile + '<\/span>',
        button_text_style : '.button { text-align: center; font-weight: bold; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; font-size: 11px; text-shadow: 0 1px 0 #FFFFFF; color:#464646; }',
        button_height : "23",
        button_width : "132",
        button_text_top_padding : 3,
        button_image_url : WP_Params.swfupload_picture,

        // Event Handler Settings - these functions as defined in Handlers.js
        //  The handlers are not part of SWFUpload but are part of my website and control how
        //  my website reacts to the SWFUpload events.
        file_queue_error_handler : s_fileQueueError,
        file_queued_handler : s_fileQueued,
        file_dialog_complete_handler : s_fileDialogComplete,
        upload_progress_handler : s_uploadProgress,
        upload_error_handler : s_uploadError,
        upload_success_handler : s_uploadSuccess,
        upload_complete_handler : s_uploadComplete,
        debug_handler : s_debug_function,
        queue_complete_handler : s_queue_complete

        //debug: true

    };
    
    if(jQuery("#uploaderButton").length){
        uploader = new SWFUpload(uploader_settings);
    }

});
////__________ HANDLERS_____________////

function s_queue_complete (numFilesUploaded){
    if (sendOnFinish){
        jQuery('#post').submit();
    }
}

function s_debug_function (message) {
    console.log(message);
}

function s_fileQueued(file) {

    try {
        jQuery('#uploaderQueue').append('<div id="upload-item-' + file.id + '" class=" uploadFileQueued "><div class="progress"><div class="bar"></div></div><div class="filename original"><span class="percent"></span> ' + file.name + '</div></div>');
        // Display the progress div
        jQuery('.progress', '#upload-item-' + file.id).show();
    } catch(ex) {
        this.debug(ex);
    }

}

function s_fileQueueError(file, errorCode, message) {
    try {
        var imageName = "error.gif";
        var errorName = "";
        if(errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
            errorName = "You have attempted to queue too many files.";
        }

        if(errorName !== "") {
            alert(errorName);
            return;
        }

        switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                imageName = "zerobyte.gif";
                break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                imageName = "toobig.gif";
                break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            default:
                alert(message);
                break;
        }

        addImage("images/" + imageName);

    } catch (ex) {
        this.debug(ex);
    }

}

function s_fileDialogComplete(numFilesSelected, numFilesQueued) {
    try {
        if(numFilesQueued > 0) {
            this.startUpload();
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function s_uploadProgress(file, bytesLoaded) {

    // Lengthen the progress bar
    var w = jQuery('#uploaderQueue').width() - 2, item = jQuery('#upload-item-' + file.id);
    jQuery('.bar', item).width(w * bytesLoaded / file.size);
    jQuery('.percent', item).html(Math.ceil(bytesLoaded / file.size * 100) + '%');

}

function s_uploadSuccess(file, serverData) {
    try {
        //	var progress = new FileProgress(file, this.customSettings.upload_target);
        result = jQuery.parseJSON(serverData);
        if(result.error != "") {
            alert(result.error);
        } else {
            table = jQuery('#sound-att-table');
            table.append('<tr><td class="soundmap-att-left">' + result.fileName + '</td><td>' + result.length + '</td></tr>');
            box = jQuery('#soundmap-attachments');
            box.append('<input type="hidden" name="soundmap_attachments_id[]" value="' + result.attachment + '">');

        }
    } catch (ex) {
        this.debug(ex);
    }
}

function s_uploadComplete(file) {
    try {
        /*  I want the next upload to continue automatically so I'll call startUpload here */
        if(this.getStats().files_queued > 0) {
            this.startUpload();
        } else {
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function s_uploadError(file, errorCode, message) {
    var imageName = "error.gif";
    //var progress;
    try {
        switch (errorCode) {
            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                try {
                } catch (ex1) {
                    this.debug(ex1);
                }
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                try {
                } catch (ex2) {
                    this.debug(ex2);
                }
            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                imageName = "uploadlimit.gif";
                break;
            default:
                alert(message);
                break;
        }

    } catch (ex3) {
        this.debug(ex3);
    }

}