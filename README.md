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

To show the map, you only have to select the url you want to use for this. On the config page you will find and option called <b>Show map page</b> here you can write the url. 
For example, if you want to use www.yoursoundmap.com/map you need to write here <b>map</b>. If you want to use the root, write [home].

Once you select one url, the plugin will use a template page called 'theme_yoururl.php'. You can see the template file located under 'soundmap/theme' as an example.

It is important that inside the template file you include this HTML line:
 	<div class="map_canvas"></div> 
for rendering the map.

Examples
----------
http://www.soinumapa.net

License
---------------------

Copyright 2011  Audio Laborategia Elkartea  (email : audiolab.elkartea@gmail.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

