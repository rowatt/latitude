<?php

class LatitudePlugin
{
	/**
	 * @var LatitudePlugin holds instance for this singleton
	 */
	private static $instance = NULL;

	/**
	 * @var string minimum WP version
	 */
	private $min_wp_version = '3.4';

	/**
	 * @var string minimum PHP version
	 */
	private $min_php_version = '50200';

	/**
	 * @var int Google Latitude User ID
	 */
	private $user_id;

	/**
	 * @var string URL to get location info from
	 */
	private $latitude_url = 'https://latitude.google.com/latitude/apps/badge/api';

	/**
	 * @var string Text to use if we can't determine location
	 */
	private $unknown_location_text = 'an unknown location';

	/**
	 * @var int how many hours before location is considered stale and therefore not shown
	 */
	private $stale_time = 24;

	/**
	 * @var int how many hours to cache location data for
	 */
	private $cache_time = 6;

	/**
	 * @var WordPressSettingsFramework settings framework object
	 */
	private $wpsf;

	/**
	 * @var string
	 */
	private $l10n = 'latitude';

	/**
	 * @var array holds any initialisation errors
	 */
	private $errors = array();

	/**
	 * Singleton instantiator
	 * @static
	 * @return LatitudePlugin
	 */
	public static function get_instance()
	{
		if( !self::$instance instanceof LatitudePlugin )
			self::$instance = new LatitudePlugin();

		return self::$instance;
	}

	public function __construct()
	{
		//check we have correct versions of WP, PHP etc.
		if( !$this->check_requirements() )
		{
			add_action( 'admin_notices', array( &$this, 'abort_notice') );
			return;
		}

		//get options
		$options = get_option('latitude_settings');
		if( is_array($options) )
		{
			if( !empty($options['latitude_general_user_id']) ) $this->user_id = $options['latitude_general_user_id'];
			if( !empty($options['latitude_general_unknown_location_text']) ) $this->unknown_location_text = $options['latitude_general_unknown_location_text'];
			if( array_key_exists( 'latitude_general_stale_time', $options) ) $this->stale_time = $options['latitude_general_stale_time'];
			if( array_key_exists( 'latitude_general_cache_time', $options) ) $this->cache_time = $options['latitude_general_cache_time'];
		}
		/* --------------------------------------------------------------
		/* !SETUP HOOKS
		/* -------------------------------------------------------------- */

		//setup options
		add_action( 'init', array( &$this, 'options_init') );

		//add shortcode
		add_shortcode( 'location' , array( &$this, 'get_location') );

		//uninstall
		register_uninstall_hook(__FILE__, array( __CLASS__, 'uninstall') );
	}

	/**
	 * Show message if plugin can't run
	 * Run from admin_notices hook if required
	 */
	public function abort_notice()
	{
		if( !is_admin() || !current_user_can('activate_plugins') ) return;
		$msg = implode( '<br />', $this->errors );
		if( empty($msg) ) $msg = "Unknown error initialising Latitude plugin";
		echo "<div id='message' class='error'><p><strong>{$msg}</strong></p></div>";
	}

	/**
	 * Initialise options, settings page etc
	 */
	public function options_init()
	{
		//nothing to do if we are not in admin
		//can't use admin_init hook, because some of our hooks here are called before admin_init
		if( !is_admin() ) return;

		//add settings page
		add_action( 'admin_menu', array(&$this, 'admin_menu'), 99 );

		//add settings link to plugin listing
		add_filter( 'plugin_action_links', array( __CLASS__, 'plugin_links'), 10, 2 );

		// Include and create a new WordPressSettingsFramework
		require_once( LATITUDE_PLUGIN_DIR .'/settings/wp-settings-framework.php' );
		$this->wpsf = new WordPressSettingsFramework( LATITUDE_PLUGIN_DIR .'/settings/latitude-general.php', 'latitude' );

		// Add settings validation filter
		add_filter( $this->wpsf->get_option_group() .'_settings_validate', array(&$this, 'validate_settings') );
	}

	/**
	 * add admin menu
	 * run by admin_menu hook
	 */
	public function admin_menu()
	{
		add_options_page( __( 'Latitude Settings', $this->l10n ), __( 'Latitude', $this->l10n ), 'edit_posts', 'latitude', array(&$this, 'settings_page') );
	}

	/**
	 * The main settings page
	 */
	public function settings_page()
	{
		//clear the location cache whenever we visit the settings page
		delete_transient( 'latitude_user_location' );

	?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h2>Latitude Plugin Settings</h2>
		<?php
			$this->wpsf->settings();
		?>
    </div>
	<?php

	}

