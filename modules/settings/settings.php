<?php
/**
 * 
 * Settings module, displays and saves settings form
 * 
 */
class Smaasb_Settings extends Smaasb_Module_Admin implements Smaasb_Module_Interface
{
	/**
	 * Called on module initialization
	 */
	public function init()
	{		
		// Register page in Wordpress
		$this->register_page(array(
			'parent' => 'social-media-analytics-share-buttons',
			'title' => 'WP Social Analytics Email Settings',
			'menu' => 'Email Settings',
			'capability' => (!smaasb_is_demo()) ? 'manage_options' : 'publish_posts'
		));

		// Register Wordpress settings
		add_action('admin_init', array($this, 'init_settings'));

		// Add link to settings page directly on Plugins page
		add_filter('plugin_action_links_wp-social-analytics/bootstrap.php', array($this, 'plugin_settings_link'));

	}	

	/**
	 * Initialize settings fields in Wordpress
	 */
	public function init_settings()
	{
		// Register setting group
		register_setting('smaasb_settings', 'smaasb_settings', array($this, 'sanitize_setting'));

		// Register section for Weekly report options
		add_settings_section('smaasb_settings_weekly_report', 'Weekly report', array($this, 'section_weekly_report_callback'), 'smaasb_settings');		

		// Enable/disable field
		add_settings_field('weekly_report_enabled', 'Send weekly reports', array($this, 'render_setting'), 'smaasb_settings', 'smaasb_settings_weekly_report', array('id'=>'weekly_report_enabled', 'desc'=>'', 'type'=>'checkbox'));

		// Recipient's email field
		add_settings_field('weekly_report_recipient', 'Recipient\'s email', array($this, 'render_setting'), 'smaasb_settings', 'smaasb_settings_weekly_report', array('id'=>'weekly_report_recipient', 'desc'=>''));

		// Default settings
		$this->init_default_settings();
		
	}

	/**
	 * Render form for settings field
	 */
	public function render_setting($args)
	{
		extract($args);

		// Option name in DB
		$option_name = 'smaasb_settings';

		// By default all fields are: type="text"
		if (!isset($type))
		{
			$type = 'text';
		}

		// Read current option values
		$options = get_option($option_name);

		// For non-existing options in DB make them empty
		if (!isset($options[$id]))
		{
			$options[$id] = '';
		}

		// Render field
		switch ($type)
		{
          case 'text':
              if (empty($options[$id]))
              {
              	$options[$id] = null;
              }
              echo "<input class='regular-text' type='text' id='label_$id' name='" . $option_name . "[$id]' value='".esc_attr($options[$id])."' />";  
              echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
            break;
          case 'checkbox':
              if (empty($options[$id]))
              {
				$options[$id] = 0;   
              }
              echo '<input name="'.$option_name.'['.$id.']" id="'.$id.'" type="checkbox" value="1" '. (($options[$id] == 1) ? 'checked="checked"' : '') .'>';
              echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
            break;
    	}
	}

	/**
	 * Add link to settings page
	 */
	public function plugin_settings_link($links)
	{
		$link = '<a href="admin.php?page=social-analytics">Settings</a>'; 
  		array_unshift($links, $link); 
  		return $links; 
	}

	/**
	 * Callback function called by Wordpress when sending form
	 * 
	 */
	public function sanitize_setting($data)
	{
		// Set 0 instead of null for weekly report enabled status - so we know user disabled it
		if (!isset($data['weekly_report_enabled']))
		{ 
			$data['weekly_report_enabled'] = 0;
		}

		// Validate recipient's email - ignore values which don't have email format
		if (isset($data['weekly_report_recipient']) && !filter_var($data['weekly_report_recipient'], FILTER_VALIDATE_EMAIL))
		{
    		$data['weekly_report_recipient'] = '';
		}
		return $data;
	}

	/**
	 * Called when module page is being accessed
	 */
	public function load()
	{
		// Nothing here - Form handling is done by Wordpress itself
		// See view file for more details
	}

	/**
	 * Called when module page is being rendered
	 */	
	public function render()
	{
	
		// Prepare data which will be passed to the view
		$view_data = array(
			'title' => $this->options['title'],
			'email' => $this->sample_email_content(),
			'view' => $this->options['module'].'/'.$this->options['module'].'.view.php',
		);

		extract($view_data);
		include($this->options['path'] . 'views/layout.php');	
	}		

	/**
	 * Responsible for displaying Weekly Report section
	 */	
	public function section_weekly_report_callback($args)
	{
		echo 'Each Monday you can receive weekly report with social shares statistics of all posts published during the week.';
	}

	/**
	 * Initialize default settings
	 */
	private function init_default_settings()
	{
		
		// Read current options
		$options = get_option('smaasb_settings');
		$changed = false;

		// Enable sending weekly reports by default
		if (!isset($options['weekly_report_enabled']))
		{
			$options['weekly_report_enabled'] = 1;
			$changed = true;
		}

		// Set admin email as default recipient of weekly reports
		if (!isset($options['weekly_report_recipient']))
		{
			$options['weekly_report_recipient'] = get_option('admin_email');
			$changed = true;
		}

		// Save default values, if needed
		if ($changed === true)
		{
			update_option('smaasb_settings', $options);		
		}		
	}

	/**
	 * Get sample email content for preview
	 */
	private function sample_email_content()
	{
		// Set period: last 7 days
		$start = new DateTime();
		$end = new DateTime();
		$start->modify('-7 days');
		$end->modify('-1 day');

		// Prepare email with sample data
		$email = new Smaasb_Social_Email();
		$posts = Smaasb_Social_Post_Demo::get_data(5);

		// Apply content
		return $email->content($posts, $start, $end);
	}
}