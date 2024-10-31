<?php
/*
 * Plugin Name: Rep U Press Social Media Analytics
 * Plugin URI:  http://www.repupress.com
 * Description: The Rep U Press Social Media Analytics Plugin will allow you to track your social shares across the web. Our Plugin currently supports the following networks: Facebook, Twitter, LinkedIn, Pinterest and Google+.
 * Version:     1.0
 * Author:      RepUPress.com
 * Author URI:  http://www.repupress.com/social-media-manager/
*/

// Primary Options Menu

function smabs_primary_menu() {
	add_menu_page(
		'Social Media Analytics Primary Menu',				/* Page Name */
		'RepUPress SA',										/* Menu Link */
		'manage_options',									/* Required User Role */
		'social-media-analytics-share-buttons',				/* Menu Slug */
		'smasb_primary_options_page'						/* Function Name */
	);		
}
add_action( 'admin_menu', 'smabs_primary_menu' );

function smasb_primary_options_page() {
	require( 'repupress-social-media-analytics.php' );
}



// Direct access is forbidden
if (!defined('WPINC'))
{
	exit;
}

// Check required PHP version
if (version_compare(PHP_VERSION, '5.3', '<'))
{
	require_once dirname( __FILE__ ) . '/requirements-error.php';
	add_action('admin_init', array('Smaasb_Requirements_Error', 'init'));
}
else  // Load plugin only if requirements are met
{

	// Require main plugin files	
	require_once (plugin_dir_path( __FILE__ ) . 'plugin/cron_actions.php');
	require_once (plugin_dir_path( __FILE__ ) . 'plugin/wp_social_analytics.php');

	// Ensure the main class exists
	if (class_exists('Social_Media_Analytics_Share_Buttons'))
	{

		// Hook activating plugin
		function smaasb_activate()
		{
			Social_Media_Analytics_Share_Buttons::activate();
		}

		// Hook deactivating plugin
		function smaasb_deactivate()
		{
			Social_Media_Analytics_Share_Buttons::deactivate();
		}	

		// Initialize the plugin
		function smaasb_init()
		{
			$plugin = new Social_Media_Analytics_Share_Buttons(plugin_dir_path(__FILE__), plugins_url('', __FILE__));
			$plugin->init();
		}

		// Check if plugin's demo mode is enabled
		function smaasb_is_demo()
		{
			return (!defined('WSA_DEMO') || WSA_DEMO === false) ? false : true;
		}

		// Init plugin and (de)activation hooks
		smaasb_init();
		register_activation_hook(__FILE__, 'smaasb_activate');
		register_deactivation_hook( __FILE__, 'smaasb_deactivate');
	}
	
	// Premium SEO Page

	function repupress_socail_media_analytics_premium_menu() {
		add_submenu_page(
			'social-media-analytics-share-buttons',
			'Social Media and Reputation Management Premium Platform',		/* Page Name */
			'Social Premium',										/* Menu Link */
			'manage_options',										/* Required User Role */
			'repupress-socail-media-analytics-premium',								/* Menu Slug */
			'repupress_socail_media_analytics_premium_options_page'					/* Function Name */
		);		
	}
	add_action( 'admin_menu', 'repupress_socail_media_analytics_premium_menu' );

	function repupress_socail_media_analytics_premium_options_page() {
		require_once 'repupress-socail-media-analytics-premium.php';
	}
	
	function repupress_socail_media_analytics_styles() {
	$repupress_socail_media_plugin_url = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'repupress_socail_media_analytics_styles', $repupress_socail_media_plugin_url . 'css/social-analytics.css');
	
	}
	add_action( 'admin_head', 'repupress_socail_media_analytics_styles' );
	
}