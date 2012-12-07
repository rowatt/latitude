=== Plugin Name ===
Contributors: markauk
Tags: location, latitude, google
Requires at least: 3.4
Tested up to: 3.5
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show your Google Latitude location on your web site.

== Description ==

This plugin allows you to insert your location anywhere on your web site using a simple [location] shortcode.

The plugin gets your location from Google Latitude. You must set your Latitude account to allow public sharing of your location, which can be done on your [Google Latitude account page](https://latitude.google.com/latitude/b/0/apps).

Plugin options allow:

* defining which user to get location for
* how long to cache the location for to help site performance
* after how long a location is considered stale and not displayed
* what text to show if the location is stale or otherwise not known

In the future I may add more flexibility such as parameters in the shortcode or a widget. Please [contact me](http://rowatt.com) if you like this plugin and have any suggestions.

== Installation ==

1. Upload to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the settings - in particular you must add your Latitude user id
1. use the [location] shortcode anywhere you want your location displayed

== Frequently Asked Questions ==

= What is my Latitude user ID and where do I find it? =

Your Latitude user ID is *not* your Google account ID/email address. The Latitude ID is a long number and can be found on your [Google Latitude account page](https://latitude.google.com/latitude/b/0/apps) inside the Location Badge code immediately after where it says `user=`.


== Changelog ==

= 0.1 =

* Initial release
