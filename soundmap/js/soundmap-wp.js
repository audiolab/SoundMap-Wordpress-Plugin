
jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function    
    var map_canvas = $('.map_canvas');
    var g_map;
    map_canvas.after('<div id="hidden-markers-content" style ="display:hidden;"></div>')
    info_w = false;
    if (map_canvas){
        markers_map = $('.map_canvas').gmap({'callback': function(map){
		g_map=map;
		info_w = new google.maps.InfoWindow({content: ' '});

	    	var data = {
		    action: 'soundmap_JSON_load_markers'
		};
		$.post(WP_Params.ajaxurl, data, function(response) {
		    result = $.parseJSON(response);
		    $.each( result, function(i, m) {
			$('.map_canvas').gmap('addMarker', { 'position': new google.maps.LatLng(m.lat, m.lng) }).data('postid', m.id).click(function(){
			    id=$(this).data('postid');
			    marker = $(this).get(0);
			    
			    var data = {
				action: 'soundmap_load_infowindow',
				marker: id
			    };
			    
			    $.post(WP_Params.ajaxurl, data, function(response){
				info_w.close();
				$("#hidden-markers-content").append(response);								
				info_w.setContent($("#hidden-markers-content").children().get(0));
				info_w.open(g_map, marker);			    					
			    });			    
			})			    			    			    
		    });
		});
            }
        });    
    }
        
	
});