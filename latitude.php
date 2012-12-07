<?php
/*
Plugin Name: Latitude
Plugin URI: http://rowatt.com/latitude
Description: Get your location from Google Latitude
Version: 0.1
Author: Mark Rowatt Anderson
Author URI: http://rowatt.com
License: GPL2
*/

/*  Copyright 2009-2012  Mark Rowatt Anderson  (http://rowatt.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('classes/class-latitude-plugin.php');

//get the plugin basename and abs path to plugin directory
//__FILE__ won't work for basename if path has symlinks
//if basename using __FILE__ has more than one '/' we probably
//have symlinks, in which case we have to assume that plugin is at
//latitude/latitude.php - so don't change dir if using symlinks!
if( substr_count(plugin_basename(__FILE__), DIRECTORY_SEPARATOR) <= 1 )
	define('LATITUDE__FILE', __FILE__);
else
	define('LATITUDE__FILE', WP_PLUGIN_DIR . '/' . 'latitude/latitude.php' );

define( 'LATITUDE_PLUGIN_DIR_URL', plugins_url( '', LATITUDE__FILE ) );
define( 'LATITUDE_PLUGIN_DIR', dirname(LATITUDE__FILE) );
define( 'LATITUDE_BASENAME', plugin_basename(LATITUDE__FILE) );

//define as TRUE in wp-config to turn on debug mode
if( !defined('LATITUDE_DEBUG') )
	define( 'LATITUDE_DEBUG', FALSE );

LatitudePlugin::get_instance();

/* EOF */