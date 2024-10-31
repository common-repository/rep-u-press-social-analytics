<?php
require_once 'social_counter.php';
require_once 'social_demo.php';
require_once 'social_post.php';
require_once 'social_function.php';
require_once 'social_shortcode.php';
require_once 'social_email_report.php';

/**
 * 
 * Statistics module, responsible for updating social media shares statistics for posts
 * 
 */
class Smaasb_Statistics implements Smaasb_Module_Interface
{

	/**
	 * Called on module initialization
	 */
	public function init()
	{		

		// Register AJAX method - statistics updates are being called via JS
		add_action("wp_ajax_nopriv_update_shares_count", array($this, 'ajaxUpdateSocialCounter'));
		add_action("wp_ajax_update_shares_count",  array($this, 'ajaxUpdateSocialCounter'));			

		// Register filter for the_content function on Frontend
		if (!is_admin())
		{
			add_filter('the_content', array($this, 'socialCounter'));
		}
	}	

	/**
	 * Generate JS code responsible for making AJAX request while being on post
	 * Social media statistics are updated in this AJAX request
	 */	
	public function socialCounter($post)
	{
		// Count statistics only for posts/pages
		if (!is_singular())
		{
			return $post;
		}

		// Get data about current post
		$post_id = get_the_ID();
		$post_url = get_permalink();
		$post_date = get_the_date('U');

		// Add JS code at the end of content
		$code = "<script type=\"text/javascript\">
				httpRequest = new XMLHttpRequest();
    			httpRequest.open('POST', '".admin_url( 'admin-ajax.php' )."');
    			httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    			httpRequest.send('action=update_shares_count&post_id=".$post_id."&post_url='+encodeURIComponent('".$post_url."')+'&post_date=".$post_date."');
			</script>";
		$post .= $code;
		
		return $post;
	}

	/**
	 * Handle AJAX request for updating social media shares statistics
	 */	
	public function ajaxUpdateSocialCounter()
	{
		// Check if all required parameters are in request
		if (!isset($_POST['post_id']) || !isset($_POST['post_url']) || !isset($_POST['post_date']))
		{
			wp_die();	
		}
		
		// Sanitize input parameters
		$post_id = (int) $_POST['post_id'];
		$post_date = (int) $_POST['post_date'];
		$post_url = sanitize_text_field($_POST['post_url']);

		// Update statistics
		$this->updateSocialCounter($post_id, $post_url, $post_date);

		// Echo "success" message and end script
		echo '1';
		wp_die();
	}

	/**
	 * Update social media statistics for specific post
	 */	
	public function updateSocialCounter($post_id, $post_url, $post_date)
	{
		// Get current statistics 
		$model = new Smaasb_Social_Post();
		$social_post = $model->find($post_id);	

		$social_counter = new Smaasb_Social_Counter();

		if ($social_post) // there is data for this post
		{
			// Decide if social media stats should be updated, based on post's age
			$hours_since_publication = floor( (time() -  $post_date )/3600);	
			$interval = $this->determineCacheInterval($hours_since_publication);

			if (time() - $social_post->updated > $interval) // it's required to update data
			{
				// Get new statistics
				$data_count = $social_counter->count($post_url);

				// Save them in DB
				$model->update($post_id, $data_count);	
			}
		}
		else // No data for this post
		{
			// Get statistics
			$data_count = $social_counter->count($post_url);

			// Insert them to DB
			$model->insert($post_id, $data_count);
		}

	}

	/**
	 * Determine cache interval based on post's age
	 */	
	private function determineCacheInterval($hours)
	{
		$hours = (int) $hours;

		if ($hours < 3)
		{
			$minutes = 5;
		}
		else if ($hours < 6)
		{
			$minutes = 15;
		}
		else if ($hours < 12)
		{
			$minutes = 30;
		}
		else if ($hours < 24)
		{
			$minutes = 60;
		}
		else if ($hours < 168)
		{
			$minutes = 300;
		}
		else 
		{
			$minutes = 10080;
		}

		return $minutes * 60;
	}

}