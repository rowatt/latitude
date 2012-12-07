<?php

global $wpsf_settings;
$refresh_page = site_url() . '?refresh_location=1';

$wpsf_settings[] = array(
    'section_id' => 'general',
    'section_title' => 'General Settings',
    'section_description' => '',
    'section_order' => 1,
    'fields' => array(
        array(
            'id' => 'user_id',
            'title' => 'Latitude User ID',
            'desc' => 'The Google Latitude user id to use. This is found on the <a href="https://latitude.google.com/latitude/b/0/apps" target="_blank">Latitude sharing page</a> inside in the code for embedding Latitude on a web site.',
            'type' => 'text',
            'std' => ''
        ),
        array(
	        'id' => 'stale_time',
	        'title' => 'Stale location timeout',
	        'desc' => '(hours) Don\'t show location if it is older than this many hours. Enter 0 if you want to show the location however old it is.',
	        'type' => 'text',
	        'std' => '24'
        ),
        array(
	        'id' => 'cache_time',
	        'title' => 'Location cache time',
	        'desc' => "(hours) Don't update the location more than once in this many hours. This avoids having to get the location from Google every time a page is loaded which would slow page loading. Enter 0 if you do not want to cache the location at all. You can force an update by visiting this settings page.",
	        'type' => 'text',
	        'std' => '6'
        ),
        array(
	        'id' => 'unknown_text',
	        'title' => 'Unknown location text',
	        'desc' => 'text to display instead of location if the location cannot be determined or if it is too old.',
	        'type' => 'text',
	        'std' => 'an unknown location'
        )
    )
);

/* EOF */