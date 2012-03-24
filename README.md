SOUNDMAP FOR WORDPRESS
=================

Soundmap is a free plugin for implement soundmaps over a Wordpress site and the Google Maps Api.
The plugin creates a new content type called Marker, wich represent the sound marks over the map.


Installation
-----

You only need a Wordpress instalation and just installl the plugin as any other:

1. Download the plugin
2. Copy the folder <b>soundmap</b> inside your Wordpress Plugins folder (usually 'wp-content/plugins')
3. Activate the plugin from the Plugins option inside the Wordpress's administration area.

Usage
------

Once you have activated the plugin on your WordPress installation, creating a soundmap is very easy.
On the configuration panel for plugin, located under the Settings panel of Wordpress, ypu will find all the options related with the plugin, just selected the original position, zoom and type for the map and the sound player you want to use.

Showing the soundmap
-----

To show the map, you only have use the theme tag <b>the_map</b> as follows:

	the_map(css_id = 'map_canvas', all_markers = FALSE, $options = array());

in the template page you want to show the map. More information about this and more theme tags in the documentation.

It is important that inside the template file you include this HTML line:
 	<?php the_map("map_canvas",true); ?>
for rendering the map.

<b>For more info, look in the WIKI pages.</b>

Examples
----------
http://www.soinumapa.net

License
---------------------

Copyright 2011  Audio Laborategia Elkartea  (email : audiolab.elkartea@gmail.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

