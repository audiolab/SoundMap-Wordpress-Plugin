
jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function    
    var map_canvas = $('#map_canvas');
    var or_latln= new google.maps.LatLng(WP_Params.lat, WP_Params.lng);
    var or_maptype;
    switch (WP_Params.mapType){
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
    if (map_canvas){
             $('#map_canvas').gmap({'center': or_latln, 'zoom' : Number(WP_Params.zoom),'mapTypeId': or_maptype, 'callback': function(map){
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
    
    var map_canvas_options = $('#map_canvas_options');
    if (map_canvas_options){
	$('#map_canvas_options').gmap({'center': or_latln, 'zoom' : Number(WP_Params.zoom), 'mapTypeId': or_maptype, 'callback': function(map){
		$(map).dragend(function(event){
			var new_center = map.getCenter();
			$('#soundmap_op_origin_lat').val(new_center.lat());
			$('#soundmap_op_origin_lng').val(new_center.lng());
	        });
		$(map).addEventListener('zoom_changed', function(event){
		    var new_zoom = map.getZoom();
		    $('#soundmap_op_origin_zoom').val(new_zoom);
		});
		$(map).addEventListener('maptypeid_changed',function(event){
		    var new_type = map.getMapTypeId();
		    switch (new_type)
		    {
			case google.maps.MapTypeId.ROADMAP:
			    $('#soundmap_op_origin_type').children('option[value|="ROADMAP"]').attr('selected','selected');
			    break;
			case google.maps.MapTypeId.HYBRID:
			    $('#soundmap_op_origin_type').children('option[value|="HYBRID"]').attr('selected','selected');
			    break;
			case google.maps.MapTypeId.TERRAIN:
			    $('#soundmap_op_origin_type').children('option[value|="TERRAIN"]').attr('selected','selected');
			    break;
			case google.maps.MapTypeId.SATELLITE:
			    $('#soundmap_op_origin_type').children('option[value|="SATELLITE"]').attr('selected','selected');
			    break;
		    }
		});
                
	    }
	});
	$('#soundmap_op_origin_lat, #soundmap_op_origin_lng, #soundmap_op_origin_zoom', '#soundmap_op_origin_type').change(function(){	    
	    var latlng = new google.maps.LatLng($('#soundmap_op_origin_lat').val(), $('#soundmap_op_origin_lng').val());
	    var z = $('#soundmap_op_origin_zoom').val();	    
	    map = map_canvas_options.gmap('getMap');
	    map.panTo(latlng);
	    map.setCenter(latlng);
	    map.setZoom(Number(z));
	});
	$('#soundmap_op_origin_type').change(function(){
	    var t = $('#soundmap_op_origin_type').val();
	    map = map_canvas_options.gmap('getMap');
	    switch (t){
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