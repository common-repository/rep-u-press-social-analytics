<?php
/**
 * 
 * Class responsible for handling unsupported PHP version
 * 
 */
class Smaasb_Requirements_Error
{	

	/**
	 * Called on class initialization
	 */	
	public static function init()
	{
		// Display requirements error only to users who can manage plugins
		if (current_user_can('activate_plugins'))
		{
			// Register admin notice
			add_action('admin_notices', array(__CLASS__, 'notice'));
		}
	}


	/**
	 * Display notice to user
	 */
	public static function notice()
	{
		// Print error message
		printf('<div class="error"><p>Social Analytics plugin isn\'t working correctly. Your PHP version is %s, while minimum 5.3 is required.</p></div>', PHP_VERSION);
	}
}