	/**
	 * Same as $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	public function validate_settings( $input )
	{
		$old_values = get_option('latitude_settings');
		$errors = array();

		//user_id
		if( !is_numeric($input['latitude_general_user_id']) )
		{
			$errors[] = "{$input['latitude_general_user_id']} is not a valid Google Latitude user id";
			$input['latitude_general_user_id'] = $old_values['latitude_general_user_id'];
		}

		//stale_time
		if( !is_numeric($input['latitude_general_stale_time']) )
		{
			$errors[] = "Stale location timeout must be a number";
			$input['latitude_general_stale_time'] = $old_values['latitude_general_stale_time'];
		}

		//cache_time
		if( !is_numeric($input['latitude_general_cache_time']) )
		{
			$errors[] = "Location cache time must be a number";
			$input['latitude_general_cache_time'] = $old_values['latitude_general_cache_time'];
		}
		elseif( 0==$input['latitude_general_cache_time'] )
		{
			//clear location cache if we aren't caching any more
			delete_transient( 'latitude_user_location' );
		}
		//unknown_text
		//no validation

		if( $errors )
			add_settings_error( 'latitude', 'code', implode('<br />',$errors), 'error' );
		else
			add_settings_error( 'latitude', 'code', 'Latitude options updated.', 'updated' );

		return $input;
	}

	/**
	 * Add settings to plugin listing page
	 * Called by plugin_action_links filter
	 *
	 * @static
	 * @param $links
	 * @param $file
	 * @return array
	 */
	static public function plugin_links( $links, $file )
	{
		if ( $file == LATITUDE_BASENAME )
		{
			$add_link = '<a href="'.get_admin_url().'options-general.php?page=latitude">'.__('Settings').'</a>';
			array_unshift( $links, $add_link );
		}
		return $links;
	}

	/**
	 * Check core requirements met. Run during __construct.
	 */
	private function check_requirements()
	{
		if( version_compare( get_bloginfo( 'version' ), $this->min_wp_version, '<') )
			$this->errors[] = "Latitude plugin requires at least WordPress version {$this->min_wp_version}";

		//get php version
		if (!defined('PHP_VERSION_ID'))
		{
			$php_version = explode('.', PHP_VERSION);
			define('PHP_VERSION_ID', ($php_version[0] * 10000 + $php_version[1] * 100 + $php_version[2]));
		}

		if( PHP_VERSION_ID < $this->min_php_version )
		{
			$major_v = intval($this->min_php_version/10000);
			$minor_v = intval($this->min_php_version/100) - $major_v*100;
			$release_v = $this->min_php_version - $major_v*10000 - $minor_v*100;
			$this->errors[] = "Latitude plugin requires PHP version {$major_v}.{$minor_v}.{$release_v} or greater.";
		}

		if( $this->errors )
			return FALSE;
		else
			return TRUE;
	}
	
	/**
	 * Delete plugin and user options entry when plugin is deleted
	 *
	 * @static
	 */
	static public function uninstall()
	{
		delete_option('latitude_options');
	}

	/**
	 * Get the location of the defined user from Google
	 *
	 * @return string text for location
	 */
	public function get_location()
	{
		//check persistent cache
		$location_text = get_transient('latitude_user_location');
		if( $location_text ) return $location_text;

		//nothing cached, or cache has expired...

		$result = wp_remote_get("{$this->latitude_url}?user={$this->user_id}&type=json");

		$body = empty( $result['body'] ) ? FALSE : $result['body'];
		$location_age = 0;

		if( $body )
		{
			$vars = json_decode( $body );

			$location = empty($vars->features[0]->properties->reverseGeocode) ? '' : $vars->features[0]->properties->reverseGeocode;
			$time = empty($vars->features[0]->properties->timeStamp) ? 0 : $vars->features[0]->properties->timeStamp;
			$location_age = time() - $time;

			if( !$time )
				$location_text =  $this->unknown_location_text;
			elseif( $this->stale_time && ( $location_age > $this->stale_time*60*60) )
				$location_text =  $this->unknown_location_text;
			elseif( !$location )
				$location_text =  $this->unknown_location_text;
			else
				$location_text = $location;
		}
		else
		{
			$location_text = $this->unknown_location_text;
		}

		//set cache if required
		if( $this->cache_time )
			set_transient( 'latitude_user_location', $location_text, $this->cache_time * 60 * 60 );

		return $location_text;
	}

}

/* EOF */