SOUNDMAP FOR WORDPRESS
=================

Soundmap is a free plugin for implement soundmaps over a Wordpress site and the Google Maps Api.
The plugin creates a new content type called Marker, wich represent the sound marks over the map.


Installation
-----

You only need a Wordpress instalation and just installl the plugin as any other:

	1. Download the plugin
	2. Copy the folder *soundmap* inside your Wordpress Plugins folder (usually 'wp-content/plugins')
	3. Activate the plugin from the Plugins option inside the Wordpress's administration area.

Usage
------

Once you have activated the plugin on your WordPress installation, creating a soundmap is very easy.
On the configuration panel for plugin, located under the Settings panel of Wordpress, ypu will find all the options related with the plugin, just selected the original position, zoom and type for the map and the sound player you want to use.

Showing the soundmap on your wordpress
-----

To show the map, you only have to select the url you want to use for this. On the config page you will find and option called *Show map page* here you can write the url. 
For example, if you want to use www.ypursoundmap.com/map you need to write here *map*. If you want to use the root, write [home].

Once you select one url, the plugin will use a template page called 'theme_yoururl.php'. You can see the template file located under 'soundmap/theme' as an example.

It is important that inside the template file you include this HTML line <div class="map_canvas"></div> for rendering the map.

Examples
----------
http://www.souinumapa.net

License
---------------------

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this work except in compliance with the License.
You may obtain a copy of the License in the LICENSE file, or at:

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

