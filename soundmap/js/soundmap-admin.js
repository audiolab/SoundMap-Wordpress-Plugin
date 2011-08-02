
jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function    
    var map_canvas = $('#map_canvas');
    
    var soundmark;
    if (map_canvas){
             $('#map_canvas').gmap({'callback': function(map){
		if($('#soundmap-marker-lat').val()!=""){
		    var latlng = new google.maps.LatLng($('#soundmap-marker-lat').val(), $('#soundmap-marker-lng').val());
		    map.panTo(latlng);
		     soundmark = map_canvas.gmap('addMarker', {'position': latlng, 'title':'', 'draggable':true}, function(map, marker){                            
                        });
		}
                $(map).click( function (event){
                    if (soundmark == undefined){
                        soundmark = map_canvas.gmap('addMarker', {'position': event.latLng, 'title':'', 'draggable':true}, function(map, marker){
                            var marker_position=marker.getPosition();
                            $('#soundmap-marker-lat').val(marker_position.lat());
                            $('#soundmap-marker-lng').val(marker_position.lng());
                        });
                        
                    }else{
                        var marker = soundmark.get(0);
                        var new_position = event.latLng;
                        
                        marker.setPosition(new_position);
                        
                        $('#soundmap-marker-lat').val(new_position.lat());
                        $('#soundmap-marker-lng').val(new_position.lng());
                    }                    
                    })                
                }});    
    }
    
    $('#soundmap-marker-lat, #soundmap-marker-lng').change(function(){
	var latlng = new google.maps.LatLng($('#soundmap-marker-lat').val(), $('#soundmap-marker-lng').val());
	map = map_canvas.gmap('getMap');
	map.panTo(latlng);
	if(soundmark == undefined){
	  soundmark = map_canvas.gmap('addMarker', {'position': latlng, 'title':'', 'draggable':true}, function(map, marker){});
	}else{
	    var marker = soundmark.get(0);	    	    
	    marker.setPosition(latlng);
	}	
    });
    
    $("#uploader").pluploadQueue({
		// General settings
		runtimes : 'gears,flash,silverlight,browserplus,html5',
		url : WP_Params.plugin_url + 'js/plupload/upload.php',
		max_file_size : '10mb',
		chunk_size : '1mb',
		unique_names : true,		
		multiple_queues : true,
		// Specify what files to browse for
		filters : [
			{title : "Sound files", extensions : "mp3"},			
		],

		// Flash settings
		flash_swf_url : WP_Params.plugin_url + 'js/plupload/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : WP_Params.plugin_url + 'js/plupload/plupload.silverlight.xap',
		//Events
		init : {
		    FileUploaded: function(up, file, info) {
			// Called when a file has finished uploading
			//log('[FileUploaded] File:', file, "Info:", info);
			var data = {
		    	    action: 'soundmap_file_uploaded',
			    file_name: file,
			    file_info: info
			};
			$.post(ajaxurl, data, function(response) {
			    result = $.parseJSON(response);
			    if (result.error != ""){
				alert (result.error);
			    }else{
				table = $('#sound-att-table');
				table.append ('<tr><td class="soundmap-att-left">' + result.fileName + '</td><td>' + result.length + '</td></tr>');
				box = $('#soundmap-attachments');
				box.append ('<input type="hidden" name="soundmap_attachments_id[]" value="' + result.attachment + '">');
			    }
			});
		    }  
		}
    });
    
    $('#post').submit(function(e) {
	    var uploader = $('#uploader').pluploadQueue();

	    // Validate number of uploaded files
	    if (uploader.total.uploaded == 0) {
		    // Files in queue upload them first
		    if (uploader.files.length > 0) {
			    // When all files are uploaded submit form
			    uploader.bind('UploadProgress', function() {
				    if (uploader.total.uploaded == uploader.files.length)
					    $('#post').submit();
			    });

			    uploader.start();
		    } else
			  //  alert('You must at least upload one file.');

		    e.preventDefault();
	    }
    });
    
    $('#soundmap-marker-datepicker').DatePicker({
	format:'d/m/Y',
	date: $('#soundmap-marker-date').val(),
	current: $('#soundmap-marker-date').val(),
	starts: 1,
	flat: true,
	onChange: function(formated, dates){
		$('#soundmap-marker-date').val(formated);
	}
    });
	
});