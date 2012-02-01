var g_map;
var or_latln;
var info_w;

jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
    var map_canvas = $('.map_canvas');
    or_latln = new google.maps.LatLng(WP_Params.lat, WP_Params.lng);
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
    map_canvas.after('<div id="hidden-markers-content" style ="display:hidden;"></div>')
    info_w = false;
    if(map_canvas) {
        markers_map = $('.map_canvas').gmap({
            'center' : or_latln,
            'zoom' : Number(WP_Params.zoom),
            'mapTypeId' : or_maptype
        }).bind('init', function(event, map) {
            if( typeof onMapFinished == 'function') {
                onMapFinished(map);
            }
            g_map = map;
            info_w = new google.maps.InfoWindow({
                content : ' '
            });
            var data = {
                action : 'soundmap_JSON_load_markers',
                markers : WP_Params.markers

            };
            $.post(WP_Params.ajaxurl, data, function(response) {
                result = $.parseJSON(response);
                if(result) {
                    /////////////_ICON
                    var image = new google.maps.MarkerImage(WP_Params.pluginURL + '/img/map_icons/marker.png',
                    // This marker is 20 pixels wide by 32 pixels tall.
                    new google.maps.Size(19, 30),
                    // The origin for this image is 0,0.
                    new google.maps.Point(0, 0),
                    // The anchor for this image is the base of the flagpole at 0,32.
                    new google.maps.Point(9, 30));
                    var shadow = new google.maps.MarkerImage(WP_Params.pluginURL + '/img/map_icons/marker_shadow.png',
                    // The shadow image is larger in the horizontal dimension
                    // while the position and offset are the same as for the main image.
                    new google.maps.Size(34, 30), new google.maps.Point(0, 0), new google.maps.Point(9, 30));
                    // Shapes define the clickable region of the icon.
                    // The type defines an HTML <area> element 'poly' which
                    // traces out a polygon as a series of X,Y points. The final
                    // coordinate closes the poly by connecting to the first
                    // coordinate.
                    var shape = {
                        coord : [9, 0, 19, 9, 12, 17, 9, 30, 6, 17, 0, 9, 9, 0],
                        type : 'poly'
                    };
                    //////////////_ICON
                    ajax_url = WP_Params.ajaxurl;

                    $.each(result, function(i, m) {
                        $('.map_canvas').gmap('addMarker', {
                            id : m.id.toString(),
                            'position' : new google.maps.LatLng(m.lat, m.lng),
                            'shadow' : shadow,
                            'icon' : image,
                            'shape' : shape
                        }).click(function() {
                            id = this.id;
                            marker = this;


                            var data = {
                                action : 'soundmap_load_infowindow',
                                marker : id                                
                            };

                            $.post(ajax_url, data, function(response) {
                                info_w.close();
                                $("#hidden-markers-content").append(response);
                                info_w.setContent($("#hidden-markers-content").children().get(0));
                                info_w.open(g_map, marker);
                               
                            });
                        })
                    });
                }
            });
        });
    }

});
