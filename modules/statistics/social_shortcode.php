<?php
// Add Shortcode
function social_shares_shortcode($atts, $content = null) {

	// Attributes
	extract(shortcode_atts(
		array(
			'service' => 'all',
		), $atts )
	);

	// For service = "all" use "total" field from the object
	if ($service == 'all')
	{
		$service = 'total';
	}

	// Get statistic and return it
	$shares = social_shares();
	return $shares->$service;
}
add_shortcode('social_shares', 'social_shares_shortcode');