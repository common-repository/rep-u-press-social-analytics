<?php
/**
 * 
 * Main class of the plugin responsible for initializing all modules
 * 
 */
class Social_Media_Analytics_Share_Buttons
{
	/**
	 * Path to plugin directory
	 */
	private $plugin_path;

	/**
	 * Plugin URL
	 */	
	private $plugin_url;

	/**
	 *  Save values from WP to further use as we don't want to use global functions
	 */
	public function __construct($plugin_path, $plugin_url)
	{
		$this->plugin_path = $plugin_path;
		$this->plugin_url = $plugin_url;
	}

	/**
	*  Called upon plugin activation in Wordpress
	*/
	public static function activate()
	{
		global $wpdb;

		// Create separate DB table for posts social statistics
		$table_name = $wpdb->prefix . "posts_social"; 
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  post_id int(11) unsigned NOT NULL,
		  facebook int(11) NOT NULL DEFAULT 0,
		  twitter int(11) NOT NULL DEFAULT 0,
		  pinterest int(11) NOT NULL DEFAULT 0,
		  googleplus int(11) NOT NULL DEFAULT 0,
		  linkedin int(11) NOT NULL DEFAULT 0,
		  updated int(11) unsigned DEFAULT NULL,
		  PRIMARY  KEY  (post_id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		// Store version of database schema
		add_option('smaasb_db_version', '1.0.0');

		// Schedule weekly summary email (via wp-cron)
		$timestamp = wp_next_scheduled('smaasb_weekly_report');
		if ($timestamp == false)
		{
			// Send emails weekly, starting from next Monday
			wp_schedule_event(strtotime('next monday'), 'weekly', 'smaasb_weekly_report');
		}

	}

	/**
	*  Called upon plugin deactivation in Wordpress
	*/
	public static function deactivate()
	{
		// Remove scheduled entries from wp-cron
		wp_clear_scheduled_hook('smaasb_weekly_report');
	}

	/**
	*  Initializes the plugin
	*/
	public function init()
	{		
		// Load required files
		$this->load_dependencies();
		
		// Prepare module loader
		$loader = new Smaasb_Module_Loader();

		// Register module Statistics (front)
		$loader->register_module($this->plugin_path, $this->plugin_url, 'statistics');		

		// Register module Dashboard (admin)
		$loader->register_module($this->plugin_path, $this->plugin_url, 'dashboard');

		// Register module Settings (admin)
		$loader->register_module($this->plugin_path, $this->plugin_url, 'settings');

		// Initialize registered modules
		$loader->init_modules();
	}

	/**
	*  Load required files
	*/
	private function load_dependencies()
	{
		include($this->plugin_path . 'plugin/module/loader.php');
		include($this->plugin_path . 'plugin/module/interface.php');
		include($this->plugin_path . 'plugin/module/admin.php');
	}

}