<?php
/**
 * 
 * Definitions of all wp-cron actions
 * 
 */

/**
 * Register "once weekly" schedule option
 */	
add_filter('cron_schedules', 'smaasb_add_weekly_schedule'); 

function smaasb_add_weekly_schedule($schedules)
{
	$schedules['weekly'] = array(
		'interval' => 7 * 24 * 3600, // 7 days * 24 hours * 60 minutes (3600 seconds)
		'display' => 'Once Weekly'
	);
	return $schedules;
}


/**
 * Send weekly report
 */	
add_action('smaasb_weekly_report', 'smaasb_weekly_report');

function smaasb_weekly_report()
{
	// Check if sending weekly report is enabled in the Settings
	// Ensure also that e-mail recipient is set
	$settings = get_option('smaasb_settings');
	if (!isset($settings['weekly_report_enabled']) || $settings['weekly_report_enabled'] == 0 || !isset($settings['weekly_report_recipient']) || empty($settings['weekly_report_recipient']))
	{
		return false;
	}

	// Set period: last 7 days
	$start = new DateTime();
	$end = new DateTime();
	$start->modify('-7 days');
	$end->modify('-1 day');

	// Get data for selected period
	$social_post = new Smaasb_Social_Post();
	$posts = $social_post->get_data($start, $end, 100);

	// Do not send report if any posts were published
	if (empty($posts)) 
	{
		return false;
	}

	// Prepare email
	$email = new Smaasb_Social_Email();

	// Set recipient
	$email->to($settings['weekly_report_recipient']);

	// Set subject
	$email->subject('Social Analytics Report for '.get_option('blogname'));

	// Set content
	$email->content($posts, $start, $end);
	
	// Send email
	$email->send();
}